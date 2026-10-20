/*
 * Fades sections in as they scroll into view (ported from components/motion/RevealObserver.tsx).
 *
 * - `data-reveal` on an element reveals it; `data-reveal-group` on a list reveals its children one after another
 *   (60ms stagger, capped).
 * - Does nothing for users who prefer reduced motion.
 * - Anything already on screen, or anything that receives keyboard focus, is shown immediately (no animation) so
 *   focus never lands on invisible content.
 */
const REVEALED = 'is-revealed';

export function initReveal() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    if (!('IntersectionObserver' in window)) return;

    const reveal = (el) => el.classList.add(REVEALED);
    // Opt out of the effect entirely: shown at once, nothing to animate.
    const show = (el) => el.removeAttribute('data-reveal');

    document.querySelectorAll('[data-reveal-group]').forEach((group) => {
        Array.from(group.children).forEach((child, i) => {
            child.setAttribute('data-reveal', '');
            child.style.setProperty('--reveal-index', String(Math.min(i, 6)));
        });
    });

    const pending = Array.from(document.querySelectorAll(`[data-reveal]:not(.${REVEALED})`));
    const fold = window.innerHeight;
    for (const el of pending) {
        if (el.getBoundingClientRect().top < fold) show(el);
    }
    document.documentElement.classList.add('js-reveal');

    const io = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    reveal(entry.target);
                    io.unobserve(entry.target);
                }
            }
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.12 },
    );
    pending.filter((el) => el.hasAttribute('data-reveal')).forEach((el) => io.observe(el));

    document.addEventListener('focusin', (e) => {
        const target = e.target?.closest?.(`[data-reveal]:not(.${REVEALED})`);
        if (target) {
            io.unobserve(target);
            show(target);
        }
    });
    window.addEventListener('beforeprint', () => document.querySelectorAll('[data-reveal]').forEach(show));
}
