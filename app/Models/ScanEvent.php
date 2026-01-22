<?php

namespace App\Models;

use App\Models\Traits\BelongsToLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanEvent extends Model
{
    use BelongsToLocation;
    protected $fillable = [
        'location_id',
        'bracelet_id',
        'child_id',
        'code_used',
        'status',
        'scanned_at',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'expires_at' => 'datetime',
    ];


    /**
     * Get the child that was scanned.
     */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    /**
     * Check if scan event is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if scan event is valid
     */
    public function isValid(): bool
    {
        return $this->status === 'valid';
    }

    /**
     * Check if scan event is invalid
     */
    public function isInvalid(): bool
    {
        return $this->status === 'invalid';
    }

    /**
     * Check if scan event is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || $this->expires_at->isPast();
    }
}
