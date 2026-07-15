@extends('layouts.app', [
    'title' => __('messages.consent.marketing_title').' - WAYOUT',
    'description' => __('messages.consent.marketing_description'),
])

@section('content')
<section class="wayout-shell py-16 sm:py-24">
    <div class="mx-auto max-w-2xl rounded-[2rem] bg-white p-6 text-center shadow-xl sm:p-10">
        <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-700">WAYOUT</p>
        <h1 class="mt-3 text-3xl font-black text-slate-950">{{ __('messages.consent.marketing_title') }}</h1>
        <p class="mt-4 font-semibold leading-7 text-slate-600">{{ __('messages.consent.marketing_description', ['email' => $entry->email]) }}</p>

        @if($revoked ?? false)
            <p class="mt-6 rounded-2xl bg-emerald-50 p-4 font-bold text-emerald-800">{{ __('messages.consent.marketing_revoked') }}</p>
        @elseif(($alreadyRevoked ?? false) || ! $entry->marketing_consent)
            <p class="mt-6 rounded-2xl bg-slate-100 p-4 font-bold text-slate-700">{{ __('messages.consent.marketing_already_revoked') }}</p>
        @else
            <form method="POST" action="{{ request()->fullUrl() }}" class="mt-7">
                @csrf
                <button class="rounded-full bg-slate-950 px-7 py-4 font-black text-white">{{ __('messages.consent.marketing_revoke_button') }}</button>
            </form>
        @endif

        <a href="{{ route('home') }}" class="mt-6 inline-block font-black text-violet-700">{{ __('messages.checkout_success.back_home') }}</a>
    </div>
</section>
@endsection
