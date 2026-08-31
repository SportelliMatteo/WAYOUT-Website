@if (config('services.recaptcha.enabled'))
    <input
        type="hidden"
        name="g-recaptcha-response"
        value=""
        data-recaptcha-action="{{ $action }}"
        data-recaptcha-site-key="{{ config('services.recaptcha.site_key') }}"
        data-recaptcha-error="{{ __('messages.messages.recaptcha_error') }}"
    >
    @error('recaptcha')
        <p class="{{ $errorClass ?? 'text-sm font-semibold text-rose-500' }}" role="alert">{{ $message }}</p>
    @enderror
@endif
