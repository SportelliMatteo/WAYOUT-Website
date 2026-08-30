import { getApp, getApps, initializeApp } from 'firebase/app';
import { getAuth, RecaptchaVerifier, signInWithPhoneNumber } from 'firebase/auth';

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

    if (!form || !prefix || !number || !send || !codePanel || !code || !verify || !token || !status || !submit) return;

    const config = window.wayoutFirebaseConfig || {};
    const messages = window.wayoutPhoneVerificationMessages || {};
    const initialSendLabel = send.textContent.trim();
    const resendSeconds = Math.max(1, Number.parseInt(send.dataset.resendSeconds || '60', 10) || 60);
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
        submit.disabled = true;
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
    const resetRecaptcha = () => {
        recaptcha?.clear();
        recaptcha = null;
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
            resetRecaptcha();
            recaptcha = new RecaptchaVerifier(auth, 'payment-phone-recaptcha', { size: 'invisible' });
            confirmation = await signInWithPhoneNumber(auth, targetPhone, recaptcha);
            codePanel.classList.remove('hidden');
            showStatus(messages.codeSent);
            startResendCooldown();
            code.focus();
        } catch (error) {
            console.error('Firebase checkout phone verification failed.', error);
            resetRecaptcha();
            showStatus(messages.sendError, 'error');
        } finally {
            sendInProgress = false;
            updateSendButton();
        }
    });

    verify.addEventListener('click', async () => {
        if (!confirmation || !/^\d{6}$/.test(code.value.trim())) {
            showStatus(messages.codeError, 'error');
            return;
        }

        verify.disabled = true;
        showStatus(messages.verifying);

        try {
            const credential = await confirmation.confirm(code.value.trim());
            token.value = await credential.user.getIdToken(true);
            submit.disabled = false;
            prefix.disabled = true;
            number.readOnly = true;
            updateSendButton();
            codePanel.classList.add('hidden');
            showStatus(messages.verified, 'success');
        } catch (error) {
            console.error('Firebase checkout OTP confirmation failed.', error);
            showStatus(messages.codeError, 'error');
        } finally {
            verify.disabled = false;
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupCheckoutPhoneVerification, { once: true });
} else {
    setupCheckoutPhoneVerification();
}
