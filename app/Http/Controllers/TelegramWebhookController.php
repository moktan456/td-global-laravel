<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\NotificationLog;
use App\Models\TelegramLinkToken;
use App\Services\Telegram;
use App\Support\Messages;
use App\Support\Statuses;
use Illuminate\Http\Request;

/**
 * Telegram calls this with every message sent to the bot (registered from Staff → Telegram). Only requests carrying
 * our secret are accepted. Commands: /start <token> connects this chat to the client who created the link, /status
 * lists the connected client's latest shipments, /stop disconnects, /chatid replies with this chat's ID (used once, to
 * set up the staff alert group). Once the secret is verified the answer is always 200, so Telegram never retries a
 * handled message. (Ported from src/app/api/telegram/webhook/route.ts.)
 */
class TelegramWebhookController extends Controller
{
    public function __construct(private Telegram $telegram) {}

    public function handle(Request $request)
    {
        $cfg = config('td.telegram');
        if (! $cfg['bot_token'] || ! $cfg['webhook_secret']) {
            return response('Telegram is not set up', 503);
        }
        $provided = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');
        if ($provided === '' || ! hash_equals(hash('sha256', $cfg['webhook_secret'], true), hash('sha256', $provided, true))) {
            return response('Forbidden', 403);
        }

        try {
            $this->process($request->json()->all());
        } catch (\Throwable) {
            report('[telegram webhook] handler error');
        }

        return response()->json(['ok' => true]);
    }

    private function process(array $update): void
    {
        $chat = $update['message']['chat'] ?? null;
        $text = $update['message']['text'] ?? null;
        if (! is_int($chat['id'] ?? null) || ! is_string($text)) {
            return;
        }
        $chatId = $chat['id'];
        $parts = preg_split('/\s+/', trim($text));
        $command = strtolower(explode('@', $parts[0] ?? '')[0]);
        $arg = $parts[1] ?? '';

        // Works in any chat, including the staff group.
        if ($command === '/chatid') {
            $this->telegram->send($chatId, Messages::telegramChatId($chatId));

            return;
        }
        // Everything else is for one-to-one chats with clients only.
        if (($chat['type'] ?? '') !== 'private') {
            return;
        }

        if ($command === '/start' && $arg !== '') {
            $this->connect($chatId, $arg);

            return;
        }
        if ($command === '/stop') {
            $changed = Client::where('telegram_chat_id', $chatId)->update(['telegram_chat_id' => null, 'telegram_linked_at' => null]);
            $this->telegram->send($chatId, $changed > 0 ? Messages::telegramUnlinked() : Messages::telegramNotConnected());

            return;
        }
        if ($command === '/status') {
            $ids = Client::where('telegram_chat_id', $chatId)->pluck('id');
            if ($ids->isEmpty()) {
                $this->telegram->send($chatId, Messages::telegramNotConnected());

                return;
            }
            $items = \App\Models\Shipment::whereIn('client_id', $ids)->latest()->limit(5)->get()
                ->map(fn ($s) => ['tracking' => $s->tracking_number, 'stage' => Statuses::info($s->status)['label']])->all();
            $this->telegram->send($chatId, Messages::telegramShipmentList($items));

            return;
        }

        $this->telegram->send($chatId, Messages::telegramWelcome());
    }

    private function connect(int $chatId, string $token): void
    {
        if (! preg_match('/^[A-Za-z0-9_-]{43}$/', $token)) {
            $this->telegram->send($chatId, Messages::telegramLinkInvalid());

            return;
        }
        // Claim the token in one step so two people can't both use it.
        $claimed = TelegramLinkToken::where('token_hash', hash('sha256', $token))->whereNull('used_at')->where('expires_at', '>', now())
            ->update(['used_at' => now()]);
        $row = $claimed ? TelegramLinkToken::where('token_hash', hash('sha256', $token))->first() : null;
        $client = $row ? Client::find($row->client_id) : null;
        if (! $client) {
            $this->telegram->send($chatId, Messages::telegramLinkInvalid());

            return;
        }
        $client->forceFill(['telegram_chat_id' => $chatId, 'telegram_linked_at' => now(), 'notify_telegram' => true])->save();
        $sent = $this->telegram->send($chatId, Messages::telegramLinked($client->full_name));
        NotificationLog::create([
            'client_id' => $client->id, 'audience' => 'client', 'channel' => 'telegram', 'kind' => 'telegram_linked',
            'status' => $sent['ok'] ? 'sent' : 'failed', 'error' => $sent['ok'] ? null : $sent['error'],
        ]);
    }
}
