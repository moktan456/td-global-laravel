@extends('layouts.staff', ['title' => 'Not found'])

@section('content')
    <div class="max-w-xl rounded-card border border-line bg-white p-8">
        <p class="font-display text-2xl font-semibold text-navy-900">We couldn't find that page</p>
        <p class="mt-2 text-muted">The shipment or client may have been typed wrongly, or the link is old. Go back to the lists and search for it.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <x-button-link href="/staff">Shipments</x-button-link>
            <x-button-link href="/staff/clients" variant="outline">Clients</x-button-link>
        </div>
    </div>
@endsection
