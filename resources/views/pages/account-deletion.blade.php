@extends('layouts.app', ['title' => 'Richiesta cancellazione account — WAYOUT', 'description' => 'Invia all’assistenza WAYOUT la richiesta di cancellazione del tuo account.'])

@section('content')
<section class="py-12 lg:py-20">
    <div class="wayout-shell max-w-3xl">
        <div class="wayout-panel rounded-[1.5rem] p-6 sm:p-10">
            <p class="brand-eyebrow">Assistenza account</p>
            <h1 class="mt-4 text-4xl font-black text-slate-950">Richiedi la cancellazione dell’account</h1>
            <p class="mt-4 font-semibold leading-7 text-slate-600">Compila il modulo con l’email associata al tuo account WAYOUT. L’assistenza ti contatterà via email per verificare e gestire la richiesta.</p>

            <div role="alert" aria-labelledby="deletion-warning-title" class="mt-6 rounded-xl border-2 border-rose-300 bg-rose-50 p-5 text-rose-900">
                <h2 id="deletion-warning-title" class="text-lg font-black">Attenzione: la cancellazione comporta la perdita delle tue attività</h2>
                <p class="mt-2 font-semibold leading-7">{{ $warning }}</p>
                <p class="mt-2 text-sm leading-6">L’invio del modulo apre una richiesta all’assistenza. La perdita delle attività avverrà quando si procederà alla cancellazione dell’account.</p>
            </div>

            @if(session('account_deletion_ticket'))
                <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-5 font-semibold text-emerald-900" role="status">
                    <p>La richiesta di cancellazione dell’account è stata ricevuta.</p>
                    <p class="mt-2 break-all text-sm">Riferimento: {{ session('account_deletion_ticket') }}</p>
                    <p class="mt-2 text-sm">L’assistenza ti contatterà all’email indicata per la verifica e la gestione della richiesta. Il tuo account non è stato cancellato con questo invio.</p>
                </div>
            @else
                <form method="POST" action="{{ route('account.deletion.store') }}" class="mt-8 grid gap-5">
                    @csrf
                    @include('partials.recaptcha', ['action' => 'account_deletion'])
                    <input type="hidden" name="request_id" value="{{ $requestId }}">
                    <div class="absolute -left-[10000px]" aria-hidden="true">
                        <label for="deletion-website">Sito web</label>
                        <input id="deletion-website" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    @if($errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 font-bold text-rose-800" role="alert">{{ $errors->first() }}</div>
                    @endif
                    <label class="grid gap-2 font-black text-slate-800">Nome e cognome
                        <input name="name" value="{{ old('name') }}" required maxlength="200" autocomplete="name" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
                    </label>
                    <label class="grid gap-2 font-black text-slate-800">Email associata all’account nell’app
                        <input type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
                        <span class="text-sm font-medium leading-6 text-slate-500">Inserisci l’email associata all’account WAYOUT nell’app per cui richiedi la cancellazione.</span>
                    </label>
                    <label class="grid gap-2 font-black text-slate-800">Numero di telefono associato all’account nell’app
                        <input type="tel" name="phone" value="{{ old('phone') }}" required maxlength="40" autocomplete="tel" inputmode="tel" placeholder="Es. +39 333 1234567" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
                        <span class="text-sm font-medium leading-6 text-slate-500">Inserisci il numero di telefono associato all’account WAYOUT nell’app per cui richiedi la cancellazione.</span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl bg-rose-50 p-4 text-sm font-semibold leading-6 text-slate-700">
                        <input type="checkbox" name="consequences_accepted" value="1" required aria-describedby="deletion-warning-title" class="mt-1 h-5 w-5 shrink-0">
                        <span>Chiedo la cancellazione del mio account e confermo di aver compreso che, procedendo alla cancellazione, perderò tutte le partecipazioni agli eventi, le richieste di partecipazione e le creazioni effettuate.</span>
                    </label>
                    <p class="text-sm leading-6 text-slate-600">I dati sono usati per gestire la richiesta. Consulta la <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener" class="font-bold text-violet-700 underline">Privacy policy</a>.</p>
                    <button type="submit" class="rounded-xl bg-rose-700 px-6 py-4 text-lg font-black text-white transition hover:bg-rose-800 focus:outline-none focus:ring-4 focus:ring-rose-300">Invia richiesta di cancellazione</button>
                </form>
            @endif
            <p class="mt-6 text-sm text-slate-600">Hai bisogno di aiuto? <a href="{{ route('contact') }}" class="font-bold text-violet-700 underline">Contatta l’assistenza</a>.</p>
        </div>
    </div>
</section>
@endsection
