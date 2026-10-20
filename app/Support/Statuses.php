<?php

namespace App\Support;

/** The eight clearance stages (resources/content/statuses.php). */
class Statuses
{
    public static function all(): array
    {
        return content('statuses.statuses');
    }

    public static function keys(): array
    {
        return array_column(self::all(), 'key');
    }

    public static function exists(?string $key): bool
    {
        return in_array($key, self::keys(), true);
    }

    /** Label and client wording of a stage; unknown keys fall back to the first stage, like the Next.js code. */
    public static function info(string $key): array
    {
        foreach (self::all() as $s) {
            if ($s['key'] === $key) {
                return $s;
            }
        }

        return self::all()[0];
    }
}
