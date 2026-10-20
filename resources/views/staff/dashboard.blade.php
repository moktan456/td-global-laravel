@extends('layouts.app', ['title' => 'Shipments', 'robots' => 'noindex, nofollow'])

@section('content')
    <x-page-header title="Shipments" :crumbs="[['label' => 'Staff']]" />
    <x-container class="py-10">
        <x-staff-nav current="/staff" :name="auth()->user()->name" :role="auth()->user()->role" />
        <form class="mt-6 flex flex-wrap items-end gap-4" role="search" aria-label="Filter shipments" method="get" action="/staff">
            <div>
                <label for="q" class="block font-semibold text-navy-900">Tracking number</label>
                <input id="q" name="q" value="{{ $query }}" class="mt-2 min-h-11 rounded-control border border-control bg-white px-3 uppercase">
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
            <button type="submit" class="min-h-11 rounded-control bg-navy-900 px-5 font-semibold text-white hover:bg-navy-800">Filter</button>
        </form>

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
    </x-container>
@endsection
