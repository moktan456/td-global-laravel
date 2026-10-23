{{-- Staff: make a one-time Telegram connect link for one client (copy it, scan the QR code, or send it on WhatsApp). This partial is ONLY the form: the page script swaps it in as a single element. The bot link for connected clients is staff/telegram-bot-link. --}}
@php
    $state = $state ?? \App\Support\FormState::idle();
    $link = $state->value('link');
    $wa = $state->value('wa');
    $fid = "tglink{$client->id}";
    $connected = (bool) $client->telegram_chat_id;
@endphp
<form id="{{ $fid }}-form" method="post" action="/staff/clients/{{ $client->id }}/telegram-link" data-enhance class="mt-3 grid gap-3">
    @csrf
    <x-form-status :state="$state" :ids="[]" />
    @if ($link)
        <div class="flex flex-wrap items-start gap-6">
            <div class="grid min-w-0 flex-1 basis-72 gap-2">
                <label for="{{ $fid }}-link" class="text-sm font-semibold text-navy-900">Connect link for {{ $client->full_name }}</label>
                <input id="{{ $fid }}-link" readonly value="{{ $link }}" data-select-on-focus class="block w-full rounded-control border border-control bg-sand px-3 py-2.5 font-mono text-sm">
                <div class="flex flex-wrap items-center gap-3">
                    <x-button type="button" variant="outline" data-copy="{{ $fid }}-link">Copy link</x-button>
                    @if ($wa)
                        <x-button-link :href="$wa" variant="outline" target="_blank" rel="noopener">Send on WhatsApp<span class="sr-only"> (opens WhatsApp with the message ready)</span></x-button-link>
                    @endif
                    <span data-copy-status role="status" class="text-sm text-muted"></span>
                </div>
            </div>
            <figure class="grid justify-items-center gap-2">
                <div role="img" aria-label="QR code for {{ $client->full_name }}'s Telegram connect link" class="size-44 overflow-hidden rounded-control border border-line bg-white">{!! \App\Support\Qr::svg($link, 176) !!}</div>
                <figcaption class="max-w-44 text-center text-sm text-muted">Client nearby? They scan this with their phone camera to connect on Telegram.</figcaption>
            </figure>
        </div>
    @else
        <div>
            <x-button type="submit" variant="outline">{{ $connected ? 'Create a new connect link' : 'Create Telegram link' }}<span class="sr-only"> for {{ $client->full_name }}</span></x-button>
            @if ($connected)
                <p class="mt-2 text-sm text-muted">For a new phone or a new Telegram account. The client is reconnected when they press Start, and any earlier link stops working.</p>
            @endif
        </div>
    @endif
</form>
