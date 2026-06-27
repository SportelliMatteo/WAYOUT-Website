<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Conferma acquisto Founder Pass WAYOUT</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <h1 style="font-size: 24px;">Conferma acquisto Founder Pass WAYOUT</h1>
    <p>Ciao,</p>
    <p>ti confermiamo che l’acquisto del tuo <strong>{{ $purchase['plan_name'] }}</strong> è stato registrato correttamente.</p>
    <p>
        Importo:
        <strong>{{ number_format($purchase['amount'] / 100, 2, ',', '.') }} {{ strtoupper($purchase['currency']) }}</strong>
    </p>
    <p>La tua email waitlist associata è <strong>{{ $purchase['email'] }}</strong>.</p>
    <p>Grazie per aver scelto WAYOUT.</p>
</body>
</html>
