/* Cost calculator (ported from components/forms/CostCalculator.tsx): the estimate updates as you change an answer. */
const grouping = new Intl.NumberFormat('en-IN', { maximumFractionDigits: 0 });
const nu = (n) => `Nu. ${grouping.format(n)}`;

function estimate(rates, { type, mode, direction, lines }) {
    const base = rates.base[type];
    const extraLines = Math.max(0, lines - rates.includedLines);
    const lineFee = extraLines * rates.perExtraLine;
    const handling = rates.mode[mode];
    const subtotal = base + lineFee + handling;
    const discount = direction === 'export' ? Math.round(subtotal * rates.exportDiscount) : 0;
    return { base, extraLines, lineFee, handling, discount, total: subtotal - discount };
}

export function initCalculator() {
    const root = document.querySelector('[data-calculator]');
    if (!root) return;
    const rates = JSON.parse(root.dataset.rates);
    const form = root.querySelector('form');
    const linesInput = form.elements.lines;
    const $ = (sel) => root.querySelector(sel);

    const render = () => {
        const direction = form.elements.direction.value;
        const type = form.elements.type.value;
        const mode = form.elements.mode.value;
        const raw = linesInput.value;
        const lines = Number(raw);
        const valid = raw !== '' && Number.isInteger(lines) && lines >= 1 && lines <= 500;
        const describedBy = ['calc-lines-hint'];

        $('[data-lines-error]').hidden = valid;
        if (valid) linesInput.removeAttribute('aria-invalid');
        else {
            linesInput.setAttribute('aria-invalid', 'true');
            describedBy.push('calc-lines-error');
        }
        linesInput.setAttribute('aria-describedby', describedBy.join(' '));
        $('[data-result-ok]').hidden = !valid;
        $('[data-result-bad]').hidden = valid;
        $('[data-breakdown]').hidden = !valid;
        if (!valid) return;

        const r = estimate(rates, { type, mode, direction, lines });
        $('[data-total]').textContent = nu(r.total);
        $('[data-type]').textContent = type;
        $('[data-mode]').textContent = mode;
        $('[data-extra-lines]').textContent = r.extraLines;
        $('[data-base]').textContent = nu(r.base);
        $('[data-line-fee]').textContent = nu(r.lineFee);
        $('[data-handling]').textContent = nu(r.handling);
        $('[data-discount-label]').hidden = r.discount <= 0;
        $('[data-discount]').hidden = r.discount <= 0;
        $('[data-discount]').textContent = `−${nu(r.discount)}`;
    };
    form.addEventListener('input', render);
    form.addEventListener('change', render);
    render();
}

/** Certificate viewer: a native <dialog>; a click on the dimmed backdrop closes it (Escape and Close already do). */
export function initCertificates() {
    document.querySelectorAll('li:has(> button[data-open-dialog])').forEach((card) => {
        const dialog = card.querySelector('dialog');
        card.querySelector('[data-open-dialog]').addEventListener('click', () => dialog.showModal());
        dialog.addEventListener('click', (e) => {
            if (e.target === dialog) dialog.close();
        });
    });
}
