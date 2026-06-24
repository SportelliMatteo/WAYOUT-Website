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
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-slate-900">Hai già la waitlist: ora blocca lo sconto Founder Join 12M Pass</h1>
                <p class="max-w-2xl text-base sm:text-lg text-slate-600 leading-relaxed">Sei già prenotato nella waitlist: i <strong>60 giorni gratis</strong> sono garantiti con l’iscrizione. Completa il checkout Stripe per bloccare il <strong>50% di sconto</strong> sui primi 12 mesi.</p>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-emerald-700">Prova gratuita</p>
                        <p class="mt-3 text-3xl font-bold text-emerald-900">60 giorni gratis</p>
                        <p class="mt-2 text-sm text-emerald-700">Usa l’app senza limiti appena sarà online.</p>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Prezzo speciale early access</p>
                        <p class="mt-3 text-3xl font-extrabold text-slate-900">€2,99<span class="text-base font-semibold text-slate-500">/mese</span></p>
                        <p class="mt-2 text-sm text-slate-500 line-through">€4,99/mese dopo il lancio</p>
                    </div>
                </div>

                <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-xl">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-700 text-white">🎯</span>
                            <div>
                                <p class="font-semibold text-slate-900">Promozione riservata ai primi iscritti</p>
                                <p class="text-sm text-slate-600">Dopo il lancio questa offerta non sarà più disponibile.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-900 text-white">💎</span>
                            <div>
                                <p class="font-semibold text-slate-900">Accesso anticipato</p>
                                <p class="text-sm text-slate-600">Sei tra i primi ad assicurarti il posto in WAYOUT.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-900 text-white">⚡</span>
                            <div>
                                <p class="font-semibold text-slate-900">Sconto 50%</p>
                                <p class="text-sm text-slate-600">Blocca il prezzo speciale di €2,99/mese per i primi 12 mesi.</p>
                            </div>
                        </div>
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
                            <p class="mt-2 text-2xl font-extrabold text-white">Accesso VIP WAYOUT</p>
                        </div>
                        <div class="rounded-3xl bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-950">50% off</div>
                    </div>
                    <div class="space-y-4">
                        <div class="rounded-3xl bg-slate-900/95 p-5">
                            <p class="text-sm text-slate-300">Prezzo</p>
                            <p class="mt-2 text-4xl font-extrabold text-white">€2,99<span class="text-base font-medium text-slate-400">/mese</span></p>
                        </div>
                        <div class="rounded-3xl bg-slate-900/95 p-5">
                            <p class="text-sm text-slate-300">Durata</p>
                            <p class="mt-2 text-2xl font-semibold text-white">12 mesi</p>
                        </div>
                        <div class="rounded-3xl bg-slate-900/95 p-5">
                            <p class="text-sm text-slate-300">Bonus</p>
                            <p class="mt-2 text-2xl font-semibold text-white">60 giorni gratis</p>
                        </div>
                    </div>
                </div>
                <div class="mt-8 rounded-3xl bg-slate-900/90 p-6 text-sm text-slate-200">
                    <p class="font-semibold text-white">Perché conviene?</p>
                    <ul class="mt-4 space-y-3 text-slate-100">
                        <li>• Prezzo esclusivo riservato alla waitlist</li>
                        <li>• Sconto valido solo per i primi iscritti</li>
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

        const response = await fetch("{{ route('subscribe.checkout') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (!response.ok || data.error) {
            alert(data.error || 'Impossibile avviare il checkout Stripe. Controlla la configurazione.');
            button.disabled = false;
            button.textContent = 'Vai alla cassa Stripe';
            return;
        }

        const stripe = Stripe('{{ config('services.stripe.key') ?? 'pk_test_1234567890' }}');
        const result = await stripe.redirectToCheckout({ sessionId: data.sessionId });

        if (result.error) {
            alert(result.error.message);
            button.disabled = false;
            button.textContent = 'Vai alla cassa Stripe';
        }
    });
</script>
@endsection
