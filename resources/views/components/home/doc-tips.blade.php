<section aria-labelledby="tips-heading" class="bg-sand py-20 lg:py-28">
    <x-container class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
        <div>
            <x-section-heading id="tips-heading" eyebrow="Document prep tips" title="How to get your paperwork right">
                <x-slot:intro><p>These tips come from our clearance experts. A few minutes of checking saves days of delay.</p></x-slot:intro>
            </x-section-heading>
            <ul data-reveal-group class="mt-8 grid gap-4">
                @foreach (content('site.docTips') as $tip)
                    <li class="flex gap-3">
                        <x-lucide name="circle-check" class="mt-0.5 size-6 shrink-0 text-green-dark" />
                        <span>{{ $tip }}</span>
                    </li>
                @endforeach
            </ul>
            <a href="/blog/invoice-and-packing-list-must-match" class="mt-8 inline-flex min-h-11 items-center gap-2 font-semibold text-brand underline decoration-2 underline-offset-4 hover:text-brand-dark">
                Read the full guide
                <x-lucide name="arrow-right" class="arrow-nudge size-4" />
            </a>
        </div>
        <div data-reveal class="{{ ui('photoFrame') }} aspect-[4/3]">
            <x-photo name="docsReview" sizes="(min-width: 1024px) 45vw, 100vw" :zoom="true" />
        </div>
    </x-container>
</section>
