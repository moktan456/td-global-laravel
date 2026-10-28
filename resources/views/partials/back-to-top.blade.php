{{-- Back to top: bottom left, mirrors the quick-contact buttons on the right. Hidden (and out of the tab order) until the page is scrolled; see resources/js/page-extras.js. --}}
<button type="button" data-back-to-top hidden aria-label="{{ label('a11y.backToTop') }}"
    class="fixed bottom-4 left-4 z-30 inline-flex size-12 items-center justify-center rounded-full bg-navy-900 text-white shadow-lift ring-2 ring-white/40 transition-[transform,background-color] duration-(--duration-hover) ease-out-quart hover:-translate-y-0.5 hover:bg-navy-800 focus-visible:-translate-y-0.5 focus-visible:outline-gold focus-visible:outline-offset-4 focus-visible:ring-4 focus-visible:ring-navy-950 active:translate-y-0 print:hidden sm:bottom-6 sm:left-6">
    <x-lucide name="arrow-up" class="size-6" />
</button>
