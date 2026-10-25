/*
 * Forms (replaces React's useActionState + useFormStatus): a form marked `data-enhance` submits in the background and
 * the server answers with the re-rendered form (same markup, same error summary), which is swapped in place and the
 * status message gets focus. Without JavaScript the form posts normally and the page comes back with the same state.
 */
function pending(form, on) {
    form.querySelectorAll('[data-submit]').forEach((b) => {
        b.disabled = on;
        b.setAttribute('aria-disabled', String(on));
        b.querySelector('[data-submit-label]').hidden = on;
        b.querySelector('[data-submit-pending]').hidden = !on;
    });
}

function focusStatus(root) {
    root.querySelector('[data-form-status]')?.focus();
}

async function submit(form, event) {
    event.preventDefault();
    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) return;
    pending(form, true);
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Enhance': '1', 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
            credentials: 'same-origin',
        });
        if (!response.ok) throw new Error(String(response.status));
        const redirect = response.headers.get('X-Redirect');
        if (redirect) {
            window.location.assign(redirect);
            return;
        }
        const html = await response.text();
        const holder = document.createElement('div');
        holder.innerHTML = html.trim();
        const next = holder.firstElementChild;
        // A form can sit inside a wrapper that also shows its result (the Track page): swap the whole wrapper.
        const root = form.closest('[data-enhance-root]') ?? form;
        root.replaceWith(next);
        bindAll(next);
        focusStatus(next);
        if (form.hasAttribute('data-refresh')) refreshLive();
    } catch {
        pending(form, false);
        form.submit(); // let the server render the page with the result instead
    }
}

/**
 * After a save, the rest of the page (title-bar stage badges, track tabs, timeline) may be out of date. Re-fetch the
 * page and swap just the regions marked `data-live`; the forms are left alone so nobody loses typing or focus.
 */
async function refreshLive() {
    try {
        const response = await fetch(window.location.href, { credentials: 'same-origin', headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) return;
        const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
        document.querySelectorAll('[data-live]').forEach((el) => {
            const fresh = doc.querySelector(`[data-live="${el.dataset.live}"]`);
            if (fresh) el.replaceWith(fresh);
            else el.remove();
        });
    } catch {
        // The page stays as it was; the next load shows the latest.
    }
}

export function bindForm(form) {
    if (form.matches('[data-enhance]')) form.addEventListener('submit', (e) => submit(form, e));
    form.querySelectorAll('[data-password-toggle]').forEach(bindPasswordToggle);
}

/** Binds every enhanced form inside (or equal to) an element that was just swapped in. */
function bindAll(el) {
    if (el.matches('form[data-enhance]')) bindForm(el);
    el.querySelectorAll('form[data-enhance]').forEach(bindForm);
    bindCopyButtons(el);
}

/** "Copy link" buttons (the Telegram connect link on the Clients page). */
export function bindCopyButtons(root = document) {
    root.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            const field = document.getElementById(button.dataset.copy);
            const status = button.parentElement.querySelector('[data-copy-status]');
            try {
                await navigator.clipboard.writeText(field.value);
                status.textContent = 'Copied.';
            } catch {
                status.textContent = ''; // The link stays selectable in the field.
            }
        });
    });
    root.querySelectorAll('[data-select-on-focus]').forEach((field) => field.addEventListener('focus', () => field.select()));
}

/** Show / hide a password (WCAG: an eye button with a state, never a placeholder-only label). */
export function bindPasswordToggle(button) {
    const input = document.getElementById(button.getAttribute('aria-controls'));
    const show = button.querySelector('[data-icon-show]');
    const hide = button.querySelector('[data-icon-hide]');
    button.addEventListener('click', () => {
        const shown = input.type === 'text';
        input.type = shown ? 'password' : 'text';
        button.setAttribute('aria-pressed', String(!shown));
        button.setAttribute('aria-label', shown ? 'Show password' : 'Hide password');
        show.hidden = !shown;
        hide.hidden = shown;
    });
}

export function initForms() {
    document.querySelectorAll('form[data-enhance]').forEach(bindForm);
    bindCopyButtons();
    document.querySelectorAll('[data-password-toggle]').forEach((b) => {
        if (!b.closest('form[data-enhance]')) bindPasswordToggle(b);
    });
    // A page that came back from a normal (non-script) submit: move focus to the result, like the React forms did.
    if (document.querySelector('[data-form-status]')) focusStatus(document);
}
