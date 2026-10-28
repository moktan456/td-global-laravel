@extends('layouts.app', ['title' => page_text('notFound.metaTitle')])

@section('content')
    <x-container class="max-w-2xl py-24 text-center">
        <p class="text-sm font-semibold uppercase tracking-[0.14em] text-brand">{{ page_text('notFound.eyebrow') }}</p>
        <h1 class="mt-3 font-display text-4xl font-semibold text-navy-900">{{ page_text('notFound.title') }}</h1>
        <p class="mt-4 text-lg text-muted">{{ page_text('notFound.text') }}</p>
        <x-search-form id="notfound-search" :label="page_text('notFound.searchLabel')" class="mx-auto mt-8 max-w-sm" />
        <x-button-link href="/" class="mt-6">{{ page_text('notFound.button') }}</x-button-link>
    </x-container>
@endsection
