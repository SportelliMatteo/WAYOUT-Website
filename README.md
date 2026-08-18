<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Email transazionali con Brevo

Il progetto invia le email tramite il relay SMTP di Brevo. Configurare in `.env`:

```dotenv
EMAIL_SENDING_ENABLED=false
EMAIL_RESEND_COOLDOWN_SECONDS=300
EMAIL_LOG_CHANNEL=email
EMAIL_LOG_LEVEL=info
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=account-brevo@example.com
MAIL_PASSWORD=chiave-smtp-brevo
MAIL_FROM_ADDRESS=hello@dominio-verificato.example
MAIL_FROM_NAME=WAYOUT
MAIL_REPLY_TO_ADDRESS=amministrazione@wayoutapp.it
MAIL_REPLY_TO_NAME=WAYOUT
CONTACT_EMAIL=hello@wayoutapp.it
```

Usare una chiave SMTP Brevo, non una API key. Il mittente deve essere verificato in Brevo. Con `EMAIL_SENDING_ENABLED=false` nessuna email viene consegnata al mailer. `EMAIL_RESEND_COOLDOWN_SECONDS` stabilisce il tempo minimo tra due richieste manuali per lo stesso acquisto. Dopo modifiche alla configurazione in produzione eseguire `php artisan config:clear` (oppure rigenerare la config cache).

I tentativi di invio sono registrati separatamente in `storage/logs/email-YYYY-MM-DD.log`, senza password o chiavi SMTP. Il log distingue gli eventi `email.skipped`, `email.attempt`, `email.sent` ed `email.failed`.

Il database conserva i timestamp in UTC. Dashboard e nuovi log li presentano in `Europe/Rome`, configurabile con `APP_DISPLAY_TIMEZONE` e `LOG_TIMEZONE`; `APP_TIMEZONE` deve restare `UTC` per evitare timestamp incoerenti e gestire correttamente l’ora legale.

## Audit dei consensi

I consensi sono salvati come eventi append-only in `consent_events`, con data e ora, fonte, azione (`granted`/`revoked`), email del soggetto, IP, user agent, hash della sessione e riferimenti a waitlist, ordine o messaggio di contatto. Le versioni accettate, gli hash SHA-256 e gli URL dei documenti vengono registrati nello stesso evento.

Testi e versioni si gestiscono dalla sezione **Documenti legali** della dashboard admin. Il database è la fonte ufficiale per pagine pubbliche, informative mostrate nei form e audit, mentre gli identificativi di versione non vengono esposti accanto alle checkbox. Ogni pubblicazione richiede un nuovo identificativo di versione, crea una copia HTML immutabile in `legal_document_versions` e aggiorna il puntatore corrente in `legal_documents`. Le versioni già richiamate dagli eventi di consenso non vengono modificate. Nella waitlist la presa visione di Privacy policy e Termini e condizioni viene registrata con l’invio del profilo completo, senza una checkbox separata; marketing, contatti e acquisto mantengono le rispettive checkbox quando previste.

L’editor accetta HTML essenziale e lo sanitizza prima del salvataggio. Script, iframe, form, attributi evento e URL non sicuri vengono rimossi. La lingua modificata è quella selezionata nella dashboard con il selettore IT/EN. I consensi storici non devono essere ricostruiti o retrodatati: gli utenti preesistenti senza evento strutturato devono accettare i testi alla successiva interazione utile.

## Accesso amministrativo con TOTP

La dashboard supporta fino a quattro account amministrativi separati e con gli stessi permessi (`ADMIN_MAX_USERS=4`). Non esistono ruoli Owner o gerarchie. Al primo accesso dopo la migrazione, le credenziali `ADMIN_EMAIL` e `ADMIN_PASSWORD` inizializzano il primo account; la password viene salvata nel database esclusivamente come hash. Dopo questa inizializzazione le credenziali in `.env` non vengono più usate e possono essere rimosse dalla configurazione di produzione. Qualunque amministratore autenticato può creare gli account mancanti fino al limite configurato.

Ogni amministratore deve configurare un’app TOTP compatibile (Google Authenticator, 2FAS, Aegis o equivalente) scansionando il QR generato localmente dal server. Il segreto TOTP è cifrato tramite `APP_KEY`; i recovery code sono salvati solo come hash e quelli mostrati temporaneamente nella sessione sono cifrati. Un codice TOTP già utilizzato non può essere riutilizzato nello stesso intervallo temporale.

Dalla schermata di login, un amministratore che ha dimenticato la password può reimpostarla inserendo email, nuova password e uno dei recovery code salvati. Il codice viene consumato, tutte le sessioni dell’account vengono invalidate e l’Authenticator configurato resta attivo. Nella schermata di verifica OTP, un recovery code può invece sostituire il codice Authenticator e obbliga a configurare nuovamente il TOTP.

Ogni amministratore può modificare autonomamente soltanto la propria password dalla sezione **Configurazione → Cambia la mia password**, inserendo password attuale, nuova password e conferma. La modifica incrementa la versione di autenticazione e invalida tutte le altre sessioni dello stesso account, mantenendo attiva quella utilizzata per il cambio. Nell’audit viene registrata l’operazione, senza password o hash.

La dashboard non permette di resettare, disabilitare o eliminare altri amministratori. Ogni utente può rigenerare il proprio TOTP soltanto conoscendo password e codice corrente. Per il recupero d’emergenza di un singolo account, eseguibile esclusivamente con accesso al server:

```bash
php artisan admin:reset-otp amministratore@example.com
```

Per resettare il TOTP di tutti gli account e invalidare tutte le sessioni amministrative:

```bash
php artisan admin:reset-otp --all
```

Il reset incrementa la versione di autenticazione, invalida le sessioni precedenti e obbliga a scansionare un nuovo QR al login successivo. Accessi, errori, attivazioni e reset vengono registrati senza includere password, segreti TOTP o recovery code.

La tabella append-only `admin_audit_events` conserva uno snapshot di nome ed email dell’operatore, azione, oggetto, valori precedenti e successivi, IP, user agent e timestamp. Sono sottoposte ad audit le modifiche alle capienze waitlist/Founder, la pubblicazione dei documenti legali, la creazione degli account e i reset OTP da console. Lo storico è consultabile in **Configurazione → Attività amministrative**.
