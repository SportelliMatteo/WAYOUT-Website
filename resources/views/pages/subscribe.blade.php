@extends('layouts.app', [
    'title' => 'Acquisto Stripe - Wayout',
    'description' => 'Procedi al checkout Stripe per bloccare lo sconto esclusivo sulla waitlist di Wayout.'
])

@section('content')
<section class=" py-24">
    <div class="max-w-screen-2xl mx-auto px-5 lg:px-20">
        <div class="grid gap-10 lg:grid-cols-[1.4fr_0.9fr] items-center">
            <div class="space-y-6">
                <span class="inline-flex items-center rounded-full bg-slate-900 text-slate-100 px-4 py-2 text-sm font-semibold tracking-wide">Offerta riservata alla waitlist</span>
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-slate-900">Sei già in waitlist: scegli il tuo Founder Pass pre-lancio</h1>
                <p class="max-w-2xl text-base sm:text-lg text-slate-600 leading-relaxed">Sei già prenotato tra i <strong>2.000 utenti della waitlist</strong>: i <strong>60 giorni di prova gratuita</strong> sono garantiti con l’iscrizione. Completa il checkout Stripe per bloccare una delle due offerte Founder Pass riservate ai primi iscritti.</p>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-emerald-700">Prova gratuita</p>
                        <p class="mt-3 text-3xl font-bold text-emerald-900">60 giorni</p>
                        <p class="mt-2 text-sm text-emerald-700">Attiva il tuo accesso di prova appena l’app sarà online.</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Posti limitati</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">2.000</p>
                        <p class="mt-2 text-sm text-slate-500">Disponibili solo per i primi iscritti alla waitlist.</p>
                    </div>
                </div>

                <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-xl">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-700 text-white">🎯</span>
                            <div>
                                <p class="font-semibold text-slate-900">Promozione riservata ai primi iscritti</p>
                                <p class="text-sm text-slate-600">Le offerte Founder Pass sono disponibili solo durante la fase di pre-lancio.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-900 text-white">💎</span>
                            <div>
                                <p class="font-semibold text-slate-900">Accesso anticipato</p>
                                <p class="text-sm text-slate-600">Sei tra i primi a riservare il tuo posto su WAYOUT.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-900 text-white">⚡</span>
                            <div>
                                <p class="font-semibold text-slate-900">Founder Pass disponibili</p>
                                <p class="text-sm text-slate-600">Scegli tra Join Pass a 29€ e Creator Pass a 59€ per 12 mesi.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Scegli il tuo piano</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition hover:border-slate-400 has-[:checked]:border-slate-900 has-[:checked]:bg-slate-50">
                            <input type="radio" name="plan" value="join" checked class="sr-only" />
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">Founder Join 12M Pass</p>
                                    <p class="text-sm text-slate-500">29€ per 12 mesi</p>
                                </div>
                                <span class="text-sm font-semibold text-slate-900">Join</span>
                            </div>
                        </label>
                        <label class="cursor-pointer rounded-2xl border border-slate-200 p-4 transition hover:border-slate-400 has-[:checked]:border-slate-900 has-[:checked]:bg-slate-50">
                            <input type="radio" name="plan" value="creator" class="sr-only" />
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">Founder 12M Creator Pass</p>
                                    <p class="text-sm text-slate-500">59€ per 12 mesi</p>
                                </div>
                                <span class="text-sm font-semibold text-slate-900">Creator</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <button id="stripe-checkout-button" class="w-full inline-flex items-center justify-center rounded-full bg-slate-900 hover:bg-slate-800 text-white px-6 py-4 text-lg font-semibold shadow-xl transition">Vai alla cassa Stripe</button>
                    <a href="{{ route('home') }}" class="w-full inline-flex items-center justify-center rounded-full border border-slate-300 bg-white text-slate-900 px-6 py-4 text-lg font-semibold shadow-sm">Torna alla home</a>
                </div>
            </div>

            <div class="rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 p-8 text-black shadow-2xl overflow-hidden">
                <div class="rounded-[2rem] border border-white/10 bg-slate-950/95 p-8 backdrop-blur-xl">
                    <div class="mb-6 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Offerta esclusiva</p>
                            <p class="mt-2 text-2xl font-extrabold text-white">Founder Pass pre-lancio</p>
                        </div>
                        <div class="rounded-3xl bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-950">2 opzioni</div>
                    </div>
                    <div class="space-y-4">
                        <div class="rounded-3xl bg-slate-900/95 p-5">
                            <p class="text-sm text-slate-300">Founder Join 12M Pass</p>
                            <p class="mt-2 text-4xl font-extrabold text-white">29€</p>
                            <p class="mt-2 text-sm text-slate-400">Partecipa a tavoli e feste private già esistenti</p>
                        </div>
                        <div class="rounded-3xl bg-slate-900/95 p-5">
                            <p class="text-sm text-slate-300">Founder 12M Creator Pass</p>
                            <p class="mt-2 text-4xl font-extrabold text-white">59€</p>
                            <p class="mt-2 text-sm text-slate-400">Crea e gestisci tavoli e feste private</p>
                        </div>
                        <div class="rounded-3xl bg-slate-900/95 p-5">
                            <p class="text-sm text-slate-300">Durata</p>
                            <p class="mt-2 text-2xl font-semibold text-white">12 mesi</p>
                        </div>
                    </div>
                </div>
                <div class="mt-8 rounded-3xl bg-slate-900/90 p-6 text-sm text-slate-200">
                    <p class="font-semibold text-white">Perché conviene?</p>
                    <ul class="mt-4 space-y-3 text-slate-100">
                        <li>• Prezzo esclusivo riservato alla waitlist</li>
                        <li>• Disponibilità limitata a 500 Join Pass e 150 Creator Pass</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const button = document.getElementById('stripe-checkout-button');
    button?.addEventListener('click', async function () {
        button.disabled = true;
        button.textContent = 'Caricamento...';

        const selectedPlan = document.querySelector('input[name="plan"]:checked')?.value || 'join';
        const stripeKey = @json(config('services.stripe.key'));

        try {
            const response = await fetch("{{ route('subscribe.checkout') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ plan: selectedPlan }),
            });

            const data = await response.json();

            if (!response.ok || data.error) {
                alert(data.error || 'Impossibile avviare il checkout Stripe. Controlla la configurazione.');
                button.disabled = false;
                button.textContent = 'Vai alla cassa Stripe';
                return;
            }

            if (data.completed) {
                window.location.href = data.redirectUrl || '{{ route('checkout.success') }}';
                return;
            }

            if (!stripeKey) {
                alert('Stripe non è configurato. Inserisci STRIPE_KEY in .env.');
                button.disabled = false;
                button.textContent = 'Vai alla cassa Stripe';
                return;
            }

            const stripe = Stripe(stripeKey);
            const result = await stripe.redirectToCheckout({ sessionId: data.sessionId });

            if (result.error) {
                alert(result.error.message);
                button.disabled = false;
                button.textContent = 'Vai alla cassa Stripe';
            }
        } catch (error) {
            alert('Impossibile avviare il checkout Stripe. Riprova tra poco.');
            button.disabled = false;
            button.textContent = 'Vai alla cassa Stripe';
        }
    });
</script>
@endsection
