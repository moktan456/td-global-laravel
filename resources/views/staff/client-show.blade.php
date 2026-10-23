@extends('layouts.app', ['title' => $client->full_name, 'robots' => 'noindex, nofollow'])

@section('content')
    <x-page-header :title="$client->full_name" :crumbs="[['label' => 'Staff', 'href' => '/staff'], ['label' => 'Clients', 'href' => '/staff/clients'], ['label' => $client->full_name]]">
        @if ($client->company)
            <x-slot:intro><p>{{ $client->company }}</p></x-slot:intro>
        @endif
    </x-page-header>
    <x-container class="py-10">
        <x-staff-nav current="/staff/clients" :name="auth()->user()->name" :role="auth()->user()->role" />
        <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_2fr]">
            <div class="grid gap-6 self-start">
                <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 rounded-card border border-line bg-white p-6">
                    <dt class="text-muted">Email</dt>
                    <dd class="min-w-0 break-words">{{ $client->email }}</dd>
                    <dt class="text-muted">Phone</dt>
                    <dd>{{ $client->phone }}</dd>
                    <dt class="text-muted">Registered</dt>
                    <dd>{{ $client->created_at->timezone('Asia/Thimphu')->format('j M Y') }} by {{ $client->registered_by === 'staff' ? 'staff' : 'the client' }}</dd>
                    @if ($telegram)
                        <dt class="text-muted">Telegram</dt>
                        <dd>{{ $client->telegram_chat_id ? ($client->notify_telegram ? 'Connected' : 'Connected, switched off by the client') : 'Not connected' }}</dd>
                    @endif
                </dl>
                <div class="flex flex-wrap gap-3">
                    <x-button-link href="/staff/shipments/new?client={{ $client->id }}">New request for {{ $client->full_name }}</x-button-link>
                    <x-button-link href="/staff/clients" variant="outline">All clients</x-button-link>
                </div>
            </div>
            <section aria-labelledby="requests-heading">
                <h2 id="requests-heading" class="font-display text-2xl font-semibold text-navy-900">Requests ({{ $shipments->total() }})</h2>
                <div class="mt-6 overflow-x-auto rounded-card border border-line bg-white">
                    <table class="w-full min-w-[560px] text-left">
                        <caption class="sr-only">Requests made for {{ $client->full_name }}, most recently updated first</caption>
                        <thead class="bg-sand text-sm text-navy-900">
                            <tr>
                                <th scope="col" class="px-4 py-3">Tracking number</th>
                                <th scope="col" class="px-4 py-3">Goods</th>
                                <th scope="col" class="px-4 py-3">Stage</th>
                                <th scope="col" class="px-4 py-3">Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($shipments as $s)
                                <tr class="border-t border-line">
                                    <td class="px-4 py-3"><a href="/staff/shipments/{{ $s->tracking_number }}" class="font-mono font-semibold text-brand underline underline-offset-2">{{ $s->tracking_number }}</a></td>
                                    <td class="px-4 py-3">{{ $s->goods }}<span class="block text-sm capitalize text-muted">{{ $s->direction }} · {{ $s->mode }} freight</span></td>
                                    <td class="px-4 py-3"><x-status-badge :status="$s->status" /></td>
                                    <td class="px-4 py-3 text-muted">{{ $s->updated_at->timezone('Asia/Thimphu')->format('j M Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-muted">No requests yet for this client.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-pager :paginator="$shipments" label="requests" />
            </section>
        </div>
    </x-container>
@endsection
