<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'duration_months',
        'stripe_product_id',
        'stripe_price_id',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price'           => 'decimal:2',
        'features'        => 'array',
        'is_active'       => 'boolean',
        'duration_months' => 'integer',
        'sort_order'      => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(LocationSubscription::class, 'plan_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
