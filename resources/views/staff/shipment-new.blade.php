@extends('layouts.app', ['title' => 'New request', 'robots' => 'noindex, nofollow'])

@section('content')
    <x-page-header title="New request on a client's behalf" :crumbs="[['label' => 'Staff', 'href' => '/staff'], ['label' => 'New request']]" />
    <x-container class="py-10">
        <x-staff-nav current="/staff/shipments/new" :name="auth()->user()->name" :role="auth()->user()->role" />
        <div class="mt-8 max-w-3xl">
            @include('staff.shipment-form', ['state' => $state, 'clients' => $clients])
        </div>
    </x-container>
@endsection
