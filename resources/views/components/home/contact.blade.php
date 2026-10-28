@props(['headingLevel' => 'h2', 'withHeading' => true])
{{--
    `withHeading=false` is for the Contact page, where the page header already carries the title and intro: the section
    drops its own heading and the panel title becomes the next heading level (h2).
--}}
@php
    $panelHeading = (! $withHeading || $headingLevel === 'h1') ? 'h2' : 'h3';
    $phone = site('contact.phone');
    $phoneHref = site('contact.phoneHref');
    $email = site('contact.email');
    $address = site('contact.address');
    $whatsapp = whatsapp_url(site('contact.whatsapp'));
    $mapQuery = site('contact.mapQuery');
    $mapUrl = site('contact.mapUrl');
@endphp
<section @if ($withHeading) aria-labelledby="contact-heading" @else aria-label="Contact details and message form" @endif class="on-dark bg-navy-900 text-white {{ $withHeading ? 'py-20 lg:py-28' : 'pb-20 lg:pb-28' }}">
    <x-container>
        @if ($withHeading)
            <x-section-heading id="contact-heading" :as="$headingLevel" :dark="true" :eyebrow="page_text('contactPanel.eyebrow')" :title="page_text('contactPanel.title')">
                <x-slot:intro><p>{{ page_text('contactPanel.intro') }}</p></x-slot:intro>
            </x-section-heading>
        @endif
        {{-- Two matching panels: same top, same bottom (grid stretch). --}}
        <div class="{{ $withHeading ? 'mt-12' : '' }} grid gap-8 lg:grid-cols-2">
            <div class="flex flex-col overflow-hidden rounded-card border border-line-dark bg-navy-950/60">
                <div class="relative min-h-64 flex-1">
                    {{-- The office map once a location is set; a mountain-road photo until then. Fills its panel. --}}
                    @if ($mapQuery)
                        <iframe title="Map showing the {{ site('name') }} office" src="https://maps.google.com/maps?q={{ rawurlencode($mapQuery) }}&amp;z=16&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="absolute inset-0 size-full border-0"></iframe>
                    @else
                        <x-photo name="aboutMountainRoad" sizes="(min-width: 1024px) 45vw, 100vw" :decorative="true" />
                    @endif
                </div>
                <ul class="grid gap-3 p-6 sm:p-8">
                    @if (is_filled($address))
                        <li class="flex items-center gap-3"><x-lucide name="map-pin" class="size-5 shrink-0 text-gold" /><span>{{ $address }}</span></li>
                    @endif
                    @if (is_filled($phone))
                        <li class="flex items-center gap-3">
                            <x-lucide name="phone" class="size-5 shrink-0 text-gold" />
                            @if ($phoneHref)
                                <a href="tel:{{ $phoneHref }}" class="underline underline-offset-4">{{ $phone }}</a>
                            @else
                                <span>{{ $phone }}</span>
                            @endif
                        </li>
                    @endif
                    @if ($whatsapp)
                        <li class="flex items-center gap-3">
                            <x-lucide name="message-circle" class="size-5 shrink-0 text-gold" />
                            <a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="underline underline-offset-4">{{ label('buttons.whatsapp') }}<span class="sr-only"> {{ label('a11y.newTab') }}</span></a>
                        </li>
                    @endif
                    <li class="flex items-center gap-3">
                        <x-lucide name="mail" class="size-5 shrink-0 text-gold" />
                        <a href="mailto:{{ $email }}" class="break-all underline underline-offset-4">{{ $email }}</a>
                    </li>
                    @if ($mapUrl)
                        <li class="flex items-center gap-3">
                            <x-lucide name="navigation" class="size-5 shrink-0 text-gold" />
                            <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="underline underline-offset-4">{{ label('buttons.directions') }}<span class="sr-only"> {{ label('a11y.mapNewTab') }}</span></a>
                        </li>
                    @endif
                </ul>
            </div>
            <div class="rounded-card border border-line-dark bg-navy-950/60 p-6 sm:p-8">
                <{{ $panelHeading }} class="font-display text-2xl font-semibold text-white">{{ page_text('contactPanel.formTitle') }}</{{ $panelHeading }}>
                <p class="mt-1 mb-6 text-on-dark-muted">{{ page_text('contactPanel.formNote') }}</p>
                @include('forms.contact', ['tone' => 'dark'])
            </div>
        </div>
    </x-container>
</section>
