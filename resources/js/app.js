import { getApp, getApps, initializeApp } from 'firebase/app';
import {
    getAuth,
    RecaptchaVerifier,
    signInWithPhoneNumber,
    signOut,
} from 'firebase/auth';

const setupWaitlistPhoneVerification = () => {
    const form = document.getElementById('waitlist-profile-form');

    if (!form) return;

    const prefix = document.getElementById('waitlist-phone-prefix');
    const number = document.getElementById('waitlist-phone-number');
    const sendButton = document.getElementById('waitlist-phone-send');
    const codePanel = document.getElementById('waitlist-phone-code-panel');
    const code = document.getElementById('waitlist-phone-code');
    const verifyButton = document.getElementById('waitlist-phone-verify');
    const token = document.getElementById('firebase-id-token');
    const submit = document.getElementById('waitlist-profile-submit');
    const submitText = submit?.querySelector('.waitlist-profile-submit-text');
    const submitLoader = submit?.querySelector('.waitlist-profile-submit-loader');
    const status = document.getElementById('waitlist-phone-status');
    const config = window.wayoutFirebaseConfig || {};
    const messages = window.wayoutPhoneVerificationMessages || {};

    if (!submit || !submitText || !submitLoader) {
        return;
    }

    const defaultSubmitText = submitText.textContent;
    const setProfileSubmitting = (isSubmitting) => {
        submit.disabled = isSubmitting;
        submit.setAttribute('aria-busy', isSubmitting ? 'true' : 'false');
        submitText.textContent = isSubmitting
            ? (submit.dataset.loadingText || defaultSubmitText)
            : defaultSubmitText;
        submitLoader.classList.toggle('hidden', !isSubmitting);
    };

    window.addEventListener('pageshow', () => setProfileSubmitting(false));

    if (!prefix || !number || !sendButton || !codePanel || !code || !verifyButton || !token || !status) {
        form.addEventListener('submit', (event) => {
            if (!event.defaultPrevented && form.checkValidity()) {
                setProfileSubmitting(true);
            }
        });

        return;
    }

    let confirmationResult = null;
    let recaptchaVerifier = null;
    let verifiedPhoneNumber = null;
    let resendTimer = null;

    const e164PhoneNumber = () => `${prefix.value}${number.value.replace(/\D/g, '')}`;
    const isValidPhoneNumber = (value) => /^\+[1-9]\d{6,14}$/.test(value);

    const showStatus = (message, type = 'info') => {
        status.textContent = message;
        status.classList.remove('hidden', 'bg-rose-500/15', 'text-rose-100', 'bg-emerald-500/15', 'text-emerald-100', 'bg-white/10', 'text-slate-200');

        const classes = type === 'error'
            ? ['bg-rose-500/15', 'text-rose-100']
            : type === 'success'
                ? ['bg-emerald-500/15', 'text-emerald-100']
                : ['bg-white/10', 'text-slate-200'];

        status.classList.add(...classes);
    };

    const resetVerification = () => {
        confirmationResult = null;
        verifiedPhoneNumber = null;
        token.value = '';
        code.value = '';
        codePanel.classList.add('hidden');
        submit.disabled = true;
    };

    const resetRecaptcha = () => {
        if (recaptchaVerifier) {
            recaptchaVerifier.clear();
            recaptchaVerifier = null;
        }
    };

    const startResendCooldown = () => {
        let seconds = 30;
        window.clearInterval(resendTimer);
        sendButton.disabled = true;
        sendButton.textContent = `${messages.resendCode} (${seconds}s)`;

        resendTimer = window.setInterval(() => {
            seconds -= 1;

            if (seconds <= 0) {
                window.clearInterval(resendTimer);
                sendButton.disabled = false;
                sendButton.textContent = messages.resendCode;
                return;
            }

            sendButton.textContent = `${messages.resendCode} (${seconds}s)`;
        }, 1000);
    };

    const configured = ['apiKey', 'authDomain', 'projectId', 'appId'].every((key) => Boolean(config[key]));

    if (!configured) {
        sendButton.disabled = true;
        showStatus(messages.configurationError, 'error');
        return;
    }

    const firebaseApp = getApps().length ? getApp() : initializeApp(config);
    const auth = getAuth(firebaseApp);
    auth.languageCode = document.documentElement.lang || 'it';

    [prefix, number].forEach((field) => field.addEventListener('input', () => {
        if (verifiedPhoneNumber !== e164PhoneNumber() || confirmationResult) {
            resetVerification();
        }
    }));

    sendButton.addEventListener('click', async () => {
        const phoneNumber = e164PhoneNumber();

        resetVerification();

        if (!isValidPhoneNumber(phoneNumber)) {
            showStatus(messages.sendError, 'error');
            return;
        }

        sendButton.disabled = true;
        showStatus(messages.verifying);

        try {
            if (!recaptchaVerifier) {
                recaptchaVerifier = new RecaptchaVerifier(auth, 'waitlist-phone-recaptcha', {
                    size: 'invisible',
                });
            }

            confirmationResult = await signInWithPhoneNumber(auth, phoneNumber, recaptchaVerifier);
            codePanel.classList.remove('hidden');
            showStatus(messages.codeSent);
            code.focus();
            startResendCooldown();
        } catch (error) {
            console.error('Firebase phone verification send failed.', error);
            resetRecaptcha();
            sendButton.disabled = false;
            sendButton.textContent = messages.sendCode;
            showStatus(messages.sendError, 'error');
        }
    });

    verifyButton.addEventListener('click', async () => {
        if (!confirmationResult || !/^\d{6}$/.test(code.value.trim())) {
            showStatus(messages.codeError, 'error');
            return;
        }

        verifyButton.disabled = true;
        showStatus(messages.verifying);

        try {
            const credential = await confirmationResult.confirm(code.value.trim());
            token.value = await credential.user.getIdToken(true);
            verifiedPhoneNumber = e164PhoneNumber();
            prefix.disabled = true;
            number.disabled = true;
            sendButton.disabled = true;
            code.disabled = true;
            verifyButton.disabled = true;
            submit.disabled = false;
            showStatus(messages.verified, 'success');
            await signOut(auth);
        } catch (error) {
            console.error('Firebase phone verification confirmation failed.', error);
            verifyButton.disabled = false;
            showStatus(messages.codeError, 'error');
        }
    });

    code.addEventListener('input', () => {
        code.value = code.value.replace(/\D/g, '').slice(0, 6);
    });

    form.addEventListener('submit', (event) => {
        if (!token.value || verifiedPhoneNumber !== e164PhoneNumber()) {
            event.preventDefault();
            showStatus(messages.required, 'error');
            return;
        }

        // Disabled fields are not submitted by browsers; enable them after the final check.
        prefix.disabled = false;
        number.disabled = false;
        setProfileSubmitting(true);
    });
};

document.addEventListener('DOMContentLoaded', setupWaitlistPhoneVerification);
