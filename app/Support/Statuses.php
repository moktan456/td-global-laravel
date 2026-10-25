<?php

namespace App\Support;

/**
 * The stages a shipment moves through, on up to two tracks (resources/content/statuses.php):
 *  - customs: the clearance stages (shipments.status)
 *  - transport: the optional delivery stages (shipments.transport_status)
 * Stage keys are unique across both tracks, so a key alone says which track it belongs to.
 */
class Statuses
{
    public const CUSTOMS = 'customs';

    public const TRANSPORT = 'transport';

    /** What each track is called on screen. */
    public const TRACK_LABELS = [self::CUSTOMS => 'Customs clearance', self::TRANSPORT => 'Transport'];

    /** The customs stages (the original list; lists and filters that don't say "transport" mean these). */
    public static function all(): array
    {
        return content('statuses.statuses');
    }

    public static function transport(): array
    {
        return content('statuses.transport');
    }

    /** The stages of one track, in order. */
    public static function forTrack(string $track): array
    {
        return $track === self::TRANSPORT ? self::transport() : self::all();
    }

    /** @return list<string> The two track names, customs first. */
    public static function tracks(): array
    {
        return [self::CUSTOMS, self::TRANSPORT];
    }

    public static function trackLabel(string $track): string
    {
        return self::TRACK_LABELS[$track] ?? ucfirst($track);
    }

    /** Customs stage keys. */
    public static function keys(): array
    {
        return array_column(self::all(), 'key');
    }

    /** The stage keys of a track. */
    public static function keysFor(string $track): array
    {
        return array_column(self::forTrack($track), 'key');
    }

    /** Is this a customs stage? (Kept as it was: the customs filters and forms rely on it.) */
    public static function exists(?string $key): bool
    {
        return in_array($key, self::keys(), true);
    }

    /** Is this a stage of the given track? */
    public static function existsIn(string $track, ?string $key): bool
    {
        return in_array($key, self::keysFor($track), true);
    }

    /** Which track a stage key belongs to, or null if it is not a stage. */
    public static function trackOf(?string $key): ?string
    {
        foreach (self::tracks() as $track) {
            if (self::existsIn($track, $key)) {
                return $track;
            }
        }

        return null;
    }

    /** The first stage of a track (where it starts). */
    public static function first(string $track): string
    {
        return self::keysFor($track)[0];
    }

    /** The last stage of a track (the track is finished there). */
    public static function last(string $track): string
    {
        $keys = self::keysFor($track);

        return $keys[array_key_last($keys)];
    }

    /** Position of a stage within its track: [1-based position, number of stages]. */
    public static function position(?string $key): ?array
    {
        $track = self::trackOf($key);
        if (! $track) {
            return null;
        }
        $keys = self::keysFor($track);

        return [array_search($key, $keys, true) + 1, count($keys)];
    }

    /** Label and client wording of a stage, from either track; unknown keys fall back to the first customs stage, like the Next.js code. */
    public static function info(string $key): array
    {
        foreach (array_merge(self::all(), self::transport()) as $s) {
            if ($s['key'] === $key) {
                return $s;
            }
        }

        return self::all()[0];
    }
}
