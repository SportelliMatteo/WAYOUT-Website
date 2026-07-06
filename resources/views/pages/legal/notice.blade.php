@extends('pages.legal.layout', [
    'title' => 'Note legali',
    'description' => 'Informazioni societarie, responsabilità e riferimenti legali di Wayout.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black text-slate-950">Informazioni societarie</h2>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div class="rounded-lg bg-white p-4"><dt class="font-black text-slate-500">Ragione sociale</dt><dd class="mt-1 font-black">WAYOUT S.R.L.</dd></div>
            <div class="rounded-lg bg-white p-4"><dt class="font-black text-slate-500">C.F./P.IVA</dt><dd class="mt-1 font-black">14805930964</dd></div>
            <div class="rounded-lg bg-white p-4"><dt class="font-black text-slate-500">REA</dt><dd class="mt-1 font-black">MI2808098</dd></div>
            <div class="rounded-lg bg-white p-4"><dt class="font-black text-slate-500">PEC</dt><dd class="mt-1 font-black">wayout@pec.wayoutapp.it</dd></div>
        </dl>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">Responsabilità sui contenuti</h2>
        <p class="mt-3">Wayout cura i contenuti del sito con attenzione, ma non garantisce che siano sempre completi, aggiornati o privi di errori. I contenuti informativi possono essere modificati senza preavviso.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">Link esterni</h2>
        <p class="mt-3">Il sito può contenere link a servizi di terze parti, come social network o provider di pagamento. Wayout non controlla tali siti e non è responsabile dei relativi contenuti, servizi o trattamenti dati.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">Marchi e contenuti</h2>
        <p class="mt-3">Il marchio WAYOUT, i loghi, le grafiche e i contenuti del sito sono protetti dalle norme applicabili in materia di proprietà intellettuale e concorrenza. Ogni uso non autorizzato è vietato.</p>
    </section>
</div>
@endsection
