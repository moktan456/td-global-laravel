{{-- Staff: make a one-time Telegram connect link for a client and copy it to send them. --}}
@php
    $state = $state ?? \App\Support\FormState::idle();
    $link = $state->value('link');
    $fid = "tglink{$client->id}";
@endphp
<form id="{{ $fid }}-form" method="post" action="/staff/clients/{{ $client->id }}/telegram-link" data-enhance class="mt-3 grid gap-3">
    @csrf
    <x-form-status :state="$state" :ids="[]" />
    @if ($link)
        <div class="grid gap-2">
            <label for="{{ $fid }}-link" class="text-sm font-semibold text-navy-900">Connect link for {{ $client->full_name }}</label>
            <input id="{{ $fid }}-link" readonly value="{{ $link }}" data-select-on-focus class="block w-full rounded-control border border-control bg-sand px-3 py-2.5 font-mono text-sm">
            <div class="flex flex-wrap items-center gap-3">
                <x-button type="button" variant="outline" data-copy="{{ $fid }}-link">Copy link</x-button>
                <span data-copy-status role="status" class="text-sm text-muted"></span>
            </div>
        </div>
    @else
        <div>
            <x-button type="submit" variant="outline">Create Telegram link<span class="sr-only"> for {{ $client->full_name }}</span></x-button>
        </div>
    @endif
</form>
