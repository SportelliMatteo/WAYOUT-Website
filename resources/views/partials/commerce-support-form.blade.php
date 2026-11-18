@if(session('commerce_ticket.'.$source))
    <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-5 font-semibold text-emerald-900" role="status">
        <p>La tua richiesta è stata ricevuta dall’assistenza.</p>
        <p class="mt-2 break-all text-sm">Riferimento: {{ session('commerce_ticket.'.$source) }}</p>
        <p class="mt-2 text-sm">L’assistenza ti contatterà all’email indicata per verificare e gestire la richiesta. L’invio non conferma l’esecuzione del rimborso o della disdetta.</p>
    </div>
@else
    <form method="POST" action="{{ route($source === 'promo-withdrawal' ? 'withdrawal.review.store' : 'subscription.cancel.store') }}" class="mt-6 grid gap-5">
        @csrf
        @include('partials.recaptcha', ['action' => $source === 'promo-withdrawal' ? 'withdrawal_review' : 'subscription_cancel'])
        <input type="hidden" name="request_id" value="{{ $requestId }}">
        <div class="absolute -left-[10000px]" aria-hidden="true">
            <label for="{{ $source }}-website">Sito web</label>
            <input id="{{ $source }}-website" name="website" tabindex="-1" autocomplete="off">
        </div>
        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 font-bold text-rose-800" role="alert">{{ $errors->first() }}</div>
        @endif
        <label class="grid gap-2 font-black text-slate-800">Nome e cognome
            <input name="name" value="{{ old('name') }}" required maxlength="200" autocomplete="name" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
        </label>
        <label class="grid gap-2 font-black text-slate-800">Email associata all’account nell’app
            <input type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
            <span class="text-sm font-medium leading-6 text-slate-500">Inserisci l’email associata all’account WAYOUT nell’app per cui richiedi questa operazione.</span>
        </label>
        <label class="grid gap-2 font-black text-slate-800">Numero di telefono associato all’account nell’app
            <input type="tel" name="phone" value="{{ old('phone') }}" required maxlength="40" autocomplete="tel" inputmode="tel" placeholder="Es. +39 333 1234567" class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">
            <span class="text-sm font-medium leading-6 text-slate-500">Inserisci il numero di telefono associato all’account WAYOUT nell’app per cui richiedi questa operazione.</span>
        </label>
        <label class="grid gap-2 font-black text-slate-800">Messaggio
            <textarea name="message" required maxlength="4000" rows="6" placeholder="Descrivi la tua richiesta e, se disponibile, indica il numero d’ordine per facilitarne la gestione." class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-semibold focus:border-violet-500 focus:outline-none focus:ring-4 focus:ring-violet-100">{{ old('message') }}</textarea>
            <span class="text-sm font-medium leading-6 text-slate-500">Puoi aggiungere il numero d’ordine, il prodotto acquistato e l’email usata per l’acquisto. Non inserire password o dati della carta.</span>
        </label>
        <label class="flex items-start gap-3 rounded-xl bg-violet-50 p-4 text-sm font-semibold leading-6 text-slate-700">
            <input type="checkbox" name="request_confirmed" value="1" required class="mt-1 h-5 w-5 shrink-0">
            <span>{{ $source === 'promo-withdrawal' ? 'Confermo di voler inviare la mia richiesta di recesso e rimborso all’assistenza.' : 'Confermo di voler inviare la mia richiesta di disdetta e di non rinnovo all’assistenza.' }}</span>
        </label>
        <p class="text-sm leading-6 text-slate-600">I dati sono usati per gestire la tua richiesta. Consulta la <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener" class="font-bold text-violet-700 underline">Privacy policy</a>.</p>
        <button type="submit" class="rounded-xl bg-violet-700 px-6 py-4 text-lg font-black text-white shadow-lg shadow-violet-700/20 transition hover:bg-violet-800 focus:outline-none focus:ring-4 focus:ring-violet-300">{{ $source === 'promo-withdrawal' ? 'Invia richiesta di recesso e rimborso' : 'Invia richiesta di disdetta' }}</button>
    </form>
@endif
