/*
 * Header behaviour (ported from components/layout/SiteHeader.tsx and CollapsibleSearch.tsx): the shadow on scroll,
 * the "Company" disclosure menu, the mobile menu and the collapsible search. The class strings are the same ones
 * the React components used, so the transitions are identical.
 */
const POPOVER_OPEN = 'visible opacity-100 translate-y-0 duration-(--duration-enter) ease-out-quart'.split(' ');
const POPOVER_CLOSED = 'invisible opacity-0 -translate-y-1.5 duration-(--duration-exit) ease-in-quad pointer-events-none'.split(' ');

function setPopover(panel, open) {
    panel.classList.remove(...(open ? POPOVER_CLOSED : POPOVER_OPEN));
    panel.classList.add(...(open ? POPOVER_OPEN : POPOVER_CLOSED));
}

function swapIcons(button, open) {
    const o = button.querySelector('[data-icon-open]');
    const c = button.querySelector('[data-icon-close]');
    if (o) o.hidden = open;
    if (c) c.hidden = !open;
}

function initScrollShadow(header) {
    const shadow = 'shadow-[0_6px_24px_-12px_rgb(18_32_46/0.25)]';
    const update = () => header.classList.toggle(shadow, window.scrollY > 8);
    update();
    window.addEventListener('scroll', update, { passive: true });
}

function initCompanyMenu() {
    const wrapper = document.querySelector('[data-company-menu]');
    if (!wrapper) return;
    const button = wrapper.querySelector('[data-company-toggle]');
    const panel = wrapper.querySelector('[data-company-panel]');
    const list = wrapper.querySelector('ul');
    const chevron = wrapper.querySelector('[data-chevron]');
    let open = false;

    const set = (next) => {
        open = next;
        button.setAttribute('aria-expanded', String(open));
        setPopover(panel, open);
        list.inert = !open;
        chevron.classList.toggle('rotate-180', open);
    };
    button.addEventListener('click', () => set(!open));
    document.addEventListener('pointerdown', (e) => {
        if (open && !wrapper.contains(e.target)) set(false);
    });
    document.addEventListener('keydown', (e) => {
        if (open && e.key === 'Escape') {
            set(false);
            button.focus();
        }
    });
    wrapper.addEventListener('focusout', (e) => {
        if (open && !wrapper.contains(e.relatedTarget)) set(false);
    });
    wrapper.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => set(false)));
}

function initMobileMenu() {
    const toggle = document.querySelector('[data-mobile-toggle]');
    const panel = document.querySelector('[data-mobile-panel]');
    if (!toggle || !panel) return;
    let open = false;
    const set = (next) => {
        open = next;
        toggle.setAttribute('aria-expanded', String(open));
        setPopover(panel, open);
        panel.inert = !open;
        swapIcons(toggle, open);
    };
    toggle.addEventListener('click', () => set(!open));
    document.addEventListener('keydown', (e) => {
        if (open && e.key === 'Escape') {
            set(false);
            toggle.focus();
        }
    });
}

function initCollapsibleSearch() {
    const wrapper = document.querySelector('[data-collapsible-search]');
    if (!wrapper) return;
    const panel = wrapper.querySelector('[data-search-panel]');
    const input = wrapper.querySelector('[data-search-input]');
    const toggle = wrapper.querySelector('[data-search-toggle]');
    let open = false;

    const set = (next) => {
        open = next;
        toggle.setAttribute('aria-expanded', String(open));
        panel.inert = !open;
        panel.classList.toggle('w-60', open);
        panel.classList.toggle('opacity-100', open);
        panel.classList.toggle('w-0', !open);
        panel.classList.toggle('opacity-0', !open);
        swapIcons(toggle, open);
        if (open) input.focus();
    };
    toggle.addEventListener('click', () => set(!open));
    document.addEventListener('pointerdown', (e) => {
        if (open && !wrapper.contains(e.target) && !input.value) set(false);
    });
    document.addEventListener('keydown', (e) => {
        if (open && e.key === 'Escape') {
            set(false);
            toggle.focus();
        }
    });
}

export function initHeader() {
    const header = document.querySelector('[data-site-header]');
    if (header) initScrollShadow(header);
    initCompanyMenu();
    initMobileMenu();
    initCollapsibleSearch();
}
