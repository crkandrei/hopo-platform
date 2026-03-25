<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BirthdayReservation extends Model
{
    protected $fillable = [
        'location_id',
        'birthday_hall_id',
        'birthday_package_id',
        'time_slot_id',
        'reservation_date',
        'reservation_time',
        'child_name',
        'child_age',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'number_of_children',
        'number_of_adults',
        'notes',
        'status',
        'token',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'status' => 'string',
    ];

    protected static function booted(): void
    {
        static::creating(function (BirthdayReservation $reservation) {
            if (empty($reservation->token)) {
                $reservation->token = (string) Str::uuid();
            }
        });
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function birthdayHall(): BelongsTo
    {
        return $this->belongsTo(BirthdayHall::class, 'birthday_hall_id');
    }

    public function birthdayPackage(): BelongsTo
    {
        return $this->belongsTo(BirthdayPackage::class, 'birthday_package_id');
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(BirthdayTimeSlot::class, 'time_slot_id');
    }
}
