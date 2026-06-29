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
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $request->session()->put('waitlist_offer_access', true);
        $request->session()->put('waitlist_email', strtolower($validated['email']));
        $request->session()->flash('subscribe_entry_allowed', true);

        return redirect()->route('subscribe');
    }

    public function success(Request $request)
    {
        return view('pages.checkout-success');
    }

    public function resendPurchaseConfirmation(Request $request)
    {
        $email = $request->session()->get('waitlist_email');

        if (! $email) {
            return back()->with('purchase_confirmation_error', 'Email waitlist non trovata. Reinserisci la tua email dalla home.');
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
                ->with('purchase_confirmation_error', 'Non siamo riusciti a inviare nuovamente l’email. Riprova tra qualche minuto.');
        }

        if (! $purchase) {
            return back()->with('purchase_confirmation_error', 'Non risulta ancora un acquisto confermato per questa email.');
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
                ->with('purchase_confirmation_error', 'Non siamo riusciti a inviare nuovamente l’email. Riprova tra qualche minuto.');
        }

        return back()
            ->with($this->purchasedPlanFlashData($email, $purchase))
            ->with('purchase_confirmation_success', 'Ti abbiamo inviato nuovamente l’email di conferma acquisto.');
    }

    public function checkout(Request $request, FounderAvailability $availability)
    {
        if (! $request->session()->get('waitlist_offer_access')) {
            return response()->json([
                'error' => 'Accesso non autorizzato. Torna dal banner della waitlist.',
            ], 403);
        }

        $email = $request->session()->get('waitlist_email');

        if (! $email) {
            return response()->json([
                'error' => 'Email waitlist non trovata. Reinserisci la tua email dalla home.',
            ], 403);
        }

        $plan = $request->input('plan');

        if (! in_array($plan, ['join', 'creator'], true)) {
            return response()->json([
                'error' => 'Seleziona un Founder Pass valido.',
            ], 422);
        }

        if ($availability->isPlanSoldOut($plan)) {
            return response()->json([
                'error' => $this->planName($plan).' è esaurito. Scegli un altro pass o resta in waitlist.',
            ], 422);
        }

        $planConfig = match ($plan) {
            'creator' => [
                'name' => $this->planName('creator'),
                'description' => 'Founder 12M Creator Pass - 12 months + 60 days trial',
                'unit_amount' => '5900',
                'interval' => 'year',
            ],
            default => [
                'name' => $this->planName('join'),
                'description' => 'Founder Join 12M Pass - 12 months + 60 days trial',
                'unit_amount' => '2900',
                'interval' => 'year',
            ],
        };

        $directCheckout = $request->boolean('direct_checkout', true);

        if ($directCheckout) {
            try {
                $soldOut = false;
                $purchase = null;

                DB::transaction(function () use ($email, $plan, $planConfig, $availability, &$purchase, &$soldOut) {
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
                        'plan' => $plan,
                        'amount' => (int) $planConfig['unit_amount'],
                        'currency' => 'eur',
                        'stripe_session_id' => 'direct_'.uniqid(),
                        'status' => 'succeeded',
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
                    'error' => 'Non siamo riusciti a registrare il pagamento. Riprova tra qualche minuto.',
                ], 500);
            }

            if ($soldOut) {
                return response()->json([
                    'error' => $this->planName($plan).' è esaurito. Scegli un altro pass o resta in waitlist.',
                ], 422);
            }

            return response()->json([
                'purchaseId' => $purchase,
                'completed' => true,
                'redirectUrl' => route('checkout.success'),
            ]);
        }

        $secret = config('services.stripe.secret');

        if (! $secret) {
            return response()->json([
                'error' => 'Stripe non è configurato. Inserisci STRIPE_SECRET in .env.',
            ], 500);
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 200)
                ->withToken($secret)
                ->asForm()
                ->post('https://api.stripe.com/v1/checkout/sessions', [
                    'payment_method_types[]' => 'card',
                    'mode' => 'subscription',
                    'success_url' => route('checkout.success', [], true),
                    'cancel_url' => route('subscribe', [], true),
                    'line_items[0][price_data][currency]' => 'eur',
                    'line_items[0][price_data][product_data][name]' => $planConfig['name'],
                    'line_items[0][price_data][product_data][description]' => $planConfig['description'],
                    'line_items[0][price_data][unit_amount]' => $planConfig['unit_amount'],
                    'line_items[0][price_data][recurring][interval]' => $planConfig['interval'],
                    'line_items[0][quantity]' => '1',
                    'customer_email' => $email,
                    'metadata[email]' => $email,
                    'metadata[plan]' => $plan,
                ]);
        } catch (Throwable $exception) {
            Log::error('Stripe checkout request failed.', [
                'plan' => $plan,
                'exception' => $exception,
            ]);

            return response()->json([
                'error' => 'Checkout temporaneamente non disponibile. Riprova tra qualche minuto.',
            ], 502);
        }

        if ($response->failed()) {
            Log::warning('Stripe checkout returned an error.', [
                'plan' => $plan,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'error' => 'Checkout temporaneamente non disponibile. Riprova tra qualche minuto.',
            ], 502);
        }

        if (! $response->json('id')) {
            Log::warning('Stripe checkout response did not include a session id.', [
                'plan' => $plan,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'error' => 'Checkout temporaneamente non disponibile. Riprova tra qualche minuto.',
            ], 502);
        }

        return response()->json([
            'sessionId' => $response->json('id'),
        ]);
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
}
