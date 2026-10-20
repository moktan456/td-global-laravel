{{--
    Floating email + WhatsApp buttons, bottom right on every page (email above WhatsApp).
    Sits below the header/menus (z-30 < z-40) and is hidden when printing. The footer reserves room under its last
    row (pb-32) and the page has scroll-padding-bottom, so nothing, including a keyboard-focused control, is left
    hidden behind the buttons. The focus ring is two-tone so it shows on light and dark sections.
--}}
@php
    $whatsapp = whatsapp_url(site('contact.whatsapp'));
    $button = 'inline-flex size-12 items-center justify-center rounded-full text-white shadow-lift ring-2 ring-white/40 '
        .'transition-[transform,background-color] duration-(--duration-hover) ease-out-quart '
        .'hover:-translate-y-0.5 focus-visible:-translate-y-0.5 active:translate-y-0 '
        .'focus-visible:outline-gold focus-visible:outline-offset-4 focus-visible:ring-4 focus-visible:ring-navy-950';
@endphp
<nav aria-label="Quick contact" class="fixed right-4 bottom-4 z-30 print:hidden sm:right-6 sm:bottom-6">
    <ul class="flex flex-col gap-3">
        <li>
            <a href="mailto:{{ site('contact.email') }}" class="{{ $button }} bg-navy-900 hover:bg-navy-800">
                <x-lucide name="mail" class="size-6" />
                <span class="sr-only">Email us</span>
            </a>
        </li>
        @if ($whatsapp)
            <li>
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="{{ $button }} bg-whatsapp hover:bg-whatsapp-dark">
                    <x-brand-icon name="whatsapp" class="size-7" />
                    <span class="sr-only">Chat on WhatsApp (opens in a new tab)</span>
                </a>
            </li>
        @endif
    </ul>
</nav>
