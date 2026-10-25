<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * The optional transport details staff can record on a shipment. Everything is optional. The driver's name and phone are
 * for staff only; the rest may be shown to the client on the Track page.
 */
class TransportDetails
{
    public const FIELDS = ['carrier', 'vehicle_number', 'pickup_place', 'destination', 'expected_delivery', 'driver_name', 'driver_phone'];

    /** @return array<string, string> The submitted values, trimmed (missing ones as ''). */
    public static function read(Request $request): array
    {
        $values = [];
        foreach (self::FIELDS as $field) {
            $raw = $request->input($field, '');
            $values[$field] = is_string($raw) ? trim($raw) : '';
        }

        return $values;
    }

    /** @return array<string, string> Field => message (empty when everything is fine). */
    public static function errors(array $v): array
    {
        $errors = [];
        foreach (['carrier' => 120, 'pickup_place' => 160, 'destination' => 160, 'driver_name' => 120, 'vehicle_number' => 40, 'driver_phone' => 40] as $field => $max) {
            if (mb_strlen($v[$field] ?? '') > $max) {
                $errors[$field] = "Use {$max} characters or fewer.";
            }
        }
        if (($v['driver_phone'] ?? '') !== '' && ! preg_match('/^[0-9+()\-\s]+$/', $v['driver_phone'])) {
            $errors['driver_phone'] ??= 'Use digits, spaces, + ( ) and - only.';
        }
        if (($v['expected_delivery'] ?? '') !== '' && ! self::validDate($v['expected_delivery'])) {
            $errors['expected_delivery'] = 'Enter the date as day, month and year.';
        }

        return $errors;
    }

    /** @return array<string, mixed> Columns ready for the shipments table (empty strings become null). */
    public static function attributes(array $v): array
    {
        $out = [];
        foreach (self::FIELDS as $field) {
            $value = $v[$field] ?? '';
            $out[$field] = $value === '' ? null : $value;
        }
        $out['expected_delivery'] = $out['expected_delivery'] ? Carbon::createFromFormat('Y-m-d', $out['expected_delivery'])->startOfDay() : null;

        return $out;
    }

    private static function validDate(string $value): bool
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d') === $value;   // also rejects 2026-02-31
        } catch (\Throwable) {
            return false;
        }
    }
}
