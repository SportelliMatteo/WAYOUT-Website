@extends('layouts.app', ['title' => __('messages.withdrawal.review_meta_title'), 'description' => __('messages.withdrawal.review_meta_description'), 'robots' => 'noindex,nofollow'])

@section('content')
<section class="py-12 lg:py-20">
    <div class="wayout-shell max-w-3xl">
        <div class="wayout-panel rounded-[1.5rem] p-6 sm:p-10">
            <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-700">{{ __('messages.withdrawal.step_two') }}</p>
            <h1 class="mt-3 text-4xl font-black text-slate-950">{{ __('messages.withdrawal.review_declaration') }}</h1>
            <p class="mt-4 font-semibold leading-7 text-slate-600">{{ __('messages.withdrawal.review_help') }}</p>
            @if($errors->any())
                <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 font-bold text-rose-800" role="alert">{{ $errors->first() }} <a class="underline" href="{{ route('contact') }}">Contatta l’assistenza</a>.</div>
                @if(session('withdrawal_receipt_url'))
                    <a href="{{ session('withdrawal_receipt_url') }}" class="mt-3 inline-block font-bold text-violet-700 underline">Apri e conserva la ricevuta della dichiarazione registrata</a>
                @endif
            @endif

            <dl class="mt-8 grid gap-4 rounded-lg border border-slate-200 bg-slate-50 p-5 sm:grid-cols-2">
                <div><dt class="text-xs font-black uppercase text-slate-500">{{ __('messages.withdrawal.name') }}</dt><dd class="mt-1 font-black">{{ $review['first_name'] }} {{ $review['last_name'] }}</dd></div>
                <div><dt class="text-xs font-black uppercase text-slate-500">{{ __('messages.withdrawal.contract_order') }}</dt><dd class="mt-1 font-black">{{ $review['order_reference'] }}</dd></div>
                <div><dt class="text-xs font-black uppercase text-slate-500">{{ __('messages.withdrawal.purchase_email_short') }}</dt><dd class="mt-1 font-black">{{ $review['purchase_email'] }}</dd></div>
                <div><dt class="text-xs font-black uppercase text-slate-500">{{ __('messages.withdrawal.receipt_email_short') }}</dt><dd class="mt-1 font-black">{{ $review['receipt_email'] }}</dd></div>
            </dl>

            <div class="mt-6 rounded-lg border-2 border-violet-200 bg-violet-50 p-5">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-violet-700">{{ __('messages.withdrawal.declaration_sending') }}</p>
                <p class="mt-3 font-semibold leading-7 text-slate-800">{{ $declaration }}</p>
                <p class="mt-3 font-semibold leading-7 text-slate-800">Chiedo il rimborso del pacchetto promo acquistato. L’assistenza gestirà la richiesta e mi ricontatterà all’email indicata.</p>
            </div>

            <form method="POST" action="{{ route('withdrawal.confirm') }}" class="mt-8">
                @csrf
                <input type="hidden" name="nonce" value="{{ $review['nonce'] }}">
                <button class="w-full rounded-xl bg-violet-700 px-6 py-4 text-lg font-black text-white shadow-lg shadow-violet-700/20 transition hover:bg-violet-800 focus:outline-none focus:ring-4 focus:ring-violet-300">{{ __('messages.withdrawal.confirm') }}</button>
            </form>
            <a href="{{ route('legal.refunds', ['lang' => app()->getLocale()]) }}#recedere" class="mt-4 block text-center font-black text-slate-600 hover:text-violet-700">{{ __('messages.withdrawal.back_to_form') }}</a>
        </div>
    </div>
</section>
@endsection
