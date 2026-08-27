<?php

namespace App\Http\Controllers;

use App\Exceptions\WayoutApiException;
use App\Mail\PurchaseConfirmationMail;
use App\Support\CheckoutFeatures;
use App\Support\ConsentAuditService;
use App\Support\FounderPromoCatalog;
use App\Support\PrivacySafeLogContext;
use App\Support\PurchasePdfService;
use App\Support\QontoInvoiceService;
use App\Support\TransactionalEmailSender;
use App\Support\WayoutApiClient;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Throwable;

class SubscribeController extends Controller
{
    public function show(
        Request $request,
        CheckoutFeatures $checkoutFeatures,
        FounderPromoCatalog $catalog,
    ) {
        if (! $request->session()->pull('subscribe_entry_allowed')) {
            return redirect()->route('home');
        }

        try {
            $founderPackages = $catalog->founderPackages();
            $catalogError = null;
        } catch (WayoutApiException $exception) {
            Log::warning('Founder promo catalog unavailable.', [
                'status' => $exception->status,
                'code' => $exception->apiCode,
            ]);
            $founderPackages = ['join' => null, 'creator' => null];
            $catalogError = __('messages.subscribe.catalog_unavailable');
        }

        return view('pages.subscribe', [
            'legalEntityInvoiceEnabled' => $checkoutFeatures->legalEntityInvoiceEnabled(),
            'founderPackages' => $founderPackages,
            'catalogError' => $catalogError,
            'firebaseConfig' => config('services.firebase.client'),
        ]);
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

    public function success(
        Request $request,
        string $purchase,
        WayoutApiClient $wayout,
        ConsentAuditService $audit,
        QontoInvoiceService $qontoInvoices,
        TransactionalEmailSender $emailSender,
    ) {
        $order = DB::table('purchases')->where('id', $purchase)->first();
        abort_unless($order, 404);

        $checkoutState = $this->confirmWayoutPurchase(
            $request,
            $order,
            $wayout,
            $audit,
            $qontoInvoices,
            $emailSender,
        );
        $order = DB::table('purchases')->where('id', $purchase)->first();
        $purchaseAnalytics = $checkoutState === 'confirmed'
            ? $this->purchaseAnalytics($order)
            : null;
        $statusUrl = URL::temporarySignedRoute('checkout.status', now()->addHour(), ['purchase' => $purchase]);

        return view('pages.checkout-success', compact('checkoutState', 'purchaseAnalytics', 'statusUrl'));
    }

    public function status(
        Request $request,
        string $purchase,
        WayoutApiClient $wayout,
        ConsentAuditService $audit,
        QontoInvoiceService $qontoInvoices,
        TransactionalEmailSender $emailSender,
    ) {
        $order = DB::table('purchases')->where('id', $purchase)->first();

        if (! $order) {
            return response()->json(['state' => 'not_found'], 404);
        }

        $state = $this->confirmWayoutPurchase(
            $request,
            $order,
            $wayout,
            $audit,
            $qontoInvoices,
            $emailSender,
        );
        $order = DB::table('purchases')->where('id', $purchase)->first();

        return response()->json([
            'state' => $state,
            'analytics' => $state === 'confirmed' ? $this->purchaseAnalytics($order) : null,
        ]);
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

    private function confirmWayoutPurchase(
        Request $request,
        object $purchase,
        WayoutApiClient $wayout,
        ConsentAuditService $audit,
        QontoInvoiceService $qontoInvoices,
        TransactionalEmailSender $emailSender,
    ): string {
        if ($purchase->status === 'succeeded') {
            return 'confirmed';
        }

        if (in_array($purchase->status, ['failed', 'canceled', 'expired', 'overbooked'], true)) {
            return 'review';
        }

        if (! is_string($purchase->wayout_user_id ?? null) || $purchase->wayout_user_id === '') {
            return 'review';
        }

        try {
            $response = $wayout->internalGet('/api/v1/internal/users/'.$purchase->wayout_user_id.'/subscription');
        } catch (WayoutApiException $exception) {
            Log::warning('Wayout purchase confirmation unavailable.', [
                'purchase_id' => $purchase->id,
                'status' => $exception->status,
                'code' => $exception->apiCode,
            ]);

            return 'pending';
        }

        if (data_get($response, 'data.has_active_entitlement') !== true) {
            return 'pending';
        }

        $stripeSubscriptionId = data_get($response, 'data.subscription.stripe_subscription_id');
        $source = data_get($response, 'data.subscription.source');

        if ($source !== 'STRIPE') {
            Log::warning('Wayout confirmation returned a non-Stripe entitlement.', [
                'purchase_id' => $purchase->id,
                'source' => $source,
            ]);

            return 'pending';
        }

        DB::transaction(function () use ($purchase, $stripeSubscriptionId): void {
            $locked = DB::table('purchases')->where('id', $purchase->id)->lockForUpdate()->first();

            if (! $locked || $locked->status === 'succeeded') {
                return;
            }

            DB::table('purchases')->where('id', $purchase->id)->update([
                'status' => 'succeeded',
                'wayout_subscription_id' => is_string($stripeSubscriptionId) ? $stripeSubscriptionId : null,
                'updated_at' => now(),
            ]);
        });

        $confirmed = DB::table('purchases')->where('id', $purchase->id)->first();

        if (! $confirmed || $confirmed->status !== 'succeeded') {
            return 'pending';
        }

        $this->recordSuccessfulPurchaseConsent(
            $request,
            $audit,
            $confirmed->id,
            $confirmed->email,
            'wayout_payment_confirmation',
            $confirmed->plan,
            $confirmed->waitlist_entry_id,
        );

        $qontoInvoices->sendForPurchase($confirmed->id);
        $this->sendAutomaticPurchaseConfirmation($confirmed->id, $emailSender);
        $request->session()->put('checkout_plan', $confirmed->plan);
        $request->session()->put('purchase_confirmation_email', $confirmed->email);

        return 'confirmed';
    }

    private function purchaseAnalytics(object $purchase): array
    {
        return [
            'transaction_id' => $purchase->order_reference ?: (string) $purchase->id,
            'plan' => $purchase->plan,
            'value' => ((int) $purchase->amount) / 100,
            'currency' => strtoupper($purchase->currency ?: 'eur'),
        ];
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
}
