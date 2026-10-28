@extends('layouts.app', [
    'title' => page_text('contact.metaTitle'),
    'description' => page_text('contact.metaDescription'),
    'canonical' => '/contact',
])

@section('content')
    <x-page-header :title="page_text('contact.title')" path="/contact" :crumbs="[['label' => 'Contact us']]">
        <x-slot:intro><p>{{ page_text('contact.intro') }}</p></x-slot:intro>
    </x-page-header>
    <x-home.contact :with-heading="false" />
@endsection
