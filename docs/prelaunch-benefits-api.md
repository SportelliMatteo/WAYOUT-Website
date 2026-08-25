# Pre-launch benefits API

Il sito Laravel è l'unica fonte dati per waitlist e Founder Pass. Il backend dell'app mobile non viene chiamato da Laravel: è il backend dell'app a invocare questi endpoint server-to-server quando deve recuperare un vantaggio.

La specifica OpenAPI è disponibile in `public/openapi/prelaunch-benefits.yaml`; Swagger UI è pubblicata su `/api/docs`.

## Configurazione

Impostare in produzione:

```dotenv
EMAIL_SENDING_ENABLED=true
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=<credenziale SMTP Brevo>
MAIL_PASSWORD=<chiave SMTP Brevo>
MAIL_FROM_ADDRESS=<mittente verificato in Brevo>
MAIL_FROM_NAME=WAYOUT

WAITLIST_MAGIC_LINK_MINUTES=30
WAITLIST_VERIFICATION_RESEND_SECONDS=60
BENEFIT_EMAIL_CHALLENGE_MINUTES=10
BENEFIT_EMAIL_CHALLENGE_RESEND_SECONDS=60
BENEFIT_EMAIL_CHALLENGE_MAX_ATTEMPTS=5

BENEFIT_API_KEY=<identificativo condiviso col backend app>
BENEFIT_API_SECRET=<segreto casuale di almeno 32 byte>
BENEFIT_API_MAX_CLOCK_SKEW_SECONDS=300
```

Il segreto HMAC deve essere conservato esclusivamente nei secret manager dei due backend. Non va incluso nell'app mobile, nel link email o nel repository.

## Firma HMAC

Ogni richiesta usa gli header `X-Wayout-Key`, `X-Wayout-Timestamp`, `X-Wayout-Nonce` e `X-Wayout-Signature`.

La stringa canonica contiene, separati da `\n`:

```text
<timestamp Unix>
<nonce casuale univoco di almeno 16 caratteri>
<metodo HTTP maiuscolo>
<path completo che inizia con /api>
<sha256 esadecimale del body JSON esatto>
```

`X-Wayout-Signature` è l'HMAC-SHA256 esadecimale della stringa canonica. Il server accetta al massimo lo scarto temporale configurato e registra ogni nonce in cache per bloccare i replay. Il body firmato deve essere trasmesso senza ricodificarlo dopo il calcolo della firma.

## Flussi

Percorso dall'email:

1. La comunicazione di lancio contiene un deep/universal link con il solo `benefit_id` pubblico e opaco.
2. L'app verifica il numero di telefono e crea o riconosce il proprio account.
3. Il backend dell'app chiama `POST /api/v1/prelaunch/benefits/claim` con `benefit_id`, il proprio identificativo `account_reference` e una chiave d'idempotenza.
4. Laravel restituisce posizione e tipo di vantaggio e registra il collegamento definitivo.

Percorso dall'app:

1. Dopo la verifica telefonica, l'utente inserisce l'email usata nel pre-lancio.
2. Il backend dell'app chiama `POST /api/v1/prelaunch/benefits/email-challenges`.
3. Laravel invia tramite Brevo un OTP di sei cifre. La risposta HTTP ha forma neutra anche per email inesistenti.
4. Il backend dell'app chiama `POST /api/v1/prelaunch/benefits/email-challenges/verify` con OTP, `account_reference` e chiave d'idempotenza.
5. Laravel verifica l'email e registra lo stesso collegamento definitivo.

La priorità del vantaggio restituito è `founder_creator`, poi `founder_join`, infine `waitlist`. Un record può essere associato a un solo account dell'app; una ripetizione verso lo stesso account è idempotente, mentre un tentativo di trasferimento restituisce HTTP 409.

## Identificativi

- `waitlist_entries.id`: UUID interno e chiave relazionale del database Laravel.
- `benefit_id`: UUID pubblico, stabile e non sequenziale da inserire nel link email.
- `waitlist_position`: posizione assegnata soltanto dopo la verifica dell'email.
- `account_reference`: identificativo opaco creato e gestito dal backend dell'app; Laravel lo memorizza soltanto al riscatto.
- `waitlist_entry_id` sugli ordini: collega in modo certo waitlist, Founder Pass e successivo riscatto.

Non vengono scambiati database dump né dati telefonici. Il numero e il relativo OTP restano interamente nel backend dell'app.
