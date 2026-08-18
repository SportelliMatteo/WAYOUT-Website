<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('messages.email.purchase_subject') }}</title>
</head>
<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;line-height:1.6">
<div style="max-width:600px;margin:0 auto;padding:32px 20px">
    <div style="background:#0f172a;color:#fff;border-radius:20px;padding:28px"><p style="margin:0 0 8px;color:#c4b5fd;font-size:12px;font-weight:bold;letter-spacing:.12em">WAYOUT</p><h1 style="margin:0;font-size:28px">{{ __('messages.email.purchase_subject') }}</h1></div>
    <div style="background:#fff;border-radius:20px;padding:28px;margin-top:16px">
        <p>{{ __('messages.email.hello') }}</p>
        <p>{!! __('messages.email.purchase_confirmed', ['plan' => '<strong>'.e($purchase['plan_name']).'</strong>']) !!}</p>
        <p>{{ __('messages.email.amount') }} <strong>{{ number_format($purchase['amount'] / 100, 2, ',', '.') }} {{ strtoupper($purchase['currency']) }}</strong></p>
        @if (! empty($purchase['order_reference']))
            <p>Numero d’ordine: <strong>{{ $purchase['order_reference'] }}</strong></p>
        @endif
        <p>{!! __('messages.email.waitlist_email', ['email' => '<strong>'.e($purchase['email']).'</strong>']) !!}</p>
        <p>{{ __('messages.email.purchase_summary') }}</p>
        @if (($purchase['attachment_kind'] ?? null) === 'order_summary')
            <p style="border-left:4px solid #7c3aed;background:#f5f3ff;padding:12px 14px">{{ __('messages.email.order_summary_attached') }}</p>
        @elseif (! empty($purchase['invoice_requested']))
            @if (($purchase['invoice_status'] ?? null) === 'test_created')
                <p style="border-left:4px solid #f59e0b;background:#fffbeb;padding:12px 14px">{{ __('messages.email.invoice_test_created') }}</p>
            @elseif (($purchase['invoice_status'] ?? null) === 'sent')
                <p style="border-left:4px solid #16a34a;background:#f0fdf4;padding:12px 14px">{{ __('messages.email.invoice_submitted') }}</p>
            @elseif (($purchase['invoice_status'] ?? null) === 'failed')
                <p style="border-left:4px solid #dc2626;background:#fef2f2;padding:12px 14px">{{ __('messages.email.invoice_failed') }}</p>
            @else
                <p style="border-left:4px solid #64748b;background:#f8fafc;padding:12px 14px">{{ __('messages.email.invoice_processing') }}</p>
            @endif
            @if (($purchase['attachment_kind'] ?? null) === 'courtesy_invoice')
                <p>{{ __('messages.email.courtesy_invoice_attached') }}</p>
            @endif
        @endif
        <p><a href="{{ route('legal.sales') }}" target="_blank" style="color:#6d28d9;font-weight:bold">{{ __('messages.email.sales_terms_link') }}</a> · <a href="{{ route('legal.presale') }}" target="_blank" style="color:#6d28d9;font-weight:bold">{{ __('messages.legal.presale') }}</a> · <a href="{{ route('legal.refunds') }}" target="_blank" style="color:#6d28d9;font-weight:bold">{{ __('messages.email.refunds_link') }}</a> · <a href="{{ route('legal.refunds') }}#recedere" target="_blank" style="color:#6d28d9;font-weight:bold">Recedere dal contratto qui</a></p>
        <p>{{ __('messages.email.thanks') }}</p>
        @include('emails.partials.footer', ['reason' => __('messages.email.purchase_reason', ['email' => $purchase['email']])])
    </div>
</div>
</body>
</html>
