@extends('layouts.staff', ['title' => 'New request', 'crumbs' => [['label' => 'Shipments', 'href' => '/staff'], ['label' => 'New request']]])

@section('content')
        <div class="max-w-3xl">
            @include('staff.shipment-form', ['state' => $state, 'clients' => $clients, 'assignees' => $assignees, 'chosenClient' => $chosenClient ?? null])
        </div>
@endsection
