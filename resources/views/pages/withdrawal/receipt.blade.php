@extends('layouts.app', ['title' => __('messages.withdrawal.receipt_meta_title'), 'description' => __('messages.withdrawal.receipt_meta_description'), 'robots' => 'noindex,nofollow,noarchive'])

@section('content')
<section class="py-12 lg:py-20">
    <div class="wayout-shell max-w-3xl">
        <div class="wayout-panel rounded-[1.5rem] p-6 sm:p-10">
            <span class="inline-flex rounded-full bg-emerald-100 px-4 py-2 text-sm font-black text-emerald-800">{{ __('messages.withdrawal.registered') }}</span>
            <h1 class="mt-5 text-4xl font-black text-slate-950">{{ __('messages.withdrawal.receipt_title') }}</h1>
            <p class="mt-4 font-semibold leading-7 text-slate-600">{{ __('messages.withdrawal.receipt_help') }}</p>

            <dl class="mt-8 grid gap-4 rounded-lg border border-slate-200 bg-slate-50 p-5 sm:grid-cols-2">
                <div><dt class="text-xs font-black uppercase text-slate-500">{{ __('messages.withdrawal.receipt_code') }}</dt><dd class="mt-1 font-black">{{ $withdrawal->receipt_number }}</dd></div>
                <div><dt class="text-xs font-black uppercase text-slate-500">{{ __('messages.withdrawal.date_time') }}</dt><dd class="mt-1 font-black">{{ $withdrawal->submitted_at->timezone(config('app.display_timezone'))->format('d/m/Y H:i:s') }}</dd></div>
                <div><dt class="text-xs font-black uppercase text-slate-500">{{ __('messages.withdrawal.holder') }}</dt><dd class="mt-1 font-black">{{ $withdrawal->first_name }} {{ $withdrawal->last_name }}</dd></div>
                <div><dt class="text-xs font-black uppercase text-slate-500">{{ __('messages.withdrawal.contract_order') }}</dt><dd class="mt-1 font-black">{{ $withdrawal->order_reference }}</dd></div>
            </dl>

            <div class="mt-6 rounded-lg border border-violet-200 bg-violet-50 p-5">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-violet-700">{{ __('messages.withdrawal.declaration_received') }}</p>
                <p class="mt-3 font-semibold leading-7 text-slate-800">{{ $withdrawal->declaration }}</p>
            </div>

            <a href="{{ route('withdrawal.receipt.download', ['token' => $token]) }}" class="mt-8 inline-flex rounded-xl bg-slate-950 px-6 py-4 font-black text-white hover:bg-violet-800">{{ __('messages.withdrawal.download_receipt') }}</a>
        </div>
    </div>
</section>
@endsection
