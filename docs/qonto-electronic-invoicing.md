# Fatturazione elettronica con Qonto

L’app crea la fattura solo dopo che l’acquisto risulta pagato. Il flusso è:

1. creazione del cliente Qonto con i dati di fatturazione;
2. creazione della fattura cliente;
3. registrazione della fattura come pagata;
4. in produzione, richiesta automatica di trasmissione allo SdI tramite `report_einvoicing`.
5. recupero del PDF prodotto da Qonto e allegato come copia di cortesia all’email di conferma, quando disponibile.

Se Qonto non risponde, il pagamento resta valido. Nella riga `purchases` lo stato passa a `failed` e l’errore viene conservato in `qonto_invoice_error`.

La dashboard amministrativa mostra, per ogni ordine con fattura, lo stato interno, il numero Qonto, lo stato della fattura, lo stato SdI, l’ultimo aggiornamento e l’eventuale errore completo. Da lì è possibile ritentare una fattura fallita oppure richiedere subito una sincronizzazione.

## PDF allegati alle conferme

- Senza richiesta fattura viene generato un `riepilogo-ordine-*.pdf`, marcato in modo evidente come documento non fiscale e privo di numerazione da fattura.
- Con richiesta fattura l’app non genera una seconda fattura locale: recupera la rappresentazione PDF prodotta da Qonto. Il documento fiscalmente valido resta l’XML trasmesso attraverso lo SdI.
- Poiché Qonto genera il PDF in modo asincrono, l’app attende e interroga l’API per alcuni secondi. Se il PDF non è ancora disponibile, l’email di conferma pagamento viene comunque inviata senza allegare una falsa fattura; un successivo reinvio della conferma tenterà nuovamente il recupero.
- In Sandbox il PDF e la fattura sono esclusivamente di test e non vengono trasmessi allo SdI.

## Prima configurazione

1. Attiva la fatturazione elettronica italiana nell’app Qonto. Qonto richiede che sia già attiva per poter creare fatture di un’organizzazione italiana.
2. Accedi al [Qonto Developer Portal](https://developers.qonto.com/) e prepara l’ambiente Sandbox.
3. Nell’app Qonto Sandbox apri **Integrations and Partnerships > API key** e recupera login e secret key.
4. Dal Developer Portal recupera anche lo staging token.
5. Inserisci le variabili riportate sotto esclusivamente nel file privato `_wayout/.env` dell'hosting Aruba.

## Modalità test (Sandbox)

```dotenv
QONTO_INVOICING_ENABLED=true
QONTO_ENVIRONMENT=sandbox
QONTO_BASE_URL=
QONTO_AUTH_METHOD=api_key
QONTO_LOGIN=...
QONTO_SECRET_KEY=...
QONTO_ACCESS_TOKEN=
QONTO_STAGING_TOKEN=...
QONTO_INVOICE_IBAN=...
QONTO_INVOICE_VAT_RATE=0.22
```

La Sandbox permette di verificare creazione cliente, creazione fattura e marcatura come pagata, ma Qonto non collega la Sandbox alla rete di fatturazione elettronica: nessuna fattura di test viene realmente inviata allo SdI. Nel database lo stato risultante è `test_created`.

## Modalità produzione

1. Verifica nell’app Qonto che la numerazione automatica delle fatture sia attiva; in caso contrario l’API richiede un numero esplicito.
2. Verifica IBAN e aliquota IVA con il commercialista. Il prezzo restituito dal catalogo WAYOUT è trattato come importo lordo e l’imponibile viene ricavato usando `QONTO_INVOICE_VAT_RATE`.
3. Sostituisci le credenziali Sandbox con quelle dell’organizzazione Qonto di produzione.
4. Imposta:

```dotenv
QONTO_INVOICING_ENABLED=true
QONTO_ENVIRONMENT=production
QONTO_BASE_URL=
QONTO_AUTH_METHOD=api_key
QONTO_LOGIN=...
QONTO_SECRET_KEY=...
QONTO_ACCESS_TOKEN=
QONTO_STAGING_TOKEN=
QONTO_INVOICE_IBAN=...
QONTO_INVOICE_VAT_RATE=0.22
```

5. Dopo ogni modifica alle variabili, esegui una volta il Cron PHP `_wayout/run-deploy.php`, che rigenera in sicurezza la cache Laravel.

In produzione la creazione usa `report_einvoicing=true`: per un’organizzazione italiana abilitata Qonto inoltra automaticamente l’XML allo SdI. La consegna è asincrona; lo stato `sent` nel database indica che Qonto ha accettato la creazione, non l’esito finale dello SdI.

## OAuth opzionale

Per OAuth imposta `QONTO_AUTH_METHOD=bearer` e valorizza `QONTO_ACCESS_TOKEN`. Il token OAuth ha scadenza e richiede quindi un processo di refresh; per automatizzare soltanto il proprio account Qonto, l’API key statica è la configurazione più semplice.

## Ritentare una fattura fallita

```bash
php artisan qonto:send-invoice UUID_ACQUISTO
```

Il comando accetta solo acquisti pagati con richiesta fattura e non duplica fatture già completate.

## Monitoraggio Qonto e SdI

La sincronizzazione manuale di tutte le fatture è disponibile con:

```bash
php artisan qonto:sync-invoices
```

Per un solo acquisto:

```bash
php artisan qonto:sync-invoices UUID_ACQUISTO
```

Laravel pianifica automaticamente il comando ogni dieci minuti. Nel pannello Aruba configura un processo Cron di tipo PHP, ogni dieci minuti, verso il percorso assoluto `/web/htdocs/www.wayoutapp.it/home/_wayout/run-schedule.php`. Gli orari del pannello Cron sono UTC.

In Sandbox lo stato serve a verificare il flusso applicativo, ma non rappresenta una consegna reale allo SdI. In produzione la dashboard espone gli stati restituiti da Qonto e gli eventi del ciclo di vita disponibili dall’API.

Documentazione ufficiale: [autenticazione API key](https://docs.qonto.com/get-started/business-api/authentication/api-key), [URL Sandbox e produzione](https://docs.qonto.com/get-started/business-api/urls), [creazione cliente](https://docs.qonto.com/api-reference/business-api/clients/create-a-client), [creazione fattura](https://docs.qonto.com/api-reference/business-api/expense-management/client-quotes-notes/client-invoices/create-a-client-invoice).
