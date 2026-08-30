<?php

namespace App\Http\Controllers;

use App\Mail\WithdrawalReceiptMail;
use App\Models\WithdrawalRequest;
use App\Support\DatabaseUuid;
use App\Support\LegalDocumentService;
use App\Support\TransactionalEmailSender;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class WithdrawalController extends Controller
{
    private const SESSION_KEY = 'withdrawal.review';

    public function create(LegalDocumentService $legalDocuments)
    {
        return $this->legalView('pages.withdrawal.create', $legalDocuments);
    }

    public function downloadTemplate()
    {
        $english = app()->getLocale() === 'en';
        $path = resource_path($english
            ? 'documents/withdrawal-form-template-en.docx'
            : 'documents/modulo-tipo-recesso.docx');

        abort_unless(is_file($path), 404);

        return response()->download($path, __('messages.withdrawal.template_filename'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function storeReview(Request $request)
    {
        $validated = $request->validate([
            'website' => ['nullable', 'max:0'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'purchase_email' => ['required', 'email:rfc', 'max:255'],
            'receipt_email' => ['required', 'email:rfc', 'max:255'],
            'order_reference' => ['required', 'string', 'max:100'],
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'plan' => ['required', Rule::in(['join', 'creator', 'other'])],
        ]);

        unset($validated['website']);
        $validated['first_name'] = trim($validated['first_name']);
        $validated['last_name'] = trim($validated['last_name']);
        $validated['purchase_email'] = Str::lower(trim($validated['purchase_email']));
        $validated['receipt_email'] = Str::lower(trim($validated['receipt_email']));
        $validated['order_reference'] = trim($validated['order_reference']);
        $validated['locale'] = app()->getLocale();

        $purchase = $this->matchPurchase($validated);
        if (! $purchase) {
            $request->session()->forget(self::SESSION_KEY);

            return back()
                ->withInput()
                ->withErrors(['order_reference' => __('messages.withdrawal.order_not_found_text')])
                ->with('order_not_found', true);
        }

        $validated['plan'] = $purchase->plan;
        $validated['purchase_date'] = Carbon::parse($purchase->created_at)->toDateString();
        $validated['nonce'] = Str::random(48);
        $validated['idempotency_key'] = (string) Str::uuid();
        $validated['public_token'] = Str::random(64);
        $validated['expires_at'] = now()->addMinutes(30)->timestamp;

        $request->session()->put(self::SESSION_KEY, $validated);

        return redirect()->route('withdrawal.review');
    }

    public function review(Request $request)
    {
        $review = $this->validReview($request);

        if (! $review) {
            return redirect()->route('legal.refunds')
                ->withErrors(['withdrawal' => __('messages.withdrawal.review_expired')]);
        }

        app()->setLocale($review['locale'] ?? 'it');

        return response()->view('pages.withdrawal.review', [
            'review' => $review,
            'declaration' => $this->declaration($review),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function confirm(
        Request $request,
        LegalDocumentService $legalDocuments,
        TransactionalEmailSender $emailSender,
    ) {
        $review = $this->validReview($request);

        if (! $review || ! hash_equals((string) $review['nonce'], (string) $request->input('nonce'))) {
            return redirect()->route('legal.refunds')
                ->withErrors(['withdrawal' => __('messages.withdrawal.review_expired')]);
        }

        $locale = in_array($review['locale'] ?? null, ['it', 'en'], true) ? $review['locale'] : 'it';
        app()->setLocale($locale);

        $submittedAt = now();
        $publicToken = $review['public_token'];
        $documents = collect(['withdrawal_info', 'refunds', 'privacy'])
            ->mapWithKeys(fn (string $key) => [$key => $legalDocuments->current($key, $locale)]);
        $purchase = $this->matchPurchase($review);
        $deadline = $purchase ? Carbon::parse($purchase->created_at)->addDays(14) : null;

        $withdrawal = DB::transaction(function () use ($request, $review, $documents, $purchase, $deadline, $submittedAt, $publicToken, $locale) {
            $existing = WithdrawalRequest::query()
                ->where('idempotency_key', $review['idempotency_key'])
                ->first();

            if ($existing) {
                return $existing;
            }

            $withdrawal = WithdrawalRequest::query()->create([
                'purchase_id' => $purchase?->id,
                'receipt_number' => 'WR-'.$submittedAt->format('Ymd').'-'.Str::upper(Str::random(8)),
                'public_token_hash' => hash('sha256', $publicToken),
                'idempotency_key' => $review['idempotency_key'],
                'locale' => $locale,
                'first_name' => $review['first_name'],
                'last_name' => $review['last_name'],
                'purchase_email' => $review['purchase_email'],
                'receipt_email' => $review['receipt_email'],
                'order_reference' => $review['order_reference'],
                'purchase_date' => $review['purchase_date'] ?: null,
                'plan' => $review['plan'],
                'declaration' => $this->declaration($review),
                'document_versions' => $documents->map->version->all(),
                'document_hashes' => $documents->map->content_hash->all(),
                'submitted_at' => $submittedAt,
                'ordinary_deadline_at' => $deadline,
                'within_ordinary_period' => $deadline ? $submittedAt->lessThanOrEqualTo($deadline) : null,
                'submitted_ip_hash' => $this->fingerprint($request->ip()),
                'user_agent_hash' => $this->fingerprint($request->userAgent()),
            ]);

            DB::table('withdrawal_request_events')->insert([
                'id' => DatabaseUuid::new(),
                'withdrawal_request_id' => $withdrawal->id,
                'event_type' => 'request.received',
                'actor_type' => 'consumer',
                'metadata' => json_encode(['purchase_matched' => (bool) $purchase]),
                'occurred_at' => $submittedAt,
                'created_at' => $submittedAt,
            ]);

            return $withdrawal;
        });

        try {
            $sent = $emailSender->send($withdrawal->receipt_email, (new WithdrawalReceiptMail($withdrawal, $publicToken))->locale($locale));
            $withdrawal->forceFill($sent
                ? ['receipt_email_sent_at' => now()]
                : ['receipt_email_failed_at' => now()])->save();
            $this->recordEmailEvent($withdrawal, $sent ? 'receipt.sent' : 'receipt.skipped');
        } catch (Throwable) {
            $withdrawal->forceFill(['receipt_email_failed_at' => now()])->save();
            $this->recordEmailEvent($withdrawal, 'receipt.failed');
        }

        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('withdrawal.receipt', ['token' => $publicToken]);
    }

    public function receipt(string $token)
    {
        $withdrawal = $this->withdrawalFromToken($token);
        app()->setLocale($withdrawal->locale ?: 'it');

        return response()->view('pages.withdrawal.receipt', [
            'withdrawal' => $withdrawal,
            'token' => $token,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function download(string $token)
    {
        $withdrawal = $this->withdrawalFromToken($token);
        app()->setLocale($withdrawal->locale ?: 'it');

        return response(view('pages.withdrawal.receipt-text', compact('withdrawal'))->render(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.__('messages.withdrawal.receipt_filename').'-'.$withdrawal->receipt_number.'.txt"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    private function legalView(string $view, LegalDocumentService $legalDocuments)
    {
        $locale = app()->getLocale();
        $withdrawalInfo = $legalDocuments->current('withdrawal_info', $locale);
        $refundPolicy = $legalDocuments->current('refunds', $locale);
        $version = $withdrawalInfo->version === $refundPolicy->version
            ? $withdrawalInfo->version
            : __('messages.withdrawal.version_summary', [
                'withdrawal' => $withdrawalInfo->version,
                'refund' => $refundPolicy->version,
            ]);

        return view($view, [
            'title' => __('messages.legal.refunds'),
            'description' => __('messages.withdrawal.page_description'),
            'version' => $version,
            'updated' => max(Carbon::parse($withdrawalInfo->published_at), Carbon::parse($refundPolicy->published_at))
                ->setTimezone(config('app.display_timezone'))->translatedFormat('d F Y'),
            'withdrawalInfo' => $withdrawalInfo,
            'refundPolicy' => $refundPolicy,
        ]);
    }

    private function validReview(Request $request): ?array
    {
        $review = $request->session()->get(self::SESSION_KEY);

        return is_array($review) && ($review['expires_at'] ?? 0) >= now()->timestamp ? $review : null;
    }

    private function declaration(array $review): string
    {
        return __('messages.withdrawal.declaration', [
            'first_name' => $review['first_name'],
            'last_name' => $review['last_name'],
            'order' => $review['order_reference'],
            'plan' => $review['plan'],
            'email' => $review['purchase_email'],
        ], $review['locale'] ?? 'it');
    }

    private function matchPurchase(array $review): ?object
    {
        $query = DB::table('purchases')
            ->whereRaw('LOWER(email) = ?', [$review['purchase_email']])
            ->where('status', 'succeeded');

        $byOrderReference = (clone $query)->whereRaw('UPPER(order_reference) = ?', [strtoupper($review['order_reference'])])->first();
        if ($byOrderReference) {
            return $byOrderReference;
        }

        $byStripeReference = (clone $query)->where('stripe_session_id', $review['order_reference'])->first();
        if ($byStripeReference) {
            return $byStripeReference;
        }

        return null;
    }

    private function withdrawalFromToken(string $token): WithdrawalRequest
    {
        abort_unless(strlen($token) === 64, 404);

        return WithdrawalRequest::query()
            ->where('public_token_hash', hash('sha256', $token))
            ->firstOrFail();
    }

    private function fingerprint(?string $value): ?string
    {
        return filled($value) ? hash_hmac('sha256', $value, (string) config('app.key')) : null;
    }

    private function recordEmailEvent(WithdrawalRequest $withdrawal, string $type): void
    {
        DB::table('withdrawal_request_events')->insert([
            'id' => DatabaseUuid::new(),
            'withdrawal_request_id' => $withdrawal->id,
            'event_type' => $type,
            'actor_type' => 'system',
            'occurred_at' => now(),
            'created_at' => now(),
        ]);
    }
}
