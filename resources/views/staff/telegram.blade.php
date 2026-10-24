@extends('layouts.staff', ['title' => 'Telegram'])

@php
    $rows = [];
    $botOk = $bot && $bot['ok'];
    $botUser = $botOk ? ($bot['result']['username'] ?? '?') : null;
@endphp

@section('content')
        <div class="grid max-w-3xl gap-8">
            <section aria-labelledby="checks" class="rounded-card border border-line bg-white p-6">
                <h2 id="checks" class="font-display text-2xl font-semibold text-navy-900">Set-up checklist</h2>
                <ul class="mt-2 divide-y divide-line">
                    @php
                        $row = function (string $label, bool $ok, ?string $detail = null) {
                            return compact('label', 'ok', 'detail');
                        };
                        $items = [
                            $row('Bot token (TELEGRAM_BOT_TOKEN)', $hasToken, $hasToken ? null : 'Add it to .env, then clear the config cache.'),
                            $row('Bot recognised by Telegram', $botOk, $bot ? ($botOk ? '@'.$botUser : 'Telegram said: '.$bot['error']) : null),
                            $row('Bot username (TELEGRAM_BOT_USERNAME)', $hasUsername && (! $botOk || $botUser === $username),
                                ! $hasUsername ? 'Add it to .env (without the @).' : ($botOk && $botUser !== $username ? "It says {$username} but the bot is @{$botUser}." : '@'.$username)),
                            $row('Webhook secret (TELEGRAM_WEBHOOK_SECRET)', $hasSecret, $hasSecret ? null : '16 to 256 letters, digits, _ or -. Add it to .env.'),
                        ];
                        $hookOk = $hook && $hook['ok'];
                        $hookUrl = $hookOk ? ($hook['result']['url'] ?? '') : '';
                        $pending = $hookOk ? ($hook['result']['pending_update_count'] ?? 0) : 0;
                        $items[] = $row('Bot connected to this website', $connected, $hookOk
                            ? ($connected ? "Telegram sends messages to {$expected}.".($pending ? " {$pending} waiting." : '') : ($hookUrl ? "Currently pointing at {$hookUrl}. Press the button below." : 'Not connected yet. Press the button below.'))
                            : null);
                        if ($hookOk && ! empty($hook['result']['last_error_message'])) {
                            $items[] = $row('Last delivery error from Telegram', false, $hook['result']['last_error_message']);
                        }
                        $items[] = $row('Staff alert group (TELEGRAM_STAFF_CHAT_ID)', $hasStaffChat, $hasStaffChat ? null : 'Optional. Add the bot to your team\'s group, send /chatid there, and put the number it replies with in .env.');
                    @endphp
                    @foreach ($items as $it)
                        <li class="flex items-start gap-3 py-3">
                            <span aria-hidden="true" class="mt-0.5 font-bold {{ $it['ok'] ? 'text-success' : 'text-error' }}">{{ $it['ok'] ? '✓' : '✗' }}</span>
                            <span>
                                <span class="font-semibold text-navy-900">{{ $it['label'] }}</span>
                                <span class="sr-only">{{ $it['ok'] ? ' — done' : ' — not done' }}</span>
                                @if ($it['detail'])
                                    <span class="block text-muted">{{ $it['detail'] }}</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>

            @if ($hasToken && $hasSecret)
                <section aria-labelledby="connect" class="rounded-card border border-line bg-white p-6">
                    <h2 id="connect" class="font-display text-2xl font-semibold text-navy-900">Connect the bot</h2>
                    <p class="mt-2 text-muted">Tells Telegram to send every message to the bot to this website. Do it once, and again if the website address changes.</p>
                    <div class="mt-5">
                        @include('staff.telegram-connect', ['connected' => $connected, 'state' => $state])
                    </div>
                </section>
            @endif

            @if ($botOk && ! empty($bot['result']['username']))
                <p class="text-muted">
                    Your bot: <a href="https://t.me/{{ $bot['result']['username'] }}" class="font-semibold text-brand underline underline-offset-2">t.me/{{ $bot['result']['username'] }}</a>.
                    To connect a client, open <strong>Clients</strong> and create their connect link.
                </p>
            @endif
        </div>
@endsection
