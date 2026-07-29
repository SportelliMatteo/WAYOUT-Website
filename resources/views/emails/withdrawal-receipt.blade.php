<!doctype html>
<html lang="it">
<head><meta charset="utf-8"><title>Ricevuta recesso {{ $withdrawal->receipt_number }}</title></head>
<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;line-height:1.6">
<div style="max-width:600px;margin:0 auto;padding:32px 20px">
    <div style="background:#0f172a;color:#fff;border-radius:20px;padding:28px">
        <p style="margin:0 0 8px;color:#c4b5fd;font-size:12px;font-weight:bold;letter-spacing:.12em">WAYOUT</p>
        <h1 style="margin:0;font-size:28px">Ricevuta della dichiarazione di recesso</h1>
    </div>
    <div style="background:#fff;border-radius:20px;padding:28px;margin-top:16px">
        <p>La dichiarazione è stata ricevuta e registrata con il codice <strong>{{ $withdrawal->receipt_number }}</strong>.</p>
        <p><strong>Data e ora:</strong> {{ $withdrawal->submitted_at->timezone(config('app.display_timezone'))->format('d/m/Y H:i:s') }} (Europe/Rome)</p>
        <p><strong>Intestatario:</strong> {{ $withdrawal->first_name }} {{ $withdrawal->last_name }}</p>
        <p><strong>Contratto/ordine:</strong> {{ $withdrawal->order_reference }}</p>
        <p><strong>Dichiarazione inviata:</strong><br>{{ $withdrawal->declaration }}</p>
        <p><a href="{{ route('withdrawal.receipt', ['token' => $publicToken]) }}" style="color:#6d28d9;font-weight:bold">Apri e scarica la ricevuta</a></p>
        @include('emails.partials.footer', ['reason' => 'Ricevi questa email perché hai inviato una dichiarazione di recesso a WAYOUT.'])
    </div>
</div>
</body>
</html>
