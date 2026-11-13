import {test} from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {runInNewContext} from 'node:vm';

const source = readFileSync(new URL('../../resources/js/experience.js', import.meta.url), 'utf8');

function setup({reduced = false, observed = false} = {}) {
    let now = 0;
    let sequence = 0;
    let onIntersection;
    const pending = new Map();
    const element = (dataset = {}) => ({
        dataset, events: {}, attributes: {}, hidden: false,
        addEventListener(type, fn) { this.events[type] = fn; },
        setAttribute(key, value) { this.attributes[key] = value; },
        getAttribute(key) { return this.attributes[key]; },
        focus() { this.focused = true; }, animate() {},
    });
    const tabs = [0, 1, 2].map((i) => element({appTab: String(i)}));
    const panels = [0, 1, 2].map((i) => element({appPanel: String(i)}));
    tabs.forEach((tab, i) => tab.setAttribute('aria-selected', String(i === 0)));
    panels.forEach((panel, i) => { panel.hidden = i !== 0; });
    const playback = element({pauseLabel: 'Pause', playLabel: 'Resume'});
    const section = {
        querySelectorAll: (selector) => selector === '[data-app-tab]' ? tabs : panels,
        querySelector: () => playback,
    };
    const document = {...element(), hidden: false,
        querySelector: () => null,
        querySelectorAll: (selector) => selector === '[data-app-tabs]' ? [section] : [],
    };
    const motion = {...element(), matches: reduced};
    const window = {matchMedia: () => motion};
    const Observer = function (callback) { onIntersection = callback; this.observe = () => {}; };
    if (observed) window.IntersectionObserver = Observer;
    runInNewContext(source, {document, window, IntersectionObserver: Observer,
        setTimeout: (fn, delay) => { const id = ++sequence; pending.set(id, {fn, due: now + delay}); return id; },
        clearTimeout: (id) => pending.delete(id),
    });
    const advance = (duration) => {
        const end = now + duration;
        while (true) {
            const next = [...pending].sort((a, b) => a[1].due - b[1].due)[0];
            if (!next || next[1].due > end) break;
            now = next[1].due;
            pending.delete(next[0]);
            next[1].fn();
        }
        now = end;
    };
    return {tabs, panels, playback, document, advance,
        visible: (value) => onIntersection([{isIntersecting: value}]),
        active: () => tabs.findIndex((tab) => tab.getAttribute('aria-selected') === 'true'),
    };
}

test('automatically cycles through all panels and wraps every five seconds', () => {
    const ui = setup();
    ui.advance(5000); assert.equal(ui.active(), 1);
    assert.equal(ui.panels[0].hidden, true); assert.equal(ui.panels[1].hidden, false);
    ui.advance(5000); assert.equal(ui.active(), 2);
    ui.advance(5000); assert.equal(ui.active(), 0);
});

test('click changes the panel immediately and restarts a full timer', () => {
    const ui = setup();
    ui.advance(4000);
    ui.tabs[2].events.click(); assert.equal(ui.active(), 2);
    ui.advance(4999); assert.equal(ui.active(), 2);
    ui.advance(1); assert.equal(ui.active(), 0);
});

test('keyboard navigation stays available and restarts rotation', () => {
    const ui = setup();
    let prevented = false;
    ui.tabs[0].events.keydown({key: 'End', preventDefault: () => { prevented = true; }});
    assert.equal(prevented, true); assert.equal(ui.active(), 2); assert.equal(ui.tabs[2].focused, true);
    ui.advance(5000); assert.equal(ui.active(), 0);
});

test('rotation pauses when the browser tab or section is not visible', () => {
    const ui = setup({observed: true});
    ui.advance(10000); assert.equal(ui.active(), 0);
    ui.visible(true); ui.advance(5000); assert.equal(ui.active(), 1);
    ui.document.hidden = true; ui.document.events.visibilitychange();
    ui.advance(10000); assert.equal(ui.active(), 1);
    ui.document.hidden = false; ui.document.events.visibilitychange();
    ui.advance(5000); assert.equal(ui.active(), 2);
    ui.visible(false); ui.advance(10000); assert.equal(ui.active(), 2);
});

test('reduced motion starts paused while manual selection still works', () => {
    const ui = setup({reduced: true});
    ui.advance(10000); assert.equal(ui.active(), 0);
    ui.tabs[1].events.click(); assert.equal(ui.active(), 1);
    ui.advance(10000); assert.equal(ui.active(), 1);
});
