@extends('layouts.app', [
    'title' => __('messages.home.title'),
    'description' => __('messages.home.description')
])

@section('content')
@php
    $capacity = fn (string $key) => number_format(($founderCapacities[$key] ?? config('founder.default_capacities')[$key]), 0, ',', '.');
    $waitlistFull = $founderAvailability['waitlist']['is_full'] ?? false;
    $joinFull = $founderAvailability['join']['is_full'] ?? false;
    $creatorFull = $founderAvailability['creator']['is_full'] ?? false;
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

                    <form method="POST" action="{{ route('waitlist.store') }}" class="mt-5 grid gap-3 sm:grid-cols-[1fr_auto]">
                        @csrf
                        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="{{ __('messages.home.email_placeholder') }}"
                            required
                            class="min-h-14 w-full rounded-full border border-white/10 bg-white px-5 text-base font-bold text-slate-950 placeholder-slate-400 outline-none transition focus:ring-4 focus:ring-violet-300/40"
                        />
                        <button type="submit" class="min-h-14 w-full rounded-full wayout-purple px-7 text-base font-black text-white shadow-[0_18px_40px_rgba(124,35,245,0.32)] transition hover:scale-[1.01] sm:w-auto">
                            {{ $waitlistFull ? __('messages.home.verify_email') : __('messages.home.join_button') }}
                        </button>
                    </form>

                    @if($waitlistFull)
                        <p class="mt-3 rounded-2xl bg-white/10 px-4 py-3 text-sm font-bold text-violet-100">
                            {{ __('messages.home.waitlist_closed_message') }}
                        </p>
                    @endif

                    @error('email')
                        <p class="mt-3 px-2 text-sm font-semibold text-rose-200">{{ $message }}</p>
                    @enderror

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

