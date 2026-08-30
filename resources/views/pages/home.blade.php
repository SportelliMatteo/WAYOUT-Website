@extends('layouts.app', [
    'title' => __('messages.home.title'),
    'description' => __('messages.home.description')
])

@section('content')
@php
    $capacity = fn (string $key) => number_format(($founderCapacities[$key] ?? config('founder.default_capacities')[$key]), 0, ',', '.');
    $waitlistFull = $founderAvailability['waitlist']['is_full'] ?? false;
    $joinOfferPackage = $founderPackages['join'] ?? null;
    $creatorOfferPackage = $founderPackages['creator'] ?? null;
    $founderOfferCatalogError = $founderCatalogError ?? null;
    $offerPrice = fn (?array $package) => $package ? number_format((float) $package['price'], 2, ',', '.').' €' : '—';
    $offerAvailability = function (?array $package) {
        if (!$package || ($package['available'] ?? null) === 0) return __('messages.home.sold_out');
        $maximum = $package['max_available_quantity'] ?? null;
        if (!is_numeric($maximum)) return '—';
        if ((int) $maximum === -1) return __('messages.home.unlimited_availability');
        return __('messages.home.total_passes', ['count' => number_format((int) $maximum, 0, ',', '.')]);
    };
    $joinOfferFull = !$joinOfferPackage || ($joinOfferPackage['available'] ?? null) === 0;
    $creatorOfferFull = !$creatorOfferPackage || ($creatorOfferPackage['available'] ?? null) === 0;
    $founderOfferUnavailable = $joinOfferFull && $creatorOfferFull;
