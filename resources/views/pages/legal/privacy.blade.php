@extends('pages.legal.layout', [
    'title' => 'Privacy policy',
    'description' => 'Informativa sul trattamento dei dati personali degli utenti del sito WAYOUT.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Oggetto e ambito di applicazione</h2>
        <p class="mt-3">La presente Privacy Policy descrive come WAYOUT S.r.l. tratta i dati personali delle persone che visitano il sito wayoutapp.it, si iscrivono alla waitlist, completano il relativo profilo, richiedono informazioni, accedono alla pre-sale, acquistano un Founder Pass o contattano WAYOUT per assistenza, recesso, rimborso o per esercitare i propri diritti in materia di protezione dei dati personali.</p>
        <p class="mt-3">L’informativa si applica esclusivamente ai trattamenti effettuati nell’ambito del sito e dei flussi pre-lancio sopra indicati. I siti, i servizi e le piattaforme di terzi eventualmente raggiungibili tramite link, inclusi i social network e l’ambiente di pagamento Stripe, applicano le proprie informative privacy per i trattamenti svolti secondo i rispettivi ruoli. Il semplice collegamento a un servizio esterno non comporta di per sé l’attivazione di strumenti di tracciamento sul sito WAYOUT.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Titolare del trattamento e contatti</h2>
        <p class="mt-3">Il titolare del trattamento è:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>WAYOUT S.r.l.;</li>
            <li>Codice fiscale e Partita IVA: 14805930964;</li>
            <li>sede legale: Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italia;</li>
            <li>PEC: <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>;</li>
            <li>email per richieste privacy e protezione dei dati: <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>;</li>
            <li>email per assistenza generale: <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>.</li>
        </ul>
        <p class="mt-3">Le richieste relative all’esercizio dei diritti privacy devono essere preferibilmente inviate a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>, indicando nell’oggetto “Richiesta privacy”.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Categorie e origine dei dati trattati</h2>
        <h3 class="mt-6 text-xl font-black text-slate-900">3.1 Dati forniti direttamente dall’utente</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>dati della waitlist e del profilo: indirizzo email, nome, cognome, data di nascita, prefisso internazionale e numero di telefono;</li>
            <li>scelta relativa al consenso marketing, che è facoltativa e separata dall’iscrizione alla waitlist;</li>
            <li>dati del checkout e dell’acquisto: Founder Pass selezionato, dati anagrafici e di contatto confermati, richiesta di fattura e, solo se la fattura viene richiesta, codice fiscale, indirizzo, CAP, Comune, Provincia, Stato ed eventuale email di fatturazione;</li>
            <li>dati inseriti nei form di contatto: nome, email, oggetto e contenuto del messaggio;</li>
            <li>dati e comunicazioni forniti per assistenza, reclami, recesso, rimborsi o richieste privacy, inclusi il numero d’ordine e le informazioni necessarie a gestire la richiesta.</li>
        </ul>
        <h3 class="mt-6 text-xl font-black text-slate-900">3.2 Dati generati durante l’utilizzo del sito</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>data e ora di iscrizione o aggiornamento, identificativi interni, stato del profilo, stato dell’offerta e informazioni necessarie a gestire il limite dei posti disponibili;</li>
            <li>piano selezionato, importo, valuta, stato dell’ordine o del pagamento, numero o identificativo interno dell’acquisto e identificativo della sessione Stripe;</li>
            <li>eventuali evidenze relative alla presa visione delle informative, ai consensi e alle condizioni accettate, quali data, ora, fonte, versione del testo e, ove proporzionato, indirizzo IP;</li>
            <li>dati tecnici di navigazione e sicurezza, quali indirizzo IP, user agent, informazioni del browser, identificativi di sessione, token CSRF, preferenza lingua, log applicativi, eventi anti-spam e tentativi di accesso o utilizzo anomalo.</li>
            <li>dati raccolti, previo consenso e per la rispettiva categoria, mediante Google Tag Manager, Google Analytics 4 e Meta Pixel, quali identificativi online e cookie, informazioni su dispositivo e browser, indirizzo IP, area geografica approssimativa, pagina o contenuto visualizzato, fonte di provenienza, eventi, conversioni e interazioni con il sito e con le campagne pubblicitarie.</li>
        </ul>
        <h3 class="mt-6 text-xl font-black text-slate-900">3.3 Dati ricevuti da terzi</h3>
        <p class="mt-3">In relazione ai pagamenti, WAYOUT può ricevere da Stripe informazioni quali l’esito del pagamento, l’importo, la valuta, l’identificativo della sessione o della transazione, l’email del cliente e i metadati necessari a riconciliare l’ordine. I dati completi della carta di pagamento e il codice CVC non sono acquisiti o conservati direttamente da WAYOUT.</p>
        <p class="mt-3">In relazione all’invio delle comunicazioni, WAYOUT può ricevere da Brevo informazioni tecniche sull’invio, la consegna, i rimbalzi e le disiscrizioni. Alla data di questa versione, il tracciamento individuale delle aperture e dei clic mediante pixel o strumenti equivalenti è disattivato. In relazione alla misurazione e alla pubblicità online, Google e Meta possono restituire a WAYOUT statistiche, eventi, segmenti e informazioni aggregate o pseudonimizzate sull’interazione con il sito e le campagne, esclusivamente dopo il consenso pertinente.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">3.4 Dati particolari e contenuti liberi</h3>
        <p class="mt-3">WAYOUT non richiede, tramite il sito, categorie particolari di dati personali ai sensi dell’articolo 9 del GDPR, quali dati sanitari, biometrici, religiosi, politici o relativi alla vita sessuale. Si invita pertanto a non inserire tali informazioni nei campi a testo libero, salvo che siano strettamente necessarie per una specifica richiesta e sussista un’idonea base giuridica.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Finalità, basi giuridiche e tempi di conservazione</h2>
        <p class="mt-3">I dati sono trattati per le finalità e secondo le basi giuridiche indicate di seguito. I periodi di conservazione possono essere prolungati quando ciò sia necessario per adempiere a obblighi di legge, rispondere a richieste delle autorità, accertare, esercitare o difendere un diritto, oppure gestire un incidente o una contestazione concreta.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.1 Funzionamento tecnico del sito e sicurezza</h3>
        <p class="mt-3"><strong>Dati trattati: </strong>Dati di navigazione, sessione, token CSRF, preferenza lingua, IP, user agent, log tecnici ed eventi anti-spam o di sicurezza.</p>
        <p class="mt-3"><strong>Finalità: </strong>Consentire il funzionamento dei form e delle sessioni, mantenere l’accesso al flusso pre-sale, prevenire abusi e frodi, proteggere il sito, diagnosticare anomalie e garantire la continuità operativa.</p>
        <p class="mt-3"><strong>Base giuridica: </strong>Legittimo interesse di WAYOUT alla sicurezza e al corretto funzionamento del servizio, nonché adempimento degli obblighi di sicurezza applicabili (art. 6, par. 1, lett. c) e f), GDPR).</p>
        <p class="mt-3"><strong>Conservazione: </strong>I dati di sessione sono conservati per la durata tecnica configurata. I log tecnici ordinari sono conservati, di regola, per 90 giorni. I log di sicurezza e amministrativi possono essere conservati fino a 12 mesi; per gli accessi degli eventuali amministratori di sistema è assicurata una conservazione non inferiore a 6 mesi, ove applicabile. I dati possono essere conservati più a lungo quando ciò sia necessario per gestire incidenti, abusi, contenziosi o richieste delle autorità.</p>
        <p class="mt-3"><strong>Conferimento: </strong>I dati tecnici strettamente necessari sono raccolti automaticamente; il loro mancato trattamento può impedire il corretto funzionamento del sito.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.2 Iscrizione e gestione della waitlist</h3>
        <p class="mt-3"><strong>Dati trattati: </strong>Email, nome, cognome, data di nascita, prefisso e numero di telefono, timestamp, identificativo e stato dell’iscrizione, stato dell’offerta e posizione operativa rispetto al limite disponibile.</p>
        <p class="mt-3"><strong>Finalità: </strong>Registrare e gestire l’iscrizione, completare il profilo, verificare l’idoneità, gestire il limite degli utenti validi, associare l’utente al futuro account e rendere possibile l’eventuale attribuzione del Waitlist Pass secondo le condizioni applicabili.</p>
        <p class="mt-3"><strong>Base giuridica: </strong>Esecuzione di misure precontrattuali adottate su richiesta dell’interessato e legittimo interesse di WAYOUT a organizzare e proteggere la fase di pre-lancio (art. 6, par. 1, lett. b) e f), GDPR).</p>
        <p class="mt-3"><strong>Conservazione: </strong>Fino al go-live pubblico dell’app e per i 12 mesi successivi. Decorso tale termine, i dati sono cancellati o anonimizzati se l’utente non ha creato un account nell’app e non ha effettuato un acquisto, salvo una diversa base giuridica, obblighi di legge o esigenze di tutela dei diritti.</p>
        <p class="mt-3"><strong>Conferimento: </strong>I campi indicati come obbligatori sono necessari per completare l’iscrizione e accedere ai benefici o alle offerte collegati alla waitlist.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.3 Verifica del requisito di maggiore età</h3>
        <p class="mt-3"><strong>Dati trattati: </strong>Data di nascita ed esito della verifica 18+.</p>
        <p class="mt-3"><strong>Finalità: </strong>Verificare che l’utente sia maggiorenne, poiché il sito, la waitlist e la pre-sale sono riservati a persone di almeno 18 anni.</p>
        <p class="mt-3"><strong>Base giuridica: </strong>Esecuzione di misure precontrattuali e legittimo interesse di WAYOUT a garantire la sicurezza e la conformità del servizio (art. 6, par. 1, lett. b) e f), GDPR).</p>
        <p class="mt-3"><strong>Conservazione: </strong>Per il medesimo periodo applicabile al profilo waitlist o all’ordine. WAYOUT potrà adottare misure di minimizzazione, conservando in futuro il solo esito della verifica quando la data completa non sia più necessaria.</p>
        <p class="mt-3"><strong>Conferimento: </strong>Il conferimento è obbligatorio per accedere ai flussi riservati ai maggiorenni.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.4 Comunicazioni di servizio relative alla waitlist e al lancio</h3>
        <p class="mt-3"><strong>Dati trattati: </strong>Email, nome e informazioni relative all’iscrizione e all’idoneità al Waitlist Pass.</p>
        <p class="mt-3"><strong>Finalità: </strong>Inviare conferme di iscrizione, aggiornamenti strettamente necessari sulla disponibilità del servizio, istruzioni per completare la registrazione al go-live e informazioni operative sul Waitlist Pass.</p>
        <p class="mt-3"><strong>Base giuridica: </strong>Esecuzione di misure precontrattuali o del servizio richiesto dall’utente (art. 6, par. 1, lett. b), GDPR).</p>
        <p class="mt-3"><strong>Conservazione: </strong>Per la durata della waitlist e per il tempo necessario a completare l’eventuale attivazione del beneficio; successivamente secondo i periodi applicabili al profilo o al rapporto contrattuale.</p>
        <p class="mt-3"><strong>Conferimento: </strong>L’iscrizione comporta la ricezione delle sole comunicazioni strettamente necessarie alla gestione della waitlist e del beneficio richiesto.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.5 Marketing, newsletter e comunicazioni promozionali</h3>
        <p class="mt-3"><strong>Dati trattati: </strong>Email, nome, consenso marketing e relative evidenze; dati tecnici relativi a invio, consegna, rimbalzo e disiscrizione delle comunicazioni gestite tramite Brevo. Il tracciamento individuale di aperture e clic è disattivato alla data di questa versione.</p>
        <p class="mt-3"><strong>Finalità: </strong>Inviare offerte, inviti alla pre-sale, promozioni, novità commerciali e iniziative di WAYOUT.</p>
        <p class="mt-3"><strong>Base giuridica: </strong>Consenso libero, specifico, informato e revocabile (art. 6, par. 1, lett. a), e art. 7 GDPR; art. 130 del d.lgs. 196/2003).</p>
        <p class="mt-3"><strong>Conservazione: </strong>Fino alla revoca del consenso e, comunque, per un massimo di 24 mesi dall’ultima interazione attiva e documentabile dell’utente. La prova del consenso e della revoca può essere conservata per ulteriori 5 anni per finalità di accountability e difesa. I log tecnici di invio sono conservati secondo la configurazione del provider e la Data Retention Policy di WAYOUT, nei limiti necessari.</p>
        <p class="mt-3"><strong>Conferimento: </strong>Il consenso è facoltativo, non è preselezionato e il mancato consenso non impedisce l’iscrizione alla waitlist né l’acquisto.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.6 Accesso alla pre-sale, checkout e tentativi di acquisto</h3>
        <p class="mt-3"><strong>Dati trattati: </strong>Email, dati anagrafici e di contatto, piano selezionato, importo, valuta, stato del checkout, timestamp, identificativo interno e dati tecnici necessari alla prenotazione temporanea del pass.</p>
        <p class="mt-3"><strong>Finalità: </strong>Mostrare l’offerta pre-sale, verificare i requisiti, riservare temporaneamente la disponibilità, predisporre il checkout, gestire errori e tentativi non completati.</p>
        <p class="mt-3"><strong>Base giuridica: </strong>Esecuzione di misure precontrattuali su richiesta dell’utente e legittimo interesse di WAYOUT a gestire correttamente le disponibilità e prevenire abusi (art. 6, par. 1, lett. b) e f), GDPR).</p>
        <p class="mt-3"><strong>Conservazione: </strong>I dati relativi a tentativi non completati, falliti o scaduti sono conservati, di regola, per un massimo di 30 giorni dall’abbandono o dall’ultimo tentativo, salvo esigenze tecniche, antifrode o di tutela.</p>
        <p class="mt-3"><strong>Conferimento: </strong>I dati necessari al checkout sono obbligatori per procedere all’acquisto.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.7 Acquisto e gestione del Founder Pass</h3>
        <p class="mt-3"><strong>Dati trattati: </strong>Dati anagrafici e di contatto, pass selezionato, prezzo, valuta, numero d’ordine, data dell’acquisto, stato dell’ordine e del pagamento, identificativi Stripe, comunicazioni contrattuali e dati di attivazione.</p>
        <p class="mt-3"><strong>Finalità: </strong>Concludere ed eseguire il contratto di vendita, registrare e confermare l’ordine, associare e attivare il pass, fornire assistenza e gestire le comunicazioni contrattuali.</p>
        <p class="mt-3"><strong>Base giuridica: </strong>Esecuzione del contratto e adempimento di obblighi legali, fiscali e contabili (art. 6, par. 1, lett. b) e c), GDPR), nonché legittimo interesse alla tutela dei diritti (lett. f).</p>
        <p class="mt-3"><strong>Conservazione: </strong>Di regola 10 anni dalla conclusione dell’operazione o del rapporto, o per il diverso periodo previsto dalla normativa applicabile e dalle esigenze di tutela contrattuale.</p>
        <p class="mt-3"><strong>Conferimento: </strong>Il conferimento dei dati richiesti è necessario per acquistare e gestire il Founder Pass.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.8 Pagamento tramite Stripe</h3>
        <p class="mt-3"><strong>Dati trattati: </strong>Dati dell’ordine, email, importo, valuta, identificativi e stato della transazione. I dati completi della carta, il CVC e gli ulteriori dati antifrode sono trattati direttamente da Stripe.</p>
        <p class="mt-3"><strong>Finalità: </strong>Eseguire il pagamento, verificare l’esito, prevenire frodi, riconciliare l’ordine e gestire rimborsi o contestazioni.</p>
        <p class="mt-3"><strong>Base giuridica: </strong>Esecuzione del contratto, obblighi legali e legittimo interesse alla prevenzione delle frodi e alla tutela dei diritti (art. 6, par. 1, lett. b), c) e f), GDPR). Stripe tratta i dati secondo i ruoli e le basi giuridiche descritti nella propria informativa.</p>
        <p class="mt-3"><strong>Conservazione: </strong>WAYOUT conserva i riferimenti della transazione per il periodo applicabile all’ordine, di regola 10 anni. Stripe applica i propri tempi di conservazione.</p>
        <p class="mt-3"><strong>Conferimento: </strong>Il trattamento dei dati di pagamento è necessario per completare l’acquisto.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.9 Fatturazione e adempimenti contabili</h3>
        <p class="mt-3"><strong>Dati trattati: </strong>Nome, cognome, dati dell’ordine e, solo se l’utente richiede la fattura, codice fiscale, indirizzo, CAP, Comune, Provincia, Stato, eventuale email di fatturazione e gli ulteriori dati strettamente necessari all’emissione e alla conservazione del documento fiscale.</p>
        <p class="mt-3"><strong>Finalità: </strong>Emettere la fattura richiesta, registrare l’operazione, gestire rimborsi e adempiere agli obblighi fiscali e contabili.</p>
        <p class="mt-3"><strong>Base giuridica: </strong>Adempimento di obblighi legali ed esecuzione del contratto (art. 6, par. 1, lett. c) e b), GDPR).</p>
        <p class="mt-3"><strong>Conservazione: </strong>Di regola 10 anni o per il diverso termine previsto dalla normativa fiscale, contabile e civilistica applicabile.</p>
        <p class="mt-3"><strong>Conferimento: </strong>La richiesta della fattura è facoltativa; il codice fiscale diventa obbligatorio solo quando la fattura viene richiesta.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.10 Contatti, supporto e richieste informative</h3>
        <p class="mt-3"><strong>Dati trattati: </strong>Nome, email, oggetto, messaggio e ulteriori informazioni volontariamente fornite.</p>
        <p class="mt-3"><strong>Finalità: </strong>Rispondere a richieste di utenti, creator, locali, partner o altri interessati e mantenere lo storico necessario alla gestione del contatto.</p>
        <p class="mt-3"><strong>Base giuridica: </strong>Esecuzione di misure precontrattuali su richiesta dell’interessato oppure legittimo interesse di WAYOUT a gestire le comunicazioni e le relazioni con gli utenti e i partner (art. 6, par. 1, lett. b) e f), GDPR).</p>
        <p class="mt-3"><strong>Conservazione: </strong>Fino a 24 mesi dalla chiusura della richiesta. Se dal contatto nasce un contratto, un reclamo o una controversia, si applica il periodo pertinente al relativo rapporto.</p>
        <p class="mt-3"><strong>Conferimento: </strong>I campi obbligatori del form sono necessari per ricevere e gestire la richiesta.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.11 Recesso, rimborsi, reclami e contestazioni</h3>
        <p class="mt-3"><strong>Dati trattati: </strong>Identità, email, numero e dati dell’ordine, richiesta, comunicazioni, motivazione eventualmente fornita, esito, importo e riferimento del rimborso.</p>
        <p class="mt-3"><strong>Finalità: </strong>Gestire il diritto di recesso, le richieste di rimborso, i reclami, le contestazioni e la relativa documentazione.</p>
        <p class="mt-3"><strong>Base giuridica: </strong>Esecuzione del contratto, adempimento di obblighi legali e legittimo interesse alla gestione e difesa dei diritti (art. 6, par. 1, lett. b), c) e f), GDPR).</p>
        <p class="mt-3"><strong>Conservazione: </strong>10 anni quando la richiesta è collegata a un ordine o a un pagamento; negli altri casi, di regola, 5 anni dalla chiusura, salvo contenzioso o obblighi ulteriori.</p>
        <p class="mt-3"><strong>Conferimento: </strong>I dati necessari devono essere forniti per consentire l’identificazione dell’ordine e la valutazione della richiesta.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.12 Gestione delle richieste privacy</h3>
        <p class="mt-3"><strong>Dati trattati: </strong>Dati identificativi e di contatto, contenuto della richiesta, eventuali elementi necessari a verificare l’identità, risposta e documentazione delle attività svolte.</p>
        <p class="mt-3"><strong>Finalità: </strong>Ricevere, verificare e rispondere alle richieste di esercizio dei diritti previsti dal GDPR e documentare l’adempimento.</p>
        <p class="mt-3"><strong>Base giuridica: </strong>Adempimento di un obbligo legale (art. 6, par. 1, lett. c), GDPR).</p>
        <p class="mt-3"><strong>Conservazione: </strong>Di regola 5 anni dalla chiusura della richiesta, salvo necessità di conservazione più lunga per contestazioni o richieste delle autorità.</p>
        <p class="mt-3"><strong>Conferimento: </strong>WAYOUT può richiedere informazioni ragionevoli per verificare l’identità del richiedente.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.13 Prova di consensi, informative e condizioni accettate</h3>
        <p class="mt-3"><strong>Dati trattati: </strong>Identificativo utente o ordine, data e ora, fonte, versione del testo, scelta effettuata e, ove proporzionato, indirizzo IP.</p>
        <p class="mt-3"><strong>Finalità: </strong>Dimostrare il rispetto degli obblighi di trasparenza, la validità del consenso marketing e le condizioni contrattuali applicabili.</p>
        <p class="mt-3"><strong>Base giuridica: </strong>Consenso, esecuzione del contratto, obblighi legali e legittimo interesse probatorio (art. 6, par. 1, lett. a), b), c) e f), GDPR, secondo il singolo caso).</p>
        <p class="mt-3"><strong>Conservazione: </strong>Il log marketing è conservato per la durata del consenso e per 5 anni dalla revoca; le evidenze relative alla vendita sono conservate con l’ordine, di regola per 10 anni; le evidenze relative alla waitlist, di regola, per la durata del rapporto e 5 anni successivi.</p>
        <p class="mt-3"><strong>Conferimento: </strong>La registrazione della presa visione o accettazione è necessaria quando richiesta per accedere al relativo servizio o concludere il contratto.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">4.14 Analisi statistica, misurazione e pubblicità online</h3>
        <p class="mt-3"><strong>Dati trattati: </strong>Identificativi online e cookie, indirizzo IP, dati del browser e del dispositivo, area geografica approssimativa, URL e pagina visualizzata, referrer, eventi di navigazione, interazioni con i contenuti, conversioni e dati relativi alle campagne. Gli strumenti previsti sono Google Tag Manager, Google Analytics 4 e Meta Pixel lato browser.</p>
        <p class="mt-3"><strong>Finalità: </strong>Misurare l’utilizzo del sito e l’efficacia delle campagne, produrre statistiche, individuare problemi di navigazione, ottimizzare contenuti e flussi di waitlist/pre-sale e, tramite Meta Pixel, misurare campagne, creare pubblici o svolgere remarketing, ove attivato.</p>
        <p class="mt-3"><strong>Base giuridica: </strong>Consenso preventivo dell’utente (art. 6, par. 1, lett. a), GDPR e art. 122 del d.lgs. 196/2003). Google Tag Manager e Google Analytics 4 sono bloccati fino al consenso alla categoria Analytics; Meta Pixel è bloccato fino al consenso alla categoria Marketing. La configurazione iniziale utilizza Google Consent Mode in modalità Basic e non prevede l’invio a Google o Meta di segnali di tracciamento prima del consenso pertinente.</p>
        <p class="mt-3"><strong>Conservazione: </strong>I dati a livello di utente ed evento in Google Analytics 4 sono configurati per una conservazione di 14 mesi, salvo dati aggregati privi di identificazione personale. I dati e gli identificativi gestiti da Meta sono conservati secondo le impostazioni dell’account, le durate indicate nella Cookie Policy e le regole del fornitore. La scelta cookie viene conservata, di regola, per 6 mesi, salvo modifica anticipata delle preferenze o necessità di documentare il consenso.</p>
        <p class="mt-3"><strong>Conferimento: </strong>Il consenso è facoltativo e distinto per le categorie Analytics e Marketing. In caso di rifiuto, il sito e i servizi essenziali restano utilizzabili e i relativi strumenti non necessari non vengono attivati.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Natura obbligatoria o facoltativa del conferimento</h2>
        <p class="mt-3">I campi contrassegnati come obbligatori sono necessari per fornire il servizio richiesto. Il mancato conferimento può impedire l’iscrizione alla waitlist, la verifica del requisito 18+, l’accesso alla pre-sale, il completamento dell’acquisto o la gestione della richiesta.</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>Il consenso marketing è sempre facoltativo e separato. La sua mancata prestazione non produce conseguenze sull’accesso alla waitlist o sulla possibilità di acquistare.</li>
            <li>La richiesta di fattura è facoltativa. Il codice fiscale viene richiesto solo se l’utente seleziona l’opzione per ricevere la fattura.</li>
            <li>I dati tecnici strettamente necessari al funzionamento e alla sicurezza del sito sono raccolti automaticamente.</li>
            <li>Il consenso agli strumenti Analytics e Marketing è facoltativo e può essere prestato separatamente. Il rifiuto non impedisce l’accesso al sito, alla waitlist o alla pre-sale; impedisce soltanto l’attivazione dei tag non necessari.</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Comunicazioni di servizio e comunicazioni marketing</h2>
        <p class="mt-3">Le comunicazioni necessarie a confermare l’iscrizione, gestire la waitlist, informare sull’effettiva disponibilità del servizio, attivare il Waitlist Pass, confermare o gestire un acquisto, comunicare ritardi, modifiche contrattuali, recesso o rimborsi sono comunicazioni di servizio o contrattuali e possono essere inviate anche in assenza di consenso marketing, nei limiti in cui siano strettamente connesse alla richiesta o al rapporto con l’utente.</p>
        <p class="mt-3">Le comunicazioni che promuovono offerte, Founder Pass, vantaggi commerciali, iniziative o altre attività promozionali sono inviate soltanto agli utenti che hanno espresso uno specifico consenso marketing. Il consenso può essere revocato in qualsiasi momento mediante il link di disiscrizione presente nelle comunicazioni, quando disponibile, oppure scrivendo a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. La revoca non pregiudica la liceità dei trattamenti effettuati prima della revoca e non interrompe le comunicazioni strettamente necessarie alla gestione della waitlist o del contratto.</p>
        <p class="mt-3">Alla data di questa versione, WAYOUT non utilizza pixel di tracciamento o funzionalità equivalenti per rilevare individualmente l’apertura o i clic nelle email transazionali o marketing. Qualunque futura attivazione di tali strumenti sarà preceduta dall’aggiornamento dell’informativa e, quando richiesto dalla normativa applicabile, dalla raccolta di uno specifico consenso.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">7. Destinatari dei dati</h2>
        <p class="mt-3">I dati possono essere comunicati, nei limiti necessari, alle seguenti categorie di soggetti:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>personale, amministratori e collaboratori autorizzati da WAYOUT, nel rispetto di specifiche istruzioni e del principio di necessità;</li>
            <li>SiteGround e gli eventuali soggetti coinvolti nell’hosting, nell’infrastruttura, nel database, nei backup, nei log e nella sicurezza del sito. L’infrastruttura di hosting scelta da WAYOUT è localizzata all’interno dell’Unione europea;</li>
            <li>fornitori di sviluppo, manutenzione, assistenza tecnica e sicurezza informatica;</li>
            <li>Brevo, utilizzato per l’invio e la gestione delle email transazionali e delle comunicazioni marketing, incluse consegna, rimbalzi e disiscrizioni. Il tracciamento individuale di aperture e clic è disattivato alla data di questa versione;</li>
            <li>Google, per Google Tag Manager e Google Analytics 4, e Meta Platforms, per Meta Pixel lato browser, esclusivamente dopo il consenso pertinente e secondo i rispettivi ruoli privacy;</li>
            <li>Stripe e i relativi partner finanziari e tecnici per l’elaborazione dei pagamenti, la prevenzione delle frodi, la riconciliazione e i rimborsi;</li>
            <li>commercialista, consulenti fiscali, sistemi di fatturazione e altri soggetti necessari agli adempimenti amministrativi e contabili;</li>
            <li>consulenti legali, privacy, assicurativi o di sicurezza, quando necessario alla tutela di WAYOUT o degli utenti;</li>
            <li>autorità giudiziarie, amministrative, fiscali o di pubblica sicurezza e altri soggetti cui la comunicazione sia obbligatoria per legge o necessaria per accertare, esercitare o difendere un diritto.</li>
        </ul>
        <p class="mt-3">I dati non sono diffusi. I fornitori che trattano dati per conto di WAYOUT sono nominati responsabili del trattamento ai sensi dell’articolo 28 del GDPR, ove richiesto; altri soggetti, come Stripe per specifiche attività, possono operare anche quali autonomi titolari.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">8. Pagamenti tramite Stripe</h2>
        <p class="mt-3">Il checkout dei Founder Pass è gestito tramite Stripe. Quando l’utente avvia il pagamento, interagisce con un ambiente e con strumenti tecnici forniti da Stripe. Stripe può raccogliere e trattare dati di pagamento, transazione, dispositivo, rete e prevenzione frodi e, a seconda dell’attività, può agire come responsabile del trattamento per conto di WAYOUT e/o come autonomo titolare.</p>
        <p class="mt-3">WAYOUT riceve e conserva soltanto le informazioni necessarie a gestire l’ordine, quali l’esito, l’importo, la valuta, l’identificativo della sessione o transazione e i dati di riconciliazione. WAYOUT non riceve né conserva il numero completo della carta o il codice CVC. Per conoscere nel dettaglio i trattamenti svolti da Stripe, l’utente è invitato a consultare l’informativa privacy di Stripe disponibile sul relativo sito.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">9. Cookie, strumenti tecnici, analytics e pubblicità</h2>
        <p class="mt-3">Il sito utilizza cookie e strumenti tecnici necessari al funzionamento dell’applicazione Laravel, alla gestione della sessione, alla protezione CSRF, alla memorizzazione della preferenza lingua e alla registrazione delle scelte cookie. Stripe.js e gli strumenti tecnici di pagamento vengono caricati soltanto quando l’utente accede effettivamente al flusso di checkout. Gli strumenti strettamente necessari sono utilizzati sulla base della necessità tecnica e non richiedono consenso, fermo restando l’obbligo di informazione.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">9.1 Google Analytics 4</h3>
        <p class="mt-3">Previo consenso Analytics, WAYOUT utilizza Google Analytics 4 per ottenere statistiche sull’utilizzo del sito, sulle pagine visitate, sulle fonti di traffico, sugli eventi e sulle conversioni. Google Analytics può trattare cookie, identificativi online e informazioni su browser, dispositivo, indirizzo IP, area geografica approssimativa e interazioni. La configurazione iniziale prevede Google Signals, personalizzazione pubblicitaria, User-ID, tracciamento cross-domain ed Enhanced Conversions disattivati. WAYOUT non invia a Google email, numero di telefono, codice fiscale o altri dati direttamente identificativi.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">9.2 Google Tag Manager</h3>
        <p class="mt-3">Google Tag Manager è utilizzato per gestire e distribuire i tag del sito e non come banca dati degli utenti. Il contenitore e Google Analytics 4 vengono caricati soltanto dopo il consenso Analytics, secondo Google Consent Mode in modalità Basic. I tag appartenenti ad altre categorie, incluso Meta Pixel, possono attivarsi esclusivamente dopo il consenso specifico alla relativa categoria.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">9.3 Meta Pixel</h3>
        <p class="mt-3">Previo consenso Marketing, WAYOUT utilizza Meta Pixel esclusivamente lato browser per misurare visite e conversioni derivanti dalle campagne, comprendere l’efficacia della comunicazione pubblicitaria e, ove attivato, creare pubblici personalizzati o svolgere remarketing. Meta può ricevere identificativi online, cookie, indirizzo IP, informazioni su browser e dispositivo, pagina visitata, referrer ed eventi compiuti sul sito. Alla data di questa versione, Advanced Matching e Conversions API sono disattivati.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">9.4 Scelta e revoca del consenso</h3>
        <p class="mt-3">Google Analytics 4 e Meta Pixel non sono necessari per utilizzare il sito. L’utente può accettare, rifiutare o modificare separatamente le categorie Analytics e Marketing mediante il banner e il pannello delle preferenze cookie. La revoca ha effetto per il futuro e non pregiudica la liceità del trattamento svolto prima della revoca. Il rifiuto non impedisce l’iscrizione alla waitlist, l’accesso alla pre-sale o l’acquisto.</p>
        <p class="mt-3">Per l’elenco aggiornato dei cookie, le rispettive durate, i fornitori e le modalità di gestione si rinvia alla Cookie Policy pubblicata sul sito.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">10. Trasferimenti di dati al di fuori dello Spazio Economico Europeo</h2>
        <p class="mt-3">L’hosting principale del sito tramite SiteGround è configurato all’interno dell’Unione europea. Brevo dichiara che i server sui quali elabora e conserva i propri database sono situati nell’Unione europea. Google, Meta, Stripe o alcuni fornitori e subfornitori possono tuttavia trattare o rendere accessibili dati anche in Paesi situati al di fuori dello Spazio Economico Europeo.</p>
        <p class="mt-3">In tali casi WAYOUT verifica, per quanto di propria competenza, che il trasferimento avvenga nel rispetto degli articoli 44 e seguenti del GDPR, sulla base di una decisione di adeguatezza della Commissione europea, dell’adesione del destinatario a un quadro riconosciuto come adeguato, delle Clausole Contrattuali Standard approvate dalla Commissione europea o di un altro meccanismo valido, con eventuali misure supplementari ove necessarie.</p>
        <p class="mt-3">Ulteriori informazioni sulle garanzie applicate e, ove disponibile, una copia delle stesse possono essere richieste scrivendo a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">11. Profilazione e processi decisionali automatizzati</h2>
        <p class="mt-3">Previo consenso, Google Analytics 4 e Meta Pixel possono elaborare le interazioni con il sito per produrre statistiche, misurare campagne e, nel caso degli strumenti Marketing, creare segmenti o pubblici utilizzabili per attività promozionali e remarketing. Tali attività possono rientrare nella nozione di profilazione, ma non producono di per sé decisioni con effetti giuridici o analogamente significativi nei confronti dell’utente.</p>
        <p class="mt-3">WAYOUT non adotta tramite il sito decisioni basate unicamente su trattamenti automatizzati che producano effetti giuridici o incidano in modo analogo significativamente sull’utente ai sensi dell’articolo 22 del GDPR. Stripe può utilizzare sistemi automatizzati per la prevenzione delle frodi e la valutazione del rischio di pagamento secondo la propria informativa e i propri ruoli privacy.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">12. Utenti minorenni</h2>
        <p class="mt-3">Il sito, la waitlist e la pre-sale sono riservati a utenti di almeno 18 anni. La data di nascita viene richiesta per verificare tale requisito. Qualora WAYOUT venga a conoscenza della raccolta di dati di un minorenne in violazione delle condizioni di accesso, adotterà misure ragionevoli per cancellare i dati o limitarne il trattamento, salvo obblighi di legge o necessità di tutela.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">13. Sicurezza dei dati</h2>
        <p class="mt-3">WAYOUT adotta misure tecniche e organizzative ragionevoli e proporzionate al rischio per proteggere i dati da perdita, uso improprio, accesso non autorizzato, alterazione o divulgazione. Le misure includono, secondo la configurazione effettiva, connessioni cifrate, gestione delle sessioni e dei token di sicurezza, limitazione e autorizzazione degli accessi, backup, logging, controlli anti-spam e rate limiting.</p>
        <p class="mt-3">Nessun sistema informatico può tuttavia garantire una sicurezza assoluta. In caso di violazione dei dati personali, WAYOUT adotterà le misure previste dalla normativa applicabile, inclusa, quando richiesta, la notifica al Garante e la comunicazione agli interessati.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">14. Diritti dell’interessato</h2>
        <p class="mt-3">Nei casi e nei limiti previsti dal GDPR, l’interessato può esercitare i seguenti diritti:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>ottenere conferma che sia o meno in corso un trattamento e accedere ai propri dati e alle informazioni relative al trattamento;</li>
            <li>ottenere la rettifica dei dati inesatti e l’integrazione dei dati incompleti;</li>
            <li>ottenere la cancellazione dei dati quando ricorrono i presupposti previsti dalla legge;</li>
            <li>ottenere la limitazione del trattamento;</li>
            <li>ricevere i dati in formato strutturato, di uso comune e leggibile da dispositivo automatico e trasmetterli a un altro titolare, quando il diritto alla portabilità è applicabile;</li>
            <li>opporsi, per motivi connessi alla propria situazione particolare, ai trattamenti fondati sul legittimo interesse;</li>
            <li>opporsi in qualsiasi momento al trattamento per finalità di marketing diretto;</li>
            <li>revocare il consenso in qualsiasi momento, senza pregiudicare la liceità del trattamento svolto prima della revoca;</li>
            <li>non essere sottoposto a una decisione basata unicamente sul trattamento automatizzato nei casi previsti dall’articolo 22 del GDPR;</li>
            <li>proporre reclamo al Garante per la protezione dei dati personali o ad altra autorità di controllo competente.</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">15. Modalità di esercizio dei diritti</h2>
        <p class="mt-3">Le richieste possono essere inviate a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> oppure alla PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>. È utile indicare il diritto che si intende esercitare, l’indirizzo email utilizzato sul sito e ogni informazione necessaria a individuare i dati interessati.</p>
        <p class="mt-3">WAYOUT può chiedere informazioni aggiuntive ragionevolmente necessarie a verificare l’identità del richiedente. Il riscontro viene fornito senza ingiustificato ritardo e, di regola, entro un mese dal ricevimento della richiesta. Tale termine può essere prorogato di ulteriori due mesi nei casi previsti dal GDPR, tenuto conto della complessità e del numero delle richieste; in tal caso l’interessato viene informato della proroga e dei relativi motivi entro un mese.</p>
        <p class="mt-3">L’esercizio dei diritti è normalmente gratuito. In presenza di richieste manifestamente infondate o eccessive, in particolare per il loro carattere ripetitivo, WAYOUT può applicare le misure consentite dalla normativa.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">16. Reclamo al Garante</h2>
        <p class="mt-3">L’interessato che ritenga che il trattamento dei propri dati violi la normativa applicabile può proporre reclamo al Garante per la protezione dei dati personali, secondo le modalità indicate sul sito <a class="font-black text-violet-700" href="https://www.garanteprivacy.it" target="_blank" rel="noopener noreferrer">www.garanteprivacy.it</a>, oppure rivolgersi all’autorità di controllo dello Stato membro in cui risiede o lavora o in cui si è verificata la presunta violazione.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">17. Link e servizi di terzi</h2>
        <p class="mt-3">Il sito può contenere link a Instagram, TikTok, Stripe o altri siti e servizi esterni. Il semplice link non comporta, di per sé, l’installazione sul sito di pixel o strumenti di tracciamento del soggetto terzo. Dal momento in cui l’utente accede al servizio esterno, il trattamento è disciplinato dalle informative e dalle condizioni del relativo fornitore.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">18. Aggiornamenti della Privacy Policy</h2>
        <p class="mt-3">WAYOUT può aggiornare questa Privacy Policy per riflettere modifiche normative, organizzative, tecniche o dei servizi e fornitori utilizzati. La versione aggiornata sarà pubblicata sul sito con l’indicazione della data di revisione. In caso di modifiche rilevanti, WAYOUT potrà informare gli utenti tramite il sito o attraverso i recapiti disponibili, quando appropriato.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">19. Data di efficacia</h2>
        <p class="mt-3">La presente Privacy Policy è efficace dalla data della sua pubblicazione sul sito wayoutapp.it.</p>
    </section>
</div>
@endsection
