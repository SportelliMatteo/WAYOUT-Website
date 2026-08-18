<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Riepilogo ordine {{ $purchase->order_reference }}</title>
    <style>
        @page { margin: 34px; }
        body { color: #0f172a; font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.55; }
        .header { background: #111827; border-radius: 14px; color: #fff; padding: 24px; }
        .brand { color: #c4b5fd; font-size: 11px; font-weight: bold; letter-spacing: 2px; }
        h1 { font-size: 25px; margin: 8px 0 0; }
        .warning { background: #fef3c7; border: 2px solid #f59e0b; border-radius: 10px; color: #78350f; font-size: 15px; font-weight: bold; margin: 18px 0; padding: 14px; text-align: center; }
        .card { border: 1px solid #e2e8f0; border-radius: 12px; margin-top: 14px; padding: 18px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 10px 4px; text-align: left; vertical-align: top; }
        th { color: #64748b; font-size: 10px; text-transform: uppercase; width: 34%; }
        .total { font-size: 18px; font-weight: bold; }
        .note { color: #475569; font-size: 10px; margin-top: 20px; }
        .footer { border-top: 1px solid #e2e8f0; color: #64748b; font-size: 9px; margin-top: 28px; padding-top: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">WAYOUT</div>
        <h1>Riepilogo ordine</h1>
    </div>

    <div class="warning">DOCUMENTO NON FISCALE — NON È UNA FATTURA</div>

    <div class="card">
        <table>
            <tr><th>Riferimento ordine</th><td>{{ $purchase->order_reference }}</td></tr>
            <tr><th>Data pagamento</th><td>{{ \Illuminate\Support\Carbon::parse($purchase->created_at)->timezone('Europe/Rome')->format('d/m/Y H:i') }}</td></tr>
            <tr><th>Cliente</th><td>{{ trim(($purchase->first_name ?? '').' '.($purchase->last_name ?? '')) }}</td></tr>
            <tr><th>Email</th><td>{{ $purchase->email }}</td></tr>
            <tr><th>Prodotto</th><td>{{ $planName }}</td></tr>
            <tr><th>Stato pagamento</th><td>Confermato</td></tr>
            <tr><th>Importo pagato</th><td class="total">{{ number_format($purchase->amount / 100, 2, ',', '.') }} {{ strtoupper($purchase->currency) }}</td></tr>
        </table>
    </div>

    <p class="note">
        Questo PDF è esclusivamente un riepilogo dell’ordine e del pagamento. Non costituisce fattura,
        fattura elettronica, documento commerciale o altro documento fiscale e non utilizza una numerazione da fattura.
    </p>

    <div class="footer">
        WAYOUT S.R.L. — C.F./P.IVA 14805930964 — {{ config('email.support_address') }}
    </div>
</body>
</html>
