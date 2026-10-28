@extends('layouts.app', [
    'title' => $article['title'],
    'description' => $article['excerpt'],
    'canonical' => '/blog/'.$article['slug'],
])

@section('content')
    <x-page-header :title="$article['title']" :path="'/blog/'.$article['slug']" :crumbs="[['label' => 'Blog', 'href' => '/blog'], ['label' => $article['title']]]">
        <x-slot:intro>
            <p>{{ $article['category'] }} · {{ is_filled($article['author']) ? $article['author'] : site('name') }} · <time datetime="{{ $article['date'] }}">{{ format_date($article['date']) }}</time></p>
        </x-slot:intro>
    </x-page-header>
    <x-json-ld :data="\App\Support\Seo::article($article)" />
    <x-container class="max-w-3xl py-16 lg:py-20">
        <article>
            <div class="relative aspect-[16/9] overflow-hidden rounded-card">
                <x-photo :name="$article['image']" sizes="(min-width: 768px) 720px, 100vw" :priority="true" />
            </div>
            <p class="mt-10 text-xl text-ink">{{ $article['excerpt'] }}</p>
            @foreach ($article['body'] as $block)
                <section class="mt-8">
                    @if (! empty($block['heading']))
                        <h2 class="font-display text-2xl font-semibold text-navy-900">{{ $block['heading'] }}</h2>
                    @endif
                    @foreach ($block['paragraphs'] as $para)
                        <p class="mt-4 text-lg">{{ $para }}</p>
                    @endforeach
                    @if (! empty($block['list']))
                        <ul class="mt-4 list-disc space-y-2 pl-6 text-lg marker:text-brand">
                            @foreach ($block['list'] as $li)
                                <li>{{ $li }}</li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach
            <div class="mt-12 rounded-card bg-sand p-6">
                <p class="font-display text-xl font-semibold text-navy-900">{{ page_text('blog.ctaTitle') }}</p>
                <p class="mt-2 text-muted">{{ page_text('blog.ctaText') }}</p>
                <x-button-link href="/quote" class="mt-4">{{ page_text('blog.ctaButton') }}</x-button-link>
            </div>
        </article>
        <a href="/blog" class="group/back mt-10 inline-flex min-h-11 items-center gap-2 font-semibold text-brand underline decoration-2 underline-offset-4 hover:text-brand-dark">
            <x-lucide name="arrow-left" class="size-4 transition-transform duration-(--duration-hover) group-hover/back:-translate-x-1 group-focus-visible/back:-translate-x-1" />
            {{ label('buttons.allArticles') }}
        </a>
    </x-container>
@endsection
