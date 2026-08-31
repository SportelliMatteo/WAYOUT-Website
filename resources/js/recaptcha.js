const scriptPromises = new Map();

const loadRecaptcha = (siteKey) => {
    if (window.grecaptcha?.execute) return Promise.resolve(window.grecaptcha);
    if (scriptPromises.has(siteKey)) return scriptPromises.get(siteKey);

    const promise = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(siteKey)}&trustedtypes=true`;
        script.async = true;
        script.defer = true;
        script.onload = () => window.grecaptcha?.ready(() => resolve(window.grecaptcha));
        script.onerror = () => reject(new Error('Unable to load reCAPTCHA.'));
        document.head.appendChild(script);
    });

    scriptPromises.set(siteKey, promise);

    return promise;
};

document.addEventListener('submit', async (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) return;

    const field = form.querySelector('[data-recaptcha-action]');
    if (!(field instanceof HTMLInputElement)) return;

    if (form.dataset.recaptchaVerified === 'true') {
        delete form.dataset.recaptchaVerified;
        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();

    if (form.dataset.recaptchaPending === 'true') return;

    const siteKey = field.dataset.recaptchaSiteKey || '';
    const action = field.dataset.recaptchaAction || '';
    const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;
    form.dataset.recaptchaPending = 'true';
    submitter?.setAttribute('disabled', 'disabled');

    try {
        if (!siteKey || !action) throw new Error('reCAPTCHA is not configured.');

        const recaptcha = await loadRecaptcha(siteKey);
        field.value = await recaptcha.execute(siteKey, { action });
        form.dataset.recaptchaVerified = 'true';
        submitter?.removeAttribute('disabled');
        if (submitter) {
            form.requestSubmit(submitter);
        } else {
            form.requestSubmit();
        }
    } catch (error) {
        console.error('reCAPTCHA form verification failed.', error);
        window.alert(field.dataset.recaptchaError || 'Unable to complete the anti-spam check.');
    } finally {
        delete form.dataset.recaptchaPending;
        submitter?.removeAttribute('disabled');
    }
}, true);
