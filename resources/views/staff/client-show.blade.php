@extends('layouts.staff', ['title' => $client->company ?: $client->full_name, 'subtitle' => $client->company ? $client->full_name : null, 'crumbs' => [['label' => 'Clients', 'href' => '/staff/clients'], ['label' => $client->company ?: $client->full_name]]])

@section('actions')
    <x-button-link href="/staff/shipments/new?client={{ $client->id }}"><x-lucide name="plus" class="mr-2 size-5" />New request</x-button-link>
@endsection

@section('content')
        <div class="grid gap-8 lg:grid-cols-[1fr_2fr]">
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
@endsection
