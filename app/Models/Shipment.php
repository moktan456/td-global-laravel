<?php

namespace App\Models;

use App\Support\Statuses;
use App\Support\TrackingNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One clearance request = one tracking number. Every stage change (and the first stage) is written to the timeline,
 * the job the database triggers did in the Supabase version.
 */
class Shipment extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (Shipment $s) {
            $s->tracking_number ??= TrackingNumber::generate();
            $s->status ??= Statuses::keys()[0];
        });
        static::created(fn (Shipment $s) => $s->logStage());
        static::updated(function (Shipment $s) {
            if ($s->wasChanged('status')) {
                $s->logStage();
            }
        });
    }

    /** Writes the current stage to the timeline. */
    public function logStage(?string $note = null): ShipmentEvent
    {
        return $this->events()->create([
            'status' => $this->status,
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
