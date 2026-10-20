<?php

namespace App\Support;

/** The cost calculator's sample rates and formula (ported from src/content/rates.ts). */
class Rates
{
    public static function estimate(string $type, string $mode, string $direction, int $lines): array
    {
        $r = content('rates');
        $base = $r['base'][$type];
        $extraLines = max(0, $lines - $r['includedLines']);
        $lineFee = $extraLines * $r['perExtraLine'];
        $handling = $r['mode'][$mode];
        $subtotal = $base + $lineFee + $handling;
        $discount = $direction === 'export' ? (int) round($subtotal * $r['exportDiscount']) : 0;

        return compact('base', 'extraLines', 'lineFee', 'handling', 'discount') + ['total' => $subtotal - $discount];
    }

    /** "Nu. 2,400" with Indian digit grouping (last three digits, then pairs), like Intl "en-IN". */
    public static function nu(int $n): string
    {
        $s = (string) abs($n);
        if (strlen($s) > 3) {
            $head = substr($s, 0, -3);
            $s = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $head).','.substr($s, -3);
        }

        return 'Nu. '.($n < 0 ? '−' : '').$s;
    }
}
