@extends('layouts.staff', ['title' => $s->tracking_number, 'subtitle' => $s->goods, 'crumbs' => [['label' => 'Shipments', 'href' => '/staff'], ['label' => $s->tracking_number]]])

@section('actions')
    @foreach ($s->tracks() as $t)
        <span class="inline-flex items-center gap-2 text-sm text-muted">{{ \App\Support\Statuses::trackLabel($t) }} <x-status-badge :status="$s->stageOn($t)" /></span>
    @endforeach
@endsection

@section('mobilebar')
    @foreach ([['#update-heading', 'Update', 'package'], ['#details-heading', 'Details', 'user-round'], ['#timeline-heading', 'Timeline', 'clock']] as [$href, $label, $icon])
        <li><a href="{{ $href }}" class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-sm font-semibold text-white"><x-lucide :name="$icon" class="size-5" />{{ $label }}</a></li>
    @endforeach
@endsection

@php
    $trackUrl = absolute_url('/track?n='.$s->tracking_number);
    $waUrl = $s->client?->phone ? \App\Support\Messages::waLink($s->client->phone, \App\Support\Messages::whatsappTrackingNumber($s->client->full_name, $s->tracking_number, \App\Support\Messages::about($s), $trackUrl)) : null;
@endphp

@section('content')
    @if ($isNew)
        <p role="status" class="mb-6 rounded-tile bg-sand p-4 font-semibold text-navy-900">Request created. The tracking number has been sent to the client. See Activity below for what was delivered.</p>
    @endif
    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        {{-- Left: the work. Move the shipment on (one tab per track), then see where it has been. --}}
        <div class="grid gap-6 self-start">
            <section aria-labelledby="update-heading" class="rounded-card border border-line bg-surface p-6">
                <h2 id="update-heading" class="font-display text-2xl font-semibold text-navy-900">Update stage</h2>
                <nav data-live="tabs" aria-label="Track to update" class="mt-4 border-b border-line">
                    <ul class="-mb-px flex flex-wrap gap-1">
                        @foreach (\App\Support\Statuses::tracks() as $t)
                            @php($now = $s->stageOn($t))
                            <li>
                                <a href="/staff/shipments/{{ $s->tracking_number }}?track={{ $t }}" @if ($tab === $t) aria-current="page" @endif class="inline-flex min-h-11 items-center gap-2 border-b-2 px-4 font-semibold transition-colors duration-150 {{ $tab === $t ? 'border-navy-900 text-navy-900' : 'border-transparent text-muted hover:text-navy-900' }}">
                                    {{ \App\Support\Statuses::trackLabel($t) }}
                                    @if ($now)
                                        <span class="rounded-full bg-sand px-2 py-0.5 text-xs font-semibold text-navy-900">{{ \App\Support\Statuses::info($now)['label'] }}</span>
                                    @else
                                        <span class="rounded-full border border-line px-2 py-0.5 text-xs font-semibold text-muted">Off</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
                <div class="mt-5">
                    @if ($tab === 'transport')
                        @if ($s->transport_enabled)
                            @include('staff.transport-status-form', ['state' => $transportState, 's' => $s])
                        @else
                            @include('staff.track-off', ['s' => $s, 'track' => 'transport'])
                        @endif
                    @else
                        @if ($s->customs_enabled)
                            @include('staff.status-form', ['state' => $state, 's' => $s])
                        @else
                            @include('staff.track-off', ['s' => $s, 'track' => 'customs'])
                        @endif
                    @endif
                </div>
            </section>
            <section data-live="timeline" aria-labelledby="timeline-heading" class="rounded-card border border-line bg-surface p-6">
                <h2 id="timeline-heading" class="font-display text-2xl font-semibold text-navy-900">Timeline</h2>
                <div class="mt-6">
                    <x-timeline audience="staff" :events="$events" :show-tracks="count($s->tracks()) > 1" />
                </div>
            </section>
        </div>

        {{-- Right: who it is for, what is tracked, how to reach them, what was sent. --}}
        <div class="grid gap-6 self-start">
            <section aria-labelledby="details-heading" class="rounded-card border border-line bg-surface p-6">
                <h2 id="details-heading" class="font-display text-xl font-semibold text-navy-900">Details</h2>
                <dl class="mt-4 grid gap-4">
                    <div>
                        <dt class="text-sm text-muted">Client</dt>
                        <dd class="mt-0.5 break-words font-semibold">
                            @if ($s->client)
                                <a href="/staff/clients/{{ $s->client->id }}" class="text-link underline underline-offset-2">{{ $s->client->company ?: $s->client->full_name }}</a>
                                @if ($s->client->company)<span class="block font-normal text-muted">{{ $s->client->full_name }}</span>@endif
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-muted">Contact</dt>
                        <dd class="mt-0.5 break-words">{{ $s->client?->email }}<span class="block">{{ $s->client?->phone }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-sm text-muted">Direction and method</dt>
                        <dd class="mt-0.5 capitalize">{{ $s->direction }} · {{ $s->mode }} freight</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-muted">Requested by</dt>
                        <dd class="mt-0.5">{{ $s->requested_by === 'staff' ? "Staff, on the client's behalf" : 'The client, online' }}</dd>
                    </div>
                    @if ($s->details)
                        <div>
                            <dt class="text-sm text-muted">Notes on the request</dt>
                            <dd class="mt-0.5 break-words">{{ $s->details }}</dd>
                        </div>
                    @endif
                </dl>
                <div class="mt-5 border-t border-line pt-5">
                    @include('staff.assign-form', ['state' => $assignState, 's' => $s, 'assignees' => $assignees])
                </div>
            </section>

            <section aria-labelledby="tracks-heading" class="rounded-card border border-line bg-surface p-6">
                <h2 id="tracks-heading" class="font-display text-xl font-semibold text-navy-900">What we track</h2>
                <div class="mt-4">
                    @include('staff.tracks-form', ['state' => $tracksState, 's' => $s])
                </div>
            </section>

            @if ($s->transport_enabled)
                <section aria-labelledby="tdetails-heading" class="rounded-card border border-line bg-surface p-6">
                    <h2 id="tdetails-heading" class="font-display text-xl font-semibold text-navy-900">Transport details</h2>
                    @if ($s->delivered_at)
                        <p class="mt-3 rounded-tile bg-success/10 p-3 text-sm font-semibold text-navy-900">Delivered{{ $s->delivered_to ? ' to '.$s->delivered_to : '' }} on {{ $s->delivered_at->timezone('Asia/Thimphu')->format('j M Y, H:i') }}.</p>
                    @endif
                    <div class="mt-4">
                        @include('staff.transport-details-form', ['state' => $detailsState, 's' => $s])
                    </div>
                </section>
            @endif

            <details id="tracking-tools" @if ($resendState->status !== 'idle') open @endif class="rounded-card border border-line bg-surface p-5">
                <summary class="min-h-11 cursor-pointer py-2 font-semibold text-link">Client lost the tracking number? Show it and the link</summary>
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

            <section aria-labelledby="activity-heading" class="rounded-card border border-line bg-surface p-6">
                <h2 id="activity-heading" class="font-display text-xl font-semibold text-navy-900">Activity</h2>
                <p class="mt-1 text-sm text-muted">Messages sent to the client, and whether they arrived.</p>
                <div class="mt-4">
                    <x-notification-log :rows="$log" />
                </div>
            </section>
        </div>
    </div>
@endsection
