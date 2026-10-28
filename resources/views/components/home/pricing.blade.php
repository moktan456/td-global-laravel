@props(['headingLevel' => 'h2'])
@php($planHeading = $headingLevel === 'h1' ? 'h2' : 'h3')
<section aria-labelledby="pricing-heading" class="py-20 lg:py-28">
    <x-container>
        <x-section-heading id="pricing-heading" :as="$headingLevel" :eyebrow="content('home.sections.pricing.eyebrow')" :title="content('home.sections.pricing.title')" />
        <p class="mt-3 text-muted">{{ content('home.sections.pricing.note') }}</p>
        <ul data-reveal-group class="mt-10 overflow-hidden rounded-card border border-line bg-white">
            @foreach (content('site.plans') as $plan)
                @php($highlighted = $plan['highlighted'] ?? false)
                <li class="grid items-center gap-4 border-b border-line px-6 py-6 last:border-b-0 transition-colors duration-(--duration-enter) sm:grid-cols-[1fr_2fr_auto_auto] sm:gap-8 {{ $highlighted ? 'bg-sand' : 'hover:bg-sand/60 focus-within:bg-sand/60' }}">
                    <{{ $planHeading }} class="font-display text-xl font-semibold text-navy-900">
                        {{ $plan['name'] }}@if ($highlighted)<span class="ml-2 inline-block rounded-full bg-gold px-2.5 py-0.5 align-middle font-sans text-xs font-semibold uppercase tracking-wider text-ink">Popular</span>@endif
                    </{{ $planHeading }}>
                    <ul class="flex flex-wrap gap-x-4 gap-y-1 text-muted">
                        @foreach ($plan['features'] as $f)
                            <li class="flex items-center gap-1.5">
                                <x-lucide name="circle-check" class="size-4 text-green-dark" />
                                {{ $f }}
                            </li>
                        @endforeach
                    </ul>
                    <p class="font-display text-3xl font-semibold text-navy-900">{{ $plan['price'] }}</p>
                    <x-button-link :href="'/quote?plan='.rawurlencode($plan['name'])" :variant="$highlighted ? 'primary' : 'outline'">
                        Get started<span class="sr-only"> with {{ $plan['name'] }}</span>
                        <x-lucide name="arrow-right" class="arrow-nudge size-4" />
                    </x-button-link>
                </li>
            @endforeach
        </ul>
    </x-container>
</section>
