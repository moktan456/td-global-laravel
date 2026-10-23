/*
 * Small dropdown menus built on <details data-menu> (no framework): they close when you click somewhere else or press
 * Escape (focus returns to the button), and opening one closes the others. Without JavaScript they still open and close
 * with the button.
 */
export function initMenus() {
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
