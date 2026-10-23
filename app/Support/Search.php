<?php

namespace App\Support;

/** Helpers for the staff search boxes (Clients and Shipments). */
class Search
{
    /** The typed text, trimmed and capped; anything that isn't plain text (?q[]=x) counts as empty. */
    public static function text(mixed $raw, int $max = 60): string
    {
        return is_string($raw) ? mb_substr(trim($raw), 0, $max) : '';
    }

    /** A LIKE pattern for `... LIKE ? ESCAPE '!'`. "!" is the escape character, so a typed % or _ is searched for literally. */
    public static function like(string $term): string
    {
        return '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';
    }

    /** @return list<string> The words of the search; every word has to match somewhere. */
    public static function words(string $text): array
    {
        return array_values(array_filter(preg_split('/\s+/', $text) ?: []));
    }

    /** Digits of a typed phone number ("+975 17 975757" -> "97517975757"); empty when the word has none. */
    public static function digits(string $word): string
    {
        return preg_replace('/\D/', '', $word);
    }
}
