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
    pending(form, true);
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Enhance': '1', 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
            credentials: 'same-origin',
        });
        if (!response.ok) throw new Error(String(response.status));
        const html = await response.text();
        const holder = document.createElement('div');
        holder.innerHTML = html.trim();
        const next = holder.firstElementChild;
        form.replaceWith(next);
        bindForm(next);
        focusStatus(next);
    } catch {
        pending(form, false);
        form.submit(); // let the server render the page with the result instead
    }
}

export function bindForm(form) {
    if (form.matches('[data-enhance]')) form.addEventListener('submit', (e) => submit(form, e));
    form.querySelectorAll('[data-password-toggle]').forEach(bindPasswordToggle);
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
    document.querySelectorAll('[data-password-toggle]').forEach((b) => {
        if (!b.closest('form[data-enhance]')) bindPasswordToggle(b);
    });
    // A page that came back from a normal (non-script) submit: move focus to the result, like the React forms did.
    if (document.querySelector('[data-form-status]')) focusStatus(document);
}
