@extends('layouts.staff', ['title' => $s->tracking_number, 'subtitle' => $s->goods, 'crumbs' => [['label' => 'Shipments', 'href' => '/staff'], ['label' => $s->tracking_number]]])

@section('actions')
    <x-status-badge :status="$s->status" />
@endsection

@section('content')
        @if ($isNew)
            <p role="status" class="mb-6 rounded-tile bg-sand p-4 font-semibold text-navy-900">Request created. The tracking number has been sent to the client. See Notifications below for what was delivered.</p>
        @endif
        <div class="grid gap-8 lg:grid-cols-2">
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
                @php
                    $trackUrl = absolute_url('/track?n='.$s->tracking_number);
                    $waUrl = $s->client?->phone ? \App\Support\Messages::waLink($s->client->phone, \App\Support\Messages::whatsappTrackingNumber($s->client->full_name, $s->tracking_number, \App\Support\Messages::about($s), $trackUrl)) : null;
                @endphp
                <details id="tracking-tools" @if ($resendState->status !== 'idle') open @endif class="rounded-card border border-line bg-white p-5">
                    <summary class="min-h-11 cursor-pointer py-2 font-semibold text-brand">Client lost the tracking number? Show it and the link</summary>
                    <p class="mt-2 text-muted">Send it again, or copy it and give it yourself.</p>
                    <div class="mt-5 grid gap-5">
                        <div class="grid gap-2">
                            <label for="share-number" class="text-sm font-semibold text-navy-900">Tracking number</label>
                            <input id="share-number" readonly value="{{ $s->tracking_number }}" data-select-on-focus class="block w-full rounded-control border border-control bg-sand px-3 py-2.5 font-mono text-sm">
                            <div class="flex flex-wrap items-center gap-3">
                                <x-button type="button" variant="outline" data-copy="share-number">Copy number</x-button>
                                <span data-copy-status role="status" class="text-sm text-muted"></span>
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <label for="share-link" class="text-sm font-semibold text-navy-900">Link to the Track page</label>
                            <input id="share-link" readonly value="{{ $trackUrl }}" data-select-on-focus class="block w-full rounded-control border border-control bg-sand px-3 py-2.5 font-mono text-sm">
                            <div class="flex flex-wrap items-center gap-3">
                                <x-button type="button" variant="outline" data-copy="share-link">Copy link</x-button>
                                @if ($waUrl)
                                    <x-button-link :href="$waUrl" variant="outline" target="_blank" rel="noopener">Send on WhatsApp<span class="sr-only"> (opens WhatsApp with the message ready)</span></x-button-link>
                                @endif
                                <span data-copy-status role="status" class="text-sm text-muted"></span>
                            </div>
                        </div>
                        @include('staff.resend-form', ['state' => $resendState, 's' => $s])
                    </div>
                </details>
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
@endsection
