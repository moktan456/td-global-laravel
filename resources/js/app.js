import { initReveal } from './reveal';
import { initSpot } from './spot';
import { initPageExtras } from './page-extras';
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
    initPageExtras();
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
else boot();
