@extends('layouts.app', [
    'title' => $title . ' - Wayout',
    'description' => $description,
])

@section('content')
<section class="py-10 lg:py-16">
    <div class="wayout-shell">
        <div class="max-w-5xl">
            <span class="inline-flex rounded-full border border-violet-200 bg-white/80 px-4 py-2 text-sm font-black uppercase tracking-[0.22em] text-violet-700">
                {{ __('messages.legal_page.area') }}
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
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">{{ __('messages.legal_page.documents') }}</p>
                <nav class="mt-4 flex flex-col gap-2 text-sm font-black">
                    <a class="rounded-lg px-3 py-2 transition hover:bg-violet-50 hover:text-violet-700 {{ request()->routeIs('legal.privacy') ? 'bg-slate-950 text-white hover:bg-slate-950 hover:text-white' : 'text-slate-600' }}" href="{{ route('legal.privacy', ['lang' => app()->getLocale()]) }}">{{ __('messages.legal.privacy') }}</a>
                    <a class="rounded-lg px-3 py-2 transition hover:bg-violet-50 hover:text-violet-700 {{ request()->routeIs('legal.cookies') ? 'bg-slate-950 text-white hover:bg-slate-950 hover:text-white' : 'text-slate-600' }}" href="{{ route('legal.cookies', ['lang' => app()->getLocale()]) }}">{{ __('messages.legal.cookies') }}</a>
                    <a class="rounded-lg px-3 py-2 transition hover:bg-violet-50 hover:text-violet-700 {{ request()->routeIs('legal.terms') ? 'bg-slate-950 text-white hover:bg-slate-950 hover:text-white' : 'text-slate-600' }}" href="{{ route('legal.terms', ['lang' => app()->getLocale()]) }}">{{ __('messages.legal.terms') }}</a>
                    <a class="rounded-lg px-3 py-2 transition hover:bg-violet-50 hover:text-violet-700 {{ request()->routeIs('legal.passes') ? 'bg-slate-950 text-white hover:bg-slate-950 hover:text-white' : 'text-slate-600' }}" href="{{ route('legal.passes', ['lang' => app()->getLocale()]) }}">{{ __('messages.legal.passes') }}</a>
                    <a class="rounded-lg px-3 py-2 transition hover:bg-violet-50 hover:text-violet-700 {{ request()->routeIs('legal.sales') ? 'bg-slate-950 text-white hover:bg-slate-950 hover:text-white' : 'text-slate-600' }}" href="{{ route('legal.sales', ['lang' => app()->getLocale()]) }}">{{ __('messages.legal.sales') }}</a>
                    <a class="rounded-lg px-3 py-2 transition hover:bg-violet-50 hover:text-violet-700 {{ request()->routeIs('legal.presale') ? 'bg-slate-950 text-white hover:bg-slate-950 hover:text-white' : 'text-slate-600' }}" href="{{ route('legal.presale', ['lang' => app()->getLocale()]) }}">{{ __('messages.legal.presale') }}</a>
                    <a class="rounded-lg px-3 py-2 transition hover:bg-violet-50 hover:text-violet-700 {{ request()->routeIs('legal.refunds', 'withdrawal.*') ? 'bg-slate-950 text-white hover:bg-slate-950 hover:text-white' : 'text-slate-600' }}" href="{{ route('legal.refunds', ['lang' => app()->getLocale()]) }}">{{ __('messages.legal.refunds') }}</a>
                    <a class="rounded-lg px-3 py-2 transition hover:bg-violet-50 hover:text-violet-700 {{ request()->routeIs('legal.notice') ? 'bg-slate-950 text-white hover:bg-slate-950 hover:text-white' : 'text-slate-600' }}" href="{{ route('legal.notice', ['lang' => app()->getLocale()]) }}">{{ __('messages.legal.notice') }}</a>
                </nav>
            </aside>
        </div>
    </div>
</section>
@endsection
