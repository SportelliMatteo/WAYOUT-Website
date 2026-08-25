@php
    $money = fn (?int $amount) => number_format(($amount ?? 0) / 100, 2, ',', '.') . '€';
    $number = fn (?int $value) => number_format($value ?? 0, 0, ',', '.');
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
                <p class="mt-1 text-sm font-bold text-slate-500">{{ __('messages.admin.of_spots', ['count' => $number($capacities['waitlist_capacity'])]) }}</p>
                <p class="mt-1 text-xs font-bold text-amber-700">{{ __('messages.admin.pending_count', ['count' => $number($stats['waitlist_pending'])]) }} · {{ __('messages.admin.claimed_count', ['count' => $number($stats['benefits_claimed'])]) }}</p>
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
                <p class="mt-1 text-sm font-bold text-slate-500">{{ $number($stats['join_orders']) }} / {{ $number($capacities['join_capacity']) }} pass</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Creator Pass</p>
                <p class="mt-3 text-3xl font-black">{{ $money($stats['creator_revenue']) }}</p>
                <p class="mt-1 text-sm font-bold text-slate-500">{{ $number($stats['creator_orders']) }} / {{ $number($capacities['creator_capacity']) }} pass</p>
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
                    <h2 class="text-xl font-black">{{ __('messages.admin.settings_title') }}</h2>
                <p class="text-sm font-bold text-slate-500">{{ __('messages.admin.settings_text') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-4 grid gap-3 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">
                @csrf
                <div>
                    <label for="waitlist_capacity" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Waitlist</label>
                    <input id="waitlist_capacity" name="waitlist_capacity" type="number" min="0" max="1000000" value="{{ $capacities['waitlist_capacity'] }}" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                </div>
                <div>
                    <label for="join_capacity" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Join Pass</label>
                    <input id="join_capacity" name="join_capacity" type="number" min="0" max="1000000" value="{{ $capacities['join_capacity'] }}" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                </div>
                <div>
                    <label for="creator_capacity" class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Creator Pass</label>
                    <input id="creator_capacity" name="creator_capacity" type="number" min="0" max="1000000" value="{{ $capacities['creator_capacity'] }}" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3 font-bold outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10">
                </div>
                <button type="submit" class="rounded-lg bg-slate-950 px-5 py-3 font-black text-white shadow-sm transition hover:bg-violet-700">
                    {{ __('messages.admin.save') }}
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

            @if($adminUsers->count() < config('admin.max_users'))
                <form method="POST" action="{{ route('admin.users.store') }}" class="mt-5 grid gap-3 rounded-xl bg-slate-50 p-4 lg:grid-cols-2">
                    @csrf
                    <h3 class="font-black lg:col-span-2">{{ __('messages.admin.add_admin_user') }} ({{ $adminUsers->count() }}/{{ config('admin.max_users') }})</h3>
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
        </section>
        </div>

        <div id="admin-panel-legal" role="tabpanel" aria-labelledby="admin-tab-legal" data-admin-panel="legal" class="{{ $activeAdminTab === 'legal' ? '' : 'hidden' }}">
        <section id="legal-documents" class="mt-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl font-black">{{ __('messages.admin.legal_documents_title') }}</h2>
                    <p class="mt-1 max-w-4xl text-sm font-bold text-slate-500">{{ __('messages.admin.legal_documents_text') }}</p>
                </div>
                <span class="rounded-full bg-violet-100 px-4 py-2 text-xs font-black uppercase tracking-[0.14em] text-violet-800">{{ strtoupper(app()->getLocale()) }}</span>
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
                        $editingDocument = old('document_key') === $documentKey;
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

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="text-xs font-bold text-slate-500">
                                    <span class="font-black text-slate-700">{{ __('messages.admin.legal_history') }}:</span>
                                    {{ ($legalDocumentHistory->get($documentKey) ?? collect())->take(5)->pluck('version')->join(' · ') }}
                                </div>
                                <button type="submit" class="rounded-lg bg-slate-950 px-5 py-3 font-black text-white shadow-sm transition hover:bg-violet-700">
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
                        <option value="claimed" @selected($filters['status'] === 'claimed')>{{ __('messages.admin.benefit_claimed') }}</option>
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
                            <th class="px-4 py-3">{{ __('messages.admin.benefit_claim') }}</th>
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
                                    @if($entry->claimed_at)
                                        <span class="inline-flex rounded-full bg-violet-100 px-3 py-1 text-xs font-black text-violet-800">{{ $entry->claimed_benefit_type }}</span>
                                        <span class="mt-1 block text-xs font-bold text-slate-500">{{ $entry->account_reference }} · {{ $date($entry->claimed_at) }}</span>
                                    @else
                                        <span class="font-bold text-slate-400">{{ __('messages.admin.not_claimed') }}</span>
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
            <div class="border-t border-slate-200 p-4">
                {{ $waitlistEntries->links() }}
            </div>
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
