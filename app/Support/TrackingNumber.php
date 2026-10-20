<?php

namespace App\Support;

use App\Models\Shipment;

/**
 * Tracking numbers look like TD-2610-K7QX4M: prefix, year+month, then 6 characters from an alphabet without look-alikes
 * (no 0/O, 1/I/L), so they are easy to read over the phone. Numbers issued earlier have 4 characters and stay valid.
 */
class TrackingNumber
{
    public const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    public const SUFFIX_LENGTH = 6;

    private const PATTERN = '/^TD-\d{4}-[23456789ABCDEFGHJKMNPQRSTUVWXYZ]{4}(?:[23456789ABCDEFGHJKMNPQRSTUVWXYZ]{2})?$/';

    /** A fresh, unused number. The month stamp uses Bhutan time. */
    public static function generate(): string
    {
        do {
            $suffix = '';
            for ($i = 0; $i < self::SUFFIX_LENGTH; $i++) {
                $suffix .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
            $candidate = 'TD-'.now('Asia/Thimphu')->format('ym').'-'.$suffix;
        } while (Shipment::where('tracking_number', $candidate)->exists());

        return $candidate;
    }

    /** Uppercases, trims and re-inserts dashes: "td2610k7qx4m" → "TD-2610-K7QX4M" (older 4-character numbers too). */
    public static function normalize(string $input): string
    {
        $raw = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $input));
        if ((strlen($raw) !== 10 && strlen($raw) !== 12) || ! str_starts_with($raw, 'TD')) {
            return strtoupper(trim($input));
        }

        return 'TD-'.substr($raw, 2, 4).'-'.substr($raw, 6);
    }

    public static function isValid(string $input): bool
    {
        return (bool) preg_match(self::PATTERN, self::normalize($input));
    }

    /** Bhutan numbers: "17975757", "+975 17975757" → "+97517975757". */
    public static function normalizePhone(string $input): string
    {
        $digits = preg_replace('/\D/', '', $input);
        if (str_starts_with($digits, '975') && strlen($digits) === 11) {
            return '+'.$digits;
        }
        if (strlen($digits) === 8) {
            return '+975'.$digits;
        }

        return $digits ? '+'.$digits : '';
    }
}
