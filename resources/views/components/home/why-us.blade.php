@php
    $icons = ['expertise' => 'award', 'regulatory' => 'landmark', 'tracking' => 'radar'];
@endphp
<section aria-labelledby="why-heading" class="py-20 lg:py-28">
    <x-container class="grid gap-12 lg:grid-cols-[1.9fr_1fr] lg:items-center">
        <x-section-heading id="why-heading" :eyebrow="content('home.sections.why.eyebrow')" :title="content('home.sections.why.title')" class="lg:order-2">
            <x-slot:intro>
                <p>{{ content('home.sections.why.intro') }}</p>
            </x-slot:intro>
        </x-section-heading>
        <ul data-reveal-group class="grid gap-5 sm:grid-cols-3 lg:order-1">
            @foreach (content('site.whyUs') as $item)
                <li class="group rounded-card border border-line bg-white p-6 hover:border-brand/40 {{ ui('cardLift') }}">
                    <span class="inline-flex size-12 items-center justify-center rounded-tile bg-sand text-brand transition-colors duration-(--duration-enter) group-hover:bg-brand group-hover:text-white">
                        <x-lucide :name="$icons[$item['icon']]" class="size-6" />
                    </span>
                    <h3 class="mt-5 font-display text-xl font-semibold text-navy-900">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-muted">{{ $item['body'] }}</p>
                </li>
            @endforeach
        </ul>
    </x-container>
</section>
