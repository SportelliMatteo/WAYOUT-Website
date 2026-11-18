# Contatti e ticket backend

Il form Contatti conserva messaggio e consenso legale nelle tabelle esistenti
e apre un ticket tramite `POST /api/v1/internal/support/tickets`, con firma HMAC
e senza JWT. Mostra il successo soltanto quando il backend conferma il ticket.
La checkbox rimanda esclusivamente a Privacy policy e Termini e condizioni,
letti dal backend con scope SITE. I nuovi eventi di consenso registrano soltanto
`privacy` e `terms`, senza richiedere il documento `CONTACT_ACCEPTANCE`.
Se uno dei due documenti non è disponibile, checkbox e invio sono disabilitati.

| Oggetto | Categoria backend |
| --- | --- |
| Club e venue (`club`) | `CLUB_E_VENUE` |
| Collaborazioni (`collaboration`) | `COLLABORAZIONI` |
| Curiosità e informazioni (`information`) | `CURIOSITA_E_INFORMAZIONI` |
| Altro (`other`) | `ALTRO` |

Il titolo è l’etichetta dell’oggetto. Il messaggio riporta il testo dell’utente
e il riferimento locale della richiesta. `context.source` è `contact`,
`page_url` è la pagina Contatti senza query string; il contesto include IP,
user agent e `locale = it-IT`. Priorità `NORMAL` impostata dal server.

L’UUID del modulo è associato alla sessione e coincide con l’ID in
`contact_messages` e `purchase_support_requests`. Il doppio invio riutilizza
il ticket confermato senza duplicare messaggio o consenso. Un invio incerto
non viene ripetuto automaticamente; messaggio e consensi restano conservati.
La notifica email preesistente resta attiva se configurata, dopo la conferma
del ticket. CSRF, reCAPTCHA, honeypot e throttle restano applicati.

Non sono necessarie nuove migrazioni oltre a quelle già previste per i ticket.
