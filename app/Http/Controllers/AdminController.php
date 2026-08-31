<?php

namespace App\Http\Controllers;

use App\Exceptions\WayoutApiException;
use App\Models\AdminUser;
use App\Support\AdminAuditService;
use App\Support\CheckoutFeatures;
use App\Support\DatabaseUuid;
use App\Support\DataRetentionService;
use App\Support\FounderPromoCatalog;
use App\Support\LegalDocumentService;
use App\Support\QontoInvoiceService;
use App\Support\SitePreviewAccess;
use App\Support\SiteVisibility;
use App\Support\WaitlistBenefitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard(
        Request $request,
        LegalDocumentService $legalDocuments,
        SiteVisibility $siteVisibility,
        CheckoutFeatures $checkoutFeatures,
        FounderPromoCatalog $founderCatalog,
        WaitlistBenefitService $waitlistBenefits,
        SitePreviewAccess $sitePreview,
        DataRetentionService $dataRetention,
    ) {
        if (! $this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => $request->query('status', 'all'),
            'plan' => $request->query('plan', 'all'),
        ];
        $perPage = fn (string $name): int => in_array((int) $request->query($name.'_per_page', 10), [5, 10, 20], true)
            ? (int) $request->query($name.'_per_page', 10)
            : 10;

        $purchaseSummary = DB::table('purchases')
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' THEN 1 ELSE 0 END) as succeeded_orders")
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' THEN amount ELSE 0 END) as succeeded_revenue")
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' AND plan = 'join' THEN 1 ELSE 0 END) as join_orders")
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' AND plan = 'join' THEN amount ELSE 0 END) as join_revenue")
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' AND plan = 'creator' THEN 1 ELSE 0 END) as creator_orders")
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' AND plan = 'creator' THEN amount ELSE 0 END) as creator_revenue")
            ->first();

        $stats = [
            'waitlist_total' => DB::table('waitlist_entries')->whereNotNull('email_verified_at')->count(),
            'waitlist_pending' => DB::table('waitlist_entries')->whereNull('email_verified_at')->count(),
            'waitlist_benefits_eligible' => $waitlistBenefits->count(),
            'buyers_total' => DB::table('purchases')->where('status', 'succeeded')->distinct('email')->count('email'),
            'orders_total' => (int) ($purchaseSummary->total_orders ?? 0),
            'orders_succeeded' => (int) ($purchaseSummary->succeeded_orders ?? 0),
            'revenue_total' => (int) ($purchaseSummary->succeeded_revenue ?? 0),
            'join_orders' => (int) ($purchaseSummary->join_orders ?? 0),
            'join_revenue' => (int) ($purchaseSummary->join_revenue ?? 0),
            'creator_orders' => (int) ($purchaseSummary->creator_orders ?? 0),
            'creator_revenue' => (int) ($purchaseSummary->creator_revenue ?? 0),
            'cookie_consent_updates' => Schema::hasTable('cookie_consent_events') ? DB::table('cookie_consent_events')->count() : 0,
            'cookie_consent_all' => Schema::hasTable('cookie_consent_events') ? DB::table('cookie_consent_events')->where('analytics', true)->where('marketing', true)->count() : 0,
        ];

        try {
            $founderPackages = $founderCatalog->founderPackages();
        } catch (WayoutApiException $exception) {
            Log::warning('Founder promo catalog unavailable in admin dashboard.', [
                ...$exception->logContext(),
            ]);
            $founderPackages = ['join' => null, 'creator' => null];
        }

        $purchaseAggregate = DB::table('purchases')
            ->select('waitlist_entry_id')
            ->selectRaw('COUNT(*) as orders_total')
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' THEN 1 ELSE 0 END) as orders_succeeded")
            ->selectRaw("SUM(CASE WHEN status = 'succeeded' THEN amount ELSE 0 END) as revenue_total")
            ->selectRaw('MAX(created_at) as latest_purchase_at')
            ->selectRaw($this->successfulPlansExpression())
            ->whereNotNull('waitlist_entry_id')
            ->groupBy('waitlist_entry_id');

        $waitlistQuery = DB::table('waitlist_entries')
            ->leftJoinSub($purchaseAggregate, 'purchase_totals', function ($join) {
                $join->on('waitlist_entries.id', '=', 'purchase_totals.waitlist_entry_id');
            })
            ->select([
                'waitlist_entries.id',
                'waitlist_entries.benefit_id',
                'waitlist_entries.email',
                'waitlist_entries.waitlist_position',
                'waitlist_entries.email_verified_at',
                'waitlist_entries.marketing_consent',
                'waitlist_entries.created_at',
                'waitlist_entries.updated_at',
                DB::raw('COALESCE(purchase_totals.orders_total, 0) as orders_total'),
                DB::raw('COALESCE(purchase_totals.orders_succeeded, 0) as orders_succeeded'),
                DB::raw('COALESCE(purchase_totals.revenue_total, 0) as revenue_total'),
                'purchase_totals.latest_purchase_at',
                'purchase_totals.plans',
            ])
            ->orderByDesc('waitlist_entries.created_at');

        if ($filters['q'] !== '') {
            $search = '%'.Str::lower($filters['q']).'%';
            $uuidCast = DB::connection()->getDriverName() === 'mysql' ? 'CHAR' : 'TEXT';
            $waitlistQuery->where(function ($query) use ($search, $uuidCast) {
                $query->whereRaw('LOWER(waitlist_entries.email) LIKE ?', [$search])
                    ->orWhereRaw("LOWER(CAST(waitlist_entries.id AS {$uuidCast})) LIKE ?", [$search])
                    ->orWhereRaw("LOWER(CAST(waitlist_entries.benefit_id AS {$uuidCast})) LIKE ?", [$search]);
            });
        }

        if ($filters['status'] === 'buyers') {
            $waitlistQuery->whereRaw('COALESCE(purchase_totals.orders_succeeded, 0) > 0');
        } elseif ($filters['status'] === 'no_purchase') {
            $waitlistQuery->whereNotNull('waitlist_entries.email_verified_at')
                ->whereRaw('COALESCE(purchase_totals.orders_succeeded, 0) = 0');
        } elseif ($filters['status'] === 'pending') {
            $waitlistQuery->whereNull('waitlist_entries.email_verified_at');
        } elseif ($filters['status'] === 'benefit_eligible') {
            $waitlistQuery->whereNotNull('waitlist_entries.email_verified_at')
                ->whereNotNull('waitlist_entries.benefit_id')
                ->whereNotNull('waitlist_entries.waitlist_position')
                ->whereRaw('COALESCE(purchase_totals.orders_succeeded, 0) = 0');
        }

        if (in_array($filters['plan'], ['join', 'creator'], true)) {
            $waitlistQuery->whereExists(function ($query) use ($filters) {
                $query->selectRaw('1')
                    ->from('purchases')
                    ->whereColumn('purchases.waitlist_entry_id', 'waitlist_entries.id')
                    ->where('purchases.status', 'succeeded')
                    ->where('purchases.plan', $filters['plan']);
            });
        }

        $waitlistEntries = $waitlistQuery
            ->paginate($perPage('waitlist'), ['*'], 'waitlist_page')
            ->withQueryString();

        $recentPurchases = DB::table('purchases')
            ->orderByDesc('created_at')
            ->paginate($perPage('orders'), ['*'], 'orders_page')
            ->withQueryString();

        $recentConsentEvents = DB::table('consent_events')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate($perPage('consents'), ['*'], 'consents_page')
            ->withQueryString();

        $recentWithdrawals = Schema::hasTable('withdrawal_requests')
            ? DB::table('withdrawal_requests')
                ->orderByDesc('submitted_at')
                ->paginate($perPage('withdrawals'), ['*'], 'withdrawals_page')
                ->withQueryString()
            : collect();

        $joinBuyers = $this->buyersForPlan('join', $perPage('join_buyers'), 'join_buyers_page');
        $creatorBuyers = $this->buyersForPlan('creator', $perPage('creator_buyers'), 'creator_buyers_page');
        $currentLegalDocuments = $legalDocuments->allCurrent();
        $legalDocumentHistory = $legalDocuments->paginatedHistory(
            fn (string $document): int => $perPage('legal_'.$document),
        );
        $adminUsers = AdminUser::query()
            ->orderBy('name')
            ->paginate($perPage('admins'), ['*'], 'admins_page')
            ->withQueryString();
        $recentAdminAuditEvents = Schema::hasTable('admin_audit_events')
            ? DB::table('admin_audit_events')
                ->orderByDesc('occurred_at')
                ->orderByDesc('id')
                ->paginate($perPage('audit'), ['*'], 'audit_page')
                ->withQueryString()
            : collect();
        $sitePreviewExpiresAt = $sitePreview->expiresAt($request);
        $retentionPreview = $dataRetention->preview();
        $retentionRuns = DB::table('data_retention_runs')
            ->orderByDesc('started_at')
            ->paginate($perPage('retention'), ['*'], 'retention_page')
            ->withQueryString();

        return view('admin.dashboard', [
            'filters' => $filters,
            'stats' => $stats,
            'founderPackages' => $founderPackages,
            'waitlistEntries' => $waitlistEntries,
            'recentPurchases' => $recentPurchases,
            'recentConsentEvents' => $recentConsentEvents,
            'recentWithdrawals' => $recentWithdrawals,
            'joinBuyers' => $joinBuyers,
            'creatorBuyers' => $creatorBuyers,
            'planLabels' => $this->planLabels(),
            'legalDocuments' => $currentLegalDocuments,
            'legalDocumentHistory' => $legalDocumentHistory,
            'adminUsers' => $adminUsers,
            'currentAdmin' => $request->attributes->get('admin_user'),
            'recentAdminAuditEvents' => $recentAdminAuditEvents,
            'siteVisibilityMode' => $siteVisibility->mode(),
            'sitePreviewExpiresAt' => $sitePreviewExpiresAt,
            'legalEntityInvoiceEnabled' => $checkoutFeatures->legalEntityInvoiceEnabled(),
            'retentionPreview' => $retentionPreview,
            'retentionRuns' => $retentionRuns,
            'nextRetentionRunAt' => $dataRetention->nextRunAt(),
        ]);
    }

    public function enableSitePreview(
        Request $request,
        SitePreviewAccess $sitePreview,
        AdminAuditService $audit,
    ) {
        $expiresAt = $sitePreview->enable($request);
        $audit->record(
            $request,
            'site_preview.enabled',
            'site_settings',
            targetLabel: 'protected website preview',
            newValues: ['expires_at' => $expiresAt->toISOString()],
        );

        return redirect()->route('home');
    }

    public function disableSitePreview(
        Request $request,
        SitePreviewAccess $sitePreview,
        AdminAuditService $audit,
    ) {
        $sitePreview->disable($request);
        $audit->record(
            $request,
            'site_preview.disabled',
            'site_settings',
            targetLabel: 'protected website preview',
        );

        return redirect()->route('admin.dashboard', ['tab' => 'settings'])
            ->with('admin_success', __('messages.admin.site_preview_disabled'));
    }

    public function publishLegalDocument(
        Request $request,
        string $document,
        LegalDocumentService $legalDocuments,
        AdminAuditService $audit,
    ) {
        if (! $this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        abort_unless(in_array($document, $legalDocuments->keys(), true), 404);

        $validated = $request->validate([
            'locale' => ['required', 'in:it,en'],
            'version' => ['required', 'date_format:Y-m-d'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'content_html' => ['required', 'string', 'max:200000'],
        ]);

        $published = DB::transaction(function () use ($request, $document, $validated, $legalDocuments, $audit) {
            $previous = $legalDocuments->current($document, $validated['locale']);
            $published = $legalDocuments->publish(
                $document,
                $validated['locale'],
                $validated['version'],
                $validated['title'],
                $validated['description'],
                $validated['content_html'],
            );

            $audit->record(
                $request,
                'legal_document.published',
                'legal_document',
                $published->version_id,
                $document.' · '.$validated['locale'],
                [
                    'version' => $previous->version,
                    'title' => $previous->title,
                    'content_hash' => $previous->content_hash,
                ],
                [
                    'version' => $published->version,
                    'title' => $published->title,
                    'content_hash' => $published->content_hash,
                ],
            );

            return $published;
        });

        return redirect()
            ->to(route('admin.dashboard', [
                'lang' => $validated['locale'],
                'tab' => 'legal',
                'legal_group' => config('legal.documents.'.$document.'.group', 'policies'),
            ]).'#legal-documents')
            ->with('admin_success', __('messages.admin.legal_published', [
                'document' => $validated['title'],
                'version' => $published->version,
            ]));
    }

    public function updateSiteVisibility(
        Request $request,
        SiteVisibility $siteVisibility,
        SitePreviewAccess $sitePreview,
        AdminAuditService $audit,
    ) {
        if (! $this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:'.implode(',', $siteVisibility->modes())],
        ]);
        $previous = $siteVisibility->mode();

        DB::transaction(function () use ($request, $validated, $previous, $siteVisibility, $audit) {
            $existing = DB::table('founder_settings')
                ->where('key', SiteVisibility::SETTING_KEY)
                ->exists();

            DB::table('founder_settings')->updateOrInsert(
                ['key' => SiteVisibility::SETTING_KEY],
                [
                    'value' => $siteVisibility->valueFor($validated['mode']),
                    'updated_at' => now(),
                    ...($existing ? [] : ['id' => DatabaseUuid::new(), 'created_at' => now()]),
                ],
            );

            $audit->record(
                $request,
                'site_visibility.updated',
                'site_settings',
                targetLabel: 'public website',
                oldValues: ['mode' => $previous],
                newValues: ['mode' => $validated['mode']],
            );
        });
        $sitePreview->disable($request);

        return redirect()->route('admin.dashboard', ['tab' => 'settings'])
            ->with('admin_success', __('messages.admin.site_visibility_updated'));
    }

    public function updateCheckoutSettings(
        Request $request,
        CheckoutFeatures $checkoutFeatures,
        AdminAuditService $audit,
    ) {
        if (! $this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate([
            'legal_entity_invoice_enabled' => ['required', 'boolean'],
        ]);
        $previous = $checkoutFeatures->legalEntityInvoiceEnabled();
        $enabled = (bool) $validated['legal_entity_invoice_enabled'];

        DB::transaction(function () use ($request, $previous, $enabled, $audit) {
            $existing = DB::table('founder_settings')
                ->where('key', CheckoutFeatures::LEGAL_ENTITY_INVOICE_KEY)
                ->exists();

            DB::table('founder_settings')->updateOrInsert(
                ['key' => CheckoutFeatures::LEGAL_ENTITY_INVOICE_KEY],
                [
                    'value' => $enabled ? 1 : 0,
                    'updated_at' => now(),
                    ...($existing ? [] : ['id' => DatabaseUuid::new(), 'created_at' => now()]),
                ],
            );

            $audit->record(
                $request,
                'checkout_settings.updated',
                'checkout_settings',
                targetLabel: 'legal entity invoice',
                oldValues: ['legal_entity_invoice_enabled' => $previous],
                newValues: ['legal_entity_invoice_enabled' => $enabled],
            );
        });

        return redirect()->route('admin.dashboard', ['tab' => 'settings'])
            ->with('admin_success', __('messages.admin.checkout_settings_updated'));
    }

    public function retryInvoice(
        Request $request,
        string $purchase,
        QontoInvoiceService $invoices,
        AdminAuditService $audit,
    ) {
        $record = DB::table('purchases')->where('id', $purchase)->first();
        abort_unless($record, 404);

        if (! $record->invoice_requested || $record->status !== 'succeeded') {
            return redirect()->route('admin.dashboard', ['tab' => 'orders'])
                ->withErrors(['invoice' => __('messages.admin.invoice_not_retryable')]);
        }

        $previous = $this->invoiceAuditValues($record);
        $successful = $invoices->sendForPurchase($purchase);
        $updated = DB::table('purchases')->where('id', $purchase)->first();

        $audit->record(
            $request,
            'qonto_invoice.retried',
            'purchase',
            $purchase,
            $record->order_reference,
            $previous,
            $this->invoiceAuditValues($updated),
        );

        $response = redirect()->to(route('admin.dashboard', ['tab' => 'orders']).'#orders');

        return $successful
            ? $response->with('admin_success', __('messages.admin.invoice_retry_success'))
            : $response->withErrors(['invoice' => $updated->qonto_invoice_error ?: __('messages.admin.invoice_retry_failed')]);
    }

    public function syncInvoice(
        Request $request,
        string $purchase,
        QontoInvoiceService $invoices,
        AdminAuditService $audit,
    ) {
        $record = DB::table('purchases')->where('id', $purchase)->first();
        abort_unless($record, 404);

        if (! $record->qonto_invoice_id) {
            return redirect()->route('admin.dashboard', ['tab' => 'orders'])
                ->withErrors(['invoice' => __('messages.admin.invoice_not_created')]);
        }

        $previous = $this->invoiceAuditValues($record);
        $successful = $invoices->syncForPurchase($purchase);
        $updated = DB::table('purchases')->where('id', $purchase)->first();

        $audit->record(
            $request,
            'qonto_invoice.synchronized',
            'purchase',
            $purchase,
            $record->order_reference,
            $previous,
            $this->invoiceAuditValues($updated),
        );

        $response = redirect()->to(route('admin.dashboard', ['tab' => 'orders']).'#orders');

        return $successful
            ? $response->with('admin_success', __('messages.admin.invoice_sync_success'))
            : $response->withErrors(['invoice' => $updated->qonto_invoice_error ?: __('messages.admin.invoice_sync_failed')]);
    }

    private function buyersForPlan(string $plan, int $perPage, string $pageName)
    {
        return DB::table('purchases')
            ->select('email')
            ->selectRaw('MAX(first_name) as first_name')
            ->selectRaw('MAX(last_name) as last_name')
            ->selectRaw('COUNT(*) as orders_total')
            ->selectRaw('SUM(amount) as revenue_total')
            ->selectRaw('MAX(created_at) as latest_purchase_at')
            ->where('status', 'succeeded')
            ->where('plan', $plan)
            ->groupBy('email')
            ->orderByDesc('latest_purchase_at')
            ->paginate($perPage, ['*'], $pageName)
            ->withQueryString();
    }

    /** @return array<string, mixed> */
    private function invoiceAuditValues(object $purchase): array
    {
        return [
            'electronic_invoice_status' => $purchase->electronic_invoice_status,
            'qonto_invoice_id' => $purchase->qonto_invoice_id,
            'qonto_invoice_number' => $purchase->qonto_invoice_number,
            'qonto_invoice_status' => $purchase->qonto_invoice_status,
            'qonto_einvoicing_status' => $purchase->qonto_einvoicing_status,
            'qonto_invoice_error' => $purchase->qonto_invoice_error,
            'qonto_invoice_synced_at' => $purchase->qonto_invoice_synced_at,
        ];
    }

    private function isAuthenticated(Request $request): bool
    {
        return $request->session()->has('admin_authenticated');
    }

    private function planLabels(): array
    {
        return [
            'join' => 'Founder Join 12M',
            'creator' => 'Founder Creator 12M',
        ];
    }

    private function successfulPlansExpression(): string
    {
        if (in_array(DB::connection()->getDriverName(), ['sqlite', 'mysql'], true)) {
            return "GROUP_CONCAT(DISTINCT CASE WHEN status = 'succeeded' THEN plan END) as plans";
        }

        return "STRING_AGG(DISTINCT CASE WHEN status = 'succeeded' THEN plan END, ',') as plans";
    }
}
