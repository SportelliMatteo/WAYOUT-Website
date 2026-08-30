WAYOUT — {{ __('messages.withdrawal.email_heading') }}

{{ __('messages.withdrawal.receipt_code') }}: {{ $withdrawal->receipt_number }}
{{ __('messages.withdrawal.date_time') }}: {{ $withdrawal->submitted_at->timezone(config('app.display_timezone'))->format('d/m/Y H:i:s') }} (Europe/Rome)
{{ __('messages.withdrawal.holder') }}: {{ $withdrawal->first_name }} {{ $withdrawal->last_name }}
{{ __('messages.withdrawal.contract_order') }}: {{ $withdrawal->order_reference }}

{{ __('messages.withdrawal.submitted_declaration') }}:
{{ $withdrawal->declaration }}

{{ __('messages.withdrawal.online_receipt') }}: {{ route('withdrawal.receipt', ['token' => $publicToken]) }}

WAYOUT S.R.L. - C.F./P.IVA 14805930964 - {{ config('email.support_address') }}
