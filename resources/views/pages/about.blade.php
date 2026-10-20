@extends('layouts.app', [
    'title' => 'About Us — Clearing Agent in Gelephu',
    'description' => site('legal.name').' is a clearing agent and logistics partner registered in '.site('legal.jurisdiction').' (GMC), UEN '.site('legal.uen').'.',
    'canonical' => '/about',
])

@section('content')
    <x-page-header title="About us" path="/about" :crumbs="[['label' => 'About us']]">
        <x-slot:intro><p>A clearing agent and logistics partner registered in Gelephu Mindfulness City (GMC).</p></x-slot:intro>
    </x-page-header>
    <x-home.about :links="false" />
    @include('about.certificates')
    <x-home.why-us />
    <x-home.team />
    <x-home.partners />
    <x-home.trust />
@endsection
