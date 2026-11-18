# Richieste di recesso, rimborso e disdetta

I due moduli pubblici inviano soltanto un ticket tramite
`POST /api/v1/internal/support/tickets`, con firma HMAC interna e nessun JWT.
Non verificano acquisti, account, date, prodotti o rinnovi: la verifica e la
successiva gestione restano al backend e all’assistenza.

- `/recedere-dal-contratto`: richiesta di recesso e rimborso,
  `context.source = promo-withdrawal`.
- `/disdetta-abbonamento`: richiesta di disdetta e non rinnovo,
  `context.source = subscription-non-renewal`.

Entrambi chiedono nome, email di contatto, numero di telefono e messaggio.
Il telefono è obbligatorio e viene aggiunto al campo `message` del ticket come
`Telefono di contatto: …`, senza aggiungere campi al contratto API. Nel messaggio viene
suggerito di indicare, se disponibile, il numero d’ordine. Non è obbligatorio
avere un ordine nel database Laravel. Non viene applicato un filtro automatico
sui 14 giorni e non vengono create nuove dichiarazioni o ricevute locali.
I documenti legali correnti continuano a essere letti dal backend; le ricevute
storiche già registrate restano consultabili agli URL originali.

Il payload contiene soltanto `category`, `title`, `message`, `priority`,
`contact_email`, `contact_name` e `context`, come nell’esempio Swagger fornito.
Categorie `RECESSO_E_RIMBORSO` e `DISDETTA_ABBONAMENTO`, priorità `NORMAL`;
titoli e sorgenti distinguono le richieste.
Il messaggio contiene il testo scritto dall’utente e il riferimento della richiesta
sito. Il contesto aggiunge URL della pagina senza query string, IP, user agent
limitato a 512 caratteri e lingua `it-IT`. Nessun campo di contesto viene preso
direttamente dal browser.

CSRF, honeypot, reCAPTCHA e throttle per IP proteggono i moduli. La conferma
esplicita è obbligatoria. Una risposta `success: true` con `data.id` UUID mostra
la conferma di ricezione; non dichiara che rimborso o disdetta siano già eseguiti.

## Migrazione e duplicati

Prima del rilascio eseguire la migrazione
`2026_11_18_000004_allow_support_requests_without_purchase.php` con il normale
runner di deploy. Mantiene i vecchi record, rende nullable il riferimento
all’acquisto e aggiunge l’hash HMAC dell’email di contatto.

`purchase_support_requests` registra solo i metadati di consegna del ticket:
UUID del modulo, sorgente, hash dell’email, stato e UUID del ticket backend.
I nuovi record non hanno un `purchase_id`. Un UUID associato alla sessione e
un lock impediscono il doppio invio dello stesso modulo. Un nuovo modulo dopo
la conferma può inviare una nuova richiesta.

Rifiuti espliciti 4xx lasciano lo stato `failed`. Timeout, 5xx e risposte senza
UUID lasciano `uncertain`; un’interruzione può lasciare `sending`. In questi
casi non si reinvia automaticamente: l’API documentata non espone una chiave
di idempotenza e il ticket potrebbe già essere stato creato.

Per riconciliare un invio incerto cercare il riferimento del sito nel messaggio
del ticket del backoffice. Se esiste, salvare il suo UUID in `backend_ticket_id`
e impostare `status = sent`. Solo dopo aver verificato che nessun ticket esista,
impostare `status = failed` per consentire un nuovo tentativo manuale.

## Test

`PurchaseSupportTest` verifica firma e payload, invii senza acquisti o numero
d’ordine, messaggio obbligatorio, idempotenza, conferma esplicita, captcha,
throttle ed errori. `WithdrawalTest` verifica pagina, documenti, download e
redirect dei vecchi URL di conferma. I test non aprono ticket reali.
