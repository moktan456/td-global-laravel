@php($partners = array_values(array_filter(content('site.partners'), fn ($p) => is_filled($p))))
@if (count($partners))
    <section aria-labelledby="partners-heading" class="bg-sand py-16">
        <x-container>
            <h2 id="partners-heading" class="text-center font-sans text-sm font-semibold uppercase tracking-[0.14em] text-brand">Our partners</h2>
            <ul data-reveal-group class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ($partners as $p)
                    <li class="flex h-24 items-center justify-center rounded-card border border-dashed border-control bg-white text-muted">{{ $p }}</li>
                @endforeach
            </ul>
        </x-container>
    </section>
@endif
