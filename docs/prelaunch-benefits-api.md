# Waitlist benefits API

Il sito Laravel espone due endpoint Benefits destinati esclusivamente al backend dell'app:

```http
GET /api/v1/prelaunch/benefits?page=1&per_page=100
POST /api/v1/prelaunch/benefits/eligibility
```

La specifica OpenAPI è in `public/openapi/prelaunch-benefits.yaml`; Swagger UI è pubblicata su `/api/docs`.

## Regola di idoneità

La risposta include soltanto gli utenti che:

- hanno verificato l'email e ottenuto una posizione in waitlist;
- hanno un `benefit_id` assegnato;
- non hanno alcun acquisto Founder con stato `succeeded`.

Il benefit restituito è sempre `waitlist`, dura 60 giorni ed è non cumulabile con un Founder Pass. Ordini falliti o ancora pendenti non eliminano il diritto.

## Elenco paginato

```json
{
  "success": true,
  "data": [
    {
      "benefit_id": "11111111-1111-4111-8111-111111111111",
      "email": "mario@example.com",
      "waitlist_position": 184,
      "benefit_type": "waitlist",
      "duration_days": 60,
      "email_verified_at": "2026-08-27T10:30:00.000000Z"
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 100,
    "total": 1,
    "last_page": 1
  }
}
```

Il backend dell'app deve scorrere tutte le pagine e usare `benefit_id` come identificativo stabile. Laravel non gestisce più claim, associazione a un account dell'app o OTP email per i Benefits.

## Verifica tramite email

Quando l'utente inserisce l'email nel popup, l'app deve inviarla al proprio backend. È il backend dell'app, che conserva il segreto HMAC, a chiamare Laravel:

```http
POST /api/v1/prelaunch/benefits/eligibility
Content-Type: application/json

{"email":"mario@example.com"}
```

Risposta positiva:

```json
{
  "success": true,
  "data": {
    "eligible": true,
    "benefit": {
      "benefit_id": "11111111-1111-4111-8111-111111111111",
      "email": "mario@example.com",
      "waitlist_position": 184,
      "benefit_type": "waitlist",
      "duration_days": 60,
      "email_verified_at": "2026-08-27T10:30:00.000000Z"
    }
  }
}
```

Per email sconosciute, non verificate o associate a un acquisto Founder riuscito, la risposta resta HTTP 200 con `eligible: false` e `benefit: null`.

## Configurazione

```dotenv
BENEFIT_API_DEFAULT_PER_PAGE=100
BENEFIT_API_MAX_PER_PAGE=200
BENEFIT_API_KEY=<identificativo condiviso col backend app>
BENEFIT_API_SECRET=<segreto casuale di almeno 32 byte>
BENEFIT_API_MAX_CLOCK_SKEW_SECONDS=300
```

La chiave e il segreto devono essere impostati nel `.env` effettivo di entrambi i backend, non nell'app mobile e non nel repository.

## Firma HMAC

Ogni richiesta usa gli header `X-Wayout-Key`, `X-Wayout-Timestamp`, `X-Wayout-Nonce` e `X-Wayout-Signature`.

Per la GET, la stringa canonica contiene, separati da `\n`:

```text
<timestamp Unix>
<nonce casuale univoco di almeno 16 caratteri>
GET
/api/v1/prelaunch/benefits
<sha256 esadecimale del body vuoto>
```

I parametri query non sono inclusi nel path firmato. La firma è l'HMAC-SHA256 esadecimale della stringa canonica. Il server verifica lo scarto temporale e registra il nonce in cache per impedire replay.

Per la POST si firmano invece metodo `POST`, path `/api/v1/prelaunch/benefits/eligibility` e SHA-256 del body JSON esatto. Il body non deve essere ricodificato dopo il calcolo della firma.

## Dati e minimizzazione

La risposta contiene soltanto email, identificativo opaco del benefit, posizione e data di verifica. Non espone nome, telefono, profilo, consenso marketing, ordini o dati di pagamento. La dashboard amministrativa usa la stessa regola di idoneità mostrata dall'API.
