/* Home page carousels (ported from components/home/HeroCarousel.tsx, ServiceCarousel.tsx, ArticleCarousel.tsx). */

const INTERVAL = 6000;
const SLIDE_ACTIVE = ['opacity-100'];
const SLIDE_INACTIVE = ['pointer-events-none', 'opacity-0'];

function showSlide(slide, active) {
    slide.inert = !active;
    slide.classList.remove(...(active ? SLIDE_INACTIVE : SLIDE_ACTIVE));
    slide.classList.add(...(active ? SLIDE_ACTIVE : SLIDE_INACTIVE));
}

/**
 * Auto-rotating hero carousel (WAI-ARIA APG pattern). It advances every 6s for everyone; the first control
 * stops/starts it (WCAG 2.2.2) and keyboard focus inside pauses it. It deliberately does NOT pause on hover.
 */
export function initHeroCarousel() {
    const root = document.querySelector('[data-hero-carousel]');
    if (!root) return;
    const slides = Array.from(root.querySelectorAll('[data-slide]'));
    const live = root.querySelector('[data-live]');
    const playButton = root.querySelector('[data-play-toggle]');
    const dots = Array.from(root.querySelectorAll('[data-dot]'));
    let index = 0;
    let playing = true;
    let focusWithin = false;
    let timer = null;

    const rotating = () => playing && !focusWithin;
    const schedule = () => {
        window.clearTimeout(timer);
        if (rotating()) timer = window.setTimeout(() => go(index + 1), INTERVAL);
        live.setAttribute('aria-live', rotating() ? 'off' : 'polite');
    };
    const go = (i) => {
        index = (i + slides.length) % slides.length;
        slides.forEach((s, n) => showSlide(s, n === index));
        dots.forEach((d, n) => {
            const on = n === index;
            if (on) d.setAttribute('aria-current', 'true');
            else d.removeAttribute('aria-current');
            const bar = d.querySelector('[data-bar]');
            bar.classList.toggle('w-8', on);
            bar.classList.toggle('bg-white', on);
            bar.classList.toggle('w-2.5', !on);
            bar.classList.toggle('bg-on-dark-muted', !on);
            bar.classList.toggle('group-hover:bg-white', !on);
        });
        schedule();
    };

    playButton.addEventListener('click', () => {
        playing = !playing;
        playButton.querySelector('[data-icon-pause]').hidden = !playing;
        playButton.querySelector('[data-icon-play]').hidden = playing;
        playButton.querySelector('[data-label]').textContent = playing ? (playButton.dataset.stopLabel || 'Stop automatic slide show') : (playButton.dataset.startLabel || 'Start automatic slide show');
        schedule();
    });
    root.querySelector('[data-prev]').addEventListener('click', () => go(index - 1));
    root.querySelector('[data-next]').addEventListener('click', () => go(index + 1));
    dots.forEach((d, n) => d.addEventListener('click', () => go(n)));

    // Only keyboard focus pauses rotation; clicking a control shouldn't.
    root.addEventListener('focusin', (e) => {
        if (e.target.matches(':focus-visible')) {
            focusWithin = true;
            schedule();
        }
    });
    root.addEventListener('focusout', (e) => {
        if (!root.contains(e.relatedTarget)) {
            focusWithin = false;
            schedule();
        }
    });
    schedule();
}

/** Native scroll-snap row: swipe, trackpad and keyboard work without JS. The buttons are a convenience on top. */
export function initServiceCarousel() {
    const root = document.querySelector('[data-service-carousel]');
    if (!root) return;
    const track = root.querySelector('[data-track]');
    const prev = root.querySelector('[data-scroll-prev]');
    const next = root.querySelector('[data-scroll-next]');
    let atStart = true;
    let atEnd = false;

    const update = () => {
        atStart = track.scrollLeft <= 4;
        atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
        prev.setAttribute('aria-disabled', String(atStart));
        next.setAttribute('aria-disabled', String(atEnd));
    };
    const scrollByCard = (dir) => {
        const card = track.querySelector('li');
        if (!card) return;
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        track.scrollBy({ left: dir * (card.clientWidth + 24), behavior: reduce ? 'auto' : 'smooth' });
    };
    prev.addEventListener('click', () => !atStart && scrollByCard(-1));
    next.addEventListener('click', () => !atEnd && scrollByCard(1));
    update();
    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
}

/** Manual (never auto-rotating) featured-article slider. */
export function initArticleCarousel() {
    const root = document.querySelector('[data-article-carousel]');
    if (!root) return;
    const slides = Array.from(root.querySelectorAll('[data-slide]'));
    const counter = root.querySelector('[data-counter]');
    const pad = (n) => String(n).padStart(2, '0');
    let index = 0;
    const go = (i) => {
        index = (i + slides.length) % slides.length;
        slides.forEach((s, n) => showSlide(s, n === index));
        counter.textContent = `${pad(index + 1)} / ${pad(slides.length)}`;
    };
    root.querySelector('[data-prev]').addEventListener('click', () => go(index - 1));
    root.querySelector('[data-next]').addEventListener('click', () => go(index + 1));
}
