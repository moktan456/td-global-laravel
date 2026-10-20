@props(['rows'])
{{-- What was sent about a shipment, to whom, and whether it arrived. Status is shown as text and an icon, not colour alone. --}}
@php
    $kinds = ['tracking_number' => 'Tracking number', 'status_update' => 'Status update', 'account_ready' => 'Account ready', 'telegram_linked' => 'Telegram connected', 'new_request' => 'New request alert', 'tracking_reminder' => 'Tracking number reminder'];
    $statuses = ['sent' => ['Sent', 'circle-check', 'text-success'], 'failed' => ['Failed', 'circle-alert', 'text-error'], 'skipped' => ['Skipped', 'circle-slash', 'text-muted']];
@endphp
@if (count($rows) === 0)
    <p class="text-muted">No notifications have been sent for this shipment yet.</p>
@else
    <ul class="grid gap-3">
        @foreach ($rows as $r)
            @php([$label, $icon, $tone] = $statuses[$r->status])
            <li class="rounded-tile border border-line bg-white p-4">
                <p class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span class="inline-flex items-center gap-1.5 font-semibold {{ $tone }}">
                        <x-lucide :name="$icon" class="size-4" />
                        {{ $label }}
                    </span>
                    <span class="font-semibold text-navy-900">{{ $kinds[$r->kind] ?? $r->kind }} · {{ $r->channel === 'email' ? 'Email' : 'Telegram' }} to {{ $r->audience === 'staff' ? 'staff' : 'client' }}</span>
                </p>
                <p class="mt-1 text-sm text-muted">
                    <time datetime="{{ $r->created_at->toIso8601String() }}">{{ $r->created_at->timezone('Asia/Thimphu')->format('j M Y, g:i a') }}</time>{{ $r->error ? ' · '.$r->error : '' }}
                </p>
            </li>
        @endforeach
    </ul>
@endif
