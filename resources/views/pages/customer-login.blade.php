@extends('layouts.app', ['title' => __('account.login'), 'description' => __('account.login_intro')])
@section('content')
@php
    $checkoutProfile = [];
    $firebaseConfig = config('services.firebase.client');
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
<section class="packages-page"><div class="wayout-shell max-w-2xl">
    <header class="packages-heading"><p class="experience-kicker">WAYOUT</p><h1>{{ __('account.login') }}</h1><p>{{ __('account.login_intro') }}</p></header>
    @if($errors->any())<p class="mb-4 rounded-2xl bg-rose-50 p-4 text-rose-800" role="alert">{{ $errors->first() }}</p>@endif
    <form id="payment-details-form" data-submit-after-phone-verification="true" method="POST" action="{{ route('customer.authenticate') }}" class="space-y-5 rounded-[2rem] bg-white p-6 shadow-lg">
        @csrf
        <input id="payment-firebase-token" type="hidden" name="firebase_token">
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

        <div id="customer-login-loader" class="hidden items-center justify-center gap-3 rounded-2xl bg-violet-50 p-4 text-sm font-bold text-violet-900" role="status" aria-live="polite">
            <span class="h-5 w-5 animate-spin rounded-full border-2 border-violet-200 border-t-violet-700 motion-reduce:animate-none" aria-hidden="true"></span>
            <span>{{ __('account.logging_in') }}</span>
        </div>
    </form>
    <div class="mt-6 rounded-2xl bg-violet-50 p-5">
        <p class="mb-4 font-semibold text-slate-700">{{ __('account.app_account_help') }}</p>
        @include('partials.app-downloads')
    </div>
</div></section>
<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
    window.wayoutFirebaseConfig = {{ Illuminate\Support\Js::from($firebaseBrowserConfig) }};
    window.wayoutPhoneVerificationMessages = {{ Illuminate\Support\Js::from($phoneVerificationMessages) }};
</script>
@endsection
