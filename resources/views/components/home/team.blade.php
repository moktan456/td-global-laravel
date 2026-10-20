{{-- Sections hide themselves until real content replaces the placeholders. --}}
@php($team = array_values(array_filter(content('site.team'), fn ($m) => is_filled($m['name']))))
@if (count($team))
    <section aria-labelledby="team-heading" class="py-20 lg:py-28">
        <x-container>
            <x-section-heading id="team-heading" eyebrow="Our team" title="Meet our team" />
            <ul data-reveal-group class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($team as $m)
                    <li>
                        <div class="{{ ui('photoFrame') }} aspect-[4/5] bg-sand">
                            <x-photo :name="$m['image']" sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw" :zoom="true" />
                        </div>
                        <p class="mt-4 font-display text-xl font-semibold text-navy-900">{{ $m['name'] }}</p>
                        @if (is_filled($m['role']))
                            <p class="text-muted">{{ $m['role'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </x-container>
    </section>
@endif
