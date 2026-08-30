@extends('pages.legal.layout', [
    'title' => 'Informativa sul diritto di recesso',
    'description' => 'Documento pubblico da rendere disponibile prima dell’acquisto, nel checkout, nella conferma d’ordine e nella pagina Recesso e rimborsi del sito wayoutapp.it.',
])

@section('legal-content')
<div class="space-y-8">
        <div class="mt-4 space-y-2 rounded-lg border border-slate-200 bg-slate-50 p-4">
            <p class="font-black text-slate-950"><strong>FINALITÀ</strong></p>
            <p class="text-slate-700">Questa informativa spiega in modo autonomo e completo come il consumatore può recedere dall’acquisto online di un Founder Pass WAYOUT entro 14 giorni, quali effetti produce il recesso e come viene eseguito il rimborso.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><strong>IN BREVE</strong></p>
            <p class="text-slate-700">Puoi annullare l’acquisto del Founder Pass senza indicare il motivo entro 14 giorni di calendario dalla conclusione del contratto. Per rispettare il termine è sufficiente inviare la dichiarazione prima della scadenza. WAYOUT rimborserà integralmente il prezzo, senza commissioni.</p>
        </div>
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Venditore e ambito di applicazione</h2>
        <p class="mt-3"><strong>1.1 </strong>Il venditore è WAYOUT S.r.l., con sede legale in Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italia, codice fiscale e Partita IVA 14805930964.</p>
        <p class="mt-3"><strong>1.2 </strong>La presente informativa si applica all’acquisto online dei Founder Pass 12M effettuato da una persona fisica che agisce per scopi estranei alla propria attività imprenditoriale, commerciale, artigianale o professionale.</p>
        <p class="mt-3"><strong>1.3 Per ordini, assistenza, recesso e rimborsi: <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>. PEC: <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>.</strong></p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Diritto di recesso e durata del termine</h2>
        <p class="mt-3"><strong>2.1 </strong>Il consumatore ha diritto di recedere dal contratto senza indicarne il motivo e senza penalità entro 14 giorni di calendario.</p>
        <p class="mt-3"><strong>2.2 </strong>Il termine decorre dal giorno successivo alla conclusione del contratto, che coincide con la conferma dell’ordine successiva al pagamento completato e alla registrazione positiva dell’acquisto nei sistemi WAYOUT. La data di conclusione dell’acquisto è indicata nella conferma d’ordine.</p>
        <p class="mt-3"><strong>2.3 </strong>Per rispettare il termine è sufficiente trasmettere la dichiarazione di recesso prima della sua scadenza. Il motivo del recesso non è richiesto.</p>
        <p class="mt-3"><strong>2.4 </strong>Se le informazioni sul diritto di recesso non fossero fornite correttamente, si applicano le estensioni del termine previste dalla normativa vigente. La presente previsione non limita eventuali tutele più favorevoli riconosciute al consumatore dalla legge applicabile.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Come esercitare il recesso</h2>
        <p class="mt-3"><strong>3.1 </strong>Il consumatore può scegliere liberamente uno dei canali indicati di seguito. L’uso del modulo tipo non è obbligatorio: è sufficiente una dichiarazione esplicita dalla quale risulti chiaramente la decisione di recedere.</p>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><strong>Canale</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Come utilizzarlo</strong></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Funzione online</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Utilizzare “Recedere dal contratto qui” nella pagina <a class="font-black text-violet-700" href="https://wayoutapp.it/recedere-dal-contratto">https://wayoutapp.it/recedere-dal-contratto</a>, compilare i dati richiesti e inviare definitivamente la dichiarazione con “Conferma recesso”.</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Email</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Inviare una dichiarazione esplicita a <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a>.</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>PEC</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Inviare una dichiarazione esplicita a <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a>.</strong></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><strong>Modulo tipo</strong></td>
                        <td class="px-4 py-3 align-top"><strong>Compilare il modello contenuto nell’Allegato A e inviarlo tramite email o PEC; il suo utilizzo è facoltativo.</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3"><strong>3.2 </strong>La comunicazione deve consentire di identificare il consumatore e il contratto. È quindi opportuno indicare almeno nome e cognome, email utilizzata per l’acquisto e numero d’ordine; se il numero d’ordine non è disponibile, possono essere indicati data dell’acquisto e Founder Pass acquistato.</p>
        <p class="mt-3"><strong>3.3 </strong>Il consumatore può indicare un recapito elettronico diverso dall’email di acquisto per ricevere la conferma, purché WAYOUT possa effettuare le verifiche ragionevolmente necessarie per prevenire abusi o accessi non autorizzati.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Funzione online di recesso</h2>
        <p class="mt-3"><strong>4.1 </strong>Durante l’intero periodo nel quale il diritto può essere esercitato, il sito rende disponibile in modo visibile e facilmente accessibile la funzione “Recedere dal contratto qui” o una formula equivalente e inequivocabile.</p>
        <p class="mt-3"><strong>4.2 </strong>La funzione consente di fornire o confermare il nome del consumatore, le informazioni che identificano il contratto e il recapito elettronico al quale inviare la conferma.</p>
        <p class="mt-3"><strong>4.3 </strong>Dopo la compilazione, la dichiarazione viene presentata mediante il comando “Conferma recesso” o altra formula equivalente e inequivocabile.</p>
        <p class="mt-3"><strong>4.4 </strong>WAYOUT invia senza indebito ritardo una ricevuta su supporto durevole, normalmente via email, contenente il testo della dichiarazione e la data e l’ora della sua trasmissione. La ricevuta deve essere conservata dal consumatore.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Effetti del recesso e rimborso</h2>
        <p class="mt-3"><strong>5.1 </strong>Il recesso scioglie il contratto relativo al Founder Pass. Il pass non verrà attivato oppure, se già associato a un account, potrà essere disattivato a seguito del rimborso.</p>
        <p class="mt-3"><strong>5.2 </strong>WAYOUT rimborserà tutti i pagamenti ricevuti per l’acquisto senza indebito ritardo e comunque entro 14 giorni dal giorno in cui è informata della decisione di recedere.</p>
        <p class="mt-3"><strong>5.3 </strong>Il rimborso sarà eseguito utilizzando lo stesso mezzo di pagamento usato per l’acquisto, salvo diverso accordo espresso con il consumatore e a condizione che quest’ultimo non sostenga alcun costo. WAYOUT non applica commissioni di rimborso.</p>
        <p class="mt-3"><strong>5.4 </strong>Se il rimborso sul mezzo originario è tecnicamente impossibile, WAYOUT contatterà il consumatore per concordare un mezzo alternativo sicuro. I tempi con cui la banca o il circuito rendono visibile l’accredito possono dipendere da soggetti terzi.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Regola più favorevole applicata da WAYOUT</h2>
        <p class="mt-3"><strong>6.1 </strong>Il Founder Pass è acquistato in pre-sale, è attivabile a partire dal go-live pubblico dell’app e la durata di 12 mesi decorre dalla corretta attivazione individuale dell’account.</p>
        <p class="mt-3"><strong>6.2 </strong>WAYOUT riconosce il rimborso integrale in caso di recesso tempestivo anche se il go-live o l’attivazione del pass intervengono durante il periodo di 14 giorni. Nel flusso di pre-sale non è richiesta una rinuncia al diritto di recesso e non viene trattenuto un importo proporzionale per l’eventuale periodo di utilizzo intervenuto prima del recesso.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">7. Recesso e altri casi di rimborso</h2>
        <p class="mt-3"><strong>7.1 </strong>Il diritto di recesso entro 14 giorni è distinto dagli ulteriori rimborsi previsti per il superamento della data massima di lancio, il mancato lancio definitivo, una modifica sostanziale e peggiorativa, l’impossibilità definitiva di attivazione, gli errori di pagamento o i rimedi relativi alla conformità del servizio digitale.</p>
        <p class="mt-3"><strong>7.2 </strong>Tali casi sono disciplinati nella <a class="font-black text-violet-700" href="/recedere-dal-contratto">Refund Policy</a> / pagina <a class="font-black text-violet-700" href="/recedere-dal-contratto">Recesso e rimborsi</a>, nelle <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a> e nei <a class="font-black text-violet-700" href="/termini-di-vendita">Termini di vendita online</a>.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">8. Trattamento dei dati</h2>
        <p class="mt-3"><strong>8.1 </strong>I dati comunicati per esercitare il recesso sono trattati per identificare l’ordine, gestire la dichiarazione, inviare la ricevuta, disporre il rimborso, adempiere agli obblighi di legge e tutelare i diritti delle parti, secondo la <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> del sito.</p>
        <p class="mt-3"><strong>8.2 </strong>Non devono essere trasmessi i dati completi della carta di pagamento. Per la riconciliazione sono sufficienti i dati dell’ordine e, se richiesto, una prova dell’addebito priva delle informazioni non necessarie.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">9. Riferimenti contrattuali e normativi</h2>
        <p class="mt-3"><strong>9.1 </strong>La presente informativa integra i <a class="font-black text-violet-700" href="/termini-di-vendita">Termini di vendita online</a> e le <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a>. In caso di contrasto, prevalgono le disposizioni inderogabili di legge e, tra i documenti WAYOUT, la previsione più favorevole al consumatore per lo specifico diritto disciplinato.</p>
        <p class="mt-3"><strong>9.2 </strong>Riferimenti essenziali: articoli 49 e da 52 a 57, incluso l’articolo 54-bis, e Allegato I del decreto legislativo 6 settembre 2005, n. 206 (Codice del Consumo), nel testo vigente.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">Allegato A - Modulo tipo di recesso</h2>
        <div class="mt-4 space-y-3 rounded-[1.25rem] border-2 border-violet-200 bg-violet-50 p-5">
            <p>Compilare e restituire il presente modulo solo se si desidera recedere dal contratto. È possibile utilizzare anche qualsiasi altra dichiarazione esplicita.</p>
            <p class="font-black text-slate-950">DESTINATARIO</p>
            <p>WAYOUT S.r.l. - Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italia<br>Email: <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a> - PEC: <a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a></p>
            <p>Con la presente comunico il recesso dal contratto di acquisto del seguente Founder Pass WAYOUT:<br>☐ Founder Join 12M<br>☐ Founder Creator 12M<br>☐ Altro / da specificare</p>
            <div class="grid gap-4 pt-2">
                <p><span class="font-bold">Numero ordine:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Data dell’acquisto:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Nome e cognome del consumatore:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Email utilizzata per l’acquisto:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Email alla quale inviare la conferma:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <p><span class="font-bold">Indirizzo del consumatore (facoltativo per l’invio elettronico):</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <p><span class="font-bold">Data:</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                    <p><span class="font-bold">Firma (solo per modulo cartaceo):</span><span class="mt-2 block min-h-7 border-b border-dashed border-slate-500"></span></p>
                </div>
            </div>
            <p class="font-black text-slate-950">INVIO ONLINE</p>
            <p>In alternativa, il recesso può essere esercitato tramite la funzione “Recedere dal contratto qui” disponibile su <a class="font-black text-violet-700" href="https://wayoutapp.it/recedere-dal-contratto">https://wayoutapp.it/recedere-dal-contratto</a>. Dopo l’invio, WAYOUT trasmette senza indebito ritardo una ricevuta su supporto durevole con il contenuto, la data e l’ora della dichiarazione.</p>
            <a class="inline-flex rounded-xl bg-violet-700 px-5 py-3 font-black text-white hover:bg-violet-800" href="/documenti/modulo-tipo-recesso?lang=it">Scarica il modulo (.docx)</a>
        </div>
    </section>
</div>
@endsection