@if(session('waitlist_profile_prompt'))
    @php
        $waitlistProfile = session('waitlist_profile', []);
        $adultMaxDate = now()->subYears(18)->toDateString();
        $phonePrefixes = [
            '+39' => 'IT +39',
            '+33' => 'FR +33',
            '+34' => 'ES +34',
            '+49' => 'DE +49',
            '+44' => 'UK +44',
            '+1' => 'US +1',
        ];
    @endphp
    <div id="waitlist-profile" data-show="1" class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden px-3 py-4 sm:px-4 sm:py-8">
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-md" aria-hidden="true"></div>
        <div class="relative z-10 w-full max-w-2xl overflow-hidden rounded-[2rem] border border-white/15 bg-slate-950/95 p-5 text-white opacity-0 shadow-2xl transition-all duration-300 sm:rounded-[2.5rem] sm:p-8" id="waitlist-profile-panel" role="dialog" aria-modal="true" aria-labelledby="waitlist-profile-title">
            <div class="mb-5 flex items-center justify-between gap-4">
                <span class="inline-flex rounded-full bg-violet-500/20 px-4 py-2 text-xs font-black uppercase tracking-[0.26em] text-violet-100">{{ __('messages.home.waitlist') }}</span>
                <button id="waitlist-profile-close-x" aria-label="{{ __('messages.nav.close_menu') }}" class="shrink-0 rounded-full bg-white/10 p-2 text-slate-300 transition hover:bg-white/15 hover:text-white">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <h3 id="waitlist-profile-title" class="text-3xl font-black leading-tight tracking-tight sm:text-4xl">{{ __('messages.home.profile_title') }}</h3>
            @if(session('waitlist_error'))
                <p class="mt-4 rounded-2xl bg-rose-500/15 px-4 py-3 text-sm font-bold text-rose-100">{{ session('waitlist_error') }}</p>
            @endif
            <form method="POST" action="{{ route('waitlist.profile') }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="email" value="{{ session('waitlist_email') }}" />
                <input type="hidden" name="waitlist_status" value="{{ session('waitlist_status') }}" />
                <div class="grid gap-3 text-left sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-300">{{ __('messages.home.first_name') }}</span>
                        <input type="text" name="first_name" value="{{ old('first_name', $waitlistProfile['first_name'] ?? '') }}" required autocomplete="given-name" class="min-h-12 w-full rounded-2xl border border-white/10 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:ring-4 focus:ring-violet-300/30" />
                        @error('first_name')<span class="mt-1 block text-xs font-bold text-rose-200">{{ $message }}</span>@enderror
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-300">{{ __('messages.home.last_name') }}</span>
                        <input type="text" name="last_name" value="{{ old('last_name', $waitlistProfile['last_name'] ?? '') }}" required autocomplete="family-name" class="min-h-12 w-full rounded-2xl border border-white/10 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:ring-4 focus:ring-violet-300/30" />
                        @error('last_name')<span class="mt-1 block text-xs font-bold text-rose-200">{{ $message }}</span>@enderror
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-300">{{ __('messages.home.birth_date') }}</span>
                        <input type="date" name="birth_date" value="{{ old('birth_date', $waitlistProfile['birth_date'] ?? '') }}" max="{{ $adultMaxDate }}" required autocomplete="bday" class="min-h-12 w-full rounded-2xl border border-white/10 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:ring-4 focus:ring-violet-300/30" />
                        @error('birth_date')<span class="mt-1 block text-xs font-bold text-rose-200">{{ __('messages.home.birth_date_error') }}</span>@enderror
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-300">{{ __('messages.home.phone_number') }}</span>
                        <div class="flex gap-2">
                            <select name="phone_prefix" required autocomplete="tel-country-code" class="min-h-12 w-28 rounded-2xl border border-white/10 bg-white px-3 text-sm font-bold text-slate-950 outline-none transition focus:ring-4 focus:ring-violet-300/30">
                                @foreach($phonePrefixes as $prefix => $label)
                                    <option value="{{ $prefix }}" @selected(old('phone_prefix', $waitlistProfile['phone_prefix'] ?? '+39') === $prefix)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <input type="tel" name="phone_number" value="{{ old('phone_number', $waitlistProfile['phone_number'] ?? '') }}" required autocomplete="tel-national" inputmode="tel" class="min-h-12 min-w-0 flex-1 rounded-2xl border border-white/10 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:ring-4 focus:ring-violet-300/30" />
                        </div>
                        @error('phone_prefix')<span class="mt-1 block text-xs font-bold text-rose-200">{{ $message }}</span>@enderror
                        @error('phone_number')<span class="mt-1 block text-xs font-bold text-rose-200">{{ $message }}</span>@enderror
                    </label>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-left text-sm font-medium leading-6 text-slate-200 [&_a]:font-black [&_a]:text-violet-200 [&_a]:underline [&_a]:underline-offset-4">
                    {!! $legalDocumentsHtml['waitlist_acceptance'] !!}
                </div>
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-white/10 bg-white/5 p-4 text-left">
                    <input type="checkbox" name="marketing_consent" value="1" @checked(old('marketing_consent', $waitlistProfile['marketing_consent'] ?? false)) class="mt-0.5 h-5 w-5 shrink-0 rounded border-white/20 text-violet-600 focus:ring-violet-300" />
                    <span class="text-sm font-medium leading-6 text-slate-200">
                        {!! $legalDocumentsHtml['marketing'] !!}
                    </span>
                </label>
                <button type="submit" class="w-full rounded-full bg-white px-5 py-4 text-base font-black text-slate-950 sm:text-lg">{{ __('messages.home.profile_submit') }}</button>
            </form>
        </div>
    </div>
@endif

