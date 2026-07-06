@extends('pages.legal.layout', [
    'title' => 'Privacy policy',
    'description' => 'Informativa sul trattamento dei dati personali degli utenti del sito Wayout.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Titolare del trattamento</h2>
        <p class="mt-3">Il titolare del trattamento è <strong>WAYOUT S.R.L.</strong>, C.F./P.IVA 14805930964, PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>.</p>
        <p class="mt-3">Per richieste privacy puoi scrivere a <a class="font-black text-violet-700" href="mailto:hello@wayoutapp.it">hello@wayoutapp.it</a> o, in mancanza, ai recapiti indicati nel sito.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Dati trattati</h2>
        <p class="mt-3">Attraverso il sito trattiamo le seguenti categorie di dati:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>dati di contatto inseriti nei form, come nome, email, oggetto e messaggio;</li>
            <li>email inserita per l’iscrizione alla waitlist e per l’accesso alle offerte Founder Pass;</li>
            <li>dati relativi agli acquisti Founder Pass, come email, tipologia di pass, importo, valuta, stato dell’ordine e identificativo sessione Stripe quando disponibile;</li>
            <li>dati tecnici e di navigazione necessari al funzionamento del sito, inclusi log applicativi, sessione, protezione CSRF, lingua scelta e informazioni tecniche del browser;</li>
            <li>dati necessari alla gestione dell’area amministrativa.</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Finalità e basi giuridiche</h2>
        <div class="mt-3 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Finalità</th>
                        <th class="px-4 py-3">Base giuridica</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="px-4 py-3">Gestione richieste inviate tramite form contatti</td><td class="px-4 py-3">Esecuzione di misure precontrattuali o legittimo interesse a rispondere alle richieste</td></tr>
                    <tr><td class="px-4 py-3">Iscrizione e gestione della waitlist</td><td class="px-4 py-3">Consenso dell’utente e/o esecuzione di misure richieste dall’utente</td></tr>
                    <tr><td class="px-4 py-3">Gestione dell’acquisto Founder Pass e comunicazioni connesse</td><td class="px-4 py-3">Esecuzione del contratto e adempimenti fiscali/contabili</td></tr>
                    <tr><td class="px-4 py-3">Sicurezza, prevenzione abusi, logging tecnico e amministrazione del sito</td><td class="px-4 py-3">Legittimo interesse e obblighi di sicurezza</td></tr>
                    <tr><td class="px-4 py-3">Invio di comunicazioni operative relative a waitlist, acquisto o conferme</td><td class="px-4 py-3">Esecuzione del servizio richiesto</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Conferimento dei dati</h2>
        <p class="mt-3">Il conferimento dei dati contrassegnati come obbligatori nei form è necessario per iscriversi alla waitlist, acquistare un Founder Pass o inviare una richiesta di contatto. In mancanza, non potremo fornire il servizio richiesto.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Destinatari e fornitori</h2>
        <p class="mt-3">I dati possono essere trattati da personale autorizzato e da fornitori tecnici che supportano hosting, database, email, sicurezza, manutenzione, pagamenti e infrastruttura. Per i pagamenti può essere coinvolto <strong>Stripe</strong>, che tratta dati necessari all’esecuzione del pagamento secondo i propri ruoli e informative.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Trasferimenti extra SEE</h2>
        <p class="mt-3">Alcuni fornitori tecnologici potrebbero trattare dati fuori dallo Spazio Economico Europeo. In tal caso il trasferimento avviene sulla base di strumenti previsti dal GDPR, come decisioni di adeguatezza, clausole contrattuali standard o altre garanzie applicabili.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">7. Tempi di conservazione</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>messaggi di contatto: per il tempo necessario a gestire la richiesta e comunque non oltre 24 mesi, salvo necessità di tutela legale;</li>
            <li>dati waitlist: fino al lancio del servizio o alla richiesta di cancellazione, salvo necessità tecniche o legali;</li>
            <li>dati acquisto: per il tempo richiesto da obblighi fiscali, contabili e di tutela contrattuale;</li>
            <li>log tecnici: per tempi proporzionati alla sicurezza e diagnosi del servizio.</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">8. Diritti dell’interessato</h2>
        <p class="mt-3">Puoi chiedere accesso, rettifica, cancellazione, limitazione, opposizione, portabilità dei dati ove applicabile, revoca del consenso e proporre reclamo al Garante per la protezione dei dati personali.</p>
        <p class="mt-3">Per esercitare i diritti scrivi a <a class="font-black text-violet-700" href="mailto:hello@wayoutapp.it">hello@wayoutapp.it</a>.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">9. Aggiornamenti</h2>
        <p class="mt-3">Questa informativa può essere aggiornata in caso di modifiche tecniche, organizzative o normative. La versione pubblicata sul sito è quella in vigore.</p>
    </section>
</div>
@endsection
