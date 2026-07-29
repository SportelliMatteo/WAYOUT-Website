@extends('pages.legal.layout', [
    'title' => 'Termini sito e waitlist',
    'description' => 'Termini di utilizzo del sito WAYOUT e della waitlist pre-lancio.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Definizioni</h2>
        <p class="mt-3">Ai fini dei presenti Termini:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>“WAYOUT” o “Società”: WAYOUT S.r.l., titolare e gestore del Sito e della Waitlist;</li>
            <li>“Sito”: il sito web accessibile dal dominio wayoutapp.it e dalle relative pagine e sottopagine;</li>
            <li>“Utente”: la persona fisica che visita il Sito o utilizza una delle relative funzionalità;</li>
            <li>“Waitlist”: la lista pre-lancio mediante la quale un Utente maggiorenne manifesta interesse per il futuro servizio WAYOUT;</li>
            <li>“Utente valido”: l’Utente maggiorenne che ha fornito dati completi, veritieri e utilizzabili, ha accettato i presenti Termini e non presenta iscrizioni duplicate, abusive, fraudolente o effettuate mediante strumenti automatizzati;</li>
            <li>“Waitlist Pass”: l’eventuale beneficio gratuito di durata limitata, con sole funzionalità Join, riservato agli Utenti validi che soddisfano le condizioni indicate all’articolo 7;</li>
            <li>“Go-live”: la data in cui l’app WAYOUT viene resa pubblicamente disponibile nella città pilota indicata dalla Società.</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Informazioni sulla Società</h2>
        <p class="mt-3">Il Sito e la Waitlist sono gestiti da:</p>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Denominazione</strong></td>
                        <td class="px-4 py-3 align-top">WAYOUT S.r.l.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Sede legale</strong></td>
                        <td class="px-4 py-3 align-top">Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italia</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Codice fiscale e Partita IVA</strong></td>
                        <td class="px-4 py-3 align-top">14805930964</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>REA</strong></td>
                        <td class="px-4 py-3 align-top">MI-2808098</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>PEC</strong></td>
                        <td class="px-4 py-3 align-top"><a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Email di assistenza</strong></td>
                        <td class="px-4 py-3 align-top"><a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Sito</strong></td>
                        <td class="px-4 py-3 align-top"><a class="font-black text-violet-700" href="https://wayoutapp.it" target="_blank" rel="noopener noreferrer">wayoutapp.it</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3">Le informazioni societarie e i recapiti sono resi accessibili in modo permanente anche nel footer e nelle pagine legali del Sito.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Ambito, accettazione e prova dei Termini</h2>
        <p class="mt-3">3.1 I presenti Termini disciplinano l’accesso al Sito, l’uso dei form pubblici, l’iscrizione e la permanenza nella Waitlist, nonché l’eventuale attribuzione del Waitlist Pass.</p>
        <p class="mt-3">3.2 L’iscrizione alla Waitlist è completata quando l’Utente: (i) inserisce l’indirizzo email; (ii) completa i campi richiesti, inclusa la data di nascita e il numero di telefono; (iii) ha la possibilità di consultare, prima dell’invio, i presenti Termini e la <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> tramite i collegamenti resi disponibili nel form; e (iv) invia il form. Con l’invio, l’Utente richiede l’iscrizione alla Waitlist e accetta i presenti Termini. La <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> ha funzione informativa e non costituisce oggetto di consenso.</p>
        <p class="mt-3">3.3 WAYOUT può conservare, nel rispetto della <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a>, gli elementi necessari a documentare l’iscrizione e l’accettazione, tra cui data e ora, versione dei Termini, provenienza dell’evento, identificativo tecnico e, quando necessario e proporzionato, indirizzo IP e user agent.</p>
        <p class="mt-3">3.4 L’Utente può consultare, salvare o stampare i Termini in qualsiasi momento. La versione applicabile è quella accettata al momento dell’iscrizione, salvo successivi aggiornamenti validamente comunicati ai sensi dell’articolo 18.</p>
        <p class="mt-3">3.5 In caso di contrasto, le <a class="font-black text-violet-700" href="/termini-di-vendita">Condizioni di Pre-sale e vendita</a> prevalgono per gli acquisti dei Founder Pass; i futuri Termini dell’app prevalgono per l’uso dell’app e delle funzionalità sociali dopo il go-live.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Requisiti di età e capacità</h2>
        <p class="mt-3">4.1 La Waitlist è riservata esclusivamente a persone fisiche che abbiano compiuto 18 anni e dispongano della capacità necessaria per assumere gli impegni previsti dai presenti Termini.</p>
        <p class="mt-3">4.2 L’Utente deve indicare la propria data di nascita e garantisce che i dati forniti sono corretti. WAYOUT può richiedere verifiche ragionevoli prima di confermare benefici o accessi collegati al lancio.</p>
        <p class="mt-3">4.3 I minorenni non possono iscriversi alla Waitlist. Se WAYOUT rileva o ha ragionevoli motivi per ritenere che un’iscrizione riguardi un minorenne, può rifiutarla o rimuoverla e trattare i relativi dati secondo la <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> e la normativa applicabile.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Natura del Sito e del progetto pre-lancio</h2>
        <p class="mt-3">5.1 Il Sito presenta il progetto WAYOUT, raccoglie manifestazioni di interesse, consente di iscriversi alla Waitlist e, mediante pagine e condizioni separate, può consentire l’accesso alla pre-sale dei Founder Pass.</p>
        <p class="mt-3">5.2 WAYOUT è progettata come piattaforma digitale di aggregazione sociale tra utenti maggiorenni. In via ordinaria, WAYOUT non è un locale, non organizza direttamente eventi presso locali terzi, non prenota ingressi, liste o tavoli fisici, non vende ticket, drink o consumazioni e non incassa somme dovute ai locali, salvo diversa indicazione espressa riferita a una specifica iniziativa o partnership.</p>
        <p class="mt-3">5.3 Il Sito descrive funzionalità e obiettivi di un servizio in fase pre-lancio. Salvo impegni espressamente qualificati come vincolanti, roadmap, date, schermate, esempi, numeri e rappresentazioni dell’esperienza sono indicativi e possono evolvere durante lo sviluppo.</p>
        <p class="mt-3">5.4 L’iscrizione alla Waitlist non costituisce acquisto, prenotazione di servizi presso locali, titolo di ingresso, garanzia di partecipazione a eventi o conclusione di un contratto per l’utilizzo dell’app.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Iscrizione, posizione e gestione della Waitlist</h2>
        <p class="mt-3">6.1 L’iscrizione alla Waitlist è gratuita e non comporta alcun obbligo di acquistare un Founder Pass o altri servizi.</p>
        <p class="mt-3">6.2 L’Utente deve fornire almeno email, nome, cognome, data di nascita, prefisso e numero di telefono, nonché gli ulteriori dati indicati come obbligatori nel form. I dati devono essere completi, aggiornati, veritieri e riferibili all’Utente.</p>
        <p class="mt-3">6.3 È consentita una sola iscrizione per persona. Non sono ammesse iscrizioni duplicate mediante indirizzi email o recapiti differenti, iscrizioni per conto di terzi senza autorizzazione, iscrizioni automatizzate, account fittizi o tentativi di alterare la disponibilità dei posti.</p>
        <p class="mt-3">6.4 La priorità provvisoria nella Waitlist è determinata, di regola, dalla data e dall’ora della prima registrazione dell’email ricevuta correttamente dal sistema. La priorità diventa rilevante ai fini degli eventuali benefici solo dopo il completamento del profilo e la verifica dei requisiti di Utente valido.</p>
        <p class="mt-3">6.5 Se un’iscrizione risulta incompleta, duplicata, riferita a un minorenne, manifestamente falsa, fraudolenta o abusiva, WAYOUT può escluderla dal conteggio e rendere nuovamente disponibile il relativo posto.</p>
        <p class="mt-3">6.6 La Waitlist resta disponibile fino al raggiungimento della capienza prevista, salvo temporanee indisponibilità dovute a manutenzione, sicurezza, prevenzione degli abusi, adeguamenti normativi o problemi tecnici. La sola ricezione dell’email da parte del sistema non attribuisce un posto quando la capienza è già esaurita o il profilo non è stato completato correttamente.</p>
        <p class="mt-3">6.7 L’Utente può chiedere in qualsiasi momento la cancellazione dalla Waitlist scrivendo a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. La cancellazione comporta la perdita della posizione e degli eventuali benefici non ancora attivati, fatto salvo quanto previsto dalla normativa applicabile e dalla <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a>.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">7. Waitlist Pass gratuito</h2>
        <p class="mt-3">7.1 Il Waitlist Pass è un beneficio promozionale gratuito riservato, salvo diversa comunicazione ufficiale, ai primi 2.000 Utenti validi della Waitlist.</p>
        <p class="mt-3">7.2 Il Waitlist Pass ha durata di 60 giorni dalla corretta attivazione individuale nell’app, che potrà avvenire a partire dal go-live. Il beneficio comprende esclusivamente le funzionalità Join indicate nell’app e nelle relative condizioni; non comprende la creazione o la gestione di tavoli e non attribuisce funzionalità Creator.</p>
        <p class="mt-3">7.3 Per ricevere e attivare il Waitlist Pass, l’Utente deve, entro 30 giorni di calendario dall’email con cui WAYOUT comunica la disponibilità del beneficio, salvo un termine più lungo indicato nella stessa comunicazione: creare o completare l’account nell’app; mantenere il requisito di maggiore età; completare le verifiche effettivamente previste; accettare i Termini dell’app e prendere visione delle informative e policy applicabili; utilizzare un dispositivo e una versione dell’app compatibili.</p>
        <p class="mt-3">7.4 Il Waitlist Pass è personale, non cedibile, non trasferibile, non rivendibile e privo di valore monetario. Non può essere convertito in denaro, credito o servizi di locali terzi.</p>
        <p class="mt-3">7.5 Il Waitlist Pass non è cumulabile con un Founder Pass acquistato in pre-sale, con eventuali Launch Offer o con altre promozioni, salvo diversa indicazione espressa di WAYOUT. Se l’Utente acquista un Founder Pass, si applicano le relative condizioni e il beneficio gratuito non si somma alla durata acquistata.</p>
        <p class="mt-3">7.6 L’iscrizione alla Waitlist non attribuisce automaticamente il Waitlist Pass. Il beneficio spetta solo agli Utenti che rientrano nei primi 2.000 Utenti validi e completano correttamente, entro il termine comunicato, tutti gli adempimenti richiesti. Iscrizioni incomplete, duplicate, riferite a minorenni, fraudolente, automatizzate o basate su dati non corretti non sono considerate valide ai fini del beneficio.</p>
        <p class="mt-3">7.7 Il Waitlist Pass non garantisce la disponibilità di specifici tavoli, utenti, creator, serate, locali o occasioni sociali, né l’accettazione in uno specifico tavolo, l’ingresso presso locali terzi, la compatibilità personale tra utenti o un determinato risultato sociale.</p>
        <p class="mt-3">7.8 Poiché il Waitlist Pass è gratuito, l’eventuale mancato lancio del progetto o dell’app non dà diritto a rimborsi del beneficio o a indennizzi monetari. Restano fermi i diritti inderogabili previsti dalla legge e gli eventuali diritti derivanti da acquisti separati, disciplinati dalle <a class="font-black text-violet-700" href="/termini-di-vendita">Condizioni di Pre-sale e vendita</a>.</p>
        <p class="mt-3">7.9 Le modalità tecniche di attivazione o utilizzo del beneficio possono essere adeguate per ragioni oggettive di sicurezza, conformità, compatibilità o sviluppo. WAYOUT preserva, per quanto ragionevolmente possibile, la durata e la funzione essenziale del beneficio e comunica in modo chiaro eventuali modifiche sostanziali.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">8. Comunicazioni di servizio e marketing</h2>
        <p class="mt-3">8.1 L’iscrizione alla Waitlist comporta l’invio delle comunicazioni necessarie a gestire la richiesta dell’Utente, tra cui conferma dell’iscrizione, aggiornamenti essenziali sullo stato della Waitlist e del lancio, istruzioni per l’attivazione del Waitlist Pass, avvisi di sicurezza e modifiche rilevanti ai presenti Termini.</p>
        <p class="mt-3">8.2 Tali comunicazioni di servizio sono distinte dalle comunicazioni promozionali. Newsletter, offerte commerciali, promozioni e campagne relative alla pre-sale o ad altre iniziative saranno inviate solo sulla base di un consenso marketing separato, facoltativo e revocabile.</p>
        <p class="mt-3">8.3 La revoca del consenso marketing non cancella automaticamente l’iscrizione alla Waitlist e non impedisce l’invio delle comunicazioni strettamente necessarie a gestirla. L’Utente può invece chiedere la cancellazione dalla Waitlist ai sensi dell’articolo 6.7.</p>
        <p class="mt-3">8.4 Le modalità di trattamento dei dati e di revoca del consenso sono descritte nella <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a>.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">9. Obblighi e responsabilità dell’Utente</h2>
        <p class="mt-3">L’Utente si impegna a:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>utilizzare il Sito e la Waitlist in modo lecito, corretto e conforme alla loro finalità;</li>
            <li>fornire informazioni veritiere, aggiornate e non ingannevoli;</li>
            <li>non utilizzare dati o recapiti di terzi senza una base legittima;</li>
            <li>mantenere il controllo della propria casella email e dei recapiti comunicati;</li>
            <li>non compiere attività che possano compromettere sicurezza, integrità, disponibilità o prestazioni del Sito;</li>
            <li>segnalare senza ritardo eventuali usi non autorizzati dei propri recapiti o vulnerabilità riscontrate, evitando di sfruttarle o divulgarle in modo dannoso;</li>
            <li>rispettare la normativa applicabile, i diritti di WAYOUT e i diritti di terzi.</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">10. Usi vietati</h2>
        <p class="mt-3">È vietato, a titolo esemplificativo:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>tentare accessi non autorizzati, eludere misure di sicurezza, introdurre malware o interferire con il funzionamento del Sito;</li>
            <li>utilizzare bot, script, scraping o sistemi automatizzati per iscriversi, raccogliere dati, alterare capienze o replicare contenuti, salvo autorizzazione scritta;</li>
            <li>fornire identità o dati falsi, creare iscrizioni multiple, rivendere o trasferire posizioni e benefici;</li>
            <li>inviare tramite i form contenuti illeciti, minacciosi, diffamatori, discriminatori, fraudolenti, offensivi o lesivi di diritti altrui;</li>
            <li>usare marchi, contenuti o segni distintivi di WAYOUT in modo da suggerire partnership, approvazioni o autorizzazioni inesistenti;</li>
            <li>utilizzare il Sito per finalità commerciali non autorizzate, spam, phishing, raccolta abusiva di dati o altre attività illecite.</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">11. Pre-sale e Founder Pass acquistati</h2>
        <p class="mt-3">11.1 L’eventuale acquisto di un Founder Join 12M o Founder Creator 12M costituisce un rapporto separato, disciplinato dalle <a class="font-black text-violet-700" href="/termini-di-vendita">Condizioni di Pre-sale e vendita</a>, dalla pagina “<a class="font-black text-violet-700" href="/come-funzionano-i-pass">Come funzionano i Pass</a>”, dalle informazioni precontrattuali mostrate nel checkout e dalla disciplina su recesso e rimborsi.</p>
        <p class="mt-3">11.2 I presenti Termini non sostituiscono le condizioni economiche e contrattuali dell’acquisto. In caso di contrasto relativo a pagamento, durata, decorrenza, fatturazione, recesso o rimborso, prevalgono le <a class="font-black text-violet-700" href="/termini-di-vendita">Condizioni di Pre-sale e vendita</a> applicabili all’ordine.</p>
        <p class="mt-3">11.3 L’iscrizione alla Waitlist non obbliga l’Utente ad acquistare e non garantisce la disponibilità di Founder Pass, che possono essere soggetti a cap quantitativi o economici.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">12. Locali, utenti e soggetti terzi</h2>
        <p class="mt-3">12.1 Salvo partnership espressamente indicata, WAYOUT non è affiliata ai locali citati o rappresentati sul Sito e non è parte di prenotazioni, accordi, pagamenti o rapporti conclusi autonomamente tra utenti, creator, locali o altri soggetti terzi.</p>
        <p class="mt-3">12.2 L’accesso, i prezzi, la disponibilità, le regole di ingresso e i servizi offerti dai locali o da altri terzi restano disciplinati dai rispettivi soggetti e possono cambiare. L’Utente deve verificare direttamente con il fornitore interessato le condizioni applicabili. WAYOUT resta responsabile delle informazioni e dei servizi direttamente forniti dalla Società nei limiti previsti dalla legge.</p>
        <p class="mt-3">12.3 Il Sito può contenere collegamenti a siti o piattaforme esterne. Tali servizi sono gestiti dai rispettivi titolari e disciplinati dalle loro condizioni e informative. L’accesso è volontario e WAYOUT non gestisce né controlla il funzionamento dei servizi esterni, fermo restando quanto previsto dalla legge per contenuti o condotte direttamente imputabili alla Società.</p>
        <p class="mt-3">12.4 Eventuali qualifiche come “partner”, “ufficiale” o equivalenti saranno utilizzate solo in presenza di uno specifico rapporto o conferma effettiva.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">13. Proprietà intellettuale</h2>
        <p class="mt-3">13.1 Marchi, denominazioni, loghi, testi, grafiche, immagini, video, interfacce, layout, codice, database e contenuti del Sito sono di proprietà di WAYOUT o utilizzati sulla base di licenze e sono protetti dalla normativa applicabile.</p>
        <p class="mt-3">13.2 All’Utente è consentita esclusivamente la consultazione personale e non commerciale del Sito. È vietato riprodurre, modificare, distribuire, estrarre sistematicamente, comunicare al pubblico, creare opere derivate o sfruttare i contenuti senza autorizzazione, salvo quanto consentito inderogabilmente dalla legge.</p>
        <p class="mt-3">13.3 L’invio di osservazioni o suggerimenti non trasferisce a WAYOUT diritti su contenuti di terzi. L’Utente deve evitare di trasmettere materiali riservati o protetti senza autorizzazione.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">14. Disponibilità, manutenzione e sicurezza del Sito</h2>
        <p class="mt-3">14.1 WAYOUT adotta misure ragionevoli per mantenere il Sito disponibile, integro e sicuro. Come ogni servizio online, il Sito può tuttavia essere temporaneamente indisponibile o presentare errori, anche in relazione a dispositivi, browser, reti o configurazioni specifiche.</p>
        <p class="mt-3">14.2 Interruzioni o limitazioni temporanee possono verificarsi per manutenzione, aggiornamenti, sicurezza, prevenzione degli abusi, adeguamenti normativi, problemi dei fornitori, eventi di forza maggiore o altre esigenze tecniche e organizzative oggettive.</p>
        <p class="mt-3">14.3 Quando ragionevolmente possibile, WAYOUT riduce la durata delle interruzioni e comunica quelle programmate o particolarmente rilevanti. Le funzionalità, le pagine e i contenuti pre-lancio possono evolvere, nel rispetto delle informazioni espressamente vincolanti e dei diritti già maturati.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">15. Dichiarazioni e garanzie</h2>
        <p class="mt-3">15.1 I contenuti del Sito hanno finalità informative e promozionali relative a un progetto in fase pre-lancio. Fatte salve le informazioni espressamente vincolanti e i diritti inderogabili dell’Utente, WAYOUT non garantisce specifiche date di lancio, numero minimo di utenti, tavoli o esperienze, disponibilità in ogni città, né risultati personali o sociali.</p>
        <p class="mt-3">15.2 Screenshot, prototipi, esempi e descrizioni possono rappresentare funzionalità in sviluppo e non costituiscono garanzia che ogni elemento sarà disponibile nella medesima forma al go-live.</p>
        <p class="mt-3">15.3 Le date di lancio indicate sul Sito o nelle comunicazioni sono stime, salvo che siano espressamente definite come termini vincolanti in condizioni specifiche.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">16. Responsabilità di WAYOUT</h2>
        <p class="mt-3">16.1 WAYOUT risponde delle proprie condotte e degli inadempimenti direttamente imputabili alla Società secondo la normativa applicabile. Nei limiti consentiti dalla legge, non sono imputabili a WAYOUT interruzioni, ritardi o malfunzionamenti causati esclusivamente da reti, dispositivi o software dell’Utente, servizi di telecomunicazione, fornitori terzi, attacchi informatici non ragionevolmente prevenibili, forza maggiore o altri fatti estranei al controllo della Società.</p>
        <p class="mt-3">16.2 Gli utenti, i creator, i locali e gli altri terzi agiscono autonomamente nei rapporti e nelle attività che non costituiscono servizi direttamente forniti da WAYOUT. La Società non assume obblighi derivanti da promesse, prenotazioni, pagamenti o accordi conclusi esclusivamente tra tali soggetti, fermo restando quanto previsto dalla legge e la responsabilità di WAYOUT per informazioni o attività direttamente imputabili alla Società.</p>
        <p class="mt-3">16.3 Gli strumenti di verifica, moderazione, segnalazione, blocco e sicurezza riducono i rischi, ma non possono assicurare in modo assoluto l’identità, l’affidabilità o la condotta futura degli utenti. WAYOUT non svolge sorveglianza fisica degli incontri e non è un servizio di emergenza.</p>
        <p class="mt-3">16.4 Le persone interessate decidono autonomamente se incontrarsi, recarsi presso un locale, utilizzare mezzi di trasporto, consumare alcol o proseguire rapporti al di fuori della piattaforma. Devono adottare prudenza e rivolgersi direttamente alle autorità o ai servizi di emergenza in caso di pericolo.</p>
        <p class="mt-3">16.5 Nessuna disposizione dei presenti Termini esclude o limita la responsabilità di WAYOUT o i diritti dell’Utente nei casi in cui l’esclusione o la limitazione siano vietate dalla legge, inclusi dolo o colpa grave, danni alla persona direttamente imputabili alla Società e le altre tutele inderogabili riconosciute ai consumatori.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">17. Rifiuto, sospensione o rimozione dalla Waitlist</h2>
        <p class="mt-3">17.1 La validità e la permanenza dell’iscrizione presuppongono il possesso continuativo dei requisiti previsti dai presenti Termini. Non sono ammesse iscrizioni basate su dati falsi o incompleti, duplicate, automatizzate, illecite o abusive, né iscrizioni che compromettono la sicurezza o l’integrità del Sito.</p>
        <p class="mt-3">17.2 Quando emergono elementi oggettivi di irregolarità, WAYOUT può adottare le sole misure necessarie e proporzionate per verificare l’iscrizione, impedirne l’uso abusivo o escluderla dalla Waitlist. Quando è ragionevolmente possibile e compatibile con sicurezza, prevenzione delle frodi e obblighi legali, l’Utente riceve una comunicazione sintetica e può segnalare eventuali errori.</p>
        <p class="mt-3">17.3 L’esclusione di un’iscrizione non valida comporta la perdita della relativa posizione e degli eventuali benefici gratuiti non ancora attivati. Restano fermi i diritti inderogabili e i diritti relativi a eventuali acquisti separati, disciplinati dalle rispettive condizioni.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">18. Modifiche al Sito e ai Termini</h2>
        <p class="mt-3">18.1 WAYOUT può aggiornare i presenti Termini per modifiche del Sito o della Waitlist, esigenze tecniche o di sicurezza, evoluzione del progetto, cambiamenti normativi o chiarimenti redazionali. Gli aggiornamenti si applicano per il futuro e devono essere coerenti con la natura del servizio pre-lancio.</p>
        <p class="mt-3">18.2 La versione aggiornata è pubblicata sul Sito con la relativa data. Le modifiche meramente formali, necessarie per legge o favorevoli all’Utente possono avere effetto dalla pubblicazione.</p>
        <p class="mt-3">18.3 Le modifiche sostanziali che incidono negativamente sulla posizione di Utenti già iscritti sono comunicate con congruo anticipo tramite il Sito o email. Prima della loro efficacia, l’Utente può chiedere la cancellazione dalla Waitlist senza conseguenze ulteriori; quando la legge o la natura della modifica lo richiedono, viene richiesta una nuova accettazione.</p>
        <p class="mt-3">18.4 Le modifiche non pregiudicano retroattivamente i diritti già maturati, non riducono la durata di benefici già attivati e non derogano alle tutele inderogabili del consumatore.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">19. Privacy e cookie</h2>
        <p class="mt-3">Il trattamento dei dati personali è disciplinato dalla <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> del Sito. L’uso di cookie e tecnologie analoghe è descritto nella <a class="font-black text-violet-700" href="/cookie-policy">Cookie Policy</a> e gestito mediante il centro preferenze. Tali documenti sono distinti dai presenti Termini e sono accessibili in modo permanente dal footer del Sito.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">20. Assistenza, reclami e segnalazioni</h2>
        <p class="mt-3">20.1 Per richieste relative al Sito o alla Waitlist, cancellazioni, reclami o segnalazioni, l’Utente può scrivere a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. Per comunicazioni formali può utilizzare la PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>.</p>
        <p class="mt-3">20.2 L’Utente deve fornire informazioni sufficienti a identificare la richiesta e l’iscrizione interessata, evitando di trasmettere dati non necessari o di terzi.</p>
        <p class="mt-3">20.3 WAYOUT esaminerà le richieste in tempi ragionevoli, tenendo conto della natura e urgenza del caso. Il canale di assistenza non è un servizio di emergenza; in caso di pericolo immediato occorre contattare le autorità competenti.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">21. Legge applicabile, foro e disposizioni finali</h2>
        <p class="mt-3">21.1 I presenti Termini sono regolati dalla legge italiana, fatta salva l’applicazione delle norme inderogabili eventualmente più favorevoli previste dalla legge del Paese di residenza abituale del consumatore.</p>
        <p class="mt-3">21.2 Per ogni controversia con un Utente consumatore è competente il giudice del luogo di residenza o domicilio del consumatore, ove previsto dalla normativa applicabile. Per gli Utenti che non agiscono come consumatori resta competente il foro individuato secondo le regole ordinarie, salvo diverso accordo valido.</p>
        <p class="mt-3">21.3 Prima di adire l’autorità giudiziaria, l’Utente è invitato a contattare WAYOUT ai recapiti indicati all’articolo 20, senza che ciò limiti il diritto di rivolgersi al giudice o agli organismi di risoluzione alternativa delle controversie previsti dalla legge.</p>
        <p class="mt-3">21.4 Se una disposizione è dichiarata nulla, invalida o inefficace, le restanti disposizioni continueranno ad avere efficacia. La disposizione interessata sarà interpretata o sostituita, nei limiti consentiti, in modo coerente con la finalità originaria e con la normativa applicabile.</p>
        <p class="mt-3">21.5 L’eventuale mancato esercizio di un diritto non costituisce rinuncia allo stesso.</p>
        <p class="mt-3">21.6 La versione in lingua italiana è quella di riferimento. Eventuali traduzioni sono fornite per comodità e, in caso di divergenza, prevale la versione italiana, nei limiti consentiti dalla legge.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">22. Trasparenza e tutela inderogabile dell’Utente</h2>
        <p class="mt-3">22.1 I presenti Termini devono essere interpretati e applicati in modo coerente con la natura gratuita della Waitlist, con la trasparenza contrattuale e con la normativa a tutela dei consumatori. Le condizioni che incidono sull’iscrizione o sugli eventuali benefici si fondano sui requisiti oggettivi indicati nel documento e non attribuiscono a WAYOUT facoltà arbitrarie.</p>
        <p class="mt-3">22.2 Nessuna disposizione limita i diritti inderogabili dell’Utente. Le clausole eventualmente nulle, inefficaci o abusive non producono effetti nei confronti del consumatore; in caso di dubbio interpretativo, si applica l’interpretazione più favorevole al consumatore prevista dalla normativa applicabile.</p>
        <p class="mt-3"><strong>Contatti: <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> | PEC: <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a></strong></p>
    </section>
</div>
@endsection
