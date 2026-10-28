@extends('layouts.app', [
    'title' => page_text('services.metaTitle'),
    'description' => page_text('services.metaDescription'),
    'canonical' => '/services',
])

@section('content')
    <x-json-ld :data="array_map(fn ($s) => \App\Support\Seo::service($s), content('services'))" />
    <x-page-header :title="page_text('services.title')" path="/services" :crumbs="[['label' => 'Services']]">
        <x-slot:intro><p>{{ page_text('services.intro') }}</p></x-slot:intro>
    </x-page-header>
    <section aria-label="Our services" class="py-16 lg:py-24">
        <x-container>
            <ul data-reveal-group class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (content('services') as $i => $s)
                    <li id="{{ $s['slug'] }}" class="scroll-mt-28">
                        <article class="group flex h-full flex-col overflow-hidden rounded-card border border-line bg-white {{ ui('cardLift') }}">
                            <div class="relative aspect-[4/3] overflow-hidden">
                                <x-photo :name="$s['image']" sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw" :priority="$i < 3" :zoom="true" />
                            </div>
                            <div class="flex flex-1 flex-col p-6">
                                <h2 class="font-display text-2xl font-semibold text-navy-900">{{ $s['title'] }}</h2>
                                <p class="mt-2 flex-1 text-muted">{{ $s['summary'] }}</p>
                                <a href="/services/{{ $s['slug'] }}" class="mt-4 inline-flex min-h-11 items-center gap-2 font-semibold text-brand underline decoration-2 underline-offset-4 hover:text-brand-dark">
                                    {{ label('buttons.learnMore') }}<span class="sr-only"> about {{ \Illuminate\Support\Str::lower($s['title']) }}</span>
                                    <x-lucide name="arrow-right" class="arrow-nudge size-4" />
                                </a>
                            </div>
                        </article>
                    </li>
                @endforeach
            </ul>
        </x-container>
    </section>
    <x-home.calculator-cta />
@endsection
