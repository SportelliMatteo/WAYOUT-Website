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
                ]);
        }

        $email = strtolower($emailValidator->validated()['email']);
        $waitlistEntry = DB::table('waitlist_entries')
            ->where('email', $email)
            ->first();
        $storedProfile = $this->profileData($waitlistEntry);
        $profileSubmitted = $request->hasAny(['first_name', 'last_name', 'birth_date']);

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
                ]);
        }

        $validated = $validator->validated();

        return [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'birth_date' => $validated['birth_date'],
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

        $validatedCustomer = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'birth_date' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'invoice_requested' => ['sometimes', 'boolean'],
            'fiscal_code' => ['required_if:invoice_requested,true', 'nullable', 'string', 'max:32'],
        ]);

        $customer = [
            'first_name' => $validatedCustomer['first_name'],
            'last_name' => $validatedCustomer['last_name'],
            'birth_date' => $validatedCustomer['birth_date'],
            'invoice_requested' => $request->boolean('invoice_requested'),
            'fiscal_code' => $validatedCustomer['fiscal_code'] ?? null,
        ];

        $request->session()->put('waitlist_profile', [
            'first_name' => $customer['first_name'],
            'last_name' => $customer['last_name'],
            'birth_date' => $customer['birth_date'],
        ]);

        $plan = $request->input('plan');

        if (! in_array($plan, ['join', 'creator'], true)) {
            return response()->json([
                'error' => __('messages.messages.invalid_pass'),
            ], 422);
        }

        if ($availability->isPlanSoldOut($plan)) {
            return response()->json([
                'error' => __('messages.messages.pass_sold_out', ['plan' => $this->planName($plan)]),
            ], 422);
        }

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

        $directCheckout = $request->boolean('direct_checkout', false);

        if ($directCheckout) {
            try {
                $soldOut = false;
                $purchase = null;

                DB::transaction(function () use ($email, $plan, $planConfig, $availability, $customer, &$purchase, &$soldOut) {
                    $capacity = (int) (DB::table('founder_settings')
                        ->where('key', $availability->capacityKeyForPlan($plan))
                        ->lockForUpdate()
                        ->value('value') ?? config('founder.default_capacities.'.$availability->capacityKeyForPlan($plan)));

                    $soldCount = DB::table('purchases')
                        ->where('status', 'succeeded')
                        ->where('plan', $plan)
                        ->count();

                    if ($capacity <= 0 || $soldCount >= $capacity) {
                        $soldOut = true;

                        return;
                    }

                    $purchase = DB::table('purchases')->insertGetId([
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
                    'metadata[plan]' => $plan,
                    'metadata[invoice_requested]' => $customer['invoice_requested'] ? 'true' : 'false',
                    'metadata[fiscal_code]' => $customer['fiscal_code'] ?? '',
                ]);
        } catch (Throwable $exception) {
            Log::error('Stripe checkout request failed.', [
                'plan' => $plan,
                'exception' => $exception,
            ]);

            return response()->json([
                'error' => __('messages.messages.checkout_unavailable'),
            ], 502);
        }

        if ($response->failed()) {
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
            Log::warning('Stripe checkout response did not include a session id.', [
                'plan' => $plan,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'error' => __('messages.messages.checkout_unavailable'),
            ], 502);
        }

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
            'plan' => $plan,
            'amount' => (int) ($response->json('amount_total') ?? ($plan === 'creator' ? 5900 : 2900)),
            'currency' => strtolower($response->json('currency') ?? 'eur'),
            'stripe_session_id' => $sessionId,
            'status' => 'succeeded',
            'invoice_requested' => ($metadata['invoice_requested'] ?? 'false') === 'true',
            'fiscal_code' => filled($metadata['fiscal_code'] ?? null) ? $metadata['fiscal_code'] : null,
            'updated_at' => now(),
        ];

        try {
            $existingPurchase = DB::table('purchases')
                ->where('stripe_session_id', $sessionId)
                ->first();

            if ($existingPurchase) {
                DB::table('purchases')
                    ->where('id', $existingPurchase->id)
                    ->update($purchaseData);
            } else {
                DB::table('purchases')->insert([
                    ...$purchaseData,
                    'created_at' => now(),
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
        ];
    }

    private function profileComplete(?object $entry): bool
    {
        return filled($entry?->first_name)
            && filled($entry?->last_name)
            && filled($entry?->birth_date);
    }
}
