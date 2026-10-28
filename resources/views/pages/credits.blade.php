@extends('layouts.app', [
    'title' => page_text('credits.metaTitle'),
    'description' => page_text('credits.metaDescription'),
    'canonical' => '/credits',
])

@section('content')
    <x-page-header :title="page_text('credits.title')" :crumbs="[['label' => page_text('credits.title')]]">
        <x-slot:intro>
            <p>{!! str_replace('{Unsplash}', '<a href="https://unsplash.com/license" class="font-semibold text-white underline underline-offset-2">Unsplash</a>', e(page_text('credits.intro'))) !!}</p>
        </x-slot:intro>
    </x-page-header>
    <x-container class="max-w-3xl py-16">
        <ul class="divide-y divide-line border-y border-line">
            @foreach (content('credits') as $key => $c)
                <li class="py-4">
                    <p class="text-ink">{{ content('images')[$key]['alt'] ?? $key }}</p>
                    <p class="mt-1 text-sm text-muted">
                        @if (! empty($c['placeholder']) || empty($c['photographer']))
                            {{ page_text('credits.placeholder') }} (Unsplash ID {{ $c['unsplashId'] }})
                        @else
                            {{ page_text('credits.photoBy') }}
                            @if (! empty($c['photographerUrl']))
                                <a href="{{ $c['photographerUrl'] }}" class="font-semibold text-brand underline underline-offset-2">{{ $c['photographer'] }}</a>
                            @else
                                {{ $c['photographer'] }}
                            @endif
                            {{ page_text('credits.on') }}
                            <a href="{{ $c['photoUrl'] }}" class="font-semibold text-brand underline underline-offset-2">Unsplash</a>
                        @endif
                    </p>
                </li>
            @endforeach
        </ul>
    </x-container>
@endsection
