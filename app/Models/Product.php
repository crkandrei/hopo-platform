<?php

namespace App\Models;

use App\Models\Traits\BelongsToLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, BelongsToLocation;

    const SGR_VALUE = 0.50;

    protected $fillable = [
        'location_id',
        'name',
        'price',
        'is_active',
        'tva_rate_id',
        'has_sgr',
        'barcode',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'has_sgr' => 'boolean',
    ];


    public function tvaRate(): BelongsTo
    {
        return $this->belongsTo(TvaRate::class, 'tva_rate_id');
    }

    /**
     * Get the play session products for this product.
     */
    public function playSessionProducts(): HasMany
    {
        return $this->hasMany(PlaySessionProduct::class);
    }
}
