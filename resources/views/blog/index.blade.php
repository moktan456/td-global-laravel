@extends('layouts.app', [
    'title' => page_text('blog.metaTitle'),
    'description' => page_text('blog.metaDescription'),
    'canonical' => '/blog',
])

@section('content')
    <x-page-header :title="page_text('blog.title')" path="/blog" :crumbs="[['label' => 'Blog']]">
        <x-slot:intro><p>{{ page_text('blog.intro') }}</p></x-slot:intro>
    </x-page-header>
    <x-container class="py-16 lg:py-24">
        @if (! count(\App\Support\Content::articles()))
            <p class="text-lg text-muted">{{ page_text('blog.empty') }}</p>
        @endif
        <ul data-reveal-group class="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
            @foreach (\App\Support\Content::articles() as $a)
                <li>
                    <article class="group relative flex h-full flex-col">
                        <div class="relative aspect-[3/2] overflow-hidden rounded-card">
                            <x-photo :name="$a['image']" sizes="(min-width: 1024px) 33vw, (min-width: 768px) 50vw, 100vw" :decorative="true" :zoom="true" />
                        </div>
                        <p class="mt-5 text-sm font-semibold uppercase tracking-[0.14em] text-brand">{{ $a['category'] }}</p>
                        <h2 class="mt-2 font-display text-2xl font-semibold text-navy-900">
                            <a href="/blog/{{ $a['slug'] }}" class="underline decoration-transparent decoration-2 underline-offset-4 transition-[text-decoration-color] duration-(--duration-hover) after:absolute after:inset-0 group-hover:decoration-brand group-focus-within:decoration-brand">{{ $a['title'] }}</a>
                        </h2>
                        <p class="mt-2 flex-1 text-muted">{{ $a['excerpt'] }}</p>
                        <p class="mt-4 text-sm text-muted"><time datetime="{{ $a['date'] }}">{{ format_date($a['date']) }}</time></p>
                    </article>
                </li>
            @endforeach
        </ul>
    </x-container>
@endsection
