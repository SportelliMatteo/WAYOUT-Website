<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>{{ __('messages.email.benefit_code_subject') }}</title></head>
<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;line-height:1.6">
<div style="max-width:600px;margin:0 auto;padding:32px 20px">
    <div style="background:#0f172a;color:#fff;border-radius:20px;padding:28px">
        <p style="margin:0 0 8px;color:#c4b5fd;font-size:12px;font-weight:bold;letter-spacing:.12em">WAYOUT</p>
        <h1 style="margin:0;font-size:28px">{{ __('messages.email.benefit_code_heading') }}</h1>
    </div>
    <div style="background:#fff;border-radius:20px;padding:28px;margin-top:16px">
        <p>{{ __('messages.email.benefit_code_text') }}</p>
        <p style="font-size:34px;font-weight:900;letter-spacing:.25em;margin:24px 0">{{ $code }}</p>
        <p style="font-size:13px;color:#64748b">{{ __('messages.email.benefit_code_expiry', ['minutes' => $expiresInMinutes]) }}</p>
        @include('emails.partials.footer', ['reason' => __('messages.email.benefit_code_reason')])
    </div>
</div>
</body>
</html>
