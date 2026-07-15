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
    $waitlistProfile = session('waitlist_profile', []);
    $adultMaxDate = now()->subYears(18)->toDateString();
    $phonePrefixes = [
        '+39' => 'IT +39',
        '+33' => 'FR +33',
        '+34' => 'ES +34',
        '+49' => 'DE +49',
        '+44' => 'UK +44',
        '+1' => 'US +1',
    ];
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
                        @foreach(__('messages.subscribe.compare_rows') as $rowIndex => $row)
                            @php($isCapacityRow = $rowIndex === 7)
                            <tr>
                                <td class="py-3 pr-4 font-black text-slate-950">{{ $row[0] }}</td>
                                <td class="px-4 py-3 text-right">{{ $isCapacityRow ? __('messages.subscribe.spots_available', ['count' => $capacity('waitlist_capacity')]) : $row[1] }}</td>
                                <td class="px-4 py-3 text-right">{{ $isCapacityRow ? __('messages.subscribe.spots_available', ['count' => $capacity('join_capacity')]) : $row[2] }}</td>
                                <td class="py-3 pl-4 text-right">{{ $isCapacityRow ? __('messages.subscribe.spots_available', ['count' => $capacity('creator_capacity')]) : $row[3] }}</td>
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

<div id="payment-details-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 py-6">
    <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-md" aria-hidden="true"></div>
    <div role="dialog" aria-modal="true" aria-labelledby="payment-details-title" class="relative z-10 w-full max-w-2xl overflow-hidden rounded-[2rem] bg-white p-5 shadow-2xl sm:p-7">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-violet-700">{{ __('messages.subscribe.payment_details_eyebrow') }}</p>
                <h2 id="payment-details-title" class="mt-2 text-2xl font-black leading-tight text-slate-950">{{ __('messages.subscribe.payment_details_title') }}</h2>
            </div>
            <button id="payment-details-close" type="button" class="rounded-full bg-slate-100 p-2 text-slate-500 transition hover:bg-slate-200 hover:text-slate-950" aria-label="{{ __('messages.nav.close_menu') }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="payment-details-form" class="mt-5 space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block sm:col-span-2">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.email') }}</span>
                    <input type="email" name="email" value="{{ session('waitlist_email') }}" readonly class="min-h-12 w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 text-sm font-bold text-slate-700 outline-none" />
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.first_name') }}</span>
                    <input type="text" name="first_name" value="{{ $waitlistProfile['first_name'] ?? '' }}" required autocomplete="given-name" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.last_name') }}</span>
                    <input type="text" name="last_name" value="{{ $waitlistProfile['last_name'] ?? '' }}" required autocomplete="family-name" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.birth_date') }}</span>
                    <input type="date" name="birth_date" value="{{ $waitlistProfile['birth_date'] ?? '' }}" max="{{ $adultMaxDate }}" required autocomplete="bday" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.phone_number') }}</span>
                    <div class="flex gap-2">
                        <select name="phone_prefix" required autocomplete="tel-country-code" class="min-h-12 w-28 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                            @foreach($phonePrefixes as $prefix => $label)
                                <option value="{{ $prefix }}" @selected(($waitlistProfile['phone_prefix'] ?? '+39') === $prefix)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="tel" name="phone_number" value="{{ $waitlistProfile['phone_number'] ?? '' }}" required autocomplete="tel-national" inputmode="tel" class="min-h-12 min-w-0 flex-1 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                    </div>
                </label>
            </div>
            <label class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                <input id="invoice-requested" type="checkbox" name="invoice_requested" value="true" class="mt-1 h-5 w-5 rounded border-slate-300 text-violet-700 focus:ring-violet-500" />
                <span>
                    <span class="block text-sm font-black text-slate-950">{{ __('messages.subscribe.invoice_requested') }}</span>
                    <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">{{ __('messages.subscribe.invoice_note') }}</span>
                </span>
            </label>
            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-violet-200 bg-violet-50 p-4">
                <input id="purchase-terms-accepted" type="checkbox" name="purchase_terms_accepted" value="true" required class="mt-1 h-5 w-5 shrink-0 rounded border-slate-300 text-violet-700 focus:ring-violet-500" />
                <span class="text-sm font-semibold leading-6 text-slate-700 [&_a]:font-black [&_a]:text-violet-700 [&_a]:underline [&_a]:underline-offset-4">
                    {!! $legalDocumentsHtml['purchase_acceptance'] !!}
                </span>
            </label>
            <label id="fiscal-code-wrap" class="mb-4 hidden">
                <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.fiscal_code') }}</span>
                <input type="text" name="fiscal_code" maxlength="32" autocomplete="off" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold uppercase text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
            </label>
            <p id="payment-details-error" class="hidden rounded-2xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"></p>
            <button id="payment-details-submit" type="submit" class="mt-6 inline-flex w-full items-center justify-center gap-3 rounded-full wayout-purple px-7 py-4 text-base font-black text-white shadow-[0_18px_40px_rgba(124,35,245,0.32)] transition hover:scale-[1.01] disabled:cursor-wait disabled:opacity-80">
                <span class="payment-submit-text">{{ __('messages.subscribe.confirm_and_pay') }}</span>
                <span class="payment-submit-loader hidden h-5 w-5 animate-spin rounded-full border-2 border-white/35 border-t-white" aria-hidden="true"></span>
            </button>
        </form>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const button = document.getElementById('stripe-checkout-button');
    const paymentModal = document.getElementById('payment-details-modal');
    const paymentForm = document.getElementById('payment-details-form');
    const paymentError = document.getElementById('payment-details-error');
    const paymentSubmit = document.getElementById('payment-details-submit');
    const paymentSubmitText = paymentSubmit?.querySelector('.payment-submit-text');
    const paymentSubmitLoader = paymentSubmit?.querySelector('.payment-submit-loader');
    const invoiceCheckbox = document.getElementById('invoice-requested');
    const fiscalCodeWrap = document.getElementById('fiscal-code-wrap');
    const fiscalCodeInput = fiscalCodeWrap?.querySelector('input[name="fiscal_code"]');
    const originalButtonText = button?.textContent;

    function setPaymentLoading(isLoading) {
        if (!paymentSubmit) return;

        paymentSubmit.disabled = isLoading;
        paymentSubmitText?.classList.toggle('hidden', isLoading);
        paymentSubmitLoader?.classList.toggle('hidden', !isLoading);
    }

    function closePaymentModal() {
        paymentModal?.classList.add('hidden');
        paymentModal?.classList.remove('flex');
        document.body.style.overflow = '';
    }

    button?.addEventListener('click', function () {
        const selectedPlan = document.querySelector('input[name="plan"]:checked')?.value;
        if (!selectedPlan) {
            alert(@json(__('messages.subscribe.no_passes_available')));
            return;
        }

        paymentError?.classList.add('hidden');
        paymentModal?.classList.remove('hidden');
        paymentModal?.classList.add('flex');
        document.body.style.overflow = 'hidden';
    });

    document.getElementById('payment-details-close')?.addEventListener('click', closePaymentModal);
    invoiceCheckbox?.addEventListener('change', function () {
        fiscalCodeWrap?.classList.toggle('hidden', !invoiceCheckbox.checked);
        if (fiscalCodeInput) {
            fiscalCodeInput.required = invoiceCheckbox.checked;
            if (!invoiceCheckbox.checked) {
                fiscalCodeInput.value = '';
            }
        }
    });
    fiscalCodeInput?.addEventListener('input', function () {
        fiscalCodeInput.value = fiscalCodeInput.value.toUpperCase();
    });

    paymentForm?.addEventListener('submit', async function (event) {
        event.preventDefault();
        button.disabled = true;
        button.textContent = @json(__('messages.subscribe.loading'));
        setPaymentLoading(true);
        paymentError?.classList.add('hidden');

        const selectedPlan = document.querySelector('input[name="plan"]:checked')?.value;
        if (!selectedPlan) {
            alert(@json(__('messages.subscribe.no_passes_available')));
            button.disabled = false;
            button.textContent = @json(__('messages.subscribe.passes_sold_out'));
            setPaymentLoading(false);
            return;
        }
        const stripeKey = @json(config('services.stripe.key'));
        const formData = new FormData(paymentForm);
        const payload = {
            plan: selectedPlan,
            direct_checkout: false,
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            birth_date: formData.get('birth_date'),
            phone_prefix: formData.get('phone_prefix'),
            phone_number: formData.get('phone_number'),
            invoice_requested: invoiceCheckbox?.checked || false,
            purchase_terms_accepted: formData.get('purchase_terms_accepted') === 'true',
            fiscal_code: formData.get('fiscal_code')?.toString().toUpperCase() || null,
        };

        try {
            const response = await fetch("{{ route('subscribe.checkout') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok || data.error) {
                paymentError.textContent = data.message || data.error || @json(__('messages.subscribe.checkout_error'));
                paymentError.classList.remove('hidden');
                button.disabled = false;
                button.textContent = originalButtonText;
                setPaymentLoading(false);
                return;
            }

            if (data.completed) {
                window.location.href = data.redirectUrl || '{{ route('checkout.success') }}';
                return;
            }

            if (!stripeKey) {
                paymentError.textContent = @json(__('messages.subscribe.stripe_key_missing'));
                paymentError.classList.remove('hidden');
                button.disabled = false;
                button.textContent = originalButtonText;
                setPaymentLoading(false);
                return;
            }

            const stripe = Stripe(stripeKey);
            const result = await stripe.redirectToCheckout({ sessionId: data.sessionId });

            if (result.error) {
                paymentError.textContent = result.error.message;
                paymentError.classList.remove('hidden');
                button.disabled = false;
                button.textContent = originalButtonText;
                setPaymentLoading(false);
            }
        } catch (error) {
            paymentError.textContent = @json(__('messages.subscribe.checkout_retry'));
            paymentError.classList.remove('hidden');
            button.disabled = false;
            button.textContent = originalButtonText;
            setPaymentLoading(false);
        }
    });
</script>
@endsection
