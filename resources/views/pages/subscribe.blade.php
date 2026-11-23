@extends('layouts.app', [
    'title' => __('experience.plans_title'),
    'description' => __('experience.plans_description')
])

@section('content')
@php
    $hasPromos = !empty($packages);
    $permissionLabels = __('experience.permission_labels');
    $checkoutProfile = $customerProfile ? [...$customerProfile, 'birth_date' => $customerProfile['date_of_birth'] ?? ''] : [];
    $isAccount = request()->routeIs('customer.dashboard');
    $adultMaxDate = now()->subYears(18)->toDateString();
@endphp
<section class="packages-page">
    <div class="wayout-shell">
        @if($isAccount)
            @include('partials.customer-account')
        @endif
        <header class="packages-heading">
            <p class="experience-kicker">{{ __('experience.plans_label') }}</p>
            <h1>{{ __('experience.plans_headline') }}<br><span>{{ __('experience.plans_highlight') }}</span></h1>
            <p>{{ __('experience.plans_intro') }}</p>
            <a class="mt-4 inline-block font-bold text-violet-700 underline underline-offset-4" href="{{ route('faq', ['lang' => app()->getLocale()]).'#pass-e-abbonamenti' }}">{{ __('experience.faq_link') }} ↗</a>
            <div class="exclusive-tags">@foreach(__('experience.sales_tags') as $tag)<span>✓ {{ $tag }}</span>@endforeach</div>
        </header>
        <div class="offer-switch" role="tablist" aria-label="{{ __('experience.offer_navigation') }}">
            <button type="button" id="promo-tab" role="tab" aria-selected="{{ $hasPromos ? 'true' : 'false' }}" aria-controls="promo-offers" tabindex="{{ $hasPromos ? 0 : -1 }}" data-offer-tab="promo-offers">
                <span>{{ __('experience.promo_short') }}</span><small>{{ __('experience.one_shot') }}</small>
            </button>
            <button type="button" id="subscriptions-tab" role="tab" aria-selected="{{ $hasPromos ? 'false' : 'true' }}" aria-controls="subscription-offers" tabindex="{{ $hasPromos ? -1 : 0 }}" data-offer-tab="subscription-offers">
                <span>{{ __('experience.recurring_short') }}</span><small>{{ __('experience.recurring_badge') }}</small>
            </button>
        </div>
        <div class="offer-comparison">
        <section id="promo-offers" class="offer-section offer-section-promo" role="tabpanel" aria-labelledby="promo-tab" tabindex="0" @if(!$hasPromos) hidden @endif>
            <header class="offer-section-heading">
                <span class="offer-kind">{{ __('experience.one_shot') }}</span>
                <h2 id="promo-title">{{ __('experience.promo_title') }}</h2>
                <p>{{ __('experience.promo_intro') }}</p>
            </header>
        @if(session('checkout_error'))<p class="mt-8 rounded-2xl bg-rose-50 p-4 text-center font-bold text-rose-800" role="alert">{{ session('checkout_error') }}</p>@endif
        @if($catalogError)
            <div class="packages-empty" role="alert"><h2>{{ __('experience.catalog_error') }}</h2><a href="{{ route('subscribe', ['lang' => app()->getLocale()]) }}" class="experience-cta">{{ __('experience.retry') }} ↗</a></div>
        @elseif(empty($packages))
            <div class="packages-empty"><h2>{{ __('experience.empty_title') }}</h2><p>{{ __('experience.empty_text') }}</p></div>
        @else
            <div class="packages-grid" data-package-grid>
                @foreach($packages as $package)
                    @php
                        $soldOut = ($package['available'] ?? null) === 0;
                        $duration = (int) ($package['duration_days'] ?? 0);
                        $canPurchase = !$soldOut && $duration > 0 && ($package['eligibility']['allowed'] ?? true);
                        $inputId = 'package-'.$loop->index;
                        $currency = strtoupper($package['currency'] ?? 'EUR');
                        $price = number_format((float) ($package['price'] ?? 0), 2, app()->getLocale() === 'it' ? ',' : '.', app()->getLocale() === 'it' ? '.' : ',');
                        $permissions = collect(data_get($package, 'tier.permissions', []))->filter(fn ($permission) =>
                            (!empty($permissionLabels[$permission['key'] ?? '']) || !empty($permission['description'])) && (($permission['value_type'] ?? null) === 'BOOL' ? ($permission['value'] ?? false) === true : is_numeric($permission['value'] ?? null) && (int) $permission['value'] !== 0)
                        );
                    @endphp
                    <article class="package-card" data-package-code="{{ $package['code'] }}">
                        <div class="package-badges"><span>{{ ($package['free'] ?? false) ? __('experience.free') : __('experience.one_shot') }}</span>@if($soldOut)<span>{{ __('experience.sold_out') }}</span>@elseif(is_numeric($package['available'] ?? null))<span class="stock-badge">{{ __('experience.remaining', ['count' => $package['available']]) }}</span>@endif</div>
                        <h2>{{ $package['name'] ?? $package['code'] }}</h2>
                        @if(!empty($package['description']))<p class="package-description">{{ $package['description'] }}</p>@endif
                        <p class="package-price">{{ $price }}<small>{{ $currency === 'EUR' ? '€' : $currency }}</small></p>
                        <p class="package-price-note">{{ ($package['free'] ?? false) ? __('experience.free') : __('experience.exclusive_price').' · '.__('experience.one_shot') }}</p>
                        <div class="package-period"><strong>{{ $duration > 0 ? __('experience.duration', ['days' => $duration]) : __('experience.validity_pending') }}</strong><span>{{ __('experience.activation') }}</span></div>
                        @if($permissions->isNotEmpty())
                            <p class="package-benefits-heading">{{ __('experience.included') }}</p>
                            <ul class="package-permissions package-benefits" aria-label="{{ __('experience.included') }}">
                                @foreach($permissions as $permission)
                                    @php
                                        $label = $permissionLabels[$permission['key'] ?? ''] ?? null;
                                        $parts = $label !== null && str_contains($label, ':count') ? explode(':count', $label, 2) : null;
                                    @endphp
                                    <li>
                                        <span class="package-benefit-check" aria-hidden="true">✓</span>
                                        <span class="package-benefit-text">@if($parts){{ $parts[0] }}<strong>{{ $permission['value'] }}</strong>{{ $parts[1] }}@else{{ $label ?? ($permission['description'].(($permission['value_type'] ?? null) === 'INT' ? ' · '.$permission['value'] : '')) }}@endif</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="package-purchase">
                            <p>{{ $duration > 0 ? __('experience.validity', ['days' => $duration]) : __('experience.validity_pending') }}</p>
                            @if(!empty($package['eligibility']['message']))<p class="text-sm text-slate-600">{{ $package['eligibility']['message'] }}</p>@endif
                            <p>✓ {{ __('experience.no_renewal') }}</p>
                            <input id="{{ $inputId }}" class="sr-only" type="radio" name="plan" value="{{ $package['code'] }}" data-free="{{ ($package['free'] ?? false) ? 'true' : 'false' }}" data-price="{{ $package['price'] ?? '0.00' }}" data-currency="{{ $currency }}" tabindex="-1" aria-hidden="true" @disabled(!$canPurchase)>
                            <button type="button" data-package-checkout="{{ $inputId }}" data-summary="{{ ($package['name'] ?? $package['code']).' · '.$price.' '.$currency.' · '.__('experience.duration', ['days' => $duration]) }}" @disabled(!$canPurchase)><span>{{ $soldOut ? __('experience.sold_out') : (($package['free'] ?? false) ? __('experience.activate') : __('experience.buy')) }}</span><span aria-hidden="true">↗</span></button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
        </section>
        <section id="subscription-offers" class="offer-section offer-section-recurring" role="tabpanel" aria-labelledby="subscriptions-tab" tabindex="0" @if($hasPromos) hidden @endif>
            <header class="offer-section-heading">
                <span class="offer-kind">{{ __('experience.recurring_badge') }}</span>
                <h2 id="subscriptions-title">{{ __('experience.recurring_title') }}</h2>
                <p>{{ __('experience.recurring_description') }}</p>
            </header>
            <div id="recurring-catalog-intro" class="recurring-catalog-intro" role="status">
                <p id="recurring-catalog-status">{{ __('experience.loading_plans') }}</p>
            <input id="recurring-catalog" type="radio" name="plan" value="__plans" class="sr-only" tabindex="-1">
            <button type="button" class="experience-cta mt-4 hidden" id="recurring-retry" data-package-checkout="recurring-catalog" data-summary="{{ __('experience.recurring_title') }}">{{ __('experience.retry') }} <span aria-hidden="true">↗</span></button>
            </div>
            <div id="recurring-plans" class="packages-grid recurring-plans-grid" aria-live="polite"></div>
        </section>
        </div>
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
        <p class="mt-4 text-sm text-slate-600">{{ __('messages.subscribe.seller_phone') }} <a class="font-bold text-violet-700" href="tel:+393522164100">+393522164100</a></p>
        <div id="selected-package-summary" class="selected-package-summary" aria-live="polite"></div>
        <form data-legal-unavailable="{{ $legalContentUnavailable ? 'true' : 'false' }}" id="payment-details-form" class="mt-5 space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block sm:col-span-2">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.email') }}</span>
                    <input type="email" name="email" readonly value="{{ $customerProfile['email'] ?? '' }}" required autocomplete="email" maxlength="255" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 text-sm font-bold text-slate-700 outline-none" />
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.first_name') }}</span>
                    <input type="text" name="first_name" readonly value="{{ $checkoutProfile['first_name'] ?? '' }}" required minlength="2" maxlength="120" autocomplete="given-name" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.last_name') }}</span>
                    <input type="text" name="last_name" readonly value="{{ $checkoutProfile['last_name'] ?? '' }}" required minlength="2" maxlength="120" autocomplete="family-name" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.birth_date') }}</span>
                    <input type="date" name="birth_date" readonly value="{{ $checkoutProfile['birth_date'] ?? '' }}" max="{{ $adultMaxDate }}" required autocomplete="bday" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.gender') }}</span>
                    <select name="gender" disabled class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                        <option value="">{{ __('messages.subscribe.gender_placeholder') }}</option>
                        <option value="MALE" @selected(($checkoutProfile['gender'] ?? '') === 'MALE')>{{ __('messages.subscribe.gender_male') }}</option>
                        <option value="FEMALE" @selected(($checkoutProfile['gender'] ?? '') === 'FEMALE')>{{ __('messages.subscribe.gender_female') }}</option>
                        <option value="OTHER" @selected(($checkoutProfile['gender'] ?? '') === 'OTHER')>{{ __('messages.subscribe.gender_other') }}</option>
                    </select>
                </label>
            </div>
            <label id="invoice-option" class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                <input id="invoice-requested" type="checkbox" name="invoice_requested" value="true" class="mt-1 h-5 w-5 rounded border-slate-300 text-violet-700 focus:ring-violet-500" />
                <span>
                    <span class="block text-sm font-black text-slate-950">{{ __('messages.subscribe.invoice_requested') }}</span>
                    <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">{{ $legalEntityInvoiceEnabled ? __('messages.subscribe.invoice_note') : __('messages.subscribe.invoice_note_individual_only') }}</span>
                </span>
            </label>
            <section id="invoice-details" class="hidden space-y-4 rounded-2xl border border-slate-200 p-4 sm:p-5">
                <fieldset>
                    <legend class="mb-3 text-sm font-black text-slate-950">{{ __('messages.subscribe.invoice_holder_type') }}</legend>
                    <div class="grid gap-3 {{ $legalEntityInvoiceEnabled ? 'sm:grid-cols-2' : '' }}">
                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl bg-slate-50 p-3 text-sm font-bold text-slate-800">
                            <input type="radio" name="billing_customer_type" value="individual" checked class="h-5 w-5 border-slate-300 text-violet-700 focus:ring-violet-500" />
                            {{ __('messages.subscribe.individual') }}
                        </label>
                        @if($legalEntityInvoiceEnabled)
                            <label class="flex cursor-pointer items-center gap-3 rounded-2xl bg-slate-50 p-3 text-sm font-bold text-slate-800">
                                <input type="radio" name="billing_customer_type" value="legal_entity" class="h-5 w-5 border-slate-300 text-violet-700 focus:ring-violet-500" />
                                {{ __('messages.subscribe.legal_entity') }}
                            </label>
                        @endif
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

                @if($legalEntityInvoiceEnabled)
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
                @endif
            </section>
            <label id="purchase-terms" class="flex cursor-pointer items-start gap-3 rounded-2xl border border-violet-200 bg-violet-50 p-4">
                <input id="purchase-terms-accepted" type="checkbox" name="purchase_terms_accepted" value="true" required @disabled($legalContentUnavailable) class="mt-1 h-5 w-5 shrink-0 rounded border-slate-300 text-violet-700 focus:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-50" />
                <span class="text-sm font-semibold leading-6 text-slate-700 [&_a]:font-black [&_a]:text-violet-700 [&_a]:underline [&_a]:underline-offset-4">
                    @if($legalContentUnavailable)
                        {{ __('experience.legal_unavailable') }}
                    @else
                        {{ __('experience.purchase_site_documents_acceptance') }}
                        <span>
                            @foreach($checkoutLegalDocuments as $legalDocument)
                                <a href="{{ \App\Support\LegalDocumentService::route('legal.document', ['document' => $legalDocument['code'], 'scope' => 'SITE', 'lang' => app()->getLocale()]) }}" target="_blank" rel="noopener">{{ $legalDocument['name'] }}</a>@if(!$loop->last),@endif
                            @endforeach
                        </span>
                    @endif
                </span>
            </label>
            <label id="recurring-terms" class="hidden flex items-start gap-3 rounded-2xl bg-violet-50 p-4">
                <input id="recurring-terms-accepted" type="checkbox" name="recurring_terms_accepted" class="mt-1 h-5 w-5">
                <span>{{ __('experience.recurring_consent') }}</span>
            </label>
            <div id="purchase-legal-documents" class="space-y-4" aria-live="polite"></div>
            <p id="payment-details-error" role="alert" class="hidden whitespace-pre-line rounded-2xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"></p>
            <button id="payment-details-submit" type="submit" @disabled($legalContentUnavailable) class="mt-6 inline-flex w-full items-center justify-center gap-3 rounded-full wayout-purple px-7 py-4 text-base font-black text-white shadow-[0_18px_40px_rgba(124,35,245,0.32)] transition hover:scale-[1.01] disabled:cursor-wait disabled:opacity-60">
                <span class="payment-submit-text">{{ __('messages.subscribe.confirm_and_pay') }}</span>
                <span class="payment-submit-loader hidden h-5 w-5 animate-spin rounded-full border-2 border-white/35 border-t-white" aria-hidden="true"></span>
            </button>
        </form>
    </div>
