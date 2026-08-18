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
            <p class="mt-3 max-w-4xl text-sm font-semibold leading-6 text-slate-600">{{ __('messages.subscribe.faq_intro') }}</p>
            <p class="mt-3 max-w-4xl rounded-2xl border border-violet-200 bg-violet-50 p-4 text-sm font-bold leading-6 text-violet-950">{{ __('messages.subscribe.faq_precedence') }}</p>
            <div class="mt-8 space-y-10">
                @foreach(collect(__('messages.subscribe.faqs'))->groupBy('category') as $category => $faqs)
                    <section>
                        <h3 class="text-xl font-black text-slate-950">{{ $category }}</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            @foreach($faqs as $faq)
                                <article data-legal-faq class="wayout-card rounded-[1.5rem] p-5">
                                    <h4 class="text-lg font-black text-slate-950">{{ $faq['question'] }}</h4>
                                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">{{ $faq['answer'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </section>
    </div>
</section>

<div id="payment-details-modal" class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto px-4 py-6">
    <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-md" aria-hidden="true"></div>
    <div role="dialog" aria-modal="true" aria-labelledby="payment-details-title" class="relative z-10 my-auto max-h-[calc(100dvh-3rem)] w-full max-w-2xl overflow-y-auto overscroll-contain rounded-[2rem] bg-white p-5 shadow-2xl sm:p-7">
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
                    <input type="text" name="first_name" value="{{ $waitlistProfile['first_name'] ?? '' }}" required minlength="2" maxlength="120" autocomplete="given-name" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.last_name') }}</span>
                    <input type="text" name="last_name" value="{{ $waitlistProfile['last_name'] ?? '' }}" required minlength="2" maxlength="120" autocomplete="family-name" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
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
                        <input type="tel" name="phone_number" value="{{ $waitlistProfile['phone_number'] ?? '' }}" required minlength="5" maxlength="32" pattern="[0-9 .()\-]{5,32}" autocomplete="tel-national" inputmode="tel" class="min-h-12 min-w-0 flex-1 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
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
            <section id="invoice-details" class="hidden space-y-4 rounded-2xl border border-slate-200 p-4 sm:p-5">
                <fieldset>
                    <legend class="mb-3 text-sm font-black text-slate-950">{{ __('messages.subscribe.invoice_holder_type') }}</legend>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl bg-slate-50 p-3 text-sm font-bold text-slate-800">
                            <input type="radio" name="billing_customer_type" value="individual" checked class="h-5 w-5 border-slate-300 text-violet-700 focus:ring-violet-500" />
                            {{ __('messages.subscribe.individual') }}
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl bg-slate-50 p-3 text-sm font-bold text-slate-800">
                            <input type="radio" name="billing_customer_type" value="legal_entity" class="h-5 w-5 border-slate-300 text-violet-700 focus:ring-violet-500" />
                            {{ __('messages.subscribe.legal_entity') }}
                        </label>
                    </div>
                </fieldset>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block sm:col-span-2">
                        <span id="billing-address-label" class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.billing_address') }}</span>
                        <input type="text" name="billing_address" autocomplete="street-address" class="invoice-common-field min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.postal_code') }}</span>
                        <input type="text" name="billing_postal_code" autocomplete="postal-code" maxlength="20" inputmode="numeric" class="invoice-common-field min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold uppercase text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.city') }}</span>
                        <input type="text" name="billing_city" autocomplete="address-level2" class="invoice-common-field min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.province') }}</span>
                        <input type="text" name="billing_province" autocomplete="address-level1" maxlength="8" class="invoice-common-field min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold uppercase text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.country') }}</span>
                        <input type="text" name="billing_country" value="IT" autocomplete="country" maxlength="2" class="invoice-common-field min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold uppercase text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                    </label>
                </div>

                <div id="individual-invoice-fields">
                    <label class="block">
                        <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.fiscal_code') }}</span>
                        <input type="text" name="fiscal_code" minlength="16" maxlength="16" autocomplete="off" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold uppercase text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                    </label>
                </div>

                <div id="legal-entity-invoice-fields" class="hidden grid gap-4 sm:grid-cols-2">
                    <label class="block sm:col-span-2">
                        <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.company_name') }}</span>
                        <input type="text" name="company_name" autocomplete="organization" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.vat_number') }}</span>
                        <input type="text" name="vat_number" maxlength="20" autocomplete="off" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold uppercase text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.sdi_code') }}</span>
                        <input type="text" name="sdi_code" minlength="7" maxlength="7" pattern="[A-Za-z0-9]{7}" autocomplete="off" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold uppercase text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.pec') }}</span>
                        <input type="email" name="pec" autocomplete="email" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                    </label>
                </div>
            </section>
            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-violet-200 bg-violet-50 p-4">
                <input id="purchase-terms-accepted" type="checkbox" name="purchase_terms_accepted" value="true" required class="mt-1 h-5 w-5 shrink-0 rounded border-slate-300 text-violet-700 focus:ring-violet-500" />
                <span class="text-sm font-semibold leading-6 text-slate-700 [&_a]:font-black [&_a]:text-violet-700 [&_a]:underline [&_a]:underline-offset-4">
                    {!! $legalDocumentsHtml['purchase_acceptance'] !!}
                </span>
            </label>
            <p id="payment-details-error" role="alert" class="hidden whitespace-pre-line rounded-2xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"></p>
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
    const invoiceDetails = document.getElementById('invoice-details');
    const individualInvoiceFields = document.getElementById('individual-invoice-fields');
    const legalEntityInvoiceFields = document.getElementById('legal-entity-invoice-fields');
    const billingAddressLabel = document.getElementById('billing-address-label');
    const billingTypeInputs = document.querySelectorAll('input[name="billing_customer_type"]');
    const fiscalCodeInput = invoiceDetails?.querySelector('input[name="fiscal_code"]');
    const originalButtonText = button?.textContent;

    function updateInvoiceFields() {
        const requested = invoiceCheckbox?.checked || false;
        const customerType = document.querySelector('input[name="billing_customer_type"]:checked')?.value || 'individual';
        const isLegalEntity = requested && customerType === 'legal_entity';

        invoiceDetails?.classList.toggle('hidden', !requested);
        individualInvoiceFields?.classList.toggle('hidden', !requested || isLegalEntity);
        legalEntityInvoiceFields?.classList.toggle('hidden', !isLegalEntity);

        invoiceDetails?.querySelectorAll('.invoice-common-field').forEach((input) => input.required = requested);
        invoiceDetails?.querySelectorAll('#individual-invoice-fields input').forEach((input) => input.required = requested && !isLegalEntity);
        invoiceDetails?.querySelectorAll('#legal-entity-invoice-fields input').forEach((input) => {
            input.required = isLegalEntity && input.name !== 'sdi_code';
        });

        if (billingAddressLabel) {
            billingAddressLabel.textContent = isLegalEntity
                ? @json(__('messages.subscribe.registered_office_address'))
                : @json(__('messages.subscribe.billing_address'));
        }
    }

    function setPaymentLoading(isLoading) {
        if (!paymentSubmit) return;

        paymentSubmit.disabled = isLoading;
        paymentSubmitText?.classList.toggle('hidden', isLoading);
        paymentSubmitLoader?.classList.toggle('hidden', !isLoading);
    }

    function normalizeFiscalValue(value) {
        return (value || '').toString().toUpperCase().replace(/[^A-Z0-9]/g, '');
    }

    function isValidItalianVat(value) {
        const vat = normalizeFiscalValue(value).replace(/^IT/, '');
        if (!/^\d{11}$/.test(vat)) return false;

        let sum = 0;
        for (let index = 0; index < 10; index++) {
            const digit = Number(vat[index]);
            if (index % 2 === 0) {
                sum += digit;
            } else {
                const doubled = digit * 2;
                sum += doubled > 9 ? doubled - 9 : doubled;
            }
        }

        return ((10 - (sum % 10)) % 10) === Number(vat[10]);
    }

    function isValidItalianFiscalCode(value) {
        const fiscalCode = normalizeFiscalValue(value);
        if (!/^[A-Z0-9]{16}$/.test(fiscalCode)) return false;

        const oddValues = {
            0: 1, 1: 0, 2: 5, 3: 7, 4: 9, 5: 13, 6: 15, 7: 17, 8: 19, 9: 21,
            A: 1, B: 0, C: 5, D: 7, E: 9, F: 13, G: 15, H: 17, I: 19, J: 21,
            K: 2, L: 4, M: 18, N: 20, O: 11, P: 3, Q: 6, R: 8, S: 12, T: 14,
            U: 16, V: 10, W: 22, X: 25, Y: 24, Z: 23,
        };
        let sum = 0;

        for (let index = 0; index < 15; index++) {
            const character = fiscalCode[index];
            sum += index % 2 === 0 ? oddValues[character] : parseInt(character, 10) >= 0
                ? Number(character)
                : character.charCodeAt(0) - 65;
        }

        return String.fromCharCode((sum % 26) + 65) === fiscalCode[15];
    }

    function validatePaymentFields() {
        updateInvoiceFields();
        paymentForm?.querySelectorAll('input, select').forEach((field) => {
            field.setCustomValidity('');
            field.removeAttribute('aria-invalid');
        });

        if (!invoiceCheckbox?.checked) return paymentForm?.checkValidity() ?? false;

        const country = paymentForm.elements.billing_country?.value?.trim().toUpperCase();
        const customerType = paymentForm.elements.billing_customer_type?.value;
        const postalCode = paymentForm.elements.billing_postal_code;
        const province = paymentForm.elements.billing_province;
        const fiscalCode = paymentForm.elements.fiscal_code;
        const vatNumber = paymentForm.elements.vat_number;

        if (country === 'IT' && !/^\d{5}$/.test(postalCode?.value || '')) {
            postalCode?.setCustomValidity(@json(__('messages.subscribe.invalid_postal_code')));
        }
        if (country === 'IT' && !/^[A-Z]{2}$/i.test(province?.value || '')) {
            province?.setCustomValidity(@json(__('messages.subscribe.invalid_province')));
        }
        if (customerType === 'individual' && !isValidItalianFiscalCode(fiscalCode?.value)) {
            fiscalCode?.setCustomValidity(@json(__('messages.subscribe.invalid_fiscal_code')));
        }
        if (customerType === 'legal_entity' && country === 'IT' && !isValidItalianVat(vatNumber?.value)) {
            vatNumber?.setCustomValidity(@json(__('messages.subscribe.invalid_vat_number')));
        }

        return paymentForm?.checkValidity() ?? false;
    }

    function showServerValidationErrors(data) {
        const messages = Object.values(data.errors || {}).flat().filter(Boolean);
        paymentError.textContent = messages.length ? messages.join('\n') : (data.message || data.error || @json(__('messages.subscribe.checkout_error')));
        paymentError.classList.remove('hidden');

        const firstFieldName = Object.keys(data.errors || {})[0];
        const firstField = firstFieldName ? paymentForm?.elements.namedItem(firstFieldName) : null;
        if (firstField instanceof HTMLElement) {
            firstField.setAttribute('aria-invalid', 'true');
            firstField.focus();
        }
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
    invoiceCheckbox?.addEventListener('change', updateInvoiceFields);
    billingTypeInputs.forEach((input) => input.addEventListener('change', updateInvoiceFields));
    fiscalCodeInput?.addEventListener('input', function () {
        fiscalCodeInput.value = fiscalCodeInput.value.toUpperCase();
    });
    invoiceDetails?.querySelectorAll('input[name="vat_number"], input[name="sdi_code"], input[name="billing_province"], input[name="billing_country"]').forEach((input) => {
        input.addEventListener('input', () => input.value = input.value.toUpperCase());
    });
    updateInvoiceFields();

    paymentForm?.addEventListener('submit', async function (event) {
        event.preventDefault();
        paymentError?.classList.add('hidden');

        if (!validatePaymentFields()) {
            paymentForm.reportValidity();
            paymentError.textContent = @json(__('messages.subscribe.checkout_validation_error'));
            paymentError.classList.remove('hidden');
            return;
        }

        const selectedPlan = document.querySelector('input[name="plan"]:checked')?.value;
        if (!selectedPlan) {
            alert(@json(__('messages.subscribe.no_passes_available')));
            button.disabled = false;
            button.textContent = @json(__('messages.subscribe.passes_sold_out'));
            setPaymentLoading(false);
            return;
        }

        button.disabled = true;
        button.textContent = @json(__('messages.subscribe.loading'));
        setPaymentLoading(true);
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
            billing_customer_type: invoiceCheckbox?.checked ? formData.get('billing_customer_type') : null,
            billing_address: invoiceCheckbox?.checked ? formData.get('billing_address') : null,
            billing_postal_code: invoiceCheckbox?.checked ? formData.get('billing_postal_code') : null,
            billing_city: invoiceCheckbox?.checked ? formData.get('billing_city') : null,
            billing_province: invoiceCheckbox?.checked ? formData.get('billing_province')?.toString().toUpperCase() : null,
            billing_country: invoiceCheckbox?.checked ? formData.get('billing_country')?.toString().toUpperCase() : null,
            fiscal_code: formData.get('fiscal_code')?.toString().toUpperCase() || null,
            company_name: formData.get('company_name') || null,
            vat_number: formData.get('vat_number')?.toString().toUpperCase() || null,
            sdi_code: formData.get('sdi_code')?.toString().toUpperCase() || null,
            pec: formData.get('pec')?.toString().toLowerCase() || null,
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
                showServerValidationErrors(data);
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