@endphp
<section class="relative overflow-x-clip">
    <div class="wayout-shell grid items-center gap-8 py-8 sm:py-10 lg:min-h-[calc(100vh-6rem)] lg:grid-cols-[1.02fr_0.98fr] lg:gap-12 lg:py-16">
        <div class="relative z-10 text-center lg:text-left">
            <span class="inline-flex max-w-full items-center rounded-full border border-violet-200 bg-white/80 px-4 py-2 text-sm font-bold text-violet-700 shadow-sm">
                {{ __('messages.home.eyebrow') }}
            </span>
            <h1 class="mx-auto mt-6 max-w-4xl text-4xl font-black leading-[0.96] text-slate-950 sm:text-6xl lg:mx-0 lg:text-7xl">
                {{ __('messages.home.headline_a') }} <span class="wayout-gradient-text">{{ __('messages.home.headline_b') }}</span>
            </h1>
            <p class="mx-auto mt-5 max-w-2xl text-base font-medium leading-7 text-slate-600 sm:mt-6 sm:text-xl sm:leading-8 lg:mx-0">
                {{ __('messages.home.intro') }}
            </p>

            <div class="mx-auto mt-7 max-w-2xl overflow-hidden rounded-[2rem] bg-slate-950 text-white shadow-[0_26px_80px_rgba(15,23,42,0.20)] sm:mt-8 lg:mx-0">
                <div class="bg-[radial-gradient(circle_at_12%_0%,rgba(124,35,245,0.62),transparent_22rem),radial-gradient(circle_at_95%_10%,rgba(185,255,74,0.20),transparent_18rem)] p-4 sm:p-5">
                    <div class="flex flex-col items-center gap-4 text-center lg:items-start lg:text-left">
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-200">{{ __('messages.home.early_access') }}</p>
                            <p class="mt-2 text-xl font-black leading-tight sm:text-2xl">{{ $waitlistFull ? __('messages.home.waitlist_full') : __('messages.home.waitlist_open') }}</p>
                        </div>
                        <div class="flex w-full flex-wrap justify-center gap-2 text-center text-xs font-black text-slate-950 sm:text-sm lg:justify-start">
                            <div class="min-w-[6.25rem] rounded-2xl bg-white px-3 py-2"><span class="block text-base sm:text-lg">{{ $capacity('waitlist_capacity') }}</span>{{ __('messages.home.spots') }}</div>
                            <div class="min-w-[6.25rem] rounded-2xl bg-white px-3 py-2"><span class="block text-base sm:text-lg">60</span>{{ __('messages.home.days') }}</div>
                            <div class="min-w-[6.25rem] rounded-2xl wayout-lime px-3 py-2"><span class="block text-base sm:text-lg">{{ __('messages.home.founder_pass_short') }}</span>{{ __('messages.home.founder_pass_short2') }}</div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 leading-tight sm:text-sm">
                            {{ __('messages.home.text_down_chip') }} {{ $capacity('waitlist_capacity') }}{{ __('messages.home.text_down_chip2') }}
                        </p>
                    </div>

                    <form id="waitlist-join-form" method="POST" action="{{ route('waitlist.store') }}" class="mt-5 space-y-3">
                        @csrf
                        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('messages.home.email_placeholder') }}" required autocomplete="email" class="min-h-14 w-full rounded-full border border-white/10 bg-white px-5 text-base font-bold text-slate-950 placeholder-slate-400 outline-none transition focus:ring-4 focus:ring-violet-300/40" />
                        @error('email')
                            <p class="px-2 text-left text-sm font-semibold text-rose-200">{{ $message }}</p>
                        @enderror
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 text-left">
                            <input type="checkbox" name="waitlist_terms_accepted" value="1" required @checked(old('waitlist_terms_accepted')) class="mt-0.5 h-5 w-5 shrink-0 rounded border-white/20 text-violet-600 focus:ring-violet-300" />
                            <span class="text-sm font-medium leading-6 text-slate-200 [&_a]:font-black [&_a]:text-violet-200 [&_a]:underline [&_a]:underline-offset-4">{!! $legalDocumentsHtml['waitlist_acceptance'] !!}</span>
                        </label>
                        @error('waitlist_terms_accepted')
                            <p class="px-2 text-left text-sm font-semibold text-rose-200">{{ $message }}</p>
                        @enderror
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 text-left">
                            <input type="checkbox" name="marketing_consent" value="1" @checked(old('marketing_consent')) class="mt-0.5 h-5 w-5 shrink-0 rounded border-white/20 text-violet-600 focus:ring-violet-300" />
                            <span class="text-sm font-medium leading-6 text-slate-200">{!! $legalDocumentsHtml['marketing'] !!}</span>
                        </label>
                        <button id="waitlist-join-submit" type="submit" class="inline-flex min-h-14 w-full items-center justify-center gap-3 rounded-full wayout-purple px-7 text-base font-black text-white shadow-[0_18px_40px_rgba(124,35,245,0.32)] transition hover:scale-[1.01] disabled:cursor-wait disabled:opacity-80">
                            <span class="waitlist-submit-text">{{ __('messages.home.join_button') }}</span>
                            <span class="waitlist-submit-loader hidden h-5 w-5 animate-spin rounded-full border-2 border-white/35 border-t-white" aria-hidden="true"></span>
                        </button>
                    </form>

                    @if($waitlistFull)
                        <p class="mt-3 rounded-2xl bg-white/10 px-4 py-3 text-sm font-bold text-violet-100">
                            {{ __('messages.home.waitlist_closed_message') }}
                        </p>
                    @endif

                    @if(session('waitlist_error'))
                        <p class="mt-3 px-2 text-sm font-semibold text-rose-200">{{ session('waitlist_error') }}</p>
                    @endif
                </div>
                
            </div>
        </div>

        <div class="relative mx-auto flex w-full max-w-[560px] flex-col items-center pb-4 pt-2 lg:max-w-full lg:pb-10 lg:pt-4">
            <div class="absolute left-1/2 top-1/2 -z-10 h-[min(80vw,360px)] w-[min(80vw,360px)] -translate-x-1/2 -translate-y-1/2 rounded-full bg-violet-500/18 blur-3xl sm:h-[510px] sm:w-[510px]"></div>
            <img src="/images/screen/1.png" alt="Schermata esplora di WAYOUT" class="phone-float pulse-glow relative z-10 w-[min(72vw,285px)] max-w-full rounded-[2.5rem] shadow-[0_34px_90px_rgba(15,23,42,0.22)] sm:w-[min(58vw,370px)] sm:rounded-[3rem] lg:w-[min(72%,390px)] xl:w-[410px]" />
            
            <div class="absolute left-0 top-16 z-20 w-[min(88%,270px)] rounded-[2rem] bg-slate-950 p-5 text-white shadow-2xl xl:block">
                <p class="text-sm font-bold text-violet-200">{{ __('messages.home.live_milan') }}</p>
                <p class="mt-2 text-4xl font-black">48</p>
                <p class="mt-1 text-sm text-slate-300">{{ __('messages.home.events_this_week') }}</p>
            </div>
            <div class="relative z-20 mt-[-3.5rem] w-[min(88%,270px)] rounded-[2rem] border border-white/70 bg-white/[0.9] p-4 shadow-2xl backdrop-blur-xl sm:mt-[-4.5rem] sm:p-5 lg:absolute lg:bottom-10 lg:right-0 lg:mt-0 lg:w-[250px]">
                <p class="text-sm font-black text-slate-950">{{ __('messages.home.matteo_table') }}</p>
                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">{{ __('messages.home.available') }}</p>
                        <p class="text-3xl font-black text-violet-700">{{ __('messages.home.four_spots') }}</p>
                    </div>
                    <span class="rounded-full wayout-lime px-3 py-2 text-xs font-black text-slate-950">Join</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="features" class="py-10 lg:py-20">
    <div class="wayout-shell">
        <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
            <div>
                <p class="text-sm font-black uppercase tracking-[0.28em] text-violet-700">{{ __('messages.home.how_it_works') }}</p>
                <h2 class="mt-4 text-3xl font-black leading-tight text-slate-950 sm:text-5xl">{{ __('messages.home.features_title') }}</h2>
            </div>
            <p class="text-base font-medium leading-7 text-slate-600 sm:text-lg sm:leading-8">
                {{ __('messages.home.features_intro') }}
            </p>
        </div>

        <div class="mt-8 grid gap-5 md:grid-cols-3 lg:mt-10">
            <article class="wayout-card rounded-[2rem] p-6 transition hover:-translate-y-1 hover:shadow-[0_24px_70px_rgba(124,35,245,0.14)]">
                <div class="flex h-12 w-12 items-center justify-center rounded-full wayout-purple text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10h.01"/></svg>
                </div>
                <h3 class="mt-6 text-2xl font-black text-slate-950">{{ __('messages.home.feature_1_title') }}</h3>
                <p class="mt-3 leading-7 text-slate-600">{{ __('messages.home.feature_1_text') }}</p>
            </article>
            <article class="wayout-card rounded-[2rem] p-6 transition hover:-translate-y-1 hover:shadow-[0_24px_70px_rgba(124,35,245,0.14)]">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-950 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
                </div>
                <h3 class="mt-6 text-2xl font-black text-slate-950">{{ __('messages.home.feature_2_title') }}</h3>
                <p class="mt-3 leading-7 text-slate-600">{{ __('messages.home.feature_2_text') }}</p>
            </article>
            <article class="wayout-card rounded-[2rem] p-6 transition hover:-translate-y-1 hover:shadow-[0_24px_70px_rgba(124,35,245,0.14)]">
                <div class="flex h-12 w-12 items-center justify-center rounded-full wayout-lime text-slate-950">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v7a2 2 0 01-2 2H8l-5 3V10a2 2 0 012-2h2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3h6a2 2 0 012 2v6a2 2 0 01-2 2H9l-4 2V5a2 2 0 012-2z"/></svg>
                </div>
                <h3 class="mt-6 text-2xl font-black text-slate-950">{{ __('messages.home.feature_3_title') }}</h3>
                <p class="mt-3 leading-7 text-slate-600">{{ __('messages.home.feature_3_text') }}</p>
            </article>
        </div>
    </div>
