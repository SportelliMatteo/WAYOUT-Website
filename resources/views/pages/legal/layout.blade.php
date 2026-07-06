@extends('layouts.app', [
    'title' => $title . ' - Wayout',
    'description' => $description,
])

@section('content')
<section class="py-10 lg:py-16">
    <div class="wayout-shell">
        <div class="max-w-5xl">
            <span class="inline-flex rounded-full border border-violet-200 bg-white/80 px-4 py-2 text-sm font-black uppercase tracking-[0.22em] text-violet-700">
                Area legale
            </span>
            <h1 class="mt-6 text-4xl font-black leading-tight text-slate-950 sm:text-6xl">{{ $title }}</h1>
            <p class="mt-4 max-w-3xl text-base font-semibold leading-7 text-slate-600">
                Ultimo aggiornamento: {{ $updated ?? '29 giugno 2026' }}
            </p>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[0.72fr_0.28fr] lg:items-start">
            <article class="wayout-panel rounded-[1.5rem] p-5 leading-7 text-slate-700 sm:p-8">
                @yield('legal-content')
            </article>

            <aside class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm lg:sticky lg:top-28">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Documenti</p>
                <nav class="mt-4 flex flex-col gap-2 text-sm font-black">
                    <a class="rounded-lg px-3 py-2 transition hover:bg-violet-50 hover:text-violet-700 {{ request()->routeIs('legal.privacy') ? 'bg-slate-950 text-white hover:bg-slate-950 hover:text-white' : 'text-slate-600' }}" href="{{ route('legal.privacy') }}">Privacy policy</a>
                    <a class="rounded-lg px-3 py-2 transition hover:bg-violet-50 hover:text-violet-700 {{ request()->routeIs('legal.cookies') ? 'bg-slate-950 text-white hover:bg-slate-950 hover:text-white' : 'text-slate-600' }}" href="{{ route('legal.cookies') }}">Cookie policy</a>
                    <a class="rounded-lg px-3 py-2 transition hover:bg-violet-50 hover:text-violet-700 {{ request()->routeIs('legal.terms') ? 'bg-slate-950 text-white hover:bg-slate-950 hover:text-white' : 'text-slate-600' }}" href="{{ route('legal.terms') }}">Termini e condizioni</a>
                    <a class="rounded-lg px-3 py-2 transition hover:bg-violet-50 hover:text-violet-700 {{ request()->routeIs('legal.passes') ? 'bg-slate-950 text-white hover:bg-slate-950 hover:text-white' : 'text-slate-600' }}" href="{{ route('legal.passes') }}">Come funzionano i Pass</a>
                    <a class="rounded-lg px-3 py-2 transition hover:bg-violet-50 hover:text-violet-700 {{ request()->routeIs('legal.sales') ? 'bg-slate-950 text-white hover:bg-slate-950 hover:text-white' : 'text-slate-600' }}" href="{{ route('legal.sales') }}">Condizioni di vendita</a>
                    <a class="rounded-lg px-3 py-2 transition hover:bg-violet-50 hover:text-violet-700 {{ request()->routeIs('legal.notice') ? 'bg-slate-950 text-white hover:bg-slate-950 hover:text-white' : 'text-slate-600' }}" href="{{ route('legal.notice') }}">Note legali</a>
                </nav>
            </aside>
        </div>
    </div>
</section>
@endsection
