<section aria-labelledby="calc-heading" class="on-dark relative overflow-hidden bg-navy-900 py-20 text-white">
    <div aria-hidden="true" class="absolute inset-y-0 right-0 hidden w-1/2 opacity-25 lg:block">
        <x-photo name="heroRoad" sizes="50vw" :decorative="true" />
    </div>
    <x-container class="relative">
        <x-section-heading id="calc-heading" :dark="true" :eyebrow="content('home.sections.calculator.eyebrow')" :title="content('home.sections.calculator.title')">
            <x-slot:intro><p>{{ content('home.sections.calculator.intro') }}</p></x-slot:intro>
        </x-section-heading>
        <x-button-link href="/calculator" variant="outline-light" class="mt-8">{{ content('home.sections.calculator.button') }}</x-button-link>
    </x-container>
</section>
