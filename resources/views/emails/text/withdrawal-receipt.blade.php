WAYOUT — Ricevuta della dichiarazione di recesso

Codice ricevuta: {{ $withdrawal->receipt_number }}
Data e ora: {{ $withdrawal->submitted_at->timezone(config('app.display_timezone'))->format('d/m/Y H:i:s') }} (Europe/Rome)
Intestatario: {{ $withdrawal->first_name }} {{ $withdrawal->last_name }}
Contratto/ordine: {{ $withdrawal->order_reference }}

Dichiarazione inviata:
{{ $withdrawal->declaration }}

Ricevuta online: {{ route('withdrawal.receipt', ['token' => $publicToken]) }}

WAYOUT S.R.L. - C.F./P.IVA 14805930964 - {{ config('email.support_address') }}
