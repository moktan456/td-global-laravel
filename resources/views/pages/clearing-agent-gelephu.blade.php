@extends('layouts.app', [
    'title' => 'Clearing Agent in Gelephu & GMC',
    'description' => 'Clearing agent in Rabdeyling, Gelephu (GMC). Custom clearing and logistics for imports and exports by road and air. Get a free quote from TD Global.',
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
    <x-page-header title="Clearing agent in Gelephu & GMC" path="/clearing-agent-gelephu" :crumbs="[['label' => 'Clearing agent in Gelephu']]">
        <x-slot:intro><p>{{ site('name') }} is a customs clearing agent and logistics partner based in {{ site('seo.area') }}, {{ site('seo.region') }} (GMC), Bhutan.</p></x-slot:intro>
    </x-page-header>

    <section aria-labelledby="intro-heading" class="py-20 lg:py-28">
        <x-container class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <x-section-heading id="intro-heading" title="Custom clearing and logistics in Gelephu, GMC" />
                <div data-reveal class="mt-6 grid max-w-2xl gap-4 text-lg text-muted">
                    <p>A clearing agent, also called a customs broker, prepares and lodges your customs declaration, works out duties and taxes, and deals with customs until your goods are released. Getting the paperwork right the first time keeps your shipment moving.</p>
                    <p>We are based in {{ site('seo.area') }}, inside Gelephu Mindfulness City. We clear imports and exports for importers and exporters in Gelephu and GMC. We also arrange transport, storage and onward forwarding, so clearing and logistics are handled by one team.</p>
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
                            <a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="underline underline-offset-4">Chat on WhatsApp<span class="sr-only"> (opens in a new tab)</span></a>
                        </li>
                    @endif
                    <li class="flex items-center gap-3">
                        <x-lucide name="mail" class="size-5 shrink-0 text-brand" />
                        <a href="mailto:{{ $email }}" class="break-all underline underline-offset-4">{{ $email }}</a>
                    </li>
                </ul>
                <div data-reveal class="mt-8 flex flex-wrap gap-3">
                    <x-button-link href="/quote">Request a free quote</x-button-link>
                    <x-button-link href="/contact" variant="outline">Contact us</x-button-link>
                </div>
            </div>
            <div data-reveal class="{{ ui('photoFrame') }} aspect-[4/3]">
                <x-photo name="heroRoad" sizes="(min-width: 1024px) 45vw, 100vw" :zoom="true" />
            </div>
        </x-container>
    </section>

    <section aria-labelledby="clear-heading" class="bg-sand py-20 lg:py-28">
        <x-container>
            <x-section-heading id="clear-heading" eyebrow="What we handle" title="Clearing services for importers and exporters">
                <x-slot:intro><p>Everything needed to get cargo cleared, by road or by air.</p></x-slot:intro>
            </x-section-heading>
            <ul data-reveal-group class="mt-12 grid gap-6 sm:grid-cols-2">
                @foreach (content('services') as $s)
                    <li class="flex flex-col rounded-card border border-line bg-white p-6 {{ ui('cardLift') }}">
                        <h3 class="font-display text-xl font-semibold text-navy-900">{{ $s['title'] }}</h3>
                        <p class="mt-2 flex-1 text-muted">{{ $s['summary'] }}</p>
                        <a href="/services/{{ $s['slug'] }}" class="mt-4 inline-flex min-h-11 items-center font-semibold text-brand underline underline-offset-4 hover:text-brand-dark">Read more<span class="sr-only"> about {{ \Illuminate\Support\Str::lower($s['title']) }}</span></a>
                    </li>
                @endforeach
            </ul>
        </x-container>
    </section>

    <section aria-labelledby="steps-heading" class="py-20 lg:py-28">
        <x-container>
            <x-section-heading id="steps-heading" eyebrow="How it works" title="Custom clearing in four steps">
                <x-slot:intro><p>From your first email to release of your goods.</p></x-slot:intro>
            </x-section-heading>
            <div class="mt-12">
                <x-process-steps :steps="content('process')" />
            </div>
        </x-container>
    </section>

    <section aria-labelledby="faq-heading" class="bg-sand py-20 lg:py-28">
        <x-container class="max-w-4xl">
            <x-section-heading id="faq-heading" eyebrow="Questions" title="Clearing agent questions, answered">
                <x-slot:intro><p>Can’t find what you need? Call us or send a message.</p></x-slot:intro>
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
            <h2 id="cta-heading" class="font-display text-3xl font-semibold sm:text-4xl">Need a clearing agent in Gelephu?</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-on-dark-muted">Tell us what you are shipping and we will get back to you, usually within one business day.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <x-button-link href="/quote">Request a free quote</x-button-link>
                <x-button-link href="/calculator" variant="outline-light">Estimate the cost</x-button-link>
            </div>
        </x-container>
    </section>
@endsection
