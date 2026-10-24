@extends('layouts.staff', ['title' => 'Shipments'])

@section('actions')
    <x-button-link href="/staff/shipments/new"><x-lucide name="plus" class="mr-2 size-5" />New request</x-button-link>
@endsection

@section('content')
        <form class="flex flex-wrap items-end gap-4" role="search" aria-label="Search shipments" method="get" action="/staff">
            <div class="min-w-56 flex-1 basis-64">
                <label for="q" class="block font-semibold text-navy-900">Search</label>
                <input id="q" type="search" name="q" value="{{ $query }}" placeholder="Tracking number, client, phone or goods" autocomplete="off" class="mt-2 min-h-11 w-full rounded-control border border-control bg-white px-3">
            </div>
            <div>
                <label for="status" class="block font-semibold text-navy-900">Stage</label>
                <select id="status" name="status" class="mt-2 min-h-11 rounded-control border border-control bg-white px-3">
                    <option value="">All stages</option>
                    @foreach (\App\Support\Statuses::all() as $s)
                        <option value="{{ $s['key'] }}" @selected($status === $s['key'])>{{ $s['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="min-h-11 rounded-control bg-navy-900 px-5 font-semibold text-white hover:bg-navy-800">Search</button>
            @if ($query !== '' || $status !== '')
                <a href="/staff" class="inline-flex min-h-11 items-center px-2 font-semibold text-brand underline underline-offset-2">Clear</a>
            @endif
        </form>
        <p role="status" class="mt-4 text-muted">
            @if ($shipments->total() === 0)
                No shipments match{{ $query !== '' ? ' “'.$query.'”' : '' }}.
            @elseif ($query !== '' || $status !== '')
                {{ $shipments->total() }} {{ $shipments->total() === 1 ? 'shipment matches' : 'shipments match' }}{{ $query !== '' ? ' “'.$query.'”' : '' }}. Showing {{ $shipments->firstItem() }}–{{ $shipments->lastItem() }}.
            @else
                Showing {{ $shipments->firstItem() }}–{{ $shipments->lastItem() }} of {{ $shipments->total() }}, most recently updated first.
            @endif
        </p>

        <div class="mt-6 overflow-x-auto rounded-card border border-line bg-white">
            <table class="w-full min-w-[720px] text-left">
                <caption class="sr-only">Shipments, most recently updated first</caption>
                <thead class="bg-sand text-sm text-navy-900">
                    <tr>
                        <th scope="col" class="px-4 py-3">Tracking number</th>
                        <th scope="col" class="px-4 py-3">Client</th>
                        <th scope="col" class="px-4 py-3">Goods</th>
                        <th scope="col" class="px-4 py-3">Stage</th>
                        <th scope="col" class="px-4 py-3">Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shipments as $s)
                        <tr class="border-t border-line">
                            <td class="px-4 py-3"><a href="/staff/shipments/{{ $s->tracking_number }}" class="font-mono font-semibold text-brand underline underline-offset-2">{{ $s->tracking_number }}</a></td>
                            <td class="px-4 py-3">{{ $s->client?->full_name }}{{ $s->client?->company ? ' · '.$s->client->company : '' }}</td>
                            <td class="px-4 py-3">{{ $s->goods }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$s->status" /></td>
                            <td class="px-4 py-3 text-muted">{{ $s->updated_at->timezone('Asia/Thimphu')->format('j M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-muted">No shipments match.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pager :paginator="$shipments" label="shipments" />
@endsection
