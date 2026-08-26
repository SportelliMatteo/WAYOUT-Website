@extends('layouts.app', [
    'title' => __('messages.checkout_success.title'),
    'description' => __('messages.checkout_success.description')
])

@section('content')
@php
    $checkoutPlan = session('checkout_plan') ?? request('plan');
    $isConfirmed = ($checkoutState ?? 'pending') === 'confirmed';
    $isReview = ($checkoutState ?? 'pending') === 'review';
@endphp
@if($isConfirmed && $purchaseAnalytics)
    <span
        class="hidden"
        data-analytics-page-event="purchase"
        data-analytics-params='@json($purchaseAnalytics)'
        data-analytics-dedupe="purchase_{{ $purchaseAnalytics['transaction_id'] }}"
        data-analytics-dedupe-scope="local"
    ></span>
@endif
<section class="py-14 lg:py-24">
    <div class="mx-auto max-w-4xl px-5">
        <div class="relative mt-2 mb-20 overflow-hidden rounded-[2rem] bg-slate-950 p-5 text-white shadow-[0_30px_100px_rgba(15,23,42,0.22)] sm:rounded-[2.5rem] sm:p-10">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_0%,rgba(124,35,245,0.55),transparent_26rem),radial-gradient(circle_at_90%_10%,rgba(185,255,74,0.24),transparent_18rem)]"></div>
            <div class="relative">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white text-3xl font-black text-violet-700">{{ $isConfirmed ? '✓' : ($isReview ? '!' : '…') }}</div>
                <p class="mt-6 text-sm font-black uppercase tracking-[0.26em] text-violet-200">{{ __('messages.checkout_success.'.($isConfirmed ? 'eyebrow' : ($isReview ? 'review_eyebrow' : 'pending_eyebrow'))) }}</p>
                <h1 class="mt-3 text-3xl font-black leading-tight sm:text-5xl">{{ __('messages.checkout_success.'.($isConfirmed ? 'headline' : ($isReview ? 'review_headline' : 'pending_headline'))) }}</h1>
                <p class="mt-5 max-w-2xl text-base leading-7 text-slate-300 sm:text-lg sm:leading-8">
                    {{ __('messages.checkout_success.'.($isConfirmed ? 'text' : ($isReview ? 'review_text' : 'pending_text'))) }}
                </p>
                @if($isConfirmed)
                <div class="mt-6 max-w-2xl rounded-3xl bg-white/[0.08] p-5">
                    <p class="text-base font-bold leading-7 text-slate-100">
                        {{ __('messages.checkout_success.pass_summary') }}
                    </p>
                    @if($checkoutPlan === 'creator')
                        <p class="mt-3 text-sm font-semibold leading-6 text-violet-100">
                            {{ __('messages.checkout_success.creator_summary') }}
                        </p>
                    @endif
                </div>
                <div class="mt-6 max-w-2xl">
                    <p class="text-sm font-black uppercase tracking-[0.2em] text-violet-200">
                        {{ __('messages.checkout_success.documents_title') }}
                    </p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <a href="{{ route('legal.passes') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/[0.08] px-4 py-3 text-center text-sm font-black text-white transition hover:border-violet-300 hover:bg-white/[0.14]">
                            {{ __('messages.legal.passes') }}
                        </a>
                        <a href="{{ route('legal.sales') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/[0.08] px-4 py-3 text-center text-sm font-black text-white transition hover:border-violet-300 hover:bg-white/[0.14]">
                            {{ __('messages.legal.sales') }}
                        </a>
                        <a href="{{ route('legal.presale') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/[0.08] px-4 py-3 text-center text-sm font-black text-white transition hover:border-violet-300 hover:bg-white/[0.14]">
                            {{ __('messages.legal.presale') }}
                        </a>
                        <a href="{{ route('legal.refunds') }}#recedere" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-violet-300/40 bg-violet-400/15 px-4 py-3 text-center text-sm font-black text-violet-100 transition hover:border-violet-200 hover:bg-violet-400/25">
                            {{ __('messages.checkout_success.withdrawal_link') }}
                        </a>
                    </div>
                </div>
                @endif
                <div class="mt-8">
                    <a href="{{ route('home') }}" class="inline-flex w-full items-center justify-center rounded-full bg-white px-7 py-4 text-base font-black text-slate-950 shadow-xl transition hover:scale-[1.02] sm:w-auto sm:text-lg">
                        {{ __('messages.checkout_success.back_home') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@if(!$isConfirmed && !$isReview && !empty($statusUrl))
<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
    (() => {
        const delays = [1000, 3000, 8000];
        const check = async (index) => {
            try {
                const response = await fetch(@json($statusUrl), { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (data.state === 'confirmed' || data.state === 'review') {
                    window.location.reload();
                    return;
                }
            } catch (error) {
                console.error('Wayout purchase confirmation check failed.', error);
            }

            if (index + 1 < delays.length) window.setTimeout(() => check(index + 1), delays[index + 1]);
        };

        window.setTimeout(() => check(0), delays[0]);
    })();
</script>
@endif
@endsection
