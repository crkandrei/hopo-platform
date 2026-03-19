<?php
// app/Models/LocationBridge.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocationBridge extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'api_key',
        'client_id',
        'status',
        'version',
        'mode',
        'last_seen_at',
        'last_print_at',
        'print_count',
        'z_report_count',
        'error_count',
        'uptime',
    ];

    protected $casts = [
        'last_seen_at'   => 'datetime',
        'last_print_at'  => 'datetime',
        'print_count'    => 'integer',
        'z_report_count' => 'integer',
        'error_count'    => 'integer',
        'uptime'         => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(BridgeCommand::class, 'location_id', 'location_id');
    }
}
