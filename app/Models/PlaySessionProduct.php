<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaySessionProduct extends Model
{
    protected $fillable = [
        'play_session_id',
        'product_id',
        'quantity',
        'unit_price',
        'is_sgr',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'is_sgr' => 'boolean',
    ];

    /**
     * Get the play session that owns this product entry.
     */
    public function playSession(): BelongsTo
    {
        return $this->belongsTo(PlaySession::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate total price for this product entry.
     * 
     * @return float
     */
    public function getTotalPriceAttribute(): float
    {
        return round($this->quantity * $this->unit_price, 2);
    }
}
