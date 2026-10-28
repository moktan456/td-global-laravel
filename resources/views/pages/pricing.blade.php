@extends('layouts.app', [
    'title' => page_text('pricing.metaTitle'),
    'description' => page_text('pricing.metaDescription' , ['price' => content('site.plans.0.price')]),
    'canonical' => '/pricing',
])

@section('content')
    <x-page-header :title="page_text('pricing.title')" path="/pricing" :crumbs="[['label' => 'Pricing']]">
        <x-slot:intro><p>{{ page_text('pricing.intro') }}</p></x-slot:intro>
    </x-page-header>
    <x-home.pricing />
    <x-home.calculator-cta />
@endsection
