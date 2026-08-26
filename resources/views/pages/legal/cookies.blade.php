@extends('pages.legal.layout', [
    'title' => 'Cookie policy',
    'description' => 'Informativa sull’uso di cookie e strumenti simili sul sito WAYOUT.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Titolare del trattamento e contatti</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>WAYOUT S.r.l.;</li>
            <li>Codice fiscale e Partita IVA: 14805930964;</li>
            <li>sede legale: Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italia;</li>
            <li>PEC: <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>;</li>
            <li>email privacy: <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>;</li>
            <li>assistenza generale: <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>.</li>
        </ul>
        <p class="mt-3">Per le informazioni generali sul trattamento dei dati personali, sui destinatari, sui trasferimenti e sui diritti degli interessati si rinvia alla <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> pubblicata sul sito.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Cosa sono i cookie e gli altri strumenti di tracciamento</h2>
        <p class="mt-3">I cookie sono piccoli file di testo che un sito o un servizio di terza parte può memorizzare nel browser o nel dispositivo dell’utente e leggere durante la visita o in accessi successivi. Possono essere di sessione, quando vengono eliminati al termine della sessione o dopo un breve periodo di inattività, oppure persistenti, quando restano memorizzati più a lungo.</p>
        <p class="mt-3">Il sito può inoltre utilizzare tecnologie analoghe, quali local storage, identificativi online, pixel, tag, parametri URL e richieste di rete. In questa policy il termine “cookie” comprende, ove pertinente, anche tali strumenti.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Categorie di strumenti utilizzati</h2>
        <h3 class="mt-6 text-xl font-black text-slate-900">3.1 Strumenti strettamente necessari</h3>
        <p class="mt-3">Consentono la navigazione, la protezione dei form, la gestione della sessione, la registrazione delle preferenze cookie, il checkout e la prevenzione delle frodi. Il loro utilizzo non richiede consenso quando è limitato a quanto strettamente necessario per il servizio richiesto, fermo restando l’obbligo di informazione.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">3.2 Analytics</h3>
        <p class="mt-3">Google Tag Manager e Google Analytics 4 vengono caricati soltanto dopo il consenso dell’utente alla categoria Analytics. La configurazione utilizza Google Consent Mode in modalità Basic: prima dell’interazione con il banner i tag Google restano bloccati e non vengono trasmessi dati a Google.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">3.3 Marketing e profilazione</h3>
        <p class="mt-3">Meta Pixel lato browser viene attivato soltanto dopo il consenso alla categoria Marketing. Alla data di questa versione Advanced Matching e Conversions API sono disattivati. Eventuali future attivazioni richiederanno un aggiornamento preventivo della documentazione e, quando necessario, una nuova scelta dell’utente.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Gestione del consenso</h2>
        <p class="mt-3">WAYOUT utilizza una CMP interna. Al primo accesso sono attivi soltanto gli strumenti strettamente necessari. Il banner consente, con comandi chiari e di pari evidenza, di:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>accettare tutti gli strumenti non necessari;</li>
            <li>rifiutare tutti gli strumenti non necessari e proseguire con i soli strumenti tecnici;</li>
            <li>personalizzare separatamente le categorie Analytics e Marketing;</li>
            <li>chiudere il banner mantenendo le impostazioni predefinite, senza tracciamento non necessario.</li>
        </ul>
        <p class="mt-3">Lo scrolling, la prosecuzione della navigazione, il silenzio o caselle preselezionate non costituiscono consenso. L’utente può modificare o revocare in qualsiasi momento le proprie scelte mediante il collegamento “Gestisci preferenze cookie” disponibile nel footer o tramite un comando equivalente sempre accessibile.</p>
        <p class="mt-3">La scelta viene memorizzata mediante il cookie tecnico wayout_cookie_consent per 6 mesi e registrata nei log necessari a documentare le preferenze e la versione dell’informativa. Il banner può essere riproposto prima della scadenza in caso di cancellazione del cookie, modifica richiesta dall’utente o cambiamenti significativi delle finalità, categorie o terze parti.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Cookie e strumenti strettamente necessari</h2>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><em><strong>Nome / strumento</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Fornitore e dominio</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Finalità</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Durata indicativa</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Categoria / consenso</strong></em></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">wayout-session</td>
                        <td class="px-4 py-3 align-top">WAYOUT / wayoutapp.it</td>
                        <td class="px-4 py-3 align-top">Mantiene la sessione e collega i passaggi dei form, della waitlist, della pre-sale e dell’area amministrativa.</td>
                        <td class="px-4 py-3 align-top">120 minuti di inattività, salvo diversa configurazione di produzione</td>
                        <td class="px-4 py-3 align-top">Necessario - nessun consenso</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">wayout_cookie_consent</td>
                        <td class="px-4 py-3 align-top">WAYOUT / CMP interna</td>
                        <td class="px-4 py-3 align-top">Memorizza accettazione, rifiuto e preferenze granulari e consente di applicare la scelta.</td>
                        <td class="px-4 py-3 align-top">6 mesi, salvo modifica o revoca</td>
                        <td class="px-4 py-3 align-top">Preferenza tecnica - nessun consenso</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Token CSRF e dati di sicurezza</td>
                        <td class="px-4 py-3 align-top">WAYOUT / Laravel</td>
                        <td class="px-4 py-3 align-top">Protegge form e richieste da utilizzi non autorizzati. Può essere conservato nella sessione senza cookie autonomo.</td>
                        <td class="px-4 py-3 align-top">Durata della sessione</td>
                        <td class="px-4 py-3 align-top">Necessario - nessun consenso</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Preferenza lingua</td>
                        <td class="px-4 py-3 align-top">WAYOUT / wayoutapp.it</td>
                        <td class="px-4 py-3 align-top">Ricorda la lingua selezionata nella sessione.</td>
                        <td class="px-4 py-3 align-top">Durata della sessione</td>
                        <td class="px-4 py-3 align-top">Funzionalità richiesta - nessun consenso</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">__stripe_mid</td>
                        <td class="px-4 py-3 align-top">Stripe / domini Stripe</td>
                        <td class="px-4 py-3 align-top">Prevenzione delle frodi e valutazione del rischio della transazione.</td>
                        <td class="px-4 py-3 align-top">Fino a 1 anno</td>
                        <td class="px-4 py-3 align-top">Pagamento/antifrode - nessun consenso</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">__stripe_sid</td>
                        <td class="px-4 py-3 align-top">Stripe / domini Stripe</td>
                        <td class="px-4 py-3 align-top">Prevenzione delle frodi e valutazione del rischio della transazione.</td>
                        <td class="px-4 py-3 align-top">Circa 30 minuti</td>
                        <td class="px-4 py-3 align-top">Pagamento/antifrode - nessun consenso</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Altri identificativi antifrode Stripe</td>
                        <td class="px-4 py-3 align-top">Stripe / domini Stripe e m.stripe.network</td>
                        <td class="px-4 py-3 align-top">Segnali tecnici di sicurezza e antifrode; nomi e durata possono variare secondo il flusso Stripe.</td>
                        <td class="px-4 py-3 align-top">Sessione o durata variabile secondo Stripe</td>
                        <td class="px-4 py-3 align-top">Pagamento/antifrode - nessun consenso</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3">Gli strumenti tecnici di Stripe vengono caricati nell’ambiente di checkout esterno gestito dal backend WAYOUT, non alla semplice apertura della pagina di pre-sale sul sito. L’elenco effettivo degli identificativi viene verificato periodicamente mediante scansione tecnica e strumenti del browser.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Google Tag Manager e Google Analytics 4</h2>
        <h3 class="mt-6 text-xl font-black text-slate-900">6.1 Google Tag Manager</h3>
        <p class="mt-3">Google Tag Manager è utilizzato per amministrare e distribuire i tag del sito e non come banca dati degli utenti. Il contenitore viene caricato soltanto dopo il consenso Analytics. I tag appartenenti ad altre categorie possono attivarsi esclusivamente dopo il consenso specifico alla relativa categoria.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">6.2 Google Analytics 4</h3>
        <p class="mt-3">Previo consenso Analytics, WAYOUT utilizza Google Analytics 4 per statistiche sull’utilizzo del sito e misurazione delle conversioni. La configurazione iniziale prevede Google Signals, personalizzazione pubblicitaria, User-ID, tracciamento cross-domain ed Enhanced Conversions disattivati. WAYOUT non invia a Google email, numero di telefono, codice fiscale o altri dati direttamente identificativi.</p>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><em><strong>Nome / strumento</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Fornitore e dominio</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Finalità</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Durata indicativa</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Categoria / consenso</strong></em></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">_ga</td>
                        <td class="px-4 py-3 align-top">Google / wayoutapp.it</td>
                        <td class="px-4 py-3 align-top">Distingue i browser e consente la misurazione statistica.</td>
                        <td class="px-4 py-3 align-top">Fino a 2 anni, salvo configurazione o limiti del browser</td>
                        <td class="px-4 py-3 align-top">Analytics - consenso richiesto</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">_ga_*</td>
                        <td class="px-4 py-3 align-top">Google / wayoutapp.it</td>
                        <td class="px-4 py-3 align-top">Mantiene lo stato della sessione per la specifica proprietà Google Analytics 4.</td>
                        <td class="px-4 py-3 align-top">Fino a 2 anni, salvo configurazione o limiti del browser</td>
                        <td class="px-4 py-3 align-top">Analytics - consenso richiesto</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3">Il periodo di conservazione dei dati evento e utente nell’account GA4 è configurato in 14 mesi; tale periodo è distinto dalla durata dei cookie nel browser.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">7. Meta Pixel</h2>
        <p class="mt-3">Previo consenso Marketing, WAYOUT utilizza Meta Pixel esclusivamente lato browser per misurare visite e conversioni derivanti dalle campagne, comprendere l’efficacia della pubblicità, creare pubblici e svolgere eventuale remarketing. Meta può ricevere identificativi online, informazioni su browser e dispositivo, indirizzo IP, pagina visitata, referrer ed eventi compiuti sul sito, anche quando l’utente non possiede un account Facebook o Instagram o non vi è autenticato.</p>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><em><strong>Nome / strumento</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Fornitore e dominio</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Finalità</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Durata indicativa</strong></em></th>
                        <th class="px-4 py-3 align-top"><em><strong>Categoria / consenso</strong></em></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">_fbp</td>
                        <td class="px-4 py-3 align-top">Meta Platforms / wayoutapp.it</td>
                        <td class="px-4 py-3 align-top">Misurazione, attribuzione delle campagne, creazione di pubblici e advertising Meta.</td>
                        <td class="px-4 py-3 align-top">Fino a 90 giorni, secondo configurazione e policy Meta</td>
                        <td class="px-4 py-3 align-top">Marketing/profilazione - consenso richiesto</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">_fbc</td>
                        <td class="px-4 py-3 align-top">Meta Platforms / wayoutapp.it</td>
                        <td class="px-4 py-3 align-top">Memorizza l’identificativo del clic pubblicitario quando l’URL contiene il parametro fbclid.</td>
                        <td class="px-4 py-3 align-top">Fino a 90 giorni, se presente</td>
                        <td class="px-4 py-3 align-top">Marketing/profilazione - consenso richiesto</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">8. Link esterni e social network</h2>
        <p class="mt-3">Il footer può contenere semplici collegamenti ai profili WAYOUT su Instagram e TikTok. In assenza di pulsanti social incorporati, video embed, SDK o widget, il semplice link non determina l’installazione di cookie social sul sito WAYOUT. Dopo il clic e l’accesso alla piattaforma esterna, il relativo fornitore tratta i dati secondo le proprie informative.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">9. Trasferimenti verso Paesi non appartenenti allo SEE</h2>
        <p class="mt-3">L’hosting principale del sito è configurato nell’Unione europea. Google, Meta, Stripe o alcuni loro fornitori e subfornitori possono tuttavia trattare o rendere accessibili dati anche al di fuori dello Spazio Economico Europeo. I trasferimenti avvengono, secondo i rispettivi ruoli, sulla base di decisioni di adeguatezza, Clausole Contrattuali Standard o altri meccanismi previsti dagli articoli 44 e seguenti del GDPR. Ulteriori dettagli sono disponibili nella <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> e nelle informative dei fornitori.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">10. Come gestire i cookie dal browser</h2>
        <p class="mt-3">L’utente può cancellare o bloccare i cookie mediante le impostazioni del browser. Il blocco degli strumenti strettamente necessari può impedire il corretto funzionamento dei form, della sessione, della pre-sale, dell’area amministrativa o del checkout. Le impostazioni del browser non sostituiscono il pannello delle preferenze del sito, che consente di gestire in modo immediato le categorie Analytics e Marketing.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">11. Aggiornamento dell’inventario e della Cookie Policy</h2>
        <p class="mt-3">I nomi, i domini e le durate dei cookie di terze parti possono cambiare per aggiornamenti dei fornitori, dei browser o della configurazione tecnica. WAYOUT verifica periodicamente l’inventario mediante scansioni e test in ambiente di produzione e aggiorna questa policy e la CMP quando cambiano strumenti, finalità, categorie, durate o terze parti. In caso di cambiamenti significativi che incidano sulle scelte già espresse, il banner viene riproposto o viene richiesta una nuova scelta.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">12. Contatti</h2>
        <p class="mt-3">Per domande sulla presente Cookie Policy o sull’uso degli strumenti di tracciamento è possibile scrivere a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. Per esercitare i diritti previsti dal GDPR si rinvia alla <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> del sito.</p>
        <p class="mt-3"><em><strong>Data di efficacia: dalla pubblicazione sul sito wayoutapp.it.</strong></em></p>
    </section>
</div>
@endsection
