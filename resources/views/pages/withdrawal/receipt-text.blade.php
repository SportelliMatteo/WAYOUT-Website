WAYOUT S.R.L.
{{ mb_strtoupper(__('messages.withdrawal.receipt_title')) }}

{{ __('messages.withdrawal.receipt_code') }}: {{ $withdrawal->receipt_number }}
{{ __('messages.withdrawal.date_time') }}: {{ $withdrawal->submitted_at->timezone(config('app.display_timezone'))->format('d/m/Y H:i:s') }} (Europe/Rome)
{{ __('messages.withdrawal.holder') }}: {{ $withdrawal->first_name }} {{ $withdrawal->last_name }}
{{ __('messages.withdrawal.purchase_email_short') }}: {{ $withdrawal->purchase_email }}
{{ __('messages.withdrawal.receipt_email_short') }}: {{ $withdrawal->receipt_email }}
{{ __('messages.withdrawal.contract_order') }}: {{ $withdrawal->order_reference }}
Pass: {{ $withdrawal->plan }}

{{ __('messages.withdrawal.submitted_declaration') }}
{{ $withdrawal->declaration }}

{{ __('messages.withdrawal.generated_document') }}
