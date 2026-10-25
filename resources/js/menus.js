/*
 * Small dropdown menus built on <details data-menu> (no framework): they close when you click somewhere else or press
 * Escape (focus returns to the button), and opening one closes the others. Without JavaScript they still open and close
 * with the button.
 */
export function initMenus() {
    initFoldOnPhone();
    initFindShortcut();
    initDensity();
    initTheme();
    initPlainConfirms();
    document.addEventListener('click', (event) => {
        document.querySelectorAll('details[data-menu][open]').forEach((menu) => {
            if (!menu.contains(event.target)) menu.open = false;
        });
    });
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        const open = document.querySelector('details[data-menu][open]');
        if (!open) return;
        open.open = false;
        open.querySelector('summary')?.focus();
    });
}

/** Filter chips are folded away on phones (unless a stage is chosen) and always open on wider screens. */
function initFoldOnPhone() {
    const wide = window.matchMedia('(min-width: 768px)');
    const apply = () => {
        document.querySelectorAll('details[data-fold-on-phone]').forEach((d) => {
            d.open = wide.matches || d.hasAttribute('data-has-choice');
        });
    };
    apply();
    wide.addEventListener('change', apply);
}

/** Press "/" anywhere in the staff workspace to jump to the "Find a shipment" box (not while typing in a field). */
function initFindShortcut() {
    document.addEventListener('keydown', (event) => {
        if (event.key !== '/' || event.ctrlKey || event.metaKey || event.altKey) return;
        const t = event.target;
        if (t instanceof HTMLElement && (t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName))) return;
        const box = [...document.querySelectorAll('[data-find]')].find((el) => el.offsetParent !== null);
        if (!box) return;
        event.preventDefault();
        box.focus();
    });
}

/** "Compact rows" on the shipment list: a toggle button, remembered per browser (storage can be blocked, so every access is guarded). */
function initDensity() {
    const button = document.querySelector('[data-density-toggle]');
    const read = () => { try { return localStorage.getItem('staff-density'); } catch { return null; } };
    const apply = (compact) => {
        document.documentElement.toggleAttribute('data-density-compact', compact);
        document.documentElement.dataset.density = compact ? 'compact' : 'comfortable';
        button?.setAttribute('aria-pressed', String(compact));
    };
    apply(read() === 'compact');
    button?.addEventListener('click', () => {
        const compact = button.getAttribute('aria-pressed') !== 'true';
        apply(compact);
        try { localStorage.setItem('staff-density', compact ? 'compact' : 'comfortable'); } catch { /* keep the choice for this page only */ }
    });
}

/** Ordinary (non-enhanced) forms can ask first too: data-confirm="Are you sure?". Enhanced forms ask in forms.js. */
function initPlainConfirms() {
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (form instanceof HTMLFormElement && form.dataset.confirm && !form.hasAttribute('data-enhance') && !window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
}

/** Light / dark for the staff area: the head script sets data-theme early; this wires the account-menu buttons and follows the device until someone chooses. */
function initTheme() {
    const root = document.documentElement;
    const buttons = [...document.querySelectorAll('[data-theme-toggle]')];
    const stored = () => { try { const t = localStorage.getItem('staff-theme'); return t === 'light' || t === 'dark' ? t : null; } catch { return null; } };
    const show = () => buttons.forEach((b) => {
        const dark = root.dataset.theme === 'dark';
        b.querySelector('[data-theme-label]').textContent = dark ? 'Light mode' : 'Dark mode';
    });
    show();
    buttons.forEach((b) => b.addEventListener('click', () => {
        const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
        root.dataset.theme = next;
        try { localStorage.setItem('staff-theme', next); } catch { /* applies to this page only */ }
        show();
    }));
    window.matchMedia?.('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (stored() || !root.dataset.theme) return;
        root.dataset.theme = e.matches ? 'dark' : 'light';
        show();
    });
}
