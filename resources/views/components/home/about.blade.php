@props(['headingLevel' => 'h2', 'links' => true])
{{-- `links` adds the "More about us" and landing-page buttons; turn them off on the About page itself. --}}
<section aria-labelledby="about-heading" class="py-20 lg:py-28">
    <x-container class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
        <div data-reveal-group class="grid h-80 grid-cols-2 gap-4 sm:h-[26rem]">
            <div class="{{ ui('photoFrame') }}">
                <x-photo name="aboutTruck" sizes="(min-width: 1024px) 25vw, 50vw" :zoom="true" class="object-[85%_50%]" />
            </div>
            <div class="{{ ui('photoFrame') }} mt-12">
                <x-photo name="aboutMountainRoad" sizes="(min-width: 1024px) 25vw, 50vw" :zoom="true" />
            </div>
        </div>
        <div>
            <x-section-heading id="about-heading" :as="$headingLevel" eyebrow="About us" title="Your trusted customs clearance partner">
                <x-slot:intro>
                    <p>TD Global Clearing &amp; Logistics Pte. Ltd. is a clearing agent and logistics partner in Gelephu, GMC. We help importers and exporters move goods across borders without delays. We take care of declarations, duties and documentation, and keep you informed at every step, so you can focus on running your business.</p>
                </x-slot:intro>
            </x-section-heading>
            @if ($links)
                <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2">
                    <x-button-link href="/about" variant="outline">More about us</x-button-link>
                    <x-button-link href="/clearing-agent-gelephu" variant="ghost" class="px-0 text-brand underline">Clearing agent in Gelephu &amp; GMC</x-button-link>
                </div>
            @endif
        </div>
    </x-container>
</section>
