import {test} from 'node:test';
import assert from 'node:assert/strict';

const listeners = {};
globalThis.document = {addEventListener: (type, listener) => { listeners[type] = listener; }, activeElement: null};
const {paginationUrl} = await import('../../resources/js/admin-pagination.js');

const footerData = {
    id: 'pagination-audit_page',
    dataset: {adminPagination: 'audit_page', paginationTab: 'settings', paginationGroup: ''},
};

test('page links keep current filters and independently updated tables', () => {
    const url = paginationUrl('https://wayout.test/admin?tab=users&orders_page=3&audit_per_page=5&lang=it', footerData, '2');
    assert.equal(url.searchParams.get('orders_page'), '3');
    assert.equal(url.searchParams.get('audit_per_page'), '5');
    assert.equal(url.searchParams.get('audit_page'), '2');
    assert.equal(url.searchParams.get('tab'), 'settings');
    assert.equal(url.searchParams.get('lang'), 'it');
    assert.equal(url.hash, '#pagination-audit_page');
});

test('changing page size resets only its own page and retains the legal group', () => {
    const footer = {id: 'pagination-legal_sales_page', dataset: {adminPagination: 'legal_sales_page', paginationTab: 'legal', paginationGroup: 'commerce'}};
    const url = paginationUrl('https://wayout.test/admin?legal_sales_page=3&orders_page=2', footer, null, {name: 'legal_sales_per_page', value: '20'});
    assert.equal(url.searchParams.has('legal_sales_page'), false);
    assert.equal(url.searchParams.get('orders_page'), '2');
    assert.equal(url.searchParams.get('legal_sales_per_page'), '20');
    assert.equal(url.searchParams.get('legal_group'), 'commerce');
});

function scenario(responseOk = true) {
    const state = {replaced: [], navigations: [], scrolls: [], history: [], error: {hidden: true}};
    const select = {value: '10', options: [{defaultSelected: true, value: '5'}]};
    const incomingContent = {scrollLeft: 0};
    const replacement = {
        previousElementSibling: incomingContent,
        hasAttribute: () => true,
        getBoundingClientRect: () => ({top: 260}),
    };
    const content = {scrollLeft: 85, replaceWith: (element) => state.replaced.push(element)};
    const footer = {
        ...footerData, previousElementSibling: content,
        setAttribute() {}, removeAttribute() {},
        querySelector: (selector) => selector === '[data-pagination-error]' ? state.error : select,
        getBoundingClientRect: () => ({top: 200}),
        replaceWith: (element) => state.replaced.push(element),
    };
    globalThis.window = {
        location: {href: 'https://wayout.test/admin?tab=settings', origin: 'https://wayout.test', assign: (url) => state.navigations.push(url)},
        scrollBy: (options) => state.scrolls.push(options),
    };
    globalThis.history = {state: null, replaceState: (_, __, url) => state.history.push(url)};
    globalThis.DOMParser = class { parseFromString() { return {getElementById: () => replacement}; } };
    globalThis.fetch = async () => ({ok: responseOk, redirected: false, text: async () => '<html></html>'});
    const link = {href: 'https://wayout.test/admin?audit_page=2', closest: () => footer};
    const event = {target: {closest: () => link}, button: 0, preventDefault() { state.prevented = true; }};
    return {state, event, incomingContent, replacement};
}

test('next page replaces only the table and controls, preserving viewport and horizontal scroll without navigation', async () => {
    const {state, event, incomingContent, replacement} = scenario();
    listeners.click(event);
    await new Promise(setImmediate);
    assert.equal(state.prevented, true);
    assert.deepEqual(state.replaced, [incomingContent, replacement]);
    assert.equal(incomingContent.scrollLeft, 85);
    assert.deepEqual(state.scrolls, [{top: 60, left: 0, behavior: 'instant'}]);
    assert.equal(state.history[0].searchParams.get('audit_page'), '2');
    assert.deepEqual(state.navigations, []);
});

test('failed requests preserve the current table and report the error without reloading', async () => {
    const {state, event} = scenario(false);
    listeners.click(event);
    await new Promise(setImmediate);
    assert.equal(state.error.hidden, false);
    assert.deepEqual(state.replaced, []);
    assert.deepEqual(state.navigations, []);
    assert.deepEqual(state.history, []);
});

test('modified clicks keep normal open-in-new-tab behavior', () => {
    const {state, event} = scenario();
    event.ctrlKey = true;
    listeners.click(event);
    assert.equal(state.prevented, undefined);
});
