# WAYOUT - Wireframe del sito

Documento low-fidelity basato sulle pagine attualmente presenti nel progetto.

## Struttura globale

```text
Desktop
┌──────────────────────────────────────────────────────────────┐
│ NAV fixed: Logo | Home | Chi siamo | Contatti | IT | EN      │
├──────────────────────────────────────────────────────────────┤
│ CONTENUTO PAGINA                                             │
├──────────────────────────────────────────────────────────────┤
│ FOOTER                                                       │
│ Logo + descrizione + social | Navigazione + legale | Societa │
└──────────────────────────────────────────────────────────────┘

Mobile
┌──────────────────────┐
│ NAV fixed: Logo | ☰  │
├──────────────────────┤
│ CONTENUTO PAGINA     │
├──────────────────────┤
│ FOOTER a colonne     │
└──────────────────────┘
```

## Home `/`

```text
┌──────────────────────────────────────────────────────────────┐
│ HERO                                                         │
│ ┌────────────────────────────┐ ┌───────────────────────────┐ │
│ │ Eyebrow                    │ │ Mockup app / schermata    │ │
│ │ H1: Trova il tuo tavolo    │ │ Card: eventi a Milano     │ │
│ │ Testo introduttivo         │ │ Card: tavolo disponibile  │ │
│ │                            │ └───────────────────────────┘ │
│ │ WAITLIST CARD              │                               │
│ │ Totale posti | 60 giorni   │                               │
│ │ Email input | CTA          │                               │
│ │ Messaggio esaurito/errori  │                               │
│ └────────────────────────────┘                               │
├──────────────────────────────────────────────────────────────┤
│ COME FUNZIONA                                                │
│ Titolo + testo                                               │
│ [Esplora città] [Crea tavolo] [Entra nel gruppo]             │
├──────────────────────────────────────────────────────────────┤
│ ESPERIENZA APP                                               │
│ Blocco scuro: testo a sinistra + slideshow schermate app     │
└──────────────────────────────────────────────────────────────┘
```

Stati principali:
- Waitlist aperta: CTA “Accedi”.
- Waitlist chiusa: CTA “Verifica email” e messaggio di chiusura.
- Dopo iscrizione: modal Founder Pass.
- Modal utente già registrato/acquirente: stato differenziato, resend email se ha già comprato.

## Modal Founder Pass

```text
┌──────────────────────────────────────────────────────────────┐
│ Overlay scuro                                                │
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ Header: badge Waitlist + chiudi                         │ │
│ │ Titolo stato iscrizione                                 │ │
│ │ Testo email/stato                                       │ │
│ │ [60 giorni] [posti riservati]                           │ │
│ │ Founder Pass card                                       │ │
│ │ ┌─────────────────────┐ ┌─────────────────────────────┐ │ │
│ │ │ Join Pass 29€       │ │ Creator Pass 59€            │ │ │
│ │ │ descrizione/stato   │ │ descrizione/stato           │ │ │
│ │ └─────────────────────┘ └─────────────────────────────┘ │ │
│ │ CTA: Scopri pass / Resto in waitlist                    │ │
│ └──────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────┘
```

## Chi Siamo `/chi-siamo`

```text
┌──────────────────────────────────────────────────────────────┐
│ HERO                                                         │
│ ┌──────────────────────────┐ ┌─────────────────────────────┐ │
│ │ Eyebrow                  │ │ Immagine team/progetto      │ │
│ │ H1                       │ │ Card mission sovrapposta    │ │
│ │ Intro                    │ └─────────────────────────────┘ │
│ └──────────────────────────┘                                 │
├──────────────────────────────────────────────────────────────┤
│ TRE CARD VALORI                                              │
│ [01 Persone vere] [02 Tavoli aperti] [03 Ricordi condivisi]  │
├──────────────────────────────────────────────────────────────┤
│ BLOCCO PROGETTO                                              │
│ Titolo a sinistra | testo narrativo a destra                 │
└──────────────────────────────────────────────────────────────┘
```

## Contatti `/contatti`

```text
┌──────────────────────────────────────────────────────────────┐
│ ┌────────────────────────────┐ ┌───────────────────────────┐ │
│ │ Colonna sticky             │ │ Form contatti             │ │
│ │ Eyebrow + H1 + testo       │ │ Nome | Email              │ │
│ │ Card scura “Perche”        │ │ Oggetto                   │ │
│ │ - Club e venue             │ │ Messaggio                 │ │
│ │ - Collaborazioni           │ │ CTA Invio                 │ │
│ │ - Curiosita                │ │ Success/error state       │ │
│ └────────────────────────────┘ └───────────────────────────┘ │
└──────────────────────────────────────────────────────────────┘
```

