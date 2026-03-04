<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BirthdayTimeSlot extends Model
{
    protected $fillable = [
        'birthday_hall_id',
        'day_of_week',
        'start_time',
        'end_time',
        'max_reservations',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'day_of_week' => 'integer',
    ];

    protected static array $dayNames = [
        0 => 'Luni',
        1 => 'Marți',
        2 => 'Miercuri',
        3 => 'Joi',
        4 => 'Vineri',
        5 => 'Sâmbătă',
        6 => 'Duminică',
    ];

    public function birthdayHall(): BelongsTo
    {
        return $this->belongsTo(BirthdayHall::class, 'birthday_hall_id');
    }

    public function birthdayReservations(): HasMany
    {
        return $this->hasMany(BirthdayReservation::class, 'time_slot_id');
    }

    public function getDayNameAttribute(): string
    {
        if ($this->day_of_week === null) {
            return 'Orice zi';
        }
        return self::$dayNames[$this->day_of_week] ?? '';
    }

    public function isAvailableOn(Carbon $date): bool
    {
        if ($this->day_of_week === null) {
            return true;
        }
        // day_of_week: 0=Luni..6=Duminică; Carbon 'N': 1=Monday..7=Sunday
        return (int) $date->format('N') === $this->day_of_week + 1;
    }
}
