<div
    id="wayout-analytics-config"
    class="hidden"
    data-enabled="{{ config('analytics.enabled') ? '1' : '0' }}"
    data-consent-version="{{ config('analytics.consent_version') }}"
    data-consent-days="{{ config('analytics.consent_days') }}"
    data-gtm-id="{{ config('analytics.gtm_id') }}"
    data-ga4-id="{{ config('analytics.ga4_id') }}"
    data-meta-pixel-id="{{ config('analytics.meta_pixel_id') }}"
    data-debug="{{ config('analytics.debug') ? '1' : '0' }}"
    data-consent-endpoint="{{ route('cookie-consent.store') }}"
></div>

<section id="cookie-consent-banner" class="fixed inset-x-3 bottom-3 z-[70] hidden sm:inset-x-5 sm:bottom-5" role="dialog" aria-modal="false" aria-labelledby="cookie-consent-title">
    <div class="relative mx-auto max-w-5xl rounded-[2rem] border border-white/15 bg-slate-950 p-5 text-white shadow-[0_30px_100px_rgba(15,23,42,0.48)] sm:p-6">
        <button id="cookie-consent-close" type="button" class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-slate-300 transition hover:bg-white/15 hover:text-white" aria-label="{{ __('messages.cookie_consent.continue_necessary') }}" title="{{ __('messages.cookie_consent.continue_necessary') }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
            <div class="pr-10">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-violet-200">WAYOUT</p>
                <h2 id="cookie-consent-title" class="mt-2 text-xl font-black sm:text-2xl">{{ __('messages.cookie_consent.title') }}</h2>
                <p class="mt-3 max-w-3xl text-sm font-medium leading-6 text-slate-300">
                    {{ __('messages.cookie_consent.text') }}
                    <a class="font-black text-white underline underline-offset-4" href="{{ route('legal.cookies') }}">{{ __('messages.legal.cookies') }}</a>
                </p>
            </div>
            <div class="grid gap-2 sm:grid-cols-3 lg:min-w-[31rem]">
                <button id="cookie-consent-reject" type="button" class="min-h-12 rounded-full border border-white/20 px-5 text-sm font-black text-white transition hover:bg-white/10">{{ __('messages.cookie_consent.reject') }}</button>
                <button id="cookie-consent-customize" type="button" class="min-h-12 rounded-full border border-violet-300/50 bg-violet-400/10 px-5 text-sm font-black text-violet-100 transition hover:bg-violet-400/20">{{ __('messages.cookie_consent.customize') }}</button>
                <button id="cookie-consent-accept" type="button" class="min-h-12 rounded-full bg-white px-5 text-sm font-black text-slate-950 transition hover:bg-violet-100">{{ __('messages.cookie_consent.accept') }}</button>
            </div>
        </div>
    </div>
</section>

<div id="cookie-preferences-modal" class="fixed inset-0 z-[80] hidden items-center justify-center overflow-y-auto px-4 py-6">
    <button id="cookie-preferences-backdrop" type="button" class="fixed inset-0 cursor-default bg-slate-950/75 backdrop-blur-md" aria-label="{{ __('messages.nav.close_menu') }}"></button>
    <section role="dialog" aria-modal="true" aria-labelledby="cookie-preferences-title" class="relative z-10 my-auto max-h-[calc(100dvh-3rem)] w-full max-w-2xl overflow-y-auto rounded-[2rem] bg-white p-5 shadow-2xl sm:p-7">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-700">WAYOUT</p>
                <h2 id="cookie-preferences-title" class="mt-2 text-2xl font-black text-slate-950">{{ __('messages.cookie_consent.preferences_title') }}</h2>
            </div>
            <button id="cookie-preferences-close" type="button" class="rounded-full bg-slate-100 p-2 text-slate-500 transition hover:bg-slate-200 hover:text-slate-950" aria-label="{{ __('messages.nav.close_menu') }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="mt-4 text-sm font-medium leading-6 text-slate-600">{{ __('messages.cookie_consent.preferences_text') }}</p>
        <div class="mt-6 space-y-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-start justify-between gap-4">
                    <div><h3 class="font-black text-slate-950">{{ __('messages.cookie_consent.necessary_title') }}</h3><p class="mt-1 text-sm leading-6 text-slate-600">{{ __('messages.cookie_consent.necessary_text') }}</p></div>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-800">{{ __('messages.cookie_consent.always_active') }}</span>
                </div>
            </div>
            <label class="flex cursor-pointer items-start justify-between gap-4 rounded-2xl border border-slate-200 p-4">
                <span><span class="block font-black text-slate-950">{{ __('messages.cookie_consent.analytics_title') }}</span><span class="mt-1 block text-sm leading-6 text-slate-600">{{ __('messages.cookie_consent.analytics_text') }}</span></span>
                <input id="cookie-consent-analytics" type="checkbox" class="mt-1 h-6 w-6 shrink-0 rounded border-slate-300 text-violet-700 focus:ring-violet-500">
            </label>
            <label class="flex cursor-pointer items-start justify-between gap-4 rounded-2xl border border-slate-200 p-4">
                <span><span class="block font-black text-slate-950">{{ __('messages.cookie_consent.marketing_title') }}</span><span class="mt-1 block text-sm leading-6 text-slate-600">{{ __('messages.cookie_consent.marketing_text') }}</span></span>
                <input id="cookie-consent-marketing" type="checkbox" class="mt-1 h-6 w-6 shrink-0 rounded border-slate-300 text-violet-700 focus:ring-violet-500">
            </label>
        </div>
        <div class="mt-6 grid gap-2 sm:grid-cols-3">
            <button id="cookie-preferences-reject" type="button" class="min-h-14 rounded-full border border-slate-300 px-5 text-sm font-black text-slate-700 transition hover:bg-slate-100">{{ __('messages.cookie_consent.reject') }}</button>
            <button id="cookie-preferences-save" type="button" class="min-h-14 rounded-full bg-slate-950 px-5 text-sm font-black text-white transition hover:bg-violet-700">{{ __('messages.cookie_consent.save') }}</button>
            <button id="cookie-preferences-accept" type="button" class="min-h-14 rounded-full bg-violet-700 px-5 text-sm font-black text-white transition hover:bg-violet-800">{{ __('messages.cookie_consent.accept') }}</button>
        </div>
        <p class="mt-4 text-center text-xs font-medium leading-5 text-slate-500">{{ __('messages.cookie_consent.revocation_note') }}</p>
    </section>
</div>
