@extends('layouts.staff', ['title' => 'Shipments'])

@section('actions')
    <x-button-link href="/staff/shipments/new"><x-lucide name="plus" class="mr-2 size-5" />New request</x-button-link>
@endsection

@php
    // Links for the stage chips keep the search that is typed in.
    $chipUrl = fn (string $stage) => '/staff'.(($qs = http_build_query(array_filter(['q' => $query, 'status' => $stage, 'assigned' => $assigned, 'sort' => $sort === 'updated' ? '' : $sort]))) ? '?'.$qs : '');
    $exportQs = ($qs = http_build_query(array_filter(['q' => $query, 'status' => $status, 'assigned' => $assigned]))) ? '?'.$qs : '';
@endphp

@section('content')
    @if (! $hasAny)
        <div class="mx-auto max-w-xl rounded-card border border-dashed border-line bg-white px-6 py-12 text-center">
            <x-lucide name="package-plus" class="mx-auto size-10 text-navy-900" />
            <p class="mt-4 font-display text-2xl font-semibold text-navy-900">No shipments yet</p>
            <p class="mt-2 text-muted">Create the first request. The tracking number goes straight to the client, and the shipment shows up here.</p>
            <div class="mt-6"><x-button-link href="/staff/shipments/new">New request</x-button-link></div>
        </div>
    @else
    <div class="grid gap-6">
        {{-- Three numbers worth a glance. --}}
        <dl class="grid grid-cols-3 gap-2 sm:gap-4 lg:max-w-2xl">
            <div class="rounded-card border border-line bg-white px-3 py-3 sm:px-5 sm:py-4">
                <dt class="text-sm text-muted">In progress</dt>
                <dd class="mt-1 font-display text-3xl font-semibold text-navy-900">{{ $inProgress }}</dd>
            </div>
            <div class="rounded-card border border-line bg-white px-3 py-3 sm:px-5 sm:py-4">
                <dt class="text-sm text-muted"><a href="/staff?assigned=me" class="underline-offset-2 hover:underline"><span class="sm:hidden">Yours</span><span class="hidden sm:inline">Looked after by you</span></a></dt>
                <dd class="mt-1 font-display text-3xl font-semibold text-navy-900">{{ $mine }}</dd>
            </div>
            <div class="rounded-card border border-line bg-white px-3 py-3 sm:px-5 sm:py-4">
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
            <div>
                <label for="assigned" class="block font-semibold text-navy-900">Looked after by</label>
                <select id="assigned" name="assigned" class="mt-2 min-h-11 rounded-control border border-control bg-white px-3">
                    <option value="">Anyone</option>
                    <option value="me" @selected($assigned === 'me')>Me</option>
                    <option value="none" @selected($assigned === 'none')>Nobody yet</option>
                    @foreach ($people as $p)
                        @if ($p['value'] !== (string) auth()->id())
                            <option value="{{ $p['value'] }}" @selected($assigned === $p['value'])>{{ $p['label'] }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label for="sort" class="block font-semibold text-navy-900">Sort by</label>
                <select id="sort" name="sort" class="mt-2 min-h-11 rounded-control border border-control bg-white px-3">
                    @foreach (\App\Support\ListFilters::SORTS as $value => $label)
                        <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="min-h-11 rounded-control bg-navy-900 px-5 font-semibold text-white hover:bg-navy-800">Search</button>
            @if ($query !== '' || $status !== '' || $assigned !== '' || $sort !== 'updated')
                <a href="/staff" class="inline-flex min-h-11 items-center px-2 font-semibold text-brand underline underline-offset-2">Clear</a>
            @endif
        </form>

        {{-- Filter by stage: each chip shows how many shipments are there (within the search). With transport in use, the
             chips are in two labelled rows, one per track; a stage key belongs to one track, so a chip needs no extra setting. --}}
        @php
            $chipRows = [['label' => 'Customs clearance', 'stages' => \App\Support\Statuses::all(), 'all' => true]];
            if ($usesTransport || array_intersect([$status], \App\Support\Statuses::keysFor('transport'))) {
                $chipRows[] = ['label' => 'Transport', 'stages' => \App\Support\Statuses::transport(), 'all' => false];
            }
            $labelled = count($chipRows) > 1;
        @endphp
        <details data-fold-on-phone @if ($status !== '') data-has-choice @endif class="group/f" open>
        <summary class="inline-flex min-h-11 cursor-pointer list-none items-center gap-2 rounded-control border border-control bg-white px-4 font-semibold text-navy-900 md:hidden [&::-webkit-details-marker]:hidden"><x-lucide name="list-filter" class="size-5" />Filter by stage<span class="font-normal text-muted">{{ $status !== '' ? ': '.(\App\Support\Statuses::info($status)['label'] ?? '') : '' }}</span><x-lucide name="chevron-down" class="size-4 transition-transform group-open/f:rotate-180" /></summary>
        <nav aria-label="Filter by stage" class="mt-3 grid gap-3 md:mt-0">
            @foreach ($chipRows as $row)
                <div class="{{ $labelled ? 'grid gap-2 sm:grid-cols-[9.5rem_1fr] sm:items-start' : '' }}">
                    @if ($labelled)
                        <p class="pt-3 text-sm font-semibold text-muted">{{ $row['label'] }}</p>
                    @endif
                    <ul class="flex flex-wrap gap-2">
                        @if ($row['all'])
                            <li>
                                <a href="{{ $chipUrl('') }}" @if ($status === '') aria-current="page" @endif class="{{ ui('chip', $status === '' ? 'on' : 'off') }}">All <span class="font-normal opacity-80">{{ $everything }}</span></a>
                            </li>
                        @endif
                        @foreach ($row['stages'] as $stage)
                            @php($count = $perStage[$stage['key']] ?? 0)
                            <li>
                                <a href="{{ $chipUrl($stage['key']) }}" @if ($status === $stage['key']) aria-current="page" @endif class="{{ ui('chip', $status === $stage['key'] ? 'on' : ($count ? 'off' : 'empty')) }}">{{ $stage['label'] }} <span class="font-normal opacity-80">{{ $count }}</span></a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>
        </details>

        <div>
            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
            <p role="status" class="text-muted">
                @if ($shipments->total() === 0)
                    No shipments match{{ $query !== '' ? ' “'.$query.'”' : '' }}.
                @elseif ($query !== '' || $status !== '' || $assigned !== '')
                    {{ $shipments->total() }} {{ $shipments->total() === 1 ? 'shipment matches' : 'shipments match' }}{{ $query !== '' ? ' “'.$query.'”' : '' }}. Showing {{ $shipments->firstItem() }}–{{ $shipments->lastItem() }}.
                @else
                    Showing {{ $shipments->firstItem() }}–{{ $shipments->lastItem() }} of {{ $shipments->total() }}, most recently updated first.
                @endif
            </p>
            @if ($shipments->total() > 0)
                <a href="/staff/export/shipments.csv{{ $exportQs }}" class="inline-flex min-h-11 items-center gap-2 rounded-control border border-navy-900 px-4 font-semibold text-navy-900 transition-colors duration-150 hover:bg-navy-900 hover:text-white"><x-lucide name="download" class="size-4" />Export CSV<span class="sr-only"> of these shipments</span></a>
            @endif
            </div>

            @if ($shipments->count())
                {{-- A table on wide screens, a stack of cards on phones (no sideways scrolling). --}}
                <div class="mt-4 overflow-hidden rounded-card border border-line bg-white">
                    <div role="table" aria-label="Shipments" aria-rowcount="{{ $shipments->count() + 1 }}">
                    <div role="rowgroup">
                    <div role="row" class="hidden gap-x-6 border-b border-line bg-sand px-5 py-3 text-sm font-semibold text-navy-900 xl:grid xl:grid-cols-[10rem_minmax(0,1.1fr)_minmax(0,1.2fr)_14rem_7.5rem_6.5rem]">
                        <span role="columnheader" @if ($sort === 'tracking') aria-sort="ascending" @endif>Tracking number</span><span role="columnheader" @if ($sort === 'client') aria-sort="ascending" @endif>Client</span><span role="columnheader">Goods</span><span role="columnheader" @if ($sort === 'stage') aria-sort="ascending" @endif>Stage</span><span role="columnheader">Looked after by</span><span role="columnheader" @if (in_array($sort, ['updated', 'oldest'], true)) aria-sort="{{ $sort === 'updated' ? 'descending' : 'ascending' }}" @endif>Updated</span>
                    </div>
                    </div>
                    <ul role="rowgroup" class="divide-y divide-line">
                        @foreach ($shipments as $s)
                            <li role="row" class="grid items-center gap-x-6 md:grid-cols-2 gap-y-2 px-5 py-4 transition-colors duration-150 hover:bg-sand/50 xl:grid-cols-[10rem_minmax(0,1.1fr)_minmax(0,1.2fr)_14rem_7.5rem_6.5rem]">
                                <p role="cell"><a href="/staff/shipments/{{ $s->tracking_number }}" class="font-mono font-semibold text-brand underline underline-offset-2">{{ $s->tracking_number }}</a></p>
                                <p role="cell" class="min-w-0 break-words"><span class="sr-only">Client: </span>{{ $s->client?->company ?: $s->client?->full_name }}@if ($s->client?->company)<span class="block text-sm text-muted">{{ $s->client->full_name }}</span>@endif</p>
                                <p role="cell" class="min-w-0 break-words text-muted xl:text-ink"><span class="sr-only">Goods: </span>{{ $s->goods }}</p>
                                <p role="cell"><span class="sr-only">Stage: </span>@include('staff.stage-badges', ['s' => $s])</p>
                                <p role="cell" class="min-w-0 break-words text-sm xl:text-base {{ $s->assignee ? 'text-ink' : 'text-muted' }}"><span class="sr-only">Looked after by: </span>{{ $s->assignee?->name ?? 'Nobody yet' }}</p>
                                <p role="cell" class="text-sm text-muted xl:text-base"><span class="sr-only">Updated </span>{{ $s->updated_at->timezone('Asia/Thimphu')->format('j M Y') }}</p>
                            </li>
                        @endforeach
                    </ul>
                    </div>
                </div>
            @endif
            <x-pager :paginator="$shipments" label="shipments" />
        </div>
    </div>
    @endif
@endsection
