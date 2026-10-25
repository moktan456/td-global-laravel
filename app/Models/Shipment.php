<?php

namespace App\Models;

use App\Support\Statuses;
use App\Support\TrackingNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One clearance request = one tracking number, following customs clearance, transport, or both. Every stage change (and each track's first stage) is written to the timeline,
 * the job the database triggers did in the Supabase version.
 */
class Shipment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'customs_enabled' => 'boolean',
            'transport_enabled' => 'boolean',
            'expected_delivery' => 'date',
            'delivered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Shipment $s) {
            $s->tracking_number ??= TrackingNumber::generate();
            $s->customs_enabled ??= true;
            $s->transport_enabled ??= false;
            // Each track that is switched on starts at its first stage; a track that is off has no stage.
            $s->status ??= $s->customs_enabled ? Statuses::first(Statuses::CUSTOMS) : null;
            $s->transport_status ??= $s->transport_enabled ? Statuses::first(Statuses::TRANSPORT) : null;
            $s->mustFollowATrack();
        });
        // Switching a track on starts it at its first stage (a track switched off earlier keeps the stage it had).
        static::updating(function (Shipment $s) {
            if ($s->customs_enabled && $s->status === null) {
                $s->status = Statuses::first(Statuses::CUSTOMS);
            }
            if ($s->transport_enabled && $s->transport_status === null) {
                $s->transport_status = Statuses::first(Statuses::TRANSPORT);
            }
            $s->mustFollowATrack();
        });
        static::created(function (Shipment $s) {
            foreach ($s->tracks() as $track) {
                $s->logStage(null, $track);
            }
        });
        static::updated(function (Shipment $s) {
            // Only a track that is switched on writes to the timeline.
            if ($s->customs_enabled && $s->wasChanged('status')) {
                $s->logStage(null, Statuses::CUSTOMS);
            }
            if ($s->transport_enabled && $s->wasChanged('transport_status')) {
                $s->logStage(null, Statuses::TRANSPORT);
            }
        });
    }

    /** A shipment always follows at least one track (customs clearance, transport, or both). */
    private function mustFollowATrack(): void
    {
        if (! $this->customs_enabled && ! $this->transport_enabled) {
            throw new \InvalidArgumentException('A shipment must follow customs clearance, transport, or both.');
        }
    }

    /** @return list<string> The tracks this shipment follows (at least one), customs first. */
    public function tracks(): array
    {
        return array_values(array_filter([
            $this->customs_enabled ? Statuses::CUSTOMS : null,
            $this->transport_enabled ? Statuses::TRANSPORT : null,
        ]));
    }

    public function followsTransport(): bool
    {
        return (bool) $this->transport_enabled;
    }

    /** The current stage key on a track (null when the track is off). */
    public function stageOn(string $track): ?string
    {
        return match (true) {
            $track === Statuses::CUSTOMS && $this->customs_enabled => $this->status,
            $track === Statuses::TRANSPORT && $this->transport_enabled => $this->transport_status,
            default => null,
        };
    }

    /** One stage to show where a single badge is wanted: the customs stage, or the transport stage when customs is off. */
    public function primaryStatus(): string
    {
        return $this->stageOn(Statuses::CUSTOMS) ?? $this->stageOn(Statuses::TRANSPORT) ?? $this->status ?? $this->transport_status ?? Statuses::first(Statuses::CUSTOMS);
    }

    /** Where the shipment is, in words: "Pending Risk", or with two tracks "Customs: Pending Risk · Transport: In Transit". */
    public function stageSummary(): string
    {
        $tracks = $this->tracks();
        if (count($tracks) < 2) {
            return Statuses::info($this->primaryStatus())['label'];
        }

        return implode(' · ', array_map(fn ($t) => ($t === Statuses::CUSTOMS ? 'Customs' : 'Transport').': '.Statuses::info($this->stageOn($t))['label'], $tracks));
    }

    /** Is every track this shipment follows at its last stage? */
    public function isFinished(): bool
    {
        foreach ($this->tracks() as $track) {
            if ($this->stageOn($track) !== Statuses::last($track)) {
                return false;
            }
        }

        return true;
    }

    /** Shipments with at least one track that is switched on and not yet at its last stage. */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where(fn ($c) => $c->where('customs_enabled', true)->where('status', '!=', Statuses::last(Statuses::CUSTOMS)))
                ->orWhere(fn ($t) => $t->where('transport_enabled', true)->where('transport_status', '!=', Statuses::last(Statuses::TRANSPORT)));
        });
    }

    /** Writes the current stage of a track to the timeline. */
    public function logStage(?string $note = null, string $track = Statuses::CUSTOMS): ShipmentEvent
    {
        return $this->events()->create([
            'track' => $track,
            'status' => $this->stageOn($track),
            'note' => $note,
            'created_by' => auth()->id(),
        ]);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** The staff member looking after this shipment (null = nobody yet). */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShipmentEvent::class)->orderBy('created_at')->orderBy('id');
    }
}
