<section aria-labelledby="calc-heading" class="on-dark relative overflow-hidden bg-navy-900 py-20 text-white">
    <div aria-hidden="true" class="absolute inset-y-0 right-0 hidden w-1/2 opacity-25 lg:block">
        <x-photo name="heroRoad" sizes="50vw" :decorative="true" />
    </div>
    <x-container class="relative">
        <x-section-heading id="calc-heading" :dark="true" eyebrow="Calculate cost" title="Cost calculator">
            <x-slot:intro><p>Know the price of your clearance in advance. Get an indicative estimate for standard or complex shipments in under a minute.</p></x-slot:intro>
        </x-section-heading>
        <x-button-link href="/calculator" variant="outline-light" class="mt-8">Calculate clearance cost</x-button-link>
    </x-container>
</section>
