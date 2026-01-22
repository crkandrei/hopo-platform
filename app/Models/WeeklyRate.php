<?php

namespace App\Models;

use App\Models\Traits\BelongsToLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyRate extends Model
{
    use BelongsToLocation;
    protected $fillable = [
        'location_id',
        'day_of_week',
        'hourly_rate',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'hourly_rate' => 'decimal:2',
    ];


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

