<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use App\Support\LegalDocumentService;
use App\Support\PurchaseSupportService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
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

    public function receipt(string $token)
    {
        $withdrawal = $this->withdrawalFromToken($token);
        app()->setLocale($withdrawal->locale ?: 'it');

        return response()->view('pages.withdrawal.receipt', [
            'withdrawal' => $withdrawal,
            'token' => $token,
            'supportRequest' => DB::table('purchase_support_requests')->where('purchase_id', $withdrawal->purchase_id)
                ->where('source', PurchaseSupportService::WITHDRAWAL)->first(),
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

    private function withdrawalFromToken(string $token): WithdrawalRequest
    {
        abort_unless(strlen($token) === 64, 404);

        return WithdrawalRequest::query()
            ->where('public_token_hash', hash('sha256', $token))
            ->firstOrFail();
    }

}
