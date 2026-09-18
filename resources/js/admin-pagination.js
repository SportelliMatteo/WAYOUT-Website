// Each pagination footer follows its table scroll container or legal history list.
// Replace those two siblings only, preserving forms, open details and active tabs.
export function paginationUrl(current, footer, page, perPage) {
    const url = new URL(current);
    const pageName = footer.dataset.adminPagination;
    url.searchParams.set('tab', footer.dataset.paginationTab);
    if (footer.dataset.paginationGroup) url.searchParams.set('legal_group', footer.dataset.paginationGroup);
    if (perPage) {
        url.searchParams.set(perPage.name, perPage.value);
        url.searchParams.delete(pageName);
    } else {
        url.searchParams.set(pageName, page);
    }
    url.hash = footer.id;
    return url;
}

let loading = false;

async function loadPage(footer, url) {
    if (loading) return;
    loading = true;
    footer.setAttribute('aria-busy', 'true');
    footer.querySelector('[data-pagination-error]').hidden = true;
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 20000);
    try {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {Accept: 'text/html'},
            signal: controller.signal,
        });
        if (response.redirected && new URL(response.url).pathname !== url.pathname) {
            window.location.assign(response.url);
            return;
        }
        if (!response.ok) throw new Error('Pagination request failed');
        const html = new DOMParser().parseFromString(await response.text(), 'text/html');
        const replacement = html.getElementById(footer.id);
        const oldContent = footer.previousElementSibling;
        const newContent = replacement?.previousElementSibling;
        if (!replacement?.hasAttribute('data-admin-pagination') || !oldContent || !newContent) {
            throw new Error('Pagination content missing');
        }
        const footerTop = footer.getBoundingClientRect().top;
        const horizontalScroll = oldContent.scrollLeft;
        const selectFocused = document.activeElement?.matches('[data-per-page]') && footer.contains(document.activeElement);
        oldContent.replaceWith(newContent);
        footer.replaceWith(replacement);
        newContent.scrollLeft = horizontalScroll;
        if (selectFocused) replacement.querySelector('[data-per-page]').focus({preventScroll: true});
        // Keep the controls at the same viewport position even if rows change height.
        window.scrollBy({top: replacement.getBoundingClientRect().top - footerTop, left: 0, behavior: 'instant'});
        // Pagination updates the current dashboard URL without adding tab history entries.
        history.replaceState(history.state, '', url);
    } catch (_) {
        footer.querySelector('[data-pagination-error]').hidden = false;
        const select = footer.querySelector('[data-per-page]');
        select.value = [...select.options].find((option) => option.defaultSelected)?.value || select.value;
    } finally {
        clearTimeout(timeout);
        footer.removeAttribute('aria-busy');
        loading = false;
    }
}

document.addEventListener('click', (event) => {
    const link = event.target.closest?.('[data-admin-pagination] a[href]');
    if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const footer = link.closest('[data-admin-pagination]');
    const target = new URL(link.href);
    if (target.origin !== window.location.origin) return;
    event.preventDefault();
    const page = target.searchParams.get(footer.dataset.adminPagination);
    if (page) loadPage(footer, paginationUrl(window.location.href, footer, page));
});

document.addEventListener('change', (event) => {
    const select = event.target.closest?.('[data-admin-pagination] [data-per-page]');
    if (!select) return;
    if (loading) {
        select.value = [...select.options].find((option) => option.defaultSelected)?.value || select.value;
        return;
    }
    const footer = select.closest('[data-admin-pagination]');
    loadPage(footer, paginationUrl(window.location.href, footer, null, {name: select.dataset.perPage, value: select.value}));
});
