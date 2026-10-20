<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Talks to the Telegram Bot API. Never throws, and never puts the bot token (it is in the URL) into an error or log line. */
class Telegram
{
    public static function configured(): bool
    {
        return (bool) (config('td.telegram.bot_token') && config('td.telegram.bot_username'));
    }

    /** @return array{ok: true}|array{ok: false, error: string, gone?: bool} */
    public function send(int|string $chatId, string $html): array
    {
        $token = config('td.telegram.bot_token');
        if (! $token) {
            return ['ok' => false, 'error' => 'Telegram is not set up yet (TELEGRAM_BOT_TOKEN)'];
        }
        try {
            $res = Http::timeout(8)->post(config('td.telegram.api_base')."/bot{$token}/sendMessage", [
                'chat_id' => preg_match('/^-?\d{1,15}$/', (string) $chatId) ? (int) $chatId : $chatId,
                'text' => $html,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);
        } catch (\Throwable) {
            return ['ok' => false, 'error' => 'Could not reach Telegram'];
        }
        if ($res->successful()) {
            return ['ok' => true];
        }
        $description = is_string($res->json('description')) ? mb_substr($res->json('description'), 0, 200) : "HTTP {$res->status()}";
        Log::error("[telegram] sendMessage failed: {$description}");

        return ['ok' => false, 'error' => $description, 'gone' => $res->status() === 403 || (bool) preg_match('/chat not found/i', $description)];
    }

    /** Any Bot API method (setWebhook, getMe, ...). @return array{ok: true, result: mixed}|array{ok: false, error: string} */
    public function call(string $method, array $body = []): array
    {
        $token = config('td.telegram.bot_token');
        if (! $token) {
            return ['ok' => false, 'error' => 'TELEGRAM_BOT_TOKEN is not set'];
        }
        try {
            $res = Http::timeout(10)->post(config('td.telegram.api_base')."/bot{$token}/{$method}", $body);
        } catch (\Throwable) {
            return ['ok' => false, 'error' => 'Could not reach Telegram'];
        }
        if ($res->json('ok')) {
            return ['ok' => true, 'result' => $res->json('result')];
        }

        return ['ok' => false, 'error' => is_string($res->json('description')) ? mb_substr($res->json('description'), 0, 200) : "HTTP {$res->status()}"];
    }

    /** t.me link that opens the bot and presses Start with the token. */
    public static function connectLink(string $token): string
    {
        return 'https://t.me/'.config('td.telegram.bot_username').'?start='.$token;
    }
}
