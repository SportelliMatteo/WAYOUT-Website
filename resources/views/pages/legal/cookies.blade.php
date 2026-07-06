@extends('pages.legal.layout', [
    'title' => 'Cookie policy',
    'description' => 'Informativa sull’uso di cookie e strumenti simili sul sito Wayout.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Cosa sono i cookie</h2>
        <p class="mt-3">I cookie sono piccoli file o strumenti tecnici salvati sul dispositivo dell’utente o letti dal sito per consentire il funzionamento del servizio, ricordare preferenze o, se presenti, misurare e personalizzare l’esperienza.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Cookie usati dal sito</h2>
        <p class="mt-3">Alla data di questa policy, il sito usa cookie e strumenti tecnici necessari al funzionamento di Laravel, della sessione, della protezione CSRF e della preferenza lingua.</p>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-600">
                    <tr><th class="px-4 py-3">Nome/tipologia</th><th class="px-4 py-3">Finalità</th><th class="px-4 py-3">Durata</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="px-4 py-3">Cookie di sessione Laravel</td><td class="px-4 py-3">Mantiene la sessione utente e il corretto funzionamento del sito</td><td class="px-4 py-3">Sessione o durata configurata</td></tr>
                    <tr><td class="px-4 py-3">Token CSRF</td><td class="px-4 py-3">Protegge i form da invii non autorizzati</td><td class="px-4 py-3">Sessione o durata configurata</td></tr>
                    <tr><td class="px-4 py-3">Preferenza lingua</td><td class="px-4 py-3">Ricorda la lingua scelta tra italiano e inglese</td><td class="px-4 py-3">Sessione</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Cookie di profilazione e marketing</h2>
        <p class="mt-3">Al momento non risultano implementati cookie di profilazione, advertising o analytics non tecnici nel codice del sito. Se in futuro verranno aggiunti strumenti di analytics, advertising, pixel o tracciamento, questa policy dovrà essere aggiornata e, ove richiesto, sarà implementato un meccanismo di consenso preventivo.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Servizi di terze parti</h2>
        <p class="mt-3">La pagina di acquisto può caricare script di Stripe per consentire il checkout. L’uso di servizi di pagamento può comportare il trattamento di dati da parte di Stripe secondo le proprie informative.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Gestione dal browser</h2>
        <p class="mt-3">Puoi gestire o cancellare i cookie dalle impostazioni del browser. Il blocco dei cookie tecnici potrebbe impedire il corretto funzionamento di form, sessione, login admin o checkout.</p>
    </section>
</div>
@endsection
