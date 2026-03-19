<?php
// app/Models/BridgeCommand.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BridgeCommand extends Model
{
    use HasUuids;

    protected $fillable = [
        'location_id',
        'command',
        'payload',
        'status',
        'ack_message',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
