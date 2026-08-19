# Firebase Phone Authentication

La waitlist usa Firebase Authentication per inviare l’OTP via SMS. Il telefono è il primo dato richiesto e diventa l’identità non modificabile dell’account. Dopo la conferma del codice, Laravel conserva ID token e telefono per un massimo di un’ora in una sessione server-side cifrata. La verifica autorevole del token viene eseguita dal backend WAYOUT, non da questo sito.

Il secondo form raccoglie nome, cognome, data di nascita, email e genere. Al submit Laravel:

1. invia l’ID token a `POST /api/auth/verify-firebase-token`;
2. legge il token temporaneo restituito e confronta il `phone_number` del JWT appena verificato con quello della sessione;
3. genera un nickname con FakerPHP;
4. chiama `POST /api/auth/create-profile` passando il token in `X-Temp-Token`, il telefono verificato e i dati del profilo.

## Configurazione Firebase

1. Crea o seleziona il progetto nella Firebase Console.
2. In **Authentication > Sign-in method**, abilita il provider **Phone**.
3. In **Authentication > Settings > Authorized domains**, aggiungi i domini del sito e quelli usati in sviluppo.
4. Registra una Web App Firebase e copia la relativa configurazione pubblica.
5. Genera una chiave JSON da **Project settings > Service accounts** e salvala fuori dal repository. Il pattern `firebase-service-account*.json` è ignorato da Git.
6. Esegui `php artisan migrate` e configura le variabili seguenti:

```dotenv
FIREBASE_PHONE_VERIFICATION_ENABLED=true
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_API_KEY=your-web-api-key
FIREBASE_AUTH_DOMAIN=your-project-id.firebaseapp.com
FIREBASE_APP_ID=your-web-app-id
WAYOUT_APP_API_URL=https://staging-app.wayoutapp.it
WAYOUT_APP_API_CONNECT_TIMEOUT=5
WAYOUT_APP_API_TIMEOUT=15
```

Imposta `FIREBASE_PHONE_VERIFICATION_ENABLED=false` soltanto negli ambienti locali o di test in cui vuoi saltare completamente OTP e creazione del profilo remoto. Il valore predefinito è `false`; negli ambienti pubblici deve essere `true`.

Non servono una service account, `FIREBASE_CREDENTIALS`, `storageBucket` o `messagingSenderId`: questo sito usa Firebase soltanto per Phone Authentication. Le variabili client elencate sopra sono pubbliche.

Usa un driver di sessione server-side (`file`, `database` o `redis`) e imposta `SESSION_ENCRYPT=true`. In produzione usa HTTPS e `SESSION_SECURE_COOKIE=true`; il driver `cookie` è rifiutato dal flusso di registrazione perché inserirebbe il token nel cookie del browser.

Dopo aver modificato l’ambiente, esegui `php artisan config:clear` (oppure rigenera la cache di configurazione in produzione).

## Collaudo senza SMS reali

Firebase permette di configurare numeri di telefono fittizi e codici OTP statici dalla sezione del provider Phone. Usali negli ambienti di sviluppo per evitare invii e limiti SMS reali; la disattivazione della verifica app/reCAPTCHA non deve essere abilitata in produzione.
