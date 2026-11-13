@extends('layouts.app', [
    'title' => $title . ' - Wayout',
    'description' => $description,
])

@section('content')
<section class="py-10 lg:py-16">
    <div class="wayout-shell">
        <div class="max-w-5xl">
            <span class="inline-flex rounded-full border border-violet-200 bg-white/80 px-4 py-2 text-sm font-black uppercase tracking-[0.22em] text-violet-700">
                {{ isset($documentScope) ? __('experience.legal_'.strtolower($documentScope)) : __('messages.legal_page.area') }}
            </span>
            <h1 class="mt-6 text-4xl font-black leading-tight text-slate-950 sm:text-6xl">{{ $title }}</h1>
            <p class="mt-4 max-w-3xl text-base font-semibold leading-7 text-slate-600">
                {{ __('messages.legal_page.last_update') }}: {{ $updated ?? '-' }} · {{ __('messages.legal_page.version') }} {{ $version ?? '-' }}
            </p>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[0.72fr_0.28fr] lg:items-start">
            <article class="wayout-panel min-w-0 break-words rounded-[1.5rem] p-5 leading-7 text-slate-700 [overflow-wrap:anywhere] sm:p-8">
                @yield('legal-content')
            </article>

            <aside class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm lg:sticky lg:top-28">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500"><a href="{{ route('legal.index', ['lang' => app()->getLocale()]) }}">{{ __('messages.legal_page.documents') }}</a></p>
                @include('partials.legal-navigation')
            </aside>
        </div>
    </div>
</section>
@endsection
