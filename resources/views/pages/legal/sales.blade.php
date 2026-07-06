@extends('pages.legal.layout', [
    'title' => 'Condizioni di vendita e recesso',
    'description' => 'Condizioni di acquisto dei Founder Pass Wayout e informazioni sul diritto di recesso.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Venditore</h2>
        <p class="mt-3">Il venditore è <strong>WAYOUT S.R.L.</strong>, C.F./P.IVA 14805930964, PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Prodotti digitali/pre-lancio</h2>
        <p class="mt-3">Il sito consente l’acquisto di Founder Pass pre-lancio, tra cui Join Pass e Creator Pass, con prezzo, durata e disponibilità indicati nella pagina di checkout. Il servizio è collegato al lancio e all’attivazione dell’app Wayout.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Prezzi e pagamento</h2>
        <p class="mt-3">I prezzi sono indicati in euro. Il pagamento può avvenire tramite Stripe o altri sistemi indicati nel checkout. L’ordine si considera ricevuto quando il pagamento risulta registrato come riuscito nei sistemi Wayout.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Conferma d’ordine</h2>
        <p class="mt-3">Dopo l’acquisto, l’utente riceve o può richiedere una conferma via email all’indirizzo associato alla waitlist. È responsabilità dell’utente inserire un indirizzo email corretto.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Diritto di recesso</h2>
        <p class="mt-3">Se acquisti come consumatore, hai normalmente diritto di recedere entro 14 giorni dalla conclusione del contratto, salvo eccezioni previste dalla legge per contenuti o servizi digitali già eseguiti con consenso espresso e accettazione della perdita del diritto di recesso.</p>
        <p class="mt-3">Per esercitare il recesso scrivi a <a class="font-black text-violet-700" href="mailto:hello@wayoutapp.it">hello@wayoutapp.it</a> indicando email di acquisto, pass acquistato e richiesta esplicita di recesso.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Rimborsi</h2>
        <p class="mt-3">Se il recesso o il rimborso è dovuto, Wayout provvederà al rimborso con lo stesso metodo di pagamento usato dall’utente, salvo diverso accordo, nei tempi previsti dalla normativa applicabile e dai circuiti di pagamento.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">7. Disponibilità limitata</h2>
        <p class="mt-3">Waitlist, Join Pass e Creator Pass hanno quantità configurabili e limitate. In caso di esaurimento, il sito può impedire nuove iscrizioni o acquisti.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">8. Assistenza</h2>
        <p class="mt-3">Per problemi relativi ad acquisti, conferme o accesso, scrivi a <a class="font-black text-violet-700" href="mailto:hello@wayoutapp.it">hello@wayoutapp.it</a>.</p>
    </section>
</div>
@endsection
