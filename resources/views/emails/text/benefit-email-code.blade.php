{{ __('messages.email.benefit_code_heading') }}

{{ __('messages.email.benefit_code_text') }}

{{ $code }}

{{ __('messages.email.benefit_code_expiry', ['minutes' => $expiresInMinutes]) }}
