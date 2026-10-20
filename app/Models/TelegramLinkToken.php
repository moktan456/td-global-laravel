<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramLinkToken extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'token_hash';

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'used_at' => 'datetime', 'created_at' => 'datetime'];
    }
}
