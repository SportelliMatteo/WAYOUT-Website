@extends('layouts.app', [
    'title' => 'Wayout',
    'description' => 'Trova persone con cui condividere tavoli, serate e nuove esperienze.'
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
                Social clubbing, prima di tutti
            </span>
            <h1 class="mx-auto mt-6 max-w-4xl text-4xl font-black leading-[0.96] text-slate-950 sm:text-6xl lg:mx-0 lg:text-7xl">
                Trova il tuo tavolo. <span class="wayout-gradient-text">Entra nella notte.</span>
            </h1>
            <p class="mx-auto mt-5 max-w-2xl text-base font-medium leading-7 text-slate-600 sm:mt-6 sm:text-xl sm:leading-8 lg:mx-0">
                WAYOUT unisce persone che non si conoscono ancora e le porta a condividere club, tavoli e feste private nella stessa città.
            </p>

            <div class="mx-auto mt-7 max-w-2xl overflow-hidden rounded-[2rem] bg-slate-950 text-white shadow-[0_26px_80px_rgba(15,23,42,0.20)] sm:mt-8 lg:mx-0">
                <div class="bg-[radial-gradient(circle_at_12%_0%,rgba(124,35,245,0.62),transparent_22rem),radial-gradient(circle_at_95%_10%,rgba(185,255,74,0.20),transparent_18rem)] p-4 sm:p-5">
                    <div class="flex flex-col items-center gap-4 text-center lg:items-start lg:text-left">
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-200">Accesso anticipato</p>
                            <p class="mt-2 text-xl font-black leading-tight sm:text-2xl">{{ $waitlistFull ? 'Waitlist al completo' : 'Entra nella waitlist WAYOUT' }}</p>
                        </div>
                        <div class="flex w-full flex-wrap justify-center gap-2 text-center text-xs font-black text-slate-950 sm:text-sm lg:justify-start">
                            <div class="min-w-[6.25rem] rounded-2xl bg-white px-3 py-2"><span class="block text-base sm:text-lg">{{ $capacity('waitlist_capacity') }}</span>posti</div>
                            <div class="min-w-[6.25rem] rounded-2xl bg-white px-3 py-2"><span class="block text-base sm:text-lg">60</span>giorni</div>
                            <div class="min-w-[6.25rem] rounded-2xl wayout-lime px-3 py-2"><span class="block text-base sm:text-lg">Pass</span>Founder</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('waitlist.store') }}" class="mt-5 grid gap-3 sm:grid-cols-[1fr_auto]">
                        @csrf
                        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="La tua email"
                            required
                            class="min-h-14 w-full rounded-full border border-white/10 bg-white px-5 text-base font-bold text-slate-950 placeholder-slate-400 outline-none transition focus:ring-4 focus:ring-violet-300/40"
                        />
                        <button type="submit" class="min-h-14 w-full rounded-full wayout-purple px-7 text-base font-black text-white shadow-[0_18px_40px_rgba(124,35,245,0.32)] transition hover:scale-[1.01] sm:w-auto">
                            {{ $waitlistFull ? 'Verifica email' : 'Accedi' }}
                        </button>
                    </form>

                    @if($waitlistFull)
                        <p class="mt-3 rounded-2xl bg-white/10 px-4 py-3 text-sm font-bold text-violet-100">
                            La waitlist è chiusa perché i posti sono terminati. Se sei già iscritto, inserisci la tua email per accedere alle offerte Founder.
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
                <p class="text-sm font-bold text-violet-200">Live a Milano</p>
                <p class="mt-2 text-4xl font-black">48</p>
                <p class="mt-1 text-sm text-slate-300">tavoli e feste in creazione questa settimana</p>
            </div>
            <div class="relative z-20 mt-[-3.5rem] w-[min(88%,270px)] rounded-[2rem] border border-white/70 bg-white/[0.9] p-4 shadow-2xl backdrop-blur-xl sm:mt-[-4.5rem] sm:p-5 lg:absolute lg:bottom-10 lg:right-0 lg:mt-0 lg:w-[250px]">
                <p class="text-sm font-black text-slate-950">Tavolo di Matteo</p>
                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Disponibili</p>
                        <p class="text-3xl font-black text-violet-700">4 posti</p>
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
                <p class="text-sm font-black uppercase tracking-[0.28em] text-violet-700">Come funziona</p>
                <h2 class="mt-4 text-3xl font-black leading-tight text-slate-950 sm:text-5xl">Dalla mappa alla chat in pochi tocchi.</h2>
            </div>
            <p class="text-base font-medium leading-7 text-slate-600 sm:text-lg sm:leading-8">
                Organizzi il tuo tavolo, scopri gli eventi vicini, scegli le persone con cui condividere la serata e porti tutto in chat. L’esperienza resta semplice, veloce, visiva.
            </p>
        </div>

        <div class="mt-8 grid gap-5 md:grid-cols-3 lg:mt-10">
            <article class="wayout-card rounded-[2rem] p-6 transition hover:-translate-y-1 hover:shadow-[0_24px_70px_rgba(124,35,245,0.14)]">
                <div class="flex h-12 w-12 items-center justify-center rounded-full wayout-purple text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10h.01"/></svg>
                </div>
                <h3 class="mt-6 text-2xl font-black text-slate-950">Esplora la città</h3>
                <p class="mt-3 leading-7 text-slate-600">Trova club, tavoli e feste private nelle vicinanze con una mappa pensata per la notte.</p>
            </article>
            <article class="wayout-card rounded-[2rem] p-6 transition hover:-translate-y-1 hover:shadow-[0_24px_70px_rgba(124,35,245,0.14)]">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-950 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
                </div>
                <h3 class="mt-6 text-2xl font-black text-slate-950">Crea un tavolo</h3>
                <p class="mt-3 leading-7 text-slate-600">Scegli evento, posti, prezzo e vibe. Gli altri utenti possono unirsi al tuo gruppo.</p>
            </article>
            <article class="wayout-card rounded-[2rem] p-6 transition hover:-translate-y-1 hover:shadow-[0_24px_70px_rgba(124,35,245,0.14)]">
                <div class="flex h-12 w-12 items-center justify-center rounded-full wayout-lime text-slate-950">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v7a2 2 0 01-2 2H8l-5 3V10a2 2 0 012-2h2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3h6a2 2 0 012 2v6a2 2 0 01-2 2H9l-4 2V5a2 2 0 012-2z"/></svg>
                </div>
                <h3 class="mt-6 text-2xl font-black text-slate-950">Entra nel gruppo</h3>
                <p class="mt-3 leading-7 text-slate-600">Una volta dentro, la chat diventa il punto d’incontro prima della serata.</p>
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
                    <p class="text-sm font-black uppercase tracking-[0.28em] text-violet-200">Esperienza app</p>
                    <h2 class="mt-4 text-3xl font-black leading-tight sm:text-5xl">Un’interfaccia pulita, ma con energia da club.</h2>
                    <p class="mt-5 text-base leading-7 text-slate-300 sm:text-lg sm:leading-8">Lo stile di WAYOUT mette l’evento al centro e lascia ogni azione immediata.</p>
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

