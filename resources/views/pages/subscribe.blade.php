@extends('layouts.app', [
    'title' => __('experience.plans_title'),
    'description' => __('experience.plans_description')
])

@section('content')
@php
    $checkoutProfile = session('checkout_profile', []);
    $adultMaxDate = now()->subYears(18)->toDateString();
    $phonePrefixes = [
        '+39' => 'IT +39',
        '+33' => 'FR +33',
        '+34' => 'ES +34',
        '+49' => 'DE +49',
        '+44' => 'UK +44',
        '+1' => 'US +1',
    ];
    $firebaseBrowserConfig = [
        'apiKey' => $firebaseConfig['api_key'] ?? null,
        'authDomain' => $firebaseConfig['auth_domain'] ?? null,
        'projectId' => config('services.firebase.project_id'),
        'appId' => $firebaseConfig['app_id'] ?? null,
        'measurementId' => $firebaseConfig['measurement_id'] ?? null,
    ];
    $phoneVerificationMessages = [
        'configurationError' => __('messages.subscribe.phone_configuration_error'),
        'invalidPhone' => __('messages.subscribe.phone_invalid'),
        'sending' => __('messages.subscribe.phone_sending'),
        'codeSent' => __('messages.subscribe.phone_code_sent'),
        'resendCode' => __('messages.subscribe.phone_resend_code'),
        'resendCountdown' => __('messages.subscribe.phone_resend_countdown'),
        'sendError' => __('messages.subscribe.phone_send_error'),
        'verifying' => __('messages.subscribe.phone_verifying'),
        'verified' => __('messages.subscribe.phone_verified'),
        'codeError' => __('messages.subscribe.phone_code_error'),
    ];