</section>

<section class="overflow-hidden py-10 lg:py-20">
    <div class="wayout-shell">
        <div class="relative overflow-hidden rounded-[2rem] bg-slate-950 p-5 text-white shadow-[0_30px_100px_rgba(15,23,42,0.22)] sm:rounded-[2.5rem] sm:p-10 lg:p-14">
            <div class="absolute inset-0 rounded-[2rem] bg-[radial-gradient(circle_at_20%_0%,rgba(124,35,245,0.55),transparent_34rem),radial-gradient(circle_at_88%_10%,rgba(185,255,74,0.25),transparent_22rem)] sm:rounded-[2.5rem]"></div>
            <div class="relative grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.28em] text-violet-200">{{ __('messages.home.app_experience') }}</p>
                    <h2 class="mt-4 text-3xl font-black leading-tight sm:text-5xl">{{ __('messages.home.app_title') }}</h2>
                    <p class="mt-5 text-base leading-7 text-slate-300 sm:text-lg sm:leading-8">{{ __('messages.home.app_text') }}</p>
                </div>
                <div id="feature-slideshow" class="relative h-[340px] sm:h-[460px] lg:h-[520px]">
                    <img src="/images/screen/2.png" alt="Organizzazione evento WAYOUT" class="absolute inset-0 h-full w-full object-contain opacity-100 transition duration-700" />
                    <img src="/images/screen/3.png" alt="Tavoli e prezzi WAYOUT" class="absolute inset-0 h-full w-full object-contain opacity-0 transition duration-700" />
                    <img src="/images/screen/4.png" alt="Evento privato WAYOUT" class="absolute inset-0 h-full w-full object-contain opacity-0 transition duration-700" />
                </div>
            </div>
        </div>
    </div>
