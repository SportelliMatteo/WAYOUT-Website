<?php

namespace App\Http\Controllers;

use App\Mail\PurchaseConfirmationMail;
use App\Support\FounderAvailability;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
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
        $emailValidator = Validator::make($request->all(), [
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        if ($emailValidator->fails()) {
            return back()
                ->withErrors($emailValidator)
                ->withInput()
                ->with('waitlist_profile_prompt', true)
                ->with('waitlist_status', $request->input('waitlist_status', 'registered'))
                ->with('waitlist_email', strtolower((string) $request->input('email')))
                ->with('waitlist_profile_required', true)
                ->with('waitlist_profile', [
                    'first_name' => $request->input('first_name', ''),
                    'last_name' => $request->input('last_name', ''),
                    'birth_date' => $request->input('birth_date', ''),
                    'phone_prefix' => $request->input('phone_prefix', '+39'),
                    'phone_number' => $request->input('phone_number', ''),
                ]);
        }

        $email = strtolower($emailValidator->validated()['email']);
        $waitlistEntry = DB::table('waitlist_entries')
            ->where('email', $email)
            ->first();
        $storedProfile = $this->profileData($waitlistEntry);
        $profileSubmitted = $request->hasAny(['first_name', 'last_name', 'birth_date', 'phone_prefix', 'phone_number']);

        if ($this->profileComplete($waitlistEntry) && ! $profileSubmitted) {
            $profile = $storedProfile;
        } else {
            $profile = $this->validateSubmittedProfile($request, $email);

            if (! is_array($profile)) {
                return $profile;
            }
        }

        DB::table('waitlist_entries')
            ->where('email', $email)
            ->update([
                ...$profile,
                'updated_at' => now(),
            ]);

        $request->session()->put('waitlist_offer_access', true);
        $request->session()->put('waitlist_email', $email);
        $request->session()->put('waitlist_profile', $profile);
        $request->session()->flash('subscribe_entry_allowed', true);

        return redirect()->route('subscribe');
    }

    private function validateSubmittedProfile(Request $request, string $email)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'birth_date' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'phone_prefix' => ['required', 'string', 'max:8', 'regex:/^\+\d{1,4}$/'],
            'phone_number' => ['required', 'string', 'max:32', 'regex:/^[0-9\s().-]{5,32}$/'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('waitlist_profile_prompt', true)
                ->with('waitlist_status', $request->input('waitlist_status', 'registered'))
                ->with('waitlist_email', $email)
                ->with('waitlist_profile_required', true)
                ->with('waitlist_profile', [
                    'first_name' => $request->input('first_name', ''),
                    'last_name' => $request->input('last_name', ''),
                    'birth_date' => $request->input('birth_date', ''),
                    'phone_prefix' => $request->input('phone_prefix', '+39'),
                    'phone_number' => $request->input('phone_number', ''),
                ]);
        }

        $validated = $validator->validated();

        return [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'birth_date' => $validated['birth_date'],
            'phone_prefix' => $validated['phone_prefix'],
            'phone_number' => $validated['phone_number'],
        ];
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (is_string($sessionId) && str_starts_with($sessionId, 'cs_')) {
            $this->registerSuccessfulStripeCheckout($sessionId, $request);
        }

        return view('pages.checkout-success');
    }

    public function resendPurchaseConfirmation(Request $request)
    {
        $email = $request->session()->get('waitlist_email');

        if (! $email) {
            return back()->with('purchase_confirmation_error', __('messages.messages.purchase_email_missing'));
        }

        try {
            $purchase = DB::table('purchases')
                ->where('email', $email)
                ->where('status', 'succeeded')
                ->latest('created_at')
                ->first();
        } catch (QueryException $exception) {
            Log::error('Purchase confirmation lookup failed.', [
                'email' => $email,
                'exception' => $exception,
            ]);

            return back()
                ->with('waitlist_offer', true)
                ->with('waitlist_status', 'already_registered')
                ->with('waitlist_email', $email)
                ->with('purchase_confirmation_error', __('messages.messages.purchase_resend_error'));
        }

        if (! $purchase) {
            return back()->with('purchase_confirmation_error', __('messages.messages.purchase_not_found'));
        }

        $purchaseData = [
            'email' => $email,
            'plan_name' => $this->planName($purchase->plan),
            'amount' => $purchase->amount,
            'currency' => $purchase->currency,
        ];

        try {
            Mail::to($email)->send(new PurchaseConfirmationMail($purchaseData));
        } catch (Throwable $exception) {
            Log::error('Purchase confirmation resend failed.', [
                'email' => $email,
                'purchase_id' => $purchase->id,
                'exception' => $exception,
            ]);

            return back()
                ->with($this->purchasedPlanFlashData($email, $purchase))
                ->with('purchase_confirmation_error', __('messages.messages.purchase_resend_error'));
        }

        return back()
            ->with($this->purchasedPlanFlashData($email, $purchase))
            ->with('purchase_confirmation_success', __('messages.messages.purchase_resend_success'));
    }

    public function checkout(Request $request, FounderAvailability $availability)
    {
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

        $validatedCustomer = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'birth_date' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'phone_prefix' => ['required', 'string', 'max:8', 'regex:/^\+\d{1,4}$/'],
            'phone_number' => ['required', 'string', 'max:32', 'regex:/^[0-9\s().-]{5,32}$/'],
            'invoice_requested' => ['sometimes', 'boolean'],
            'purchase_terms_accepted' => ['required', 'accepted'],
            'fiscal_code' => ['required_if:invoice_requested,true', 'nullable', 'string', 'max:32'],
        ]);

        $customer = [
            'first_name' => $validatedCustomer['first_name'],
            'last_name' => $validatedCustomer['last_name'],
            'birth_date' => $validatedCustomer['birth_date'],
            'phone_prefix' => $validatedCustomer['phone_prefix'],
            'phone_number' => $validatedCustomer['phone_number'],
            'invoice_requested' => $request->boolean('invoice_requested'),
            'fiscal_code' => $this->normalizeFiscalCode($validatedCustomer['fiscal_code'] ?? null),
        ];

        $request->session()->put('waitlist_profile', [
            'first_name' => $customer['first_name'],
            'last_name' => $customer['last_name'],
            'birth_date' => $customer['birth_date'],
            'phone_prefix' => $customer['phone_prefix'],
            'phone_number' => $customer['phone_number'],
        ]);

        DB::table('waitlist_entries')
            ->where('email', $email)
            ->update([
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'birth_date' => $customer['birth_date'],
                'phone_prefix' => $customer['phone_prefix'],
                'phone_number' => $customer['phone_number'],
                'updated_at' => now(),
            ]);

        $directCheckout = $request->boolean('direct_checkout', false);

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

                DB::transaction(function () use ($email, $plan, $planConfig, $availability, $customer, &$purchase, &$soldOut) {
                    $capacity = (int) (DB::table('founder_settings')
                        ->where('key', $availability->capacityKeyForPlan($plan))
                        ->lockForUpdate()
                        ->value('value') ?? config('founder.default_capacities.'.$availability->capacityKeyForPlan($plan)));

                    $soldCount = $this->reservedPassCount($plan);

                    if ($capacity <= 0 || $soldCount >= $capacity) {
                        $soldOut = true;

                        return;
                    }

                    $purchase = DB::table('purchases')->insertGetId([
                        'email' => $email,
                        'first_name' => $customer['first_name'],
                        'last_name' => $customer['last_name'],
                        'birth_date' => $customer['birth_date'],
                        'phone_prefix' => $customer['phone_prefix'],
                        'phone_number' => $customer['phone_number'],
                        'plan' => $plan,
                        'amount' => (int) $planConfig['unit_amount'],
                        'currency' => 'eur',
                        'stripe_session_id' => 'direct_'.uniqid(),
                        'status' => 'succeeded',
                        'invoice_requested' => $customer['invoice_requested'],
                        'fiscal_code' => $customer['fiscal_code'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });
            } catch (QueryException $exception) {
                Log::error('Direct checkout purchase insert failed.', [
                    'plan' => $plan,
                    'exception' => $exception,
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

            DB::transaction(function () use ($email, $plan, $planConfig, $availability, $customer, &$reservationId, &$soldOut) {
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
                    'email' => $email,
                    'first_name' => $customer['first_name'],
                    'last_name' => $customer['last_name'],
                    'birth_date' => $customer['birth_date'],
                    'phone_prefix' => $customer['phone_prefix'],
                    'phone_number' => $customer['phone_number'],
                    'plan' => $plan,
                    'amount' => (int) $planConfig['unit_amount'],
                    'currency' => 'eur',
                    'stripe_session_id' => null,
                    'status' => 'pending',
                    'invoice_requested' => $customer['invoice_requested'],
                    'fiscal_code' => $customer['fiscal_code'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($existingReservation) {
                    DB::table('purchases')
                        ->where('id', $existingReservation->id)
                        ->update($reservationData);

                    $reservationId = $existingReservation->id;
                } else {
                    $reservationId = DB::table('purchases')->insertGetId($reservationData);
                }
            });
        } catch (QueryException $exception) {
            Log::error('Stripe checkout reservation failed.', [
                'plan' => $plan,
                'exception' => $exception,
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
                    'metadata[phone_prefix]' => $customer['phone_prefix'],
                    'metadata[phone_number]' => $customer['phone_number'],
                    'metadata[plan]' => $plan,
                    'metadata[purchase_id]' => (string) $reservationId,
                    'metadata[invoice_requested]' => $customer['invoice_requested'] ? 'true' : 'false',
                    'metadata[fiscal_code]' => $customer['fiscal_code'] ?? '',
                ]);
        } catch (Throwable $exception) {
            $this->markReservationFailed($reservationId);

            Log::error('Stripe checkout request failed.', [
                'plan' => $plan,
                'exception' => $exception,
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
                'body' => $response->body(),
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
                'body' => $response->body(),
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

    private function registerSuccessfulStripeCheckout(string $sessionId, Request $request): void
    {
        $secret = config('services.stripe.secret');

        if (! $secret) {
            Log::warning('Stripe success callback skipped because STRIPE_SECRET is missing.', [
                'stripe_session_id' => $sessionId,
            ]);

            return;
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 200)
                ->withToken($secret)
                ->get('https://api.stripe.com/v1/checkout/sessions/'.$sessionId);
        } catch (Throwable $exception) {
            Log::error('Stripe checkout session lookup failed.', [
                'stripe_session_id' => $sessionId,
                'exception' => $exception,
            ]);

            return;
        }

        if ($response->failed()) {
            Log::warning('Stripe checkout session lookup returned an error.', [
                'stripe_session_id' => $sessionId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return;
        }

        if ($response->json('payment_status') !== 'paid') {
            Log::info('Stripe checkout session was not paid yet.', [
                'stripe_session_id' => $sessionId,
                'payment_status' => $response->json('payment_status'),
            ]);

            return;
        }

        $metadata = $response->json('metadata') ?? [];
        $plan = $metadata['plan'] ?? null;

        if (! in_array($plan, ['join', 'creator'], true)) {
            Log::warning('Stripe checkout session missing a valid plan.', [
                'stripe_session_id' => $sessionId,
                'metadata' => $metadata,
            ]);

            return;
        }

        $email = $metadata['email'] ?? $response->json('customer_details.email') ?? $response->json('customer_email');

        if (! $email) {
            Log::warning('Stripe checkout session missing customer email.', [
                'stripe_session_id' => $sessionId,
            ]);

            return;
        }

        $purchaseData = [
            'email' => strtolower($email),
            'first_name' => $metadata['first_name'] ?? null,
            'last_name' => $metadata['last_name'] ?? null,
            'birth_date' => $metadata['birth_date'] ?? null,
            'phone_prefix' => $metadata['phone_prefix'] ?? null,
            'phone_number' => $metadata['phone_number'] ?? null,
            'plan' => $plan,
            'amount' => (int) ($response->json('amount_total') ?? ($plan === 'creator' ? 5900 : 2900)),
            'currency' => strtolower($response->json('currency') ?? 'eur'),
            'stripe_session_id' => $sessionId,
            'status' => 'succeeded',
            'invoice_requested' => ($metadata['invoice_requested'] ?? 'false') === 'true',
            'fiscal_code' => $this->normalizeFiscalCode($metadata['fiscal_code'] ?? null),
            'updated_at' => now(),
        ];

        try {
            $registered = false;
            $reservationId = filled($metadata['purchase_id'] ?? null) ? (int) $metadata['purchase_id'] : null;

            DB::transaction(function () use ($plan, $sessionId, $reservationId, $purchaseData, &$registered) {
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
                } else {
                    DB::table('purchases')->insert([
                        ...$data,
                        'created_at' => now(),
                    ]);
                }
            });

            if (! $registered) {
                Log::warning('Stripe checkout payment exceeded founder pass capacity.', [
                    'stripe_session_id' => $sessionId,
                    'plan' => $plan,
                    'reservation_id' => $reservationId,
                ]);
            }
        } catch (QueryException $exception) {
            Log::error('Stripe checkout purchase registration failed.', [
                'stripe_session_id' => $sessionId,
                'exception' => $exception,
            ]);

            return;
        }

        $request->session()->put('checkout_plan', $plan);
    }

    private function reservedPassCount(string $plan, ?int $exceptPurchaseId = null): int
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

    private function markReservationFailed(?int $reservationId): void
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

    private function profileData(?object $entry): array
    {
        return [
            'first_name' => $entry->first_name ?? '',
            'last_name' => $entry->last_name ?? '',
            'birth_date' => $entry->birth_date ?? '',
            'phone_prefix' => $entry->phone_prefix ?? '+39',
            'phone_number' => $entry->phone_number ?? '',
        ];
    }

    private function profileComplete(?object $entry): bool
    {
        return filled($entry?->first_name)
            && filled($entry?->last_name)
            && filled($entry?->birth_date)
            && filled($entry?->phone_prefix)
            && filled($entry?->phone_number);
    }

    private function normalizeFiscalCode(?string $fiscalCode): ?string
    {
        $fiscalCode = trim((string) $fiscalCode);

        return $fiscalCode === '' ? null : strtoupper($fiscalCode);
    }
}
