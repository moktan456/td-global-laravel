@php
    $phone = site('contact.phone');
    $phoneHref = site('contact.phoneHref');
    $email = site('contact.email');
    $address = site('contact.address');
    $hours = site('contact.hours');
    $whatsapp = whatsapp_url(site('contact.whatsapp'));
    $linkClass = 'inline-block py-1.5 text-on-dark-muted transition-colors duration-200 hover:text-white';
@endphp
<footer class="on-dark bg-navy-950 pb-32 text-on-dark-muted">
    <div class="mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1.2fr_1fr] lg:px-16">
        <div>
            <x-logo dark />
            <p class="mt-5 max-w-xs">{{ page_text('footer.about') }}</p>
            <x-social-links class="mt-5 flex gap-2" link-class="text-on-dark-muted hover:bg-navy-800 hover:text-white" />
        </div>

        <nav aria-labelledby="footer-links">
            <h2 id="footer-links" class="font-sans text-base font-semibold text-white">{{ page_text('footer.linksHeading') }}</h2>
            <ul class="mt-3">
                @foreach (content('site.footerLinks') as $l)
                    <li><a href="{{ $l['href'] }}" class="{{ $linkClass }}">{{ $l['label'] }}</a></li>
                @endforeach
            </ul>
        </nav>

        <div>
            <h2 class="font-sans text-base font-semibold text-white">{{ page_text('footer.contactHeading') }}</h2>
            <address class="mt-3 grid not-italic">
                @if (is_filled($address))
                    <span class="py-1.5">{{ $address }}</span>
                @endif
                @if (is_filled($phone))
                    @if ($phoneHref)
                        <a href="tel:{{ $phoneHref }}" class="{{ $linkClass }}">{{ $phone }}</a>
                    @else
                        <span class="py-1.5">{{ $phone }}</span>
                    @endif
                @endif
                @if ($whatsapp)
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="{{ $linkClass }}">{{ label('buttons.whatsapp') }}<span class="sr-only"> {{ label('a11y.newTab') }}</span></a>
                @endif
                <a href="mailto:{{ $email }}" class="{{ $linkClass }} break-all">{{ $email }}</a>
                @if (is_filled($hours))
                    <span class="py-1.5">{{ $hours }}</span>
                @endif
            </address>
        </div>

        <nav aria-labelledby="footer-more">
            <h2 id="footer-more" class="font-sans text-base font-semibold text-white">{{ page_text('footer.moreHeading') }}</h2>
            <ul class="mt-3">
                @foreach (content('site.footerSecondary') as $l)
                    <li><a href="{{ $l['href'] }}" class="{{ $linkClass }}">{{ $l['label'] }}</a></li>
                @endforeach
            </ul>
        </nav>
    </div>

    <div class="border-t border-line-dark">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-5 text-sm sm:px-6 lg:px-16">
            <div class="grid gap-1">
                <p>{{ page_text('footer.copyright') }} <a href="/about#certificates-heading" class="underline underline-offset-2 hover:text-white">{{ page_text('footer.viewCertificates') }}</a></p>
                <p>{{ page_text('footer.designedBy') }} <a href="{{ site('designer.url') }}" target="_blank" rel="noopener" class="underline underline-offset-2 hover:text-white">{{ site('designer.name') }}<span class="sr-only"> {{ label('a11y.newTab') }}</span></a></p>
            </div>
            <a href="#main" class="inline-flex min-h-11 items-center gap-2 hover:text-white">
                {{ label('buttons.backToTop') }}
                <x-lucide name="arrow-up" class="arrow-nudge-up arrow-nudge size-4" />
            </a>
        </div>
    </div>
</footer>
