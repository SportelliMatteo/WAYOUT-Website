const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const revealElements = document.querySelectorAll('[data-reveal]');
if (!reducedMotion.matches && 'IntersectionObserver' in window && revealElements.length) {
    document.documentElement.classList.add('motion-ready');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.12 });
    revealElements.forEach((element) => observer.observe(element));
}

const hero = document.querySelector('[data-app-hero]');
if (hero && !reducedMotion.matches) {
    let frame = null;
    const update = () => {
        frame = null;
        if (reducedMotion.matches) return;
        const bounds = hero.getBoundingClientRect();
        if (bounds.bottom < 0) return;
        hero.style.setProperty('--app-parallax', `${Math.min(100, Math.max(0, -bounds.top * 0.15))}px`);
    };
    window.addEventListener('scroll', () => {
        if (!frame) frame = requestAnimationFrame(update);
    }, { passive: true });
    update();
}

document.querySelectorAll('[data-app-tabs]').forEach((section) => {
    const tabs = [...section.querySelectorAll('[data-app-tab]')];
    const panels = [...section.querySelectorAll('[data-app-panel]')];
    const activate = (selected, focus = false) => {
        const previous = tabs.find((tab) => tab.getAttribute('aria-selected') === 'true');
        tabs.forEach((tab) => {
            const active = tab === selected;
            tab.setAttribute('aria-selected', String(active));
            tab.tabIndex = active ? 0 : -1;
        });
        panels.forEach((panel) => {
            const active = panel.dataset.appPanel === selected.dataset.appTab;
            panel.hidden = !active;
            if (active && previous !== selected && !reducedMotion.matches) {
                panel.animate([
                    { opacity: 0, transform: 'translateY(12px)' },
                    { opacity: 1, transform: 'translateY(0)' },
                ], { duration: 400, easing: 'cubic-bezier(.16,1,.3,1)' });
            }
        });
        if (focus) selected.focus();
    };
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activate(tab));
        tab.addEventListener('keydown', (event) => {
            let next = index;
            if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
            else if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
            else if (event.key === 'Home') next = 0;
            else if (event.key === 'End') next = tabs.length - 1;
            else return;
            event.preventDefault();
            activate(tabs[next], true);
        });
    });
});
