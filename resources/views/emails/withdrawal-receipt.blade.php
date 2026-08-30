<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><title>{{ __('messages.withdrawal.email_subject', ['receipt' => $withdrawal->receipt_number]) }}</title></head>
<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;line-height:1.6">
<div style="max-width:600px;margin:0 auto;padding:32px 20px">
    <div style="background:#0f172a;color:#fff;border-radius:20px;padding:28px">
        <p style="margin:0 0 8px;color:#c4b5fd;font-size:12px;font-weight:bold;letter-spacing:.12em">WAYOUT</p>
        <h1 style="margin:0;font-size:28px">{{ __('messages.withdrawal.email_heading') }}</h1>
    </div>
    <div style="background:#fff;border-radius:20px;padding:28px;margin-top:16px">
        <p>{!! __('messages.withdrawal.email_received', ['receipt' => '<strong>'.e($withdrawal->receipt_number).'</strong>']) !!}</p>
        <p><strong>{{ __('messages.withdrawal.date_time') }}:</strong> {{ $withdrawal->submitted_at->timezone(config('app.display_timezone'))->format('d/m/Y H:i:s') }} (Europe/Rome)</p>
        <p><strong>{{ __('messages.withdrawal.holder') }}:</strong> {{ $withdrawal->first_name }} {{ $withdrawal->last_name }}</p>
        <p><strong>{{ __('messages.withdrawal.contract_order') }}:</strong> {{ $withdrawal->order_reference }}</p>
        <p><strong>{{ __('messages.withdrawal.submitted_declaration') }}:</strong><br>{{ $withdrawal->declaration }}</p>
        <p><a href="{{ route('withdrawal.receipt', ['token' => $publicToken]) }}" style="color:#6d28d9;font-weight:bold">{{ __('messages.withdrawal.open_download') }}</a></p>
        @include('emails.partials.footer', ['reason' => __('messages.withdrawal.email_reason')])
    </div>
</div>
</body>
</html>
