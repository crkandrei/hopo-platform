<?php
// app/Models/BridgeLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BridgeLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'location_id',
        'level',
        'message',
        'timestamp',
        'created_at',
    ];

    protected $casts = [
        'timestamp'  => 'datetime',
        'created_at' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