@if(session('waitlist_offer'))
    @php
        $alreadyRegistered = session('waitlist_status') === 'already_registered';
        $purchasedPlan = session('purchased_plan');
    @endphp
    <div id="waitlist-offer" data-show="1" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-3 py-5 sm:px-4 sm:py-6">
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-md" aria-hidden="true"></div>
        <div id="waitlist-offer-panel" role="dialog" aria-modal="true" aria-labelledby="waitlist-offer-title" class="relative z-10 my-auto max-h-[calc(100vh-2.5rem)] w-full max-w-4xl translate-y-8 overflow-y-auto rounded-[2rem] border border-white/15 bg-slate-950/95 p-4 text-white opacity-0 shadow-2xl transition-all duration-300 sm:rounded-[2.5rem] sm:p-8">
            <div class="mb-5 flex items-center justify-between gap-4">
                <span class="inline-flex rounded-full bg-violet-500/20 px-4 py-2 text-xs font-black uppercase tracking-[0.26em] text-violet-100">Waitlist</span>
                <button id="waitlist-close-x" aria-label="Chiudi" class="shrink-0 rounded-full bg-white/10 p-2 text-slate-300 transition hover:bg-white/15 hover:text-white">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-5">
                <div class="min-w-0">
                    <h3 id="waitlist-offer-title" class="text-3xl font-black leading-tight tracking-tight sm:text-4xl">
                        @if($purchasedPlan)
                            Sei già nella waitlist e hai già acquistato
                        @else
                            {{ $alreadyRegistered ? 'Sei già nella waitlist' : 'Il tuo posto è confermato' }}
                        @endif
                    </h3>
                    <p class="mt-4 break-words text-base leading-7 text-slate-300 sm:text-lg sm:leading-8">
                        @if($purchasedPlan)
                            L’email {{ session('waitlist_email') }} risulta già iscritta e ha già acquistato il piano <strong>{{ $purchasedPlan['name'] }}</strong> da {{ number_format($purchasedPlan['amount'] / 100, 2, ',', '.') }}€.
                        @elseif($alreadyRegistered)
                            L’email {{ session('waitlist_email') }} risulta già iscritta. Puoi ancora accedere alle offerte Founder Pass.
                        @else
                            Hai riservato uno dei {{ $capacity('waitlist_capacity') }} posti disponibili e ottieni automaticamente 60 giorni di prova gratuita.
                        @endif
                    </p>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-3xl bg-white/[0.08] p-4">
                            <p class="text-sm text-slate-400">Prova gratuita</p>
                            <p class="mt-1 text-2xl font-black">60 giorni</p>
                        </div>
                        <div class="rounded-3xl bg-white/[0.08] p-4">
                            <p class="text-sm text-slate-400">Posti riservati</p>
                            <p class="mt-1 text-2xl font-black">{{ $capacity('waitlist_capacity') }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-[2rem] bg-white p-4 text-slate-950 sm:p-5">
                    <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-700">Founder Pass pre-lancio</p>
                    <div class="mt-5 grid gap-3 md:grid-cols-2">
                        <div class="rounded-3xl bg-slate-100 p-4 sm:p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-black">Founder Join 12M Pass</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-500">Partecipa a tavoli e feste private già esistenti.</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-white px-3 py-1 text-sm font-black text-slate-950">29€</span>
                            </div>
                            <p class="mt-3 text-sm font-bold text-violet-700">{{ $joinFull ? 'Esaurito' : $capacity('join_capacity').' posti disponibili' }}</p>
                        </div>
                        <div class="rounded-3xl bg-slate-950 p-4 text-white sm:p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-black">Founder Creator 12M Pass</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-300">Crea e gestisci tavoli e feste private.</p>
                                </div>
                                <span class="shrink-0 rounded-full wayout-lime px-3 py-1 text-sm font-black text-slate-950">59€</span>
                            </div>
                            <p class="mt-3 text-sm font-bold text-violet-200">{{ $creatorFull ? 'Esaurito' : $capacity('creator_capacity').' posti disponibili' }}</p>
                        </div>
                    </div>
                    <p class="mt-4 rounded-2xl bg-slate-100 p-3 text-sm font-bold text-slate-600">
                        Entrambi includono 60 giorni di prova gratuita.
                    </p>
                </div>
            </div>
            <div class="mt-6 flex flex-col gap-3 border-t border-white/10 pt-6 sm:flex-row">
                @if($purchasedPlan)
                    <form method="POST" action="{{ route('purchase.confirmation.resend') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full rounded-full bg-white px-5 py-4 text-base font-black text-slate-950 sm:text-lg">Richiedi nuovamente email acquisto</button>
                    </form>
                    <button id="keep-waitlist" type="button" class="w-full rounded-full border border-white/15 px-5 py-4 text-base font-black text-white sm:text-lg">Ho capito</button>
                @else
                    <form method="POST" action="{{ route('subscribe.access') }}" class="w-full">
                        @csrf
                        <input type="hidden" name="email" value="{{ session('waitlist_email') }}" />
                        <button id="block-discount" type="submit" class="w-full rounded-full bg-white px-5 py-4 text-base font-black text-slate-950 sm:text-lg">Scopri i Founder Pass</button>
                    </form>
                    <button id="keep-waitlist" type="button" class="w-full rounded-full border border-white/15 px-5 py-4 text-base font-black text-white sm:text-lg">Resto nella waitlist</button>
                @endif
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

        const modal = document.getElementById('waitlist-offer');
        const panel = document.getElementById('waitlist-offer-panel');
        if (!modal || !panel) return;
        requestAnimationFrame(() => {
            panel.style.opacity = '1';
            panel.style.transform = 'translateY(0) scale(1)';
        });
        const closeModal = () => {
            panel.style.transition = 'all 180ms ease-in';
            panel.style.opacity = '0';
            panel.style.transform = 'translateY(12px) scale(0.98)';
            setTimeout(() => modal.remove(), 200);
        };
        document.getElementById('keep-waitlist')?.addEventListener('click', closeModal);
        document.getElementById('waitlist-close-x')?.addEventListener('click', closeModal);
    })();
</script>
@endsection
