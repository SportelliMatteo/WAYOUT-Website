@extends('layouts.app', ['title' => 'Disdetta abbonamento — WAYOUT', 'description' => 'Richiedi la disattivazione del rinnovo del tuo abbonamento WAYOUT.'])

@section('content')
<section class="py-12 lg:py-20">
    <div class="wayout-shell max-w-3xl">
        <div class="wayout-panel rounded-[1.5rem] p-6 sm:p-10">
            <p class="brand-eyebrow">Assistenza acquisti</p>
            <h1 class="mt-4 text-4xl font-black text-slate-950">Disdetta abbonamento</h1>
            <p class="mt-4 font-semibold leading-7 text-slate-600">Invia la tua richiesta di disdetta e di non rinnovo. L’assistenza verificherà l’acquisto e ti contatterà via email per gestire la richiesta.</p>
            <p class="mt-3 text-sm leading-6 text-slate-600">Per il recesso e il rimborso, <a class="font-bold text-violet-700 underline" href="{{ route('legal.refunds') }}#recedere">usa il modulo dedicato</a>.</p>
            @include('partials.commerce-support-form', ['source' => 'subscription-non-renewal'])
        </div>
    </div>
</section>
@endsection
