<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingTier extends Model
{
    protected $fillable = [
        'location_id',
        'day_of_week',
        'duration_hours',
        'price',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'duration_hours' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get day name in Romanian
     */
    public function getDayNameAttribute(): string
    {
        $days = [
            0 => 'Luni',
            1 => 'Marți',
            2 => 'Miercuri',
            3 => 'Joi',
            4 => 'Vineri',
            5 => 'Sâmbătă',
            6 => 'Duminică',
        ];

        return $days[$this->day_of_week] ?? 'Necunoscut';
    }
}
