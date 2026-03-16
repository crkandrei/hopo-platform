<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationSubscription extends Model
{
    protected $fillable = [
        'location_id',
        'plan_id',
        'plan_type',
        'starts_at',
        'expires_at',
        'price_paid',
        'payment_method',
        'payment_source',
        'stripe_session_id',
        'stripe_payment_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'price_paid' => 'decimal:2',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
