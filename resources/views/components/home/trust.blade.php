@php($icons = ['badge' => 'badge-check', 'clock' => 'clock', 'radar' => 'radar', 'headset' => 'headset', 'shield' => 'shield-check', 'award' => 'award', 'lock' => 'lock', 'savings' => 'piggy-bank'])
<section aria-labelledby="trust-heading" class="on-dark bg-navy-900 py-20 text-white lg:py-28">
    <x-container class="text-center">
        <x-lucide name="file-check-2" class="mx-auto size-10 text-gold" />
        <h2 id="trust-heading" class="mx-auto mt-4 max-w-2xl font-display text-3xl font-semibold sm:text-4xl">Your trusted customs clearance partner worldwide</h2>
        <ul data-reveal-group class="mt-12 grid grid-cols-2 gap-4 text-left lg:grid-cols-4">
            @foreach (content('site.trustBadges') as $b)
                <li class="group flex items-center gap-3 rounded-tile border border-line-dark bg-navy-800 p-4 transition-colors duration-(--duration-enter) hover:border-gold/60 hover:bg-navy-700">
                    <x-lucide :name="$icons[$b['icon']]" class="size-6 shrink-0 text-green transition-transform duration-(--duration-enter) ease-out-quart group-hover:scale-110" />
                    <span class="font-semibold">{{ $b['label'] }}</span>
                </li>
            @endforeach
        </ul>
        <x-button-link href="/quote" variant="outline-light" class="mt-12">Make a custom request</x-button-link>
    </x-container>
</section>
