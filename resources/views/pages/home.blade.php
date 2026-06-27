@extends('layouts.app', [
    'title' => 'Wayout',
    'description' => 'Trova persone con cui condividere tavoli, serate e nuove esperienze.'
])

@section('content')

<section class="body-font">
    <div class="max-w-screen-2xl mx-auto px-5 lg:px-20 py-16 flex flex-col md:flex-row md:items-center items-center gap-12">
        <div class="md:flex-grow md:w-2/3 flex flex-col md:items-start md:text-left mb-16 md:mb-0 items-center text-center">
            <h1 class="w-full max-w-3xl mb-4 text-4xl font-extrabold tracking-tight leading-none md:text-5xl xl:text-6xl text-slate-900 md:text-left">
                Accedi in anteprima alla piattaforma che rivoluzionerà il modo di organizzare serate, trovare persone e unirsi ai tavoli più interessanti della tua città.
            </h1>
            <p class="mb-6 text-base md:text-lg lg:text-xl leading-relaxed w-full md:w-4/5 lg:w-3/4 text-slate-600 md:text-left">
                Solo 2.000 posti disponibili nella waitlist. 🔥
            </p>

            <div class="mt-6 w-full flex justify-start items-start gap-4">
                <div class="w-full max-w-full md:max-w-3xl bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-100 flex flex-col items-start gap-4">
                    <div class="w-full text-slate-900 text-left">
                        <strong class="block text-sm sm:text-base">Prenota ora il tuo accesso anticipato.</strong>
                    </div>

                    <div class="w-full">
                        <form method="POST" action="{{ route('waitlist.store') }}" class="flex flex-col gap-3 w-full">
                            @csrf
                            <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="inserisci la tua email"
                                required
                                class="w-full rounded-full border border-slate-200 px-4 py-2 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200"
                            />
                            <button type="submit" class="w-full rounded-full bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 font-semibold">Accedi alla Waitlist</button>
                        </form>

                        @error('email')
                            <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
                        @enderror

                        @if(session('waitlist_error'))
                            <p class="mt-2 text-sm text-rose-500">{{ session('waitlist_error') }}</p>
                        @endif

                        @if(session('waitlist_offer'))
                            @php
                                $alreadyRegistered = session('waitlist_status') === 'already_registered';
                                $purchasedPlan = session('purchased_plan');
                            @endphp
                            <div id="waitlist-offer" data-show="1" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
                                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" aria-hidden="true"></div>
                                <div id="waitlist-offer-panel" role="dialog" aria-modal="true" aria-labelledby="waitlist-offer-title" class="relative bg-slate-950/95 border border-white/10 rounded-[2.5rem] shadow-2xl max-w-4xl w-full mx-auto z-10 transform transition-all duration-300 opacity-0 translate-y-8 overflow-hidden">
                                    <button id="waitlist-close-x" aria-label="Chiudi" class="absolute right-5 top-5 text-slate-300 hover:text-white z-20">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <div class="grid gap-6 lg:grid-cols-[1.4fr_0.9fr] p-6 sm:p-8">
                                        <div class="space-y-5">
                                            <span class="inline-flex items-center rounded-full bg-slate-800/70 text-slate-100 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em]">Offerta riservata waitlist</span>
                                            <div class="space-y-4">
                                                <h3 id="waitlist-offer-title" class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                                                    @if($purchasedPlan)
                                                        Sei già nella waitlist e hai già acquistato
                                                    @else
                                                        {{ $alreadyRegistered ? 'Sei già nella waitlist' : 'Il tuo posto è confermato' }}
                                                    @endif
                                                </h3>
                                                <p class="text-slate-300 text-base sm:text-lg leading-relaxed">
                                                    @if($purchasedPlan)
                                                        L’email {{ session('waitlist_email') }} risulta già iscritta alla waitlist e ha già acquistato il piano <strong>{{ $purchasedPlan['name'] }}</strong>. La conferma del pagamento ti arriverà via email.
                                                    @elseif($alreadyRegistered)
                                                        L’email {{ session('waitlist_email') }} risulta già iscritta alla waitlist. Puoi comunque accedere alle offerte Founder Pass riservate ai primi iscritti e mantenere i tuoi <strong>60 giorni di prova gratuita</strong>.
                                                    @else
                                                        Hai riservato uno dei 2.000 posti disponibili su WAYOUT prima del lancio ufficiale. Iscrivendoti alla waitlist ottieni automaticamente <strong>60 giorni di prova gratuita</strong> e potrai accedere alle offerte Founder Pass riservate ai primi iscritti.
                                                    @endif
                                                </p>
                                            </div>

                                            <div class="grid gap-4 sm:grid-cols-2">
                                                <div class="rounded-3xl bg-white/5 border border-white/10 p-5">
                                                    <p class="text-sm uppercase tracking-[0.28em] text-slate-400">Prova gratuita</p>
                                                    <p class="mt-3 text-2xl font-bold text-white">60 giorni</p>
                                                    <p class="mt-2 text-sm text-slate-400">Attiva subito il tuo accesso di prova appena l’app sarà online.</p>
                                                </div>
                                                <div class="rounded-3xl bg-white/5 border border-white/10 p-5">
                                                    <p class="text-sm uppercase tracking-[0.28em] text-slate-400">Posti riservati</p>
                                                    <p class="mt-3 text-2xl font-bold text-white">2.000</p>
                                                    <p class="mt-2 text-sm text-slate-400">Disponibili solo per gli utenti iscritti alla waitlist.</p>
                                                </div>
                                            </div>

                                            <div class="rounded-3xl bg-white/5 border border-white/10 p-6 shadow-inner">
                                                @if($purchasedPlan)
                                                    <p class="text-sm text-slate-300">Piano acquistato</p>
                                                    <p class="mt-3 text-2xl font-bold text-white">{{ $purchasedPlan['name'] }}</p>
                                                    <p class="mt-2 text-sm text-slate-400">Pagamento registrato correttamente per {{ number_format($purchasedPlan['amount'] / 100, 2, ',', '.') }}€.</p>
                                                    @if(session('purchase_confirmation_success'))
                                                        <p class="mt-4 rounded-2xl border border-emerald-400/30 bg-emerald-400/10 p-3 text-sm text-emerald-100">{{ session('purchase_confirmation_success') }}</p>
                                                    @endif
                                                    @if(session('purchase_confirmation_error'))
                                                        <p class="mt-4 rounded-2xl border border-rose-400/30 bg-rose-400/10 p-3 text-sm text-rose-100">{{ session('purchase_confirmation_error') }}</p>
                                                    @endif
                                                @else
                                                    <p class="text-sm text-slate-300">Le offerte pre-lancio</p>
                                                    <ul class="mt-4 space-y-3 text-slate-300 text-sm">
                                                        <li>• Founder Join 12M Pass a 29€ per 12 mesi, 500 posti disponibili</li>
                                                        <li>• Founder 12M Creator Pass a 59€ per 12 mesi, 150 posti disponibili</li>
                                                        <li>• Entrambe includono 60 giorni di prova gratuita</li>
                                                    </ul>
                                                @endif
                                            </div>

                                            <div class="flex flex-col sm:flex-row gap-3">
                                                @if($purchasedPlan)
                                                    <form method="POST" action="{{ route('purchase.confirmation.resend') }}" class="w-full">
                                                        @csrf
                                                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-slate-200 hover:bg-slate-300 text-slate-950 px-6 py-3 text-lg font-semibold shadow-xl">Richiedi nuovamente email acquisto</button>
                                                    </form>
                                                    <button id="keep-waitlist" type="button" class="inline-flex w-full items-center justify-center rounded-full border border-slate-700 bg-slate-900 text-white px-6 py-3 text-lg font-semibold">Ho capito</button>
                                                @else
                                                    <form method="POST" action="{{ route('subscribe.access') }}" class="w-full">
                                                        @csrf
                                                        <input type="hidden" name="email" value="{{ session('waitlist_email') }}" />
                                                        <button id="block-discount" type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-slate-200 hover:bg-slate-300 text-slate-950 px-6 py-3 text-lg font-semibold shadow-xl">Scopri i Founder Pass</button>
                                                    </form>
                                                    <button id="keep-waitlist" type="button" class="inline-flex w-full items-center justify-center rounded-full border border-slate-700 bg-slate-900 text-white px-6 py-3 text-lg font-semibold">No, resto nella waitlist</button>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="rounded-[2rem] border border-white/10 bg-slate-900/80 p-6 backdrop-blur-xl text-white shadow-xl">
                                            <div class="mb-6">
                                                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Offerta esclusiva</p>
                                                <p class="mt-2 text-2xl font-extrabold">Founder Pass pre-lancio</p>
                                            </div>
                                            <div class="space-y-4">
                                                <div class="rounded-3xl bg-slate-950/90 p-5">
                                                    <p class="text-sm text-slate-400">Founder Join 12M Pass</p>
                                                    <p class="mt-2 text-3xl font-bold text-white">29€</p>
                                                    <p class="mt-2 text-sm text-slate-400">Partecipa a tavoli e feste private già esistenti</p>
                                                </div>
                                                <div class="rounded-3xl bg-slate-950/90 p-5">
                                                    <p class="text-sm text-slate-400">Founder 12M Creator Pass</p>
                                                    <p class="mt-2 text-3xl font-bold text-white">59€</p>
                                                    <p class="mt-2 text-sm text-slate-400">Include tutto il Join Pass e consente di creare e gestire tavoli e feste private</p>
                                                </div>
                                            </div>
                                            <div class="mt-6 rounded-3xl bg-white/5 p-4 text-sm text-slate-300">
                                                <p class="font-semibold text-white">Disponibile solo per chi è iscritto alla waitlist.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <script>
                                (function(){
                                    const modal = document.getElementById('waitlist-offer');
                                    const panel = document.getElementById('waitlist-offer-panel');
                                    if(!modal || !panel) return;
                                    const keep = document.getElementById('keep-waitlist');
                                    const block = document.getElementById('block-discount');
                                    const closeX = document.getElementById('waitlist-close-x');

                                    // animate in
                                    requestAnimationFrame(()=>{
                                        panel.style.opacity = '1';
                                        panel.style.transform = 'translateY(0) scale(1)';
                                    });

                                    function closeModal(){
                                        panel.style.transition = 'all 180ms ease-in';
                                        panel.style.opacity = '0';
                                        panel.style.transform = 'translateY(12px) scale(0.98)';
                                        setTimeout(()=> modal.remove(), 200);
                                    }

                                    keep?.addEventListener('click', function(){
                                        closeModal();
                                    });

                                    closeX?.addEventListener('click', function(){
                                        closeModal();
                                    });

                                    // Block discount keeps link behavior; you can add analytics here
                                    block?.addEventListener('click', function(e){
                                        // example: track then navigate; for now allow normal navigation
                                    });
                                })();
                            </script>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="md:w-2/5 w-full flex items-center justify-center">
            <div class="w-full max-w-sm bg-white rounded-3xl overflow-hidden">
                <img class="object-cover object-center w-full h-auto" alt="Wayout app mockup" src="/images/screen/1.png" />
            </div>
        </div>
    </div>
