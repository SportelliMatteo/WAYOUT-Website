# Meta Conversions API

L'integrazione usa lo stesso `ANALYTICS_META_PIXEL_ID` del Pixel già funzionante. Non servono un secondo Pixel o un gateway esterno. Sono inclusi `Lead` (email waitlist verificata) e `Purchase` (ordine confermato dal backend applicativo). Gli altri eventi restano sul Pixel.

## Attivazione sul server

1. Distribuire codice e build frontend, eseguire `php artisan migrate --force`. La migrazione aggiunge la coda degli eventi, il contesto degli acquisti e aggiorna Privacy/Cookie policy IT/EN mantenendo le versioni precedenti e gli altri contenuti correnti.
2. In Gestione eventi Meta selezionare il Pixel/dataset esistente e ottenere il token per l'integrazione diretta della Conversions API. Inserirlo soltanto nel `.env` privato del server:

   ```dotenv
   META_CAPI_ENABLED=true
   META_CAPI_ACCESS_TOKEN=token_generato_in_meta
   META_CAPI_API_VERSION=v23.0
   META_CAPI_TEST_EVENT_CODE=codice_della_sezione_test_eventi
   ```

   `ANALYTICS_ENABLED` e l'ID Pixel devono restare configurati. La versione Graph API è configurabile. Non usare variabili `VITE_*` per il token e non inserirlo nel frontend o nei log.
3. Ricostruire la configurazione (`php artisan config:cache`). L'attivazione CAPI aggiunge automaticamente 1 alla versione base del consenso: il banner richiede una nuova scelta anche ai visitatori che avevano già accettato il vecchio tracciamento. Non serve cambiare manualmente `ANALYTICS_CONSENT_VERSION` per questa attivazione.
4. Verificare il cron: `meta:send-conversions` è registrato nello scheduler Laravel e nel runner Aruba esistente. Su Aruba viene eseguito a ogni invocazione del cron; non richiede un worker persistente. Il comando può essere eseguito manualmente con `php artisan meta:send-conversions`.
5. Con un browser di test, accettare Marketing e completare una nuova verifica email o un acquisto di test. Controllare in Meta gli eventi Browser/Server, la corrispondenza di `event_id`, valore/valuta e la deduplicazione. Il test locale usa risposte HTTP simulate e non certifica la ricezione reale del Pixel/dataset.
6. Terminata la verifica, svuotare `META_CAPI_TEST_EVENT_CODE` e rigenerare la configurazione. Verificare la ricezione anche nella vista ordinaria di Gestione eventi.

Non attivare il codice test su traffico reale durante una verifica prolungata: gli eventi inviati con il codice appartengono al flusso di test.

## Eventi e consenso

- Lead nasce nella verifica effettiva dell'email, non nell'invio del modulo. Usa un identificativo derivato dall'UUID della registrazione, uguale a quello inviato dal Pixel. I tentativi ripetuti non generano nuove conversioni.
- Purchase viene accodato solo per ordini `succeeded`. Il consenso e gli identificativi del browser vengono acquisiti all'avvio del checkout; l'evento usa l'importo dell'ordine in unità monetarie e la valuta, non importi ricevuti dal browser.
- Il consenso Marketing del banner è distinto da quello per le email promozionali. Sono necessari un cookie coerente e la corrispondente registrazione nel registro dei consensi. Consensi precedenti all'attivazione, rifiutati o scaduti non autorizzano invii.
- Prima di ogni tentativo viene ricontrollata l'ultima scelta registrata. I payload revocati vengono scartati. L'email viene normalizzata e trasformata con SHA-256; gli identificativi Meta non vengono sottoposti a hashing. Gli URL di origine non contengono firme, token della verifica o identificativi dell'ordine.
- Payload e contesto sono cifrati a riposo. Il payload viene eliminato dopo la consegna; il cron elimina gli eventi e i contesti più vecchi di sette giorni. I log riportano solo identificativo evento e codice numerico di errore. Per verificare gli errori senza dati personali si possono leggere `event_id`, `attempts`, `last_error_code`, `available_at`, `delivered_at` dalla tabella `meta_conversion_events`.
- I retry mantengono identificativo e ora originali; il lock impedisce invii simultanei dello stesso evento. Timeout o errori Meta non interrompono il checkout né la verifica dell'email.

## Limite della conferma pagamenti esistente

Nel progetto la conferma del backend WAYOUT viene verificata dal controller al ritorno dal checkout e durante il polling della pagina di conferma. Non è presente una notifica server del pagamento completato.

La coda Meta può inviare e ritentare autonomamente gli ordini già confermati, anche senza browser aperto. Non può riconoscere un pagamento che il sito non ha ancora confermato. Per coprire chi chiude il checkout senza tornare, occorre integrare una notifica autenticata dal backend WAYOUT, correlata alla specifica sessione/ordine, oppure un endpoint di riconciliazione equivalente. Non si devono considerare pagati gli ordini basandosi soltanto sul ritorno dal checkout o sull'avvio di una sessione.

## Riferimenti

- [Meta: integrazione Conversions API](https://developers.facebook.com/docs/marketing-api/conversions-api/using-the-api/)
- [Meta: deduplicazione Pixel/server](https://developers.facebook.com/docs/marketing-api/conversions-api/deduplicate-pixel-and-server-events/)
- [Esempi ufficiali Meta](https://github.com/fbsamples/lead-ads-webhook-sample)
