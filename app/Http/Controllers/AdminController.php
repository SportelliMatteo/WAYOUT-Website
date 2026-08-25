<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Support\AdminAuditService;
use App\Support\LegalDocumentService;
use App\Support\QontoInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard(Request $request, LegalDocumentService $legalDocuments)
    {
        if (! $this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => $request->query('status', 'all'),
            'plan' => $request->query('plan', 'all'),
        ];

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
            'benefits_claimed' => DB::table('benefit_claims')->count(),
            'buyers_total' => DB::table('purchases')->where('status', 'succeeded')->distinct('email')->count('email'),
            'orders_total' => (int) ($purchaseSummary->total_orders ?? 0),
            'orders_succeeded' => (int) ($purchaseSummary->succeeded_orders ?? 0),
            'revenue_total' => (int) ($purchaseSummary->succeeded_revenue ?? 0),
            'join_orders' => (int) ($purchaseSummary->join_orders ?? 0),
            'join_revenue' => (int) ($purchaseSummary->join_revenue ?? 0),
            'creator_orders' => (int) ($purchaseSummary->creator_orders ?? 0),
            'creator_revenue' => (int) ($purchaseSummary->creator_revenue ?? 0),
        ];

        $capacities = $this->founderCapacities();

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
            ->leftJoin('benefit_claims', 'waitlist_entries.id', '=', 'benefit_claims.waitlist_entry_id')
            ->select([
                'waitlist_entries.id',
                'waitlist_entries.benefit_id',
                'waitlist_entries.email',
                'waitlist_entries.waitlist_position',
                'waitlist_entries.email_verified_at',
                'waitlist_entries.marketing_consent',
                'waitlist_entries.created_at',
                'waitlist_entries.updated_at',
                'benefit_claims.account_reference',
                'benefit_claims.benefit_type as claimed_benefit_type',
                'benefit_claims.claimed_at',
                DB::raw('COALESCE(purchase_totals.orders_total, 0) as orders_total'),
                DB::raw('COALESCE(purchase_totals.orders_succeeded, 0) as orders_succeeded'),
                DB::raw('COALESCE(purchase_totals.revenue_total, 0) as revenue_total'),
                'purchase_totals.latest_purchase_at',
                'purchase_totals.plans',
            ])
            ->orderByDesc('waitlist_entries.created_at');

        if ($filters['q'] !== '') {
            $search = '%'.Str::lower($filters['q']).'%';
            $waitlistQuery->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(waitlist_entries.email) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(CAST(waitlist_entries.id AS TEXT)) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(CAST(waitlist_entries.benefit_id AS TEXT)) LIKE ?', [$search]);
            });
        }

        if ($filters['status'] === 'buyers') {
            $waitlistQuery->whereRaw('COALESCE(purchase_totals.orders_succeeded, 0) > 0');
        } elseif ($filters['status'] === 'no_purchase') {
            $waitlistQuery->whereNotNull('waitlist_entries.email_verified_at')
                ->whereRaw('COALESCE(purchase_totals.orders_succeeded, 0) = 0');
        } elseif ($filters['status'] === 'pending') {
            $waitlistQuery->whereNull('waitlist_entries.email_verified_at');
        } elseif ($filters['status'] === 'claimed') {
            $waitlistQuery->whereNotNull('benefit_claims.claimed_at');
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
            ->paginate(20)
            ->withQueryString();

        $recentPurchases = DB::table('purchases')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $recentConsentEvents = DB::table('consent_events')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $recentWithdrawals = Schema::hasTable('withdrawal_requests')
            ? DB::table('withdrawal_requests')
                ->orderByDesc('submitted_at')
                ->limit(30)
                ->get()
            : collect();

        $joinBuyers = $this->buyersForPlan('join');
        $creatorBuyers = $this->buyersForPlan('creator');
        $currentLegalDocuments = $legalDocuments->allCurrent();
        $legalDocumentHistory = $legalDocuments->history();
        $adminUsers = AdminUser::query()->orderBy('name')->get();
        $recentAdminAuditEvents = Schema::hasTable('admin_audit_events')
            ? DB::table('admin_audit_events')
                ->orderByDesc('occurred_at')
                ->orderByDesc('id')
                ->limit(30)
                ->get()
            : collect();

        return view('admin.dashboard', [
            'filters' => $filters,
            'stats' => $stats,
            'capacities' => $capacities,
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
        ]);
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

    public function updateSettings(Request $request, AdminAuditService $audit)
    {
        if (! $this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $validated = $request->validate([
            'waitlist_capacity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'join_capacity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'creator_capacity' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        $previous = DB::table('founder_settings')
            ->whereIn('key', array_keys($validated))
            ->pluck('value', 'key')
            ->map(fn ($value) => (int) $value)
            ->all();

        DB::transaction(function () use ($request, $validated, $previous, $audit) {
            foreach ($validated as $key => $value) {
                $existing = DB::table('founder_settings')->where('key', $key)->exists();

                DB::table('founder_settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'updated_at' => now(),
                        ...($existing ? [] : ['created_at' => now()]),
                    ]
                );
            }

            $audit->record(
                $request,
                'founder_capacities.updated',
                'founder_settings',
                targetLabel: 'waitlist / join / creator',
                oldValues: $previous,
                newValues: $validated,
            );
        });

        return back()->with('admin_success', __('messages.admin.admin_success'));
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

    private function buyersForPlan(string $plan)
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
            ->limit(20)
            ->get();
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
        if (DB::connection()->getDriverName() === 'sqlite') {
            return "GROUP_CONCAT(DISTINCT CASE WHEN status = 'succeeded' THEN plan END) as plans";
        }

        return "STRING_AGG(DISTINCT CASE WHEN status = 'succeeded' THEN plan END, ',') as plans";
    }

    private function founderCapacities(): array
    {
        $defaults = config('founder.default_capacities');

        if (! Schema::hasTable('founder_settings')) {
            return $defaults;
        }

        return array_replace(
            $defaults,
            DB::table('founder_settings')
                ->pluck('value', 'key')
                ->map(fn ($value) => (int) $value)
                ->all()
        );
    }
}
