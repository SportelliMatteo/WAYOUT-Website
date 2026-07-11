@extends('pages.legal.layout', [
    'title' => 'Recesso e rimborso',
    'description' => 'Informazioni sul diritto di recesso e sui rimborsi degli acquisti Wayout.',
])

@section('legal-content')
<div class="space-y-8">
    <section>
        <h2 class="text-2xl font-black text-slate-950">1. Diritto di recesso</h2>
        <p class="mt-3">Se acquisti come consumatore, hai normalmente diritto di recedere entro 14 giorni dalla conclusione del contratto, salvo le eccezioni previste dalla legge per contenuti o servizi digitali già eseguiti con consenso espresso e accettazione della perdita del diritto di recesso.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">2. Come richiedere il recesso</h2>
        <p class="mt-3">Per esercitare il recesso scrivi a <a class="font-black text-violet-700" href="mailto:hello@wayoutapp.it">hello@wayoutapp.it</a>, indicando l’email usata per l’acquisto, il pass acquistato e la richiesta esplicita di recesso.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">3. Rimborsi</h2>
        <p class="mt-3">Quando il recesso o il rimborso è dovuto, Wayout provvede al rimborso con lo stesso metodo di pagamento usato dall’utente, salvo diverso accordo, nei tempi previsti dalla normativa applicabile e dai circuiti di pagamento.</p>
    </section>

    <section>
        <h2 class="text-2xl font-black text-slate-950">4. Assistenza</h2>
        <p class="mt-3">Per informazioni sullo stato di una richiesta scrivi a <a class="font-black text-violet-700" href="mailto:hello@wayoutapp.it">hello@wayoutapp.it</a>.</p>
    </section>
</div>
@endsection
