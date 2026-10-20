@extends('layouts.app', [
    'title' => 'Track Your Clearance Shipment',
    'description' => 'Check the customs clearance status of your shipment with TD Global Clearing & Logistics in Gelephu, GMC.',
    'canonical' => '/track',
])

@section('content')
    <x-page-header title="Track your shipment" path="/track" :crumbs="[['label' => 'Track shipment']]">
        <x-slot:intro><p>Enter the tracking number from your quote or confirmation. No sign-in needed.</p></x-slot:intro>
    </x-page-header>
    <x-container class="max-w-6xl py-16 lg:py-24">
        <div class="grid items-start gap-10 lg:grid-cols-2 lg:grid-rows-[auto_1fr] lg:gap-x-16 lg:gap-y-10">
            @include('track.lookup', ['state' => $state])
            <details class="max-w-lg rounded-card border border-line bg-white p-5 lg:col-start-1 lg:row-start-2">
                <summary class="min-h-11 cursor-pointer py-2 font-semibold text-brand">Lost your tracking number?</summary>
                <p class="mt-2 text-muted">Enter the phone number or email you gave us and we&apos;ll send your tracking numbers to you.</p>
                <div class="mt-5">
                    @include('track.reminder', ['state' => $reminderState])
                </div>
            </details>
        </div>
    </x-container>
@endsection