@endphp
<section class="packages-page">
    <div class="wayout-shell">
        <header class="packages-heading">
            <p class="experience-kicker">{{ __('experience.plans_label') }}</p>
            <h1>{{ __('experience.plans_headline') }}<br><span>{{ __('experience.plans_highlight') }}</span></h1>
            <p>{{ __('experience.plans_intro') }}</p>
            <div class="exclusive-tags">@foreach(__('experience.exclusive_tags') as $tag)<span>✓ {{ $tag }}</span>@endforeach</div>
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
                        $canPurchase = !$soldOut && $duration > 0;
                        $inputId = 'package-'.$loop->index;
                        $currency = strtoupper($package['currency'] ?? 'EUR');
                        $price = number_format((float) $package['price'], 2, app()->getLocale() === 'it' ? ',' : '.', app()->getLocale() === 'it' ? '.' : ',');
                        $permissions = collect(data_get($package, 'tier.permissions', []))->filter(fn ($permission) =>
                            !empty($permission['description']) && (($permission['value_type'] ?? null) === 'BOOL' ? ($permission['value'] ?? false) === true : is_numeric($permission['value'] ?? null) && (int) $permission['value'] !== 0)
                        );
                    @endphp
                    <article class="package-card" data-package-code="{{ $package['code'] }}">
                        <div class="package-badges"><span>{{ __('experience.one_shot') }}</span>@if($soldOut)<span>{{ __('experience.sold_out') }}</span>@elseif(is_numeric($package['available'] ?? null))<span class="stock-badge">{{ __('experience.remaining', ['count' => $package['available']]) }}</span>@endif</div>
                        <h2>{{ $package['name'] ?? $package['code'] }}</h2>
                        @if(!empty($package['description']))<p class="package-description">{{ $package['description'] }}</p>@endif
                        <p class="package-price">{{ $price }}<small>{{ $currency === 'EUR' ? '€' : $currency }}</small></p>
                        <p class="package-price-note">{{ __('experience.exclusive_price') }} · {{ __('experience.one_shot') }}</p>
                        <div class="package-period"><strong>{{ $duration > 0 ? __('experience.duration', ['days' => $duration]) : __('experience.validity_pending') }}</strong><span>{{ __('experience.activation') }}</span></div>
                        @if($permissions->isNotEmpty())
                            <ul class="package-permissions" aria-label="{{ __('experience.included') }}">
                                @foreach($permissions as $permission)<li><b aria-hidden="true">✓</b><span>{{ $permission['description'] }}@if(($permission['value_type'] ?? null) === 'INT' && (int) $permission['value'] > 0) · {{ $permission['value'] }}@endif</span></li>@endforeach
                            </ul>
                        @endif
                        <div class="package-purchase">
                            <p>{{ $duration > 0 ? __('experience.validity', ['days' => $duration]) : __('experience.validity_pending') }}</p>
                            <p>✓ {{ __('experience.no_renewal') }}</p>
                            <input id="{{ $inputId }}" class="sr-only" type="radio" name="plan" value="{{ $package['code'] }}" data-price="{{ $package['price'] }}" data-currency="{{ $currency }}" tabindex="-1" aria-hidden="true" @disabled(!$canPurchase)>
                            <button type="button" data-package-checkout="{{ $inputId }}" data-summary="{{ ($package['name'] ?? $package['code']).' · '.$price.' '.$currency.' · '.__('experience.duration', ['days' => $duration]) }}" @disabled(!$canPurchase)><span>{{ $soldOut ? __('experience.sold_out') : __('experience.buy') }}</span><span aria-hidden="true">↗</span></button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
        <p class="packages-difference">{{ __('experience.plans_difference') }}</p>
        <section class="packages-faq"><h2>{{ __('experience.faq_title') }}</h2>@foreach(__('experience.faqs') as $faq)<details><summary>{{ $faq['question'] }}</summary><p>{{ $faq['answer'] }}</p></details>@endforeach</section>
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
        <form id="payment-details-form" class="mt-5 space-y-4">
            <input id="payment-firebase-token" type="hidden" name="firebase_token" value="" />
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block sm:col-span-2">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.email') }}</span>
                    <input type="email" name="email" value="{{ old('email', session('purchase_confirmation_email')) }}" required autocomplete="email" maxlength="255" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 text-sm font-bold text-slate-700 outline-none" />
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.first_name') }}</span>
                    <input type="text" name="first_name" value="{{ $checkoutProfile['first_name'] ?? '' }}" required minlength="2" maxlength="120" autocomplete="given-name" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.last_name') }}</span>
                    <input type="text" name="last_name" value="{{ $checkoutProfile['last_name'] ?? '' }}" required minlength="2" maxlength="120" autocomplete="family-name" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.birth_date') }}</span>
                    <input type="date" name="birth_date" value="{{ $checkoutProfile['birth_date'] ?? '' }}" max="{{ $adultMaxDate }}" required autocomplete="bday" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-black text-slate-950">{{ __('messages.subscribe.gender') }}</span>
                    <select name="gender" required class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                        <option value="">{{ __('messages.subscribe.gender_placeholder') }}</option>
                        <option value="MALE" @selected(($checkoutProfile['gender'] ?? '') === 'MALE')>{{ __('messages.subscribe.gender_male') }}</option>
                        <option value="FEMALE" @selected(($checkoutProfile['gender'] ?? '') === 'FEMALE')>{{ __('messages.subscribe.gender_female') }}</option>
                        <option value="OTHER" @selected(($checkoutProfile['gender'] ?? '') === 'OTHER')>{{ __('messages.subscribe.gender_other') }}</option>
                    </select>
                </label>
            </div>
            <section class="space-y-3 rounded-2xl border border-violet-200 bg-violet-50 p-4 sm:p-5">
                <div>
                    <p class="text-sm font-black text-slate-950">{{ __('messages.subscribe.phone_verification_title') }}</p>
                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-600">{{ __('messages.subscribe.phone_verification_text') }}</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-[7rem_1fr_auto]">
                    <select id="payment-phone-prefix" name="phone_prefix" required autocomplete="tel-country-code" class="min-h-12 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                        @foreach($phonePrefixes as $prefix => $label)
                            <option value="{{ $prefix }}" @selected(($checkoutProfile['phone_prefix'] ?? '+39') === $prefix)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input id="payment-phone-number" type="tel" name="phone_number" value="" required minlength="5" maxlength="32" autocomplete="tel-national" inputmode="tel" placeholder="333 1234567" class="min-h-12 min-w-0 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                    <button id="payment-phone-send" type="button" data-resend-seconds="{{ max(1, (int) config('services.firebase.sms_resend_cooldown_seconds', 60)) }}" class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-slate-950 px-5 text-sm font-black text-white transition hover:bg-violet-800 disabled:cursor-wait disabled:opacity-60">{{ __('messages.subscribe.phone_send_code') }}</button>
                </div>
                <div id="payment-phone-code-panel" class="hidden grid gap-3 sm:grid-cols-[1fr_auto]">
                    <input id="payment-phone-code" type="text" inputmode="numeric" autocomplete="one-time-code" minlength="6" maxlength="6" placeholder="000000" class="min-h-12 rounded-2xl border border-slate-200 bg-white px-4 text-center text-lg font-black tracking-[0.35em] text-slate-950 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-100" />
                    <button id="payment-phone-verify" type="button" class="inline-flex min-h-12 items-center justify-center rounded-2xl wayout-purple px-5 text-sm font-black text-white disabled:cursor-wait disabled:opacity-60">{{ __('messages.subscribe.phone_verify_code') }}</button>
                </div>
                <div id="payment-phone-recaptcha"></div>
                <p id="payment-phone-status" class="hidden rounded-2xl px-4 py-3 text-sm font-bold" role="status" aria-live="polite"></p>
            </section>
            <label class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
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
            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-violet-200 bg-violet-50 p-4">
                <input id="purchase-terms-accepted" type="checkbox" name="purchase_terms_accepted" value="true" required class="mt-1 h-5 w-5 shrink-0 rounded border-slate-300 text-violet-700 focus:ring-violet-500" />
                <span class="text-sm font-semibold leading-6 text-slate-700 [&_a]:font-black [&_a]:text-violet-700 [&_a]:underline [&_a]:underline-offset-4">
                    {!! $legalDocumentsHtml['purchase_acceptance'] !!}
                </span>
            </label>
            <p id="payment-details-error" role="alert" class="hidden whitespace-pre-line rounded-2xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"></p>
            <button id="payment-details-submit" type="submit" disabled class="mt-6 inline-flex w-full items-center justify-center gap-3 rounded-full wayout-purple px-7 py-4 text-base font-black text-white shadow-[0_18px_40px_rgba(124,35,245,0.32)] transition hover:scale-[1.01] disabled:cursor-wait disabled:opacity-60">
                <span class="payment-submit-text">{{ __('messages.subscribe.confirm_and_pay') }}</span>
                <span class="payment-submit-loader hidden h-5 w-5 animate-spin rounded-full border-2 border-white/35 border-t-white" aria-hidden="true"></span>
            </button>
        </form>
    </div>
