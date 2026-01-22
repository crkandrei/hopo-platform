<?php

namespace App\Models;

use App\Models\Traits\BelongsToLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Child extends Model
{
    use HasFactory, BelongsToLocation;
    protected $fillable = [
        'location_id',
        'guardian_id',
        'name',
        'birth_date',
        'allergies',
        'internal_code',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];


    /**
     * Get the guardian that owns the child.
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    /**
     * Get the scan events for the child.
     */
    public function scanEvents(): HasMany
    {
        return $this->hasMany(ScanEvent::class);
    }

    /**
     * Get all play sessions for the child.
     */
    public function playSessions(): HasMany
    {
        return $this->hasMany(PlaySession::class);
    }

    /**
     * Get only active play sessions (not ended) for the child.
     */
    public function activeSessions(): HasMany
    {
        return $this->hasMany(PlaySession::class)->whereNull('ended_at');
    }
}
