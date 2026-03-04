<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BirthdayPackage extends Model
{
    protected $fillable = [
        'location_id',
        'name',
        'description',
        'price',
        'duration_minutes',
        'includes_food',
        'includes_decorations',
        'max_children',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'includes_food' => 'boolean',
        'includes_decorations' => 'boolean',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function birthdayReservations(): HasMany
    {
        return $this->hasMany(BirthdayReservation::class, 'birthday_package_id');
    }
}
