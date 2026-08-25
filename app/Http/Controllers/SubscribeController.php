<?php

namespace App\Http\Controllers;

use App\Mail\PurchaseConfirmationMail;
use App\Support\ConsentAuditService;
use App\Support\DatabaseUuid;
use App\Support\FounderAvailability;
use App\Support\ItalianFiscalData;
use App\Support\PrivacySafeLogContext;
use App\Support\PurchasePdfService;
use App\Support\QontoInvoiceService;
use App\Support\TransactionalEmailSender;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class SubscribeController extends Controller
{
    private const RESERVATION_MINUTES = 15;

    public function show(Request $request)
    {
        if (! $request->session()->pull('subscribe_entry_allowed')) {
            return redirect()->route('home');
        }

        return view('pages.subscribe');
    }

    public function access(Request $request)
    {
        $entryId = $request->session()->get('waitlist_verified_entry_id');
        $waitlistEntry = $entryId
            ? DB::table('waitlist_entries')->where('id', $entryId)->whereNotNull('email_verified_at')->first()
            : null;

        if (! $waitlistEntry) {
            return redirect()->route('home')->with('waitlist_error', __('messages.messages.checkout_unauthorized'));
        }

        $request->session()->put('waitlist_offer_access', true);
        $request->session()->put('waitlist_email', $waitlistEntry->email);
        $request->session()->flash('subscribe_entry_allowed', true);

        return redirect()->route('subscribe');
    }

    public function success(Request $request, ConsentAuditService $audit)
    {
        $sessionId = $request->query('session_id');

        if (is_string($sessionId) && str_starts_with($sessionId, 'cs_')) {
            $this->registerSuccessfulStripeCheckout($sessionId, $request, $audit);
        }

        return view('pages.checkout-success');
    }

    public function resendPurchaseConfirmation(Request $request, TransactionalEmailSender $emailSender)
    {
        $email = $request->session()->get('purchase_confirmation_email')
            ?? $request->session()->get('waitlist_email');

        if (! $email) {
            return $this->purchaseConfirmationResponse(
                $request,
                __('messages.messages.purchase_email_missing'),
                false,
                status: 422,
            );
        }

        $claimedAt = now();

        try {
            $claim = DB::transaction(function () use ($email, $emailSender, $claimedAt) {
                $purchase = DB::table('purchases')
                    ->where('email', $email)
                    ->where('status', 'succeeded')
                    ->latest('created_at')
                    ->lockForUpdate()
                    ->first();

                if (! $purchase) {
                    return ['status' => 'not_found'];
                }

                if (! $emailSender->enabled()) {
                    return ['status' => 'disabled', 'purchase' => $purchase];
                }

                $cooldown = max(1, (int) config('email.resend_cooldown_seconds', 300));

                if ($purchase->confirmation_email_sent_at) {
                    $availableAt = Carbon::parse($purchase->confirmation_email_sent_at)
                        ->addSeconds($cooldown);

                    if (now()->lt($availableAt)) {
                        return [
                            'status' => 'cooldown',
                            'purchase' => $purchase,
                            'retry_after' => max(1, $availableAt->timestamp - now()->timestamp),
                        ];
                    }
                }

                DB::table('purchases')
                    ->where('id', $purchase->id)
                    ->update(['confirmation_email_sent_at' => $claimedAt]);

                return [
                    'status' => 'claimed',
                    'purchase' => $purchase,
                    'previous_sent_at' => $purchase->confirmation_email_sent_at,
                ];
            });
        } catch (QueryException $exception) {
            Log::error('Purchase confirmation lookup failed.', [
                'email_hash' => PrivacySafeLogContext::fingerprint($email),
                ...PrivacySafeLogContext::exception($exception),
            ]);

            return $this->purchaseConfirmationResponse(
                $request,
                __('messages.messages.purchase_resend_error'),
                false,
                $email,
                status: 500,
            );
        }

        if ($claim['status'] === 'not_found') {
            return $this->purchaseConfirmationResponse(
                $request,
                __('messages.messages.purchase_not_found'),
                false,
                $email,
                status: 404,
            );
        }

        $purchase = $claim['purchase'];

        if ($claim['status'] === 'disabled') {
            return $this->purchaseConfirmationResponse(
                $request,
                __('messages.messages.email_sending_disabled'),
                false,
                $email,
                $purchase,
                503,
            );
        }

        if ($claim['status'] === 'cooldown') {
            return $this->purchaseConfirmationResponse(
                $request,
                __('messages.messages.purchase_resend_throttled', [
                    'seconds' => $claim['retry_after'],
                ]),
                false,
                $email,
                $purchase,
                429,
            );
        }

        try {
            $emailSender->send($email, $this->purchaseConfirmationMailable($purchase));
        } catch (Throwable $exception) {
            DB::table('purchases')
                ->where('id', $purchase->id)
                ->where('confirmation_email_sent_at', $claimedAt)
                ->update(['confirmation_email_sent_at' => $claim['previous_sent_at']]);

            Log::error('Purchase confirmation resend failed.', [
                'email_hash' => PrivacySafeLogContext::fingerprint($email),
                'purchase_id' => $purchase->id,
                ...PrivacySafeLogContext::exception($exception),
            ]);

            return $this->purchaseConfirmationResponse(
                $request,
                __('messages.messages.purchase_resend_error'),
                false,
                $email,
                $purchase,
                502,
            );
        }

        return $this->purchaseConfirmationResponse(
            $request,
            __('messages.messages.purchase_resend_success'),
            true,
            $email,
            $purchase,
        );
    }

    public function checkout(
        Request $request,
        FounderAvailability $availability,
        TransactionalEmailSender $emailSender,
        ConsentAuditService $audit,
        QontoInvoiceService $qontoInvoices,
    ) {
        if (! $request->session()->get('waitlist_offer_access')) {
            return response()->json([
                'error' => __('messages.messages.checkout_unauthorized'),
            ], 403);
        }

        $email = $request->session()->get('waitlist_email');

        if (! $email) {
            return response()->json([
                'error' => __('messages.messages.purchase_email_missing'),
            ], 403);
        }

        $plan = $request->input('plan');

        if (! in_array($plan, ['join', 'creator'], true)) {
            return response()->json([
                'error' => __('messages.messages.invalid_pass'),
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'min:2', 'max:120', "regex:/^[\\pL\\pM][\\pL\\pM .'-]*$/u"],
            'last_name' => ['required', 'string', 'min:2', 'max:120', "regex:/^[\\pL\\pM][\\pL\\pM .'-]*$/u"],
            'birth_date' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'invoice_requested' => ['sometimes', 'boolean'],
            'purchase_terms_accepted' => ['required', 'accepted'],
            'billing_customer_type' => [Rule::requiredIf($request->boolean('invoice_requested')), 'nullable', Rule::in(['individual', 'legal_entity'])],
            'billing_address' => [Rule::requiredIf($request->boolean('invoice_requested')), 'nullable', 'string', 'max:255'],
            'billing_postal_code' => [
                Rule::requiredIf($request->boolean('invoice_requested')),
                'nullable',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if ($request->boolean('invoice_requested')
                        && strtoupper((string) $request->input('billing_country')) === 'IT'
                        && preg_match('/^\d{5}$/', (string) $value) !== 1) {
                        $fail(__('messages.subscribe.invalid_postal_code'));
                    }
                },
            ],
            'billing_city' => [Rule::requiredIf($request->boolean('invoice_requested')), 'nullable', 'string', 'max:120'],
            'billing_province' => [
                Rule::requiredIf($request->boolean('invoice_requested')),
                'nullable',
                'string',
                'max:8',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if ($request->boolean('invoice_requested')
                        && strtoupper((string) $request->input('billing_country')) === 'IT'
                        && preg_match('/^[A-Z]{2}$/i', (string) $value) !== 1) {
                        $fail(__('messages.subscribe.invalid_province'));
                    }
                },
            ],
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
        ], [
            'required' => __('messages.subscribe.field_required', ['field' => ':attribute']),
            'accepted' => __('messages.subscribe.checkout_validation_error'),
            'before_or_equal' => __('messages.subscribe.birth_date_note'),
            'sdi_code.size' => __('messages.subscribe.invalid_sdi_code'),
            'sdi_code.regex' => __('messages.subscribe.invalid_sdi_code'),
        ], [
            'first_name' => __('messages.subscribe.first_name'),
            'last_name' => __('messages.subscribe.last_name'),
            'birth_date' => __('messages.subscribe.birth_date'),
            'purchase_terms_accepted' => __('messages.subscribe.purchase_terms'),
            'billing_customer_type' => __('messages.subscribe.invoice_holder_type'),
            'billing_address' => __('messages.subscribe.billing_address'),
            'billing_postal_code' => __('messages.subscribe.postal_code'),
            'billing_city' => __('messages.subscribe.city'),
            'billing_province' => __('messages.subscribe.province'),
            'billing_country' => __('messages.subscribe.country'),
            'fiscal_code' => __('messages.subscribe.fiscal_code'),
            'company_name' => __('messages.subscribe.company_name'),
            'vat_number' => __('messages.subscribe.vat_number'),
            'sdi_code' => __('messages.subscribe.sdi_code'),
            'pec' => __('messages.subscribe.pec'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validatedCustomer = $validator->validated();

        $invoiceRequested = $request->boolean('invoice_requested');
        $billingCustomerType = $invoiceRequested ? $validatedCustomer['billing_customer_type'] : null;
        $customer = [
            'first_name' => $validatedCustomer['first_name'],
            'last_name' => $validatedCustomer['last_name'],
            'birth_date' => $validatedCustomer['birth_date'],
            'invoice_requested' => $invoiceRequested,
            'billing_customer_type' => $billingCustomerType,
            'billing_address' => $invoiceRequested ? trim($validatedCustomer['billing_address']) : null,
            'billing_postal_code' => $invoiceRequested ? strtoupper(trim($validatedCustomer['billing_postal_code'])) : null,
            'billing_city' => $invoiceRequested ? trim($validatedCustomer['billing_city']) : null,
            'billing_province' => $invoiceRequested ? strtoupper(trim($validatedCustomer['billing_province'])) : null,
            'billing_country' => $invoiceRequested ? strtoupper($validatedCustomer['billing_country']) : null,
            'fiscal_code' => $billingCustomerType === 'individual' ? $this->normalizeFiscalCode($validatedCustomer['fiscal_code'] ?? null) : null,
            'company_name' => $billingCustomerType === 'legal_entity' ? trim($validatedCustomer['company_name']) : null,
            'vat_number' => $billingCustomerType === 'legal_entity'
                ? ItalianFiscalData::normalizeVatNumber($validatedCustomer['vat_number'], $validatedCustomer['billing_country'])
                : null,
            'sdi_code' => $billingCustomerType === 'legal_entity' ? $this->normalizeFiscalCode($validatedCustomer['sdi_code'] ?? null) : null,
            'pec' => $billingCustomerType === 'legal_entity' ? strtolower(trim($validatedCustomer['pec'])) : null,
            'electronic_invoice_status' => $invoiceRequested ? 'pending' : 'not_requested',
        ];

        $request->session()->put('waitlist_profile', [
            'first_name' => $customer['first_name'],
            'last_name' => $customer['last_name'],
            'birth_date' => $customer['birth_date'],
        ]);
        $waitlistEntryId = $request->session()->get('waitlist_verified_entry_id');
        $waitlistEntry = $waitlistEntryId
            ? DB::table('waitlist_entries')->where('id', $waitlistEntryId)->whereNotNull('email_verified_at')->first()
            : DB::table('waitlist_entries')->whereRaw('LOWER(email) = ?', [$email])->whereNotNull('email_verified_at')->first();

        if (! $waitlistEntry || ! hash_equals($waitlistEntry->email, $email)) {
            return response()->json(['error' => __('messages.messages.checkout_unauthorized')], 403);
        }

        $waitlistEntryId = $waitlistEntry->id;

        // This shortcut exists only to keep feature tests fast. Client input can
        // never bypass Stripe in a web/production runtime.
        $directCheckout = app()->runningUnitTests()
            && config('services.stripe.direct_checkout_enabled_for_tests')
            && $request->boolean('direct_checkout', false);

        $planConfig = match ($plan) {
            'creator' => [
                'name' => $this->planName('creator'),
                'description' => 'Founder 12M Creator Pass - 12 months from go-live',
                'unit_amount' => '5900',
            ],
            default => [
                'name' => $this->planName('join'),
                'description' => 'Founder Join 12M Pass - 12 months from go-live',
                'unit_amount' => '2900',
            ],
        };

        if ($directCheckout) {
            try {
                $soldOut = false;
                $purchase = null;

                DB::transaction(function () use ($request, $email, $plan, $planConfig, $availability, $customer, $waitlistEntryId, $audit, &$purchase, &$soldOut) {
                    $capacity = (int) (DB::table('founder_settings')
                        ->where('key', $availability->capacityKeyForPlan($plan))
                        ->lockForUpdate()
                        ->value('value') ?? config('founder.default_capacities.'.$availability->capacityKeyForPlan($plan)));

                    $soldCount = $this->reservedPassCount($plan);

                    if ($capacity <= 0 || $soldCount >= $capacity) {
                        $soldOut = true;

                        return;
                    }

                    $purchase = DatabaseUuid::new();
                    DB::table('purchases')->insert([
                        'id' => $purchase,
                        'waitlist_entry_id' => $waitlistEntryId,
                        'order_reference' => $this->orderReference($purchase),
                        'email' => $email,
                        'first_name' => $customer['first_name'],
                        'last_name' => $customer['last_name'],
                        'birth_date' => $customer['birth_date'],
                        'plan' => $plan,
                        'amount' => (int) $planConfig['unit_amount'],
                        'currency' => 'eur',
                        'stripe_session_id' => 'direct_'.uniqid(),
                        'status' => 'succeeded',
                        'invoice_requested' => $customer['invoice_requested'],
                        'fiscal_code' => $customer['fiscal_code'],
                        ...$this->billingPurchaseData($customer),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->recordSuccessfulPurchaseConsent(
                        $request,
                        $audit,
                        $purchase,
                        $email,
                        'direct_checkout',
                        $plan,
                        $waitlistEntryId,
                    );
                });
            } catch (QueryException $exception) {
                Log::error('Direct checkout purchase insert failed.', [
                    'plan' => $plan,
                    ...PrivacySafeLogContext::exception($exception),
                ]);

                return response()->json([
                    'error' => __('messages.messages.purchase_register_error'),
                ], 500);
            }

            if ($soldOut) {
                return response()->json([
                    'error' => __('messages.messages.pass_sold_out', ['plan' => $this->planName($plan)]),
                ], 422);
            }

            $request->session()->put('checkout_plan', $plan);

            $qontoInvoices->sendForPurchase($purchase);
            $this->sendAutomaticPurchaseConfirmation($purchase, $emailSender);

            return response()->json([
                'purchaseId' => $purchase,
                'completed' => true,
                'redirectUrl' => route('checkout.success'),
            ]);
        }

        $secret = config('services.stripe.secret');

        if (! $secret) {
            return response()->json([
                'error' => __('messages.messages.stripe_secret_missing'),
            ], 500);
        }

        try {
            $soldOut = false;
            $reservationId = null;

            DB::transaction(function () use ($email, $plan, $planConfig, $availability, $customer, $waitlistEntryId, &$reservationId, &$soldOut) {
                $capacity = (int) (DB::table('founder_settings')
                    ->where('key', $availability->capacityKeyForPlan($plan))
                    ->lockForUpdate()
                    ->value('value') ?? config('founder.default_capacities.'.$availability->capacityKeyForPlan($plan)));

                DB::table('purchases')
                    ->where('status', 'pending')
                    ->where('created_at', '<', now()->subMinutes(self::RESERVATION_MINUTES))
                    ->update([
                        'status' => 'expired',
                        'updated_at' => now(),
                    ]);

                $existingReservation = DB::table('purchases')
                    ->where('email', $email)
                    ->where('plan', $plan)
                    ->where('status', 'pending')
                    ->latest('created_at')
                    ->lockForUpdate()
                    ->first();

                if ($existingReservation) {
                    DB::table('purchases')
                        ->where('email', $email)
                        ->where('plan', $plan)
                        ->where('status', 'pending')
                        ->where('id', '!=', $existingReservation->id)
                        ->update([
                            'status' => 'expired',
                            'updated_at' => now(),
                        ]);
                }

                if ($capacity <= 0 || $this->reservedPassCount($plan, $existingReservation?->id) >= $capacity) {
                    $soldOut = true;

                    return;
                }

                $reservationData = [
                    'waitlist_entry_id' => $waitlistEntryId,
                    'email' => $email,
                    'first_name' => $customer['first_name'],
                    'last_name' => $customer['last_name'],
                    'birth_date' => $customer['birth_date'],
                    'plan' => $plan,
                    'amount' => (int) $planConfig['unit_amount'],
                    'currency' => 'eur',
                    'stripe_session_id' => null,
                    'status' => 'pending',
                    'invoice_requested' => $customer['invoice_requested'],
                    'fiscal_code' => $customer['fiscal_code'],
                    ...$this->billingPurchaseData($customer),
                    'updated_at' => now(),
                ];

                if ($existingReservation) {
                    DB::table('purchases')
                        ->where('id', $existingReservation->id)
                        ->update($reservationData);

                    $reservationId = $existingReservation->id;
                } else {
                    $reservationId = DatabaseUuid::new();
                    DB::table('purchases')->insert([
                        'id' => $reservationId,
                        'order_reference' => $this->orderReference($reservationId),
                        ...$reservationData,
                        'created_at' => now(),
                    ]);
                }
            });
        } catch (QueryException $exception) {
            Log::error('Stripe checkout reservation failed.', [
                'plan' => $plan,
                ...PrivacySafeLogContext::exception($exception),
            ]);

            return response()->json([
                'error' => __('messages.messages.purchase_register_error'),
            ], 500);
        }

        if ($soldOut) {
            return response()->json([
                'error' => __('messages.messages.pass_sold_out', ['plan' => $this->planName($plan)]),
            ], 422);
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 200)
                ->withToken($secret)
                ->asForm()
                ->post('https://api.stripe.com/v1/checkout/sessions', [
                    'payment_method_types[]' => 'card',
                    'mode' => 'payment',
                    'success_url' => route('checkout.success', [], true).'?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('subscribe', [], true),
                    'line_items[0][price_data][currency]' => 'eur',
                    'line_items[0][price_data][product_data][name]' => $planConfig['name'],
                    'line_items[0][price_data][product_data][description]' => $planConfig['description'],
                    'line_items[0][price_data][unit_amount]' => $planConfig['unit_amount'],
                    'line_items[0][quantity]' => '1',
                    'customer_email' => $email,
                    'metadata[email]' => $email,
                    'metadata[first_name]' => $customer['first_name'],
                    'metadata[last_name]' => $customer['last_name'],
                    'metadata[birth_date]' => $customer['birth_date'],
                    'metadata[waitlist_entry_id]' => $waitlistEntryId,
                    'metadata[plan]' => $plan,
                    'metadata[purchase_id]' => (string) $reservationId,
                    'metadata[invoice_requested]' => $customer['invoice_requested'] ? 'true' : 'false',
                    'metadata[fiscal_code]' => $customer['fiscal_code'] ?? '',
                    'metadata[billing_customer_type]' => $customer['billing_customer_type'] ?? '',
                    'metadata[billing_address]' => $customer['billing_address'] ?? '',
                    'metadata[billing_postal_code]' => $customer['billing_postal_code'] ?? '',
                    'metadata[billing_city]' => $customer['billing_city'] ?? '',
                    'metadata[billing_province]' => $customer['billing_province'] ?? '',
                    'metadata[billing_country]' => $customer['billing_country'] ?? '',
                    'metadata[company_name]' => $customer['company_name'] ?? '',
                    'metadata[vat_number]' => $customer['vat_number'] ?? '',
                    'metadata[sdi_code]' => $customer['sdi_code'] ?? '',
                    'metadata[pec]' => $customer['pec'] ?? '',
                ]);
        } catch (Throwable $exception) {
            $this->markReservationFailed($reservationId);

            Log::error('Stripe checkout request failed.', [
                'plan' => $plan,
                ...PrivacySafeLogContext::exception($exception),
            ]);

            return response()->json([
                'error' => __('messages.messages.checkout_unavailable'),
            ], 502);
        }

        if ($response->failed()) {
            $this->markReservationFailed($reservationId);

            Log::warning('Stripe checkout returned an error.', [
                'plan' => $plan,
                'status' => $response->status(),
            ]);

            return response()->json([
                'error' => __('messages.messages.checkout_unavailable'),
            ], 502);
        }

        if (! $response->json('id')) {
            $this->markReservationFailed($reservationId);

            Log::warning('Stripe checkout response did not include a session id.', [
                'plan' => $plan,
                'status' => $response->status(),
            ]);

            return response()->json([
                'error' => __('messages.messages.checkout_unavailable'),
            ], 502);
        }

        DB::table('purchases')
            ->where('id', $reservationId)
            ->where('status', 'pending')
            ->update([
                'stripe_session_id' => $response->json('id'),
                'updated_at' => now(),
            ]);

        return response()->json([
            'sessionId' => $response->json('id'),
        ]);
    }

    public function stripeWebhook(Request $request, ConsentAuditService $audit)
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('Stripe-Signature', '');
        $secret = (string) config('services.stripe.webhook_secret', '');

        if ($secret === '') {
            Log::error('Stripe webhook is not configured.');

            return response()->json(['received' => false], 503);
        }

        if (! $this->validStripeWebhookSignature($payload, $signature, $secret)) {
            Log::warning('Stripe webhook signature verification failed.');

            return response()->json(['received' => false], 400);
        }

        try {
            $event = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return response()->json(['received' => false], 400);
        }

        $type = $event['type'] ?? null;

        if (! in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            return response()->json(['received' => true]);
        }

        $sessionId = $event['data']['object']['id'] ?? null;

        if (! is_string($sessionId) || ! str_starts_with($sessionId, 'cs_')) {
            return response()->json(['received' => false], 400);
        }

        if (! $this->registerSuccessfulStripeCheckout($sessionId, $request, $audit)) {
            // A 5xx response asks Stripe to retry transient failures.
            return response()->json(['received' => false], 500);
        }

        return response()->json(['received' => true]);
    }

    private function validStripeWebhookSignature(
        string $payload,
        string $signatureHeader,
        string $secret,
        int $toleranceSeconds = 300,
    ): bool {
        $parts = collect(explode(',', $signatureHeader))
            ->mapWithKeys(function (string $part): array {
                [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);

                return is_string($key) && is_string($value) ? [$key => $value] : [];
            });
        $timestamp = $parts->get('t');
        $signatures = collect(explode(',', $signatureHeader))
            ->filter(fn (string $part): bool => str_starts_with(trim($part), 'v1='))
            ->map(fn (string $part): string => substr(trim($part), 3));

        if (! is_string($timestamp) || ! ctype_digit($timestamp)
            || abs(time() - (int) $timestamp) > $toleranceSeconds
            || $signatures->isEmpty()) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        return $signatures->contains(fn (string $provided): bool => hash_equals($expected, $provided));
    }

    private function registerSuccessfulStripeCheckout(
        string $sessionId,
        Request $request,
        ConsentAuditService $audit,
    ): bool {
        $secret = config('services.stripe.secret');

        if (! $secret) {
            Log::warning('Stripe success callback skipped because STRIPE_SECRET is missing.', [
                'stripe_session_hash' => PrivacySafeLogContext::fingerprint($sessionId),
            ]);

            return false;
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 200)
                ->withToken($secret)
                ->get('https://api.stripe.com/v1/checkout/sessions/'.$sessionId);
        } catch (Throwable $exception) {
            Log::error('Stripe checkout session lookup failed.', [
                'stripe_session_hash' => PrivacySafeLogContext::fingerprint($sessionId),
                ...PrivacySafeLogContext::exception($exception),
            ]);

            return false;
        }

        if ($response->failed()) {
            Log::warning('Stripe checkout session lookup returned an error.', [
                'stripe_session_hash' => PrivacySafeLogContext::fingerprint($sessionId),
                'status' => $response->status(),
            ]);

            return false;
        }

        if ($response->json('payment_status') !== 'paid') {
            Log::info('Stripe checkout session was not paid yet.', [
                'stripe_session_hash' => PrivacySafeLogContext::fingerprint($sessionId),
                'payment_status' => $response->json('payment_status'),
            ]);

            return true;
        }

        $metadata = $response->json('metadata') ?? [];
        $plan = $metadata['plan'] ?? null;

        if (! in_array($plan, ['join', 'creator'], true)) {
            Log::warning('Stripe checkout session missing a valid plan.', [
                'stripe_session_hash' => PrivacySafeLogContext::fingerprint($sessionId),
            ]);

            return true;
        }

        $email = $metadata['email'] ?? $response->json('customer_details.email') ?? $response->json('customer_email');

        if (! $email) {
            Log::warning('Stripe checkout session missing customer email.', [
                'stripe_session_hash' => PrivacySafeLogContext::fingerprint($sessionId),
            ]);

            return true;
        }

        $purchaseData = [
            'waitlist_entry_id' => ($metadata['waitlist_entry_id'] ?? '') ?: DB::table('waitlist_entries')
                ->whereRaw('LOWER(email) = ?', [strtolower($email)])
                ->value('id'),
            'email' => strtolower($email),
            'first_name' => $metadata['first_name'] ?? null,
            'last_name' => $metadata['last_name'] ?? null,
            'birth_date' => $metadata['birth_date'] ?? null,
            'plan' => $plan,
            'amount' => (int) ($response->json('amount_total') ?? ($plan === 'creator' ? 5900 : 2900)),
            'currency' => strtolower($response->json('currency') ?? 'eur'),
            'stripe_session_id' => $sessionId,
            'status' => 'succeeded',
            'invoice_requested' => ($metadata['invoice_requested'] ?? 'false') === 'true',
            'fiscal_code' => $this->normalizeFiscalCode($metadata['fiscal_code'] ?? null),
            'billing_customer_type' => ($metadata['billing_customer_type'] ?? '') ?: null,
            'billing_address' => ($metadata['billing_address'] ?? '') ?: null,
            'billing_postal_code' => ($metadata['billing_postal_code'] ?? '') ?: null,
            'billing_city' => ($metadata['billing_city'] ?? '') ?: null,
            'billing_province' => ($metadata['billing_province'] ?? '') ?: null,
            'billing_country' => ($metadata['billing_country'] ?? '') ?: null,
            'company_name' => ($metadata['company_name'] ?? '') ?: null,
            'vat_number' => ($metadata['vat_number'] ?? '') ?: null,
            'sdi_code' => ($metadata['sdi_code'] ?? '') ?: null,
            'pec' => ($metadata['pec'] ?? '') ?: null,
            'electronic_invoice_status' => ($metadata['invoice_requested'] ?? 'false') === 'true' ? 'pending' : 'not_requested',
            'updated_at' => now(),
        ];

        try {
            $registered = false;
            $purchaseToConfirm = null;
            $purchaseToInvoice = null;
            $reservationId = filled($metadata['purchase_id'] ?? null) ? (string) $metadata['purchase_id'] : null;

            DB::transaction(function () use ($request, $audit, $plan, $sessionId, $reservationId, $purchaseData, &$registered, &$purchaseToConfirm, &$purchaseToInvoice) {
                $capacityKey = $this->capacityKeyForPlan($plan);
                $capacity = (int) (DB::table('founder_settings')
                    ->where('key', $capacityKey)
                    ->lockForUpdate()
                    ->value('value') ?? config('founder.default_capacities.'.$capacityKey));

                $existingPurchase = $reservationId
                    ? DB::table('purchases')->where('id', $reservationId)->lockForUpdate()->first()
                    : null;

                if (! $existingPurchase) {
                    $existingPurchase = DB::table('purchases')
                        ->where('stripe_session_id', $sessionId)
                        ->lockForUpdate()
                        ->first();
                }

                if ($existingPurchase?->status === 'succeeded') {
                    $registered = true;
                    $purchaseToConfirm = $existingPurchase->confirmation_email_sent_at ? null : $existingPurchase->id;
                    $purchaseToInvoice = $existingPurchase->id;

                    $this->recordSuccessfulPurchaseConsent(
                        $request,
                        $audit,
                        $existingPurchase->id,
                        $purchaseData['email'],
                        'stripe_payment_success',
                        $plan,
                    );

                    return;
                }

                $succeededCount = DB::table('purchases')
                    ->where('status', 'succeeded')
                    ->where('plan', $plan)
                    ->when($existingPurchase, fn ($query) => $query->where('id', '!=', $existingPurchase->id))
                    ->count();

                $status = ($capacity > 0 && $succeededCount < $capacity) ? 'succeeded' : 'overbooked';
                $registered = $status === 'succeeded';
                $data = [
                    ...$purchaseData,
                    'status' => $status,
                ];

                if ($existingPurchase) {
                    DB::table('purchases')
                        ->where('id', $existingPurchase->id)
                        ->update($data);
                    $purchaseToConfirm = $registered ? $existingPurchase->id : null;
                    $purchaseToInvoice = $registered ? $existingPurchase->id : null;
                } else {
                    $purchaseToConfirm = DatabaseUuid::new();
                    DB::table('purchases')->insert([
                        'id' => $purchaseToConfirm,
                        'order_reference' => $this->orderReference($purchaseToConfirm),
                        ...$data,
                        'created_at' => now(),
                    ]);

                    if (! $registered) {
                        $purchaseToConfirm = null;
                    } else {
                        $purchaseToInvoice = $purchaseToConfirm;
                    }
                }

                if ($registered && $purchaseToConfirm) {
                    $this->recordSuccessfulPurchaseConsent(
                        $request,
                        $audit,
                        $purchaseToConfirm,
                        $purchaseData['email'],
                        'stripe_payment_success',
                        $plan,
                    );
                }
            });

            if (! $registered) {
                Log::warning('Stripe checkout payment exceeded founder pass capacity.', [
                    'stripe_session_hash' => PrivacySafeLogContext::fingerprint($sessionId),
                    'plan' => $plan,
                    'reservation_id' => $reservationId,
                ]);
            } else {
                if ($purchaseToConfirm) {
                    if ($purchaseToInvoice) {
                        app(QontoInvoiceService::class)->sendForPurchase($purchaseToInvoice);
                    }

                    $this->sendAutomaticPurchaseConfirmation(
                        $purchaseToConfirm,
                        app(TransactionalEmailSender::class),
                    );
                } elseif ($purchaseToInvoice) {
                    app(QontoInvoiceService::class)->sendForPurchase($purchaseToInvoice);
                }
            }
        } catch (QueryException $exception) {
            Log::error('Stripe checkout purchase registration failed.', [
                'stripe_session_hash' => PrivacySafeLogContext::fingerprint($sessionId),
                ...PrivacySafeLogContext::exception($exception),
            ]);

            return false;
        }

        $request->session()->put('checkout_plan', $plan);

        return true;
    }

    private function recordSuccessfulPurchaseConsent(
        Request $request,
        ConsentAuditService $audit,
        string $purchaseId,
        string $email,
        string $source,
        string $plan,
        ?string $waitlistEntryId = null,
    ): void {
        $alreadyRecorded = DB::table('consent_events')
            ->where('purchase_id', $purchaseId)
            ->where('consent_type', 'purchase_legal')
            ->where('action', 'granted')
            ->exists();

        if ($alreadyRecorded) {
            return;
        }

        $audit->record(
            $request,
            $email,
            'purchase_legal',
            'granted',
            $source,
            ['sales', 'refunds', 'purchase_acceptance'],
            [
                'waitlist_entry_id' => $waitlistEntryId
                    ?? DB::table('waitlist_entries')->where('email', $email)->value('id'),
                'purchase_id' => $purchaseId,
            ],
            ['plan' => $plan],
        );
    }

    private function sendAutomaticPurchaseConfirmation(string $purchaseId, TransactionalEmailSender $emailSender): void
    {
        $purchase = DB::table('purchases')->where('id', $purchaseId)->first();

        if (! $purchase || $purchase->status !== 'succeeded' || $purchase->confirmation_email_sent_at) {
            return;
        }

        try {
            if ($emailSender->send($purchase->email, $this->purchaseConfirmationMailable($purchase))) {
                DB::table('purchases')
                    ->where('id', $purchaseId)
                    ->whereNull('confirmation_email_sent_at')
                    ->update(['confirmation_email_sent_at' => now()]);
            }
        } catch (Throwable $exception) {
            Log::error('Automatic purchase confirmation email failed.', [
                'purchase_id' => $purchaseId,
                'email_hash' => PrivacySafeLogContext::fingerprint($purchase->email),
                ...PrivacySafeLogContext::exception($exception),
            ]);
        }
    }

    private function reservedPassCount(string $plan, ?string $exceptPurchaseId = null): int
    {
        return DB::table('purchases')
            ->where('plan', $plan)
            ->when($exceptPurchaseId, fn ($query) => $query->where('id', '!=', $exceptPurchaseId))
            ->where(function ($query) {
                $query->where('status', 'succeeded')
                    ->orWhere(function ($query) {
                        $query->where('status', 'pending')
                            ->where('created_at', '>=', now()->subMinutes(self::RESERVATION_MINUTES));
                    });
            })
            ->count();
    }

    private function markReservationFailed(?string $reservationId): void
    {
        if (! $reservationId) {
            return;
        }

        DB::table('purchases')
            ->where('id', $reservationId)
            ->where('status', 'pending')
            ->update([
                'status' => 'failed',
                'updated_at' => now(),
            ]);
    }

    private function capacityKeyForPlan(string $plan): string
    {
        return $plan === 'creator' ? 'creator_capacity' : 'join_capacity';
    }

    private function orderReference(string $purchaseId): string
    {
        return 'WO-'.now()->format('Y').'-'.strtoupper($purchaseId);
    }

    private function planName(string $plan): string
    {
        return match ($plan) {
            'creator' => 'Founder 12M Creator Pass',
            default => 'Founder Join 12M Pass',
        };
    }

    private function purchasedPlanFlashData(string $email, object $purchase): array
    {
        return [
            'waitlist_offer' => true,
            'waitlist_status' => 'already_registered',
            'waitlist_email' => $email,
            'purchased_plan' => [
                'code' => $purchase->plan,
                'name' => $this->planName($purchase->plan),
                'amount' => $purchase->amount,
                'currency' => $purchase->currency,
            ],
        ];
    }

    private function purchaseConfirmationResponse(
        Request $request,
        string $message,
        bool $successful,
        ?string $email = null,
        ?object $purchase = null,
        int $status = 200,
    ) {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => $successful,
                'message' => $message,
            ], $status);
        }

        $response = back();

        if ($email && $purchase) {
            $response->with($this->purchasedPlanFlashData($email, $purchase));
        } elseif ($email) {
            $response
                ->with('waitlist_offer', true)
                ->with('waitlist_status', 'already_registered')
                ->with('waitlist_email', $email);
        }

        return $response->with(
            $successful ? 'purchase_confirmation_success' : 'purchase_confirmation_error',
            $message,
        );
    }

    private function normalizeFiscalCode(?string $fiscalCode): ?string
    {
        $fiscalCode = trim((string) $fiscalCode);

        return $fiscalCode === '' ? null : strtoupper($fiscalCode);
    }

    private function purchaseConfirmationMailable(object $purchase): PurchaseConfirmationMail
    {
        $purchase->order_reference = $purchase->order_reference ?? $this->orderReference($purchase->id);
        $attachment = null;
        $attachmentKind = null;

        if ($purchase->invoice_requested) {
            $attachment = app(QontoInvoiceService::class)->courtesyPdfForPurchase($purchase->id);
            $attachmentKind = $attachment ? 'courtesy_invoice' : null;
        } else {
            $attachment = [
                'data' => app(PurchasePdfService::class)->orderSummary($purchase),
                'filename' => 'riepilogo-ordine-'.$purchase->order_reference.'.pdf',
            ];
            $attachmentKind = 'order_summary';
        }

        return new PurchaseConfirmationMail([
            'order_reference' => $purchase->order_reference,
            'email' => $purchase->email,
            'plan_name' => $this->planName($purchase->plan),
            'amount' => $purchase->amount,
            'currency' => $purchase->currency,
            'invoice_requested' => (bool) $purchase->invoice_requested,
            'invoice_status' => $purchase->electronic_invoice_status ?? 'not_requested',
            'attachment_kind' => $attachmentKind,
        ], $attachment);
    }

    /** @param array<string, mixed> $customer */
    private function billingPurchaseData(array $customer): array
    {
        return [
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
        ];
    }
}
