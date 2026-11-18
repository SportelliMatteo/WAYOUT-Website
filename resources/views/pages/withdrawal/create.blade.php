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
            <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-300">{{ __('messages.withdrawal.online_function') }}</p>
            <h2 class="mt-3 text-3xl font-black sm:text-4xl">{{ __('messages.withdrawal.withdraw_here') }}</h2>
            <p class="mt-3 max-w-2xl font-semibold leading-7 text-slate-300">Descrivi la tua richiesta di recesso e rimborso. L’assistenza verificherà l’acquisto e le condizioni applicabili e ti ricontatterà via email.</p>
            <p class="mt-3 text-sm leading-6 text-slate-300">Per richiedere il non rinnovo di un abbonamento, <a class="font-bold text-white underline" href="{{ route('subscription.cancel') }}">usa il modulo di disdetta</a>.</p>
        </div>
        <div class="p-5 sm:p-8">
            @include('partials.commerce-support-form', ['source' => 'promo-withdrawal'])
        </div>
    </section>

    <section id="informativa-recesso">
        <div class="mb-6 border-b border-slate-200 pb-5">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-violet-700">{{ __('messages.withdrawal.notice_label') }}</p>
            <h2 class="mt-2 text-3xl font-black text-slate-950">{{ __('messages.withdrawal.notice_title') }}</h2>
        </div>
        <div class="backend-legal-content">{!! $withdrawalInfo->content_snapshot !!}</div>
    </section>

    <section id="refund-policy" class="rounded-[1.5rem] border-2 border-amber-300 bg-amber-50/60 p-5 sm:p-8">
        <div class="mb-6 border-b border-amber-300 pb-5">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-amber-800">{{ __('messages.withdrawal.additional_rules') }}</p>
            <h2 class="mt-2 text-3xl font-black text-slate-950">Refund Policy</h2>
            <p class="mt-3 font-semibold leading-7 text-slate-700">{{ __('messages.withdrawal.additional_text') }}</p>
        </div>
        <div class="backend-legal-content">{!! $refundPolicy->content_snapshot !!}</div>
    </section>
</div>
@endsection
