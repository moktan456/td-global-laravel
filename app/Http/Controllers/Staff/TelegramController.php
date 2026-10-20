<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\Telegram;
use App\Support\FormState;
use Illuminate\Http\Request;

/** Super admin: what is configured, what Telegram reports, and a button that connects the bot to this website. */
class TelegramController extends Controller
{
    public function __construct(private Telegram $telegram) {}

    public function index()
    {
        $cfg = config('td.telegram');
        $hasToken = (bool) $cfg['bot_token'];
        // Ask Telegram itself, so the page shows what is really connected (the token never leaves the server).
        $bot = $hasToken ? $this->telegram->call('getMe') : null;
        $hook = $hasToken ? $this->telegram->call('getWebhookInfo') : null;
        $expected = rtrim(site('url'), '/').'/api/telegram/webhook';

        return view('staff.telegram', [
            'hasToken' => $hasToken,
            'hasSecret' => (bool) preg_match('/^[A-Za-z0-9_-]{16,256}$/', (string) $cfg['webhook_secret']),
            'hasUsername' => (bool) $cfg['bot_username'],
            'hasStaffChat' => (bool) $cfg['staff_chat_id'],
            'username' => $cfg['bot_username'],
            'bot' => $bot,
            'hook' => $hook,
            'expected' => $expected,
            'connected' => ($hook['ok'] ?? false) && (($hook['result']['url'] ?? '') === $expected),
            'state' => form_state('telegram'),
        ]);
    }

    /** Points the bot at this website (Telegram will send every message to /api/telegram/webhook). */
    public function connect(Request $request)
    {
        $cfg = config('td.telegram');
        $fail = fn (string $m) => $this->formResponse($request, 'telegram', 'staff.telegram-connect', FormState::failed($m), ['connected' => false]);
        if (! $cfg['bot_token']) {
            return $fail("TELEGRAM_BOT_TOKEN isn't set yet.");
        }
        if (! preg_match('/^[A-Za-z0-9_-]{16,256}$/', (string) $cfg['webhook_secret'])) {
            return $fail('TELEGRAM_WEBHOOK_SECRET must be 16 to 256 letters, digits, _ or - (set it in .env).');
        }
        $url = rtrim(site('url'), '/').'/api/telegram/webhook';
        $hook = $this->telegram->call('setWebhook', ['url' => $url, 'secret_token' => $cfg['webhook_secret'], 'allowed_updates' => ['message'], 'drop_pending_updates' => true]);
        if (! $hook['ok']) {
            return $fail("Telegram refused the webhook: {$hook['error']}");
        }
        $this->telegram->call('setMyCommands', ['commands' => [
            ['command' => 'status', 'description' => 'Show my latest shipments'],
            ['command' => 'stop', 'description' => 'Stop Telegram updates'],
        ]]);

        return $this->formResponse($request, 'telegram', 'staff.telegram-connect', FormState::success("Connected. Telegram will now send messages to {$url}."), ['connected' => true]);
    }
}
