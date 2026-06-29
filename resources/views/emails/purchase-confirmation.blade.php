<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('messages.email.purchase_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <h1 style="font-size: 24px;">{{ __('messages.email.purchase_subject') }}</h1>
    <p>{{ __('messages.email.hello') }}</p>
    <p>{!! __('messages.email.purchase_confirmed', ['plan' => '<strong>'.$purchase['plan_name'].'</strong>']) !!}</p>
    <p>
        {{ __('messages.email.amount') }}
        <strong>{{ number_format($purchase['amount'] / 100, 2, ',', '.') }} {{ strtoupper($purchase['currency']) }}</strong>
    </p>
    <p>{!! __('messages.email.waitlist_email', ['email' => '<strong>'.$purchase['email'].'</strong>']) !!}</p>
    <p>{{ __('messages.email.thanks') }}</p>
</body>
</html>
