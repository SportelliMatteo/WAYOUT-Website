@extends('layouts.app', [
    'title' => __('messages.waitlist_verify.title'),
    'description' => __('messages.waitlist_verify.description'),
])

@section('content')
<section class="wayout-shell flex min-h-[70vh] items-center justify-center py-12">
    <div class="w-full max-w-xl rounded-[2rem] bg-slate-950 p-7 text-center text-white shadow-2xl sm:p-10">
        @if($verificationValid)
            <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-200">WAYOUT</p>
            <h1 class="mt-4 text-3xl font-black">{{ __('messages.waitlist_verify.confirming_title') }}</h1>
            <p class="mt-4 font-medium leading-7 text-slate-300">{{ __('messages.waitlist_verify.confirming_text') }}</p>
            <form id="waitlist-verification-form" method="POST" action="{{ route('waitlist.verify', ['token' => $token]) }}" class="mt-7">
                @csrf
                <button type="submit" class="inline-flex min-h-14 w-full items-center justify-center rounded-full bg-white px-7 font-black text-slate-950">{{ __('messages.waitlist_verify.confirm_button') }}</button>
            </form>
            <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">document.getElementById('waitlist-verification-form')?.requestSubmit();</script>
        @else
            <h1 class="text-3xl font-black">{{ __('messages.waitlist_verify.invalid_title') }}</h1>
            <p class="mt-4 font-medium leading-7 text-slate-300">{{ __('messages.waitlist_verify.invalid_text') }}</p>
            <a href="{{ route('home') }}" class="mt-7 inline-flex min-h-14 w-full items-center justify-center rounded-full bg-white px-7 font-black text-slate-950">{{ __('messages.waitlist_verify.back_home') }}</a>
        @endif
    </div>
</section>
@endsection
