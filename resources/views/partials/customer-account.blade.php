<section class="mb-12 rounded-[2rem] border border-violet-200 bg-white p-6 shadow-lg sm:p-8" aria-labelledby="account-title">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div><p class="experience-kicker">{{ __('account.title') }}</p><h2 id="account-title" class="mt-2 text-3xl font-black">{{ __('account.hello', ['name' => $customerProfile['first_name'] ?? '']) }}</h2><p class="mt-2 text-slate-600">{{ $customerProfile['mobile_number'] ?? '' }}</p></div>
        <form method="POST" action="{{ route('customer.logout') }}">@csrf<button type="submit" class="rounded-full bg-slate-100 px-5 py-3 font-bold">{{ __('account.logout') }}</button></form>
    </div>
    @if(session('account_error'))<p class="mt-5 rounded-2xl bg-rose-50 p-4 font-semibold text-rose-800" role="alert">{{ session('account_error') }}</p>@endif
    <div class="mt-6 rounded-2xl bg-violet-50 p-5">
        <h3 class="text-xl font-black">{{ $customerAccount['offer']['name'] ?? __('account.no_plan') }}</h3>
        @if($customerAccount['subscription'])
            <dl class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div><dt class="text-sm text-slate-600">{{ __('account.source') }}</dt><dd class="font-bold">{{ $customerAccount['subscription']['source'] }}</dd></div>
                <div><dt class="text-sm text-slate-600">{{ __('account.status') }}</dt><dd class="font-bold">{{ __('account.statuses.'.$customerAccount['subscription']['status']) }}</dd></div>
                <div><dt class="text-sm text-slate-600">{{ __('account.ends_at') }}</dt><dd class="font-bold">{{ !empty($customerAccount['subscription']['end_at']) ? \Illuminate\Support\Carbon::parse($customerAccount['subscription']['end_at'])->timezone(config('app.display_timezone'))->format('d/m/Y H:i') : '—' }}</dd></div>
                <div><dt class="text-sm text-slate-600">{{ __('account.renewal') }}</dt><dd class="font-bold">{{ ($customerAccount['subscription']['auto_renew'] ?? false) ? __('account.renews') : __('account.does_not_renew') }}</dd></div>
            </dl>
        @endif
        @if($customerAccount['message'])<p data-account-purchase-message class="mt-4 font-semibold" role="status">{{ $customerAccount['message'] }}</p>@endif
        @if($customerAccount['managePortal'])
            <p class="mt-4 text-sm text-slate-600">{{ __('account.changes_immediate') }}</p>
            <form method="POST" action="{{ route('customer.portal') }}" class="mt-5">@csrf<button type="submit" class="experience-cta">{{ __('account.manage') }} ↗</button></form>
        @endif
    </div>
    <section id="subscription-history" class="mt-8 scroll-mt-28" aria-labelledby="subscription-history-title">
        <h3 id="subscription-history-title" class="text-xl font-black">{{ __('account.history') }}</h3>
        @if($customerAccount['historyError'])
            <p class="mt-3 text-slate-600" role="status">{{ __('account.history_unavailable') }}</p>
        @elseif(empty($customerAccount['history']))
            <p class="mt-3 text-slate-600">{{ __('account.history_empty') }}</p>
        @else
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
            @foreach($customerAccount['historyPagination'] as $entry)
                <article class="rounded-2xl border border-slate-200 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2"><h4 class="font-black">{{ $entry['offer']['name'] ?? __('account.unknown_offer') }}</h4>@if($entry['is_current'] ?? false)<span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-bold text-violet-800">{{ __('account.current') }}</span>@endif</div>
                    <p class="mt-2 text-sm">{{ $entry['source'] }} · {{ __('account.statuses.'.$entry['status']) }}</p>
                    <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        @foreach(['start_at' => 'starts_at', 'end_at' => 'ends_at'] as $dateKey => $label)
                            <div><dt class="text-slate-600">{{ __('account.'.$label) }}</dt><dd class="font-bold">{{ !empty($entry[$dateKey]) ? \Illuminate\Support\Carbon::parse($entry[$dateKey])->timezone(config('app.display_timezone'))->format('d/m/Y H:i') : '—' }}</dd></div>
                        @endforeach
                    </dl>
                    <p class="mt-3 text-sm text-slate-600">{{ ($entry['auto_renew'] ?? false) ? __('account.renews') : __('account.does_not_renew') }}</p>
                </article>
            @endforeach
            </div>
            @if($customerAccount['historyPagination']->hasPages())
                <nav class="mt-5 flex items-center justify-between gap-4" aria-label="{{ __('account.history_navigation') }}">
                    @if($customerAccount['historyPagination']->onFirstPage())
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-slate-300" aria-hidden="true">←</span>
                    @else
                        <a href="{{ $customerAccount['historyPagination']->previousPageUrl() }}" rel="prev" aria-label="{{ __('account.history_previous') }}" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-violet-200 font-bold text-violet-700 transition hover:bg-violet-50 focus-visible:outline-2 focus-visible:outline-violet-700"><span aria-hidden="true">←</span></a>
                    @endif
                    <span class="text-sm font-semibold text-slate-600">{{ __('account.history_page', ['current' => $customerAccount['historyPagination']->currentPage(), 'total' => $customerAccount['historyPagination']->lastPage()]) }}</span>
                    @if($customerAccount['historyPagination']->hasMorePages())
                        <a href="{{ $customerAccount['historyPagination']->nextPageUrl() }}" rel="next" aria-label="{{ __('account.history_next') }}" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-violet-200 font-bold text-violet-700 transition hover:bg-violet-50 focus-visible:outline-2 focus-visible:outline-violet-700"><span aria-hidden="true">→</span></a>
                    @else
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 text-slate-300" aria-hidden="true">→</span>
                    @endif
                </nav>
            @endif
        @endif
    </section>
</section>
