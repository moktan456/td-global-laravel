@extends('layouts.app', [
    'title' => page_text('calculator.metaTitle'),
    'description' => page_text('calculator.metaDescription'),
    'canonical' => '/calculator',
])

@section('content')
    <x-page-header :title="page_text('calculator.title')" path="/calculator" :crumbs="[['label' => 'Cost calculator']]">
        <x-slot:intro><p>{{ page_text('calculator.intro') }}</p></x-slot:intro>
    </x-page-header>
    <x-container class="py-16 lg:py-24">
        @include('pages.calculator-form')
    </x-container>
@endsection
