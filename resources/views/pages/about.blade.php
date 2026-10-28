@extends('layouts.app', [
    'title' => page_text('about.metaTitle'),
    'description' => page_text('about.metaDescription'),
    'canonical' => '/about',
])

@section('content')
    <x-page-header :title="page_text('about.title')" path="/about" :crumbs="[['label' => 'About us']]">
        <x-slot:intro><p>{{ page_text('about.intro') }}</p></x-slot:intro>
    </x-page-header>
    <x-home.about :links="false" />
    @include('about.certificates')
    <x-home.why-us />
    <x-home.team />
    <x-home.partners />
    <x-home.trust />
@endsection
