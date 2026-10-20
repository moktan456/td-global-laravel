@extends('layouts.app', [
    'title' => 'Customs Clearing Cost Calculator',
    'description' => 'Estimate your customs clearing fee for an import or export in under a minute. Indicative fees from a clearing agent in Gelephu, GMC.',
    'canonical' => '/calculator',
])

@section('content')
    <x-page-header title="Clearance cost calculator" path="/calculator" :crumbs="[['label' => 'Cost calculator']]">
        <x-slot:intro><p>Answer four questions for an indicative clearance fee. Your estimate updates as you go.</p></x-slot:intro>
    </x-page-header>
    <x-container class="py-16 lg:py-24">
        @include('pages.calculator-form')
    </x-container>
@endsection
