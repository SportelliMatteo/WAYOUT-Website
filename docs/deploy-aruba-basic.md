# Deploy su Aruba Hosting Linux Basic + MySQL

Questa procedura non richiede SSH e non espone comandi di manutenzione sul web. L'applicazione Laravel, `.env`, dipendenze, log e runner restano nella directory privata `_wayout`; nella root del dominio restano soltanto front controller e asset pubblici.

## 1. Preparazione Aruba

1. Attiva Hosting Linux Basic e il database MySQL 8 associato all'hosting.
2. Dal pannello seleziona PHP 8.4 aggiornato (almeno 8.4.1) e abilita `pdo_mysql`, `curl`, `dom`, `fileinfo`, `mbstring` e `openssl`.
3. Attiva il certificato SSL e il reindirizzamento HTTPS.
4. Annota host, database, username e password MySQL forniti da Aruba. Non usare `localhost` se il pannello indica un hostname diverso.
5. Esegui un export MySQL prima di ogni aggiornamento che contiene migrazioni.

Percorso assoluto tipico del dominio:

```text
/web/htdocs/www.wayoutapp.it/home/
```

Se il dominio o il percorso indicato dal pannello differiscono, usa sempre il valore mostrato da Aruba.

## 2. Creazione del pacchetto

Dal computer di sviluppo:

```bash
./scripts/build-aruba-package.sh
```

Il comando esegue build frontend, installazione Composer senza dipendenze di sviluppo e crea in `dist/` lo ZIP della release e il relativo `.sha256`.

Il file ZIP non contiene `.env`, password o chiavi API. Contiene già `vendor/` e gli asset compilati, indispensabili perché Hosting Basic non offre SSH.

## 3. Prima installazione

1. Carica ed estrai il contenuto dello ZIP nella root del dominio tramite File Manager o FTPS. Abilita il caricamento dei file nascosti: `.htaccess` è obbligatorio.
2. Carica separatamente `.env.production` come `/web/htdocs/www.wayoutapp.it/home/_wayout/.env`.
3. Compila almeno `APP_KEY`, credenziali MySQL, Brevo, API benefici, Firebase, backend WAYOUT e admin. `APP_URL` deve essere `https://www.wayoutapp.it`. Imposta `WAYOUT_BASE_URL` e lo stesso segreto HMAC robusto in `WAYOUT_INTERNAL_SECRET` su Laravel e `INTERNAL_API_SECRET` sul backend dell’app. Sul backend autorizza `www.wayoutapp.it` in `CHECKOUT_ALLOWED_HOSTS` e aggiungi soltanto gli host di staging/localhost realmente usati nei test. Le credenziali Stripe restano esclusivamente sul backend dell’app.
4. Lascia vuoto `ARUBA_RELEASE_ID`: il pacchetto contiene un `RELEASE_ID` privato e univoco usato automaticamente.
5. Verifica che `_wayout/storage/` e `_wayout/bootstrap/cache/` siano scrivibili dal processo PHP; non dare permessi `777`.
6. Nel pannello **Processi Cron**, crea temporaneamente un processo di tipo **PHP** verso `/web/htdocs/www.wayoutapp.it/home/_wayout/run-deploy.php`.
7. Impostalo alla prima esecuzione utile, attendi l'esito e controlla `_wayout/storage/logs/`.
8. Elimina il Cron di deploy dopo il successo. Il runner è idempotente: la stessa release non viene installata due volte.
9. Crea il Cron ricorrente, ogni 10 minuti e in UTC, verso `/web/htdocs/www.wayoutapp.it/home/_wayout/run-schedule.php`.

Il runner di deploy esegue migrazioni, pulizia cache, cache configurazione/rotte/view e `app:production-check`. Entrambi i runner rifiutano l'esecuzione HTTP.

## 4. Aggiornamenti successivi

1. Esporta il database MySQL.
2. Crea il nuovo ZIP.
3. Nella root del dominio crea un file vuoto `.maintenance`: Apache risponderà `503 Retry-After`, quindi browser, Stripe e client automatici potranno ritentare.
4. Carica ed estrai il nuovo ZIP sovrascrivendo i file applicativi. Il pacchetto non contiene `.env`, quindi le credenziali rimangono intatte.
5. Esegui una volta il Cron PHP `run-deploy.php` e controlla il risultato.
6. Solo dopo il successo elimina `.maintenance`.

Se il deploy fallisce, lascia `.maintenance`, conserva i log, ripristina i file precedenti e — solo se necessario — il backup MySQL. Le migrazioni applicative non vanno annullate manualmente senza verificare il relativo metodo `down()`.

## 5. Controlli pubblici

Verifica che la home e `/api/docs` rispondano, mentre `/.env`, `/_wayout/`, `/_wayout/.env`, `/vendor/` e `/database/` restituiscano 403/404.

Configura webhook e credenziali Stripe sul backend dell’app. Laravel avvia il checkout tramite gli endpoint interni firmati e conferma l’acquisto interrogando l’entitlement dell’utente; non espone un webhook Stripe.

Riferimenti Aruba: [Hosting Linux](https://hosting.aruba.it/web-hosting/linux/), [Laravel su hosting Aruba](https://guide.aruba.it/hosting-e-domini/hosting/strumenti-consigli-cms/installare-laravel), [Processi Cron](https://guide.aruba.it/hosting-e-domini/hosting/hosting-linux/pannello-controllo-linux/processi-cron).
