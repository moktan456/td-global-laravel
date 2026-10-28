@php
    $slides = content('home.slides');
    $controlBtn = 'inline-flex size-11 items-center justify-center rounded-full border border-white/60 text-white transition-[background-color,color,scale] duration-(--duration-hover) ease-out-quart hover:bg-white hover:text-navy-900 active:scale-95';
    $sizes = '(min-width: 1024px) 30vw, 50vw';
@endphp
{{--
    Auto-rotating carousel (WAI-ARIA APG pattern). It advances every 6s for everyone; the first control stops/starts it
    (WCAG 2.2.2) and keyboard focus inside pauses it. It deliberately does NOT pause on hover: the hero fills most of the
    first screen, so a resting mouse stopped it for good. With prefers-reduced-motion the slides swap instantly.
--}}
<section data-hero-carousel aria-roledescription="carousel" aria-label="Highlights" class="on-dark relative overflow-hidden bg-navy-900 text-white">
    <h1 class="sr-only">{{ page_text('home.heading') }}</h1>
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-16 lg:py-20">
        <div data-live aria-live="off" class="grid">
            @foreach ($slides as $i => $s)
                @php($active = $i === 0)
                <div data-slide role="group" aria-roledescription="slide" aria-label="{{ $i + 1 }} of {{ count($slides) }}" @unless ($active) inert @endunless class="col-start-1 row-start-1 grid items-center gap-10 transition-opacity duration-500 ease-out-quart lg:grid-cols-[1fr_1.05fr] {{ $active ? 'opacity-100' : 'pointer-events-none opacity-0' }}">
                    <div class="max-w-xl">
                        <p class="text-base font-semibold text-gold">{{ $s['eyebrow'] }}</p>
                        <h2 class="mt-3 font-display text-4xl font-semibold leading-[1.1] sm:text-5xl lg:text-6xl">{{ $s['title'] }}</h2>
                        <p class="mt-5 text-lg text-on-dark-muted">{{ $s['body'] }}</p>
                        <div class="mt-8">
                            <x-button-link :href="safe_url($s['cta']['href'])" variant="primary">{{ $s['cta']['label'] }}</x-button-link>
                        </div>
                    </div>
                    <div class="grid h-72 grid-cols-2 gap-3 sm:h-96 lg:h-[28rem]">
                        <div class="group relative overflow-hidden rounded-card spot">
                            <x-photo :name="$s['photos'][0]" :sizes="$sizes" :priority="$i === 0" :zoom="true" />
                        </div>
                        <div class="grid gap-3">
                            <div class="group relative overflow-hidden rounded-card spot">
                                <x-photo :name="$s['photos'][1]" :sizes="$sizes" :priority="$i === 0" :zoom="true" />
                            </div>
                            <div class="group relative overflow-hidden rounded-card spot">
                                <x-photo :name="$s['photos'][2]" :sizes="$sizes" :priority="$i === 0" :zoom="true" />
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-10 flex flex-wrap items-center gap-3">
            <button type="button" data-play-toggle data-stop-label="{{ label('a11y.stopSlides') }}" data-start-label="{{ label('a11y.startSlides') }}" class="{{ $controlBtn }}">
                <span data-icon-pause><x-lucide name="pause" class="size-5" /></span>
                <span data-icon-play hidden><x-lucide name="play" class="size-5" /></span>
                <span data-label class="sr-only">{{ label('a11y.stopSlides') }}</span>
            </button>
            <button type="button" data-prev class="{{ $controlBtn }}">
                <x-lucide name="chevron-left" class="size-5" />
                <span class="sr-only">{{ label('a11y.prevSlide') }}</span>
            </button>
            <button type="button" data-next class="{{ $controlBtn }}">
                <x-lucide name="chevron-right" class="size-5" />
                <span class="sr-only">{{ label('a11y.nextSlide') }}</span>
            </button>
            <ul class="ml-2 flex items-center" aria-label="Choose slide">
                @foreach ($slides as $i => $s)
                    <li>
                        <button type="button" data-dot @if ($i === 0) aria-current="true" @endif class="group inline-flex size-11 items-center justify-center">
                            <span data-bar aria-hidden="true" class="block h-2.5 rounded-full transition-[width,background-color] duration-300 {{ $i === 0 ? 'w-8 bg-white' : 'w-2.5 bg-on-dark-muted group-hover:bg-white' }}"></span>
                            <span class="sr-only">Slide {{ $i + 1 }}: {{ $s['title'] }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
