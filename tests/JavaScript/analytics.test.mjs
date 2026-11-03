import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../resources/js/analytics.js', import.meta.url), 'utf8');

function browser({marketing, search = '', fbc, pageEvent, fetch} = {}) {
    const cookies = new Map();
    const handlers = new Map();
    const scripts = [];
    const storage = new Map();
    if (marketing !== undefined) cookies.set('wayout_cookie_consent', encodeURIComponent(JSON.stringify({version: 2, analytics: false, marketing})));
    if (fbc) cookies.set('_fbc', fbc);
    const classes = {add() {}, remove() {}};
    const button = (id) => ({addEventListener: (name, callback) => handlers.set(`${id}:${name}`, callback)});
    const document = {
        get cookie() { return [...cookies].map(([key, value]) => `${key}=${value}`).join('; '); },
        set cookie(value) {
            const [pair] = value.split(';');
            const index = pair.indexOf('=');
            const key = pair.slice(0, index);
            if (value.includes('Max-Age=0')) cookies.delete(key);
            else cookies.set(key, pair.slice(index + 1));
        },
        getElementById(id) {
            if (id === 'wayout-analytics-config') return {dataset: {
                enabled: '1', consentVersion: '2', metaPixelId: '1234567890',
                ...(fetch ? {consentEndpoint: '/cookie-consent', leadEndpoint: '/analytics/lead'} : {}),
            }};
            if (id.startsWith('cookie-consent-')) return {...button(id), classList: classes, remove() {}};
            return null;
        },
        querySelector() { return fetch ? {content: 'csrf-test'} : null; },
        querySelectorAll(selector) { return selector === '[data-analytics-page-event]' && pageEvent ? [{dataset: pageEvent}] : []; },
        createElement() { return {}; },
        head: {appendChild: (script) => scripts.push(script)},
        body: {classList: classes},
        addEventListener() {}, dispatchEvent() {},
    };
    const window = {crypto: {randomUUID: () => '00000000-0000-4000-8000-000000000001'}};
    const location = {search, protocol: 'https:', hostname: 'www.wayoutapp.it', reload() {}};
    const context = {document, window, location, URLSearchParams, console, fetch, CustomEvent: class {},
        localStorage: {getItem: (key) => storage.get(key), setItem: (key, value) => storage.set(key, value)},
        sessionStorage: {getItem: (key) => storage.get(key), setItem: (key, value) => storage.set(key, value)}};
    vm.runInNewContext(source, context);
    return {cookies, scripts, window, click: (id) => handlers.get(`${id}:click`)()};
}

test('click attribution is only persisted and Meta loaded after marketing consent', () => {
    const state = browser({search: '?fbclid=Real_Click-123'});
    assert.equal(state.cookies.has('_fbc'), false);
    assert.equal(state.scripts.length, 0);
    state.click('cookie-consent-accept');
    assert.match(state.cookies.get('_fbc'), /^fb\.1\.\d{13}\.Real_Click-123$/);
    assert.equal(state.scripts.length, 1);
});

test('organic, refused and malformed clicks never fabricate fbc', () => {
    for (const options of [{marketing: true}, {marketing: false, search: '?fbclid=Real'},
        {marketing: true, search: '?fbclid=bad.click'}, {marketing: true, search: '?fbclid=' + 'x'.repeat(451)}]) {
        assert.equal(browser(options).cookies.has('_fbc'), false);
    }
});

test('the same click keeps its original timestamp and a new click updates attribution', () => {
    const fbc = 'fb.1.1780000000123.Real_Click-123';
    assert.equal(browser({marketing: true, search: '?fbclid=Real_Click-123', fbc}).cookies.get('_fbc'), fbc);
    assert.match(browser({marketing: true, search: '?fbclid=New_Click', fbc}).cookies.get('_fbc'), /^fb\.1\.\d{13}\.New_Click$/);
});

test('Lead uses the server event ID, including when consent is granted later', () => {
    const state = browser({pageEvent: {
        analyticsPageEvent: 'waitlist_email_verified', analyticsEventId: 'lead_shared',
        analyticsDedupe: 'lead_shared', analyticsDedupeScope: 'local',
    }});
    assert.equal(state.window.fbq, undefined);
    state.click('cookie-consent-accept');
    const lead = state.window.fbq.queue.find((args) => args[1] === 'Lead');
    assert.equal(lead[0], 'track');
    assert.equal(lead[3].eventID, 'lead_shared');
    state.window.wayoutTrack('waitlist_email_verified', {}, {eventId: 'lead_shared', dedupeKey: 'lead_shared', dedupeScope: 'local'});
    assert.equal(state.window.fbq.queue.filter((args) => args[1] === 'Lead').length, 1);
});

test('revoking marketing consent removes click attribution', () => {
    const state = browser({marketing: true, fbc: 'fb.1.1780000000123.Real'});
    state.click('cookie-consent-reject');
    assert.equal(state.cookies.has('_fbc'), false);
});


test('late-consent Lead synchronization waits for the consent audit and uses the shared ID', async () => {
    const requests = [];
    let finishAudit;
    const audit = new Promise((resolve) => { finishAudit = resolve; });
    const state = browser({pageEvent: {analyticsPageEvent: 'waitlist_email_verified', analyticsEventId: 'lead_shared'},
        fetch: (url, options) => {
            requests.push({url, options});
            return url === '/cookie-consent' ? audit : Promise.resolve({ok: true});
        }});
    state.click('cookie-consent-accept');
    assert.equal(requests.length, 1);
    assert.equal(requests[0].url, '/cookie-consent');
    finishAudit({ok: true});
    await new Promise((resolve) => setImmediate(resolve));
    assert.equal(requests[1].url, '/analytics/lead');
    assert.equal(JSON.parse(requests[1].options.body).event_id, 'lead_shared');
    assert.equal(requests[1].options.headers['X-CSRF-TOKEN'], 'csrf-test');
});
