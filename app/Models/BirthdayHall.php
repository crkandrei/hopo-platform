<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BirthdayHall extends Model
{
    public const BOOKING_MODE_SLOTS = 'slots';
    public const BOOKING_MODE_FLEXIBLE = 'flexible';

    protected $fillable = [
        'location_id',
        'name',
        'description',
        'capacity',
        'is_active',
        'booking_mode',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(BirthdayTimeSlot::class, 'birthday_hall_id');
    }

    public function birthdayReservations(): HasMany
    {
        return $this->hasMany(BirthdayReservation::class, 'birthday_hall_id');
    }
}
