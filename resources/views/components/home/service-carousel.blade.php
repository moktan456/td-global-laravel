{{-- A native scroll-snap row: swipe, trackpad and keyboard (Tab through the links) all work without JS. The buttons are a convenience on top. --}}
@php
    $arrow = 'inline-flex size-11 items-center justify-center rounded-full border border-white/60 text-white transition-[background-color,color,opacity] duration-(--duration-hover) hover:bg-white hover:text-navy-900 aria-disabled:cursor-default aria-disabled:opacity-40 aria-disabled:hover:bg-transparent aria-disabled:hover:text-white';
@endphp
<div data-service-carousel>
    <ul data-track data-reveal-group class="relative -mx-4 flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-px-4 px-4 pt-2 pb-6 [scrollbar-width:thin] [scrollbar-color:var(--color-control-dark)_transparent] sm:-mx-6 sm:scroll-px-6 sm:px-6 lg:mx-0 lg:scroll-px-0 lg:px-0">
        @foreach (content('services') as $s)
            <li class="w-[85%] shrink-0 snap-start sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)]">
                <article class="group flex h-full flex-col overflow-hidden rounded-card bg-ivory text-ink {{ ui('cardLift') }}">
                    <div class="relative aspect-[4/3] overflow-hidden">
                        <x-photo :name="$s['image']" sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 85vw" :zoom="true" />
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h3 class="font-display text-2xl font-semibold text-navy-900">{{ $s['title'] }}</h3>
                        <p class="mt-2 flex-1 text-muted">{{ $s['summary'] }}</p>
                        <a href="/services/{{ $s['slug'] }}" class="mt-4 inline-flex min-h-11 items-center gap-2 font-semibold text-brand underline decoration-2 underline-offset-4 hover:text-brand-dark">
                            {{ label('buttons.learnMore') }}<span class="sr-only"> about {{ \Illuminate\Support\Str::lower($s['title']) }}</span>
                            <x-lucide name="arrow-right" class="arrow-nudge size-4" />
                        </a>
                    </div>
                </article>
            </li>
        @endforeach
    </ul>
    <div class="mt-6 flex justify-end gap-3">
        <button type="button" data-scroll-prev class="{{ $arrow }}" aria-disabled="true">
            <x-lucide name="chevron-left" class="size-5" />
            <span class="sr-only">Scroll to previous services</span>
        </button>
        <button type="button" data-scroll-next class="{{ $arrow }}" aria-disabled="false">
            <x-lucide name="chevron-right" class="size-5" />
            <span class="sr-only">Scroll to more services</span>
        </button>
    </div>
</div>