</section>

<section id="features" class="text-slate-700 body-font">
    <div class="container px-5 py-16 mx-auto">
        <h3 class="mt-10 scroll-m-20 text-3xl font-bold tracking-tight text-center text-slate-900">
            COME FUNZIONA
        </h3>
        <div class="mx-auto flex flex-col lg:flex-row items-center justify-center gap-10 mt-14">
            <div class="lg:w-1/2 w-full lg:mb-0 mb-6">
                <div id="feature-slideshow" class="relative w-full lg:max-w-3xl mx-auto" style="height:520px;">
                    <img src="/images/screen/2.png" alt="feature 1" class="absolute inset-0 w-full h-full object-contain rounded-xl" style="opacity:1; transition: opacity 700ms; background:white;" />
                    <img src="/images/screen/3.png" alt="feature 2" class="absolute inset-0 w-full h-full object-contain rounded-xl" style="opacity:0; transition: opacity 700ms; background:white;" />
                    <img src="/images/screen/4.png" alt="feature 3" class="absolute inset-0 w-full h-full object-contain rounded-xl" style="opacity:0; transition: opacity 700ms; background:white;" />
                </div>

                <script>
                    (function(){
                        const container = document.getElementById('feature-slideshow');
                        if(!container) return;
                        const slides = Array.from(container.querySelectorAll('img'));
                        let idx = 0;
                        const next = ()=>{
                            const prev = idx;
                            idx = (idx + 1) % slides.length;
                            slides[prev].style.opacity = 0;
                            slides[idx].style.opacity = 1;
                        };
                        let interval = setInterval(next, 3500);
                        // pause on hover
                        container.addEventListener('mouseenter', ()=> clearInterval(interval));
                        container.addEventListener('mouseleave', ()=> interval = setInterval(next, 3500));
                    })();
                </script>
            </div>
            <div class="flex flex-col gap-8 lg:py-6 lg:w-1/2 lg:pl-12 text-center lg:text-left">
                <div class="flex flex-col lg:items-start items-center">
                    <div class="w-12 h-12 inline-flex items-center justify-center rounded-full bg-slate-100 text-slate-700 mb-5">
                        <svg viewBox="0 0 512 512" fill="currentColor" class="w-6 h-6">
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M384 224v184a40 40 0 01-40 40H104a40 40 0 01-40-40V168a40 40 0 0140-40h167.48" />
                            <path d="M459.94 53.25a16.06 16.06 0 00-23.22-.56L424.35 65a8 8 0 000 11.31l11.34 11.32a8 8 0 0011.34 0l12.06-12c6.1-6.09 6.67-16.01.85-22.38zM399.34 90L218.82 270.2a9 9 0 00-2.31 3.93L208.16 299a3.91 3.91 0 004.86 4.86l24.85-8.35a9 9 0 003.93-2.31L422 112.66a9 9 0 000-12.66l-9.95-10a9 9 0 00-12.71 0z" />
                        </svg>
                    </div>
                    <div class="flex-grow">
                        <h2 class="text-slate-900 text-2xl font-medium mb-3">Organizza il tuo tavolo</h2>
                        <p class="leading-relaxed text-base">
                            Posta il tuo tavolo sull’app e aspetta che gli altri utenti si uniscano! Scegli il club, inserisci tutti i dettagli e pubblica il tuo tavolo.
                        </p>
                    </div>
                </div>
                <div class="flex flex-col lg:items-start items-center">
                    <div class="w-12 h-12 inline-flex items-center justify-center rounded-full bg-slate-100 text-slate-700 mb-5">
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-6 h-6">
                            <path stroke="none" d="M0 0h24v24H0z" />
                            <path d="M8 13V4.5a1.5 1.5 0 013 0V12M11 11.5v-2a1.5 1.5 0 013 0V12M14 10.5a1.5 1.5 0 013 0V12" />
                            <path d="M17 11.5a1.5 1.5 0 013 0V16a6 6 0 01-6 6h-2 .208a6 6 0 01-5.012-2.7L7 19c-.312-.479-1.407-2.388-3.286-5.728a1.5 1.5 0 01.536-2.022 1.867 1.867 0 012.28.28L8 13M5 3L4 2M4 7H3M14 3l1-1M15 6h1" />
                        </svg>
                    </div>
                    <div class="flex-grow">
                        <h2 class="text-slate-900 text-2xl font-medium mb-3">Unisciti ad un tavolo già formato</h2>
                        <p class="leading-relaxed text-base">
                            1. Cerca i tavoli organizzati da altri utenti vicino a te.<br />2. Unisciti a un tavolo disponibile.<br />3. Verrai aggiunto alla chat di gruppo del tavolo scelto.
                        </p>
                    </div>
                </div>
                <div class="flex flex-col lg:items-start items-center">
                    <div class="w-12 h-12 inline-flex items-center justify-center rounded-full bg-slate-100 text-slate-700 mb-5">
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-6 h-6">
                            <path d="M5.8 11.3L2 22l10.7-3.79M4 3h.01M22 8h.01M15 2h.01M22 20h.01M22 2l-2.24.75a2.9 2.9 0 00-1.96 3.12v0c.1.86-.57 1.63-1.45 1.63h-.38c-.86 0-1.6.6-1.76 1.44L14 10M22 13l-.82-.33c-.86-.34-1.82.2-1.98 1.11v0c-.11.7-.72 1.22-1.43 1.22H17M11 2l.33.82c.34.86-.2 1.82-1.11 1.98v0C9.52 4.9 9 5.52 9 6.23V7" />
                            <path d="M11 13c1.93 1.93 2.83 4.17 2 5-.83.83-3.07-.07-5-2-1.93-1.93-2.83-4.17-2-5 .83-.83 3.07.07 5 2z" />
                        </svg>
                    </div>
                    <div class="flex-grow">
                        <h2 class="text-slate-900 text-2xl font-medium mb-3">Non solo tavoli nei club</h2>
                        <p class="leading-relaxed text-base">
                            Sull’app di WAYOUT potrai organizzare o partecipare anche a feste private con altri ragazzi nelle vicinanze!<br />Unisciti ad un evento già formato oppure creane uno tu.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


@endsection
