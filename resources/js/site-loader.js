const loader = document.querySelector('[data-site-loader]');

if (loader) {
    if (!document.documentElement.classList.contains('wayout-loading')) {
        loader.remove();
    } else {
        const startedAt = performance.now();
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let closing = false;
        let maximumWait;
        const finish = () => {
            if (closing) return;
            closing = true;
            clearTimeout(maximumWait);
            const delay = reducedMotion ? 0 : Math.max(0, 2400 - (performance.now() - startedAt));
            setTimeout(() => {
                clearTimeout(window.wayoutLoaderFallback);
                try { sessionStorage.setItem('wayout-intro-seen', '3'); } catch {}
                loader.classList.add('is-leaving');
                document.documentElement.classList.remove('wayout-loading');
                setTimeout(() => loader.remove(), reducedMotion ? 0 : 1050);
            }, delay);
        };
        maximumWait = setTimeout(finish, 3800);
        if (document.readyState === 'complete') finish();
        else window.addEventListener('load', finish, { once: true });
    }
}
