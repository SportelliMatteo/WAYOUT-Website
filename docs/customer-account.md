# Area riservata e pagamenti

Implementazione allineata alla GUIDA-LARAVEL-PAGAMENTI (1).md e ai contratti Swagger verificati il 20 novembre 2026.

- Accesso con telefono e OTP Firebase riservato agli account già creati nell’app WAYOUT. Nessuna registrazione dal sito. Token backend cifrato nella sessione, identità verificata con `/api/v1/users/me`.
- Stato corrente letto con HMAC da `GET /api/v1/internal/users/{uuid}/subscription`. Nome commerciale da `offer.name`, scadenza e rinnovo dalla sottoscrizione.
- Storico completo letto con il JWT del cliente da `GET /api/v1/me/subscription/history`; un errore dello storico non viene mostrato come elenco vuoto e non impedisce la lettura dello stato corrente.
- Ogni pulsante segue `POST /api/v1/internal/purchase-eligibility`. Nessun limite locale di 3 giorni o blocco ricostruito da source/status: anche promo gratuiti e IN_GRACE seguono il verdetto backend. Il checkout rivalida idoneità e disponibilità nel backend.
- I piani web espongono soltanto provider Stripe attivi e i relativi prezzi. Promo gratuiti tramite `/promo-packages/activate`, a pagamento tramite `/checkout-sessions`.
- Riferimento stabile per ordine; nessuna chiave Stripe o webhook Stripe in Laravel. Il ritorno dal checkout non prova l’acquisto: conferma con letture distanziate dello stato interno.
- “Gestisci abbonamento” appare per source STRIPE e offer.type PLAN, mai per un promo pagato con Stripe. POST protetta da sessione, CSRF e throttle, inoltrata con HMAC a `/internal/billing-portal-sessions`; uuid e return_url sono scelti dal server. Il backend decide se aprire la sessione. Il ritorno rilegge lo stato corrente.
- Cambi piano immediati, downgrade compresi, secondo guida e configurazione del Customer Portal. Il sito informa delle possibili conseguenze sui limiti delle attività.
- Il comando amministrativo di disponibilità piani governa anche login, area riservata, storico e accesso al portale.

## Verifica su staging e produzione

Il team backend configura il Customer Portal con prezzi ricorrenti collegati ai piani, conguaglio always_invoice, cambi immediati, disdetta a fine periodo, carte e fatture. Per provare il cambio piano servono almeno due prezzi ricorrenti. In produzione devono essere autorizzati gli host reali degli URL di ritorno. Gli errori di configurazione o trasporto non vanno confusi con un verdetto commerciale negativo.

La notifica backend → Laravel descritta nella guida resta una proposta e non è stata implementata. Nessun pagamento o cambio piano reale viene eseguito dai test automatici.
