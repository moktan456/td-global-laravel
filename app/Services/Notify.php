<?php

namespace App\Services;

use App\Models\Client;
use App\Models\NotificationLog;

/**
 * Tells a client about something, by email and (when they have connected Telegram) by Telegram, following their
 * preferences. Every attempt is logged. Never throws: a failed notification must not fail the action that caused it.
 * (Ported from src/lib/portal/notify.ts.)
 */
class Notify
{
    public function __construct(private Mailer $mailer, private Telegram $telegram) {}

    /**
     * @param  array{subject: string, text: string}|null  $email
     * @return array<int, array{channel: string, status: string, error?: string}>
     */
    public function client(Client $client, string $kind, ?int $shipmentId = null, ?array $email = null, ?string $telegram = null): array
    {
        $outcomes = [];

        if ($email) {
            if (! $client->notify_email) {
                $outcomes[] = ['channel' => 'email', 'status' => 'skipped', 'error' => 'Client turned email off'];
            } else {
                $r = $this->mailer->deliver($client->email, $email['subject'], $email['text']);
                $outcomes[] = $r['ok']
                    ? ['channel' => 'email', 'status' => 'sent']
                    : ['channel' => 'email', 'status' => ! empty($r['skipped']) ? 'skipped' : 'failed', 'error' => $r['error']];
            }
        }

        if ($telegram && $client->telegram_chat_id) {
            if (! $client->notify_telegram) {
                $outcomes[] = ['channel' => 'telegram', 'status' => 'skipped', 'error' => 'Client turned Telegram off'];
            } elseif (! Telegram::configured()) {
                $outcomes[] = ['channel' => 'telegram', 'status' => 'skipped', 'error' => 'Telegram is not set up yet'];
            } else {
                $r = $this->telegram->send($client->telegram_chat_id, $telegram);
                if ($r['ok']) {
                    $outcomes[] = ['channel' => 'telegram', 'status' => 'sent'];
                } else {
                    $o = ['channel' => 'telegram', 'status' => 'failed', 'error' => $r['error']];
                    if (! empty($r['gone'])) {
                        // The client blocked the bot or deleted the chat: stop trying, and tell staff.
                        $o['error'] = "{$r['error']}. Telegram was disconnected; email still works.";
                        $client->forceFill(['telegram_chat_id' => null, 'telegram_linked_at' => null])->save();
                    }
                    $outcomes[] = $o;
                }
            }
        }

        foreach ($outcomes as $o) {
            $this->record($shipmentId, $client->id, 'client', $kind, $o);
        }

        return $outcomes;
    }

    /** Alerts the team: the staff Telegram chat and the business email address, so an alert is never missed. */
    public function staff(string $kind, ?int $shipmentId, array $email, string $telegram): array
    {
        $outcomes = [];
        $chat = config('td.telegram.staff_chat_id');
        if ($chat && Telegram::configured()) {
            $r = $this->telegram->send($chat, $telegram);
            $outcomes[] = $r['ok'] ? ['channel' => 'telegram', 'status' => 'sent'] : ['channel' => 'telegram', 'status' => 'failed', 'error' => $r['error']];
        } else {
            $outcomes[] = ['channel' => 'telegram', 'status' => 'skipped', 'error' => 'Staff Telegram chat is not set up (TELEGRAM_STAFF_CHAT_ID)'];
        }
        $m = $this->mailer->deliver(site('contact.email'), $email['subject'], $email['text']);
        $outcomes[] = $m['ok'] ? ['channel' => 'email', 'status' => 'sent'] : ['channel' => 'email', 'status' => ! empty($m['skipped']) ? 'skipped' : 'failed', 'error' => $m['error']];

        foreach ($outcomes as $o) {
            $this->record($shipmentId, null, 'staff', $kind, $o);
        }

        return $outcomes;
    }

    private function record(?int $shipmentId, ?int $clientId, string $audience, string $kind, array $o): void
    {
        try {
            NotificationLog::create([
                'shipment_id' => $shipmentId, 'client_id' => $clientId, 'audience' => $audience,
                'channel' => $o['channel'], 'kind' => $kind, 'status' => $o['status'], 'error' => $o['error'] ?? null,
            ]);
        } catch (\Throwable) {
            report('[notify] could not write the notification log');
        }
    }

    /** One line for the staff dashboard: "Email sent. Telegram failed (…)". */
    public static function describe(array $outcomes): string
    {
        if (! $outcomes) {
            return 'No notification was sent.';
        }

        return collect($outcomes)->map(function ($o) {
            $name = $o['channel'] === 'email' ? 'Email' : 'Telegram';

            return match ($o['status']) {
                'sent' => "{$name} sent.",
                'skipped' => "{$name} skipped ({$o['error']}).",
                default => "{$name} failed ({$o['error']}).",
            };
        })->implode(' ');
    }
}
