<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandaloneReceiptItem extends Model
{
    protected $fillable = [
        'standalone_receipt_id',
        'source_type',
        'source_id',
        'name',
        'unit_price',
        'quantity',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function standaloneReceipt(): BelongsTo
    {
        return $this->belongsTo(StandaloneReceipt::class);
    }

    public function getTotalPriceAttribute(): float
    {
        return round((float) $this->unit_price * $this->quantity, 2);
    }
}
