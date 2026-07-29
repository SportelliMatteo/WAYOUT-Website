WAYOUT S.R.L.
RICEVUTA DELLA DICHIARAZIONE DI RECESSO

Codice ricevuta: {{ $withdrawal->receipt_number }}
Data e ora: {{ $withdrawal->submitted_at->timezone(config('app.display_timezone'))->format('d/m/Y H:i:s') }} (Europe/Rome)
Intestatario: {{ $withdrawal->first_name }} {{ $withdrawal->last_name }}
Email di acquisto: {{ $withdrawal->purchase_email }}
Email ricevuta: {{ $withdrawal->receipt_email }}
Contratto/ordine: {{ $withdrawal->order_reference }}
Pass: {{ $withdrawal->plan }}

DICHIARAZIONE INVIATA
{{ $withdrawal->declaration }}

Documento generato da WAYOUT S.R.L. - C.F./P.IVA 14805930964
