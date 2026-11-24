import { getApp, getApps, initializeApp } from 'firebase/app';
import { getAuth, inMemoryPersistence, RecaptchaVerifier, setPersistence, signInWithPhoneNumber, signOut } from 'firebase/auth';

const setupCheckoutPhoneVerification = () => {
    const form = document.getElementById('payment-details-form');
    const prefix = document.getElementById('payment-phone-prefix');
    const number = document.getElementById('payment-phone-number');
    const send = document.getElementById('payment-phone-send');
    const codePanel = document.getElementById('payment-phone-code-panel');
    const code = document.getElementById('payment-phone-code');
    const verify = document.getElementById('payment-phone-verify');
    const token = document.getElementById('payment-firebase-token');
    const status = document.getElementById('payment-phone-status');
    const submit = document.getElementById('payment-details-submit');

    const autoSubmit = form?.dataset.submitAfterPhoneVerification === 'true';
    const loginLoader = document.getElementById('customer-login-loader');

    if (!form || !prefix || !number || !send || !codePanel || !code || !verify || !token || !status || (!submit && !autoSubmit)) return;
    if (form.dataset.phoneVerificationInitialized === 'true') return;

    form.dataset.phoneVerificationInitialized = 'true';

    const config = window.wayoutFirebaseConfig || {};
    const messages = window.wayoutPhoneVerificationMessages || {};
    const initialSendLabel = send.textContent.trim();
    const resendSeconds = Math.max(1, Number.parseInt(send.dataset.resendSeconds || '60', 10) || 60);
    let loginInProgress = false;
    let confirmation = null;
    let recaptcha = null;
    let codeWasSent = false;
    let sendInProgress = false;
    let resendAvailableAt = 0;
    let countdownTimer = null;

    const phoneNumber = () => `${prefix.value}${number.value.replace(/\D/g, '')}`;
    const validPhone = (value) => /^\+[1-9]\d{6,14}$/.test(value);
    const showStatus = (message, type = 'info') => {
        status.textContent = message;
        status.className = `rounded-2xl px-4 py-3 text-sm font-bold ${type === 'error'
            ? 'bg-rose-100 text-rose-800'
            : type === 'success'
                ? 'bg-emerald-100 text-emerald-800'
                : 'bg-white text-slate-700'}`;
    };
    const reset = () => {
        token.value = '';
        if (submit) submit.disabled = true;
        confirmation = null;
        code.value = '';
        codePanel.classList.add('hidden');
        status.classList.add('hidden');
    };
    const updateSendButton = () => {
        const secondsRemaining = Math.max(0, Math.ceil((resendAvailableAt - Date.now()) / 1000));

        if (token.value || sendInProgress) {
            send.disabled = true;
            return;
        }

        if (secondsRemaining > 0) {
            send.disabled = true;
            send.textContent = (messages.resendCountdown || 'Resend in :seconds s')
                .replace(':seconds', secondsRemaining.toString());
            return;
        }

        send.disabled = false;
        send.textContent = codeWasSent ? (messages.resendCode || initialSendLabel) : initialSendLabel;

        if (countdownTimer) {
            window.clearInterval(countdownTimer);
            countdownTimer = null;
        }
    };
    const startResendCooldown = () => {
        codeWasSent = true;
        resendAvailableAt = Date.now() + (resendSeconds * 1000);
        updateSendButton();
        countdownTimer = window.setInterval(updateSendButton, 250);
    };
    const recreateRecaptchaContainer = () => {
        recaptcha?.clear();
        recaptcha = null;

        const current = document.getElementById('payment-phone-recaptcha');

        if (!current) return null;

        const replacement = current.cloneNode(false);
        current.replaceWith(replacement);

        return replacement;
    };

    if (!['apiKey', 'authDomain', 'projectId', 'appId'].every((key) => Boolean(config[key]))) {
        send.disabled = true;
        showStatus(messages.configurationError, 'error');
        return;
    }

    const firebaseApp = getApps().length ? getApp() : initializeApp(config);
    const auth = getAuth(firebaseApp);
    auth.languageCode = document.documentElement.lang || 'it';

    [prefix, number].forEach((field) => field.addEventListener('input', () => {
        reset();
        updateSendButton();
    }));

    send.addEventListener('click', async () => {
        const targetPhone = phoneNumber();
        reset();

        if (!validPhone(targetPhone)) {
            showStatus(messages.invalidPhone, 'error');
            return;
        }

        sendInProgress = true;
        updateSendButton();
        showStatus(messages.sending);

        try {
            const recaptchaContainer = recreateRecaptchaContainer();

            if (!recaptchaContainer) throw new Error('Firebase reCAPTCHA container is missing.');

            recaptcha = new RecaptchaVerifier(auth, recaptchaContainer, { size: 'invisible' });
            await setPersistence(auth, inMemoryPersistence);
            confirmation = await signInWithPhoneNumber(auth, targetPhone, recaptcha);
            codePanel.classList.remove('hidden');
            showStatus(messages.codeSent);
            startResendCooldown();
            code.focus();
        } catch (error) {
            console.error('Firebase checkout phone verification failed.', error);
            recreateRecaptchaContainer();
            showStatus(messages.sendError, 'error');
        } finally {
            sendInProgress = false;
            updateSendButton();
        }
    });

    if (autoSubmit) {
        form.addEventListener('submit', (event) => {
            if (token.value && loginInProgress) return;
            event.preventDefault();
            if (loginInProgress) return;
            if (confirmation) verify.click();
            else send.click();
        });
    }

    verify.addEventListener('click', async () => {
        if (verify.disabled || loginInProgress) return;
        if (!confirmation || !/^\d{6}$/.test(code.value.trim())) {
            showStatus(messages.codeError, 'error');
            return;
        }

        verify.disabled = true;
        if (autoSubmit) {
            loginLoader?.classList.remove('hidden');
            loginLoader?.classList.add('flex');
            form.setAttribute('aria-busy', 'true');
        }
        showStatus(messages.verifying);

        try {
            const credential = await confirmation.confirm(code.value.trim());
            token.value = await credential.user.getIdToken(true);
            await signOut(auth);
            if (submit) submit.disabled = form.dataset.legalUnavailable === 'true';
            prefix.disabled = true;
            number.readOnly = true;
            updateSendButton();
            codePanel.classList.add('hidden');
            showStatus(messages.verified, 'success');

            if (autoSubmit && form.dataset.legalUnavailable !== 'true') {
                loginInProgress = true;
                status.classList.add('hidden');
                form.requestSubmit();
            }
        } catch (error) {
            loginInProgress = false;
            console.error('Firebase checkout OTP confirmation failed.', error);
            showStatus(messages.codeError, 'error');
        } finally {
            if (!loginInProgress) {
                verify.disabled = false;
                if (autoSubmit) {
                    loginLoader?.classList.add('hidden');
                    loginLoader?.classList.remove('flex');
                    form.removeAttribute('aria-busy');
                }
            }
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupCheckoutPhoneVerification, { once: true });
} else {
    setupCheckoutPhoneVerification();
}
