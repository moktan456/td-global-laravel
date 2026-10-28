@php($testimonials = array_values(array_filter(content('site.testimonials'), fn ($t) => is_filled($t['quote']) && is_filled($t['name']))))
@if (count($testimonials))
    <section aria-labelledby="testimonials-heading" class="bg-sand py-20 lg:py-28">
        <x-container>
            <x-section-heading id="testimonials-heading" :title="content('home.sections.testimonials.title')" />
            <ul data-reveal-group class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($testimonials as $t)
                    <li class="flex flex-col rounded-card bg-white p-6 {{ ui('cardLift') }}">
                        <figure class="flex flex-1 flex-col">
                            <x-lucide name="quote" class="size-7 text-brand" />
                            <blockquote class="mt-4 flex-1 text-ink"><p>{{ $t['quote'] }}</p></blockquote>
                            <figcaption class="mt-6 flex items-center gap-3">
                                <span class="inline-flex size-11 items-center justify-center rounded-full bg-sand text-muted"><x-lucide name="user-round" class="size-5" /></span>
                                <span>
                                    <span class="block font-semibold text-navy-900">{{ $t['name'] }}</span>
                                    @if (is_filled($t['company']))
                                        <span class="block text-sm text-muted">{{ $t['company'] }}</span>
                                    @endif
                                </span>
                            </figcaption>
                        </figure>
                    </li>
                @endforeach
            </ul>
        </x-container>
    </section>
@endif
