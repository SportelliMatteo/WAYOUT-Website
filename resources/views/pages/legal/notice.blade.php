@extends('pages.legal.layout', [
    'title' => 'Note legali',
    'description' => 'Informazioni societarie e contatti ufficiali di WAYOUT.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Titolare del sito</h2>
        <p class="mt-3">Il sito <strong>WAYOUT</strong> e i relativi servizi digitali sono gestiti da <strong>WAYOUT S.r.l.</strong>, società a responsabilità limitata con sede legale in Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italia.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Dati societari</h2>
        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="rounded-lg bg-white p-4">
                <dt class="font-black text-slate-500">Denominazione:</dt>
                <dd class="mt-1">WAYOUT S.r.l.</dd>
            </div>
            <div class="rounded-lg bg-white p-4">
                <dt class="font-black text-slate-500">Sede legale:</dt>
                <dd class="mt-1">Via Guglielmo Marconi 24/B, 20082 Binasco (MI), Italia</dd>
            </div>
            <div class="rounded-lg bg-white p-4">
                <dt class="font-black text-slate-500">Codice fiscale e Partita IVA:</dt>
                <dd class="mt-1">14805930964</dd>
            </div>
            <div class="rounded-lg bg-white p-4">
                <dt class="font-black text-slate-500">Registro delle Imprese:</dt>
                <dd class="mt-1">Milano Monza Brianza Lodi</dd>
            </div>
            <div class="rounded-lg bg-white p-4">
                <dt class="font-black text-slate-500">Numero REA:</dt>
                <dd class="mt-1">MI-2808098</dd>
            </div>
            <div class="rounded-lg bg-white p-4">
                <dt class="font-black text-slate-500">Capitale sociale:</dt>
                <dd class="mt-1">Euro 1.000,00</dd>
            </div>
            <div class="rounded-lg bg-white p-4 sm:col-span-2">
                <dt class="font-black text-slate-500">Assetto societario:</dt>
                <dd class="mt-1">la società non è unipersonale</dd>
            </div>
        </dl>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Contatti ufficiali</h2>
        <dl class="mt-4 space-y-3">
            <div>
                <dt class="font-black text-slate-950">Amministratore unico:</dt>
                <dd>Matteo Sportelli - <a class="font-black text-violet-700" href="mailto:matteo.sportelli@wayoutapp.it">matteo.sportelli@wayoutapp.it</a></dd>
            </div>
            <div>
                <dt class="font-black text-slate-950">Amministrazione e assistenza contrattuale:</dt>
                <dd><a class="font-black text-violet-700" href="mailto:amministrazione@wayoutapp.it">amministrazione@wayoutapp.it</a></dd>
            </div>
            <div>
                <dt class="font-black text-slate-950">Posta elettronica certificata (PEC):</dt>
                <dd><a class="font-black text-violet-700" href="mailto:wayout@pec.wayoutapp.it">wayout@pec.wayoutapp.it</a></dd>
            </div>
        </dl>
        <p class="mt-3">Le comunicazioni che richiedono valore formale o prova dell’invio possono essere trasmesse alla PEC indicata sopra.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Utilizzo del sito e documenti applicabili</h2>
        <p class="mt-3">L’accesso e l’utilizzo del sito sono disciplinati dai <a class="font-black text-violet-700" href="/termini-e-condizioni">Termini di utilizzo del sito e della waitlist</a>. Il trattamento dei dati personali e l’impiego di cookie o altri strumenti di tracciamento sono descritti nella <a class="font-black text-violet-700" href="/privacy-policy">Privacy Policy</a> e nella <a class="font-black text-violet-700" href="/cookie-policy">Cookie Policy</a> pubblicate sul sito.</p>
        <p class="mt-3">L’eventuale acquisto online dei <strong>Founder Pass</strong> è inoltre regolato dai documenti contrattuali e precontrattuali resi disponibili prima del pagamento e richiamati nel checkout.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">5. Proprietà intellettuale</h2>
        <p class="mt-3">Salvo diversa indicazione, marchi, loghi, denominazioni, software, interfacce, testi, contenuti grafici e altri materiali presenti sul sito appartengono a <strong>WAYOUT S.r.l.</strong> o sono utilizzati sulla base di idonei diritti o autorizzazioni. Ogni riproduzione, distribuzione, modifica o impiego non autorizzato resta vietato nei limiti previsti dalla legge e dai <strong>Termini di utilizzo</strong>.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">6. Aggiornamenti</h2>
        <p class="mt-3">Le presenti <strong>Note legali</strong> possono essere aggiornate per riflettere variazioni societarie, organizzative o normative. La versione vigente è quella pubblicata sul sito con la relativa data di aggiornamento.</p>
    </section>
</div>
@endsection
