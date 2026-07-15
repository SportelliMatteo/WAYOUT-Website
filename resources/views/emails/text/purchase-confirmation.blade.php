{{ __('messages.email.hello') }}

{{ __('messages.email.purchase_confirmed', ['plan' => $purchase['plan_name']]) }}
{{ __('messages.email.amount') }} {{ number_format($purchase['amount'] / 100, 2, ',', '.') }} {{ strtoupper($purchase['currency']) }}
{{ __('messages.email.waitlist_email', ['email' => $purchase['email']]) }}
{{ __('messages.email.purchase_summary') }}

{{ __('messages.email.sales_terms_link') }}: {{ route('legal.sales') }}
{{ __('messages.email.refunds_link') }}: {{ route('legal.refunds') }}

{{ __('messages.email.purchase_reason', ['email' => $purchase['email']]) }}
WAYOUT S.R.L. - C.F./P.IVA 14805930964 - {{ config('email.support_address') }}
{{ __('messages.email.privacy_link') }}: {{ route('legal.privacy') }}
