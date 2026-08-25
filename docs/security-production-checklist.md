# Audit sicurezza e checklist di produzione WAYOUT

Audit tecnico eseguito il 25/08/2026 sul progetto Laravel, includendo rotte web/API, autenticazione amministrativa, waitlist, consensi, Stripe, Qonto, email Brevo, recupero benefici, dipendenze, build e configurazione Aruba.

## Correzioni applicate

- Asset Vite di sviluppo esclusi dal pacchetto di produzione.
- Dipendenze Composer/npm aggiornate e sottoposte ad audit.
- Swagger UI compilato localmente, senza dipendenze CDN a runtime.
- CSP con nonce, anti-clickjacking, `nosniff`, Referrer Policy, Permissions Policy, COOP e HSTS.
- Pagine admin, magic link e ricevute con token impostate `no-store`, `noindex` e `no-referrer`.
- Validazione degli host tramite `TRUSTED_HOSTS` e token limitati già a livello di routing.
- Bootstrap admin senza password in chiaro in produzione.
- Download degli allegati Qonto limitato a HTTPS, host ammessi, nessun redirect e PDF fino a 15 MB.
- Database e query convertiti per MySQL con UUID generati da Laravel.
- Layout Aruba con applicazione privata in `_wayout`, negata via Apache, e sola root pubblica esposta.
- Runner di deploy e scheduler eseguibili soltanto dal Cron PHP, non via HTTP.
- Comando `php artisan app:production-check` bloccante se la configurazione essenziale non è pronta.

## Ambienti

`.env` è l'ambiente locale e non viene versionato. In locale mantenere email su `log`, Qonto e analytics disattivati, CSP/HSTS disattivati e chiavi Stripe test.

`.env.production` è il file locale riservato da copiare manualmente sul server come `_wayout/.env`. Compilare tutti i campi vuoti e non inserirlo mai nel pacchetto, nella root pubblica o in Git.

## Obblighi prima del go-live

1. Attivare Hosting Linux Basic, database MySQL Aruba e PHP 8.4 aggiornato (almeno 8.4.1) con `pdo_mysql`, `curl`, `dom`, `fileinfo`, `mbstring` e `openssl`.
2. Usare l'albero generato dal builder: file pubblici nella root del dominio e applicazione in `_wayout`, protetta anche da `.htaccess`.
3. Generare una nuova `APP_KEY` di produzione. Non cambiarla dopo il go-live senza una procedura di rotazione: cifra TOTP e sessioni.
4. Creare credenziali MySQL dedicate, salvare una copia del DB prima di ogni migrazione e provare almeno un ripristino.
5. Forzare HTTPS dal pannello Aruba e verificarlo prima di lasciare HSTS attivo.
6. Configurare Brevo e verificare SPF, DKIM e DMARC.
7. Configurare Stripe live e il webhook live `POST /stripe/webhook`; non riusare il secret della Stripe CLI.
8. Generare chiavi benefici indipendenti e casuali; condividerle solo con il backend dell'app.
9. Creare l'admin iniziale con password hashata, completare TOTP e poi rimuovere le credenziali bootstrap dal file `.env`.
10. Lasciare Qonto disattivato finché credenziali, IBAN, aliquota e flusso reale non sono stati verificati.
11. Pubblicare Privacy Policy e Cookie Policy aggiornate e versionare i testi legali dalla dashboard.
12. Configurare il Cron PHP `_wayout/run-schedule.php` ogni 10 minuti, in UTC.
13. Monitorare errori 5xx, webhook, email, fatture, login admin, spazio disco e certificato TLS.

## Deploy

La procedura senza SSH, il layout FTP, il Cron di prima installazione e l'aggiornamento protetto sono descritti in [deploy-aruba-basic.md](deploy-aruba-basic.md).

Prima di creare un pacchetto eseguire sempre:

```bash
composer audit --locked
npm audit --audit-level=moderate
./vendor/bin/phpunit
./scripts/build-aruba-package.sh
```

Non eseguire `npm run dev`, `php artisan serve`, Pail o Tinker in produzione.

## Verifiche post-deploy

- Provare home, iscrizione, verifica email, acquisto controllato, webhook Stripe e admin.
- Verificare cookie `Secure`, `HttpOnly`, `SameSite=Lax` e assenza di violazioni CSP.
- Verificare che `/.env`, `/_wayout/`, `/storage/`, `/vendor/` e `/database/` restituiscano 403/404.
- Controllare che `public/hot` e `fonts-manifest.dev.json` non esistano sul server.
- Eseguire uno scan esterno e una prova di ripristino MySQL prima di accettare pagamenti reali.

L'audit del codice non sostituisce un penetration test sul dominio pubblicato; TLS, PHP, permessi, pannello Aruba e database vanno verificati sull'infrastruttura reale.
