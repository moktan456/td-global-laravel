@extends('layouts.app', [
    'title' => 'Contact a Clearing Agent in Gelephu, GMC',
    'description' => 'Call '.site('contact.phone').' or email '.site('contact.email').'. '.site('shortName').' Clearing & Logistics, '.site('contact.address').'.',
    'canonical' => '/contact',
])

@section('content')
    <x-page-header title="Contact a clearing agent in Gelephu, GMC" path="/contact" :crumbs="[['label' => 'Contact us']]">
        <x-slot:intro><p>Send us a message and we’ll get back to you, usually within one business day.</p></x-slot:intro>
    </x-page-header>
    <x-home.contact :with-heading="false" />
@endsection
