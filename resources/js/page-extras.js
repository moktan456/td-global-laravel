/*
 * Small page extras: the reading-progress bar, the back-to-top button, and a UTM tag on links that leave the site.
 */
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

function initProgress() {
    const bar = document.querySelector('[data-scroll-progress]');
    if (!bar) return;
    let ticking = false;
    const update = () => {
        ticking = false;
        const max = document.documentElement.scrollHeight - window.innerHeight;
        bar.style.transform = `scaleX(${max > 0 ? Math.min(1, window.scrollY / max) : 0})`;
    };
    window.addEventListener('scroll', () => {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    }, { passive: true });
    window.addEventListener('resize', update);
    update();
}

function initBackToTop() {
    const button = document.querySelector('[data-back-to-top]');
    if (!button) return;
    const update = () => {
        button.hidden = window.scrollY < window.innerHeight;
    };
    window.addEventListener('scroll', update, { passive: true });
    update();
    button.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: reduceMotion.matches ? 'auto' : 'smooth' });
        document.getElementById('main')?.focus({ preventScroll: true });
    });
}

// Links to other websites get utm_source=<this site>&utm_medium=referral, so the other side can see where visitors came
// from. Added when the link is used, so the page itself, search results and copied addresses stay clean. WhatsApp links
// are left alone (the chat link ignores it and a long address is noisier).
function initOutboundUtm() {
    const tag = (event) => {
        const a = event.target instanceof Element ? event.target.closest('a[href]') : null;
        if (!a || !/^https?:$/.test(a.protocol) || a.hostname === location.hostname) return;
        if (/(^|\.)(wa\.me|whatsapp\.com)$/.test(a.hostname)) return;
        const url = new URL(a.href);
        if (url.searchParams.has('utm_source')) return;
        url.searchParams.set('utm_source', location.hostname);
        url.searchParams.set('utm_medium', 'referral');
        a.href = url.href;
    };
    // pointerdown/keydown run before the browser follows the link; auxclick and contextmenu cover middle click and "copy link".
    ['pointerdown', 'keydown', 'contextmenu'].forEach((type) => document.addEventListener(type, tag, true));
}

export function initPageExtras() {
    initProgress();
    initBackToTop();
    initOutboundUtm();
}
