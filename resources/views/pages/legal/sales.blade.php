@extends('pages.legal.layout', [
    'title' => 'Termini di vendita',
    'description' => 'Condizioni generali applicabili alla formazione dell’ordine, al pagamento e alla gestione dell’acquisto online dei Founder Pass WAYOUT.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black uppercase text-slate-950">AMBITO</h2>
        <p class="mt-3">Il presente documento disciplina il processo di vendita online dei Founder Pass: informazioni precontrattuali, inoltro dell’ordine, pagamento tramite Stripe, conclusione del contratto, conferma su supporto durevole, fatturazione, assistenza, recesso e rimedi. Le caratteristiche specifiche dei pass e gli scenari della pre-sale restano disciplinati anche dalle separate <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a>.</p>
    </section>

        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><strong>LEGGERE PRIMA DELL’ACQUISTO</strong></p>
            <p class="text-slate-700">L’acquisto riguarda una subscription digitale in pre-sale, con pagamento unico, attivabile dal go-live pubblico dell’app e della durata di 12 mesi dalla corretta attivazione individuale. Non è previsto rinnovo automatico. Il Founder Pass non include ingressi, prenotazioni, tavoli fisici, drink, consumazioni o servizi forniti da locali o altri soggetti terzi.</p>
        </div>
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Definizioni</h2>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>“WAYOUT” o “Venditore”: WAYOUT S.r.l., società che vende i Founder Pass e gestisce il sito wayoutapp.it.</li>
            <li>“Acquirente” o “Consumatore”: la persona fisica maggiorenne che acquista un Founder Pass per finalità estranee alla propria attività imprenditoriale, commerciale, artigianale o professionale.</li>
            <li>“Sito”: il sito internet wayoutapp.it e le relative pagine di vendita e checkout.</li>
            <li>“Pre-sale”: la fase di vendita precedente al lancio pubblico dell’app WAYOUT.</li>
            <li>“Founder Pass”: Founder Join 12M o Founder Creator 12M, come descritti nelle <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a>.</li>
            <li>“Go-live”: la data in cui l’app WAYOUT viene resa pubblicamente disponibile per l’operatività iniziale nella città pilota di Milano.</li>
            <li>“Ordine”: la richiesta di acquisto trasmessa dall’Acquirente tramite il checkout online con obbligo di pagamento.</li>
            <li>“Stripe”: il prestatore esterno utilizzato per la gestione tecnica del pagamento e dei relativi strumenti antifrode.</li>
            <li>“Supporto durevole”: uno strumento che consente al Consumatore di conservare le informazioni a lui personalmente dirette e di riprodurle immutate per un periodo adeguato, inclusa l’email con documenti allegati o scaricabili in formato stabile.</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Identità del venditore e contatti</h2>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><strong>Informazione</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Dato</strong></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Denominazione</td>
                        <td class="px-4 py-3 align-top">WAYOUT S.r.l.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Sede legale</td>
                        <td class="px-4 py-3 align-top">Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italia</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Partita IVA / Codice fiscale</td>
                        <td class="px-4 py-3 align-top">14805930964</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Email assistenza</td>
                        <td class="px-4 py-3 align-top"><a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Email ordini, recesso e rimborsi</td>
                        <td class="px-4 py-3 align-top"><a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">PEC</td>
                        <td class="px-4 py-3 align-top"><a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3"><strong>2.1 </strong>L’Acquirente può utilizzare i contatti indicati per ottenere informazioni prima dell’acquisto e per comunicare rapidamente ed efficacemente con WAYOUT. Le comunicazioni relative a un ordine devono riportare, quando disponibile, l’email di acquisto e l’identificativo dell’ordine.</p>
        <p class="mt-3"><strong>2.2 </strong>L’Acquirente non deve comunicare a WAYOUT numeri completi di carta, codici di sicurezza, password o credenziali di pagamento.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Ambito di applicazione e documenti contrattuali</h2>
        <p class="mt-3"><strong>3.1 </strong>I presenti Termini disciplinano esclusivamente gli acquisti online dei Founder Pass effettuati tramite il Sito da Consumatori maggiorenni situati in Italia.</p>
        <p class="mt-3"><strong>3.2 </strong>Costituiscono parte integrante del rapporto, nella versione resa disponibile e accettata al momento dell’Ordine:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>i presenti <a class="font-black text-violet-700" href="/termini-di-vendita">Termini di vendita online</a>;</li>
            <li>le <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a>;</li>
            <li>la pagina e il riepilogo del Founder Pass selezionato;</li>
            <li>l’<a class="font-black text-violet-700" href="/recedere-dal-contratto">Informativa sul diritto di recesso</a> e la <a class="font-black text-violet-700" href="/recedere-dal-contratto">Refund Policy</a>;</li>
            <li>la <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a>, per quanto riguarda il trattamento dei dati personali;</li>
            <li>dopo il go-live, i Termini dell’app, le Community Guidelines e le Safety Policy applicabili all’utilizzo della piattaforma.</li>
        </ul>
        <p class="mt-3"><strong>3.3 </strong>In caso di contrasto, i presenti Termini prevalgono per le regole relative a ordine, pagamento, conclusione del contratto e fatturazione; le <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a> prevalgono per caratteristiche, durata, attivazione, limiti e scenari del Founder Pass. Le norme inderogabili a tutela del Consumatore prevalgono in ogni caso.</p>
        <p class="mt-3"><strong>3.4 </strong>L’acquisto non attribuisce diritti ulteriori rispetto a quelli espressamente descritti nei documenti contrattuali e non trasforma WAYOUT in organizzatore di eventi, intermediario di locali o venditore di servizi erogati da terzi.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Requisiti per effettuare l’acquisto</h2>
        <p class="mt-3"><strong>4.1 </strong>L’acquisto è riservato a persone che abbiano compiuto 18 anni, dispongano della capacità di concludere il contratto e utilizzino un mezzo di pagamento legittimamente disponibile.</p>
        <p class="mt-3"><strong>4.2 </strong>La Pre-sale è accessibile agli utenti registrati nella waitlist o comunque abilitati da WAYOUT secondo il flusso del Sito. L’accesso alla pagina non garantisce la disponibilità del pass fino al completamento del pagamento.</p>
        <p class="mt-3"><strong>4.3 </strong>L’Acquirente deve fornire dati completi, veritieri, aggiornati e riferibili alla propria persona, inclusi nome, cognome, data di nascita, numero di telefono ed email. WAYOUT può richiedere la correzione di errori o informazioni mancanti necessarie per l’ordine, la fattura o l’attivazione.</p>
        <p class="mt-3"><strong>4.4 </strong>Gli acquisti effettuati da minorenni, mediante dati falsi, strumenti automatizzati, identità altrui, mezzi di pagamento non autorizzati o condotte fraudolente possono essere rifiutati o annullati, fermo restando il rimborso delle somme eventualmente dovute secondo legge e l’adozione delle misure antifrode necessarie.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Caratteristiche principali e prezzi</h2>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><strong>Piano</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Prezzo totale</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Durata e decorrenza</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Disponibilità massima</strong></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Founder Join 12M</td>
                        <td class="px-4 py-3 align-top">€29, IVA e oneri applicabili inclusi</td>
                        <td class="px-4 py-3 align-top">12 mesi dalla corretta attivazione individuale, possibile dal go-live; pagamento unico; nessun rinnovo automatico</td>
                        <td class="px-4 py-3 align-top">Fino a 600 pass</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">Founder Creator 12M</td>
                        <td class="px-4 py-3 align-top">€59, IVA e oneri applicabili inclusi</td>
                        <td class="px-4 py-3 align-top">12 mesi dalla corretta attivazione individuale, possibile dal go-live; pagamento unico; nessun rinnovo automatico</td>
                        <td class="px-4 py-3 align-top">Fino a 200 pass</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3"><strong>5.1 </strong>Il prezzo totale viene mostrato in euro prima dell’inoltro dell’Ordine ed è comprensivo di IVA e degli altri oneri fiscali applicabili. Non sono previsti costi di rinnovo automatico o commissioni applicate direttamente da WAYOUT per il pagamento.</p>
        <p class="mt-3"><strong>5.2 </strong>Le funzionalità, le esclusioni, i cap quantitativi, il cap economico complessivo della Pre-sale e gli scenari relativi al lancio sono descritti nelle <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a>.</p>
        <p class="mt-3"><strong>5.3 </strong>Il Founder Pass è personale, non cedibile e destinato a essere associato all’account dell’Acquirente. Il Founder Creator 12M non richiede un’ulteriore selezione discrezionale rispetto ai requisiti standard di registrazione e attivazione previsti per gli utenti.</p>
        <p class="mt-3"><strong>5.4 </strong>La mera visualizzazione o selezione di un piano non ne riserva definitivamente la disponibilità. Una eventuale riserva tecnica durante il checkout è temporanea e può scadere se il pagamento non viene completato.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Informazioni prima dell’ordine</h2>
        <p class="mt-3"><strong>6.1 </strong>Prima di essere vincolato, l’Acquirente può consultare e salvare le informazioni relative a identità e contatti del Venditore, caratteristiche del Founder Pass, prezzo totale, durata, decorrenza, modalità di pagamento, assenza di rinnovo automatico, diritto di recesso, rimborsi, assistenza, conformità del servizio digitale e principali esclusioni.</p>
        <p class="mt-3"><strong>6.2 </strong>Immediatamente prima del passaggio al pagamento, il checkout presenta in modo chiaro almeno il piano selezionato, il prezzo totale, la durata di 12 mesi dalla corretta attivazione individuale dell’account nell’app (attivabile a partire dal go-live), l’assenza di rinnovo automatico, la natura di pre-sale e i link ai documenti contrattuali applicabili.</p>
        <p class="mt-3"><strong>6.3 </strong>L’Acquirente è tenuto a leggere il riepilogo e i documenti collegati prima di procedere. La <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> costituisce informativa sul trattamento dei dati e non sostituisce l’accettazione dei documenti contrattuali.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">7. Fasi tecniche per concludere l’acquisto</h2>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><strong>Fase</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Operazione</strong></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">1. Accesso</td>
                        <td class="px-4 py-3 align-top">Inserimento o riconoscimento dell’email associata alla waitlist e accesso alla pagina di Pre-sale.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">2. Scelta del piano</td>
                        <td class="px-4 py-3 align-top">Selezione del Founder Join 12M o del Founder Creator 12M, nei limiti della disponibilità.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">3. Verifica dei dati</td>
                        <td class="px-4 py-3 align-top">Inserimento o conferma dei dati anagrafici, della maggiore età e del numero di telefono.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">4. Fattura facoltativa</td>
                        <td class="px-4 py-3 align-top">Selezione dell’opzione per richiedere fattura e inserimento o conferma di nome, cognome, codice fiscale, indirizzo e numero civico, CAP, comune, provincia, Stato ed e-mail alla quale inviare la copia della fattura.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">5. Riepilogo e accettazioni</td>
                        <td class="px-4 py-3 align-top">Controllo delle informazioni essenziali e completamento delle dichiarazioni e accettazioni obbligatorie.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">6. Pagamento</td>
                        <td class="px-4 py-3 align-top">Attivazione del pulsante che indica in modo inequivocabile l’obbligo di pagare e trasferimento a Stripe Checkout.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900">7. Esito</td>
                        <td class="px-4 py-3 align-top">Visualizzazione dell’esito del pagamento e, in caso di successo, registrazione dell’Ordine e invio della conferma via email.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3"><strong>7.1 </strong>Prima dell’invio, l’Acquirente può correggere i dati mediante i comandi del form, tornare alla selezione del piano o interrompere il checkout senza concludere l’acquisto.</p>
        <p class="mt-3"><strong>7.2 Dopo l’inoltro, eventuali errori nei dati devono essere segnalati senza ritardo a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> o <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. Le correzioni sono possibili nei limiti tecnici, fiscali e antifrode applicabili e non consentono di trasferire il pass a una persona diversa.</strong></p>
        <p class="mt-3"><strong>7.3 </strong>Il contratto è concluso in lingua italiana. WAYOUT archivia elettronicamente i dati essenziali dell’Ordine e la versione dei documenti contrattuali accettati; l’Acquirente può richiederne copia ai contatti indicati.</p>
        <p class="mt-3"><strong>7.4 </strong>WAYOUT non aderisce, allo stato, a codici di condotta specifici relativi al processo di vendita, salvo diversa comunicazione pubblicata sul Sito.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">8. Passaggio al pagamento e obbligo sul comando finale</h2>
        <p class="mt-3"><strong>8.1 </strong>Nel sito WAYOUT il comando “Conferma e vai al pagamento” valida i dati, crea o riconosce l’account, verifica disponibilità e idoneità, registra un ordine provvisorio e trasferisce l’Acquirente al checkout Stripe. Tale comando non costituisce ancora il comando finale con obbligo di pagamento. L’obbligo di pagare deve risultare in modo facilmente leggibile e inequivocabile dal comando finale mostrato da Stripe con il prezzo totale.</p>
        <p class="mt-3"><strong>8.2 </strong>Prima del trasferimento a Stripe, WAYOUT verifica il pacchetto disponibile nel catalogo del backend, il requisito 18+, il numero di telefono tramite Firebase, la creazione/riconoscimento dell’account e l’idoneità all’acquisto. Il prezzo visualizzato nel riepilogo deve coincidere con quello restituito dal catalogo e salvato nell’ordine; in caso di incoerenza il checkout deve essere bloccato.</p>
        <p class="mt-3"><strong>8.3 </strong>Se l’Acquirente non completa il pagamento, il contratto non si conclude e l’eventuale disponibilità temporaneamente riservata può essere liberata. WAYOUT non addebita alcun importo per un checkout abbandonato.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">9. Conclusione del contratto</h2>
        <p class="mt-3"><strong>9.1 </strong>Il contratto si conclude quando il pagamento è completato nel checkout Stripe e WAYOUT, tramite il proprio backend, verifica l’entitlement attivo e registra l’Ordine con stato positivo. La data e l’ora di tale registrazione costituiscono la data di conclusione dell’acquisto, salva prova di un diverso momento risultante dai sistemi di pagamento.</p>
        <p class="mt-3"><strong>9.2 </strong>La pagina di successo ha funzione informativa. In caso di incoerenza, prevalgono lo stato effettivo del pagamento, la registrazione dell’Ordine e la conferma inviata da WAYOUT.</p>
        <p class="mt-3"><strong>9.3 </strong>Se un pagamento risulta autorizzato o incassato ma, per acquisti simultanei, errore tecnico, duplicazione o superamento della capienza, l’Ordine non può essere validamente registrato, WAYOUT annulla l’acquisto e rimborsa integralmente l’importo senza costi e senza indebito ritardo, di regola sullo stesso mezzo di pagamento.</p>
        <p class="mt-3"><strong>9.4 </strong>WAYOUT può sospendere la finalizzazione dell’Ordine per il tempo strettamente necessario a svolgere controlli antifrode o di sicurezza. Se il controllo non consente di confermare l’acquisto, l’Ordine viene annullato e le somme dovute sono rimborsate.</p>
        <p class="mt-3"><strong>9.5 </strong>Un errore di prezzo o descrizione manifestamente riconoscibile può comportare la correzione prima della conclusione del contratto. Se l’errore viene rilevato dopo un addebito e rende impossibile l’esecuzione alle condizioni chiaramente errate, WAYOUT informa l’Acquirente e procede al rimborso integrale, ferme le tutele inderogabili.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">10. Pagamento tramite Stripe</h2>
        <p class="mt-3"><strong>10.1 </strong>Il pagamento è gestito tramite Stripe Checkout, la cui sessione viene creata dal backend WAYOUT dopo i controlli preliminari del sito. I metodi effettivamente disponibili sono quelli visualizzati nel checkout e possono includere carte di pagamento o ulteriori strumenti abilitati da Stripe e da WAYOUT.</p>
        <p class="mt-3"><strong>10.2 </strong>WAYOUT non conserva direttamente i dati completi della carta. Stripe e gli eventuali prestatori del metodo scelto trattano i dati di pagamento secondo i propri termini e informative e possono effettuare controlli di autenticazione, sicurezza e prevenzione delle frodi.</p>
        <p class="mt-3"><strong>10.3 </strong>L’Acquirente garantisce di essere autorizzato a utilizzare il metodo di pagamento selezionato. Il rifiuto, la revoca o la mancata autenticazione del pagamento impediscono la conclusione dell’Ordine.</p>
        <p class="mt-3"><strong>10.4 </strong>La valuta dell’acquisto è l’euro. Eventuali costi applicati autonomamente dalla banca, dall’emittente o dal prestatore di pagamento dell’Acquirente non sono addebitati da WAYOUT e restano soggetti al rapporto tra l’Acquirente e tale soggetto.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">11. Conferma dell’ordine su supporto durevole</h2>
        <p class="mt-3"><strong>11.1 </strong>Dopo la conclusione del contratto, WAYOUT invia senza indebito ritardo all’email utilizzata per l’acquisto una conferma dell’Ordine su supporto durevole.</p>
        <p class="mt-3"><strong>11.2 </strong>La conferma contiene o rende disponibile in formato stabile e salvabile almeno: identificativo dell’Ordine, data, piano acquistato, prezzo totale, metodo o stato del pagamento, durata, regola di attivazione individuale a partire dal go-live, assenza di rinnovo automatico, dati del Venditore, informazioni sul recesso e copia immutabile o allegato dei documenti contrattuali accettati con relativa versione/data.</p>
        <p class="mt-3"><strong>11.3 </strong>L’Acquirente deve controllare la conferma e segnalare tempestivamente eventuali incongruenze. La mancata ricezione dell’email non annulla un contratto già concluso, ma l’Acquirente può chiederne il reinvio tramite i canali di assistenza.</p>
        <p class="mt-3"><strong>11.4 </strong>WAYOUT conserva le evidenze tecniche dell’acquisto, comprese data e ora, identificativo dell’Ordine, piano, prezzo, stato del pagamento e versione dei documenti accettati, nel rispetto della <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> e degli obblighi di legge.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">12. Fattura e documentazione fiscale</h2>
        <p class="mt-3"><strong>12.1 </strong>La conferma dell’Ordine non costituisce di per sé fattura. L’Acquirente che desidera ricevere fattura deve selezionare l’apposita opzione prima del pagamento. Per persona fisica sono richiesti i dati anagrafici/fiscali previsti dal form; per persona giuridica possono essere richiesti ragione sociale, Partita IVA, indirizzo, CAP, comune, provincia, Stato, PEC e codice destinatario SDI ove applicabili. I dati devono essere verificati prima del pagamento.</p>
        <p class="mt-3"><strong>12.2 </strong>WAYOUT può richiedere l’integrazione dei dati mancanti indispensabili per la corretta emissione del documento fiscale. L’Acquirente è responsabile dell’esattezza e completezza dei dati comunicati e deve segnalare eventuali errori senza ritardo.</p>
        <p class="mt-3"><strong>12.3 </strong>La fattura elettronica, quando richiesta, è gestita tramite Qonto secondo la normativa fiscale applicabile; gli stati di creazione, trasmissione e gli eventuali errori sono riconciliati nei sistemi WAYOUT. Le richieste tardive o di modifica sono gestite nei limiti consentiti dalla disciplina fiscale e dai sistemi utilizzati.</p>
        <p class="mt-3"><strong>12.4 </strong>In assenza di richiesta di fattura, WAYOUT emette e conserva la documentazione prevista dalla normativa applicabile e invia comunque la conferma contrattuale dell’Ordine.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">13. Esecuzione del contratto e attivazione del Founder Pass</h2>
        <p class="mt-3"><strong>13.1 </strong>Il pagamento perfeziona l’acquisto anticipato del Founder Pass. Il pass può essere attivato solo a partire dal go-live pubblico dell’app nella città pilota di Milano e diventa utilizzabile quando l’Acquirente completa correttamente l’attivazione del proprio account nell’app.</p>
        <p class="mt-3"><strong>13.2 </strong>La durata di 12 mesi decorre dalla corretta attivazione individuale del Founder Pass sull’account, non dalla data di pagamento e non automaticamente dal go-live. L’attivazione non può avvenire prima del go-live: l’Acquirente non perde giorni per il periodo compreso tra il go-live e la propria corretta attivazione.</p>
        <p class="mt-3"><strong>13.3 </strong>L’attivazione richiede il completamento dell’onboarding standard dell’app, inclusi verifica del numero di telefono, maggiore età, dati veritieri, fotografia personale reale e conforme e accettazione delle regole applicabili. Non è previsto un gate discrezionale ulteriore per Founder Creator rispetto a Founder Join.</p>
        <p class="mt-3"><strong>13.4 </strong>Finché l’Acquirente non completa correttamente l’attivazione, i 12 mesi non decorrono. Se l’attivazione è impedita da un problema imputabile a WAYOUT, si applicano assistenza o gli altri rimedi previsti dalle <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a> e dalla normativa sui servizi digitali.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">14. Diritto di recesso</h2>
        <p class="mt-3"><strong>14.1 </strong>Il Consumatore può recedere dall’acquisto senza indicarne il motivo entro 14 giorni dalla conclusione del contratto.</p>
        <p class="mt-3"><strong>14.2 </strong>Il recesso può essere esercitato mediante la funzione online “Recedere dal contratto qui”, disponibile in modo visibile e continuativo durante il periodo di recesso all’indirizzo <a class="font-black text-violet-700" href="https://wayoutapp.it/recedere-dal-contratto">https://wayoutapp.it/recedere-dal-contratto</a>, oppure mediante una dichiarazione esplicita inviata a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> o alla PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>.</p>
        <p class="mt-3"><strong>14.3 </strong>La funzione online richiede almeno il nome dell’Acquirente, le informazioni identificative dell’Ordine e il recapito elettronico per la conferma. L’invio definitivo avviene mediante un comando “Conferma recesso” o equivalente inequivocabile.</p>
        <p class="mt-3"><strong>14.4 </strong>WAYOUT invia senza indebito ritardo una ricevuta su supporto durevole contenente la dichiarazione ricevuta e la relativa data e ora. Il termine è rispettato se il Consumatore trasmette la comunicazione prima della scadenza dei 14 giorni.</p>
        <p class="mt-3"><strong>14.5 </strong>WAYOUT rimborsa integralmente i pagamenti ricevuti senza costi e senza indebito ritardo, comunque entro 14 giorni dal giorno in cui è informata del recesso, utilizzando lo stesso mezzo di pagamento salvo diverso accordo espresso e senza commissioni a carico del Consumatore.</p>
        <p class="mt-3"><strong>14.6 </strong>In coerenza con le <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a>, WAYOUT riconosce il rimborso integrale per il recesso tempestivo anche qualora il go-live sia intervenuto durante il periodo di 14 giorni, senza trattenere un importo proporzionale per il periodo eventualmente decorso, salvo una futura modifica espressamente più favorevole o richiesta dal Consumatore e conforme alla legge.</p>
        <p class="mt-3"><strong>14.7 </strong>Le istruzioni complete e il modello di recesso sono contenuti nella separata <a class="font-black text-violet-700" href="/recedere-dal-contratto">Informativa sul diritto di recesso</a> e nella pagina <a class="font-black text-violet-700" href="/recedere-dal-contratto">Recesso e rimborsi</a>; la funzione online è disponibile all’indirizzo <a class="font-black text-violet-700" href="https://wayoutapp.it/recedere-dal-contratto">https://wayoutapp.it/recedere-dal-contratto</a>.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">15. Ulteriori rimborsi e scenari della Pre-sale</h2>
        <p class="mt-3"><strong>15.1 </strong>Oltre al recesso, i rimborsi sono riconosciuti nei casi e secondo le procedure previste dalle <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a> e dalla <a class="font-black text-violet-700" href="/recedere-dal-contratto">Refund Policy</a>, inclusi mancato lancio, superamento del 31 dicembre 2026 su richiesta dell’Acquirente, modifica sostanziale e peggiorativa rifiutata entro il termine comunicato e impossibilità definitiva di attivazione non imputabile all’Acquirente.</p>
        <p class="mt-3"><strong>15.2 </strong>Se l’app non è resa pubblicamente disponibile a Milano entro il 31 dicembre 2026, l’Acquirente può scegliere se mantenere il Founder Pass per il successivo go-live oppure chiedere il rimborso integrale. In assenza di richiesta, il pass resta valido e potrà essere attivato dal successivo go-live; i 12 mesi decorreranno dalla corretta attivazione individuale.</p>
        <p class="mt-3"><strong>15.3 </strong>In caso di modifica sostanziale e peggiorativa prima del go-live, l’Acquirente può accettare la modifica oppure richiedere il rimborso entro 30 giorni dalla comunicazione. Non è previsto rimborso automatico per il solo decorso del termine.</p>
        <p class="mt-3"><strong>15.4 </strong>Non danno di per sé diritto al rimborso: ritardi entro la data massima, mancato utilizzo volontario, mancata partecipazione o accettazione in tavoli specifici, assenza del risultato sociale desiderato, mancata disponibilità di eventi determinati, crescita della community inferiore alle aspettative o sospensione correttamente disposta per violazioni imputabili all’utente, fatti salvi i diritti inderogabili.</p>
        <p class="mt-3"><strong>15.5 </strong>I rimborsi diversi dal recesso sono processati, di regola, entro 14 giorni dall’accettazione della richiesta o dalla comunicazione che ne determina il diritto e sullo stesso mezzo di pagamento, salvo impossibilità tecnica o diverso accordo.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">16. Conformità e rimedi relativi al servizio digitale</h2>
        <p class="mt-3"><strong>16.1 </strong>Dal go-live, WAYOUT è responsabile della fornitura del servizio digitale in conformità al contratto e alla normativa inderogabile applicabile ai contenuti e servizi digitali.</p>
        <p class="mt-3"><strong>16.2 </strong>In caso di mancata fornitura o difetto di conformità, il Consumatore può chiedere il ripristino della conformità e, quando ne ricorrono i presupposti, la riduzione proporzionale del prezzo o la risoluzione del contratto, oltre agli ulteriori rimedi previsti dalla legge.</p>
        <p class="mt-3"><strong>16.3 </strong>L’Acquirente deve collaborare ragionevolmente alla verifica tecnica del problema, nei limiti di mezzi non invasivi e rispettosi della privacy. La mancata fornitura o il difetto devono essere segnalati tramite i contatti di assistenza con le informazioni necessarie a identificare l’Ordine e il problema.</p>
        <p class="mt-3"><strong>16.4 </strong>Nessuna previsione dei presenti Termini limita o esclude i diritti inderogabili riconosciuti al Consumatore.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">17. Annullamento, sospensione e risoluzione per condotte dell’Acquirente</h2>
        <p class="mt-3"><strong>17.1 </strong>WAYOUT può annullare un Ordine o impedire l’attivazione in caso di minore età, frode, utilizzo di identità o mezzi di pagamento altrui, dati intenzionalmente falsi, duplicazioni abusive o violazioni gravi dei documenti contrattuali, nel rispetto della proporzionalità e delle tutele applicabili.</p>
        <p class="mt-3"><strong>17.2 </strong>Dopo il go-live, sospensione e chiusura dell’account sono disciplinate anche dai Termini dell’app, dalle Community Guidelines e dalle Safety Policy. Una sospensione correttamente imputabile all’utente può comportare la perdita dell’accesso residuo senza rimborso, fatti salvi i diritti inderogabili e la valutazione del caso concreto.</p>
        <p class="mt-3"><strong>17.3 </strong>Se una misura è riconosciuta come erronea dopo revisione, WAYOUT adotta un rimedio proporzionato, quale riattivazione, estensione del pass o rimborso appropriato.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">18. Disponibilità del sito, errori tecnici e forza maggiore</h2>
        <p class="mt-3"><strong>18.1 </strong>WAYOUT adotta misure ragionevoli per mantenere disponibile e sicuro il flusso di acquisto, ma può sospenderlo temporaneamente per manutenzione, aggiornamenti, incidenti, sicurezza, indisponibilità di fornitori o cause di forza maggiore.</p>
        <p class="mt-3"><strong>18.2 </strong>Un malfunzionamento del Sito non comporta l’addebito se il pagamento non è stato autorizzato. In caso di dubbio sull’esito, l’Acquirente deve evitare di ripetere immediatamente il pagamento e verificare l’email o contattare l’assistenza per prevenire duplicazioni.</p>
        <p class="mt-3"><strong>18.3 </strong>WAYOUT non risponde dei ritardi o disservizi esclusivamente imputabili alla banca, a Stripe, alla rete o al dispositivo dell’Acquirente, salvo gli obblighi propri del Venditore e i rimedi inderogabili. Gli importi indebitamente o duplicemente incassati sono rimborsati dopo verifica.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">19. Responsabilità e servizi di terzi</h2>
        <p class="mt-3"><strong>19.1 </strong>Le limitazioni relative alla natura sociale della piattaforma, ai rapporti con utenti e locali, alle interazioni offline e ai servizi esclusi sono contenute nelle <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a> e nei Termini dell’app.</p>
        <p class="mt-3"><strong>19.2 </strong>Stripe e gli eventuali prestatori del metodo di pagamento sono soggetti terzi autonomi per le attività loro proprie. WAYOUT resta responsabile degli obblighi di vendita che la legge pone a suo carico e assiste l’Acquirente nella gestione dell’Ordine e dei rimborsi dovuti.</p>
        <p class="mt-3"><strong>19.3 </strong>Nei limiti consentiti dalla legge, WAYOUT non risponde di danni causati esclusivamente da dati errati forniti dall’Acquirente, uso non autorizzato del suo dispositivo o mezzo di pagamento, condotte di terzi o violazioni dei documenti contrattuali. Restano sempre ferme le responsabilità non escludibili, incluse quelle per dolo o colpa grave.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">20. Trattamento dei dati personali</h2>
        <p class="mt-3"><strong>20.1 </strong>I dati personali sono trattati per gestire accesso alla Pre-sale, Ordine, pagamento, fatturazione, conferma, assistenza, recesso, rimborsi, prevenzione delle frodi, adempimenti fiscali e tutela dei diritti, secondo la <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> del Sito.</p>
        <p class="mt-3"><strong>20.2 </strong>Stripe tratta i dati di pagamento secondo i ruoli e le finalità descritti nella propria documentazione. WAYOUT non utilizza i dati completi della carta per finalità autonome.</p>
        <p class="mt-3"><strong>20.3 </strong>L’eventuale consenso marketing è facoltativo, separato dall’acquisto e revocabile. Il rifiuto o la revoca non impediscono l’acquisto né le comunicazioni strettamente contrattuali.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">21. Assistenza, reclami e comunicazioni</h2>
        <p class="mt-3"><strong>21.1 Per assistenza generale l’Acquirente può scrivere a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> o contattare il numero. Per ordini, recesso, rimborsi e aspetti amministrativi può scrivere a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. Le comunicazioni formali possono essere inviate alla PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>.</strong></p>
        <p class="mt-3"><strong>21.2 </strong>WAYOUT invia le comunicazioni contrattuali all’email associata all’Ordine. L’Acquirente deve mantenerla accessibile, controllare anche la cartella spam e comunicare eventuali cambiamenti.</p>
        <p class="mt-3"><strong>21.3 </strong>I reclami devono descrivere il problema e indicare l’email di acquisto e l’identificativo dell’Ordine. WAYOUT ne conferma la ricezione e li gestisce entro tempi ragionevoli, in funzione della complessità e senza pregiudicare i termini previsti dalla legge.</p>
        <p class="mt-3"><strong>21.4 </strong>Qualora un reclamo non sia risolto, WAYOUT fornirà, quando previsto, le informazioni sugli organismi di risoluzione alternativa delle controversie competenti e indicherà se intende partecipare alla relativa procedura. Resta sempre salvo il diritto del Consumatore di rivolgersi al giudice competente.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">22. Versione, archiviazione e modifica dei Termini</h2>
        <p class="mt-3"><strong>22.1 </strong>La versione applicabile è quella resa disponibile e accettata al momento dell’Ordine. WAYOUT registra la versione o un identificativo univoco e la rende disponibile nella conferma contrattuale in formato conservabile.</p>
        <p class="mt-3"><strong>22.2 </strong>WAYOUT può aggiornare i Termini per ordini futuri pubblicando una nuova versione e la data di aggiornamento. Le modifiche non si applicano retroattivamente agli Ordini già conclusi, salvo disposizioni di legge, misure necessarie alla sicurezza o accordo espresso con l’Acquirente, senza pregiudizio dei diritti maturati.</p>
        <p class="mt-3"><strong>22.3 </strong>Se una clausola è nulla o inefficace, le altre restano valide. La clausola interessata è sostituita nei limiti consentiti dalla disposizione legale applicabile e dalla finalità economica originaria.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">23. Legge applicabile e foro competente</h2>
        <p class="mt-3"><strong>23.1 </strong>I presenti Termini e il contratto sono regolati dalla legge italiana, fatta salva l’applicazione delle norme inderogabili eventualmente più favorevoli previste dalla legge del Paese di residenza abituale del Consumatore.</p>
        <p class="mt-3"><strong>23.2 </strong>Per le controversie con un Consumatore è competente il giudice del luogo di residenza o domicilio del Consumatore, ove previsto dalla normativa applicabile.</p>
        <p class="mt-3"><strong>23.3 </strong>La versione in lingua italiana costituisce il testo di riferimento. Eventuali traduzioni sono fornite per comodità e devono essere interpretate coerentemente con la versione italiana e le norme inderogabili.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">24. Riferimenti e rinvii finali</h2>
        <p class="mt-3"><strong>24.1 </strong>Per le caratteristiche del Founder Pass, la data massima di lancio, le modifiche del servizio, le esclusioni e gli ulteriori rimborsi si rinvia alle <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a>.</p>
        <p class="mt-3"><strong>24.2 </strong>Per il diritto di recesso, la funzione online e il modulo si rinvia all’<a class="font-black text-violet-700" href="/recedere-dal-contratto">Informativa sul diritto di recesso</a> e alla pagina <a class="font-black text-violet-700" href="/recedere-dal-contratto">Recesso e rimborsi</a>. La funzione online è disponibile all’indirizzo <a class="font-black text-violet-700" href="https://wayoutapp.it/recedere-dal-contratto">https://wayoutapp.it/recedere-dal-contratto</a>.</p>
        <p class="mt-3"><strong>24.3 </strong>Per il trattamento dei dati personali e l’uso di cookie o strumenti analoghi si rinvia alla <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a>, alla <a class="font-black text-violet-700" href="/cookie-policy">Cookie Policy</a> e al centro preferenze.</p>
        <p class="mt-3"><strong>24.4 </strong>I presenti Termini sono predisposti in conformità alla normativa italiana applicabile ai contratti a distanza, al commercio elettronico e ai servizi digitali, ferme le successive modifiche legislative inderogabili.</p>
    </section>
</div>
@endsection
