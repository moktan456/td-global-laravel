@extends('layouts.staff', ['title' => 'Shipments'])

@section('actions')
    <x-button-link href="/staff/shipments/new"><x-lucide name="plus" class="mr-2 size-5" />New request</x-button-link>
@endsection

@php
    // Links for the stage chips keep the search that is typed in.
    $chipUrl = fn (string $stage) => '/staff'.(($qs = http_build_query(array_filter(['q' => $query, 'status' => $stage]))) ? '?'.$qs : '');
    $everything = array_sum($perStage);
@endphp

@section('content')
    <div class="grid gap-6">
        {{-- Two numbers worth a glance. --}}
        <dl class="grid grid-cols-2 gap-3 sm:gap-4 lg:max-w-xl">
            <div class="rounded-card border border-line bg-white px-4 py-3 sm:px-5 sm:py-4">
                <dt class="text-sm text-muted">In progress</dt>
                <dd class="mt-1 font-display text-3xl font-semibold text-navy-900">{{ $inProgress }}</dd>
            </div>
            <div class="rounded-card border border-line bg-white px-4 py-3 sm:px-5 sm:py-4">
                <dt class="text-sm text-muted">Updated today</dt>
                <dd class="mt-1 font-display text-3xl font-semibold text-navy-900">{{ $updatedToday }}</dd>
            </div>
        </dl>

        <form class="flex flex-wrap items-end gap-3" role="search" aria-label="Search shipments" method="get" action="/staff">
            @if ($status !== '')
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div class="min-w-56 flex-1 basis-64">
                <label for="q" class="block font-semibold text-navy-900">Search</label>
                <input id="q" type="search" name="q" value="{{ $query }}" placeholder="Tracking number, client, phone or goods" autocomplete="off" class="mt-2 min-h-11 w-full rounded-control border border-control bg-white px-3">
            </div>
            <button type="submit" class="min-h-11 rounded-control bg-navy-900 px-5 font-semibold text-white hover:bg-navy-800">Search</button>
            @if ($query !== '' || $status !== '')
                <a href="/staff" class="inline-flex min-h-11 items-center px-2 font-semibold text-brand underline underline-offset-2">Clear</a>
            @endif
        </form>

        {{-- Filter by stage: each chip shows how many shipments are there (within the search). --}}
        <nav aria-label="Filter by stage">
            <ul class="flex flex-wrap gap-2">
                <li>
                    <a href="{{ $chipUrl('') }}" @if ($status === '') aria-current="page" @endif class="{{ ui('chip', $status === '' ? 'on' : 'off') }}">All <span class="font-normal opacity-80">{{ $everything }}</span></a>
                </li>
                @foreach (\App\Support\Statuses::all() as $stage)
                    @php($count = $perStage[$stage['key']] ?? 0)
                    <li>
                        <a href="{{ $chipUrl($stage['key']) }}" @if ($status === $stage['key']) aria-current="page" @endif class="{{ ui('chip', $status === $stage['key'] ? 'on' : ($count ? 'off' : 'empty')) }}">{{ $stage['label'] }} <span class="font-normal opacity-80">{{ $count }}</span></a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div>
            <p role="status" class="text-muted">
                @if ($shipments->total() === 0)
                    No shipments match{{ $query !== '' ? ' “'.$query.'”' : '' }}.
                @elseif ($query !== '' || $status !== '')
                    {{ $shipments->total() }} {{ $shipments->total() === 1 ? 'shipment matches' : 'shipments match' }}{{ $query !== '' ? ' “'.$query.'”' : '' }}. Showing {{ $shipments->firstItem() }}–{{ $shipments->lastItem() }}.
                @else
                    Showing {{ $shipments->firstItem() }}–{{ $shipments->lastItem() }} of {{ $shipments->total() }}, most recently updated first.
                @endif
            </p>

            @if ($shipments->count())
                {{-- A table on wide screens, a stack of cards on phones (no sideways scrolling). --}}
                <div class="mt-4 overflow-hidden rounded-card border border-line bg-white">
                    <div aria-hidden="true" class="hidden gap-x-6 border-b border-line bg-sand px-5 py-3 text-sm font-semibold text-navy-900 md:grid md:grid-cols-[11rem_minmax(0,1.2fr)_minmax(0,1.3fr)_13rem_6.5rem]">
                        <span>Tracking number</span><span>Client</span><span>Goods</span><span>Stage</span><span>Updated</span>
                    </div>
                    <ul class="divide-y divide-line">
                        @foreach ($shipments as $s)
                            <li class="grid items-center gap-x-6 gap-y-2 px-5 py-4 transition-colors duration-150 hover:bg-sand/50 md:grid-cols-[11rem_minmax(0,1.2fr)_minmax(0,1.3fr)_13rem_6.5rem]">
                                <p><a href="/staff/shipments/{{ $s->tracking_number }}" class="font-mono font-semibold text-brand underline underline-offset-2">{{ $s->tracking_number }}</a></p>
                                <p class="min-w-0 break-words">{{ $s->client?->company ?: $s->client?->full_name }}@if ($s->client?->company)<span class="block text-sm text-muted">{{ $s->client->full_name }}</span>@endif</p>
                                <p class="min-w-0 break-words text-muted md:text-ink">{{ $s->goods }}</p>
                                <p><x-status-badge :status="$s->status" /></p>
                                <p class="text-sm text-muted md:text-base">{{ $s->updated_at->timezone('Asia/Thimphu')->format('j M Y') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <x-pager :paginator="$shipments" label="shipments" />
        </div>
    </div>
@endsection
