@extends('layouts.app', ['title' => 'Shipment', 'robots' => 'noindex, nofollow'])

@section('content')
    <x-page-header :title="$s->tracking_number" :crumbs="[['label' => 'Staff', 'href' => '/staff'], ['label' => $s->tracking_number]]">
        <x-slot:intro><p>{{ $s->goods }}</p></x-slot:intro>
    </x-page-header>
    <x-container class="py-10">
        <x-staff-nav current="" :name="auth()->user()->name" :role="auth()->user()->role" />
        @if ($isNew)
            <p role="status" class="mt-6 rounded-tile bg-sand p-4 font-semibold text-navy-900">Request created. The tracking number has been sent to the client. See Notifications below for what was delivered.</p>
        @endif
        <div class="mt-8 grid gap-10 lg:grid-cols-2">
            <div class="grid gap-8 self-start">
                <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 rounded-card border border-line bg-white p-6">
                    <dt class="text-muted">Stage</dt>
                    <dd><x-status-badge :status="$s->status" /></dd>
                    <dt class="text-muted">Client</dt>
                    <dd>{{ $s->client?->full_name }}{{ $s->client?->company ? ' · '.$s->client->company : '' }}</dd>
                    <dt class="text-muted">Contact</dt>
                    <dd>{{ $s->client?->email }} · {{ $s->client?->phone }}</dd>
                    <dt class="text-muted">Direction / method</dt>
                    <dd class="capitalize">{{ $s->direction }} · {{ $s->mode }} freight</dd>
                    <dt class="text-muted">Requested by</dt>
                    <dd>{{ $s->requested_by === 'staff' ? "Staff, on the client's behalf" : 'The client, online' }}</dd>
                    @if ($s->details)
                        <dt class="text-muted">Details</dt>
                        <dd>{{ $s->details }}</dd>
                    @endif
                </dl>
                <section aria-labelledby="update-heading" class="rounded-card border border-line bg-white p-6">
                    <h2 id="update-heading" class="font-display text-2xl font-semibold text-navy-900">Update stage</h2>
                    <div class="mt-5">
                        @include('staff.status-form', ['state' => $state, 's' => $s])
                    </div>
                </section>
            </div>
            <div class="grid gap-10 self-start">
                <section aria-labelledby="timeline-heading">
                    <h2 id="timeline-heading" class="font-display text-2xl font-semibold text-navy-900">Timeline</h2>
                    <div class="mt-6">
                        <x-timeline audience="staff" :events="$events" />
                    </div>
                </section>
                <section aria-labelledby="notifications-heading">
                    <h2 id="notifications-heading" class="font-display text-2xl font-semibold text-navy-900">Notifications</h2>
                    <div class="mt-6">
                        <x-notification-log :rows="$log" />
                    </div>
                </section>
            </div>
        </div>
    </x-container>
@endsection