@if(session('waitlist_offer'))
    @php
        $alreadyRegistered = session('waitlist_status') === 'already_registered';
        $purchasedPlan = session('purchased_plan');
    @endphp
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
                            {!! __('messages.home.already_purchased_text', ['email' => session('waitlist_email'), 'plan' => '<strong>'.$purchasedPlan['name'].'</strong>', 'amount' => number_format($purchasedPlan['amount'] / 100, 2, ',', '.').'€']) !!}
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
                        <div class="mt-5 grid gap-3 md:grid-cols-2">
                            <div class="rounded-3xl bg-slate-100 p-4 sm:p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-black">{{ __('messages.home.join_pass') }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ __('messages.home.join_pass_text') }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-white px-3 py-1 text-sm font-black text-slate-950">29€</span>
                                </div>
                                <p class="mt-3 text-sm font-bold text-violet-700">{{ $joinFull ? __('messages.home.sold_out') : __('messages.home.spots_available', ['count' => $capacity('join_capacity')]) }}</p>
                            </div>
                            <div class="rounded-3xl bg-slate-950 p-4 text-white sm:p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-black">{{ __('messages.home.creator_pass') }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-300">{{ __('messages.home.creator_pass_text') }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full wayout-lime px-3 py-1 text-sm font-black text-slate-950">59€</span>
                                </div>
                                <p class="mt-3 text-sm font-bold text-violet-200">{{ $creatorFull ? __('messages.home.sold_out') : __('messages.home.spots_available', ['count' => $capacity('creator_capacity')]) }}</p>
                            </div>
                        </div>
                        <p class="mt-3 text-xs font-semibold leading-5 text-slate-500">
                            {!! __('messages.home.pass_terms_notice', [
                                'passes' => '<a class="font-black text-violet-700 underline-offset-4 hover:underline" href="'.route('legal.passes').'">'.__('messages.legal.passes').'</a>',
                            ]) !!}
                        </p>
                    </div>
                @endunless
            </div>
            <div class="flex flex-col gap-3 border-t border-white/10 pt-6 sm:flex-row">
                @if($purchasedPlan)
                    <form method="POST" action="{{ route('purchase.confirmation.resend') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full rounded-full bg-white px-5 py-4 text-base font-black text-slate-950 sm:text-lg">{{ __('messages.home.resend_purchase_email') }}</button>
                    </form>
                    <button id="keep-waitlist" type="button" class="w-full rounded-full border border-white/15 px-5 py-4 text-base font-black text-white sm:text-lg">{{ __('messages.home.understood') }}</button>
                @else
                    <form method="POST" action="{{ route('subscribe.access') }}" class="w-full">
                        @csrf
                        <input type="hidden" name="email" value="{{ session('waitlist_email') }}" />
                        <input type="hidden" name="waitlist_status" value="{{ session('waitlist_status') }}" />
                        <button id="block-discount" type="submit" class="w-full rounded-full bg-white px-5 py-4 text-base font-black text-slate-950 sm:text-lg">{{ __('messages.home.discover_passes') }}</button>
                    </form>
                    <button id="keep-waitlist" type="button" class="w-full rounded-full border border-white/15 px-5 py-4 text-base font-black text-white sm:text-lg">{{ __('messages.home.stay_waitlist') }}</button>
                @endif
            </div>
            </div>
        </div>
        </div>
    </div>
@endif

<script>
    (function(){
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
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => {
                panel.style.opacity = '1';
                panel.style.transform = 'translateY(0) scale(1)';
            });
            const closeModal = () => {
                panel.style.transition = 'all 180ms ease-in';
                panel.style.opacity = '0';
                panel.style.transform = 'translateY(12px) scale(0.98)';
                document.body.style.overflow = '';
                setTimeout(() => modal.remove(), 200);
            };
            closeIds.forEach((id) => document.getElementById(id)?.addEventListener('click', closeModal));
            return true;
        };

        bindModal('waitlist-profile', 'waitlist-profile-panel', ['waitlist-profile-close-x']);
        bindModal('waitlist-offer', 'waitlist-offer-panel', ['keep-waitlist', 'waitlist-close-x']);
    })();
</script>
@endsection
