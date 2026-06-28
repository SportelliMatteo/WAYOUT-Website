@extends('layouts.app', [
    'title' => 'Checkout completato - Wayout',
    'description' => 'Il checkout è stato completato con successo.'
])

@section('content')
<section class="py-14 lg:py-24">
    <div class="mx-auto max-w-4xl px-5">
        <div class="relative mt-30 mb-30 overflow-hidden rounded-[2rem] bg-slate-950 p-5 text-white shadow-[0_30px_100px_rgba(15,23,42,0.22)] sm:rounded-[2.5rem] sm:p-10">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_0%,rgba(124,35,245,0.55),transparent_26rem),radial-gradient(circle_at_90%_10%,rgba(185,255,74,0.24),transparent_18rem)]"></div>
            <div class="relative">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white text-3xl font-black text-violet-700">✓</div>
                <p class="mt-6 text-sm font-black uppercase tracking-[0.26em] text-violet-200">Pagamento completato</p>
                <h1 class="mt-3 text-3xl font-black leading-tight sm:text-5xl">Il tuo Founder Pass è confermato.</h1>
                <p class="mt-5 max-w-2xl text-base leading-7 text-slate-300 sm:text-lg sm:leading-8">
                    Grazie per aver scelto un Founder Pass pre-lancio. Il tuo acquisto è stato registrato e la conferma ti arriverà via email.
                </p>
                <div class="mt-8">
                    <a href="{{ route('home') }}" class="inline-flex w-full items-center justify-center rounded-full bg-white px-7 py-4 text-base font-black text-slate-950 shadow-xl transition hover:scale-[1.02] sm:w-auto sm:text-lg">
                        Torna alla home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
