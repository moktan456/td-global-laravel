{{-- Title and description come from the layout; the home page only adds its canonical URL. --}}
@extends('layouts.app', ['canonical' => '/'])

@section('content')
    <x-home.hero-carousel />
    <x-home.about />

    <section aria-labelledby="services-heading" class="on-dark bg-navy-900 py-20 text-white lg:py-28">
        <x-container>
            <x-section-heading id="services-heading" :dark="true" eyebrow="What we do" title="The power of seamless customs clearance">
                <x-slot:intro><p>A complete range of clearance services for your import and export needs, including:</p></x-slot:intro>
            </x-section-heading>
            <div class="mt-12">
                <x-home.service-carousel />
            </div>
        </x-container>
    </section>

    <x-home.why-us />
    <x-home.doc-tips />
    <x-home.team />
    <x-home.partners />
    <x-home.calculator-cta />
    <x-home.pricing />
    <x-home.testimonials />
    <x-home.trust />

    <section aria-labelledby="blog-heading" class="py-20 lg:py-28">
        <x-container>
            <x-section-heading id="blog-heading" eyebrow="Blog & articles" title="Top latest articles" />
            <div class="mt-12">
                <x-home.article-carousel />
            </div>
            <x-button-link href="/blog" variant="outline" class="mt-8">All articles</x-button-link>
        </x-container>
    </section>

    <x-home.contact />
@endsection
