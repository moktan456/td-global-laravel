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

            return ['ok' => false, 'error' => $this->reason($e->getMessage())];
        }
    }

    /** A short, staff-readable reason (this text only appears on staff screens). */
    private function reason(string $message): string
    {
        if (str_contains($message, 'only send testing emails') || str_contains($message, 'verify a domain')) {
            return 'Email service is in test mode: it only delivers to the account owner until the sending domain is verified (resend.com/domains)';
        }
        if (preg_match('/Expected response code .* but got code "(\d{3})", with message "([^"]{1,160})/', $message, $m)) {
            return "Email service refused it ({$m[1]}: ".trim($m[2]).')';
        }
        if (str_contains($message, 'Connection could not be established') || str_contains($message, 'timed out')) {
            return 'Could not reach the email service';
        }

        return 'Email service did not accept the message';
    }
}
