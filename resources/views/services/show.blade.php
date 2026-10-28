@extends('layouts.app', [
    'title' => $service['metaTitle'],
    'description' => $service['metaDescription'],
    'canonical' => '/services/'.$service['slug'],
])

@php
    $others = array_values(array_filter(content('services'), fn ($s) => $s['slug'] !== $service['slug']));
    $phone = site('contact.phone');
    $phoneHref = site('contact.phoneHref');
    $name = \Illuminate\Support\Str::lower($service['title']);
@endphp

@section('content')
    <x-json-ld :data="\App\Support\Seo::service($service)" />
    <x-page-header :title="$service['pageTitle']" :path="'/services/'.$service['slug']" :crumbs="[['label' => 'Services', 'href' => '/services'], ['label' => $service['title']]]">
        <x-slot:intro><p>{{ $service['summary'] }}</p></x-slot:intro>
    </x-page-header>

    <section aria-labelledby="overview-heading" class="py-20 lg:py-28">
        <x-container class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <x-section-heading id="overview-heading" :title="page_text('service.overviewTitle', ['service' => $name])" />
                <div data-reveal class="mt-6 grid max-w-2xl gap-4 text-lg text-muted">
                    @foreach ($service['body'] as $para)
                        <p>{{ $para }}</p>
                    @endforeach
                </div>
                <h3 data-reveal class="mt-8 font-sans text-base font-semibold uppercase tracking-[0.14em] text-brand">{{ page_text('service.includedLabel') }}</h3>
                <ul data-reveal class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($service['includes'] as $item)
                        <li class="flex gap-2">
                            <x-lucide name="circle-check" class="mt-0.5 size-5 shrink-0 text-green-dark" />
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
                <div data-reveal class="mt-8 flex flex-wrap gap-3">
                    <x-button-link href="/quote">{{ label('buttons.getQuote') }}<span class="sr-only"> for {{ $name }}</span></x-button-link>
                    @if (is_filled($phone) && $phoneHref)
                        <x-button-link :href="'tel:'.$phoneHref" variant="outline">{{ label('buttons.call') }}</x-button-link>
                    @endif
                </div>
            </div>
            <div data-reveal class="{{ ui('photoFrame') }} aspect-[4/3]">
                <x-photo :name="$service['image']" sizes="(min-width: 1024px) 45vw, 100vw" :priority="true" :zoom="true" />
            </div>
        </x-container>
    </section>

    <section aria-labelledby="problems-heading" class="bg-sand py-20 lg:py-28">
        <x-container>
            <x-section-heading id="problems-heading" :eyebrow="page_text('service.problemsEyebrow')" :title="page_text('service.problemsTitle', ['service' => $name])">
                <x-slot:intro><p>{{ page_text('service.problemsIntro') }}</p></x-slot:intro>
            </x-section-heading>
            <ul data-reveal-group class="mt-12 grid gap-6 sm:grid-cols-2">
                @foreach ($service['problems'] as $p)
                    <li class="rounded-card border border-line bg-white p-6 {{ ui('cardLift') }}">
                        <h3 class="font-display text-xl font-semibold text-navy-900">{{ $p['problem'] }}</h3>
                        <p class="mt-2 text-muted">{{ $p['fix'] }}</p>
                    </li>
                @endforeach
            </ul>
        </x-container>
    </section>

    <section aria-labelledby="process-heading" class="py-20 lg:py-28">
        <x-container>
            <x-section-heading id="process-heading" :eyebrow="page_text('service.processEyebrow')" :title="page_text('service.processTitle')">
                <x-slot:intro><p>{{ page_text('service.processIntro') }}</p></x-slot:intro>
            </x-section-heading>
            <div class="mt-12">
                <x-process-steps :steps="content('process')" />
            </div>
        </x-container>
    </section>

    <section aria-labelledby="more-heading" class="bg-sand py-20 lg:py-28">
        <x-container>
            <x-section-heading id="more-heading" :title="page_text('service.moreTitle')" />
            <ul data-reveal-group class="mt-10 grid gap-4 sm:grid-cols-3">
                @foreach ($others as $s)
                    <li>
                        <a href="/services/{{ $s['slug'] }}" class="group flex h-full min-h-11 flex-col justify-between gap-3 rounded-card border border-line bg-white p-6 {{ ui('cardLift') }}">
                            <span class="font-display text-xl font-semibold text-navy-900">{{ $s['title'] }}</span>
                            <span class="inline-flex items-center gap-2 font-semibold text-brand">
                                {{ label('buttons.learnMore') }}
                                <x-lucide name="arrow-right" class="arrow-nudge size-4" />
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <p class="mt-8 text-muted">Based in {{ site('seo.area') }}, {{ site('seo.region') }}? <a href="/clearing-agent-gelephu" class="font-semibold text-brand underline underline-offset-4">See how clearing works with us in Gelephu &amp; GMC</a>.</p>
        </x-container>
    </section>

    <section aria-labelledby="cta-heading" class="on-dark bg-navy-900 py-20 text-white lg:py-24">
        <x-container class="text-center">
            <h2 id="cta-heading" class="font-display text-3xl font-semibold sm:text-4xl">{{ page_text('service.ctaTitle', ['service' => $name]) }}</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-on-dark-muted">{{ page_text('service.ctaText') }}</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <x-button-link href="/quote">{{ label('buttons.requestQuote') }}</x-button-link>
                <x-button-link href="/contact" variant="outline-light">{{ label('buttons.contactUs') }}</x-button-link>
            </div>
        </x-container>
    </section>
@endsection
