@php
    $money = fn (?int $amount) => number_format(($amount ?? 0) / 100, 2, ',', '.') . '€';
    $number = fn (?int $value) => number_format($value ?? 0, 0, ',', '.');
    $packageMaximum = function (?array $package) use ($number) {
        $maximum = $package['max_available_quantity'] ?? null;

        if (!is_numeric($maximum)) return null;
        if ((int) $maximum === -1) return __('messages.admin.unlimited_quantity');

        return $number((int) $maximum);
    };
    $date = fn ($value) => $value
        ? \Illuminate\Support\Carbon::parse($value, 'UTC')->setTimezone(config('app.display_timezone'))->format('d/m/Y H:i')
        : 'Mai';
    $birthDate = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y') : '-';
    $planName = fn (?string $plan) => $plan ? ($planLabels[$plan] ?? ucfirst($plan)) : __('messages.admin.no_pass');
    $invoiceStatusLabel = fn (?string $status) => __('messages.admin.invoice_status_'.($status ?: 'not_requested'));
    $invoiceStatusClass = fn (?string $status) => match ($status) {
        'sent', 'test_created' => 'bg-emerald-100 text-emerald-800',
        'failed' => 'bg-rose-100 text-rose-800',
        'pending', 'processing' => 'bg-amber-100 text-amber-800',
        default => 'bg-slate-100 text-slate-600',
    };
    $documentVersions = fn ($value) => collect(json_decode((string) $value, true) ?: [])->map(fn ($version, $document) => $document.': '.$version)->join(', ');
    $auditValues = fn ($value) => collect(json_decode((string) $value, true) ?: [])->map(function ($item, $key) {
        $full = (string) (is_bool($item) ? ($item ? 'true' : 'false') : (is_array($item) ? json_encode($item) : $item));
        $isHash = str_contains((string) $key, 'hash') && strlen($full) > 24;

        return [
            'label' => (string) $key,
            'full' => $full,
            'display' => $isHash ? substr($full, 0, 12).'…'.substr($full, -8) : $full,
        ];
    })->values();
    $consentReference = fn ($event) => collect([
        $event->waitlist_entry_id ? 'waitlist #'.$event->waitlist_entry_id : null,
        $event->purchase_id ? __('messages.admin.order_reference', ['id' => $event->purchase_id]) : null,
        $event->contact_message_id ? __('messages.admin.contact_reference', ['id' => $event->contact_message_id]) : null,
    ])->filter()->join(', ') ?: '-';
    $retentionResult = fn ($run) => json_decode((string) $run->results, true) ?: [];
    $adminTabs = ['overview', 'users', 'orders', 'withdrawals', 'consents', 'legal', 'settings'];
    $requestedAdminTab = request()->query('tab');
    $activeAdminTab = in_array($requestedAdminTab, $adminTabs, true)
        ? $requestedAdminTab
        : (old('document_key') ? 'legal' : (request()->hasAny(['q', 'status', 'plan']) ? 'users' : 'overview'));
    $legalGroups = ['policies', 'commerce', 'consent_texts'];
    $requestedLegalGroup = request()->query('legal_group');
    $oldDocumentGroup = old('document_key') ? config('legal.documents.'.old('document_key').'.group') : null;
    $activeLegalGroup = in_array($requestedLegalGroup, $legalGroups, true)
        ? $requestedLegalGroup
        : ($oldDocumentGroup ?: 'policies');
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.admin.dashboard_title') }}</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="shortcut icon" href="/favicon.jpg" type="image/jpeg" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-950 antialiased">
    <main class="mx-auto w-full max-w-screen-2xl px-4 py-5 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-700">{{ __('messages.admin.brand') }}</p>
                <h1 class="mt-2 text-3xl font-black sm:text-4xl">{{ __('messages.admin.heading') }}</h1>
            </div>
            <div class="flex items-center gap-2">
                <span class="hidden text-right text-xs font-bold text-slate-500 sm:block">{{ $currentAdmin->name }}<br>{{ $currentAdmin->email }}</span>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'it']) }}" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-black {{ app()->getLocale() === 'it' ? 'text-violet-700' : 'text-slate-500' }}">IT</a>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-black {{ app()->getLocale() === 'en' ? 'text-violet-700' : 'text-slate-500' }}">EN</a>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:border-violet-300 hover:text-violet-700">
                    {{ __('messages.admin.logout') }}
                </button>
            </form>
            </div>
        </header>

        @if (session('admin_success'))
            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-black text-emerald-800">
                {{ session('admin_success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-black text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <nav class="sticky top-3 z-20 mt-5 overflow-x-auto rounded-2xl border border-slate-200 bg-white/95 p-2 shadow-lg shadow-slate-950/5 backdrop-blur" aria-label="{{ __('messages.admin.dashboard_sections') }}">
            <div class="flex min-w-max gap-1" role="tablist" aria-orientation="horizontal">
                @foreach($adminTabs as $tab)
                    <a
                        id="admin-tab-{{ $tab }}"
                        href="{{ route('admin.dashboard', ['lang' => app()->getLocale(), 'tab' => $tab]) }}"
                        role="tab"
                        aria-selected="{{ $activeAdminTab === $tab ? 'true' : 'false' }}"
                        tabindex="{{ $activeAdminTab === $tab ? '0' : '-1' }}"
                        data-admin-tab="{{ $tab }}"
                        class="rounded-xl px-4 py-3 text-sm font-black transition focus:outline-none focus:ring-4 focus:ring-violet-200 {{ $activeAdminTab === $tab ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-950' }}"
                    >{{ __('messages.admin.tab_'.$tab) }}</a>
                @endforeach
            </div>
        </nav>

        <div id="admin-panel-overview" role="tabpanel" aria-labelledby="admin-tab-overview" data-admin-panel="overview" class="{{ $activeAdminTab === 'overview' ? '' : 'hidden' }}">
        <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Waitlist</p>
                <p class="mt-3 text-3xl font-black">{{ $number($stats['waitlist_total']) }}</p>
                <p class="mt-1 text-xs font-bold text-amber-700">{{ __('messages.admin.pending_count', ['count' => $number($stats['waitlist_pending'])]) }} · {{ __('messages.admin.waitlist_benefit_eligible_count', ['count' => $number($stats['waitlist_benefits_eligible'])]) }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">{{ __('messages.admin.buyers') }}</p>
                <p class="mt-3 text-3xl font-black">{{ $stats['buyers_total'] }}</p>
                <p class="mt-1 text-sm font-bold text-slate-500">{{ __('messages.admin.successful_purchase_emails') }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">{{ __('messages.admin.successful_orders') }}</p>
                <p class="mt-3 text-3xl font-black">{{ $stats['orders_succeeded'] }}</p>
                <p class="mt-1 text-sm font-bold text-slate-500">{{ __('messages.admin.total_orders_count', ['count' => $stats['orders_total']]) }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Join Pass</p>
                <p class="mt-3 text-3xl font-black">{{ $money($stats['join_revenue']) }}</p>
                <p class="mt-1 text-sm font-bold text-slate-500">
                    {{ $number($stats['join_orders']) }}{{ ($maximum = $packageMaximum($founderPackages['join'] ?? null)) !== null ? ' / '.$maximum : '' }} pass
                </p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Creator Pass</p>
                <p class="mt-3 text-3xl font-black">{{ $money($stats['creator_revenue']) }}</p>
                <p class="mt-1 text-sm font-bold text-slate-500">
                    {{ $number($stats['creator_orders']) }}{{ ($maximum = $packageMaximum($founderPackages['creator'] ?? null)) !== null ? ' / '.$maximum : '' }} pass
                </p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">{{ __('messages.admin.cookie_consents') }}</p>
                <p class="mt-3 text-3xl font-black">{{ $number($stats['cookie_consent_updates']) }}</p>
                <p class="mt-1 text-sm font-bold text-slate-500">{{ __('messages.admin.cookie_all_granted', ['count' => $number($stats['cookie_consent_all'])]) }}</p>
            </div>
        </section>

        <section class="mt-4 rounded-lg border border-slate-200 bg-slate-950 p-5 text-white shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-violet-200">{{ __('messages.admin.total_revenue') }}</p>
                    <p class="mt-2 text-4xl font-black">{{ $money($stats['revenue_total']) }}</p>
                </div>
                <p class="text-sm font-bold text-slate-300">{{ __('messages.admin.successful_revenue_sum') }}</p>
            </div>
        </section>
        </div>

        <div id="admin-panel-settings" role="tabpanel" aria-labelledby="admin-tab-settings" data-admin-panel="settings" class="{{ $activeAdminTab === 'settings' ? '' : 'hidden' }}">
        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-1 border-b border-slate-200 pb-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-xl font-black">{{ __('messages.admin.site_visibility_title') }}</h2>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-black {{ $siteVisibilityMode === 'online' ? 'bg-emerald-100 text-emerald-800' : ($siteVisibilityMode === 'coming_soon' ? 'bg-violet-100 text-violet-800' : 'bg-amber-100 text-amber-800') }}">
                        {{ __('messages.admin.site_visibility_'.$siteVisibilityMode) }}
                    </span>
                </div>
                <p class="text-sm font-bold text-slate-500">{{ __('messages.admin.site_visibility_text') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.site-visibility.update') }}" class="mt-4 grid gap-3 lg:grid-cols-[1fr_auto] lg:items-end">
                @csrf
                <div>
                    <label for="site-visibility-mode" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ __('messages.admin.site_visibility_mode') }}</label>
                    <select id="site-visibility-mode" name="mode" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-3 font-bold outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                        @foreach(['online', 'coming_soon', 'maintenance'] as $mode)
                            <option value="{{ $mode }}" @selected($siteVisibilityMode === $mode)>{{ __('messages.admin.site_visibility_'.$mode) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-violet-700 px-5 py-3 font-black text-white shadow-sm transition hover:bg-violet-800">
                    {{ __('messages.admin.apply') }}
                </button>
            </form>
            @if($siteVisibilityMode !== 'online')
                <div class="mt-4 rounded-xl border border-violet-200 bg-violet-50 p-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h3 class="font-black text-violet-950">{{ __('messages.admin.site_preview_title') }}</h3>
                            @if($sitePreviewExpiresAt)
                                <p class="mt-1 text-sm font-bold text-violet-800">{{ __('messages.admin.site_preview_active_until', ['date' => $date($sitePreviewExpiresAt)]) }}</p>
                            @else
                                <p class="mt-1 text-sm font-bold text-violet-800">{{ __('messages.admin.site_preview_text') }}</p>
                            @endif
                            <p class="mt-2 text-xs font-bold text-amber-800">{{ __('messages.admin.site_preview_live_warning') }}</p>
                        </div>
                        @if($sitePreviewExpiresAt)
                            <form method="POST" action="{{ route('admin.site-preview.disable') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="whitespace-nowrap rounded-lg border border-violet-300 bg-white px-5 py-3 font-black text-violet-800 transition hover:bg-violet-100">
                                    {{ __('messages.admin.site_preview_disable') }}
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.site-preview.enable') }}" target="_blank">
                                @csrf
                                <button type="submit" class="whitespace-nowrap rounded-lg bg-slate-950 px-5 py-3 font-black text-white shadow-sm transition hover:bg-violet-800">
                                    {{ __('messages.admin.site_preview_open') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </section>

        <section id="data-retention" class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="text-xl font-black">{{ __('messages.admin.retention_title') }}</h2>
                        <p class="mt-1 text-sm font-bold text-slate-500">{{ __('messages.admin.retention_text') }}</p>
                    </div>
                    <div class="rounded-xl bg-violet-50 px-4 py-3 text-sm">
                        <span class="block text-xs font-black uppercase tracking-[0.14em] text-violet-600">{{ __('messages.admin.retention_next_run') }}</span>
                        <span class="mt-1 block font-black text-violet-950">{{ $date($nextRetentionRunAt) }}</span>
                        <span class="mt-1 block text-xs font-bold text-violet-700">{{ __('messages.admin.retention_schedule') }}</span>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 p-4 lg:grid-cols-3">
                @foreach(['email_verifications', 'unverified_waitlist', 'admin_audit_events'] as $policy)
                    <article class="rounded-xl border border-slate-200 p-4">
                        <p class="text-xs font-black uppercase tracking-[0.14em] text-violet-700">{{ __('messages.admin.retention_policy_'.$policy) }}</p>
                        <p class="mt-3 text-3xl font-black">{{ $number($retentionPreview[$policy]['count']) }}</p>
                        <p class="mt-1 text-sm font-bold text-slate-500">{{ __('messages.admin.retention_candidates') }}</p>
                        <p class="mt-3 text-xs font-bold leading-relaxed text-slate-600">{{ __('messages.admin.retention_action_'.$policy) }}</p>
                    </article>
                @endforeach
            </div>

            <div class="border-t border-slate-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-900">
                {{ __('messages.admin.retention_deferred') }}
            </div>

            <div class="border-t border-slate-200">
                <div class="px-4 py-3">
                    <h3 class="font-black">{{ __('messages.admin.retention_history') }}</h3>
                    <p class="mt-1 text-xs font-bold text-slate-500">{{ __('messages.admin.retention_history_text') }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                            <tr>
                                <th class="px-4 py-3">{{ __('messages.admin.date') }}</th>
                                <th class="px-4 py-3">{{ __('messages.admin.retention_trigger') }}</th>
                                <th class="px-4 py-3">{{ __('messages.admin.status') }}</th>
                                <th class="px-4 py-3">{{ __('messages.admin.retention_result') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($retentionRuns as $run)
                                @php
                                    $runResults = $retentionResult($run);
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-500">{{ $date($run->started_at) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 font-bold">{{ __('messages.admin.retention_trigger_'.$run->trigger) }}{{ $run->dry_run ? ' · '.__('messages.admin.retention_dry_run') : '' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-black {{ $run->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($run->status === 'failed' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ __('messages.admin.retention_status_'.$run->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs font-bold text-slate-600">
                                        @if($run->status === 'failed')
                                            {{ $run->error_message }}
                                        @else
                                            {{ collect($runResults)->map(fn ($result, $policy) => __('messages.admin.retention_policy_'.$policy).': '.($run->dry_run ? $result['matched'] : $result['affected']))->join(' · ') }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center font-bold text-slate-500">{{ __('messages.admin.retention_no_runs') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('admin.partials.pagination', ['paginator' => $retentionRuns, 'perPageName' => 'retention_per_page'])
            </div>
        </section>

        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-1 border-b border-slate-200 pb-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-xl font-black">{{ __('messages.admin.checkout_settings_title') }}</h2>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-black {{ $legalEntityInvoiceEnabled ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ $legalEntityInvoiceEnabled ? __('messages.admin.enabled') : __('messages.admin.disabled') }}
                    </span>
                </div>
                <p class="text-sm font-bold text-slate-500">{{ __('messages.admin.checkout_settings_text') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.checkout-settings.update') }}" class="mt-4 grid gap-3 lg:grid-cols-[1fr_auto] lg:items-end">
                @csrf
                <div>
                    <label for="legal-entity-invoice-enabled" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ __('messages.admin.legal_entity_invoice') }}</label>
                    <select id="legal-entity-invoice-enabled" name="legal_entity_invoice_enabled" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-3 font-bold outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                        <option value="0" @selected(! $legalEntityInvoiceEnabled)>{{ __('messages.admin.disabled') }}</option>
                        <option value="1" @selected($legalEntityInvoiceEnabled)>{{ __('messages.admin.enabled') }}</option>
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-slate-950 px-5 py-3 font-black text-white shadow-sm transition hover:bg-violet-700">
                    {{ __('messages.admin.apply') }}
                </button>
            </form>
        </section>

        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="border-b border-slate-200 pb-4">
                <h2 class="text-xl font-black">{{ __('messages.admin.security_title') }}</h2>
                <p class="mt-1 text-sm font-bold text-slate-500">{{ __('messages.admin.security_text') }}</p>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                        <tr>
                            <th class="px-3 py-3">{{ __('messages.admin.user') }}</th>
                            <th class="px-3 py-3">2FA</th>
                            <th class="px-3 py-3">{{ __('messages.admin.last_login') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($adminUsers as $adminUser)
                            <tr>
                                <td class="px-3 py-3">
                                    <span class="font-black">{{ $adminUser->name }}</span>
                                    <span class="block text-xs font-bold text-slate-500">{{ $adminUser->email }}</span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $adminUser->totp_confirmed_at ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $adminUser->totp_confirmed_at ? __('messages.admin.active') : __('messages.admin.to_configure') }}
                                    </span>
                                    @unless($adminUser->is_active)<span class="ml-1 rounded-full bg-slate-200 px-3 py-1 text-xs font-black text-slate-600">{{ __('messages.admin.disabled') }}</span>@endunless
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 font-bold text-slate-500">{{ $date($adminUser->last_login_at) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $adminUsers, 'perPageName' => 'admins_per_page'])

            @if($adminUsers->total() < config('admin.max_users'))
                <form method="POST" action="{{ route('admin.users.store') }}" class="mt-5 grid gap-3 rounded-xl bg-slate-50 p-4 lg:grid-cols-2">
                    @csrf
                    <h3 class="font-black lg:col-span-2">{{ __('messages.admin.add_admin_user') }} ({{ $adminUsers->total() }}/{{ config('admin.max_users') }})</h3>
                    <input name="name" required maxlength="120" value="{{ old('name') }}" placeholder="{{ __('messages.admin.first_and_last_name') }}" class="rounded-lg border border-slate-200 px-4 py-3 font-bold">
                    <input name="email" type="email" required value="{{ old('email') }}" placeholder="Email" class="rounded-lg border border-slate-200 px-4 py-3 font-bold">
                    <input name="password" type="password" required minlength="12" autocomplete="new-password" placeholder="{{ __('messages.admin.temporary_password') }}" class="rounded-lg border border-slate-200 px-4 py-3 font-bold">
                    <input name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" placeholder="{{ __('messages.admin.confirm_password') }}" class="rounded-lg border border-slate-200 px-4 py-3 font-bold">
                    <button class="rounded-lg bg-slate-950 px-5 py-3 font-black text-white lg:col-span-2">{{ __('messages.admin.create_user') }}</button>
                </form>
            @endif

            <details class="mt-5 rounded-xl border border-slate-200 p-4">
                <summary class="cursor-pointer font-black text-slate-800">{{ __('messages.admin.change_own_password') }}</summary>
                <p class="mt-2 text-sm font-bold text-slate-500">{{ __('messages.admin.change_own_password_help') }}</p>
                <form method="POST" action="{{ route('admin.password.change') }}" class="mt-4 grid gap-3 lg:grid-cols-3">
                    @csrf
                    <input name="current_password" type="password" required autocomplete="current-password" placeholder="{{ __('messages.admin.current_password') }}" class="rounded-lg border border-slate-200 px-4 py-3 font-bold">
                    <input name="password" type="password" required minlength="12" autocomplete="new-password" placeholder="{{ __('messages.admin.new_password') }}" class="rounded-lg border border-slate-200 px-4 py-3 font-bold">
                    <input name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" placeholder="{{ __('messages.admin.confirm_new_password') }}" class="rounded-lg border border-slate-200 px-4 py-3 font-bold">
                    <button class="rounded-lg bg-violet-700 px-5 py-3 font-black text-white lg:col-span-3">{{ __('messages.admin.update_password') }}</button>
                </form>
            </details>

            <details class="mt-5 rounded-xl border border-slate-200 p-4">
                <summary class="cursor-pointer font-black text-slate-800">{{ __('messages.admin.regenerate_own_otp') }}</summary>
                <p class="mt-2 text-sm font-bold text-slate-500">{{ __('messages.admin.regenerate_own_otp_help') }}</p>
                <form method="POST" action="{{ route('admin.otp.reset-own') }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                    @csrf
                    <input name="password" type="password" required autocomplete="current-password" placeholder="{{ __('messages.admin.password') }}" class="rounded-lg border border-slate-200 px-4 py-3 font-bold">
                    <input name="code" inputmode="numeric" required maxlength="6" placeholder="{{ __('messages.admin.new_otp_code') }}" class="rounded-lg border border-slate-200 px-4 py-3 font-bold">
                    <button class="rounded-lg bg-amber-600 px-5 py-3 font-black text-white sm:col-span-2">{{ __('messages.admin.regenerate_otp') }}</button>
                </form>
            </details>
        </section>

        <section class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-4">
                <h2 class="text-xl font-black">{{ __('messages.admin.activity_audit_title') }}</h2>
                <p class="mt-1 text-sm font-bold text-slate-500">{{ __('messages.admin.activity_audit_text') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[1280px] table-fixed divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                        <tr>
                            <th class="w-52 px-4 py-3">{{ __('messages.admin.operator') }}</th>
                            <th class="w-56 px-4 py-3">{{ __('messages.admin.action') }}</th>
                            <th class="w-40 px-4 py-3">{{ __('messages.admin.target') }}</th>
                            <th class="w-64 px-4 py-3">{{ __('messages.admin.before') }}</th>
                            <th class="w-64 px-4 py-3">{{ __('messages.admin.after') }}</th>
                            <th class="w-36 px-4 py-3">IP</th>
                            <th class="w-36 px-4 py-3">{{ __('messages.admin.date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentAdminAuditEvents as $event)
                            <tr>
                                <td class="px-4 py-3"><span class="font-black">{{ $event->actor_name }}</span><span class="block text-xs font-bold text-slate-500">{{ $event->actor_email }}</span></td>
                                <td class="break-words px-4 py-3 font-black text-violet-700">{{ $event->action }}</td>
                                <td class="break-words px-4 py-3 font-bold">{{ $event->target_label ?: $event->target_type.($event->target_id ? ' #'.$event->target_id : '') }}</td>
                                @foreach ([$event->old_values, $event->new_values] as $valueIndex => $auditValue)
                                    @php
                                        $items = $auditValues($auditValue);
                                    @endphp
                                    <td class="px-4 py-3 text-xs {{ $valueIndex === 0 ? 'text-slate-500' : 'text-slate-700' }}">
                                        @if ($items->isEmpty())
                                            <span class="font-bold">-</span>
                                        @else
                                            <dl class="space-y-2">
                                                @foreach ($items as $item)
                                                    <div>
                                                        <dt class="font-black text-slate-500">{{ $item['label'] }}</dt>
                                                        <dd class="mt-0.5 break-all font-bold" title="{{ $item['full'] }}">{{ $item['display'] }}</dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="whitespace-nowrap px-4 py-3 text-xs font-bold text-slate-500">{{ $event->ip_address ?: '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-xs font-bold text-slate-500">{{ $date($event->occurred_at) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center font-bold text-slate-500">{{ __('messages.admin.no_admin_activity') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($recentAdminAuditEvents, 'links'))
                @include('admin.partials.pagination', ['paginator' => $recentAdminAuditEvents, 'perPageName' => 'audit_per_page'])
            @endif
        </section>
        </div>

        <div id="admin-panel-legal" role="tabpanel" aria-labelledby="admin-tab-legal" data-admin-panel="legal" class="{{ $activeAdminTab === 'legal' ? '' : 'hidden' }}">
        <section id="legal-documents" class="mt-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl font-black">{{ __('messages.admin.legal_documents_title') }}</h2>
                    <p class="mt-1 max-w-4xl text-sm font-bold text-slate-500">{{ __('messages.admin.legal_documents_text') }}</p>
                </div>
                <div class="flex flex-col gap-2 sm:items-end">
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">{{ __('messages.admin.legal_document_language') }}</span>
                    <div class="flex rounded-lg bg-slate-100 p-1 text-xs font-black">
                        @foreach(['it' => 'language_italian', 'en' => 'language_english'] as $documentLocale => $languageKey)
                            <a href="{{ route('admin.dashboard', ['lang' => $documentLocale, 'tab' => 'legal', 'legal_group' => $activeLegalGroup]).'#legal-documents' }}" class="rounded-md px-3 py-2 {{ app()->getLocale() === $documentLocale ? 'bg-slate-950 text-white' : 'text-slate-500 hover:bg-white hover:text-slate-950' }}">{{ strtoupper($documentLocale) }}</a>
                        @endforeach
                    </div>
                    <span class="text-xs font-bold text-slate-500">{{ __('messages.admin.legal_editing_language', ['language' => __('messages.admin.language_'.(app()->getLocale() === 'en' ? 'english' : 'italian'))]) }}</span>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto" role="tablist" aria-label="{{ __('messages.admin.legal_categories') }}">
                <div class="flex min-w-max gap-2 rounded-xl bg-slate-100 p-1.5">
                    @foreach($legalGroups as $legalGroup)
                        <a
                            href="{{ route('admin.dashboard', ['lang' => app()->getLocale(), 'tab' => 'legal', 'legal_group' => $legalGroup]).'#legal-documents' }}"
                            role="tab"
                            aria-selected="{{ $activeLegalGroup === $legalGroup ? 'true' : 'false' }}"
                            tabindex="{{ $activeLegalGroup === $legalGroup ? '0' : '-1' }}"
                            data-legal-tab="{{ $legalGroup }}"
                            class="rounded-lg px-4 py-2.5 text-sm font-black transition focus:outline-none focus:ring-4 focus:ring-violet-200 {{ $activeLegalGroup === $legalGroup ? 'bg-white text-violet-700 shadow-sm' : 'text-slate-500 hover:text-slate-950' }}"
                        >{{ __('messages.admin.legal_group_'.$legalGroup) }}</a>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 grid gap-3">
                @foreach($legalDocuments as $documentKey => $legalDocument)
                    @php
                        $history = $legalDocumentHistory->get($documentKey);
                        $historyParameter = 'legal_'.$documentKey;
                        $editingDocument = old('document_key') === $documentKey || request()->hasAny([$historyParameter.'_page', $historyParameter.'_per_page']);
                        $documentGroup = config('legal.documents.'.$documentKey.'.group', 'policies');
                    @endphp
                    <details data-legal-panel="{{ $documentGroup }}" class="rounded-lg border border-slate-200 bg-slate-50 {{ $activeLegalGroup === $documentGroup ? '' : 'hidden' }}" @if($editingDocument) open @endif>
                        <summary class="flex cursor-pointer list-none flex-col gap-2 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <span>
                                <span class="block font-black text-slate-950">{{ $legalDocument->title }}</span>
                                <span class="mt-1 block text-xs font-bold text-slate-500">{{ $documentKey }} · {{ __('messages.legal_page.version') }} {{ $legalDocument->version }} · {{ $date($legalDocument->published_at) }}</span>
                            </span>
                            <span class="text-sm font-black text-violet-700">{{ __('messages.admin.legal_edit_publish') }}</span>
                        </summary>

                        <form method="POST" action="{{ route('admin.legal-documents.publish', ['document' => $documentKey]) }}" class="grid gap-4 border-t border-slate-200 bg-white p-4">
                            @csrf
                            <input type="hidden" name="document_key" value="{{ $documentKey }}">
                            <input type="hidden" name="locale" value="{{ app()->getLocale() }}">

                            <div class="grid gap-4 lg:grid-cols-[220px_1fr]">
                                <label>
                                    <span class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ __('messages.legal_page.version') }}</span>
                                    <input name="version" type="date" required value="{{ $editingDocument ? old('version', now()->toDateString()) : now()->toDateString() }}" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                                    <span class="mt-2 block text-xs font-bold leading-5 text-slate-500">{{ __('messages.admin.legal_version_help') }}</span>
                                </label>
                                <label>
                                    <span class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ __('messages.admin.legal_title') }}</span>
                                    <input name="title" required maxlength="255" value="{{ $editingDocument ? old('title', $legalDocument->title) : $legalDocument->title }}" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                                </label>
                            </div>

                            <label>
                                <span class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ __('messages.admin.legal_description') }}</span>
                                <input name="description" required maxlength="1000" value="{{ $editingDocument ? old('description', $legalDocument->description) : $legalDocument->description }}" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                            </label>

                            <label>
                                <span class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ __('messages.admin.legal_content_html') }}</span>
                                <textarea name="content_html" required rows="16" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-mono text-sm leading-6 outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">{{ $editingDocument ? old('content_html', $legalDocument->content_snapshot) : $legalDocument->content_snapshot }}</textarea>
                                <span class="mt-2 block text-xs font-bold leading-5 text-slate-500">{{ __('messages.admin.legal_html_help') }}</span>
                            </label>

                            <div class="flex flex-col gap-3">
                                <div class="text-xs font-bold text-slate-500">
                                    <span class="font-black text-slate-700">{{ __('messages.admin.legal_history') }}:</span>
                                    {{ $history->pluck('version')->join(' · ') ?: '-' }}
                                </div>
                                @include('admin.partials.pagination', ['paginator' => $history, 'perPageName' => $historyParameter.'_per_page'])
                                <button type="submit" class="self-end rounded-lg bg-slate-950 px-5 py-3 font-black text-white shadow-sm transition hover:bg-violet-700">
                                    {{ __('messages.admin.legal_publish_new_version') }}
                                </button>
                            </div>
                        </form>
                    </details>
                @endforeach
            </div>
        </section>
        </div>

        <div id="admin-panel-users" role="tabpanel" aria-labelledby="admin-tab-users" data-admin-panel="users" class="{{ $activeAdminTab === 'users' ? '' : 'hidden' }}">
        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="grid gap-3 lg:grid-cols-[1fr_180px_180px_auto] lg:items-end">
                <input type="hidden" name="tab" value="users">
                <div>
                    <label for="q" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ __('messages.admin.search_people') }}</label>
                    <input id="q" name="q" type="search" value="{{ $filters['q'] }}" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                </div>
                <div>
                    <label for="status" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ __('messages.admin.status') }}</label>
                    <select id="status" name="status" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                        <option value="all" @selected($filters['status'] === 'all')>{{ __('messages.admin.all') }}</option>
                        <option value="buyers" @selected($filters['status'] === 'buyers')>{{ __('messages.admin.bought') }}</option>
                        <option value="no_purchase" @selected($filters['status'] === 'no_purchase')>{{ __('messages.admin.no_purchase') }}</option>
                        <option value="pending" @selected($filters['status'] === 'pending')>{{ __('messages.admin.pending_verification') }}</option>
                        <option value="benefit_eligible" @selected($filters['status'] === 'benefit_eligible')>{{ __('messages.admin.waitlist_benefit_eligible') }}</option>
                    </select>
                </div>
                <div>
                    <label for="plan" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ __('messages.admin.pass') }}</label>
                    <select id="plan" name="plan" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                        <option value="all" @selected($filters['plan'] === 'all')>{{ __('messages.admin.all') }}</option>
                        <option value="join" @selected($filters['plan'] === 'join')>Join</option>
                        <option value="creator" @selected($filters['plan'] === 'creator')>Creator</option>
                    </select>
                </div>
                <button type="submit" class="rounded-lg wayout-purple px-5 py-3 font-black text-white shadow-[0_14px_30px_rgba(124,35,245,0.25)]">
                    {{ __('messages.admin.filter') }}
                </button>
            </form>
        </section>

        <section class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-1 border-b border-slate-200 p-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl font-black">{{ __('messages.admin.waitlist_people') }}</h2>
                    <p class="mt-1 text-sm font-bold text-slate-500">{{ __('messages.admin.results', ['count' => $waitlistEntries->total()]) }}</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Benefit ID</th>
                            <th class="px-4 py-3">{{ __('messages.admin.email') }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ __('messages.admin.waitlist_position') }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ __('messages.admin.email_verification') }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ __('messages.admin.marketing_consent') }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ __('messages.admin.status') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.pass') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.waitlist_benefit') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.orders') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.total') }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ __('messages.admin.signup') }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ __('messages.admin.latest_purchase') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($waitlistEntries as $entry)
                            @php
                                $plans = collect(explode(',', (string) $entry->plans))->filter()->map(fn ($plan) => $planName($plan))->join(', ');
                            @endphp
                            <tr class="hover:bg-violet-50/50">
                                <td class="px-4 py-3 font-mono text-xs" title="{{ $entry->id }}">{{ Str::limit($entry->id, 13) }}</td>
                                <td class="px-4 py-3 font-mono text-xs" title="{{ $entry->benefit_id }}">{{ Str::limit($entry->benefit_id, 13) }}</td>
                                <td class="px-4 py-3 font-black">{{ $entry->email }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-black">{{ $entry->waitlist_position ?: '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-black leading-none {{ $entry->email_verified_at ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $entry->email_verified_at ? __('messages.admin.email_verified') : __('messages.admin.pending_verification') }}
                                    </span>
                                    @if($entry->email_verified_at)
                                        <span class="mt-1 block text-xs font-bold text-slate-500">{{ $date($entry->email_verified_at) }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    @if ($entry->marketing_consent)
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-black leading-none text-emerald-800">{{ __('messages.admin.yes') }}</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-black leading-none text-slate-600">{{ __('messages.admin.no') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    @if (! $entry->email_verified_at)
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-black leading-none text-amber-800">{{ __('messages.admin.pending_verification') }}</span>
                                    @elseif ((int) $entry->orders_succeeded > 0)
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-black leading-none text-emerald-800">{{ __('messages.admin.bought') }}</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-black leading-none text-slate-600">{{ __('messages.admin.in_waitlist') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-700">{{ $plans ?: __('messages.admin.no_pass') }}</td>
                                <td class="px-4 py-3">
                                    @if($entry->email_verified_at && (int) $entry->orders_succeeded === 0)
                                        <span class="inline-flex rounded-full bg-violet-100 px-3 py-1 text-xs font-black text-violet-800">{{ __('messages.admin.waitlist_benefit_duration') }}</span>
                                    @else
                                        <span class="font-bold text-slate-400">{{ __('messages.admin.waitlist_benefit_not_eligible') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold">{{ __('messages.admin.successful_over_total', ['successful' => (int) $entry->orders_succeeded, 'total' => (int) $entry->orders_total]) }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-black">{{ $money((int) $entry->revenue_total) }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-500">{{ $date($entry->created_at) }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-500">{{ $date($entry->latest_purchase_at) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="px-4 py-10 text-center font-bold text-slate-500">{{ __('messages.admin.no_results') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $waitlistEntries, 'perPageName' => 'waitlist_per_page'])
        </section>
        </div>

        <div id="admin-panel-orders" role="tabpanel" aria-labelledby="admin-tab-orders" data-admin-panel="orders" class="{{ $activeAdminTab === 'orders' ? '' : 'hidden' }}">
        <section id="orders" class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-4">
                <h2 class="text-xl font-black">{{ __('messages.admin.recent_orders') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[1500px] divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('messages.admin.email') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.first_name') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.last_name') }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ __('messages.admin.birth_date') }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ __('messages.admin.pass') }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ __('messages.admin.amount') }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ __('messages.admin.status') }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ __('messages.admin.invoice') }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ __('messages.admin.invoice_progress') }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ __('messages.admin.invoice_actions') }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ __('messages.admin.date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentPurchases as $purchase)
                            <tr class="align-middle transition hover:bg-slate-50/70">
                                <td class="align-middle px-4 py-4 font-black">{{ $purchase->email }}</td>
                                <td class="align-middle px-4 py-4 font-bold text-slate-700">{{ $purchase->first_name ?: '-' }}</td>
                                <td class="align-middle px-4 py-4 font-bold text-slate-700">{{ $purchase->last_name ?: '-' }}</td>
                                <td class="align-middle whitespace-nowrap px-4 py-4 font-bold text-slate-500">{{ $birthDate($purchase->birth_date) }}</td>
                                <td class="align-middle px-4 py-4 font-bold">{{ $planName($purchase->plan) }}</td>
                                <td class="align-middle whitespace-nowrap px-4 py-4 font-black">{{ $money((int) $purchase->amount) }}</td>
                                <td class="align-middle whitespace-nowrap px-4 py-4 font-bold">{{ $purchase->status }}</td>
                                <td class="align-middle px-4 py-4 font-bold text-slate-700">
                                    @if($purchase->invoice_requested)
                                        <span class="font-black">{{ $purchase->billing_customer_type === 'legal_entity' ? __('messages.admin.legal_entity') : __('messages.admin.individual') }}</span>
                                        <span class="mt-1 block text-xs text-slate-500">
                                            {{ $purchase->billing_customer_type === 'legal_entity'
                                                ? (($purchase->company_name ?: '-').' · '.($purchase->vat_number ?: '-'))
                                                : ($purchase->fiscal_code ?: '-') }}
                                        </span>
                                    @else
                                        {{ __('messages.admin.no') }}
                                    @endif
                                </td>
                                <td class="align-middle px-4 py-4">
                                    @if($purchase->invoice_requested)
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-black {{ $invoiceStatusClass($purchase->electronic_invoice_status) }}">
                                            {{ $invoiceStatusLabel($purchase->electronic_invoice_status) }}
                                        </span>
                                        @if($purchase->qonto_invoice_number)
                                            <span class="mt-2 block font-black">{{ $purchase->qonto_invoice_number }}</span>
                                        @endif
                                        <span class="mt-1 block text-xs font-bold text-slate-500">
                                            Qonto: {{ $purchase->qonto_invoice_status ?: '-' }} · SdI: {{ $purchase->qonto_einvoicing_status ?: '-' }}
                                        </span>
                                        <span class="mt-1 block text-xs font-bold text-slate-400">{{ __('messages.admin.last_sync') }}: {{ $date($purchase->qonto_invoice_synced_at) }}</span>
                                        @if($purchase->qonto_invoice_error)
                                            <details class="mt-2 max-w-md rounded-lg bg-rose-50 p-2 text-xs text-rose-800">
                                                <summary class="cursor-pointer font-black">{{ __('messages.admin.invoice_error') }}</summary>
                                                <p class="mt-2 break-words font-mono leading-5">{{ $purchase->qonto_invoice_error }}</p>
                                            </details>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="align-middle px-4 py-4">
                                    @if($purchase->invoice_requested)
                                        <div class="flex min-w-36 flex-col gap-2">
                                            @if($purchase->electronic_invoice_status === 'failed')
                                                <form method="POST" action="{{ route('admin.purchases.invoice.retry', $purchase->id) }}">
                                                    @csrf
                                                    <button class="w-full rounded-lg bg-rose-700 px-3 py-2 text-xs font-black text-white">{{ __('messages.admin.retry_invoice') }}</button>
                                                </form>
                                            @endif
                                            @if($purchase->qonto_invoice_id)
                                                <form method="POST" action="{{ route('admin.purchases.invoice.sync', $purchase->id) }}">
                                                    @csrf
                                                    <button class="w-full rounded-lg bg-slate-950 px-3 py-2 text-xs font-black text-white">{{ __('messages.admin.sync_invoice') }}</button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="align-middle whitespace-nowrap px-4 py-4 font-bold text-slate-500">{{ $date($purchase->created_at) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-10 text-center font-bold text-slate-500">{{ __('messages.admin.no_orders') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $recentPurchases, 'perPageName' => 'orders_per_page'])
        </section>
        </div>

        <div id="admin-panel-withdrawals" role="tabpanel" aria-labelledby="admin-tab-withdrawals" data-admin-panel="withdrawals" class="{{ $activeAdminTab === 'withdrawals' ? '' : 'hidden' }}">
        <section class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-4">
                <h2 class="text-xl font-black">{{ __('messages.admin.withdrawal_requests') }}</h2>
                <p class="mt-1 text-sm font-bold text-slate-500">{{ __('messages.admin.withdrawal_requests_help') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('messages.admin.receipt_code') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.name') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.email') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.contract') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.status') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.refund_status') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.receipt_email') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentWithdrawals as $withdrawal)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 font-black">{{ $withdrawal->receipt_number }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold">{{ $withdrawal->first_name }} {{ $withdrawal->last_name }}</td>
                                <td class="px-4 py-3 font-bold">{{ $withdrawal->purchase_email }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold">{{ $withdrawal->order_reference }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold">{{ $withdrawal->status }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold">{{ $withdrawal->refund_status }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold">{{ $withdrawal->receipt_email_sent_at ? __('messages.admin.sent') : __('messages.admin.not_sent') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-500">{{ $date($withdrawal->submitted_at) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center font-bold text-slate-500">{{ __('messages.admin.no_withdrawals') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($recentWithdrawals, 'links'))
                @include('admin.partials.pagination', ['paginator' => $recentWithdrawals, 'perPageName' => 'withdrawals_per_page'])
            @endif
        </section>
        </div>

        <div id="admin-panel-consents" role="tabpanel" aria-labelledby="admin-tab-consents" data-admin-panel="consents" class="{{ $activeAdminTab === 'consents' ? '' : 'hidden' }}">
        <section class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-4">
                <h2 class="text-xl font-black">{{ __('messages.admin.consent_audit') }}</h2>
                <p class="mt-1 text-sm font-bold text-slate-500">{{ __('messages.admin.consent_audit_description') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('messages.admin.email') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.consent_type') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.action') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.source') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.document_versions') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.reference') }}</th>
                            <th class="px-4 py-3">IP</th>
                            <th class="px-4 py-3">{{ __('messages.admin.date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentConsentEvents as $event)
                            <tr>
                                <td class="px-4 py-3 font-black">{{ $event->subject_email }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold">{{ $event->consent_type }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-black {{ $event->action === 'granted' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $event->action }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-700">{{ $event->source }}</td>
                                <td class="px-4 py-3 font-bold text-slate-700">{{ $documentVersions($event->document_versions) ?: '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-700">{{ $consentReference($event) }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-500">{{ $event->ip_address ?: '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-500">{{ $date($event->occurred_at) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center font-bold text-slate-500">{{ __('messages.admin.no_consents') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $recentConsentEvents, 'perPageName' => 'consents_per_page'])
        </section>
        </div>

        <section data-admin-panel-extra="users" class="mt-6 grid gap-6 xl:grid-cols-2 {{ $activeAdminTab === 'users' ? '' : 'hidden' }}">
            @foreach (['join' => $joinBuyers, 'creator' => $creatorBuyers] as $planCode => $buyers)
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 p-4">
                        <h2 class="text-xl font-black">{{ __('messages.admin.plan_users', ['plan' => $planName($planCode)]) }}</h2>
                        <p class="mt-1 text-sm font-bold text-slate-500">{{ __('messages.admin.latest_users', ['count' => $number($buyers->count())]) }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                            <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">{{ __('messages.admin.email') }}</th>
                                    <th class="px-4 py-3">{{ __('messages.admin.name') }}</th>
                                    <th class="px-4 py-3">{{ __('messages.admin.orders') }}</th>
                                    <th class="px-4 py-3">{{ __('messages.admin.total') }}</th>
                                    <th class="px-4 py-3">{{ __('messages.admin.latest_purchase') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($buyers as $buyer)
                                    <tr>
                                        <td class="px-4 py-3 font-black">{{ $buyer->email }}</td>
                                        <td class="px-4 py-3 font-bold text-slate-700">{{ trim(($buyer->first_name ?? '').' '.($buyer->last_name ?? '')) ?: '-' }}</td>
                                        <td class="px-4 py-3 font-bold">{{ $number((int) $buyer->orders_total) }}</td>
                                        <td class="px-4 py-3 font-black">{{ $money((int) $buyer->revenue_total) }}</td>
                                        <td class="px-4 py-3 font-bold text-slate-500">{{ $date($buyer->latest_purchase_at) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-10 text-center font-bold text-slate-500">{{ __('messages.admin.no_plan_orders') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @include('admin.partials.pagination', ['paginator' => $buyers, 'perPageName' => $planCode.'_buyers_per_page'])
                </div>
            @endforeach
        </section>
    </main>

    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        document.addEventListener('DOMContentLoaded', () => {
            const adminTabs = [...document.querySelectorAll('[data-admin-tab]')];
            const adminPanels = [...document.querySelectorAll('[data-admin-panel]')];
            const adminExtras = [...document.querySelectorAll('[data-admin-panel-extra]')];
            const legalTabs = [...document.querySelectorAll('[data-legal-tab]')];
            const legalPanels = [...document.querySelectorAll('[data-legal-panel]')];

            document.querySelectorAll('[data-per-page]').forEach((select) => {
                select.addEventListener('change', () => {
                    const url = new URL(window.location.href);
                    url.searchParams.set(select.dataset.perPage, select.value);
                    url.searchParams.delete(select.dataset.pageName);
                    window.location.assign(url);
                });
            });

            const styleTab = (tab, active, dark = false) => {
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
                tab.tabIndex = active ? 0 : -1;

                const activeClasses = dark
                    ? ['bg-slate-950', 'text-white', 'shadow-sm']
                    : ['bg-white', 'text-violet-700', 'shadow-sm'];
                const inactiveClasses = dark
                    ? ['text-slate-500', 'hover:bg-slate-100', 'hover:text-slate-950']
                    : ['text-slate-500', 'hover:text-slate-950'];

                activeClasses.forEach((className) => tab.classList.toggle(className, active));
                inactiveClasses.forEach((className) => tab.classList.toggle(className, !active));
            };

            const activateAdminTab = (name, updateUrl = true) => {
                adminPanels.forEach((panel) => {
                    const active = panel.dataset.adminPanel === name;
                    panel.classList.toggle('hidden', !active);
                    panel.setAttribute('aria-hidden', active ? 'false' : 'true');
                });
                adminExtras.forEach((panel) => panel.classList.toggle('hidden', panel.dataset.adminPanelExtra !== name));
                adminTabs.forEach((tab) => styleTab(tab, tab.dataset.adminTab === name, true));

                if (updateUrl) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', name);
                    history.pushState({ adminTab: name }, '', url);
                }
            };

            const activateLegalTab = (name, updateUrl = true) => {
                legalPanels.forEach((panel) => panel.classList.toggle('hidden', panel.dataset.legalPanel !== name));
                legalTabs.forEach((tab) => styleTab(tab, tab.dataset.legalTab === name));

                if (updateUrl) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', 'legal');
                    url.searchParams.set('legal_group', name);
                    url.hash = 'legal-documents';
                    history.pushState({ adminTab: 'legal', legalTab: name }, '', url);
                }
            };

            const bindTabs = (tabs, attribute, activate) => {
                tabs.forEach((tab, index) => {
                    tab.addEventListener('click', (event) => {
                        event.preventDefault();
                        activate(tab.dataset[attribute]);
                    });
                    tab.addEventListener('keydown', (event) => {
                        if (! ['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;

                        event.preventDefault();
                        let targetIndex = index;
                        if (event.key === 'ArrowLeft') targetIndex = (index - 1 + tabs.length) % tabs.length;
                        if (event.key === 'ArrowRight') targetIndex = (index + 1) % tabs.length;
                        if (event.key === 'Home') targetIndex = 0;
                        if (event.key === 'End') targetIndex = tabs.length - 1;

                        tabs[targetIndex].focus();
                        activate(tabs[targetIndex].dataset[attribute]);
                    });
                });
            };

            bindTabs(adminTabs, 'adminTab', activateAdminTab);
            bindTabs(legalTabs, 'legalTab', activateLegalTab);

            window.addEventListener('popstate', () => {
                const url = new URL(window.location.href);
                activateAdminTab(url.searchParams.get('tab') || 'overview', false);
                activateLegalTab(url.searchParams.get('legal_group') || 'policies', false);
            });
        });
    </script>
</body>
</html>
