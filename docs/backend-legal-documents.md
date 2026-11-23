# Documenti legali del sito

La fonte dei documenti correnti è il backend WAYOUT configurato in
`services.wayout.base_url`. Le richieste sono GET pubbliche, senza token;
il footer richiede l’elenco senza scope per mostrare separatamente APP e SITE.
Dettaglio e storico richiedono sempre lo scope del documento scelto.
Il sito inserisce `current.content_html` così come ricevuto dal backend.
Invia `Accept-Language` con la lingua selezionata; la lingua del contenuto
restituito resta gestita dal backend.

## Pagine pubbliche

- `/documenti-legali`: elenco complessivo dei documenti pubblicati.
- `/documenti-legali/{scope}/it`: elenco per `site` oppure `app`.
- `/documenti-legali/{slug}/{scope}/it`: documento pubblicato, anche senza template locale.
- `/documenti-legali/{slug}/{scope}/it/versioni`: versioni precedentemente pubblicate.
- `/documenti-legali/{slug}/{scope}/it/versioni/{uuid}`: HTML della versione precisa.

I link sono costruiti con `LegalDocumentService::route`: lo slug è il codice backend
in minuscolo con trattini al posto degli underscore (es. `TERMINI_DI_VENDITA`
diventa `termini-di-vendita`). Scope e lingua sono segmenti del percorso, senza
query string. Le route Laravel validano slug, scope, lingua italiana e UUID.
Il menu laterale ha scorrimento indipendente e altezza limitata alla finestra.

I vecchi URL pubblici rimangono collegati ai codici in `config/legal.php`.
Lo storico ricevuto dal backend include anche la versione corrente: l'elenco
"Versioni precedenti" la esclude. Prima di leggere una versione per UUID, il
sito verifica l'appartenenza allo storico del documento SITE e controlla
`data.document.code` e `data.document.scope` nella risposta.

## Codici predefiniti

| Chiave sito | Codice backend | Variabile di configurazione |
| --- | --- | --- |
| privacy | PRIVACY | LEGAL_CODE_PRIVACY |
| cookies | COOKIES | LEGAL_CODE_COOKIES |
| terms | TERMS | LEGAL_CODE_TERMS |
| passes | PASSES | LEGAL_CODE_PASSES |
| sales | TERMINI_DI_VENDITA | LEGAL_CODE_SALES |
| presale | PRESALE | LEGAL_CODE_PRESALE |
| refunds | RECESSO_E_RIMBORSI | LEGAL_CODE_REFUNDS |
| withdrawal_info | INFORMATIVA_DIRITTO_RECESSO | LEGAL_CODE_WITHDRAWAL_INFO |
| notice | NOTE_LEGALI | LEGAL_CODE_NOTICE |
| marketing | MARKETING | LEGAL_CODE_MARKETING |
| waitlist_acceptance | WAITLIST_ACCEPTANCE | LEGAL_CODE_WAITLIST_ACCEPTANCE |
| contact_acceptance | CONTACT_ACCEPTANCE | LEGAL_CODE_CONTACT_ACCEPTANCE |
| purchase_acceptance | PURCHASE_ACCEPTANCE | LEGAL_CODE_PURCHASE_ACCEPTANCE |

Cambiare la relativa variabile se il backend usa un codice diverso. In presenza
di configurazione Laravel memorizzata, rigenerarla dopo la modifica.

## Consensi e indisponibilità

Le ricevute dei consensi usano versione e SHA-256 dell'HTML restituito dal backend;
il collegamento registrato identifica l'esatto UUID, anziché il testo corrente.
Un documento mancante o una risposta priva di HTML non recupera vecchi testi locali:
la pagina segnala indisponibilità con HTTP 503. Contatti e catalogo rimangono
consultabili se manca il testo della relativa checkbox, ma l'invio viene disabilitato.

Le vecchie versioni nel database non vengono eliminate. `LocalLegalDocumentArchive`
è riservato alle migrazioni storiche; non viene usato come fonte dalle pagine pubbliche.
Il backoffice del sito contiene le configurazioni, con sicurezza amministratori,
retention e relativo registro delle attività. I consensi cookie continuano a essere
registrati dal sito, senza un tab di consultazione in dashboard. Le rotte amministrative per rimborsi,
fatture e pubblicazione legale non sono più registrate.


## Footer e pulsanti degli store

Il footer e la navigazione legale mostrano solo i documenti restituiti dall’API,
separati per `scope`; il codice da solo non identifica univocamente un documento.
Tutti i link a documento, storico e versione mantengono `/app/it` o `/site/it` nel percorso.
I consensi di acquisto e contatto continuano a riferirsi ai documenti SITE.

La dashboard permette di impostare i link HTTPS per App Store e Google Play.
Sono salvati nella nuova tabella `site_settings` (migrazione
`2026_11_13_000001_create_site_settings_table`). Prima del primo salvataggio
valgono i link configurati in `services.app_stores`; dopo il salvataggio,
un campo vuoto disabilita il relativo pulsante anche se esiste un valore nell’ambiente.
I pulsanti nella home e nel footer usano la stessa configurazione.
