<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreCheckinToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'location_id',
        'child_id',
        'guardian_id',
        'status',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    public function isValid(): bool
    {
        return $this->status === 'pending'
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at === null || $this->expires_at->isPast();
    }

    public function markAsUsed(): bool
    {
        $affected = static::where('id', $this->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'used',
                'used_at' => now(),
            ]);

        if ($affected) {
            $this->status = 'used';
            $this->used_at = now();
        }

        return $affected > 0;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'pending')->where('expires_at', '>', now());
    }
}
