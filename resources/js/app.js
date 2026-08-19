import { getApp, getApps, initializeApp } from 'firebase/app';
import {
    getAuth,
    RecaptchaVerifier,
    signInWithPhoneNumber,
    signOut,
} from 'firebase/auth';

const setupWaitlistPhoneVerification = () => {
    const form = document.getElementById('waitlist-join-form');
    const prefix = document.getElementById('waitlist-phone-prefix');
    const number = document.getElementById('waitlist-phone-number');
    const sendButton = document.getElementById('waitlist-phone-send');
    const codePanel = document.getElementById('waitlist-phone-code-panel');
    const code = document.getElementById('waitlist-phone-code');
    const verifyButton = document.getElementById('waitlist-phone-verify');
    const resendButton = document.getElementById('waitlist-phone-resend');
    const codeStatus = document.getElementById('waitlist-phone-code-status');
    const codeClose = document.getElementById('waitlist-phone-code-close');
    const codeBackdrop = document.getElementById('waitlist-phone-code-backdrop');
    const token = document.getElementById('waitlist-firebase-id-token');
    const submit = document.getElementById('waitlist-join-submit');
    const status = document.getElementById('waitlist-phone-status');

    if (!form || !prefix || !number || !submit) return;
    if (!sendButton || !codePanel || !code || !verifyButton || !resendButton || !codeStatus || !token || !status) return;

    const config = window.wayoutFirebaseConfig || {};
    const messages = window.wayoutPhoneVerificationMessages || {};
    let confirmationResult = null;
    let recaptchaVerifier = null;
    let resendTimer = null;
    let resendSecondsRemaining = 0;

    const phoneNumber = () => `${prefix.value}${number.value.replace(/\D/g, '')}`;
    const isValidPhoneNumber = (value) => /^\+[1-9]\d{6,14}$/.test(value);

    const showStatus = (message, type = 'info', target = status) => {
        target.textContent = message;
        target.classList.remove('hidden', 'bg-rose-500/15', 'text-rose-100', 'bg-emerald-500/15', 'text-emerald-100', 'bg-white/10', 'text-slate-200');
        target.classList.add(...(type === 'error'
            ? ['bg-rose-500/15', 'text-rose-100']
            : type === 'success'
                ? ['bg-emerald-500/15', 'text-emerald-100']
                : ['bg-white/10', 'text-slate-200']));
    };

    const resetVerification = () => {
        confirmationResult = null;
        token.value = '';
        code.value = '';
        codePanel.classList.add('hidden');
        codeStatus.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        verifyButton.disabled = false;
    };

    const openCodePanel = () => {
        status.classList.add('hidden');
        codePanel.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        showStatus(messages.codeSent, 'info', codeStatus);
        code.focus();
    };

    const closeCodePanel = () => {
        resetVerification();
    };

    const resetRecaptcha = () => {
        recaptchaVerifier?.clear();
        recaptchaVerifier = null;
    };

    const startResendCooldown = () => {
        resendSecondsRemaining = 60;
        window.clearInterval(resendTimer);
        sendButton.disabled = true;
        resendButton.disabled = true;
        resendButton.textContent = `${messages.resendCode} (${resendSecondsRemaining}s)`;

        resendTimer = window.setInterval(() => {
            resendSecondsRemaining -= 1;
            if (resendSecondsRemaining <= 0) {
                window.clearInterval(resendTimer);
                sendButton.disabled = false;
                resendButton.disabled = false;
                resendButton.textContent = messages.resendCode;
                return;
            }
            resendButton.textContent = `${messages.resendCode} (${resendSecondsRemaining}s)`;
        }, 1000);
    };

    if (!['apiKey', 'authDomain', 'projectId', 'appId'].every((key) => Boolean(config[key]))) {
        sendButton.disabled = true;
        showStatus(messages.configurationError, 'error');
        return;
    }

    const firebaseApp = getApps().length ? getApp() : initializeApp(config);
    const auth = getAuth(firebaseApp);
    auth.languageCode = document.documentElement.lang || 'it';

    [prefix, number].forEach((field) => field.addEventListener('input', resetVerification));

    sendButton.addEventListener('click', async () => {
        const e164PhoneNumber = phoneNumber();
        resetVerification();

        if (!isValidPhoneNumber(e164PhoneNumber)) {
            showStatus(messages.sendError, 'error');
            return;
        }

        sendButton.disabled = true;
        showStatus(messages.verifying);

        try {
            recaptchaVerifier ??= new RecaptchaVerifier(auth, 'waitlist-phone-recaptcha', { size: 'invisible' });
            confirmationResult = await signInWithPhoneNumber(auth, e164PhoneNumber, recaptchaVerifier);
            openCodePanel();
            startResendCooldown();
        } catch (error) {
            console.error('Firebase phone verification send failed.', error);
            resetRecaptcha();
            sendButton.disabled = false;
            sendButton.textContent = messages.sendCode;
            showStatus(messages.sendError, 'error');
        }
    });

    resendButton.addEventListener('click', async () => {
        if (resendSecondsRemaining > 0 || !isValidPhoneNumber(phoneNumber())) return;

        resendButton.disabled = true;
        verifyButton.disabled = true;
        showStatus(messages.verifying, 'info', codeStatus);

        try {
            resetRecaptcha();
            recaptchaVerifier = new RecaptchaVerifier(auth, 'waitlist-phone-recaptcha', { size: 'invisible' });
            confirmationResult = await signInWithPhoneNumber(auth, phoneNumber(), recaptchaVerifier);
            code.value = '';
            verifyButton.disabled = false;
            showStatus(messages.codeSent, 'info', codeStatus);
            code.focus();
            startResendCooldown();
        } catch (error) {
            console.error('Firebase phone verification resend failed.', error);
            resetRecaptcha();
            verifyButton.disabled = false;
            resendButton.disabled = false;
            resendButton.textContent = messages.resendCode;
            showStatus(messages.sendError, 'error', codeStatus);
        }
    });

    verifyButton.addEventListener('click', async () => {
        if (!confirmationResult || !/^\d{6}$/.test(code.value.trim())) {
            showStatus(messages.codeError, 'error', codeStatus);
            return;
        }

        verifyButton.disabled = true;
        showStatus(messages.verifying, 'info', codeStatus);

        try {
            const credential = await confirmationResult.confirm(code.value.trim());
            token.value = await credential.user.getIdToken(true);
            showStatus(messages.verified, 'success', codeStatus);
            await signOut(auth);
            codePanel.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            submit.disabled = false;
            form.requestSubmit(submit);
        } catch (error) {
            console.error('Firebase phone verification confirmation failed.', error);
            verifyButton.disabled = false;
            showStatus(messages.codeError, 'error', codeStatus);
        }
    });

    code.addEventListener('input', () => {
        code.value = code.value.replace(/\D/g, '').slice(0, 6);
    });

    codeClose?.addEventListener('click', closeCodePanel);
    codeBackdrop?.addEventListener('click', closeCodePanel);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !codePanel.classList.contains('hidden')) closeCodePanel();
    });

    form.addEventListener('submit', (event) => {
        if (!token.value) {
            event.preventDefault();
            showStatus(messages.required, 'error');
        }
    });
};

const setupProfileSubmittingState = () => {
    const form = document.getElementById('waitlist-profile-form');
    const submit = document.getElementById('waitlist-profile-submit');
    const text = submit?.querySelector('.waitlist-profile-submit-text');
    const loader = submit?.querySelector('.waitlist-profile-submit-loader');

    if (!form || !submit || !text || !loader) return;

    const defaultText = text.textContent;
    const setSubmitting = (submitting) => {
        submit.disabled = submitting;
        submit.setAttribute('aria-busy', submitting ? 'true' : 'false');
        text.textContent = submitting ? (submit.dataset.loadingText || defaultText) : defaultText;
        loader.classList.toggle('hidden', !submitting);
    };

    form.addEventListener('submit', (event) => {
        if (!event.defaultPrevented && form.checkValidity()) setSubmitting(true);
    });
    window.addEventListener('pageshow', () => setSubmitting(false));
};

document.addEventListener('DOMContentLoaded', () => {
    setupWaitlistPhoneVerification();
    setupProfileSubmittingState();
});
