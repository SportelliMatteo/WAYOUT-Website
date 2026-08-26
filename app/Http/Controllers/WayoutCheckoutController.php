<?php

namespace App\Http\Controllers;

use App\Exceptions\WayoutApiException;
use App\Support\CheckoutFeatures;
use App\Support\DatabaseUuid;
use App\Support\FounderPromoCatalog;
use App\Support\ItalianFiscalData;
use App\Support\PrivacySafeLogContext;
use App\Support\WayoutApiClient;
use App\Support\WayoutIdentityService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Throwable;

class WayoutCheckoutController extends Controller
{
    public function store(
        Request $request,
        CheckoutFeatures $checkoutFeatures,
        FounderPromoCatalog $catalog,
        WayoutIdentityService $identities,
        WayoutApiClient $wayout,
    ) {
        if (! $request->session()->get('waitlist_offer_access')) {
            return response()->json(['error' => __('messages.messages.checkout_unauthorized')], 403);
        }

        $email = strtolower((string) $request->session()->get('waitlist_email', ''));
        $plan = (string) $request->input('plan');

        if ($email === '') {
            return response()->json(['error' => __('messages.messages.purchase_email_missing')], 403);
        }

        if (! in_array($plan, ['join', 'creator'], true)) {
            return response()->json(['error' => __('messages.messages.invalid_pass')], 422);
        }

        $legalEntityInvoiceEnabled = $checkoutFeatures->legalEntityInvoiceEnabled();
        $allowedBillingCustomerTypes = $legalEntityInvoiceEnabled ? ['individual', 'legal_entity'] : ['individual'];
        $validated = $request->validate([
            'plan' => ['required', Rule::in(['join', 'creator'])],
            'firebase_token' => ['required', 'string', 'max:10000'],
            'first_name' => ['required', 'string', 'min:2', 'max:120', "regex:/^[\\pL\\pM][\\pL\\pM .'-]*$/u"],
            'last_name' => ['required', 'string', 'min:2', 'max:120', "regex:/^[\\pL\\pM][\\pL\\pM .'-]*$/u"],
            'birth_date' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'gender' => ['required', Rule::in(['MALE', 'FEMALE', 'OTHER'])],
            'invoice_requested' => ['sometimes', 'boolean'],
            'purchase_terms_accepted' => ['required', 'accepted'],
            'billing_customer_type' => [Rule::requiredIf($request->boolean('invoice_requested')), 'nullable', Rule::in($allowedBillingCustomerTypes)],
            'billing_address' => [Rule::requiredIf($request->boolean('invoice_requested')), 'nullable', 'string', 'max:255'],
            'billing_postal_code' => [Rule::requiredIf($request->boolean('invoice_requested')), 'nullable', 'string', 'max:20'],
            'billing_city' => [Rule::requiredIf($request->boolean('invoice_requested')), 'nullable', 'string', 'max:120'],
            'billing_province' => [Rule::requiredIf($request->boolean('invoice_requested')), 'nullable', 'string', 'max:8'],
            'billing_country' => [Rule::requiredIf($request->boolean('invoice_requested')), 'nullable', 'string', 'size:2', 'alpha'],
            'fiscal_code' => [
                Rule::requiredIf($request->boolean('invoice_requested') && $request->input('billing_customer_type') === 'individual'),
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if ($request->boolean('invoice_requested')
                        && $request->input('billing_customer_type') === 'individual'
                        && ! ItalianFiscalData::isValidFiscalCode((string) $value)) {
                        $fail(__('messages.subscribe.invalid_fiscal_code'));
                    }
                },
            ],
            'company_name' => [Rule::requiredIf($request->boolean('invoice_requested') && $request->input('billing_customer_type') === 'legal_entity'), 'nullable', 'string', 'max:255'],
            'vat_number' => [
                Rule::requiredIf($request->boolean('invoice_requested') && $request->input('billing_customer_type') === 'legal_entity'),
                'nullable',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if ($request->boolean('invoice_requested')
                        && $request->input('billing_customer_type') === 'legal_entity'
                        && ! ItalianFiscalData::isValidVatNumber((string) $value, (string) $request->input('billing_country'))) {
                        $fail(__('messages.subscribe.invalid_vat_number'));
                    }
                },
            ],
            'sdi_code' => ['nullable', 'string', 'size:7', 'regex:/^[A-Z0-9]{7}$/i'],
            'pec' => [Rule::requiredIf($request->boolean('invoice_requested') && $request->input('billing_customer_type') === 'legal_entity'), 'nullable', 'email:rfc', 'max:255'],
        ]);

        $waitlistEntry = DB::table('waitlist_entries')
            ->where('id', $request->session()->get('waitlist_verified_entry_id'))
            ->whereNotNull('email_verified_at')
            ->first();

        if (! $waitlistEntry || ! hash_equals(strtolower((string) $waitlistEntry->email), $email)) {
            return response()->json(['error' => __('messages.messages.checkout_unauthorized')], 403);
        }

        try {
            $package = $catalog->packageForPlan($plan, fresh: true);

            if (! $package || ($package['free'] ?? true) !== false) {
                return response()->json(['error' => __('messages.subscribe.package_unavailable')], 422);
            }

            if (($package['available'] ?? null) === 0) {
                return response()->json(['error' => __('messages.messages.pass_sold_out', ['plan' => $package['name'] ?? $plan])], 422);
            }

            $identity = $identities->resolve($validated['firebase_token'], [
                'first_name' => trim($validated['first_name']),
                'last_name' => trim($validated['last_name']),
                'email' => $email,
                'date_of_birth' => $validated['birth_date'],
                'gender' => $validated['gender'],
            ]);

            $eligibility = $wayout->internalPost('/api/v1/internal/purchase-eligibility', [
                'user' => ['id' => $identity['id']],
                'promo_package_code' => $package['code'],
            ]);

            if (data_get($eligibility, 'data.allowed') !== true) {
                return response()->json([
                    'error' => data_get($eligibility, 'data.message') ?: __('messages.subscribe.package_unavailable'),
                    'reason' => data_get($eligibility, 'data.reason'),
                ], 422);
            }

            $customer = $this->customerData($request, $validated);
            $purchaseId = $this->reservePurchase(
                $email,
                $plan,
                $package,
                $identity['id'],
                (string) $waitlistEntry->id,
                $customer,
                $catalog,
            );
            $purchase = DB::table('purchases')->where('id', $purchaseId)->first();
            $successUrl = URL::temporarySignedRoute('checkout.success', now()->addDay(), ['purchase' => $purchaseId]);
            $cancelUrl = URL::temporarySignedRoute('checkout.cancel', now()->addDay(), ['purchase' => $purchaseId]);
            $checkout = $wayout->internalPost('/api/v1/internal/checkout-sessions', [
                'user' => ['id' => $identity['id']],
                'promo_package_code' => $package['code'],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'external_reference' => $purchase->order_reference,
            ]);

            if (data_get($checkout, 'data.allowed') !== true || ! is_string(data_get($checkout, 'data.session.url'))) {
                DB::table('purchases')->where('id', $purchaseId)->update(['status' => 'failed', 'updated_at' => now()]);

                return response()->json([
                    'error' => data_get($checkout, 'data.message') ?: __('messages.messages.checkout_unavailable'),
                    'reason' => data_get($checkout, 'data.reason'),
                ], 422);
            }

            DB::table('purchases')->where('id', $purchaseId)->update([
                'stripe_session_id' => data_get($checkout, 'data.session.id'),
                'updated_at' => now(),
            ]);

            $request->session()->put('checkout_purchase_id', $purchaseId);
            $request->session()->put('checkout_plan', $plan);
            $request->session()->put('waitlist_profile', [
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'birth_date' => $customer['birth_date'],
                'gender' => $validated['gender'],
            ]);

            return response()->json([
                'redirectUrl' => data_get($checkout, 'data.session.url'),
                'purchaseId' => $purchaseId,
            ]);
        } catch (WayoutApiException $exception) {
            Log::warning('Wayout checkout request rejected.', [
                'status' => $exception->status,
                'code' => $exception->apiCode,
            ]);

            return response()->json([
                'error' => $exception->getMessage(),
                'code' => $exception->apiCode,
            ], $this->browserStatus($exception));
        } catch (QueryException $exception) {
            Log::error('Wayout checkout reservation failed.', PrivacySafeLogContext::exception($exception));

            return response()->json(['error' => __('messages.messages.purchase_register_error')], 500);
        } catch (Throwable $exception) {
            Log::error('Wayout checkout failed.', PrivacySafeLogContext::exception($exception));

            return response()->json(['error' => __('messages.messages.checkout_unavailable')], 502);
        }
    }

    public function cancel(Request $request, string $purchase)
    {
        DB::table('purchases')
            ->where('id', $purchase)
            ->where('status', 'pending')
            ->update(['status' => 'canceled', 'updated_at' => now()]);

        $request->session()->flash('subscribe_entry_allowed', true);

        return redirect()->route('subscribe')->with('checkout_error', __('messages.subscribe.checkout_canceled'));
    }

    /** @param array<string, mixed> $validated */
    private function customerData(Request $request, array $validated): array
    {
        $invoiceRequested = $request->boolean('invoice_requested');
        $customerType = $invoiceRequested ? $validated['billing_customer_type'] : null;

        return [
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'birth_date' => $validated['birth_date'],
            'invoice_requested' => $invoiceRequested,
            'fiscal_code' => $customerType === 'individual' ? $this->upperOrNull($validated['fiscal_code'] ?? null) : null,
            'billing_customer_type' => $customerType,
            'billing_address' => $invoiceRequested ? trim($validated['billing_address']) : null,
            'billing_postal_code' => $invoiceRequested ? strtoupper(trim($validated['billing_postal_code'])) : null,
            'billing_city' => $invoiceRequested ? trim($validated['billing_city']) : null,
            'billing_province' => $invoiceRequested ? strtoupper(trim($validated['billing_province'])) : null,
            'billing_country' => $invoiceRequested ? strtoupper($validated['billing_country']) : null,
            'company_name' => $customerType === 'legal_entity' ? trim($validated['company_name']) : null,
            'vat_number' => $customerType === 'legal_entity'
                ? ItalianFiscalData::normalizeVatNumber($validated['vat_number'], $validated['billing_country'])
                : null,
            'sdi_code' => $customerType === 'legal_entity' ? $this->upperOrNull($validated['sdi_code'] ?? null) : null,
            'pec' => $customerType === 'legal_entity' ? strtolower(trim($validated['pec'])) : null,
            'electronic_invoice_status' => $invoiceRequested ? 'pending' : 'not_requested',
        ];
    }

    private function reservePurchase(
        string $email,
        string $plan,
        array $package,
        string $wayoutUserId,
        string $waitlistEntryId,
        array $customer,
        FounderPromoCatalog $catalog,
    ): string {
        return DB::transaction(function () use ($email, $plan, $package, $wayoutUserId, $waitlistEntryId, $customer, $catalog): string {
            DB::table('purchases')
                ->where('status', 'pending')
                ->where('created_at', '<', now()->subDay())
                ->update(['status' => 'expired', 'updated_at' => now()]);

            $existing = DB::table('purchases')
                ->where('wayout_user_id', $wayoutUserId)
                ->where('promo_package_code', $package['code'])
                ->where('status', 'pending')
                ->latest('created_at')
                ->lockForUpdate()
                ->first();
            $purchaseId = $existing?->id ?: DatabaseUuid::new();
            $data = [
                'waitlist_entry_id' => $waitlistEntryId,
                'wayout_user_id' => $wayoutUserId,
                'promo_package_code' => $package['code'],
                'email' => $email,
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'birth_date' => $customer['birth_date'],
                'plan' => $plan,
                'amount' => $catalog->amountInCents($package),
                'currency' => strtolower((string) ($package['currency'] ?? 'EUR')),
                'status' => 'pending',
                'invoice_requested' => $customer['invoice_requested'],
                'fiscal_code' => $customer['fiscal_code'],
                'billing_customer_type' => $customer['billing_customer_type'],
                'billing_address' => $customer['billing_address'],
                'billing_postal_code' => $customer['billing_postal_code'],
                'billing_city' => $customer['billing_city'],
                'billing_province' => $customer['billing_province'],
                'billing_country' => $customer['billing_country'],
                'company_name' => $customer['company_name'],
                'vat_number' => $customer['vat_number'],
                'sdi_code' => $customer['sdi_code'],
                'pec' => $customer['pec'],
                'electronic_invoice_status' => $customer['electronic_invoice_status'],
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('purchases')->where('id', $purchaseId)->update($data);
            } else {
                DB::table('purchases')->insert([
                    'id' => $purchaseId,
                    'order_reference' => $this->orderReference($purchaseId),
                    ...$data,
                    'created_at' => now(),
                ]);
            }

            return $purchaseId;
        });
    }

    private function orderReference(string $purchaseId): string
    {
        return 'WYO-'.strtoupper(substr(str_replace('-', '', $purchaseId), 0, 12));
    }

    private function upperOrNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : strtoupper($value);
    }

    private function browserStatus(WayoutApiException $exception): int
    {
        return match (true) {
            $exception->status === 401 => 502,
            $exception->status >= 400 && $exception->status < 500 => 422,
            default => 502,
        };
    }
}