</section>

@if(session('waitlist_verification_sent'))
    <span class="hidden" data-analytics-page-event="waitlist_signup_requested" data-analytics-dedupe="waitlist_signup_requested"></span>
    <div id="waitlist-verification-sent" class="fixed inset-0 z-[60] flex items-center justify-center px-4 py-8">
        <button id="waitlist-verification-sent-backdrop" type="button" class="fixed inset-0 cursor-default bg-slate-950/75 backdrop-blur-md" aria-label="{{ __('messages.nav.close_menu') }}"></button>
        <div
            id="waitlist-verification-sent-panel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="waitlist-verification-sent-title"
            aria-describedby="waitlist-verification-sent-description"
            class="relative z-10 w-full max-w-lg rounded-[2rem] border border-white/15 bg-slate-950 p-6 text-center text-white opacity-0 shadow-[0_30px_100px_rgba(15,23,42,0.45)] transition-all duration-300 sm:p-9"
        >
            <button id="waitlist-verification-sent-close" type="button" data-modal-initial-focus aria-label="{{ __('messages.nav.close_menu') }}" class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-slate-300 transition hover:bg-white/15 hover:text-white focus:outline-none focus:ring-4 focus:ring-violet-300/30">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-400/15 text-emerald-300">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
            </div>
            <p class="mt-5 text-xs font-black uppercase tracking-[0.24em] text-violet-200">WAYOUT</p>
            <h2 id="waitlist-verification-sent-title" class="mt-3 text-3xl font-black leading-tight">{{ __('messages.home.verification_sent_title') }}</h2>
            <p id="waitlist-verification-sent-description" class="mt-4 text-base font-semibold leading-7 text-slate-300">{{ __('messages.home.verification_sent') }}</p>
            <p class="mt-3 text-sm font-medium leading-6 text-slate-400">{{ __('messages.home.verification_sent_hint') }}</p>
            <button id="waitlist-verification-sent-confirm" type="button" class="mt-7 inline-flex min-h-14 w-full items-center justify-center rounded-full bg-white px-6 text-base font-black text-slate-950 transition hover:bg-violet-100 focus:outline-none focus:ring-4 focus:ring-violet-300/30">
                {{ __('messages.home.verification_sent_button') }}
            </button>
        </div>
    </div>
@endif

