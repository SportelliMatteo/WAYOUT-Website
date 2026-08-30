const configElement = document.getElementById('wayout-analytics-config');

if (configElement) {
    const config = {
        enabled: configElement.dataset.enabled === '1',
        consentVersion: Number(configElement.dataset.consentVersion || 1),
        consentDays: Number(configElement.dataset.consentDays || 180),
        gtmId: configElement.dataset.gtmId || '',
        ga4Id: configElement.dataset.ga4Id || '',
        metaPixelId: configElement.dataset.metaPixelId || '',
        debug: configElement.dataset.debug === '1',
        consentEndpoint: configElement.dataset.consentEndpoint || '',
    };
    const cookieName = 'wayout_cookie_consent';
    let consent = null;
    let googleLoaded = false;
    let metaLoaded = false;
    let pendingEvents = [];

    const log = (...values) => config.debug && console.info('[WAYOUT analytics]', ...values);
    const readCookie = (name) => document.cookie.split('; ').find((row) => row.startsWith(`${name}=`))?.split('=').slice(1).join('=');
    const readConsent = () => {
        try {
            const parsed = JSON.parse(decodeURIComponent(readCookie(cookieName) || ''));
            return parsed.version === config.consentVersion
                && typeof parsed.analytics === 'boolean'
                && typeof parsed.marketing === 'boolean' ? parsed : null;
        } catch (_) {
            return null;
        }
    };
    const writeConsent = (value) => {
        const maxAge = Math.max(1, config.consentDays) * 86400;
        const secure = location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = `${cookieName}=${encodeURIComponent(JSON.stringify(value))}; Path=/; Max-Age=${maxAge}; SameSite=Lax${secure}`;
    };
    const consentId = () => {
        if (consent?.consentId) return consent.consentId;
        if (window.crypto?.randomUUID) return window.crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (character) => {
            const random = Math.floor(Math.random() * 16);
            return (character === 'x' ? random : (random & 0x3) | 0x8).toString(16);
        });
    };
    const recordConsent = (value) => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!config.consentEndpoint || !csrfToken) return;
        fetch(config.consentEndpoint, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: true,
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken},
            body: JSON.stringify({
                consent_id: value.consentId,
                consent_version: value.version,
                analytics: value.analytics,
                marketing: value.marketing,
            }),
        }).catch((error) => log('consent audit unavailable', error));
    };
    const deleteCookie = (name, domain = '') => {
        document.cookie = `${name}=; Path=/; Max-Age=0; SameSite=Lax`;
        if (domain) document.cookie = `${name}=; Path=/; Domain=${domain}; Max-Age=0; SameSite=Lax`;
    };
    const clearTrackingCookies = () => {
        const rootDomain = location.hostname.split('.').slice(-2).join('.');
        document.cookie.split(';').map((part) => part.trim().split('=')[0]).forEach((name) => {
            if (name === '_fbp' || name === '_fbc' || name === '_ga' || name.startsWith('_ga_')) {
                deleteCookie(name);
                if (rootDomain.includes('.')) deleteCookie(name, `.${rootDomain}`);
            }
        });
    };

    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || function gtag(){ window.dataLayer.push(arguments); };
    window.gtag('consent', 'default', {
        analytics_storage: 'denied',
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied',
        wait_for_update: 500,
    });

    const loadScript = (id, src) => {
        if (document.getElementById(id)) return;
        const script = document.createElement('script');
        script.id = id;
        script.async = true;
        script.src = src;
        document.head.appendChild(script);
    };
    const loadGoogle = () => {
        if (googleLoaded || !config.enabled || !consent?.analytics) return;
        googleLoaded = true;
        window.gtag('consent', 'update', {
            analytics_storage: 'granted',
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
        });

        if (/^GTM-[A-Z0-9]+$/i.test(config.gtmId)) {
            window.dataLayer.push({'gtm.start': Date.now(), event: 'gtm.js'});
            loadScript('wayout-gtm', `https://www.googletagmanager.com/gtm.js?id=${encodeURIComponent(config.gtmId)}`);
            log('GTM loaded', config.gtmId);
        } else if (/^G-[A-Z0-9]+$/i.test(config.ga4Id)) {
            loadScript('wayout-ga4', `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(config.ga4Id)}`);
            window.gtag('js', new Date());
            window.gtag('config', config.ga4Id, {
                anonymize_ip: true,
                allow_google_signals: false,
                allow_ad_personalization_signals: false,
                debug_mode: config.debug,
            });
            log('GA4 loaded', config.ga4Id);
        }
    };
    const loadMeta = () => {
        if (metaLoaded || !config.enabled || !consent?.marketing || !/^\d{5,30}$/.test(config.metaPixelId)) return;
        metaLoaded = true;
        if (!window.fbq) {
            const fbq = window.fbq = function(){ fbq.callMethod ? fbq.callMethod.apply(fbq, arguments) : fbq.queue.push(arguments); };
            window._fbq = fbq;
            fbq.push = fbq;
            fbq.queue = [];
            fbq.loaded = true;
            fbq.version = '2.0';
        }
        loadScript('wayout-meta-pixel', 'https://connect.facebook.net/en_US/fbevents.js');
        window.fbq('consent', 'grant');
        window.fbq('set', 'autoConfig', false, config.metaPixelId);
        window.fbq('init', config.metaPixelId);
        window.fbq('track', 'PageView');
        log('Meta Pixel loaded', config.metaPixelId);
    };
    const applyConsent = () => {
        if (!consent) return;
        if (consent.analytics) loadGoogle();
        if (consent.marketing) loadMeta();
    };

    const cleanParams = (params = {}) => Object.fromEntries(Object.entries(params).filter(([, value]) =>
        ['string', 'number', 'boolean'].includes(typeof value) && value !== ''
    ));
    const dedupeStorage = (scope) => scope === 'local' ? localStorage : sessionStorage;
    const isDuplicate = (key, scope) => {
        if (!key) return false;
        try {
            return dedupeStorage(scope).getItem(`wayout_event_${key}`) === '1';
        } catch (_) {
            return false;
        }
    };
    const markDelivered = (key, scope) => {
        if (!key) return;
        try {
            dedupeStorage(scope).setItem(`wayout_event_${key}`, '1');
        } catch (_) {
            // Tracking still works when browser storage is unavailable.
        }
    };
    window.wayoutTrack = (eventName, params = {}, options = {}) => {
        if (!config.enabled || !/^[a-z][a-z0-9_]{1,39}$/.test(eventName)) return;
        if (isDuplicate(options.dedupeKey, options.dedupeScope)) return;
        const safeParams = cleanParams(params);
        if (!consent) {
            pendingEvents.push([eventName, safeParams, options]);
            return;
        }
        let delivered = false;
        if (consent?.analytics) {
            if (/^GTM-[A-Z0-9]+$/i.test(config.gtmId)) {
                window.dataLayer.push({event: eventName, ...safeParams});
                delivered = true;
            } else if (/^G-[A-Z0-9]+$/i.test(config.ga4Id)) {
                window.gtag('event', eventName, safeParams);
                delivered = true;
            }
        }
        if (consent?.marketing && window.fbq) {
            const standard = {
                purchase: 'Purchase',
                begin_checkout: 'InitiateCheckout',
                waitlist_email_verified: 'Lead',
                contact_submitted: 'Contact',
            }[eventName];
            window.fbq(standard ? 'track' : 'trackCustom', standard || eventName, safeParams);
            delivered = true;
        }
        if (delivered) markDelivered(options.dedupeKey, options.dedupeScope);
        log('event', eventName, safeParams);
    };

    const banner = document.getElementById('cookie-consent-banner');
    const preferences = document.getElementById('cookie-preferences-modal');
    const analyticsInput = document.getElementById('cookie-consent-analytics');
    const marketingInput = document.getElementById('cookie-consent-marketing');
    const openPreferences = () => {
        analyticsInput.checked = consent?.analytics || false;
        marketingInput.checked = consent?.marketing || false;
        preferences?.classList.remove('hidden');
        preferences?.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        document.getElementById('cookie-preferences-close')?.focus();
    };
    const closePreferences = () => {
        preferences?.classList.add('hidden');
        preferences?.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };
    const save = (analytics, marketing) => {
        const mustReload = (consent?.analytics && !analytics) || (consent?.marketing && !marketing);
        consent = {consentId: consentId(), version: config.consentVersion, necessary: true, analytics, marketing, updatedAt: new Date().toISOString()};
        writeConsent(consent);
        recordConsent(consent);
        banner?.remove();
        closePreferences();
        applyConsent();
        if (analytics || marketing) {
            const queuedEvents = pendingEvents;
            pendingEvents = [];
            queuedEvents.forEach(([eventName, params, options]) => window.wayoutTrack(eventName, params, options));
        } else {
            pendingEvents = [];
        }
        document.dispatchEvent(new CustomEvent('wayout:consent-updated', {detail: consent}));
        if (mustReload) {
            clearTrackingCookies();
            location.reload();
        }
    };

    document.getElementById('cookie-consent-accept')?.addEventListener('click', () => save(true, true));
    document.getElementById('cookie-consent-reject')?.addEventListener('click', () => save(false, false));
    document.getElementById('cookie-consent-close')?.addEventListener('click', () => save(false, false));
    document.getElementById('cookie-consent-customize')?.addEventListener('click', openPreferences);
    document.getElementById('cookie-preferences-open')?.addEventListener('click', openPreferences);
    document.getElementById('cookie-preferences-close')?.addEventListener('click', closePreferences);
    document.getElementById('cookie-preferences-backdrop')?.addEventListener('click', closePreferences);
    document.getElementById('cookie-preferences-reject')?.addEventListener('click', () => save(false, false));
    document.getElementById('cookie-preferences-save')?.addEventListener('click', () => save(analyticsInput.checked, marketingInput.checked));
    document.getElementById('cookie-preferences-accept')?.addEventListener('click', () => save(true, true));
    document.addEventListener('keydown', (event) => event.key === 'Escape' && closePreferences());

    consent = readConsent();
    if (consent) {
        banner?.remove();
        applyConsent();
    } else {
        banner?.classList.remove('hidden');
    }

    document.querySelectorAll('[data-analytics-event]').forEach((element) => {
        const trigger = element.dataset.analyticsTrigger || (element.tagName === 'FORM' ? 'submit' : 'click');
        element.addEventListener(trigger, () => window.wayoutTrack(element.dataset.analyticsEvent, {
            location: element.dataset.analyticsLocation || location.pathname,
            plan: element.dataset.analyticsPlan || '',
        }));
    });
    document.querySelectorAll('[data-analytics-page-event]').forEach((element) => {
        try {
            const params = JSON.parse(element.dataset.analyticsParams || '{}');
            window.wayoutTrack(element.dataset.analyticsPageEvent, params, {
                dedupeKey: element.dataset.analyticsDedupe,
                dedupeScope: element.dataset.analyticsDedupeScope,
            });
        } catch (error) {
            log('invalid event parameters', element.dataset.analyticsPageEvent, error);
        }
    });
}
