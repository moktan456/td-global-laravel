<?php

return [
    /*
     * Telegram bot (optional: without it the system sends email only). Create the bot with @BotFather and set these in .env.
     */
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
        'bot_username' => ltrim((string) env('TELEGRAM_BOT_USERNAME', ''), '@'),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET', ''),
        // Chat (usually a group) where staff alerts go, e.g. -1001234567890. Find it by sending /chatid to the bot there.
        'staff_chat_id' => env('TELEGRAM_STAFF_CHAT_ID', ''),
        'api_base' => env('TELEGRAM_API_BASE', 'https://api.telegram.org'),
        'link_ttl_hours' => 48,
    ],

    // Public lookups and reminders are rate limited per visitor (see App\Http\Controllers\TrackController).
    'limits' => [
        'track' => ['max' => 30, 'seconds' => 600],
        'remind_visitor' => ['max' => 5, 'seconds' => 3600],
        'remind_contact' => ['max' => 3, 'seconds' => 3600],
    ],
];
