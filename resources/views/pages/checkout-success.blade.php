@extends('layouts.app', [
    'title' => 'Checkout completato - Wayout',
    'description' => 'Il checkout è stato completato con successo.'
])

@section('content')
<section class="py-24">
    <div class="max-w-3xl mx-auto px-5 lg:px-20">
        <div class="rounded-[2rem] border border-emerald-200 bg-emerald-50 p-8 shadow-xl">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-white text-2xl">✓</div>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-emerald-700">Pagamento completato</p>
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Il tuo checkout è andato a buon fine</h1>
                </div>
            </div>
            <p class="mt-6 text-lg text-slate-700 leading-relaxed">Grazie per aver scelto un Founder Pass pre-lancio. Il tuo acquisto è stato registrato e la conferma ti arriverà via email.</p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-full bg-slate-900 px-6 py-3 text-lg font-semibold text-white">Torna alla home</a>
            </div>
        </div>
    </div>
</section>
@endsection
