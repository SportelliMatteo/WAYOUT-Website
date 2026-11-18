<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Support\AdminAuditService;
use App\Support\AppStoreLinks;
use App\Support\CheckoutFeatures;
use App\Support\DatabaseUuid;
use App\Support\DataRetentionService;
use App\Support\SitePreviewAccess;
use App\Support\SiteVisibility;
use App\Support\WaitlistBenefitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard(
        Request $request,
        SiteVisibility $siteVisibility,
        CheckoutFeatures $checkoutFeatures,
        SitePreviewAccess $sitePreview,
        DataRetentionService $dataRetention,
        AppStoreLinks $storeLinks,
        WaitlistBenefitService $waitlistBenefits,
    ) {
        if (! $this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }
        $perPage = fn (string $name): int => in_array((int) $request->query($name.'_per_page', 10), [5, 10, 20], true)
            ? (int) $request->query($name.'_per_page', 10) : 10;

        $filters = $request->validate([
            'waitlist_q' => ['sometimes', 'nullable', 'string', 'max:255'],
            'waitlist_status' => ['sometimes', 'in:all,verified,pending,eligible,ineligible'],
        ]);
        $search = trim($filters['waitlist_q'] ?? '');
        $status = $filters['waitlist_status'] ?? 'all';
        $eligibleIds = $waitlistBenefits->eligibleQuery()->select('waitlist_entries.id');
        $entries = DB::table('waitlist_entries')->select(['id', 'email', 'benefit_id', 'waitlist_position', 'email_verified_at', 'created_at']);
        if ($search !== '') {
            $entries->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(email) LIKE ?', ['%'.Str::lower($search).'%'])
                    ->orWhere('id', $search)->orWhere('benefit_id', $search);
            });
        }
        if ($status === 'eligible') {
            $entries->whereIn('id', clone $eligibleIds);
        } elseif ($status === 'ineligible') {
            $entries->whereNotIn('id', clone $eligibleIds);
        } elseif ($status === 'pending') {
            $entries->whereNull('email_verified_at');
        } elseif ($status === 'verified') {
            $entries->whereNotNull('email_verified_at');
        }
        $waitlistEntries = $entries->orderByDesc('created_at')->orderBy('id')
            ->paginate($perPage('waitlist'), ['*'], 'waitlist_page')->withQueryString();
        $pageEligibleIds = $waitlistBenefits->eligibleQuery()->whereIn('waitlist_entries.id', $waitlistEntries->pluck('id'))
            ->pluck('waitlist_entries.id')->all();

        return view('admin.dashboard', [
            'plansEnabled' => $checkoutFeatures->plansEnabled(),
            'waitlistEntries' => $waitlistEntries,
            'waitlistEligibleIds' => $pageEligibleIds,
            'waitlistSearch' => $search,
            'waitlistStatus' => $status,
            'waitlistStats' => [
                'total' => DB::table('waitlist_entries')->count(),
                'verified' => DB::table('waitlist_entries')->whereNotNull('email_verified_at')->count(),
                'pending' => DB::table('waitlist_entries')->whereNull('email_verified_at')->count(),
                'eligible' => $waitlistBenefits->count(),
            ],
            'appStoreLinks' => $storeLinks->all(),
            'adminUsers' => AdminUser::query()->orderBy('name')->paginate($perPage('admins'), ['*'], 'admins_page')->withQueryString(),
            'currentAdmin' => $request->attributes->get('admin_user'),
            'recentAdminAuditEvents' => DB::table('admin_audit_events')->whereIn('target_type', ['site_settings', 'checkout_settings', 'admin_user', 'settings'])->orderByDesc('occurred_at')->orderByDesc('id')->paginate($perPage('audit'), ['*'], 'audit_page')->withQueryString(),
            'siteVisibilityMode' => $siteVisibility->mode(),
            'sitePreviewExpiresAt' => $sitePreview->expiresAt($request),
            'legalEntityInvoiceEnabled' => $checkoutFeatures->legalEntityInvoiceEnabled(),
            'retentionPreview' => $dataRetention->preview(),
            'retentionRuns' => DB::table('data_retention_runs')->orderByDesc('started_at')->paginate($perPage('retention'), ['*'], 'retention_page')->withQueryString(),
            'nextRetentionRunAt' => $dataRetention->nextRunAt(),
        ]);
    }

    public function updateAppStoreLinks(Request $request, AppStoreLinks $storeLinks, AdminAuditService $audit)
    {
        $validated = $request->validate([
            'apple' => ['nullable', 'string', 'url:https', 'max:2048'],
            'google' => ['nullable', 'string', 'url:https', 'max:2048'],
        ]);
        DB::transaction(function () use ($request, $validated, $storeLinks, $audit) {
            $previous = $storeLinks->all();
            $storeLinks->save($validated);
            $audit->record($request, 'app_store_links.updated', 'site_settings', targetLabel: 'App Store / Google Play', oldValues: $previous, newValues: $storeLinks->all());
        });

        return redirect()->route('admin.dashboard', ['tab' => 'settings'])->with('admin_success', __('experience.store_links_saved'));
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

    public function updatePlansAvailability(Request $request, AdminAuditService $audit)
    {
        $validated = $request->validate(['plans_enabled' => ['required', 'boolean']]);
        DB::transaction(function () use ($request, $validated, $audit) {
            $query = DB::table('founder_settings')->where('key', CheckoutFeatures::PLANS_ENABLED_KEY);
            $previous = (clone $query)->lockForUpdate()->first();
            DB::table('founder_settings')->updateOrInsert(['key' => CheckoutFeatures::PLANS_ENABLED_KEY], [
                'value' => $validated['plans_enabled'] ? 1 : 0, 'updated_at' => now(),
                ...($previous ? [] : ['id' => DatabaseUuid::new(), 'created_at' => now()]),
            ]);
            $audit->record($request, 'plans.availability_updated', 'checkout_settings', targetLabel: 'Piani e acquisti',
                oldValues: ['plans_enabled' => (bool) ($previous->value ?? false)],
                newValues: ['plans_enabled' => (bool) $validated['plans_enabled']]);
        });

        return redirect()->route('admin.dashboard', ['tab' => 'settings'])->with('admin_success', 'Disponibilità dei piani aggiornata.');
    }

    private function isAuthenticated(Request $request): bool
    {
        return $request->session()->has('admin_authenticated');
    }
}
