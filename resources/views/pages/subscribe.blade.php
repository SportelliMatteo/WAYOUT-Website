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
                {{ __('messages.subscribe.intro') }}
            </p>
            <p class="mt-3 max-w-2xl text-sm font-black text-violet-700 sm:text-base">
                {{ __('messages.subscribe.presale_microcopy') }}
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="wayout-card rounded-[2rem] p-6">
                    <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-700">{{ __('messages.subscribe.summary_join_title') }}</p>
                </div>
                <div class="wayout-card rounded-[2rem] p-6">
                    <p class="text-sm font-black uppercase tracking-[0.22em] text-violet-700">{{ __('messages.subscribe.summary_creator_title') }}</p>
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
                        <div class="flex h-full flex-col">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-lg font-black text-slate-950">{{ __('messages.subscribe.join_card_title') }}</p>
                                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-500">{{ __('messages.subscribe.join_card_text') }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-black text-slate-950">29€</span>
                                    <p class="mt-2 max-w-[8rem] text-[0.68rem] font-bold leading-4 text-slate-500">{{ __('messages.subscribe.join_card_price_label') }}</p>
                                </div>
                            </div>
                            <p class="mt-4 text-sm font-black text-violet-700">{{ $joinFull ? __('messages.subscribe.sold_out') : __('messages.subscribe.spots_available', ['count' => $capacity('join_capacity')]) }}</p>
                            <div class="mt-5 space-y-4 text-sm leading-6">
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <p class="font-black text-slate-950">{{ __('messages.subscribe.card_capacity') }}</p>
                                    <p class="mt-1 font-black text-slate-950">{{ __('messages.subscribe.join_card_capacity') }}</p>
                                </div>
                                <div>
                                    <p class="font-black text-slate-950">{{ __('messages.subscribe.includes') }}</p>
                                    <ul class="mt-2 space-y-1 font-semibold text-slate-600">
                                        @foreach(__('messages.subscribe.join_includes') as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-black text-slate-950">{{ __('messages.subscribe.validity') }}</p>
                                    <p class="mt-2 font-semibold text-slate-600">{{ __('messages.subscribe.join_card_validity') }}</p>
                                </div>
                                <div>
                                    <p class="font-black text-slate-950">{{ __('messages.subscribe.not_included') }}</p>
                                    <ul class="mt-2 space-y-1 font-semibold text-slate-500">
                                        @foreach(__('messages.subscribe.join_not_included') as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </label>
                    <label class="{{ $creatorFull ? 'cursor-not-allowed opacity-55' : 'cursor-pointer hover:border-violet-300 has-[:checked]:border-violet-600 has-[:checked]:shadow-[0_18px_45px_rgba(124,35,245,0.15)]' }} rounded-[2rem] border border-slate-200 bg-white p-5 transition">
                        <input type="radio" name="plan" value="creator" @checked($defaultPlan === 'creator' && ! $creatorFull) @disabled($creatorFull) class="sr-only" />
                        <div class="flex h-full flex-col">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-lg font-black text-slate-950">{{ __('messages.subscribe.creator_card_title') }}</p>
                                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-500">{{ __('messages.subscribe.creator_card_text') }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span class="inline-flex rounded-full wayout-purple px-3 py-1 text-sm font-black text-white">59€</span>
                                    <p class="mt-2 max-w-[8rem] text-[0.68rem] font-bold leading-4 text-slate-500">{{ __('messages.subscribe.creator_card_price_label') }}</p>
                                </div>
                            </div>
                            <p class="mt-4 text-sm font-black text-violet-700">{{ $creatorFull ? __('messages.subscribe.sold_out') : __('messages.subscribe.spots_available', ['count' => $capacity('creator_capacity')]) }}</p>
                            <div class="mt-5 space-y-4 text-sm leading-6">
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <p class="font-black text-slate-950">{{ __('messages.subscribe.card_capacity') }}</p>
                                    <p class="mt-1 font-black text-slate-950">{{ __('messages.subscribe.creator_card_capacity') }}</p>
                                </div>
                                <div>
                                    <p class="font-black text-slate-950">{{ __('messages.subscribe.includes') }}</p>
                                    <ul class="mt-2 space-y-1 font-semibold text-slate-600">
                                        @foreach(__('messages.subscribe.creator_includes') as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div>
                                    <p class="font-black text-slate-950">{{ __('messages.subscribe.validity') }}</p>
                                    <p class="mt-2 font-semibold text-slate-600">{{ __('messages.subscribe.creator_card_validity') }}</p>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
                <p class="mt-5 rounded-2xl bg-violet-50 p-4 text-sm font-bold leading-6 text-violet-900">
                    {{ __('messages.subscribe.price_note') }}
                </p>
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

    <div class="wayout-shell">
        <section class="wayout-panel overflow-hidden rounded-[2rem] p-5 sm:rounded-[2.5rem] sm:p-8">
            <h2 class="text-3xl font-black leading-tight text-slate-950">{{ __('messages.subscribe.compare_title') }}</h2>
            <div class="mt-6 overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                            <th class="py-3 pr-4">{{ __('messages.subscribe.compare_feature') }}</th>
                            <th class="px-4 py-3 text-right">Waitlist Pass</th>
                            <th class="px-4 py-3 text-right">Founder Join 12M</th>
                            <th class="py-3 pl-4 text-right">Founder Creator 12M</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
                        @foreach(__('messages.subscribe.compare_rows') as $row)
                            <tr>
                                <td class="py-3 pr-4 font-black text-slate-950">{{ $row[0] }}</td>
                                <td class="px-4 py-3 text-right">{{ $row[1] }}</td>
                                <td class="px-4 py-3 text-right">{{ $row[2] }}</td>
                                <td class="py-3 pl-4 text-right">{{ $row[3] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-8">
            <h2 class="text-3xl font-black leading-tight text-slate-950">{{ __('messages.subscribe.faq_title') }}</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach(__('messages.subscribe.faqs') as $faq)
                    <article class="wayout-card rounded-[1.5rem] p-5">
                        <h3 class="text-lg font-black text-slate-950">{{ $faq['question'] }}</h3>
                        <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">{{ $faq['answer'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>
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