@if(session('waitlist_offer'))
    @php
        $alreadyRegistered = session('waitlist_status') === 'already_registered';
        $purchasedPlan = session('purchased_plan');
    @endphp
    <span
        class="hidden"
        data-analytics-page-event="{{ $alreadyRegistered ? 'waitlist_returning_member' : 'waitlist_email_verified' }}"
        data-analytics-params='@json(['has_founder_pass' => (bool) $purchasedPlan, 'plan' => $purchasedPlan['code'] ?? ''])'
        data-analytics-dedupe="{{ $alreadyRegistered ? 'waitlist_returning_member' : 'waitlist_email_verified' }}"
    ></span>
    <span class="hidden" data-analytics-page-event="founder_offer_view" data-analytics-params='@json(['returning' => $alreadyRegistered])'></span>
    <div id="waitlist-offer" data-show="1" class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden px-3 py-4 sm:px-4 sm:py-8">
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-md" aria-hidden="true"></div>
        <div class="relative z-10 flex w-full justify-center">
        <div id="waitlist-offer-panel" role="dialog" aria-modal="true" aria-labelledby="waitlist-offer-title" style="max-height: min(820px, calc(100dvh - 7rem));" class="flex w-full max-w-4xl flex-col overflow-hidden rounded-[2rem] border border-white/15 bg-slate-950/95 p-4 text-white opacity-0 shadow-2xl transition-all duration-300 sm:rounded-[2.5rem] sm:p-8">
            <div class="mb-5 flex shrink-0 items-center justify-between gap-4">
                <span class="inline-flex rounded-full bg-violet-500/20 px-4 py-2 text-xs font-black uppercase tracking-[0.26em] text-violet-100">{{ __('messages.home.waitlist') }}</span>
                <button id="waitlist-close-x" aria-label="{{ __('messages.nav.close_menu') }}" class="shrink-0 rounded-full bg-white/10 p-2 text-slate-300 transition hover:bg-white/15 hover:text-white">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto pr-1 sm:pr-2">
            <div class="space-y-5 pb-2">
                <div class="min-w-0">
                    <h3 id="waitlist-offer-title" class="text-3xl font-black leading-tight tracking-tight sm:text-4xl">
                        @if($purchasedPlan)
                            {{ __('messages.home.already_purchased_title') }}
                        @else
                            {{ $alreadyRegistered ? __('messages.home.already_registered_title') : __('messages.home.confirmed_title') }}
                        @endif
                    </h3>
                    <p class="mt-4 break-words text-base leading-7 text-slate-300 sm:text-lg sm:leading-8">
                        @if($purchasedPlan)
                            {!! __('messages.home.already_purchased_text', ['email' => e(session('waitlist_email')), 'plan' => '<strong>'.e($purchasedPlan['name']).'</strong>', 'amount' => e(number_format($purchasedPlan['amount'] / 100, 2, ',', '.').'€')]) !!}
                        @elseif($alreadyRegistered)
                            {{ __('messages.home.already_registered_text', ['email' => session('waitlist_email')]) }}
                        @else
                            {{ __('messages.home.confirmed_text', ['count' => $capacity('waitlist_capacity')]) }}
                        @endif
                    </p>
                    <p class="mt-4 break-words text-sm leading-6 text-slate-400">
                        @if(!$alreadyRegistered)
                            {{ __('messages.home.details_confirmed_text') }}
                        @endif
                    </p>
                    @if($purchasedPlan)
                        <div class="mt-5 rounded-[2rem] bg-white/[0.08] p-5">
                            <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-200">{{ __('messages.home.purchase_summary') }}</p>
                            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                                <div class="rounded-2xl bg-white/[0.08] p-4">
                                    <dt class="font-bold text-slate-400">{{ __('messages.home.purchased_pass') }}</dt>
                                    <dd class="mt-1 text-lg font-black text-white">{{ ($purchasedPlan['code'] ?? null) === 'creator' ? __('messages.home.creator_pass_summary') : __('messages.home.join_pass_summary') }}</dd>
                                </div>
                                <div class="rounded-2xl bg-white/[0.08] p-4">
                                    <dt class="font-bold text-slate-400">{{ __('messages.home.pass_start') }}</dt>
                                    <dd class="mt-1 text-lg font-black text-white">{{ __('messages.home.pass_start_value') }}</dd>
                                </div>
                                <div class="rounded-2xl bg-white/[0.08] p-4">
                                    <dt class="font-bold text-slate-400">{{ __('messages.home.pass_duration') }}</dt>
                                    <dd class="mt-1 text-lg font-black text-white">{{ __('messages.home.pass_duration_value') }}</dd>
                                </div>
                                <div class="rounded-2xl bg-white/[0.08] p-4">
                                    <dt class="font-bold text-slate-400">{{ __('messages.home.pass_renewal') }}</dt>
                                    <dd class="mt-1 text-lg font-black text-white">{{ __('messages.home.pass_renewal_value') }}</dd>
                                </div>
                            </dl>
                            @if(($purchasedPlan['code'] ?? null) === 'creator')
                                <p class="mt-4 rounded-2xl bg-violet-500/20 p-4 text-sm font-bold leading-6 text-violet-100">
                                    {{ __('messages.home.creator_purchase_note') }}
                                </p>
                            @endif
                        </div>
                        @php
                            $purchaseConfirmationMessage = session('purchase_confirmation_error')
                                ?? session('purchase_confirmation_success');
                            $purchaseConfirmationFailed = session()->has('purchase_confirmation_error');
                        @endphp
                        <div
                            id="purchase-confirmation-feedback"
                            role="{{ $purchaseConfirmationFailed ? 'alert' : 'status' }}"
                            aria-live="polite"
                            class="mt-4 rounded-2xl border px-4 py-3 text-sm font-bold leading-6 {{ $purchaseConfirmationMessage ? '' : 'hidden' }} {{ $purchaseConfirmationFailed ? 'border-rose-300/30 bg-rose-400/15 text-rose-100' : 'border-emerald-300/30 bg-emerald-400/15 text-emerald-100' }}"
                        >{{ $purchaseConfirmationMessage }}</div>
                    @else
                        <div class="mt-5 grid gap-3 {{ $alreadyRegistered ? 'sm:grid-cols-3' : 'sm:grid-cols-2' }}">
                            <div class="rounded-3xl bg-white/[0.08] p-4">
                                <p class="text-sm text-slate-400">{{ __('messages.home.free_trial') }}</p>
                                <p class="mt-1 text-2xl font-black">60 {{ __('messages.home.days') }}</p>
                            </div>
                            @if($alreadyRegistered)
                                <div class="rounded-3xl bg-white/[0.08] p-4">
                                    <p class="text-sm text-slate-400">{{ __('messages.home.pass_type') }}</p>
                                    <p class="mt-1 text-2xl font-black">{{ __('messages.home.join_only') }}</p>
                                </div>
                            @endif
                            <div class="rounded-3xl bg-white/[0.08] p-4">
                                <p class="text-sm text-slate-400">{{ __('messages.home.reserved_spots') }}</p>
                                <p class="mt-1 text-2xl font-black">{{ $capacity('waitlist_capacity') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
                @unless($purchasedPlan)
                    <div class="rounded-[2rem] bg-white p-4 text-slate-950 sm:p-5">
                        <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-700">{{ __('messages.home.founder_presale') }}</p>
                        <h4 class="mt-3 text-2xl font-black leading-tight text-slate-950">{{ __('messages.home.founder_presale_title') }}</h4>
                        <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                            {{ __('messages.home.founder_presale_subtitle') }}
                        </p>
                        @if($founderOfferCatalogError)
                            <p class="mt-4 rounded-2xl bg-amber-50 px-4 py-3 text-sm font-black text-amber-800">{{ $founderOfferCatalogError }}</p>
                        @endif
                        <div class="mt-5 grid gap-3 md:grid-cols-2">
                            <div class="rounded-3xl bg-slate-100 p-4 sm:p-5 {{ !$joinOfferPackage || ($joinOfferPackage['available'] ?? null) === 0 ? 'opacity-60' : '' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-black">{{ __('messages.home.join_pass') }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ __('messages.home.join_pass_text') }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-white px-3 py-1 text-sm font-black text-slate-950">{{ $offerPrice($joinOfferPackage) }}</span>
                                </div>
                                <p class="mt-3 text-sm font-bold text-violet-700">{{ $offerAvailability($joinOfferPackage) }}</p>
                            </div>
                            <div class="rounded-3xl bg-slate-950 p-4 text-white sm:p-5 {{ !$creatorOfferPackage || ($creatorOfferPackage['available'] ?? null) === 0 ? 'opacity-60' : '' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-black">{{ __('messages.home.creator_pass') }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-300">{{ __('messages.home.creator_pass_text') }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full wayout-lime px-3 py-1 text-sm font-black text-slate-950">{{ $offerPrice($creatorOfferPackage) }}</span>
                                </div>
                                <p class="mt-3 text-sm font-bold text-violet-200">{{ $offerAvailability($creatorOfferPackage) }}</p>
                            </div>
                        </div>
                        <p class="mt-3 text-xs font-semibold leading-5 text-slate-500">
                            {!! __('messages.home.pass_terms_notice', [
                                'passes' => '<a class="font-black text-violet-700 underline-offset-4 hover:underline" href="'.route('legal.passes').'" target="_blank" rel="noopener noreferrer">'.__('messages.legal.passes').'</a>',
                            ]) !!}
                        </p>
                    </div>
                @endunless
            </div>
            <div class="flex flex-col gap-3 border-t border-white/10 pt-6 sm:flex-row">
                @if($purchasedPlan)
                    <form id="purchase-confirmation-resend-form" method="POST" action="{{ route('purchase.confirmation.resend') }}" class="w-full">
                        @csrf
                        <button id="purchase-confirmation-resend-submit" type="submit" class="flex w-full items-center justify-center gap-3 rounded-full bg-white px-5 py-4 text-base font-black text-slate-950 transition disabled:cursor-wait disabled:opacity-70 sm:text-lg">
                            <svg class="purchase-confirmation-resend-loader hidden h-5 w-5 animate-spin" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span class="purchase-confirmation-resend-text">{{ __('messages.home.resend_purchase_email') }}</span>
                        </button>
                    </form>
                    <button id="keep-waitlist" type="button" class="w-full rounded-full border border-white/15 px-5 py-4 text-base font-black text-white sm:text-lg">{{ __('messages.home.understood') }}</button>
                @else
                    <form method="POST" action="{{ route('subscribe.access') }}" class="w-full">
                        @csrf
                        <input type="hidden" name="email" value="{{ session('waitlist_email') }}" />
                        <input type="hidden" name="waitlist_status" value="{{ session('waitlist_status') }}" />
                        <button id="block-discount" type="submit" @disabled($founderOfferUnavailable) class="w-full rounded-full bg-white px-5 py-4 text-base font-black text-slate-950 disabled:cursor-not-allowed disabled:opacity-50 sm:text-lg">{{ $founderOfferCatalogError ? __('messages.home.catalog_unavailable_short') : ($founderOfferUnavailable ? __('messages.subscribe.passes_sold_out') : __('messages.home.discover_passes')) }}</button>
                    </form>
                    <button id="keep-waitlist" type="button" class="w-full rounded-full border border-white/15 px-5 py-4 text-base font-black text-white sm:text-lg">{{ __('messages.home.stay_waitlist') }}</button>
                @endif
            </div>
            </div>
        </div>
        </div>
    </div>
@endif

<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
    (function(){
        const waitlistForm = document.getElementById('waitlist-join-form');
        const waitlistSubmit = document.getElementById('waitlist-join-submit');
        const waitlistSubmitText = waitlistSubmit?.querySelector('.waitlist-submit-text');
        const waitlistSubmitLoader = waitlistSubmit?.querySelector('.waitlist-submit-loader');

        const setWaitlistSubmitting = (isSubmitting) => {
            if (!waitlistSubmit || !waitlistSubmitText || !waitlistSubmitLoader) return;

            waitlistSubmit.disabled = isSubmitting;
            waitlistSubmit.setAttribute('aria-busy', isSubmitting ? 'true' : 'false');
            waitlistSubmitText.classList.toggle('opacity-70', isSubmitting);
            waitlistSubmitLoader.classList.toggle('hidden', !isSubmitting);
        };

        waitlistForm?.addEventListener('submit', () => setWaitlistSubmitting(true));
        window.addEventListener('pageshow', () => setWaitlistSubmitting(false));

        const resendForm = document.getElementById('purchase-confirmation-resend-form');
        const resendSubmit = document.getElementById('purchase-confirmation-resend-submit');
        const resendSubmitText = resendSubmit?.querySelector('.purchase-confirmation-resend-text');
        const resendSubmitLoader = resendSubmit?.querySelector('.purchase-confirmation-resend-loader');
        const resendFeedback = document.getElementById('purchase-confirmation-feedback');
        const resendDefaultText = resendSubmitText?.textContent;

        const setResendSubmitting = (isSubmitting) => {
            if (!resendSubmit || !resendSubmitText || !resendSubmitLoader) return;

            resendSubmit.disabled = isSubmitting;
            resendSubmit.setAttribute('aria-busy', isSubmitting ? 'true' : 'false');
            resendSubmitText.textContent = isSubmitting
                ? @json(__('messages.home.resending_purchase_email'))
                : resendDefaultText;
            resendSubmitLoader.classList.toggle('hidden', !isSubmitting);
        };

        const showResendFeedback = (message, successful) => {
            if (!resendFeedback) return;

            resendFeedback.textContent = message;
            resendFeedback.classList.remove(
                'hidden',
                'border-emerald-300/30', 'bg-emerald-400/15', 'text-emerald-100',
                'border-rose-300/30', 'bg-rose-400/15', 'text-rose-100',
            );
            resendFeedback.classList.add(...(successful
                ? ['border-emerald-300/30', 'bg-emerald-400/15', 'text-emerald-100']
                : ['border-rose-300/30', 'bg-rose-400/15', 'text-rose-100']));
            resendFeedback.setAttribute('role', successful ? 'status' : 'alert');
            resendFeedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        };

        resendForm?.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (resendSubmit?.disabled) return;

            setResendSubmitting(true);

            try {
                const response = await fetch(resendForm.action, {
                    method: 'POST',
                    body: new FormData(resendForm),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const payload = await response.json().catch(() => ({}));
                const successful = response.ok && payload.ok === true;

                showResendFeedback(
                    payload.message || @json(__('messages.messages.purchase_resend_error')),
                    successful,
                );
            } catch (error) {
                showResendFeedback(@json(__('messages.messages.purchase_resend_error')), false);
            } finally {
                setResendSubmitting(false);
            }
        });
        window.addEventListener('pageshow', () => setResendSubmitting(false));

        const container = document.getElementById('feature-slideshow');
        if (container) {
            const slides = Array.from(container.querySelectorAll('img'));
            let idx = 0;
            let interval = setInterval(() => {
                const prev = idx;
                idx = (idx + 1) % slides.length;
                slides[prev].style.opacity = 0;
                slides[idx].style.opacity = 1;
            }, 3400);
            container.addEventListener('mouseenter', () => clearInterval(interval));
            container.addEventListener('mouseleave', () => interval = setInterval(() => {
                const prev = idx;
                idx = (idx + 1) % slides.length;
                slides[prev].style.opacity = 0;
                slides[idx].style.opacity = 1;
            }, 3400));
        }

        const bindModal = (modalId, panelId, closeIds) => {
            const modal = document.getElementById(modalId);
            const panel = document.getElementById(panelId);
            if (!modal || !panel) return false;
            const previouslyFocused = document.activeElement;
            let closing = false;
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => {
                panel.style.opacity = '1';
                panel.style.transform = 'translateY(0) scale(1)';
                panel.querySelector('[data-modal-initial-focus]')?.focus();
            });
            const closeModal = () => {
                if (closing) return;
                closing = true;
                panel.style.transition = 'all 180ms ease-in';
                panel.style.opacity = '0';
                panel.style.transform = 'translateY(12px) scale(0.98)';
                document.body.style.overflow = '';
                document.removeEventListener('keydown', closeOnEscape);
                setTimeout(() => {
                    modal.remove();
                    previouslyFocused?.focus?.();
                }, 200);
            };
            const closeOnEscape = (event) => {
                if (event.key === 'Escape') closeModal();
            };
            closeIds.forEach((id) => document.getElementById(id)?.addEventListener('click', closeModal));
            document.addEventListener('keydown', closeOnEscape);
            return true;
        };

        bindModal('waitlist-verification-sent', 'waitlist-verification-sent-panel', [
            'waitlist-verification-sent-backdrop',
            'waitlist-verification-sent-close',
            'waitlist-verification-sent-confirm',
        ]);
        bindModal('waitlist-offer', 'waitlist-offer-panel', ['keep-waitlist', 'waitlist-close-x']);
    })();
</script>
@endsection
