@extends('layouts.app', [
    'title' => 'Get a Clearing Quote in Gelephu, GMC',
    'description' => 'Tell us about your import or export and get a free customs clearing quote from a clearing agent in Gelephu, GMC.',
    'canonical' => '/quote',
])

@section('content')
    <x-page-header title="Request a free quote" path="/quote" :crumbs="[['label' => 'Request a quote']]">
        <x-slot:intro><p>Tell us about your shipment. A broker will reply with a quote by email.</p></x-slot:intro>
    </x-page-header>
    <x-container class="max-w-3xl py-16 lg:py-24">
        @include('forms.quote', ['plan' => $selected])
    </x-container>
@endsection
