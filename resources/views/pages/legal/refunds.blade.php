@extends('pages.legal.layout', [
    'title' => 'Refund Policy',
    'description' => 'Documento pubblico destinato al sito wayoutapp.it e al flusso di acquisto dei Founder Pass WAYOUT.',
])

@section('legal-content')
<div class="space-y-8">
        <div class="mt-4 space-y-2 rounded-lg border border-slate-200 bg-slate-50 p-4">
            <p class="font-black text-slate-950"><strong>AMBITO</strong></p>
            <p class="text-slate-700">La presente pagina spiega il diritto di recesso, la funzione digitale per esercitarlo e gli ulteriori casi nei quali l’acquirente di un Founder Pass può ottenere un rimborso. Integra i <a class="font-black text-violet-700" href="/termini-di-vendita">Termini di vendita online</a> e le <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a>, che restano applicabili per gli aspetti non disciplinati qui.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><strong>IN BREVE</strong></p>
            <p class="text-slate-700">Il recesso entro 14 giorni non richiede motivazione. Gli ulteriori rimborsi dipendono invece dal caso concreto: mancato lancio, superamento della data massima, modifica sostanziale e peggiorativa, impossibilità definitiva di attivazione, errore di pagamento o rimedi previsti dalla legge per i servizi digitali.</p>
        </div>
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Titolare della vendita e contatti</h2>
        <p class="mt-3"><strong>1.1 </strong>Il venditore dei Founder Pass è WAYOUT S.r.l., con sede legale in Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italia, codice fiscale e Partita IVA 14805930964.</p>
        <p class="mt-3"><strong>1.2 Per richieste relative a ordini, assistenza, recesso e rimborsi è possibile scrivere a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. Le comunicazioni formali possono essere inviate alla PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>.</strong></p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Differenza tra recesso e richiesta di rimborso</h2>
        <p class="mt-3"><strong>2.1 </strong>Il recesso è il diritto del consumatore di sciogliere il contratto entro il periodo previsto dalla legge, senza dover indicare il motivo.</p>
        <p class="mt-3"><strong>2.2 </strong>La richiesta di rimborso riguarda invece gli ulteriori casi previsti dalle <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a>, dai <a class="font-black text-violet-700" href="/termini-di-vendita">Termini di vendita online</a> o dalla normativa applicabile, anche dopo la scadenza del periodo ordinario di recesso.</p>
        <p class="mt-3"><strong>2.3 </strong>L’invio di una richiesta non comporta automaticamente il riconoscimento del rimborso quando questo dipende dalla verifica dei relativi presupposti. WAYOUT conferma la ricezione e comunica l’esito dopo le verifiche necessarie, senza pregiudicare i diritti inderogabili del consumatore.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Quadro sintetico dei casi</h2>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><strong>Caso</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Esito principale</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Termine / modalità</strong></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Recesso ordinario</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Rimborso integrale</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Entro 14 giorni dalla conclusione dell’acquisto; nessuna motivazione</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Ritardo entro il 31 dicembre 2026</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Nessun rimborso automatico</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Il pass mantiene 12 mesi dalla corretta attivazione individuale</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Nessun go-live entro il 31 dicembre 2026</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Scelta tra mantenimento e rimborso integrale</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Richiesta nel termine indicato nella comunicazione, non inferiore a 30 giorni</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Mancato lancio definitivo</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Rimborso integrale</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Disposto da WAYOUT senza necessità di scelta dell’utente</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Modifica sostanziale e peggiorativa prima del go-live</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Scelta tra accettazione e rimborso integrale</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Entro 30 giorni dalla comunicazione</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Impossibilità definitiva di attivazione non imputabile all’utente</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Rimborso integrale</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Dopo assistenza e verifica tecnica</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Pagamento duplicato, overbooking o errore imputabile al sistema</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Rimborso dell’importo non dovuto</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Dopo verifica dell’ordine/pagamento</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Difetto di conformità dopo il go-live</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Ripristino; nei casi di legge riduzione o risoluzione/rimborso</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Secondo la disciplina dei servizi digitali</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Mancato uso volontario o risultato sociale non soddisfacente</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Nessun rimborso di per sé</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Salvi i diritti inderogabili</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Diritto di recesso entro 14 giorni</h2>
        <p class="mt-3"><strong>4.1 </strong>L’acquirente che agisce come consumatore può recedere dall’acquisto del Founder Pass senza indicarne il motivo entro 14 giorni di calendario dalla conclusione del contratto, ossia dalla conferma dell’ordine a seguito del pagamento completato.</p>
        <p class="mt-3"><strong>4.2 </strong>Per rispettare il termine è sufficiente trasmettere la dichiarazione di recesso prima della sua scadenza. Se l’informazione sul diritto di recesso non fosse stata correttamente fornita, si applicano le estensioni del termine previste dalla normativa vigente.</p>
        <p class="mt-3"><strong>4.3 </strong>Poiché il Founder Pass è venduto in pre-sale ed è attivabile soltanto dal go-live, con durata di 12 mesi dalla corretta attivazione individuale, WAYOUT riconosce il rimborso integrale per il recesso tempestivo anche se il go-live o l’attivazione intervengono durante il periodo di 14 giorni, senza trattenere importi proporzionali.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Funzione online “Recedere dal contratto qui”</h2>
        <p class="mt-3"><strong>5.1 </strong>Durante l’intero periodo in cui il diritto di recesso può essere esercitato, il sito mette a disposizione una funzione visibile e facilmente accessibile denominata “Recedere dal contratto qui”, disponibile all’indirizzo <a class="font-black text-violet-700" href="https://wayoutapp.it/recedere-dal-contratto">https://wayoutapp.it/recedere-dal-contratto</a>.</p>
        <p class="mt-3"><strong>5.2 </strong>La funzione consente di fornire o confermare facilmente almeno: nome e cognome; email utilizzata per l’acquisto; informazioni utili a identificare il contratto o l’ordine; recapito elettronico al quale inviare la conferma.</p>
        <p class="mt-3"><strong>5.3 </strong>Dopo la compilazione, l’utente presenta definitivamente la dichiarazione mediante il comando “Conferma recesso” o altra formula altrettanto inequivocabile.</p>
        <p class="mt-3"><strong>5.4 </strong>WAYOUT invia senza indebito ritardo un avviso di ricevimento su supporto durevole, contenente la dichiarazione trasmessa e la data e l’ora di invio. La ricevuta prova la corretta acquisizione della richiesta, senza limitare gli altri mezzi con cui il consumatore può dimostrare di aver esercitato il diritto nei termini.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Canali alternativi per esercitare il recesso</h2>
        <p class="mt-3"><strong>6.1 </strong>Il consumatore può esercitare il recesso anche inviando una dichiarazione esplicita a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> o alla PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>. Può utilizzare il modello riportato nell’Allegato A, ma non è obbligato a farlo.</p>
        <p class="mt-3"><strong>6.2 </strong>La comunicazione deve permettere di identificare il consumatore e l’acquisto. È consigliato indicare: nome e cognome, email di acquisto, numero ordine, data dell’acquisto e Founder Pass acquistato.</p>
        <p class="mt-3"><strong>6.3 </strong>Il motivo del recesso è facoltativo e non incide sulla sua validità.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">7. Effetti del recesso e modalità del rimborso</h2>
        <p class="mt-3"><strong>7.1 </strong>Il recesso scioglie il contratto relativo al Founder Pass. Se il pass è già stato associato a un account, WAYOUT può disattivarlo in conseguenza del rimborso.</p>
        <p class="mt-3"><strong>7.2 </strong>WAYOUT rimborsa tutti i pagamenti ricevuti per l’acquisto, senza indebito ritardo e comunque entro 14 giorni dal giorno in cui è informata della decisione di recedere.</p>
        <p class="mt-3"><strong>7.3 </strong>Il rimborso è eseguito utilizzando lo stesso mezzo di pagamento impiegato per la transazione iniziale, salvo diverso accordo espresso con il consumatore e a condizione che quest’ultimo non sostenga costi. WAYOUT non applica commissioni per il rimborso.</p>
        <p class="mt-3"><strong>7.4 </strong>Se il rimborso sul mezzo originario è tecnicamente impossibile, WAYOUT contatta l’acquirente per concordare un mezzo alternativo sicuro, dopo le verifiche necessarie. I tempi con cui la banca o il circuito rende visibile l’accredito possono dipendere da soggetti terzi e non modificano il termine entro il quale WAYOUT deve disporre il rimborso.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">8. Rimborso se il go-live non avviene entro il 31 dicembre 2026</h2>
        <p class="mt-3"><strong>8.1 </strong>Se l’app non viene resa pubblicamente disponibile nella città pilota di Milano entro il 31 dicembre 2026, WAYOUT informa gli acquirenti su supporto durevole.</p>
        <p class="mt-3"><strong>8.2 </strong>L’acquirente può scegliere se mantenere il Founder Pass per il successivo go-live oppure richiedere il rimborso integrale. La comunicazione indica una finestra per esercitare la scelta, in ogni caso non inferiore a 30 giorni dalla ricezione.</p>
        <p class="mt-3"><strong>8.3 </strong>In assenza di richiesta di rimborso entro la finestra comunicata, il Founder Pass resta valido e potrà essere attivato dal successivo go-live. La durata di 12 mesi decorrerà dalla corretta attivazione individuale, senza rinnovo automatico. Restano fermi i diritti inderogabili eventualmente applicabili.</p>
        <p class="mt-3"><strong>8.4 </strong>Il semplice rinvio del lancio entro il 31 dicembre 2026 non dà diritto di per sé a rimborso, salvo il recesso tempestivo, una diversa decisione commerciale di WAYOUT o altri rimedi previsti dalla legge.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">9. Mancato lancio definitivo</h2>
        <p class="mt-3"><strong>9.1 </strong>Se WAYOUT decide definitivamente di non lanciare l’app o di non rendere disponibile il servizio oggetto della pre-sale, il contratto relativo al Founder Pass è risolto e l’acquirente ha diritto al rimborso integrale.</p>
        <p class="mt-3"><strong>9.2 </strong>In questo caso WAYOUT comunica la decisione e dispone il rimborso senza necessità di una preventiva richiesta dell’acquirente, di regola sullo stesso mezzo di pagamento, senza commissioni e entro 14 giorni dalla decisione o dalla comunicazione.</p>
        <p class="mt-3"><strong>9.3 </strong>Il rimborso estingue il diritto all’attivazione del Founder Pass, fatti salvi gli ulteriori diritti inderogabili riconosciuti dalla legge.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">10. Modifica sostanziale e peggiorativa prima del go-live</h2>
        <p class="mt-3"><strong>10.1 </strong>Non ogni aggiornamento, modifica tecnica, grafica, organizzativa o di sicurezza dà diritto al rimborso. Il rimedio si applica quando, prima del go-live, una modifica elimina o riduce in modo sostanziale e peggiorativo una caratteristica essenziale del piano acquistato.</p>
        <p class="mt-3"><strong>10.2 </strong>WAYOUT informa l’acquirente su supporto durevole, descrive la modifica e consente di scegliere tra accettare il servizio modificato oppure chiedere il rimborso integrale entro 30 giorni dalla comunicazione.</p>
        <p class="mt-3"><strong>10.3 </strong>In assenza di richiesta di rimborso entro tale termine, il Founder Pass resta valido e viene attivato alle condizioni comunicate. Ciò non comporta rinuncia ai diritti inderogabili del consumatore.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">11. Impossibilità di attivazione e problemi tecnici</h2>
        <p class="mt-3"><strong>11.1 </strong>Se l’attivazione è impedita da ritardi, errori o malfunzionamenti imputabili a WAYOUT, la Società fornisce assistenza e adotta un rimedio adeguato, come ripristino, riattivazione, estensione del periodo o altra misura equivalente.</p>
        <p class="mt-3"><strong>11.2 </strong>Se l’attivazione resta definitivamente impossibile per cause non imputabili all’acquirente, WAYOUT rimborsa integralmente il prezzo pagato, fatti salvi gli ulteriori rimedi previsti dalla disciplina dei servizi digitali.</p>
        <p class="mt-3"><strong>11.3 </strong>Se il completamento dell’onboarding avviene in un momento successivo al go-live per scelta dell’acquirente, la durata del Founder Pass non è ridotta: i 12 mesi decorrono dalla corretta attivazione individuale. Il semplice ritardo volontario nell’attivazione non genera di per sé un diritto ulteriore al rimborso.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">12. Errori di ordine o pagamento</h2>
        <p class="mt-3"><strong>12.1 </strong>In caso di pagamento duplicato, importo indebitamente addebitato, ordine eccedente la disponibilità, overbooking, errore tecnico o mancata validazione definitiva dell’ordine dopo l’addebito, WAYOUT annulla la parte non dovuta e rimborsa integralmente il relativo importo senza costi.</p>
        <p class="mt-3"><strong>12.2 </strong>WAYOUT può chiedere le informazioni strettamente necessarie per riconciliare ordine e pagamento, inclusi numero ordine, email di acquisto, data, importo e prova dell’addebito. Non è necessario trasmettere i dati completi della carta.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">13. Difetti di conformità e rimedi dopo il go-live</h2>
        <p class="mt-3"><strong>13.1 </strong>Dal go-live il servizio digitale è soggetto alla garanzia legale di conformità prevista dal Codice del Consumo. In caso di mancata fornitura o difetto di conformità, il consumatore può chiedere in via prioritaria il ripristino della conformità senza spese e senza notevoli inconvenienti, entro un termine congruo.</p>
        <p class="mt-3"><strong>13.2 </strong>Quando ricorrono i presupposti di legge, il consumatore può ottenere una riduzione proporzionale del prezzo o la risoluzione del contratto. In caso di risoluzione di un servizio fornito per un periodo, il rimborso può riguardare il periodo di non conformità e la parte del prezzo anticipata per il periodo residuo non fruito.</p>
        <p class="mt-3"><strong>13.3 </strong>I rimborsi dovuti per riduzione del prezzo o risoluzione sono effettuati senza ritardo ingiustificato e comunque entro 14 giorni da quando WAYOUT è informata della decisione del consumatore, utilizzando lo stesso mezzo di pagamento salvo diverso accordo senza costi.</p>
        <p class="mt-3"><strong>13.4 </strong>Le modifiche del servizio digitale dopo il go-live sono soggette anche alle regole inderogabili che consentono al consumatore, nei casi previsti, di recedere gratuitamente entro 30 giorni quando la modifica incide negativamente e in modo non trascurabile sull’uso o sull’accesso al servizio.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">14. Sospensione, ban e revisione della misura</h2>
        <p class="mt-3"><strong>14.1 </strong>La sospensione o chiusura dell’account correttamente disposta per violazioni gravi o reiterate dei Termini dell’app, delle Community Guidelines, delle Safety Policy o della legge può comportare la perdita dell’accesso residuo senza rimborso, nei limiti consentiti dalla legge e in base a una valutazione proporzionata del caso concreto.</p>
        <p class="mt-3"><strong>14.2 </strong>L’utente può contestare la misura attraverso i canali indicati nella comunicazione ricevuta. Se la sospensione o il ban risultano erronei, WAYOUT adotta un rimedio adeguato, quale riattivazione, estensione del pass o rimborso proporzionato; se il servizio non può essere ripristinato, si applicano i rimedi inderogabili pertinenti.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">15. Casi che non danno normalmente diritto al rimborso</h2>
        <p class="mt-3"><strong>15.1 </strong>Fatti salvi il recesso, la garanzia legale e gli altri diritti inderogabili, non danno di per sé diritto al rimborso:</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>mancato utilizzo volontario del Founder Pass o registrazione tardiva imputabile all’utente;</li>
            <li>mancata partecipazione, mancata accettazione o cancellazione di uno specifico tavolo;</li>
            <li>mancanza del risultato sociale desiderato, incompatibilità o insoddisfazione soggettiva;</li>
            <li>numero di utenti, tavoli, creator o occasioni sociali inferiore alle aspettative;</li>
            <li>assenza di uno specifico locale, evento, serata, ingresso, tavolo fisico, drink o servizio di terzi;</li>
            <li>decisioni o condizioni applicate autonomamente da locali, creator, utenti o soggetti terzi;</li>
            <li>ritardo del go-live che rimane entro il 31 dicembre 2026;</li>
            <li>sospensione o ban legittimamente imputabili a violazioni dell’utente.</li>
        </ul>
        <p class="mt-3"><strong>15.2 </strong>Il Founder Pass è una subscription digitale a tempo e non un credito a consumo, un titolo d’ingresso, una prenotazione presso locali o una garanzia di disponibilità di eventi o risultati sociali.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">16. Come presentare una richiesta di rimborso diversa dal recesso</h2>
        <p class="mt-3"><strong>16.1 </strong>La richiesta può essere inviata tramite la funzione online “Recedere dal contratto qui” disponibile all’indirizzo <a class="font-black text-violet-700" href="https://wayoutapp.it/recedere-dal-contratto">https://wayoutapp.it/recedere-dal-contratto</a>, oppure a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. Deve indicare, per quanto disponibile: nome, cognome, email di acquisto, numero ordine, data di acquisto, Founder Pass acquistato e categoria della richiesta.</p>
        <p class="mt-3"><strong>16.2 </strong>Per il recesso ordinario non è richiesta alcuna motivazione. Per gli altri casi, una breve descrizione e gli eventuali documenti utili sono facoltativi, ma possono accelerare la verifica. WAYOUT non richiede dati eccedenti o i dati completi della carta di pagamento.</p>
        <p class="mt-3"><strong>16.3 </strong>WAYOUT conferma la ricezione e può chiedere integrazioni strettamente necessarie. I rimborsi diversi dal recesso sono processati, di regola, entro 14 giorni dall’accettazione della richiesta o dalla comunicazione che determina il diritto al rimborso, salvo termini inderogabili più favorevoli.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">17. Protezione dei dati</h2>
        <p class="mt-3"><strong>17.1 </strong>I dati forniti per gestire recesso, richieste di rimborso, verifiche, pagamenti e contestazioni sono trattati da WAYOUT secondo la <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> del sito, per eseguire il contratto, adempiere agli obblighi di legge, prevenire frodi e tutelare i diritti delle parti.</p>
        <p class="mt-3"><strong>17.2 </strong>La gestione del rimborso può richiedere il coinvolgimento di Stripe, istituti di pagamento, banche, fornitori tecnici e professionisti incaricati, nei limiti necessari alle rispettive attività.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">18. Coordinamento con gli altri documenti e diritti inderogabili</h2>
        <p class="mt-3"><strong>18.1 </strong>La presente <a class="font-black text-violet-700" href="/recedere-dal-contratto">Refund Policy</a> integra i <a class="font-black text-violet-700" href="/termini-di-vendita">Termini di vendita online</a> e le <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a>. In caso di contrasto, prevalgono le disposizioni inderogabili applicabili e, tra i documenti contrattuali, la disciplina più specifica per il caso concreto, interpretata in modo coerente con i diritti del consumatore.</p>
        <p class="mt-3"><strong>18.2 </strong>Nessuna previsione della presente policy limita il diritto di recesso, la garanzia legale di conformità, i rimedi per mancata fornitura o gli altri diritti che la legge riconosce inderogabilmente al consumatore.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">Allegato A - Modulo tipo di recesso</h2>
        <div class="mt-4 space-y-3 rounded-[1.25rem] border-2 border-amber-300 bg-white p-5">
            <p>Compilare e inviare il presente modulo solo se si desidera recedere dal contratto. È possibile utilizzare anche la funzione online o una diversa dichiarazione esplicita.</p>
            <p><strong>Destinatario:</strong> WAYOUT S.r.l., Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italia - <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> - PEC <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a></p>
            <p>Con la presente comunico il recesso dal contratto relativo all’acquisto del seguente Founder Pass WAYOUT:</p>
            <div class="grid gap-4 pt-2">
                <p><span class="font-bold">Founder Pass:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Numero ordine (se disponibile):</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Data dell’acquisto:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Email utilizzata per l’acquisto:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Nome e cognome:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Indirizzo (facoltativo):</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <p><span class="font-bold">Data:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                    <p><span class="font-bold">Firma (solo per modulo cartaceo):</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                </div>
            </div>
            <a class="inline-flex rounded-xl bg-slate-950 px-5 py-3 font-black text-white hover:bg-violet-800" href="/documenti/modulo-tipo-recesso?lang=it">Scarica il modulo (.docx)</a>
        </div>
    </section>
</div>
@endsection