</div>

<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
    window.wayoutFirebaseConfig = {{ Illuminate\Support\Js::from($firebaseBrowserConfig) }};
    window.wayoutPhoneVerificationMessages = {{ Illuminate\Support\Js::from($phoneVerificationMessages) }};
    let button = null;
    let originalButtonText = '';
    let previousFocus = null;
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

        paymentSubmit.disabled = isLoading || !document.getElementById('payment-firebase-token')?.value;
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

    document.querySelectorAll('[data-package-checkout]').forEach((checkoutButton) => {
        checkoutButton.addEventListener('click', () => {
            button = checkoutButton;
            originalButtonText = button.textContent;
            previousFocus = document.activeElement;
            const selectedPlan = document.getElementById(button.dataset.packageCheckout);
            if (!selectedPlan || button.disabled) return;
            selectedPlan.checked = true;
            document.getElementById('selected-package-summary').textContent = button.dataset.summary;
            paymentError?.classList.add('hidden');
            paymentModal?.classList.remove('hidden');
            paymentModal?.classList.add('flex');
            document.body.style.overflow = 'hidden';
            document.getElementById('payment-details-close')?.focus();
        });
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
            email: formData.get('email'),
            firebase_token: formData.get('firebase_token'),
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

            if (!response.ok || data.error) {
                showServerValidationErrors(data);
                button.disabled = false;
                button.textContent = originalButtonText;
                setPaymentLoading(false);
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
