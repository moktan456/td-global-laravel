{{-- Staff: the plain bot link for a client who is already connected but lost the chat with the bot. --}}
@php
    $fid = "tglink{$client->id}";
    $bot = 'https://t.me/'.config('td.telegram.bot_username');
    $botWa = \App\Support\Messages::waLink($client->phone, \App\Support\Messages::whatsappBotLink($client->full_name, $bot));
@endphp
<div class="mt-3 grid gap-2">
        <label for="{{ $fid }}-bot" class="text-sm font-semibold text-navy-900">Bot link (if {{ $client->full_name }} lost the chat with the bot)</label>
        <input id="{{ $fid }}-bot" readonly value="{{ $bot }}" data-select-on-focus class="block w-full rounded-control border border-control bg-sand px-3 py-2.5 font-mono text-sm">
        <div class="flex flex-wrap items-center gap-3">
            <x-button type="button" variant="outline" data-copy="{{ $fid }}-bot">Copy bot link</x-button>
            @if ($botWa)
                <x-button-link :href="$botWa" variant="outline" target="_blank" rel="noopener">Send on WhatsApp<span class="sr-only"> (opens WhatsApp with the message ready)</span></x-button-link>
            @endif
            <span data-copy-status role="status" class="text-sm text-muted"></span>
        </div>
    </div>
