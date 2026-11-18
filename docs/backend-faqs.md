# FAQ del sito dal backend

`/faq` legge `GET /api/v1/faqs/grouped?type=SITE` tramite `WayoutApiClient`.
La richiesta è pubblica: nessuna firma HMAC o autorizzazione JWT.
Invia `Accept-Language` con la lingua del sito.

La risposta attesa è `success: true` e `data` come elenco di sezioni:
ogni sezione contiene `group` (`id`, `name`, oppure `null`) e `faqs`
(`id`, `question`, `answer`). Ordine di gruppi e domande conservato come ricevuto.
Le sezioni vuote sono omesse. Un gruppo nullo viene mostrato senza intestazione;
la navigazione lo indica come “Altre domande”.

Domande e risposte sono renderizzate come testo escapato, mantenendo gli a capo.
Gli anchor dei gruppi usano gli ID backend, senza dipendere dai titoli modificabili.
Non vengono richieste FAQ `APP` e non vengono aggiunti testi o listini locali.
`resources/content/faq.it.json` non è più letto dal controller.

Nessuna pubblicazione restituisce un messaggio di elenco vuoto con HTTP 200.
Errori HTTP o contenuti non validi restituiscono HTTP 503 con invito a riprovare,
senza recuperare vecchie FAQ locali. Nessuna nuova tabella o migrazione.

`BackendFaqTest` verifica richiesta SITE pubblica, ordine, gruppi nulli,
sezioni vuote, indisponibilità, payload malformati ed escaping dei contenuti.
