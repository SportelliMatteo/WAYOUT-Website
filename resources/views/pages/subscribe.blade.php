@extends('layouts.app', [
    'title' => __('messages.subscribe.title'),
    'description' => __('messages.subscribe.description')
])

@section('content')
@php
    $capacity = fn (string $key) => number_format(($founderCapacities[$key] ?? config('founder.default_capacities')[$key]), 0, ',', '.');
    $joinFull = $founderAvailability['join']['is_full'] ?? false;
    $creatorFull = $founderAvailability['creator']['is_full'] ?? false;
    $allPassesFull = $joinFull && $creatorFull;
    $defaultPlan = $joinFull && ! $creatorFull ? 'creator' : 'join';
@endphp
<section class="relative overflow-hidden py-10 lg:py-20">
    <div class="mb-20 wayout-shell grid gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-start">
        <div>
            <span class="inline-flex rounded-full border border-violet-200 bg-white/80 px-4 py-2 text-sm font-black uppercase tracking-[0.22em] text-violet-700">
                {{ __('messages.subscribe.eyebrow') }}
            </span>
            <h1 class="mt-6 max-w-4xl text-4xl font-black leading-[0.98] text-slate-950 sm:text-6xl">
                {{ __('messages.subscribe.headline') }} <span class="wayout-gradient-text">{{ __('messages.subscribe.headline_highlight') }}</span> {{ __('messages.subscribe.headline_tail') }}
            </h1>
            <p class="mt-5 max-w-2xl text-base font-medium leading-7 text-slate-600 sm:mt-6 sm:text-lg sm:leading-8">
                {{ __('messages.subscribe.intro', ['count' => $capacity('waitlist_capacity')]) }}
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="wayout-card rounded-[2rem] p-6">
                    <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-700">{{ __('messages.subscribe.free_trial') }}</p>
                    <p class="mt-3 text-4xl font-black text-slate-950">60 {{ __('messages.home.days') }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-500">{{ __('messages.subscribe.trial_active') }}</p>
                </div>
                <div class="wayout-card rounded-[2rem] p-6">
                    <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-700">{{ __('messages.subscribe.accesses') }}</p>
                    <p class="mt-3 text-4xl font-black text-slate-950">{{ $capacity('waitlist_capacity') }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-500">{{ __('messages.subscribe.reserved_first') }}</p>
                </div>
            </div>

            <div class="mt-6 wayout-panel rounded-[2rem] p-5 sm:rounded-[2.5rem] sm:p-6">
                <p class="text-sm font-black uppercase tracking-[0.22em] text-slate-500">{{ __('messages.subscribe.choose_plan') }}</p>
                @if($allPassesFull)
                    <p class="mt-4 rounded-2xl bg-rose-50 px-4 py-3 text-sm font-black text-rose-700">
                        {{ __('messages.subscribe.passes_sold_out_message') }}
                    </p>
                @endif
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="{{ $joinFull ? 'cursor-not-allowed opacity-55' : 'cursor-pointer hover:border-violet-300 has-[:checked]:border-violet-600 has-[:checked]:shadow-[0_18px_45px_rgba(124,35,245,0.15)]' }} rounded-[2rem] border border-slate-200 bg-white p-5 transition">
                        <input type="radio" name="plan" value="join" @checked($defaultPlan === 'join' && ! $joinFull) @disabled($joinFull) class="sr-only" />
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-lg font-black text-slate-950">{{ __('messages.subscribe.join_name') }}</p>
                                <p class="mt-2 text-sm font-semibold text-slate-500">{{ $joinFull ? __('messages.subscribe.pass_sold_out') : __('messages.subscribe.join_text') }}</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-black text-slate-950">29€</span>
                        </div>
                        <p class="mt-4 text-sm font-black text-violet-700">{{ $joinFull ? __('messages.subscribe.sold_out') : __('messages.subscribe.spots_available', ['count' => $capacity('join_capacity')]) }}</p>
                    </label>
                    <label class="{{ $creatorFull ? 'cursor-not-allowed opacity-55' : 'cursor-pointer hover:border-violet-300 has-[:checked]:border-violet-600 has-[:checked]:shadow-[0_18px_45px_rgba(124,35,245,0.15)]' }} rounded-[2rem] border border-slate-200 bg-white p-5 transition">
                        <input type="radio" name="plan" value="creator" @checked($defaultPlan === 'creator' && ! $creatorFull) @disabled($creatorFull) class="sr-only" />
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-lg font-black text-slate-950">{{ __('messages.subscribe.creator_name') }}</p>
                                <p class="mt-2 text-sm font-semibold text-slate-500">{{ $creatorFull ? __('messages.subscribe.pass_sold_out') : __('messages.subscribe.creator_text') }}</p>
                            </div>
                            <span class="rounded-full wayout-purple px-3 py-1 text-sm font-black text-white">59€</span>
                        </div>
                        <p class="mt-4 text-sm font-black text-violet-700">{{ $creatorFull ? __('messages.subscribe.sold_out') : __('messages.subscribe.spots_available', ['count' => $capacity('creator_capacity')]) }}</p>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-4 sm:flex-row">
                <button id="stripe-checkout-button" @disabled($allPassesFull) class="inline-flex w-full items-center justify-center rounded-full px-7 py-4 text-lg font-black text-white shadow-[0_18px_40px_rgba(124,35,245,0.32)] transition {{ $allPassesFull ? 'cursor-not-allowed bg-slate-400' : 'wayout-purple hover:scale-[1.01]' }}">
                    {{ $allPassesFull ? __('messages.subscribe.passes_sold_out') : __('messages.subscribe.checkout') }}
                </button>
                <a href="{{ route('home') }}" class="inline-flex w-full items-center justify-center rounded-full border border-slate-200 bg-white px-7 py-4 text-lg font-black text-slate-950 shadow-sm transition hover:border-violet-300">
                    {{ __('messages.subscribe.back_home') }}
                </a>
            </div>
        </div>

        <aside class="rounded-[2rem] bg-slate-950 p-5 text-white shadow-[0_30px_100px_rgba(15,23,42,0.22)] sm:rounded-[2.5rem] sm:p-8 lg:sticky lg:top-32">
            <div class="rounded-[2rem] bg-[radial-gradient(circle_at_20%_0%,rgba(124,35,245,0.65),transparent_20rem)] p-1">
                <div class="rounded-[1.75rem] bg-slate-950/85 p-6">
                    <p class="text-sm font-black uppercase tracking-[0.26em] text-violet-200">Founder Pass</p>
                    <p class="mt-3 text-3xl font-black sm:text-4xl">{{ __('messages.subscribe.presale') }}</p>
                    <div class="mt-6 space-y-4">
                        <div class="rounded-3xl bg-white/[0.08] p-5">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-black">Join</p>
                                <p class="text-3xl font-black">29€</p>
                            </div>
                            <p class="mt-2 text-sm text-slate-300">{{ $joinFull ? __('messages.subscribe.sold_out').'.' : __('messages.subscribe.spots_available', ['count' => $capacity('join_capacity')]).'.' }}</p>
                        </div>
                        <div class="rounded-3xl bg-white p-5 text-slate-950">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-black">Creator</p>
                                <p class="text-3xl font-black">59€</p>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-slate-500">{{ $creatorFull ? __('messages.subscribe.sold_out').'.' : __('messages.subscribe.spots_available', ['count' => $capacity('creator_capacity')]).'.' }}</p>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
                            <p class="font-black">{{ __('messages.subscribe.duration') }}</p>
                            <p class="mt-2 text-sm text-slate-300">{{ __('messages.subscribe.reserved_price') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
        </aside>
    </div>
</section>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const button = document.getElementById('stripe-checkout-button');
    button?.addEventListener('click', async function () {
        button.disabled = true;
        button.textContent = @json(__('messages.subscribe.loading'));

        const selectedPlan = document.querySelector('input[name="plan"]:checked')?.value;
        if (!selectedPlan) {
            alert(@json(__('messages.subscribe.no_passes_available')));
            button.disabled = false;
            button.textContent = @json(__('messages.subscribe.passes_sold_out'));
            return;
        }
        const stripeKey = @json(config('services.stripe.key'));

        try {
            const response = await fetch("{{ route('subscribe.checkout') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ plan: selectedPlan }),
            });

            const data = await response.json();

            if (!response.ok || data.error) {
                alert(data.error || @json(__('messages.subscribe.checkout_error')));
                button.disabled = false;
                button.textContent = @json(__('messages.subscribe.checkout'));
                return;
            }

            if (data.completed) {
                window.location.href = data.redirectUrl || '{{ route('checkout.success') }}';
                return;
            }

            if (!stripeKey) {
                alert(@json(__('messages.subscribe.stripe_key_missing')));
                button.disabled = false;
                button.textContent = @json(__('messages.subscribe.checkout'));
                return;
            }

            const stripe = Stripe(stripeKey);
            const result = await stripe.redirectToCheckout({ sessionId: data.sessionId });

            if (result.error) {
                alert(result.error.message);
                button.disabled = false;
                button.textContent = @json(__('messages.subscribe.checkout'));
            }
        } catch (error) {
            alert(@json(__('messages.subscribe.checkout_retry')));
            button.disabled = false;
            button.textContent = @json(__('messages.subscribe.checkout'));
        }
    });
</script>
@endsection
