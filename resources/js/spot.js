// Spot light on hover: a soft glow follows the mouse over photos (.spot) and cards (.spot-card). It only tracks the pointer;
// the glow itself is CSS (resources/css/app.css), which is switched off for touch screens and for "reduce motion".
export function initSpot() {
    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;
    const track = (el, e) => {
        const r = el.getBoundingClientRect();
        el.style.setProperty('--mx', `${e.clientX - r.left}px`);
        el.style.setProperty('--my', `${e.clientY - r.top}px`);
    };
    document.addEventListener('pointermove', (e) => {
        if (!(e.target instanceof Element)) return;
        // A photo inside a card and the card itself each follow the mouse.
        const photo = e.target.closest('.spot');
        const card = e.target.closest('.spot-card');
        if (photo) track(photo, e);
        if (card) track(card, e);
    }, { passive: true });
}
