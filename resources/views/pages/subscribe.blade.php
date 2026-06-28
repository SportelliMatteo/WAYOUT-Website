@extends('layouts.app', [
    'title' => 'Acquisto Stripe - Wayout',
    'description' => 'Procedi al checkout Stripe per bloccare lo sconto esclusivo sulla waitlist di Wayout.'
])

@section('content')
<section class="relative overflow-hidden py-10 lg:py-20">
    <div class="mb-20 wayout-shell grid gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-start">
        <div>
            <span class="inline-flex rounded-full border border-violet-200 bg-white/80 px-4 py-2 text-sm font-black uppercase tracking-[0.22em] text-violet-700">
                Offerta waitlist
            </span>
            <h1 class="mt-6 max-w-4xl text-4xl font-black leading-[0.98] text-slate-950 sm:text-6xl">
                Blocca il tuo <span class="wayout-gradient-text">Founder Pass</span> prima del lancio.
            </h1>
            <p class="mt-5 max-w-2xl text-base font-medium leading-7 text-slate-600 sm:mt-6 sm:text-lg sm:leading-8">
                Sei già prenotato tra i 2.000 utenti della waitlist. I 60 giorni di prova gratuita sono garantiti: ora puoi scegliere il pass pre-lancio più adatto a te.
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="wayout-card rounded-[2rem] p-6">
                    <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-700">Prova gratuita</p>
                    <p class="mt-3 text-4xl font-black text-slate-950">60 giorni</p>
                    <p class="mt-2 text-sm font-semibold text-slate-500">Attiva appena l’app sarà online.</p>
                </div>
                <div class="wayout-card rounded-[2rem] p-6">
                    <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-700">Accessi</p>
                    <p class="mt-3 text-4xl font-black text-slate-950">2.000</p>
                    <p class="mt-2 text-sm font-semibold text-slate-500">Posti riservati ai primi iscritti.</p>
                </div>
            </div>

            <div class="mt-6 wayout-panel rounded-[2rem] p-5 sm:rounded-[2.5rem] sm:p-6">
                <p class="text-sm font-black uppercase tracking-[0.22em] text-slate-500">Scegli il tuo piano</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="cursor-pointer rounded-[2rem] border border-slate-200 bg-white p-5 transition hover:border-violet-300 has-[:checked]:border-violet-600 has-[:checked]:shadow-[0_18px_45px_rgba(124,35,245,0.15)]">
                        <input type="radio" name="plan" value="join" checked class="sr-only" />
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-lg font-black text-slate-950">Founder Join 12M</p>
                                <p class="mt-2 text-sm font-semibold text-slate-500">Partecipa a tavoli e feste private già esistenti.</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-black text-slate-950">29€</span>
                        </div>
                    </label>
                    <label class="cursor-pointer rounded-[2rem] border border-slate-200 bg-white p-5 transition hover:border-violet-300 has-[:checked]:border-violet-600 has-[:checked]:shadow-[0_18px_45px_rgba(124,35,245,0.15)]">
                        <input type="radio" name="plan" value="creator" class="sr-only" />
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-lg font-black text-slate-950">Founder Creator 12M</p>
                                <p class="mt-2 text-sm font-semibold text-slate-500">Crea e gestisci tavoli e feste private.</p>
                            </div>
                            <span class="rounded-full wayout-purple px-3 py-1 text-sm font-black text-white">59€</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-4 sm:flex-row">
                <button id="stripe-checkout-button" class="inline-flex w-full items-center justify-center rounded-full wayout-purple px-7 py-4 text-lg font-black text-white shadow-[0_18px_40px_rgba(124,35,245,0.32)] transition hover:scale-[1.01]">
                    Vai alla cassa Stripe
                </button>
                <a href="{{ route('home') }}" class="inline-flex w-full items-center justify-center rounded-full border border-slate-200 bg-white px-7 py-4 text-lg font-black text-slate-950 shadow-sm transition hover:border-violet-300">
                    Torna alla home
                </a>
            </div>
        </div>

        <aside class="rounded-[2rem] bg-slate-950 p-5 text-white shadow-[0_30px_100px_rgba(15,23,42,0.22)] sm:rounded-[2.5rem] sm:p-8 lg:sticky lg:top-32">
            <div class="rounded-[2rem] bg-[radial-gradient(circle_at_20%_0%,rgba(124,35,245,0.65),transparent_20rem)] p-1">
                <div class="rounded-[1.75rem] bg-slate-950/85 p-6">
                    <p class="text-sm font-black uppercase tracking-[0.26em] text-violet-200">Founder Pass</p>
                    <p class="mt-3 text-3xl font-black sm:text-4xl">Pre-lancio</p>
                    <div class="mt-6 space-y-4">
                        <div class="rounded-3xl bg-white/[0.08] p-5">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-black">Join</p>
                                <p class="text-3xl font-black">29€</p>
                            </div>
                            <p class="mt-2 text-sm text-slate-300">500 posti disponibili.</p>
                        </div>
                        <div class="rounded-3xl bg-white p-5 text-slate-950">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-black">Creator</p>
                                <p class="text-3xl font-black">59€</p>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-slate-500">150 posti disponibili.</p>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
                            <p class="font-black">Durata 12 mesi</p>
                            <p class="mt-2 text-sm text-slate-300">Prezzo esclusivo riservato alla waitlist.</p>
                        </div>
                    </div>
                </div>
            </div>
            
        </aside>
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
