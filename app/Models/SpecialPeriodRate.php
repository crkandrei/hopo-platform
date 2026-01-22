<?php

namespace App\Models;

use App\Models\Traits\BelongsToLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialPeriodRate extends Model
{
    use BelongsToLocation;
    protected $fillable = [
        'location_id',
        'name',
        'start_date',
        'end_date',
        'hourly_rate',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'hourly_rate' => 'decimal:2',
    ];


    /**
     * Check if a date falls within this special period
     */
    public function includesDate($date): bool
    {
        $date = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        return $date->between($this->start_date, $this->end_date);
    }
}

