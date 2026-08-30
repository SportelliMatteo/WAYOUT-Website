@extends('pages.legal.layout', [
    'title' => 'Come funzionano i Pass',
    'description' => 'Waitlist Pass, Founder Join 12M e Founder Creator 12M: cosa includono, quando si attivano e quali condizioni si applicano.',
])

@section('legal-content')
<div class="space-y-8">
    <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
        <p class="font-black text-slate-950"><strong>In breve.</strong></p>
        <p class="text-slate-700">L’iscrizione alla waitlist è gratuita e non comporta obblighi di acquisto. Il Waitlist Pass è un possibile beneficio gratuito di 60 giorni con sole funzioni Join. I Founder Pass sono invece subscription digitali acquistate in pre-sale, valide per 12 mesi dalla corretta attivazione individuale, possibile dal go-live pubblico dell’app e senza rinnovo automatico.</p>
    </div>
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. I tre Pass a confronto</h2>
        <div class="mt-4 grid overflow-hidden rounded-lg border border-slate-200 bg-white md:grid-cols-[0.38fr_0.62fr]">
            <div class="space-y-2 bg-slate-950 p-5 text-white">
                <p><strong>WAITLIST PASS</strong></p>
                <p><strong>Gratis</strong></p>
                <p>60 giorni</p>
            </div>
            <div class="space-y-2 p-5 text-slate-700">
                <p>Possibile beneficio per i primi 2.000 utenti validi della waitlist.</p>
                <p><em><strong>Include: </strong></em>Funzionalità Join · Richieste di partecipazione · Chat dopo l’accettazione</p>
                <p><em><strong>Non include: </strong></em>Creazione e gestione dei tavoli · Funzionalità Creator</p>
            </div>
        </div>
        <div class="mt-4 grid overflow-hidden rounded-lg border border-slate-200 bg-white md:grid-cols-[0.38fr_0.62fr]">
            <div class="space-y-2 bg-slate-950 p-5 text-white">
                <p><strong>FOUNDER JOIN 12M</strong></p>
                <p><strong>€29 IVA inclusa</strong></p>
                <p>12 mesi dalla corretta attivazione individuale, possibile dal go-live</p>
            </div>
            <div class="space-y-2 p-5 text-slate-700">
                <p>Subscription pre-lancio per usare le funzionalità Join.</p>
                <p><em><strong>Include: </strong></em>Scoperta di tavoli e occasioni sociali · Richieste di partecipazione · Chat dei tavoli accettati</p>
                <p><em><strong>Non include: </strong></em>Creazione e gestione dei tavoli · Funzionalità Creator</p>
            </div>
        </div>
        <div class="mt-4 grid overflow-hidden rounded-lg border border-slate-200 bg-white md:grid-cols-[0.38fr_0.62fr]">
            <div class="space-y-2 bg-slate-950 p-5 text-white">
                <p><strong>FOUNDER CREATOR 12M</strong></p>
                <p><strong>€59 IVA inclusa</strong></p>
                <p>12 mesi dalla corretta attivazione individuale, possibile dal go-live</p>
            </div>
            <div class="space-y-2 p-5 text-slate-700">
                <p>Subscription pre-lancio con funzioni Join e Creator.</p>
                <p><em><strong>Include: </strong></em>Tutte le funzionalità Join · Creazione e gestione di tavoli digitali · Gestione delle richieste di partecipazione</p>
                <p><em><strong>Non include: </strong></em>Diritto illimitato a creare tavoli · Servizi, prenotazioni o ingressi dei locali</p>
            </div>
        </div>
        <h3 class="mt-6 text-xl font-black text-slate-900">Confronto essenziale</h3>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-[900px] divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 font-black text-slate-700">
                    <tr>
                        <th class="px-4 py-3 align-top"><strong>Caratteristica</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Waitlist Pass</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Founder Join 12M</strong></th>
                        <th class="px-4 py-3 align-top"><strong>Founder Creator 12M</strong></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Prezzo</strong></em></td>
                        <td class="px-4 py-3 align-top">Gratis</td>
                        <td class="px-4 py-3 align-top">€29</td>
                        <td class="px-4 py-3 align-top">€59</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Prezzo IVA inclusa</strong></em></td>
                        <td class="px-4 py-3 align-top">N/A</td>
                        <td class="px-4 py-3 align-top">Sì</td>
                        <td class="px-4 py-3 align-top">Sì</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Durata</strong></em></td>
                        <td class="px-4 py-3 align-top">60 giorni dall’attivazione individuale</td>
                        <td class="px-4 py-3 align-top">12 mesi dalla corretta attivazione individuale, possibile dal go-live</td>
                        <td class="px-4 py-3 align-top">12 mesi dalla corretta attivazione individuale, possibile dal go-live</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Funzioni Join</strong></em></td>
                        <td class="px-4 py-3 align-top">Sì</td>
                        <td class="px-4 py-3 align-top">Sì</td>
                        <td class="px-4 py-3 align-top">Sì</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Funzioni Creator</strong></em></td>
                        <td class="px-4 py-3 align-top">No</td>
                        <td class="px-4 py-3 align-top">No</td>
                        <td class="px-4 py-3 align-top">Sì</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Rinnovo automatico</strong></em></td>
                        <td class="px-4 py-3 align-top">No</td>
                        <td class="px-4 py-3 align-top">No</td>
                        <td class="px-4 py-3 align-top">No</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Posti / disponibilità</strong></em></td>
                        <td class="px-4 py-3 align-top">Primi 2.000 utenti validi</td>
                        <td class="px-4 py-3 align-top">Massimo 600 pass</td>
                        <td class="px-4 py-3 align-top">Massimo 200 pass</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Accesso anticipato all’app</strong></em></td>
                        <td class="px-4 py-3 align-top">No</td>
                        <td class="px-4 py-3 align-top">No</td>
                        <td class="px-4 py-3 align-top">No</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 align-top font-black text-slate-900"><em><strong>Cumulabile con altri Pass</strong></em></td>
                        <td class="px-4 py-3 align-top">No, salvo comunicazione espressa</td>
                        <td class="px-4 py-3 align-top">No</td>
                        <td class="px-4 py-3 align-top">No</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3">La disponibilità dei Founder Pass si consolida soltanto con il pagamento completato e la conferma dell’ordine. La pre-sale può chiudersi prima dei cap quantitativi nei casi previsti dalle condizioni applicabili.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Waitlist e Waitlist Pass gratuito</h2>
        <p class="mt-3">Entrare nella waitlist significa manifestare gratuitamente il proprio interesse per il lancio di WAYOUT. L’iscrizione non costituisce un acquisto, non comporta addebiti e non obbliga ad acquistare un Founder Pass.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">Chi può ricevere il Waitlist Pass</h3>
        <p class="mt-3">Il Waitlist Pass è un possibile beneficio promozionale riservato ai primi 2.000 utenti validi della waitlist. L’iscrizione, da sola, non garantisce automaticamente il beneficio.</p>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>L’utente deve avere almeno 18 anni.</li>
            <li>Il profilo deve essere completato con dati veritieri e utilizzabili.</li>
            <li>Non devono risultare iscrizioni duplicate, abusive, fraudolente o automatizzate.</li>
            <li>Devono essere completati gli adempimenti e le verifiche comunicati da WAYOUT.</li>
        </ul>
        <h3 class="mt-6 text-xl font-black text-slate-900">Durata e attivazione</h3>
        <p class="mt-3"><em><strong>Durata: </strong></em>60 giorni dalla data in cui il singolo utente completa correttamente la registrazione nell’app e attiva il beneficio. La durata non decorre automaticamente dal go-live generale.</p>
        <p class="mt-3"><em><strong>Termine di attivazione: </strong></em>l’attivazione deve essere completata entro 30 giorni di calendario dall’email con cui WAYOUT comunica che il beneficio è disponibile, salvo un termine più lungo indicato nella stessa comunicazione.</p>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><em><strong>Attenzione. </strong></em></p>
            <p class="text-slate-700">Se l’attivazione non viene completata entro il termine comunicato, il beneficio si considera rinunciato e WAYOUT può assegnare il relativo posto a un altro utente valido. Eventuali problemi tecnici imputabili a WAYOUT saranno gestiti con modalità e termini adeguati.</p>
        </div>
        <h3 class="mt-6 text-xl font-black text-slate-900">Cosa include</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>Le funzionalità Join indicate nell’app.</li>
            <li>La possibilità di scoprire tavoli o occasioni sociali disponibili.</li>
            <li>La possibilità di inviare richieste di partecipazione secondo le regole e i limiti del servizio.</li>
            <li>L’accesso alle chat dei tavoli per i quali la richiesta è stata accettata.</li>
        </ul>
        <h3 class="mt-6 text-xl font-black text-slate-900">Cosa non include</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>La creazione o la gestione di tavoli.</li>
            <li>Funzionalità Creator.</li>
            <li>Ingresso nei locali, prenotazioni, tavoli fisici, drink, consumazioni o servizi di terzi.</li>
            <li>La garanzia di essere accettati in un tavolo o di trovare tavoli in una data o località specifica.</li>
        </ul>
        <p class="mt-3"><em><strong>Caratteristiche personali. </strong></em>Il Waitlist Pass è personale, non cedibile, non trasferibile, non rivendibile e privo di valore monetario. Non può essere convertito in denaro, credito o servizi di locali terzi.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Founder Pass acquistati in pre-sale</h2>
        <p class="mt-3">I Founder Pass permettono di acquistare prima del lancio una subscription digitale WAYOUT a prezzo founder. Il pagamento è unico e anticipato, ma l’utilizzo delle funzionalità inizia soltanto dal go-live pubblico dell’app.</p>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><em><strong>La pre-sale non è accesso anticipato. </strong></em></p>
            <p class="text-slate-700">L’acquisto non dà accesso all’app prima del lancio, a una beta pubblica, a eventi, ingressi o servizi fisici. Blocca esclusivamente il Founder Pass che sarà attivabile dal go-live e inizierà a decorrere dalla corretta attivazione individuale.</p>
        </div>
        <h3 class="mt-6 text-xl font-black text-slate-900">Founder Join 12M</h3>
        <p class="mt-3"><em><strong>Prezzo totale: </strong></em>€29, IVA e oneri fiscali applicabili inclusi. <em><strong>Disponibilità massima: </strong></em>600 pass.</p>
        <p class="mt-3">Founder Join 12M abilita per 12 mesi dalla corretta attivazione individuale, possibile dal go-live le funzionalità Join rese disponibili nell’app, tra cui la scoperta di tavoli o occasioni sociali, l’invio di richieste di partecipazione e l’accesso alle chat dei tavoli per i quali la richiesta viene accettata.</p>
        <p class="mt-3"><em><strong>Non garantisce </strong></em>l’accettazione in uno specifico tavolo, la presenza di tavoli in ogni data o luogo, l’ingresso in una determinata serata o locale, né un numero minimo di utenti o occasioni sociali. Non include funzioni Creator.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">Founder Creator 12M</h3>
        <p class="mt-3"><em><strong>Prezzo totale: </strong></em>€59, IVA e oneri fiscali applicabili inclusi. <em><strong>Disponibilità massima: </strong></em>200 pass.</p>
        <p class="mt-3">Founder Creator 12M comprende tutte le funzionalità Join e, inoltre, le funzionalità per creare e gestire tavoli digitali o social experience e le relative richieste, secondo le regole e i limiti del servizio.</p>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><em><strong>Nessuna selezione discrezionale aggiuntiva. </strong></em></p>
            <p class="text-slate-700">Chi acquista Founder Creator 12M non deve superare un ulteriore procedimento discrezionale di selezione. Per l’attivazione deve completare i normali requisiti dell’account: registrazione, verifica del numero di telefono e della maggiore età, dati veritieri, fotografia personale reale e conforme e accettazione dei termini e delle regole applicabili.</p>
        </div>
        <p class="mt-3"><em><strong>Limiti delle funzioni Creator. </strong></em>L’acquisto non attribuisce un diritto assoluto o illimitato a creare qualsiasi tavolo, in qualsiasi luogo, data o numero. Le funzioni Creator restano soggette a limiti di pubblicazione, capienza, frequenza, sovrapposizione, fair use, moderazione, sicurezza, disponibilità tecnica e regole della community, senza svuotare il contenuto essenziale del piano acquistato.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Quando partono e quanto durano</h2>
        <h3 class="mt-6 text-xl font-black text-slate-900">Founder Pass</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>Sono attivabili dal go-live pubblico dell’app nella città pilota di Milano; i 12 mesi decorrono dalla corretta attivazione individuale del singolo utente, non dalla data di acquisto e non automaticamente dal go-live.</li>
            <li>Durano 12 mesi.</li>
            <li>Terminano senza rinnovo automatico e senza ulteriori addebiti.</li>
            <li>Alla scadenza, l’utente potrà scegliere liberamente se acquistare un eventuale piano ordinario disponibile.</li>
            <li>Se l’utente completa la registrazione o inizia a usare l’app in ritardo per propria scelta, la durata decorre solo dalla corretta attivazione individuale.</li>
        </ul>
        <h3 class="mt-6 text-xl font-black text-slate-900">Waitlist Pass</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>Dura 60 giorni dalla corretta attivazione individuale.</li>
            <li>Deve essere attivato entro il termine comunicato da WAYOUT, di regola 30 giorni dall’email di disponibilità.</li>
            <li>Non si rinnova automaticamente e non genera addebiti.</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Pass personali e non cumulabili</h2>
        <p class="mt-3">I Pass sono personali e destinati a essere associati all’account dell’utente. Non possono essere ceduti, trasferiti o rivenduti, salvo la gestione di errori materiali o di recapiti non più accessibili secondo le verifiche di WAYOUT.</p>
        <p class="mt-3"><em><strong>Regola di non cumulabilità. </strong></em>Il Founder Pass non si somma al Waitlist Pass gratuito di 60 giorni, a eventuali Launch Offer o ad altre promozioni incompatibili, salvo diversa comunicazione espressa di WAYOUT. Chi acquista un Founder Pass utilizzerà dal go-live il piano acquistato.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Che cosa riguarda il Pass</h2>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><em><strong>Il Pass riguarda WAYOUT, non il locale. </strong></em></p>
            <p class="text-slate-700">I Pass danno accesso alle funzionalità digitali della piattaforma. Non sono biglietti, prenotazioni presso locali, pacchetti di eventi, crediti da spendere o garanzie di ottenere specifici risultati sociali.</p>
        </div>
        <h3 class="mt-6 text-xl font-black text-slate-900">Il Pass non include e non garantisce</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>ingressi, liste, ticket, prenotazioni o ammissione in locali, club o eventi;</li>
            <li>tavoli fisici, bottiglie, drink, consumazioni, ristorazione, guardaroba o trasporto;</li>
            <li>prezzi, condizioni, disponibilità o qualità dei servizi offerti dai locali;</li>
            <li>la presenza di un numero minimo di utenti, creator, tavoli o serate;</li>
            <li>l’accettazione in tavoli specifici o la partecipazione effettiva a eventi determinati;</li>
            <li>affinità, compatibilità personale, amicizia, relazione, gradimento reciproco o esito positivo dell’incontro;</li>
            <li>l’assenza di cancellazioni, no-show, condotte scorrette, illecite o rischi legati alle interazioni tra persone.</li>
        </ul>
        <p class="mt-3">Eventuali costi, prenotazioni o rapporti con locali, creator, utenti o altri terzi sono separati dal Pass e restano disciplinati dagli accordi direttamente conclusi tra i soggetti interessati, salvo una specifica partnership ufficiale espressamente indicata da WAYOUT.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">7. Milano launch e disponibilità geografica</h2>
        <p class="mt-3">L’operatività iniziale è prevista nella città pilota di Milano. Nella prima fase, tavoli, creator, utenti e occasioni sociali saranno concentrati principalmente su Milano.</p>
        <p class="mt-3">L’estensione ad altre città italiane potrà avvenire progressivamente, ma non è garantita entro date specifiche. Il semplice accesso tecnico all’app da altri territori non garantisce la presenza di una community o di occasioni sociali nella località dell’utente.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">8. Acquisto, lancio, recesso e rimborsi</h2>
        <h3 class="mt-6 text-xl font-black text-slate-900">Acquisto e disponibilità</h3>
        <ul class="mt-3 list-disc space-y-2 pl-6">
            <li>La pre-sale è riservata a persone fisiche maggiorenni iscritte alla waitlist WAYOUT e abilitate ad accedere all’acquisto.</li>
            <li>I prezzi di €29 e €59 sono finali e comprendono IVA e oneri fiscali applicabili.</li>
            <li>La disponibilità del Pass si consolida soltanto dopo il pagamento e la conferma dell’ordine.</li>
            <li>La pre-sale può chiudersi anticipatamente per raggiungimento dei cap o negli altri casi previsti dalle condizioni applicabili.</li>
        </ul>
        <h3 class="mt-6 text-xl font-black text-slate-900">Diritto di recesso</h3>
        <p class="mt-3">Il consumatore può esercitare il diritto di recesso entro 14 giorni di calendario dalla conclusione dell’acquisto, senza indicarne il motivo, secondo le modalità descritte nella pagina <a class="font-black text-violet-700" href="/recedere-dal-contratto">Recesso e rimborsi</a> e nei documenti contrattuali.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">Se il lancio slitta</h3>
        <p class="mt-3">La data ordinaria comunicata per il lancio è indicativa. Se il go-live viene posticipato ma avviene entro il 31 dicembre 2026, il Founder Pass sarà attivabile dal go-live effettivo e durerà comunque 12 mesi dalla successiva corretta attivazione individuale. Il semplice rinvio entro tale data non comporta, di per sé, un rimborso automatico, fermo restando il recesso tempestivo e le altre tutele applicabili.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">Se l’app non va live entro il 31 dicembre 2026</h3>
        <p class="mt-3">WAYOUT informerà gli acquirenti e consentirà di scegliere, entro una finestra non inferiore a 30 giorni dalla comunicazione, se mantenere il Founder Pass per il successivo go-live oppure richiedere il rimborso integrale. In assenza di richiesta entro la finestra comunicata, il Pass resterà valido per il successivo lancio, fatti salvi i diritti inderogabili.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">Se il lancio viene annullato definitivamente</h3>
        <p class="mt-3">In caso di decisione definitiva di non lanciare l’app o di non rendere disponibile il servizio oggetto della pre-sale, WAYOUT disporrà il rimborso integrale, di regola sullo stesso mezzo di pagamento e senza necessità di una preventiva richiesta.</p>
        <h3 class="mt-6 text-xl font-black text-slate-900">Mancato utilizzo e violazioni</h3>
        <p class="mt-3">Il mancato utilizzo volontario del Pass, la mancata accettazione in specifici tavoli, una community inferiore alle aspettative o l’assenza di un determinato risultato sociale non danno di per sé diritto al rimborso. Limitazioni o sospensioni dovute a violazioni delle regole sono disciplinate dai Termini dell’app; eventuali errori riconosciuti saranno gestiti con un rimedio proporzionato.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">9. Domande frequenti</h2>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">L’iscrizione alla waitlist è a pagamento?</p>
            <p class="text-slate-700">No. È gratuita, non comporta addebiti e non obbliga ad acquistare un Founder Pass.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Essere nella waitlist garantisce il Waitlist Pass?</p>
            <p class="text-slate-700">No. Il beneficio è riservato ai primi 2.000 utenti validi che soddisfano i requisiti e completano l’attivazione entro il termine comunicato.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Da quando decorrono i 60 giorni del Waitlist Pass?</p>
            <p class="text-slate-700">Dalla corretta attivazione individuale nell’app, non automaticamente dalla data generale di go-live.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Posso creare tavoli con il Waitlist Pass o con Founder Join?</p>
            <p class="text-slate-700">No. Entrambi comprendono soltanto le funzionalità Join. Per creare e gestire tavoli serve Founder Creator 12M o un altro piano Creator disponibile.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Founder Creator richiede una selezione aggiuntiva?</p>
            <p class="text-slate-700">No. Non è prevista una selezione discrezionale ulteriore per chi lo acquista. Restano necessari i requisiti standard dell’account e si applicano i normali limiti di sicurezza, moderazione, fair use e qualità.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Il Founder Pass parte il giorno dell’acquisto?</p>
            <p class="text-slate-700">No. È attivabile dal go-live pubblico dell’app a Milano e dura 12 mesi dalla tua corretta attivazione individuale; il periodo tra go-live e attivazione non viene perso.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Il Founder Pass si rinnova automaticamente?</p>
            <p class="text-slate-700">No. Termina dopo 12 mesi senza ulteriori addebiti.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Posso sommare il Founder Pass ai 60 giorni gratuiti?</p>
            <p class="text-slate-700">No, salvo diversa comunicazione espressa di WAYOUT. Il Founder Pass sostituisce il beneficio gratuito e le promozioni incompatibili.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Il Pass garantisce l’ingresso in un locale o un tavolo fisico?</p>
            <p class="text-slate-700">No. Il Pass riguarda le funzionalità digitali WAYOUT. Ingressi, prenotazioni, consumazioni e servizi dei locali sono separati.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Il Pass garantisce che la mia richiesta venga accettata?</p>
            <p class="text-slate-700">No. Le richieste possono essere accettate, rifiutate, ritirate o scadere e dipendono da capienza, regole, sicurezza e disponibilità degli utenti.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Che cosa succede se l’app non viene lanciata entro il 31 dicembre 2026?</p>
            <p class="text-slate-700">Potrai scegliere, entro la finestra comunicata, se mantenere il Founder Pass per il successivo go-live oppure richiedere il rimborso integrale.</p>
        </div>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950">Dove trovo le regole complete?</p>
            <p class="text-slate-700">Nei <a class="font-black text-violet-700" href="/termini-e-condizioni">Termini di utilizzo del sito e della waitlist</a>, nei <a class="font-black text-violet-700" href="/termini-di-vendita">Termini di vendita online</a>, nelle <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a>, nella pagina <a class="font-black text-violet-700" href="/recedere-dal-contratto">Recesso e rimborsi</a> e nella <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a>.</p>
        </div>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">10. Documenti applicabili e assistenza</h2>
        <p class="mt-3">Questa pagina spiega in linguaggio semplice il funzionamento dei Pass. Non sostituisce i documenti contrattuali completi. In caso di differenze o dubbi, prevalgono i <a class="font-black text-violet-700" href="/termini-e-condizioni">Termini di utilizzo del sito e della waitlist</a>, i <a class="font-black text-violet-700" href="/termini-di-vendita">Termini di vendita online</a>, le <a class="font-black text-violet-700" href="/condizioni-di-pre-sale">Condizioni specifiche di pre-sale</a>, l’informativa e la policy <a class="font-black text-violet-700" href="/recedere-dal-contratto">Recesso e rimborsi</a>, i futuri Termini dell’app e le norme inderogabili applicabili.</p>
        <p class="mt-3"><em><strong>Assistenza generale: <a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a></strong></em></p>
        <p class="mt-3"><em><strong>Recesso, rimborsi, fatturazione e pagamenti: </strong></em><a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a></p>
        <p class="mt-3"><em><strong>PEC: </strong></em><a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a></p>
        <div class="mt-4 space-y-2 rounded-lg border border-violet-200 bg-violet-50/60 p-4">
            <p class="font-black text-slate-950"><em><strong>Prima di acquistare. </strong></em></p>
            <p class="text-slate-700">Consulta sempre il riepilogo del piano, il prezzo totale, la decorrenza, la durata, l’assenza di rinnovo automatico e i documenti contrattuali disponibili nel checkout.</p>
        </div>
        <p class="mt-3">ultimo aggiornamento: 27 agosto 2026</p>
    </section>
</div>
@endsection
