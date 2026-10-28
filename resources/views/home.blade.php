{{-- Title and description come from the layout; the home page only adds its canonical URL. --}}
@extends('layouts.app', ['canonical' => '/'])

@section('content')
    <x-home.hero-carousel />
    <x-home.about />

    <section aria-labelledby="services-heading" class="on-dark bg-navy-900 py-20 text-white lg:py-28">
        <x-container>
            <x-section-heading id="services-heading" :dark="true" :eyebrow="content('home.sections.services.eyebrow')" :title="content('home.sections.services.title')">
                <x-slot:intro><p>{{ content('home.sections.services.intro') }}</p></x-slot:intro>
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

    @if (count(\App\Support\Content::articles()))
    <section aria-labelledby="blog-heading" class="py-20 lg:py-28">
        <x-container>
            <x-section-heading id="blog-heading" :eyebrow="content('home.sections.blog.eyebrow')" :title="content('home.sections.blog.title')" />
            <div class="mt-12">
                <x-home.article-carousel />
            </div>
            <x-button-link href="/blog" variant="outline" class="mt-8">{{ label('buttons.allArticles') }}</x-button-link>
        </x-container>
    </section>
    @endif

    <x-home.contact />
@endsection
