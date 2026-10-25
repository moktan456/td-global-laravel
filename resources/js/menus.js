/*
 * Small dropdown menus built on <details data-menu> (no framework): they close when you click somewhere else or press
 * Escape (focus returns to the button), and opening one closes the others. Without JavaScript they still open and close
 * with the button.
 */
export function initMenus() {
    initFoldOnPhone();
    initFindShortcut();
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
