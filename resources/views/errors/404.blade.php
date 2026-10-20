@extends('layouts.app', ['title' => 'Page not found'])

@section('content')
    <x-container class="max-w-2xl py-24 text-center">
        <p class="text-sm font-semibold uppercase tracking-[0.14em] text-brand">Error 404</p>
        <h1 class="mt-3 font-display text-4xl font-semibold text-navy-900">We couldn’t find that page</h1>
        <p class="mt-4 text-lg text-muted">It may have moved. Try searching, or head back to the homepage.</p>
        <x-search-form id="notfound-search" label="Find a page" class="mx-auto mt-8 max-w-sm" />
        <x-button-link href="/" class="mt-6">Back to homepage</x-button-link>
    </x-container>
@endsection
