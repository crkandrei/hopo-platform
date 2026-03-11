<?php

namespace App\Models;

use App\Models\Traits\BelongsToLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandaloneReceipt extends Model
{
    use BelongsToLocation;

    protected $fillable = [
        'location_id',
        'created_by',
        'payment_method',
        'payment_status',
        'paid_at',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StandaloneReceiptItem::class);
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }
}
