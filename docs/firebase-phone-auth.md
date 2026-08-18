# Firebase Phone Authentication

La waitlist usa Firebase Authentication per inviare l’OTP via SMS. Il browser conferma il codice e invia al backend un Firebase ID token; il backend verifica firma, scadenza, progetto e corrispondenza esatta del numero prima di salvare il profilo.

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
FIREBASE_CREDENTIALS=/absolute/path/to/firebase-service-account.json
```

Imposta `FIREBASE_PHONE_VERIFICATION_ENABLED=false` negli ambienti in cui vuoi saltare completamente l’OTP. Con il flag disattivato i controlli Firebase non vengono mostrati nel form e il backend non richiede né verifica l’ID token. Il valore predefinito è `false`.

`FIREBASE_CREDENTIALS` può essere un percorso assoluto, un percorso relativo alla root Laravel oppure il JSON della service account fornito direttamente come variabile d’ambiente. Non pubblicare mai questa credenziale; `FIREBASE_API_KEY`, `FIREBASE_AUTH_DOMAIN`, `FIREBASE_PROJECT_ID` e `FIREBASE_APP_ID` sono invece valori client pubblici.

Dopo aver modificato l’ambiente, esegui `php artisan config:clear` (oppure rigenera la cache di configurazione in produzione).

## Collaudo senza SMS reali

Firebase permette di configurare numeri di telefono fittizi e codici OTP statici dalla sezione del provider Phone. Usali negli ambienti di sviluppo per evitare invii e limiti SMS reali; la disattivazione della verifica app/reCAPTCHA non deve essere abilitata in produzione.
