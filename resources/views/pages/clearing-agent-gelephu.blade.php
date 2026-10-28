@extends('layouts.app', [
    'title' => page_text('agent.metaTitle'),
    'description' => page_text('agent.metaDescription'),
    'canonical' => '/clearing-agent-gelephu',
])

@php
    $phone = site('contact.phone');
    $phoneHref = site('contact.phoneHref');
    $email = site('contact.email');
    $address = site('contact.address');
    $whatsapp = whatsapp_url(site('contact.whatsapp'));
@endphp

@section('content')
    <x-json-ld :data="\App\Support\Seo::faq(content('faq'))" />
    <x-page-header :title="page_text('agent.title')" path="/clearing-agent-gelephu" :crumbs="[['label' => 'Clearing agent in Gelephu']]">
        <x-slot:intro><p>{{ page_text('agent.intro') }}</p></x-slot:intro>
    </x-page-header>

    <section aria-labelledby="intro-heading" class="py-20 lg:py-28">
        <x-container class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <x-section-heading id="intro-heading" :title="page_text('agent.introTitle')" />
                <div data-reveal class="mt-6 grid max-w-2xl gap-4 text-lg text-muted">
                    @foreach (page_text('agent.introParagraphs') as $para)
                        <p>{{ $para }}</p>
                    @endforeach
                </div>
                <ul data-reveal class="mt-8 grid gap-3 text-ink">
                    @if (is_filled($address))
                        <li class="flex items-center gap-3"><x-lucide name="map-pin" class="size-5 shrink-0 text-brand" /><span>{{ $address }}</span></li>
                    @endif
                    @if (is_filled($phone))
                        <li class="flex items-center gap-3">
                            <x-lucide name="phone" class="size-5 shrink-0 text-brand" />
                            @if ($phoneHref)
                                <a href="tel:{{ $phoneHref }}" class="underline underline-offset-4">{{ $phone }}</a>
                            @else
                                <span>{{ $phone }}</span>
                            @endif
                        </li>
                    @endif
                    @if ($whatsapp)
                        <li class="flex items-center gap-3">
                            <x-lucide name="message-circle" class="size-5 shrink-0 text-brand" />
                            <a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="underline underline-offset-4">{{ label('buttons.whatsapp') }}<span class="sr-only"> {{ label('a11y.newTab') }}</span></a>
                        </li>
                    @endif
                    <li class="flex items-center gap-3">
                        <x-lucide name="mail" class="size-5 shrink-0 text-brand" />
                        <a href="mailto:{{ $email }}" class="break-all underline underline-offset-4">{{ $email }}</a>
                    </li>
                </ul>
                <div data-reveal class="mt-8 flex flex-wrap gap-3">
                    <x-button-link href="/quote">{{ label('buttons.requestQuote') }}</x-button-link>
                    <x-button-link href="/contact" variant="outline">{{ label('buttons.contactUs') }}</x-button-link>
                </div>
            </div>
            <div data-reveal class="{{ ui('photoFrame') }} aspect-[4/3]">
                <x-photo name="heroRoad" sizes="(min-width: 1024px) 45vw, 100vw" :zoom="true" />
            </div>
        </x-container>
    </section>

    <section aria-labelledby="clear-heading" class="bg-sand py-20 lg:py-28">
        <x-container>
            <x-section-heading id="clear-heading" :eyebrow="page_text('agent.servicesEyebrow')" :title="page_text('agent.servicesTitle')">
                <x-slot:intro><p>{{ page_text('agent.servicesIntro') }}</p></x-slot:intro>
            </x-section-heading>
            <ul data-reveal-group class="mt-12 grid gap-6 sm:grid-cols-2">
                @foreach (content('services') as $s)
                    <li class="flex flex-col rounded-card border border-line bg-white p-6 {{ ui('cardLift') }}">
                        <h3 class="font-display text-xl font-semibold text-navy-900">{{ $s['title'] }}</h3>
                        <p class="mt-2 flex-1 text-muted">{{ $s['summary'] }}</p>
                        <a href="/services/{{ $s['slug'] }}" class="mt-4 inline-flex min-h-11 items-center font-semibold text-brand underline underline-offset-4 hover:text-brand-dark">{{ label('buttons.readMore') }}<span class="sr-only"> about {{ \Illuminate\Support\Str::lower($s['title']) }}</span></a>
                    </li>
                @endforeach
            </ul>
        </x-container>
    </section>

    <section aria-labelledby="steps-heading" class="py-20 lg:py-28">
        <x-container>
            <x-section-heading id="steps-heading" :eyebrow="page_text('agent.stepsEyebrow')" :title="page_text('agent.stepsTitle')">
                <x-slot:intro><p>{{ page_text('agent.stepsIntro') }}</p></x-slot:intro>
            </x-section-heading>
            <div class="mt-12">
                <x-process-steps :steps="content('process')" />
            </div>
        </x-container>
    </section>

    <section aria-labelledby="faq-heading" class="bg-sand py-20 lg:py-28">
        <x-container class="max-w-4xl">
            <x-section-heading id="faq-heading" :eyebrow="page_text('agent.faqEyebrow')" :title="page_text('agent.faqTitle')">
                <x-slot:intro><p>{{ page_text('agent.faqIntro') }}</p></x-slot:intro>
            </x-section-heading>
            <div data-reveal-group class="mt-10 grid gap-3">
                @foreach (content('faq') as $f)
                    <details class="group rounded-card border border-line bg-white">
                        <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-4 rounded-card px-5 py-4 font-semibold text-navy-900 marker:content-none focus-visible:-outline-offset-3 [&::-webkit-details-marker]:hidden">
                            <span>{{ $f['question'] }}</span>
                            <x-lucide name="chevron-down" class="size-5 shrink-0 text-brand transition-transform duration-(--duration-hover) ease-out-quart group-open:rotate-180" />
                        </summary>
                        <p class="px-5 pb-5 text-muted">{{ $f['answer'] }}</p>
                    </details>
                @endforeach
            </div>
        </x-container>
    </section>

    <section aria-labelledby="cta-heading" class="on-dark bg-navy-900 py-20 text-white lg:py-24">
        <x-container class="text-center">
            <h2 id="cta-heading" class="font-display text-3xl font-semibold sm:text-4xl">{{ page_text('agent.ctaTitle') }}</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-on-dark-muted">{{ page_text('agent.ctaText') }}</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <x-button-link href="/quote">{{ label('buttons.requestQuote') }}</x-button-link>
                <x-button-link href="/calculator" variant="outline-light">{{ label('buttons.estimateCost') }}</x-button-link>
            </div>
        </x-container>
    </section>
@endsection
