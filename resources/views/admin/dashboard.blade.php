@php
    $number = fn (?int $value) => number_format($value ?? 0, 0, ',', '.');
    $date = fn ($value) => $value
        ? \Illuminate\Support\Carbon::parse($value, 'UTC')->setTimezone(config('app.display_timezone'))->format('d/m/Y H:i')
        : '—';
    $retentionResult = fn ($run) => json_decode((string) $run->results, true) ?: [];
    $auditValues = fn ($value) => collect(json_decode((string) $value, true) ?: [])->map(function ($item, $key) {
        $full = (string) (is_bool($item) ? ($item ? 'true' : 'false') : (is_array($item) ? json_encode($item) : $item));
        $isHash = str_contains((string) $key, 'hash') && strlen($full) > 24;
        return ['label' => (string) $key, 'full' => $full, 'display' => $isHash ? substr($full, 0, 12).'…'.substr($full, -8) : $full];
    })->values();
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

        @include('admin.partials.waitlist')

        <h2 class="mt-6 text-xl font-black">{{ __('messages.admin.tab_settings') }}</h2>
        <div id="admin-panel-settings" data-admin-panel="settings">
        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-black">Piani e acquisti sul sito</h2>
                <span class="rounded-full px-3 py-1 text-xs font-black {{ $plansEnabled ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $plansEnabled ? 'Abilitati' : 'Disabilitati' }}</span>
            </div>
            <p class="mt-3 text-sm leading-6 text-slate-600">Abilita la pagina Piani, i pacchetti promo, gli abbonamenti e il pagamento. Disabilitando il flusso, i collegamenti vengono nascosti e tutti gli accessi diretti al catalogo e al checkout vengono bloccati, anche durante l’anteprima amministrativa.</p>
            <form method="POST" action="{{ route('admin.plans-availability.update') }}" class="mt-5 grid gap-3 sm:grid-cols-[1fr_auto]">
                @csrf
                <label class="grid gap-2 text-sm font-bold">Disponibilità dei piani
                    <select name="plans_enabled" class="rounded-lg border border-slate-200 bg-white px-4 py-3">
                        <option value="0" @selected(! $plansEnabled)>Disabilitati — nascosti e non accessibili</option>
                        <option value="1" @selected($plansEnabled)>Abilitati — visibili e acquistabili</option>
                    </select>
                </label>
                <button class="self-end rounded-lg bg-violet-700 px-5 py-3 font-black text-white">Salva disponibilità</button>
            </form>
        </section>
        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-xl font-black">{{ __('experience.store_links_title') }}</h2>
            <p class="mt-2 text-sm text-slate-500">{{ __('experience.store_links_help') }}</p>
            <form method="POST" action="{{ route('admin.app-store-links.update') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                @csrf
                @foreach(['apple' => 'App Store', 'google' => 'Google Play'] as $store => $label)
                    <label class="block"><span class="text-sm font-bold">{{ $label }}</span><input type="url" name="{{ $store }}" value="{{ old($store, $appStoreLinks[$store]) }}" maxlength="2048" placeholder="https://{{ $store === 'apple' ? 'apps.apple.com/…' : 'play.google.com/store/apps/…' }}" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3"></label>
                @endforeach
                <button class="rounded-lg bg-violet-700 px-5 py-3 font-black text-white sm:col-span-2 sm:justify-self-start">{{ __('messages.admin.apply') }}</button>
            </form>
        </section>
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

    </main>
</body>
</html>
