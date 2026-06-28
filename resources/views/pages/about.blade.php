@extends('layouts.app', [
    'title' => 'Chi siamo - Wayout',
    'description' => 'Scopri la missione di Wayout e come uniamo persone ai tavoli.'
])

@section('content')
<section class="relative overflow-hidden py-10 lg:py-16">
    <div class="wayout-shell">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div>
                <span class="inline-flex rounded-full border border-violet-200 bg-white/80 px-4 py-2 text-sm font-black uppercase tracking-[0.22em] text-violet-700">
                    This is WAYOUT
                </span>
                <h1 class="mt-6 text-4xl font-black leading-[0.98] text-slate-950 sm:text-6xl lg:text-7xl">
                    Abbiamo dato un nome a una nuova vibe: <span class="wayout-gradient-text">social clubbing.</span>
                </h1>
                <p class="mt-5 max-w-2xl text-base font-medium leading-7 text-slate-600 sm:mt-6 sm:text-lg sm:leading-8">
                    WAYOUT nasce per unire persone che non si sono mai viste prima e portarle a fare serata insieme. Tavoli, club, feste private e nuove amicizie diventano un’unica esperienza.
                </p>
            </div>
            <div class="relative">
                <div class="absolute -inset-3 rounded-[2rem] bg-violet-500/20 blur-2xl sm:-inset-4 sm:rounded-[2.5rem]"></div>
                <img class="relative aspect-[4/3] w-full rounded-[2rem] object-cover shadow-[0_30px_90px_rgba(15,23,42,0.18)] sm:rounded-[2.5rem]" src="/images/about/about.jpg" alt="Team WAYOUT" />
                <div class="relative mx-4 -mt-10 rounded-[1.5rem] border border-white/70 bg-white/[0.9] p-4 shadow-2xl backdrop-blur-xl sm:absolute sm:-bottom-6 sm:left-6 sm:right-6 sm:mx-0 sm:mt-0 sm:rounded-[2rem] sm:p-5">
                    <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-700">Missione</p>
                    <p class="mt-2 text-xl font-black text-slate-950 sm:text-2xl">Portare aria fresca nel clubbing italiano ed europeo.</p>
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
                <h2 class="mt-5 text-2xl font-black text-slate-950">Persone vere</h2>
                <p class="mt-3 leading-7 text-slate-600">Il problema non è avere voglia di uscire. Spesso è trovare le persone giuste con cui condividere energia, allegria e leggerezza.</p>
            </article>
            <article class="wayout-card rounded-[2rem] p-6">
                <p class="text-5xl font-black text-violet-700">02</p>
                <h2 class="mt-5 text-2xl font-black text-slate-950">Tavoli aperti</h2>
                <p class="mt-3 leading-7 text-slate-600">Con l’app puoi partecipare a tavoli organizzati da altri utenti oppure crearne uno tuo e far entrare nuove persone.</p>
            </article>
            <article class="wayout-card rounded-[2rem] p-6">
                <p class="text-5xl font-black text-violet-700">03</p>
                <h2 class="mt-5 text-2xl font-black text-slate-950">Ricordi condivisi</h2>
                <p class="mt-3 leading-7 text-slate-600">La notte diventa un posto più facile da vivere: stessa città, stesse vibes, nuove connessioni da trasformare in ricordi.</p>
            </article>
        </div>

        <div class="mt-10 rounded-[2rem] bg-slate-950 p-5 text-white shadow-[0_30px_100px_rgba(15,23,42,0.22)] sm:rounded-[2.5rem] sm:p-10 lg:p-14">
            <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.28em] text-violet-200">Il progetto</p>
                    <h2 class="mt-4 text-3xl font-black leading-tight sm:text-5xl">Una soluzione nata da una sensazione comune.</h2>
                </div>
                <div class="space-y-5 text-base leading-7 text-slate-300 sm:text-lg sm:leading-8">
                    <p>
                        Siamo un team di artisti, marketer, sviluppatori, content creator e fan della notte. Abbiamo vissuto gli anni universitari con la stessa voglia: socializzare, divertirci e creare esperienze nuove con persone sulla stessa frequenza.
                    </p>
                    <p>
                        WAYOUT esiste per rendere quella possibilità più semplice. Se non sai con chi fare serata nel weekend, puoi unirti a un tavolo già formato. Se hai tu la serata in mente, puoi crearla e aprirla agli altri.
                    </p>
                    <p class="font-black text-white">
                        Non è solo prenotare un posto. È entrare in una scena.
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
