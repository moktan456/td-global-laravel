<?php

namespace App\Services;

use App\Mail\PlainMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Sends one plain-text email and says what happened. Without mail settings it reports "skipped" instead of failing. */
class Mailer
{
    /** @return array{ok: true}|array{ok: false, skipped?: bool, error: string} */
    public function deliver(string $to, string $subject, string $text): array
    {
        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            Log::warning("[mail] mail is not configured; not sent to {$to}: {$subject}");

            return ['ok' => false, 'skipped' => true, 'error' => 'Email is not set up yet (MAIL_MAILER)'];
        }
        try {
            Mail::to($to)->send(new PlainMessage($subject, $text));

            return ['ok' => true];
        } catch (\Throwable $e) {
            Log::error("[mail] sending failed for {$subject}: ".$e->getMessage());

            return ['ok' => false, 'error' => 'Email service did not accept the message'];
        }
    }
}