## Subscribe `/subscribe`

```text
┌──────────────────────────────────────────────────────────────┐
│ ┌──────────────────────────────────┐ ┌─────────────────────┐ │
│ │ Eyebrow + H1 + testo             │ │ Aside sticky scuro   │ │
│ │ [60 giorni] [accessi totali]     │ │ Founder Pass         │ │
│ │                                  │ │ Join 29€             │ │
│ │ Scelta piano                     │ │ Creator 59€          │ │
│ │ ┌──────────────┐ ┌─────────────┐ │ │ Durata 12 mesi       │ │
│ │ │ Join radio   │ │ Creator     │ │ └─────────────────────┘ │
│ │ └──────────────┘ └─────────────┘ │                         │
│ │ CTA checkout | Torna home        │                         │
│ └──────────────────────────────────┘                         │
└──────────────────────────────────────────────────────────────┘
```

Stati principali:
- Entrambi disponibili: utente sceglie piano e va a Stripe.
- Join esaurito: radio Join disabilitata, default Creator.
- Creator esaurito: radio Creator disabilitata.
- Tutti esauriti: messaggio, CTA checkout disabilitata.

## Checkout Success `/checkout/success`

```text
┌──────────────────────────────────────────────┐
│ Card scura centrale                          │
│ Icona check                                  │
│ Eyebrow pagamento completato                 │
│ H1 conferma Founder Pass                     │
│ Testo conferma email                         │
│ CTA torna alla home                          │
└──────────────────────────────────────────────┘
```

## Area Admin

### Login `/admin/login`

```text
┌──────────────────────────────┐
│ Card centrata                │
│ Logo                         │
│ Area riservata / Dashboard   │
│ Email                        │
│ Password                     │
│ CTA Entra                    │
│ Switch IT/EN                 │
└──────────────────────────────┘
```

### Dashboard `/admin`

```text
┌──────────────────────────────────────────────────────────────┐
│ Header: brand + titolo | IT/EN | Logout                     │
├──────────────────────────────────────────────────────────────┤
│ KPI: Waitlist | Buyer | Ordini | Join Pass | Creator Pass    │
├──────────────────────────────────────────────────────────────┤
│ Fatturato totale                                             │
├──────────────────────────────────────────────────────────────┤
│ Capienze Founder                                             │
│ Waitlist capacity | Join capacity | Creator capacity | Salva │
├──────────────────────────────────────────────────────────────┤
│ Filtri                                                       │
│ Search email | Status | Pass | Filtra                       │
├──────────────────────────────────────────────────────────────┤
│ Tabella persone in waitlist                                  │
│ Email | Stato | Pass | Ordini | Totale | Iscrizione | Ultimo │
├──────────────────────────────────────────────────────────────┤
│ Tabella ordini recenti                                       │
├──────────────────────────────────────────────────────────────┤
│ Tabella utenti Join | Tabella utenti Creator                 │
└──────────────────────────────────────────────────────────────┘
```

## Area Legale

Pagine:
- `/mappa-sito`
- `/privacy-policy`
- `/cookie-policy`
- `/termini-e-condizioni`
- `/condizioni-di-vendita`
- `/note-legali`

```text
┌──────────────────────────────────────────────────────────────┐
│ Header pagina legale: badge + H1 + ultimo aggiornamento      │
├──────────────────────────────────────┬───────────────────────┤
│ Articolo documento                   │ Sidebar documenti     │
│ Sezioni testuali / tabelle / liste   │ Privacy               │
│                                      │ Cookie                │
│                                      │ Termini               │
│                                      │ Vendita               │
│                                      │ Note                  │
│                                      │ Mappa                 │
└──────────────────────────────────────┴───────────────────────┘
```

Mobile:
- Sidebar documenti sotto o sopra al contenuto in colonna singola.
- Articolo a larghezza piena.

## Flussi Utente

```text
Nuovo utente
Home -> inserisce email -> waitlist store -> modal Founder Pass
-> Scopri pass -> Subscribe -> seleziona Join/Creator -> Stripe -> Success

Utente gia in waitlist
Home -> inserisce email -> modal stato gia registrato -> Subscribe

Utente gia acquirente
Home -> inserisce email -> modal acquisto gia presente -> resend conferma

Contatto
Contatti -> invia form -> messaggio successo o errore

Admin
/admin/login -> credenziali -> /admin -> filtra dati / aggiorna capienze / logout
```

## Priorita Responsive

- Mobile: nav compatta con menu full-screen, sezioni a colonna singola, CTA full-width.
- Tablet: card e KPI su griglie a 2 colonne quando lo spazio lo consente.
- Desktop: hero e form in griglie a 2 colonne, dashboard densa, tabelle con scroll orizzontale.

