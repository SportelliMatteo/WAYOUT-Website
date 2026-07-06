@extends('pages.legal.layout', [
    'title' => 'Come funzionano i Pass',
    'description' => 'Condizioni, decorrenza e limitazioni dei Waitlist Pass e Founder Pass Wayout.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Tipologie di Pass</h2>
        <p class="mt-3">Wayout può offrire un Waitlist Pass gratuito e Founder Pass pre-lancio a pagamento, nei limiti delle disponibilità indicate sul sito. Le funzioni, la durata e le condizioni economiche sono quelle mostrate al momento dell’iscrizione o dell’acquisto.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Waitlist Pass</h2>
        <p class="mt-3">Il Waitlist Pass consente di accedere ai benefici indicati nella waitlist, inclusi eventuali giorni di prova gratuita. Il Waitlist Pass è pensato per partecipare a tavoli e attività già presenti e non include automaticamente la possibilità di creare o gestire tavoli.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Founder Pass 12 mesi</h2>
        <p class="mt-3">I Founder Pass 12 mesi sono offerte pre-lancio a disponibilità limitata. La durata decorre dal go-live dell’app Wayout, salvo diversa comunicazione ufficiale. I Founder Pass non sono cumulabili con il Waitlist Pass o con altri benefici incompatibili indicati da Wayout.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Founder Creator</h2>
        <p class="mt-3">Il Founder Creator è soggetto a verifica del profilo, gate qualità e rispetto delle regole community. Wayout può limitare, sospendere o non attivare funzionalità Creator se il profilo o l’utilizzo non risultano coerenti con gli standard richiesti.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Limitazioni e modifiche</h2>
        <p class="mt-3">I Pass non garantiscono disponibilità illimitata di eventi, tavoli, posti o funzionalità specifiche. Wayout può aggiornare le funzionalità dei Pass per ragioni tecniche, organizzative, di sicurezza o di qualità del servizio, nel rispetto della normativa applicabile.</p>
    </section>
</div>
@endsection
