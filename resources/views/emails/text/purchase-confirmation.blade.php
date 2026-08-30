{{ __('messages.email.hello') }}

{{ __('messages.email.purchase_confirmed', ['plan' => $purchase['plan_name']]) }}
{{ __('messages.email.amount') }} {{ number_format($purchase['amount'] / 100, 2, ',', '.') }} {{ strtoupper($purchase['currency']) }}
@if (! empty($purchase['order_reference']))
{{ __('messages.email.order_number') }}: {{ $purchase['order_reference'] }}
@endif
{{ __('messages.email.waitlist_email', ['email' => $purchase['email']]) }}
{{ __('messages.email.purchase_summary') }}
@if (($purchase['attachment_kind'] ?? null) === 'order_summary')
{{ __('messages.email.order_summary_attached') }}
@elseif (! empty($purchase['invoice_requested']))
@if (($purchase['invoice_status'] ?? null) === 'test_created')
{{ __('messages.email.invoice_test_created') }}
@elseif (($purchase['invoice_status'] ?? null) === 'sent')
{{ __('messages.email.invoice_submitted') }}
@elseif (($purchase['invoice_status'] ?? null) === 'failed')
{{ __('messages.email.invoice_failed') }}
@else
{{ __('messages.email.invoice_processing') }}
@endif
@if (($purchase['attachment_kind'] ?? null) === 'courtesy_invoice')
{{ __('messages.email.courtesy_invoice_attached') }}
@endif
@endif

{{ __('messages.email.sales_terms_link') }}: {{ route('legal.sales', ['lang' => app()->getLocale()]) }}
{{ __('messages.legal.presale') }}: {{ route('legal.presale', ['lang' => app()->getLocale()]) }}
{{ __('messages.email.refunds_link') }}: {{ route('legal.refunds', ['lang' => app()->getLocale()]) }}

{{ __('messages.email.purchase_reason', ['email' => $purchase['email']]) }}
WAYOUT S.R.L. - C.F./P.IVA 14805930964 - {{ config('email.support_address') }}
{{ __('messages.email.privacy_link') }}: {{ route('legal.privacy', ['lang' => app()->getLocale()]) }}
