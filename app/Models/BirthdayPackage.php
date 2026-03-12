<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class BirthdayPackage extends Model
{
    public const DAY_NAMES = [
        0 => 'Luni',
        1 => 'Marți',
        2 => 'Miercuri',
        3 => 'Joi',
        4 => 'Vineri',
        5 => 'Sâmbătă',
        6 => 'Duminică',
    ];

    protected $fillable = [
        'location_id',
        'name',
        'description',
        'duration_minutes',
        'includes_food',
        'includes_decorations',
        'is_active',
    ];

    protected $casts = [
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

    public function birthdayPackageWeekdays(): HasMany
    {
        return $this->hasMany(BirthdayPackageWeekday::class)->orderBy('day_of_week');
    }

    public function scopeAvailableOnDate(Builder $query, Carbon $date): Builder
    {
        $dayOfWeek = (int) $date->format('N') - 1;

        return $query->whereHas('birthdayPackageWeekdays', function (Builder $builder) use ($dayOfWeek) {
            $builder->where('day_of_week', $dayOfWeek);
        });
    }

    public function isAvailableOn(Carbon $date): bool
    {
        $dayOfWeek = (int) $date->format('N') - 1;

        if ($this->relationLoaded('birthdayPackageWeekdays')) {
            return $this->birthdayPackageWeekdays->contains('day_of_week', $dayOfWeek);
        }

        return $this->birthdayPackageWeekdays()
            ->where('day_of_week', $dayOfWeek)
            ->exists();
    }

    public function getAvailableDayNamesAttribute(): array
    {
        $dayIndexes = $this->relationLoaded('birthdayPackageWeekdays')
            ? $this->birthdayPackageWeekdays->pluck('day_of_week')->all()
            : $this->birthdayPackageWeekdays()->pluck('day_of_week')->all();

        return collect($dayIndexes)
            ->sort()
            ->map(fn (int $dayOfWeek) => self::DAY_NAMES[$dayOfWeek] ?? '')
            ->filter()
            ->values()
            ->all();
    }
}
