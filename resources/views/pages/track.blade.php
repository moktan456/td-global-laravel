@extends('layouts.app', [
    'title' => 'Track Your Clearance Shipment',
    'description' => 'Check the customs clearance status of your shipment with TD Global Clearing & Logistics in Gelephu, GMC.',
    'canonical' => '/track',
])

@section('content')
    <x-page-header title="Track your shipment" path="/track" :crumbs="[['label' => 'Track shipment']]">
        <x-slot:intro><p>Enter the tracking number from your quote or confirmation. No sign-in needed.</p></x-slot:intro>
    </x-page-header>
    <x-container class="max-w-3xl py-16 lg:py-24">
        <div class="grid gap-10">
            @include('track.lookup', ['state' => $state])
            <details class="rounded-card border border-line bg-white p-5">
                <summary class="min-h-11 cursor-pointer py-2 font-semibold text-brand">Lost your tracking number?</summary>
                <p class="mt-2 text-muted">Enter the phone number or email you gave us and we&apos;ll send your tracking numbers to you.</p>
                <div class="mt-5">
                    @include('track.reminder', ['state' => $reminderState])
                </div>
            </details>
        </div>
    </x-container>
@endsection
