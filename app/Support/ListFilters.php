<?php

namespace App\Support;

use App\Models\Client;
use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * The search and filters of the staff lists, in one place so a list and its CSV export always agree on what "these
 * results" means. Every word typed must match somewhere; a typed % or _ is searched for literally.
 */
class ListFilters
{
    /** The chosen stage from the address (?status=...) on either track, or '' for all. A stage key names its own track. */
    public static function stage(mixed $raw): string
    {
        return is_string($raw) && Statuses::trackOf($raw) !== null ? $raw : '';
    }

    /** Shipments whose customs or transport track is at this stage (a track that is switched off never matches). */
    public static function atStage(Builder $builder, string $stage): Builder
    {
        return match (Statuses::trackOf($stage)) {
            Statuses::CUSTOMS => $builder->where('customs_enabled', true)->where('status', $stage),
            Statuses::TRANSPORT => $builder->where('transport_enabled', true)->where('transport_status', $stage),
            default => $builder,
        };
    }

    /** How the shipment list is ordered (?sort=...). Anything unknown means newest update first. */
    public const SORTS = [
        'updated' => 'Recently updated',
        'oldest' => 'Longest since updated',
        'client' => 'Client A to Z',
        'stage' => 'Customs stage, first to last',
        'tracking' => 'Tracking number',
    ];

    public static function sort(mixed $raw): string
    {
        return is_string($raw) && isset(self::SORTS[$raw]) ? $raw : 'updated';
    }

    public static function sortShipments(Builder $builder, string $sort): Builder
    {
        switch ($sort) {
            case 'oldest':
                return $builder->orderBy('updated_at')->orderBy('id');
            case 'tracking':
                return $builder->orderBy('tracking_number');
            case 'client':
                return $builder->orderBy(
                    Client::query()->selectRaw('LOWER(COALESCE(NULLIF(company, \'\'), full_name))')->whereColumn('clients.id', 'shipments.client_id')->limit(1)
                )->orderByDesc('updated_at');
            case 'stage':   // the position of the customs stage in the list; shipments with customs off go last
                $keys = array_column(Statuses::all(), 'key');
                $case = 'CASE status '.implode(' ', array_map(fn ($k, $i) => "WHEN '{$k}' THEN {$i}", $keys, array_keys($keys))).' ELSE 99 END';

                return $builder->orderByRaw('customs_enabled DESC')->orderByRaw($case)->orderByDesc('updated_at');
            default:
                return $builder->orderByDesc('updated_at')->orderByDesc('id');
        }
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
