{{-- Manual (never auto-rotating) featured-article slider. --}}
@php
    $articles = content('articles');
    $total = count($articles);
    $btn = 'inline-flex size-11 items-center justify-center rounded-full bg-white/90 text-navy-900 transition-[background-color,scale] duration-(--duration-hover) ease-out-quart hover:bg-white active:scale-95';
    $pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
@endphp
<section data-article-carousel data-reveal aria-roledescription="carousel" aria-label="Latest articles" class="relative">
    <div aria-live="polite" class="grid">
        @foreach ($articles as $i => $a)
            @php($active = $i === 0)
            <div data-slide role="group" aria-roledescription="slide" aria-label="{{ $i + 1 }} of {{ $total }}" @unless ($active) inert @endunless class="col-start-1 row-start-1 transition-opacity duration-500 ease-out-quart {{ $active ? 'opacity-100' : 'pointer-events-none opacity-0' }}">
                <article class="group on-dark relative flex min-h-[26rem] items-end overflow-hidden rounded-card">
                    <x-photo :name="$a['image']" sizes="(min-width: 1280px) 1150px, 100vw" :decorative="true" :zoom="true" />
                    <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/80 to-navy-950/10"></div>
                    <div class="relative max-w-2xl p-6 sm:p-10">
                        <p class="text-sm font-semibold uppercase tracking-[0.14em] text-gold">{{ $a['category'] }}</p>
                        <h3 class="mt-2 font-display text-2xl font-semibold text-white sm:text-3xl">{{ $a['title'] }}</h3>
                        <p class="mt-3 text-on-dark">{{ $a['excerpt'] }}</p>
                        <p class="mt-3 text-sm text-on-dark-muted">
                            {{ is_filled($a['author']) ? $a['author'].' · ' : '' }}<time datetime="{{ $a['date'] }}">{{ format_date($a['date']) }}</time>
                        </p>
                        <a href="/blog/{{ $a['slug'] }}" class="mt-4 inline-flex min-h-11 items-center gap-2 font-semibold text-white underline decoration-gold decoration-2 underline-offset-4 hover:decoration-white">
                            Read more<span class="sr-only">: {{ $a['title'] }}</span>
                            <x-lucide name="arrow-right" class="arrow-nudge size-4" />
                        </a>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
    <div class="absolute right-4 top-4 flex items-center gap-2 sm:right-6 sm:top-6">
        <p data-counter class="mr-1 rounded-full bg-navy-950/80 px-3 py-1 text-sm font-semibold text-white" aria-hidden="true">{{ $pad(1) }} / {{ $pad($total) }}</p>
        <button type="button" data-prev class="{{ $btn }}">
            <x-lucide name="chevron-left" class="size-5" />
            <span class="sr-only">Previous article</span>
        </button>
        <button type="button" data-next class="{{ $btn }}">
            <x-lucide name="chevron-right" class="size-5" />
            <span class="sr-only">Next article</span>
        </button>
    </div>
</section>
