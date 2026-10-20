import { initReveal } from './reveal';
import { initHeader } from './header';
import { initForms } from './forms';
import { initCalculator, initCertificates } from './calculator';
import { initArticleCarousel, initHeroCarousel, initServiceCarousel } from './carousels';

function boot() {
    initHeader();
    initHeroCarousel();
    initServiceCarousel();
    initArticleCarousel();
    initForms();
    initCalculator();
    initCertificates();
    initReveal();
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
else boot();
