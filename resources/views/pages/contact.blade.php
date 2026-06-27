@extends('layouts.app', [
    'title' => 'Contatti - Wayout',
    'description' => 'Scrivici per informazioni, supporto o collaborazioni con Wayout.'
])

@section('content')
<section class="bg-white mt-16">
    <div class="py-8 lg:py-16 px-4 mx-auto max-w-screen-md">
        <h2 class="mb-4 text-4xl tracking-tight font-extrabold text-center text-slate-900">
            CONTATTACI
        </h2>
        <p class="mb-8 lg:mb-16 font-light text-center text-gray-500 sm:text-xl">
            Sei un club e vuoi essere presente sull’app? Compila il modulo e ti risponderemo al più presto.
        </p>

        @if(session('contact_success'))
            <div class="mb-8 rounded-3xl border border-emerald-500/30 bg-emerald-500/10 p-5 text-slate-900">
                Grazie! Abbiamo ricevuto il tuo messaggio e ti risponderemo presto.
            </div>
        @endif

        @if(session('contact_error'))
            <div class="mb-8 rounded-3xl border border-rose-500/30 bg-rose-500/10 p-5 text-slate-900">
                {{ session('contact_error') }}
            </div>
        @endif

        <form class="space-y-8" method="POST" action="{{ route('contact.store') }}">
            @csrf
            <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />

            <div>
                <label for="name" class="block mb-2 text-sm font-medium text-slate-900">Nome</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="block p-3 w-full text-sm text-slate-900 bg-slate-50 rounded-xl border border-slate-200 focus:ring-slate-500 focus:border-slate-500" placeholder="Mario" />
                @error('name')<p class="mt-2 text-sm text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block mb-2 text-sm font-medium text-slate-900">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="block p-3 w-full text-sm text-slate-900 bg-slate-50 rounded-xl border border-slate-200 focus:ring-slate-500 focus:border-slate-500" placeholder="prova@esempio.com" />
                @error('email')<p class="mt-2 text-sm text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="subject" class="block mb-2 text-sm font-medium text-slate-900">Oggetto</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" class="block p-3 w-full text-sm text-slate-900 bg-slate-50 rounded-xl border border-slate-200 focus:ring-slate-500 focus:border-slate-500" placeholder="Facci sapere come possiamo aiutarti" />
                @error('subject')<p class="mt-2 text-sm text-rose-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="message" class="block mb-2 text-sm font-medium text-slate-900">Messaggio</label>
                <textarea id="message" name="message" rows="6" required class="block p-3 w-full text-sm text-slate-900 bg-slate-50 rounded-xl border border-slate-200 focus:ring-slate-500 focus:border-slate-500" placeholder="Scrivi qui il tuo messaggio...">{{ old('message') }}</textarea>
                @error('message')<p class="mt-2 text-sm text-rose-500">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="rounded-3xl bg-slate-900 px-8 py-4 text-white font-semibold hover:bg-slate-800">Invia</button>
        </form>
    </div>
</section>
@endsection
