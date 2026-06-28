@extends('layouts.app', [
    'title' => 'Contatti - Wayout',
    'description' => 'Scrivici per informazioni, supporto o collaborazioni con Wayout.'
])

@section('content')
<section class="py-10 lg:py-20">
    <div class="wayout-shell grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
        <div class="lg:sticky lg:top-32">
            <span class="inline-flex rounded-full border border-violet-200 bg-white/80 px-4 py-2 text-sm font-black uppercase tracking-[0.22em] text-violet-700">
                Contatti
            </span>
            <h1 class="mt-6 text-4xl font-black leading-[0.98] text-slate-950 sm:text-6xl">
                Portiamo il tuo club dentro WAYOUT.
            </h1>
            <p class="mt-5 text-base font-medium leading-7 text-slate-600 sm:mt-6 sm:text-lg sm:leading-8">
                Sei un club, un promoter, vuoi collaborare con noi o vuoi delle info generali? Scrivici: ti rispondiamo con tutte le informazioni utili.
            </p>
            <div class="mt-8 rounded-[2rem] bg-slate-950 p-5 text-white shadow-2xl sm:p-6">
                <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-200">Perché contattarci</p>
                <div class="mt-5 space-y-4">
                    <div class="rounded-3xl bg-white/[0.08] p-4">
                        <p class="font-black">Club e venue</p>
                        <p class="mt-1 text-sm text-slate-300">Porta eventi e tavoli davanti a un pubblico già pronto a uscire.</p>
                    </div>
                    <div class="rounded-3xl bg-white/[0.08] p-4">
                        <p class="font-black">Collaborazioni</p>
                        <p class="mt-1 text-sm text-slate-300">Costruiamo attivazioni, contenuti e partnership intorno alla nightlife.</p>
                    </div>
                    <div class="rounded-3xl bg-white/[0.08] p-4">
                        <p class="font-black">Curiosità e informazioni</p>
                        <p class="mt-1 text-sm text-slate-300">Hai domande? Vuoi saperne di più su Wayout? Scrivici e ti risponderemo con piacere.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="wayout-panel rounded-[2rem] p-5 sm:rounded-[2.5rem] sm:p-8">
            @if(session('contact_success'))
                <div class="mb-6 rounded-3xl border border-emerald-300 bg-emerald-50 p-5 font-semibold text-emerald-900">
                    Grazie! Abbiamo ricevuto il tuo messaggio e ti risponderemo presto.
                </div>
            @endif

            @if(session('contact_error'))
                <div class="mb-6 rounded-3xl border border-rose-300 bg-rose-50 p-5 font-semibold text-rose-900">
                    {{ session('contact_error') }}
                </div>
            @endif

            <form class="space-y-6" method="POST" action="{{ route('contact.store') }}">
                @csrf
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="name" class="mb-2 block text-sm font-black text-slate-950">Nome</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required class="block min-h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-base font-semibold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100" placeholder="Mario" />
                        @error('name')<p class="mt-2 text-sm font-semibold text-rose-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-black text-slate-950">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required class="block min-h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-base font-semibold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100" placeholder="prova@esempio.com" />
                        @error('email')<p class="mt-2 text-sm font-semibold text-rose-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="subject" class="mb-2 block text-sm font-black text-slate-950">Oggetto</label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" class="block min-h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-base font-semibold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100" placeholder="Facci sapere come possiamo aiutarti" />
                    @error('subject')<p class="mt-2 text-sm font-semibold text-rose-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="message" class="mb-2 block text-sm font-black text-slate-950">Messaggio</label>
                    <textarea id="message" name="message" rows="7" required class="block w-full rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4 text-base font-semibold text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-100" placeholder="Scrivi qui il tuo messaggio...">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-2 text-sm font-semibold text-rose-500">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="w-full rounded-full wayout-purple px-8 py-4 text-lg font-black text-white shadow-[0_18px_40px_rgba(124,35,245,0.32)] transition hover:scale-[1.01] sm:w-auto">
                    Invia messaggio
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
