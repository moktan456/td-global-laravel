<?php

namespace App\Support;

use App\Models\Shipment;

/**
 * Message templates: Telegram (HTML parse mode, every piece of user-supplied text escaped) and plain-text email
 * (ported from src/lib/portal/messages.ts and email.ts).
 */
class Messages
{
    public static function e(string $text): string
    {
        return htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Shortens long user text so one message can never approach Telegram's limit. */
    public static function clip(string $text, int $max = 400): string
    {
        $t = trim(preg_replace('/\s+/', ' ', $text));

        return mb_strlen($t) > $max ? mb_substr($t, 0, $max - 1).'…' : $t;
    }

    private static function link(string $url, string $label): string
    {
        return '<a href="'.htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'">'.self::e($label).'</a>';
    }

    private static function sign(): string
    {
        return "\n\n".self::e(site('name')).' · '.self::e(site('contact.phone'));
    }

    private static function origin(): string
    {
        return rtrim(site('url'), '/');
    }

    /** One line saying which shipment this is, so a new client recognises it: "Import · Road freight · 2 pallets of auto parts". */
    public static function about(Shipment $shipment): string
    {
        $label = fn (string $list, string $value) => collect(content("statuses.{$list}"))->firstWhere('value', $value)['label'] ?? $value;

        return $label('directions', $shipment->direction).' · '.$label('modes', $shipment->mode).' · '.self::clip($shipment->goods, 120);
    }

    /* ---------- WhatsApp (staff send these by hand; the wa.me link only pre-fills the message) ---------- */

    /** https://wa.me/<digits>?text=<message>: opens WhatsApp with the message ready for staff to press Send. */
    public static function waLink(string $phone, string $text): ?string
    {
        $digits = preg_replace('/\D/', '', $phone);

        return $digits ? "https://wa.me/{$digits}?text=".rawurlencode($text) : null;
    }

    public static function whatsappTelegramInvite(string $name, string $link): string
    {
        return "Hello {$name}, this is ".site('name').". Tap this link to get your clearance updates on Telegram, then press Start:\n{$link}";
    }

    public static function whatsappBotLink(string $name, string $link): string
    {
        return "Hello {$name}, this is ".site('name').". Here is our updates bot on Telegram:\n{$link}";
    }

    public static function whatsappTrackingNumber(string $name, string $tracking, ?string $about, string $url): string
    {
        return "Hello {$name}, this is ".site('name').". Your tracking number is {$tracking}".($about ? " ({$about})" : '').".\nFollow it any time, no sign-in needed:\n{$url}";
    }

    /* ---------- Telegram ---------- */

    public static function telegramTrackingNumber(string $name, string $tracking, ?string $about = null): string
    {
        return 'Hello '.self::e($name).", we've received your clearance request.\n\n"
            .'Your tracking number: <code>'.self::e($tracking)."</code>\n"
            .($about ? 'Shipment: '.self::e($about)."\n" : '')."\n"
            .'Follow it any time: '.self::link(self::origin()."/track?n={$tracking}", 'open your shipment')
            .self::sign();
    }

    public static function telegramStatusUpdate(string $name, string $tracking, array $info, ?string $note, ?string $about = null, ?string $track = null): string
    {
        return '<b>Update on your tracking number '.self::e($tracking)."</b>\n"
            .($about ? 'Shipment: '.self::e($about)."\n" : '')
            .'Hello '.self::e($name).'. New '.($track === Statuses::TRANSPORT ? 'transport stage' : 'stage').': <b>'.self::e($info['label'])."</b>\n".self::e($info['clientText'])
            .($note ? "\n\nNote from our team: ".self::e(self::clip($note)) : '')
            ."\n\n".self::link(self::origin()."/track?n={$tracking}", 'Open your shipment')
            .self::sign();
    }

    public static function telegramLinked(string $name): string
    {
        return 'Connected. '.self::e($name).", we'll send your clearance updates here as well as by email.\n\n"
            .'Send /status to see your latest shipments, or /stop to disconnect.'
            .self::sign();
    }

    public static function telegramStaffNewRequest(array $r): string
    {
        return "<b>New clearance request</b>\n"
            .'<code>'.self::e($r['tracking']).'</code> · '.self::e($r['direction']).' · '.self::e($r['mode'])."\n"
            .'Client: '.self::e($r['clientName']).(! empty($r['company']) ? ' ('.self::e($r['company']).')' : '')."\n"
            .'Goods: '.self::e(self::clip($r['goods'], 200))."\n\n"
            .self::link(self::origin()."/staff/shipments/{$r['tracking']}", 'Open in the staff dashboard');
    }

    public static function telegramWelcome(): string
    {
        return 'Welcome to '.self::e(site('name')).".\n\n"
            .'To get your shipment updates here, ask our team for a connect link: '.self::e(site('contact.phone')).'.';
    }

    public static function telegramLinkInvalid(): string
    {
        return 'That connect link has expired or was already used. Please ask our team for a new one, or call us on '.self::e(site('contact.phone')).'.';
    }

    public static function telegramNotConnected(): string
    {
        return "This chat isn't connected to a client yet. Ask our team for a connect link: ".self::e(site('contact.phone')).'.';
    }

    public static function telegramUnlinked(): string
    {
        return "Disconnected. You will no longer get Telegram updates, but we'll keep emailing you. Ask our team for a new connect link any time.";
    }

    /** @param  array<int, array{tracking: string, stage: string}>  $items */
    public static function telegramShipmentList(array $items): string
    {
        if (! $items) {
            return 'You have no shipments yet. Call us on '.self::e(site('contact.phone')).' to start one.';
        }

        return "<b>Your latest shipments</b>\n"
            .implode("\n", array_map(fn ($i) => '<code>'.self::e($i['tracking']).'</code> · '.self::e($i['stage']), $items))
            ."\n\n".self::link(self::origin().'/track', 'Track a shipment');
    }

    public static function telegramChatId(int|string $id): string
    {
        return "This chat's ID is <code>".self::e((string) $id).'</code>. Set it as TELEGRAM_STAFF_CHAT_ID to send staff alerts here.';
    }

    /** Sent when someone asks for their tracking numbers on the Track page. */
    public static function telegramTrackingReminder(string $name, array $items): string
    {
        return 'Hello '.self::e($name).", here are your latest tracking numbers:\n\n"
            .implode("\n\n", array_map(fn ($i) => '<code>'.self::e($i['tracking']).'</code> · '.self::e($i['stage'])."\n".self::link(self::origin()."/track?n={$i['tracking']}", 'Open'), $items))
            .self::sign();
    }

    /* ---------- Email (plain text) ---------- */

    private static function signature(): string
    {
        return "\n\n— ".site('name')."\n".site('contact.phone').' · '.site('contact.email');
    }

    /** @return array{subject: string, text: string} */
    public static function trackingNumberEmail(string $name, string $tracking, ?string $about = null): array
    {
        return [
            'subject' => "Your tracking number: {$tracking}",
            'text' => "Hello {$name},\n\nWe've received your clearance request. Your tracking number is:\n\n{$tracking}\n".($about ? "\nShipment: {$about}\n" : '')."\nCheck its progress any time, no sign-in needed:\n".self::origin()."/track?n={$tracking}".self::signature(),
        ];
    }

    public static function statusUpdateEmail(string $name, string $tracking, string $status, ?string $note, ?string $about = null, ?string $track = null): array
    {
        $info = Statuses::info($status);

        return [
            'subject' => "{$tracking}: {$info['label']}",
            'text' => "Hello {$name},\n\nUpdate on your tracking number {$tracking}".($about ? " ({$about})" : '').($track === Statuses::TRANSPORT ? ', transport' : '').": {$info['clientText']}".($note ? "\n\nNote from our team: {$note}" : '')."\n\nCheck it any time: ".self::origin()."/track?n={$tracking}".self::signature(),
        ];
    }

    public static function accountReadyEmail(string $name): array
    {
        return [
            'subject' => 'Welcome to '.site('name'),
            'text' => "Hello {$name},\n\nWe've registered you with ".site('name').". When we handle a clearance for you, we'll email you the tracking number and each progress update. You never need to sign in: with a tracking number you can check the status any time at ".self::origin().'/track.'.self::signature(),
        ];
    }

    /** Sent when someone asks for their tracking numbers on the Track page. Goes only to the email we already hold. */
    public static function trackingReminderEmail(string $name, array $items): array
    {
        $list = implode("\n\n", array_map(fn ($i) => "{$i['tracking']}  ({$i['stage']})\n".self::origin()."/track?n={$i['tracking']}", $items));

        return [
            'subject' => 'Your tracking number',
            'text' => "Hello {$name},\n\nSomeone asked for your tracking number on our website. Here are your latest shipments:\n\n{$list}\n\nOpen a link to see its progress, no sign-in needed. If you didn't ask for this, you can ignore this email.".self::signature(),
        ];
    }

    /** Alert to the business address when a client creates a request online (kept for the future self-request feature). */
    public static function newRequestEmailForStaff(array $r): array
    {
        return [
            'subject' => "New clearance request {$r['tracking']}",
            'text' => $r['clientName'].(! empty($r['company']) ? " ({$r['company']})" : '')." created a {$r['direction']} request by {$r['mode']}.\n\nGoods: {$r['goods']}\nTracking number: {$r['tracking']}\n\nOpen it: ".self::origin()."/staff/shipments/{$r['tracking']}",
        ];
    }
}
