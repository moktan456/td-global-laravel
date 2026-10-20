@extends('layouts.app', [
    'title' => 'Clearing Agent Pricing in Gelephu (Nu.)',
    'description' => 'Simple customs clearing packs from '.content('site.plans.0.price').' for one-off shipments and regular importers in Gelephu and GMC.',
    'canonical' => '/pricing',
])

@section('content')
    <x-page-header title="Clearing agent pricing" path="/pricing" :crumbs="[['label' => 'Pricing']]">
        <x-slot:intro><p>Simple packs for one-off shipments and regular importers in Gelephu and GMC.</p></x-slot:intro>
    </x-page-header>
    <x-home.pricing />
    <x-home.calculator-cta />
@endsection
