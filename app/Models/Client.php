<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'telegram_linked_at' => 'datetime',
            'notify_email' => 'boolean',
            'notify_telegram' => 'boolean',
            'telegram_chat_id' => 'integer',
        ];
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
