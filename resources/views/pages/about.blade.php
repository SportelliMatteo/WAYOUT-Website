@extends('layouts.app', [
    'title' => __('messages.about.title'),
    'description' => __('messages.about.description')
])

@section('content')
<section class="relative overflow-hidden py-10 lg:py-16">
    <div class="wayout-shell">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div>
                <span class="inline-flex rounded-full border border-violet-200 bg-white/80 px-4 py-2 text-sm font-black uppercase tracking-[0.22em] text-violet-700">
                    {{ __('messages.about.eyebrow') }}
                </span>
                <h1 class="mt-6 text-4xl font-black leading-[0.98] text-slate-950 sm:text-6xl lg:text-7xl">
                    {{ __('messages.about.headline') }} <span class="wayout-gradient-text">{{ __('messages.about.headline_highlight') }}</span>
                </h1>
                <p class="mt-5 max-w-2xl text-base font-medium leading-7 text-slate-600 sm:mt-6 sm:text-lg sm:leading-8">
                    {{ __('messages.about.intro') }}
                </p>
            </div>
            <div class="relative">
                <div class="absolute -inset-3 rounded-[2rem] bg-violet-500/20 blur-2xl sm:-inset-4 sm:rounded-[2.5rem]"></div>
                <img class="relative aspect-[4/3] w-full rounded-[2rem] object-cover shadow-[0_30px_90px_rgba(15,23,42,0.18)] sm:rounded-[2.5rem]" src="/images/about/about.jpg" alt="Team WAYOUT" />
                <div class="relative mx-4 -mt-10 rounded-[1.5rem] border border-white/70 bg-white/[0.9] p-4 shadow-2xl backdrop-blur-xl sm:absolute sm:-bottom-6 sm:left-6 sm:right-6 sm:mx-0 sm:mt-0 sm:rounded-[2rem] sm:p-5">
                    <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-700">{{ __('messages.about.mission') }}</p>
                    <p class="mt-2 text-xl font-black text-slate-950 sm:text-2xl">{{ __('messages.about.mission_text') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-12 lg:py-24">
    <div class="wayout-shell">
        <div class="grid gap-5 md:grid-cols-3">
            <article class="wayout-card rounded-[2rem] p-6">
                <p class="text-5xl font-black text-violet-700">01</p>
                <h2 class="mt-5 text-2xl font-black text-slate-950">{{ __('messages.about.card_1_title') }}</h2>
                <p class="mt-3 leading-7 text-slate-600">{{ __('messages.about.card_1_text') }}</p>
            </article>
            <article class="wayout-card rounded-[2rem] p-6">
                <p class="text-5xl font-black text-violet-700">02</p>
                <h2 class="mt-5 text-2xl font-black text-slate-950">{{ __('messages.about.card_2_title') }}</h2>
                <p class="mt-3 leading-7 text-slate-600">{{ __('messages.about.card_2_text') }}</p>
            </article>
            <article class="wayout-card rounded-[2rem] p-6">
                <p class="text-5xl font-black text-violet-700">03</p>
                <h2 class="mt-5 text-2xl font-black text-slate-950">{{ __('messages.about.card_3_title') }}</h2>
                <p class="mt-3 leading-7 text-slate-600">{{ __('messages.about.card_3_text') }}</p>
            </article>
        </div>

        <div class="mt-10 rounded-[2rem] bg-slate-950 p-5 text-white shadow-[0_30px_100px_rgba(15,23,42,0.22)] sm:rounded-[2.5rem] sm:p-10 lg:p-14">
            <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.28em] text-violet-200">{{ __('messages.about.project') }}</p>
                    <h2 class="mt-4 text-3xl font-black leading-tight sm:text-5xl">{{ __('messages.about.project_title') }}</h2>
                </div>
                <div class="space-y-5 text-base leading-7 text-slate-300 sm:text-lg sm:leading-8">
                    <p>
                        {{ __('messages.about.project_p1') }}
                    </p>
                    <p>
                        {{ __('messages.about.project_p2') }}
                    </p>
                    <p class="font-black text-white">
                        {{ __('messages.about.project_p3') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{--
<section class="pb-10 lg:pb-20">
    <div class="wayout-shell">
        <div class="grid gap-6 lg:grid-cols-2">
            <article class="wayout-card overflow-hidden rounded-[2rem]">
                <img src="/images/about/Matteo.jpg" alt="Matteo di WAYOUT" class="aspect-[4/3] w-full object-cover" />
                <div class="p-6">
                    <p class="text-sm font-black uppercase tracking-[0.24em] text-violet-700">Founder</p>
                    <h2 class="mt-2 text-3xl font-black text-slate-950">Matteo</h2>
                </div>
            </article>
            <article class="wayout-card overflow-hidden rounded-[2rem]">
                <img src="/images/about/Niccolo.jpg" alt="Niccolò di WAYOUT" class="aspect-[4/3] w-full object-cover" />
                <div class="p-6">
                    <p class="text-sm font-black uppercase tracking-[0.24em] text-violet-700">Founder</p>
                    <h2 class="mt-2 text-3xl font-black text-slate-950">Marco</h2>
                </div>
            </article>
        </div>
    </div>
</section>
--}}
@endsection
