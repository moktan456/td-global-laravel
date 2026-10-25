@extends('layouts.staff', ['title' => $client->company ?: $client->full_name, 'subtitle' => $client->company ? $client->full_name : null, 'crumbs' => [['label' => 'Clients', 'href' => '/staff/clients'], ['label' => $client->company ?: $client->full_name]]])

@section('actions')
    <x-button-link href="/staff/shipments/new?client={{ $client->id }}"><x-lucide name="plus" class="mr-2 size-5" />New request</x-button-link>
@endsection

@php
    $tabs = ['requests' => 'Requests ('.$requestCount.')'] + ($telegram ? ['telegram' => 'Telegram'] : []) + ['details' => 'Details'];
    $connected = (bool) $client->telegram_chat_id;
@endphp

@section('content')
    <div class="grid gap-6">
        {{-- Who this is, at a glance. --}}
        <div class="flex flex-wrap items-center gap-x-8 gap-y-4 rounded-card border border-line bg-surface px-6 py-5">
            <div class="min-w-0">
                <p class="break-all font-semibold text-navy-900">{{ $client->email }}</p>
                <p class="text-muted">{{ $client->phone }}</p>
            </div>
            <dl class="flex flex-wrap gap-x-8 gap-y-3">
                <div><dt class="text-sm text-muted">Requests</dt><dd class="font-display text-2xl font-semibold text-navy-900">{{ $requestCount }}</dd></div>
                <div><dt class="text-sm text-muted">In progress</dt><dd class="font-display text-2xl font-semibold text-navy-900">{{ $inProgress }}</dd></div>
                @if ($telegram)
                    <div>
                        <dt class="text-sm text-muted">Telegram</dt>
                        <dd class="mt-1">
                            @if ($connected && $client->notify_telegram)
                                <span class="inline-flex items-center rounded-full bg-success/15 px-3 py-1 text-sm font-semibold text-success">Connected</span>
                            @elseif ($connected)
                                <span class="inline-flex items-center rounded-full bg-gold/35 px-3 py-1 text-sm font-semibold text-navy-900">Connected, off</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-sand px-3 py-1 text-sm font-semibold text-navy-900">Not connected</span>
                            @endif
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        <nav aria-label="Client sections" class="border-b border-line">
            <ul class="-mb-px flex flex-wrap gap-1">
                @foreach ($tabs as $key => $label)
                    <li>
                        <a href="/staff/clients/{{ $client->id }}?tab={{ $key }}" @if ($tab === $key) aria-current="page" @endif class="inline-flex min-h-11 items-center border-b-2 px-4 font-semibold transition-colors duration-150 {{ $tab === $key ? 'border-navy-900 text-navy-900' : 'border-transparent text-muted hover:text-navy-900' }}">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        @if ($tab === 'requests')
            <section aria-labelledby="requests-heading">
                <h2 id="requests-heading" class="sr-only">Requests</h2>
                @if ($shipments->count())
                    <div class="overflow-hidden rounded-card border border-line bg-surface">
                        <div aria-hidden="true" class="hidden gap-x-6 border-b border-line bg-sand px-5 py-3 text-sm font-semibold text-navy-900 md:grid md:grid-cols-[11rem_minmax(0,1.4fr)_15rem_6.5rem]">
                            <span>Tracking number</span><span>Goods</span><span>Stage</span><span>Updated</span>
                        </div>
                        <ul class="divide-y divide-line">
                            @foreach ($shipments as $s)
                                <li class="grid items-center gap-x-6 gap-y-2 px-5 py-4 transition-colors duration-150 hover:bg-sand/50 md:grid-cols-[11rem_minmax(0,1.4fr)_15rem_6.5rem]">
                                    <p><a href="/staff/shipments/{{ $s->tracking_number }}" class="font-mono font-semibold text-link underline underline-offset-2">{{ $s->tracking_number }}</a></p>
                                    <p class="min-w-0 break-words"><span class="sr-only">Goods: </span>{{ $s->goods }}<span class="block text-sm capitalize text-muted">{{ $s->direction }} · {{ $s->mode }} freight</span></p>
                                    <p><span class="sr-only">Stage: </span>@include('staff.stage-badges', ['s' => $s])</p>
                                    <p class="text-sm text-muted md:text-base"><span class="sr-only">Updated </span>{{ $s->updated_at->timezone('Asia/Thimphu')->format('j M Y') }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="rounded-card border border-dashed border-line bg-surface px-6 py-10 text-center">
                        <p class="font-semibold text-navy-900">No requests yet for this client.</p>
                        <p class="mt-1 text-muted">Create the first one and the tracking number goes to them.</p>
                        <div class="mt-4"><x-button-link href="/staff/shipments/new?client={{ $client->id }}">New request</x-button-link></div>
                    </div>
                @endif
                <x-pager :paginator="$shipments" label="requests" />
            </section>
        @elseif ($tab === 'telegram')
            <section aria-labelledby="telegram-heading" class="max-w-2xl rounded-card border border-line bg-surface p-6">
                <h2 id="telegram-heading" class="font-display text-2xl font-semibold text-navy-900">Telegram</h2>
                @if ($connected)
                    <p class="mt-2 text-muted">{{ $client->full_name }} is connected{{ $client->telegram_linked_at ? ' since '.$client->telegram_linked_at->timezone('Asia/Thimphu')->format('j M Y') : '' }}. Updates go to them on Telegram.</p>
                    @include('staff.telegram-bot-link', ['client' => $client])
                    <div class="mt-5 border-t border-line pt-2">
                        @include('staff.telegram-link-form', ['client' => $client, 'state' => form_state('tglink'.$client->id)])
                    </div>
                @else
                    <p class="mt-2 text-muted">{{ $client->full_name }} isn't connected yet. Make a one-time link, then show them the QR code or send the link. They open it and press Start.</p>
                    @include('staff.telegram-link-form', ['client' => $client, 'state' => form_state('tglink'.$client->id)])
                @endif
            </section>
        @else
            <section aria-labelledby="details-heading" class="max-w-2xl rounded-card border border-line bg-surface p-6">
                <h2 id="details-heading" class="font-display text-2xl font-semibold text-navy-900">Details</h2>
                <dl class="mt-4 grid grid-cols-[auto_1fr] gap-x-8 gap-y-3">
                    <dt class="text-muted">Name</dt><dd>{{ $client->full_name }}</dd>
                    <dt class="text-muted">Company</dt><dd>{{ $client->company ?: '—' }}</dd>
                    <dt class="text-muted">Email</dt><dd class="min-w-0 break-words">{{ $client->email }}</dd>
                    <dt class="text-muted">Phone</dt><dd>{{ $client->phone }}</dd>
                    <dt class="text-muted">Registered</dt><dd>{{ $client->created_at->timezone('Asia/Thimphu')->format('j M Y') }}, by {{ $client->registered_by === 'staff' ? 'staff' : 'the client' }}</dd>
                    <dt class="text-muted">Email updates</dt><dd>{{ $client->notify_email ? 'On' : 'Off (the client turned them off)' }}</dd>
                    @if ($telegram)
                        <dt class="text-muted">Telegram updates</dt><dd>{{ $connected ? ($client->notify_telegram ? 'On' : 'Off (the client turned them off)') : 'Not connected' }}</dd>
                    @endif
                </dl>
            </section>
        @endif
    </div>
@endsection
