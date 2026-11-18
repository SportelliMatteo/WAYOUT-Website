# Richiesta di cancellazione account

Il modulo pubblico `/cancellazione-account` apre un ticket usando
`POST /api/v1/internal/support/tickets`, firma HMAC interna e nessun JWT.
Categoria `CANCELLAZIONE_ACCOUNT`, priorità `NORMAL`, `context.source = account-deletion`.
Non richiede acquisti o login e non rivela se l’email corrisponde a un account.
Verifica dell’identità e cancellazione effettiva restano all’assistenza/backend.

Il modulo mostra l’avviso sulla perdita di partecipazioni agli eventi,
richieste di partecipazione e creazioni effettuate. La checkbox di presa
visione è obbligatoria anche lato server. Il messaggio inviato al backend
riporta l’avviso confermato, la data della conferma e il riferimento locale.
Questo testo di interfaccia non sostituisce né modifica i documenti legali
pubblicati dal backend.

Il numero di telefono è obbligatorio e viene inserito nel campo `message`
del ticket come `Telefono di contatto: …`. Non si aggiungono campi al payload API
né si memorizza il numero nella tabella locale delle richieste.

CSRF, honeypot, reCAPTCHA (`account_deletion`) e throttle per IP proteggono
l’invio. Un UUID associato al modulo e alla sessione evita invii duplicati.
Quando si riapre il modulo dopo un ticket confermato, viene generato un nuovo
UUID, anche se il messaggio di successo è già scaduto: un nuovo invio non
riutilizza il ticket precedente. I log distinguono le conferme del backend dai
doppi invii e riportano l’host backend per le nuove conferme, senza email o telefono.
`account_deletion_requests` conserva riferimento, hash HMAC dell’email,
data della conferma, hash dell’avviso, stato e UUID del ticket.
Nome ed email non vengono memorizzati in chiaro nella nuova tabella.

Prima del rilascio eseguire la migrazione
`2026_11_18_000002_create_account_deletion_requests_table.php`
con il normale runner di deploy.

Gli stati `sending` e `uncertain` bloccano il reinvio automatico in caso di
esito incerto. La riconciliazione segue la procedura descritta in
[purchase-support-tickets.md](purchase-support-tickets.md), usando questa
tabella e il riferimento locale riportato nel messaggio del ticket.
Un rifiuto 4xx lascia lo stato `failed` e consente un nuovo tentativo.

`AccountDeletionTest` usa risposte simulate: verifica avviso e conferma,
payload e firma HMAC, idempotenza, captcha, throttle e gestione errori.
Non apre ticket reali e non cancella account.
