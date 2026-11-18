# Disponibilità dei piani sul sito

La dashboard espone **Piani e acquisti sul sito → Disponibilità dei piani**.
Il valore `founder_settings.plans_enabled` viene controllato per ogni richiesta.
Non usa una cache applicativa e l’anteprima amministrativa non lo aggira.
Modifiche riservate agli amministratori autenticati, protette da CSRF e registrate
nel registro delle attività come `plans.availability_updated`.

Quando il valore è `0`, mancante o non leggibile:

- spariscono i link ai piani dalla navigazione desktop/mobile e dal footer,
  la sezione promozionale della home e i pulsanti da Chi siamo e FAQ;
- `/piani`, `/subscribe`, `/subscribe/plans`, `/subscribe/checkout`,
  `/checkout/success/{purchase}`, `/checkout/status/{purchase}`,
  `/checkout/cancel/{purchase}` e `/purchase/confirmation` rispondono 404;
- le risposte JSON usano il codice `PLANS_DISABLED`;
- il controllo precede i controller: niente catalogo, identificazione utente,
  creazione di ordini o sessioni di pagamento tramite le rotte bloccate;
- un modulo aperto prima della disattivazione non può inviare un nuovo checkout;
- le risposte del flusso hanno `Cache-Control: no-store, private`.

Il valore `1` rende nuovamente disponibili il flusso e i collegamenti.
Il catalogo abbonamenti mostra esclusivamente offerte attive con provider
`STRIPE` attivo e un `external_price_id` valido (`price_…`). I piani disponibili
solo nell’app non sono restituiti al sito né mostrati come schede disabilitate.
La migrazione `2026_11_18_000003_add_plans_enabled_setting.php` inizializza a `1`
solo se il record non esiste, preservando lo stato attivo delle installazioni
esistenti. Le successive migrazioni non riabilitano un record impostato a `0`.

Il blocco riguarda il sito Laravel. Non modifica offerte o abbonamenti nell’app,
non annulla sessioni Stripe già create e non impedisce il completamento di
un pagamento su una pagina Stripe aperta in precedenza. Questa operazione
richiederebbe un’API dedicata del backend che gestisce Stripe.
Documenti legali, recesso, disdetta, cancellazione account e API benefit
restano disponibili secondo le rispettive regole di accesso.

Verifica: `PlansAvailabilityTest` copre interruttore, autenticazione, registro
attività, accessi diretti anche firmati, visibilità, richieste già avviate,
anteprima admin e comportamento in assenza dell’impostazione.