</div>

<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
    let button = null;
    let originalButtonText = '';
    let previousFocus = null;
    let submitLabel = @json(__('messages.subscribe.confirm_and_pay'));
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

        paymentSubmit.disabled = @json($legalContentUnavailable) || isLoading;
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
        previousFocus?.focus();
    }

    function renderPlans(data) {
        let catalog = document.getElementById('recurring-plans');
        if (!catalog) {
            catalog = document.createElement('div');
            catalog.id = 'recurring-plans';
            catalog.className = 'packages-grid mt-6';
            document.getElementById('recurring-catalog').parentElement.append(catalog);
        }
        catalog.replaceChildren();
        const periods = @json(__('experience.billing_periods'));
        const permissionLabels = @json($permissionLabels);
        const webPlans = data.plans.filter(plan => typeof plan.external_price_id === 'string' && plan.external_price_id.startsWith('price_'));
        webPlans.forEach((plan, index) => {
            const card = document.createElement('article');
            card.className = 'package-card recurring-plan-card';
            const badges = document.createElement('div'); badges.className = 'package-badges';
            const badge = document.createElement('span'); badge.textContent = @json(__('experience.recurring_badge'));
            badges.append(badge);
            const title = document.createElement('h2');
            title.textContent = plan.name;
            const price = document.createElement('p');
            price.className = 'package-price';
            price.textContent = new Intl.NumberFormat(@json(app()->getLocale()), { style: 'currency', currency: plan.currency }).format(Number(plan.price));
            const period = document.createElement('p'); period.className = 'package-price-note';
            period.textContent = periods[plan.billing_period] || plan.billing_period;
            const renewal = document.createElement('p');
            renewal.textContent = @json(__('experience.recurring_renewal'));
            const permissions = document.createElement('ul');
            permissions.className = 'package-permissions package-benefits';
            permissions.setAttribute('aria-label', @json(__('experience.included')));
            (plan.tier?.permissions || []).forEach((permission) => {
                const label = permissionLabels[permission.key];
                const enabled = permission.value_type === 'BOOL' ? permission.value === true
                    : permission.value != null && Number.isFinite(Number(permission.value)) && Number(permission.value) !== 0;
                if ((!label && !permission.description) || !enabled) return;
                const item = document.createElement('li');
                const check = document.createElement('span');
                check.className = 'package-benefit-check';
                check.setAttribute('aria-hidden', 'true');
                check.textContent = '✓';
                const text = document.createElement('span');
                text.className = 'package-benefit-text';
                if (label && label.includes(':count')) {
                    const [before, after] = label.split(':count');
                    const count = document.createElement('strong');
                    count.textContent = String(permission.value);
                    text.append(before, count, after);
                } else {
                    text.textContent = label || permission.description + (permission.value_type === 'INT' ? ` · ${permission.value}` : '');
                }
                item.append(check, text);
                permissions.append(item);
            });
            const input = document.createElement('input');
            input.type = 'radio'; input.name = 'plan'; input.className = 'sr-only';
            input.id = `recurring-plan-${index}`; input.value = plan.external_price_id;
            input.dataset.price = plan.price; input.dataset.currency = plan.currency;
            const select = document.createElement('button');
            select.type = 'button'; select.className = 'experience-cta mt-4';
            const webAvailable = typeof plan.external_price_id === 'string' && plan.external_price_id.startsWith('price_');
            if (webAvailable) select.dataset.packageCheckout = input.id;
            select.disabled = !webAvailable || plan.eligibility?.allowed === false;
            input.disabled = select.disabled;
            select.dataset.summary = `${plan.name} · ${price.textContent} · ${period.textContent} · ${renewal.textContent}`;
            select.textContent = webAvailable ? @json(__('experience.buy_plan')) : @json(__('experience.app_only_plan'));
            const purchase = document.createElement('div'); purchase.className = 'package-purchase';
            purchase.append(renewal, input, select);
            if (plan.eligibility?.message) {
                const message = document.createElement('p');
                message.className = 'text-sm text-slate-600';
                message.textContent = plan.eligibility.message;
                purchase.append(message);
            }
            const included = document.createElement('p');
            included.className = 'package-benefits-heading';
            included.textContent = @json(__('experience.included'));
            card.append(badges, title, price, period);
            if (permissions.childElementCount) card.append(included, permissions);
            card.append(purchase);
            catalog.append(card);
        });
        document.getElementById('recurring-catalog-intro').classList.toggle('hidden', webPlans.length > 0);
        if (!webPlans.length) {
            catalog.textContent = @json(__('experience.empty_plans'));
            document.getElementById('recurring-catalog-intro').classList.add('hidden');
        }
    }

    const offerTabs = [...document.querySelectorAll('[data-offer-tab]')];
    function selectOfferTab(tab, updateHash = true) {
        offerTabs.forEach((item) => {
            const active = item === tab;
            item.setAttribute('aria-selected', String(active));
            item.tabIndex = active ? 0 : -1;
            document.getElementById(item.dataset.offerTab).hidden = !active;
        });
        if (updateHash) history.replaceState(null, '', `#${tab.dataset.offerTab}`);
    }
    offerTabs.forEach((tab, index) => {
        tab.addEventListener('click', () => selectOfferTab(tab));
        tab.addEventListener('keydown', (event) => {
            let next;
            if (event.key === 'ArrowRight') next = (index + 1) % offerTabs.length;
            if (event.key === 'ArrowLeft') next = (index + offerTabs.length - 1) % offerTabs.length;
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = offerTabs.length - 1;
            if (next === undefined) return;
            event.preventDefault();
            selectOfferTab(offerTabs[next]);
            offerTabs[next].focus();
        });
    });
    function syncOfferHash() {
        const tab = offerTabs.find((item) => `#${item.dataset.offerTab}` === location.hash);
        if (tab) selectOfferTab(tab, false);
    }
    syncOfferHash();
    window.addEventListener('hashchange', syncOfferHash);

    async function loadPlans() {
        const retry = document.getElementById('recurring-retry');
        const status = document.getElementById('recurring-catalog-status');
        const catalog = document.getElementById('recurring-plans');
        retry.disabled = true;
        retry.classList.add('hidden');
        status.textContent = @json(__('experience.loading_plans'));
        catalog.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch("{{ route($isAccount ? 'customer.plans' : 'subscribe.plans') }}", { headers: { Accept: 'application/json' } });
            const data = await response.json();
            if (response.status === 401 && data.redirectUrl) { window.location.assign(data.redirectUrl); return; }
            if (!response.ok || !Array.isArray(data.plans)) throw new Error('Catalog unavailable');
            renderPlans(data);
        } catch (error) {
            status.textContent = @json(__('experience.plans_load_error'));
            retry.classList.remove('hidden');
        } finally {
            retry.disabled = false;
            catalog.setAttribute('aria-busy', 'false');
        }
    }
    loadPlans();

    document.addEventListener('click', async (event) => {
            const checkoutButton = event.target.closest('[data-package-checkout]');
            if (!checkoutButton) return;
            button = checkoutButton;
            originalButtonText = button.textContent;
            previousFocus = document.activeElement;
            const selectedPlan = document.getElementById(button.dataset.packageCheckout);
            if (!selectedPlan || button.disabled) return;
            if (selectedPlan.value === '__plans') {
                await loadPlans();
                return;
            }
            if (!@json($isAccount)) {
                window.location.assign(@json(session()->has('customer') ? route('customer.dashboard') : route('customer.login')));
                return;
            }
            selectedPlan.checked = true;
            const recurring = selectedPlan.value.startsWith('price_');
            const browsing = selectedPlan.value === '__plans';
            const free = selectedPlan.dataset.free === 'true';
            document.getElementById('purchase-terms').classList.toggle('hidden', browsing);
            document.getElementById('purchase-terms-accepted').required = !browsing;
            document.getElementById('invoice-option').classList.toggle('hidden', browsing || free);
            if (browsing || free) { invoiceCheckbox.checked = false; updateInvoiceFields(); }
            document.getElementById('recurring-terms').classList.toggle('hidden', !recurring);
            document.getElementById('recurring-terms-accepted').required = recurring;
            document.getElementById('recurring-terms-accepted').checked = false;
            submitLabel = selectedPlan.value === '__plans' ? @json(__('experience.recurring_browse'))
                : selectedPlan.dataset.free === 'true' ? @json(__('experience.activate'))
                : @json(__('messages.subscribe.confirm_and_pay'));
            if (paymentSubmitText) paymentSubmitText.textContent = submitLabel;
            document.getElementById('selected-package-summary').textContent = button.dataset.summary;
            paymentError?.classList.add('hidden');
            paymentModal?.classList.remove('hidden');
            paymentModal?.classList.add('flex');
            document.body.style.overflow = 'hidden';
            document.getElementById('payment-details-close')?.focus();
    });
    paymentModal?.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closePaymentModal();
        if (event.key !== 'Tab') return;
        const focusable = [...paymentModal.querySelectorAll('button:not(:disabled), input:not([type="hidden"]):not(:disabled), select, a[href]')].filter((element) => element.getClientRects().length);
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
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

    function renderPurchaseDocuments(documents) {
        const container = document.getElementById('purchase-legal-documents');
        container.replaceChildren();
        for (const documentData of documents) {
            const section = document.createElement('section');
            section.className = 'rounded-2xl border border-violet-200 bg-violet-50 p-4 space-y-3';
            const details = document.createElement('details');
            const summary = document.createElement('summary');
            summary.className = 'cursor-pointer font-bold underline';
            summary.textContent = documentData.name || documentData.label || documentData.code;
            const content = document.createElement('div');
            content.className = 'max-h-64 overflow-auto whitespace-pre-wrap py-3 text-sm';
            // Render backend HTML as inert text to keep scripts and remote resources out of checkout.
            const parsed = new DOMParser().parseFromString(documentData.content_html || '', 'text/html');
            parsed.querySelectorAll('script, style').forEach(node => node.remove());
            content.textContent = documentData.content_text || parsed.body.textContent || '';
            details.append(summary, content);
            const label = document.createElement('label');
            label.className = 'flex items-start gap-3 text-sm';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'accepted_version_ids[]';
            checkbox.value = documentData.version_id;
            checkbox.required = true;
            checkbox.className = 'mt-1 h-5 w-5 shrink-0';
            const text = document.createElement('span');
            const parts = (documentData.checkbox_label || '{link}').split('{link}');
            parts.forEach((part, index) => {
                text.append(document.createTextNode(part));
                if (index < parts.length - 1) {
                    const link = document.createElement('button');
                    link.type = 'button';
                    link.className = 'font-bold underline text-violet-700';
                    link.textContent = summary.textContent;
                    link.addEventListener('click', (event) => {
                        event.preventDefault();
                        details.open = true;
                        summary.focus();
                    });
                    text.append(link);
                }
            });
            label.append(checkbox, text);
            section.append(details, label);
            container.append(section);
        }
        container.querySelector('input')?.focus();
    }

    paymentForm?.addEventListener('submit', async function (event) {
        event.preventDefault();
        paymentError?.classList.add('hidden');

        if (@json($legalContentUnavailable)) {
            paymentError.textContent = @json(__('experience.legal_unavailable'));
            paymentError.classList.remove('hidden');
            return;
        }

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

        const selectedPlanInput = document.querySelector('input[name="plan"]:checked');
        window.wayoutTrack?.('begin_checkout', {
            plan: selectedPlan,
            value: Number(selectedPlanInput?.dataset.price || 0),
            currency: selectedPlanInput?.dataset.currency || 'EUR',
            invoice_requested: invoiceCheckbox?.checked || false,
        });

        button.disabled = true;
        button.textContent = @json(__('messages.subscribe.loading'));
        setPaymentLoading(true);
        const formData = new FormData(paymentForm);
        const payload = {
            plan: selectedPlan,
            accepted_version_ids: formData.getAll('accepted_version_ids[]'),
            ...(selectedPlan.startsWith('price_') ? { recurring_terms_accepted: formData.get('recurring_terms_accepted') === 'on' } : {}),
            email: formData.get('email'),
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            birth_date: formData.get('birth_date'),
            gender: formData.get('gender'),
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

            if (data.legal_required === true && Array.isArray(data.documents)) {
                renderPurchaseDocuments(data.documents);
                if (response.status === 409) {
                    paymentError.textContent = @json(__('experience.purchase_legal_updated'));
                    paymentError.classList.remove('hidden');
                }
                button.disabled = false;
                button.textContent = originalButtonText;
                setPaymentLoading(false);
                document.getElementById('purchase-legal-documents').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            if (response.status === 401 && data.redirectUrl) {
                window.location.assign(data.redirectUrl);
                return;
            }
            if (!response.ok || data.error) {
                showServerValidationErrors(data);
                button.disabled = false;
                button.textContent = originalButtonText;
                setPaymentLoading(false);
                return;
            }

            if (Array.isArray(data.plans)) {
                renderPlans(data);
                button.disabled = false; button.textContent = originalButtonText;
                setPaymentLoading(false);
                closePaymentModal();
                catalog.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            if (!data.redirectUrl) throw new Error('Missing checkout URL');
            window.location.assign(data.redirectUrl);
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
