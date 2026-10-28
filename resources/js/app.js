import { initReveal } from './reveal';
import { initSpot } from './spot';
import { initHeader } from './header';
import { initForms } from './forms';
import { initMenus } from './menus';
import { initCalculator, initCertificates } from './calculator';
import { initArticleCarousel, initHeroCarousel, initServiceCarousel } from './carousels';

function boot() {
    initHeader();
    initHeroCarousel();
    initServiceCarousel();
    initArticleCarousel();
    initForms();
    initMenus();
    initCalculator();
    initCertificates();
    initReveal();
    initSpot();
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
else boot();
