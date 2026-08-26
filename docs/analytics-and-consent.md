# Analytics e consenso cookie WAYOUT

## Architettura implementata

Il sito mostra un CMP proprietario prima di caricare qualsiasi strumento facoltativo. Le categorie sono:

- necessari: sempre attivi;
- analytics: Google Tag Manager oppure Google Analytics 4 diretto;
- marketing: Meta Pixel.

La scelta viene salvata nel cookie necessario `wayout_cookie_consent` per 180 giorni. Ogni aggiornamento viene registrato in `cookie_consent_events` con un identificativo pseudonimo e hash di IP/user-agent, senza conservare questi ultimi in chiaro. Il totale degli aggiornamenti è visibile nella dashboard Laravel. Il pulsante “Gestisci preferenze cookie” nel footer permette di modificare o revocare il consenso. Alla revoca il sito elimina i cookie Google/Meta conosciuti e ricarica la pagina.

Non vengono inviati ai fornitori dati dei form, email, telefono, nome, codice fiscale, benefit ID o altri identificativi personali. Il backend e la dashboard Laravel restano la fonte dei dati operativi; Analytics serve solo a misurare il funnel aggregato.

## Eventi del funnel

| Evento | Momento | Parametri non personali |
| --- | --- | --- |
| `waitlist_signup_requested` | richiesta del magic link | nessuno |
| `waitlist_email_verified` | prima verifica email riuscita | `has_founder_pass`, `plan` |
| `waitlist_returning_member` | email già presente e verificata | `has_founder_pass`, `plan` |
| `founder_offer_view` | apertura offerta Founder | `returning` |
| `view_founder_passes` | visualizzazione pagina pass | nessuno |
| `select_founder_plan` | scelta del pass | `plan`, `location` |
| `begin_checkout` | invio dati validi al backend WAYOUT | `plan`, `value`, `currency`, `invoice_requested` |
| `purchase` | entitlement di pagamento confermato lato server dal backend WAYOUT | `transaction_id`, `plan`, `value`, `currency` |
| `contact_submitted` | form contatti riuscito | nessuno |

`purchase` è deduplicato nel browser per riferimento ordine e non viene emesso per callback non pagate, sconosciute o ordini in revisione.

## Variabili ambiente

```dotenv
ANALYTICS_ENABLED=true
ANALYTICS_CONSENT_VERSION=1
ANALYTICS_CONSENT_DAYS=180
ANALYTICS_GTM_ID=GTM-XXXXXXX
ANALYTICS_GA4_ID=
ANALYTICS_META_PIXEL_ID=123456789012345
ANALYTICS_DEBUG=false
```

Usare una sola modalità Google:

1. consigliata: valorizzare `ANALYTICS_GTM_ID` e lasciare vuoto `ANALYTICS_GA4_ID`;
2. più semplice: lasciare vuoto GTM e valorizzare direttamente `ANALYTICS_GA4_ID=G-...`.

Quando GTM è configurato ha la precedenza sul caricamento GA4 diretto. Non inserire manualmente script GTM, GA4 o Meta nei template: causerebbero duplicazioni e potrebbero aggirare il consenso.

## Configurazione richiesta negli account esterni

### Google Analytics 4

1. Creare una proprietà GA4 e uno stream Web per il dominio definitivo.
2. Disattivare Google Signals e la personalizzazione pubblicitaria, salvo una futura valutazione legale separata.
3. Non configurare User-ID, enhanced conversions o invio di dati personali.
4. Se si usa GTM, creare il Google tag GA4 nel container e trigger per gli eventi elencati sopra. Configurare i tag perché richiedano `analytics_storage` concesso.
5. Pubblicare il container solo dopo i test in Preview/Tag Assistant.

### Meta Pixel (facoltativo)

1. Creare/selezionare un Pixel nel Business Manager.
2. Copiare il solo ID numerico in `ANALYTICS_META_PIXEL_ID`.
3. Verificare in “Test Events” che `PageView`, `Lead`, `InitiateCheckout` e `Purchase` arrivino soltanto dopo consenso Marketing.
4. Non attivare Advanced Matching automatico o invio di dati dei form senza una nuova analisi privacy.

## Collaudo prima della pubblicazione

1. Aprire il sito in una finestra privata e controllare che prima della scelta non partano richieste verso `googletagmanager.com`, `google-analytics.com` o `connect.facebook.net`.
2. Provare “Rifiuta tutto”: sito, waitlist e checkout devono continuare a funzionare senza tracker.
3. Provare separatamente solo Analytics, solo Marketing e consenso completo.
4. Verificare il pulsante nel footer e la revoca.
5. Completare un pagamento di test tramite il backend WAYOUT e verificare un solo evento `purchase` con lo stesso riferimento ordine.
6. Controllare GA4 DebugView/Tag Assistant e Meta Test Events.
7. In produzione impostare `ANALYTICS_DEBUG=false`, quindi eseguire `php artisan optimize:clear`, `php artisan config:cache` e `npm run build` durante il deploy.

Se cambia la struttura dei fornitori, delle finalità o dei tempi di conservazione, incrementare `ANALYTICS_CONSENT_VERSION`: il sito richiederà nuovamente il consenso. Prima del go-live verificare anche che Cookie Policy e Privacy Policy pubblicate descrivano gli strumenti realmente attivati.
