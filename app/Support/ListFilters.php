<?php

namespace App\Support;

use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * The search and filters of the staff lists, in one place so a list and its CSV export always agree on what "these
 * results" means. Every word typed must match somewhere; a typed % or _ is searched for literally.
 */
class ListFilters
{
    /** The chosen stage from the address (?status=...), or '' for all. */
    public static function stage(mixed $raw): string
    {
        return is_string($raw) && Statuses::exists($raw) ? $raw : '';
    }

    /** The chosen Telegram filter from the address (?telegram=connected|not), or ''. */
    public static function telegram(mixed $raw): string
    {
        return in_array($raw, ['connected', 'not'], true) ? $raw : '';
    }

    /** The "assigned to" filter from the address: 'me', 'none' (unassigned), a person's id, or '' for anyone. */
    public static function assignee(mixed $raw): string
    {
        if ($raw === 'me' || $raw === 'none') {
            return $raw;
        }

        return is_string($raw) && ctype_digit($raw) ? $raw : '';
    }

    /** Shipments looked after by a person (or by nobody). $meId is the signed-in staff member, for 'me'. */
    public static function assignedTo(Builder $builder, string $who, ?int $meId): Builder
    {
        return match (true) {
            $who === 'none' => $builder->whereNull('assigned_to'),
            $who === 'me' => $builder->where('assigned_to', $meId ?? 0),
            $who !== '' => $builder->where('assigned_to', (int) $who),
            default => $builder,
        };
    }

    /** Clients: name, company, email or phone; optionally only with / without Telegram. */
    public static function clients(Builder $builder, string $query, string $telegram = ''): Builder
    {
        $like = Search::like(...);
        if ($query !== '') {
            foreach (Search::words($query) as $term) {
                $builder->where(function ($w) use ($term, $like) {
                    $w->whereRaw("full_name LIKE ? ESCAPE '!'", [$like($term)])
                        ->orWhereRaw("company LIKE ? ESCAPE '!'", [$like($term)])
                        ->orWhereRaw("email LIKE ? ESCAPE '!'", [$like($term)]);
                    $digits = Search::digits($term);
                    if ($digits !== '') {                  // phone numbers are stored as +97517975757
                        $w->orWhereRaw("phone LIKE ? ESCAPE '!'", [$like($digits)]);
                    }
                });
            }
        }
        if ($telegram === 'connected') {
            $builder->whereNotNull('telegram_chat_id');
        } elseif ($telegram === 'not') {
            $builder->whereNull('telegram_chat_id');
        }

        return $builder;
    }

    /** Shipments: tracking number (with or without dashes), goods, or the client's name, company, email or phone. The stage is applied separately (see stage()). */
    public static function shipments(Builder $builder, string $query): Builder
    {
        $like = Search::like(...);
        if ($query === '') {
            return $builder;
        }
        foreach (Search::words($query) as $term) {
            $builder->where(function ($w) use ($term, $like) {
                $w->whereRaw("tracking_number LIKE ? ESCAPE '!'", [$like($term)])
                    ->orWhereRaw("REPLACE(tracking_number, '-', '') LIKE ? ESCAPE '!'", [$like(str_replace('-', '', $term))])
                    ->orWhereRaw("goods LIKE ? ESCAPE '!'", [$like($term)])
                    ->orWhereHas('client', function ($c) use ($term, $like) {
                        $c->where(function ($x) use ($term, $like) {
                            $x->whereRaw("full_name LIKE ? ESCAPE '!'", [$like($term)])
                                ->orWhereRaw("company LIKE ? ESCAPE '!'", [$like($term)])
                                ->orWhereRaw("email LIKE ? ESCAPE '!'", [$like($term)]);
                            $digits = Search::digits($term);
                            if ($digits !== '') {
                                $x->orWhereRaw("phone LIKE ? ESCAPE '!'", [$like($digits)]);
                            }
                        });
                    });
            });
        }

        return $builder;
    }
}
