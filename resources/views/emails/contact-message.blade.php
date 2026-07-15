<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>{{ __('messages.email.contact_heading') }}</title></head>
<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;line-height:1.6">
<div style="max-width:600px;margin:0 auto;padding:32px 20px">
    <div style="background:#fff;border-radius:20px;padding:28px">
        <h1 style="margin-top:0;font-size:26px">{{ __('messages.email.contact_heading') }}</h1>
        <p><strong>{{ __('messages.email.contact_from') }}:</strong> {{ $contact['name'] }} &lt;{{ $contact['email'] }}&gt;</p>
        <p><strong>{{ __('messages.email.contact_topic') }}:</strong> {{ $contact['subject'] }}</p>
        <div style="white-space:pre-wrap;background:#f8fafc;border-radius:12px;padding:18px">{{ $contact['message'] }}</div>
        <p>{{ __('messages.email.contact_reply_hint') }}</p>
        @include('emails.partials.footer', ['reason' => __('messages.email.contact_reason')])
    </div>
</div>
</body>
</html>
