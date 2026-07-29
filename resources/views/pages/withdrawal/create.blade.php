@extends('pages.legal.layout', [
    'title' => $title,
    'description' => $description,
    'version' => $version,
    'updated' => $updated,
])

@section('legal-content')
<div class="space-y-12">
    <section id="recedere" class="overflow-hidden rounded-[1.5rem] border-2 border-violet-300 bg-gradient-to-br from-violet-50 to-white shadow-lg shadow-violet-900/10">
        <div class="bg-slate-950 px-5 py-6 text-white sm:px-8">
            <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-300">1. Funzione online</p>
            <h2 class="mt-3 text-3xl font-black sm:text-4xl">Recedere dal contratto qui</h2>
            <p class="mt-3 max-w-2xl font-semibold leading-7 text-slate-300">Compila il modulo, verifica la dichiarazione e confermala. Non è necessario indicare un motivo.</p>
        </div>

        <form method="POST" action="{{ route('withdrawal.review.store') }}" class="grid gap-5 p-5 sm:grid-cols-2 sm:p-8">
            @csrf
            <div class="absolute -left-[10000px]" aria-hidden="true">
                <label for="website">Sito web</label>
                <input id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            @if ($errors->any() && ! session('order_not_found'))
                <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 font-bold text-rose-800 sm:col-span-2" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <label class="grid gap-2 font-black text-slate-800">
                Nome
                <input name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" maxlength="100" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
            </label>
            <label class="grid gap-2 font-black text-slate-800">
                Cognome
                <input name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" maxlength="100" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
            </label>
            <label class="grid gap-2 font-black text-slate-800 sm:col-span-2">
                Numero d’ordine o altro identificativo del contratto
                <input name="order_reference" value="{{ old('order_reference') }}" required maxlength="100" placeholder="Es. WO-2026-000123" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
            </label>
            <label class="grid gap-2 font-black text-slate-800">
                Email usata per l’acquisto
                <input type="email" name="purchase_email" value="{{ old('purchase_email') }}" required autocomplete="email" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
            </label>
            <label class="grid gap-2 font-black text-slate-800">
                Email alla quale inviare la ricevuta
                <input type="email" name="receipt_email" value="{{ old('receipt_email') }}" required autocomplete="email" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
            </label>
            <label class="grid gap-2 font-black text-slate-800">
                Data di acquisto <span class="text-xs font-semibold text-slate-500">(se disponibile)</span>
                <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" max="{{ now()->toDateString() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
            </label>
            <label class="grid gap-2 font-black text-slate-800">
                Pass acquistato
                <select name="plan" required class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
                    <option value="">Seleziona</option>
                    <option value="join" @selected(old('plan') === 'join')>Founder Join 12M</option>
                    <option value="creator" @selected(old('plan') === 'creator')>Founder Creator 12M</option>
                    <option value="other" @selected(old('plan') === 'other')>Altro / non ricordo</option>
                </select>
            </label>

            <div class="rounded-lg bg-slate-100 p-4 text-sm font-semibold leading-6 text-slate-600 sm:col-span-2">
                I dati sono usati per registrare la dichiarazione, individuare il contratto e inviare la ricevuta. Non chiediamo il motivo del recesso né dati della carta. Consulta la <a href="{{ route('legal.privacy') }}" class="font-black text-violet-700">Privacy policy</a>.
            </div>
            <button type="submit" class="rounded-xl bg-violet-700 px-6 py-4 text-lg font-black text-white shadow-lg shadow-violet-700/20 transition hover:bg-violet-800 focus:outline-none focus:ring-4 focus:ring-violet-300 sm:col-span-2">
                Verifica la dichiarazione
            </button>
        </form>

        @if (session('order_not_found'))
            <dialog id="order-not-found-modal" class="fixed inset-0 m-auto w-[calc(100%-2rem)] max-w-lg rounded-[1.5rem] border-0 p-0 shadow-2xl backdrop:bg-slate-950/60">
                <div class="p-6 text-center sm:p-8">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-100 text-2xl font-black text-rose-700">!</div>
                    <h3 class="mt-5 text-2xl font-black text-slate-950">Ordine non trovato</h3>
                    <p class="mt-3 text-lg font-bold leading-7 text-slate-700">Ordine non esistente, controlla attentamente nell'email di acquisto</p>
                    <form method="dialog" class="mt-6">
                        <button class="w-full rounded-xl bg-slate-950 px-5 py-3 font-black text-white hover:bg-violet-800">Chiudi e controlla</button>
                    </form>
                </div>
            </dialog>
            <script>
                document.getElementById('order-not-found-modal')?.showModal();
            </script>
        @endif
    </section>

    <section id="informativa-recesso">
        <div class="mb-6 border-b border-slate-200 pb-5">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-violet-700">2. Informativa</p>
            <h2 class="mt-2 text-3xl font-black text-slate-950">Informativa sul diritto di recesso</h2>
        </div>
        <div class="space-y-8">{!! $withdrawalInfo->content_snapshot !!}</div>
    </section>

    <section id="refund-policy" class="rounded-[1.5rem] border-2 border-amber-300 bg-amber-50/60 p-5 sm:p-8">
        <div class="mb-6 border-b border-amber-300 pb-5">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-amber-800">3. Regole contrattuali aggiuntive</p>
            <h2 class="mt-2 text-3xl font-black text-slate-950">Refund Policy</h2>
            <p class="mt-3 font-semibold leading-7 text-slate-700">Questa sezione è distinta dal diritto legale di recesso e disciplina ulteriori ipotesi di rimborso offerte da WAYOUT.</p>
        </div>
        <div class="space-y-8">{!! $refundPolicy->content_snapshot !!}</div>
    </section>
</div>
@endsection
