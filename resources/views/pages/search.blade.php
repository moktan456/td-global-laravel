@extends('layouts.app', ['title' => page_text('search.title'), 'noindex' => true])

@section('content')
    <x-page-header :title="page_text('search.title')" :crumbs="[['label' => page_text('search.title')]]" />
    <x-container class="max-w-3xl py-16">
        <x-search-form id="page-search" :label="page_text('search.searchAgain')" class="max-w-lg" />
        @if ($query !== '')
            <div role="status">
                <h2 class="mt-10 font-display text-2xl font-semibold text-navy-900">
                    {{ count($results) === 0 ? page_text('search.noResults', ['query' => $query]) : (count($results) === 1 ? page_text('search.oneResult', ['query' => $query]) : page_text('search.manyResults', ['query' => $query, 'count' => count($results)])) }}
                </h2>
            </div>
            @if (count($results) === 0)
                <p class="mt-3 text-muted">{{ page_text('search.tryText') }} <a href="/contact" class="font-semibold text-brand underline underline-offset-2">{{ page_text('search.askLink') }}</a>.</p>
            @endif
            <ul class="mt-6 divide-y divide-line border-y border-line">
                @foreach ($results as $r)
                    <li class="py-5">
                        <a href="{{ $r['href'] }}" class="font-display text-xl font-semibold text-navy-900 underline decoration-brand decoration-2 underline-offset-4 hover:text-brand">{{ $r['title'] }}</a>
                        <p class="mt-1 text-muted">{{ $r['description'] }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-container>
@endsection
