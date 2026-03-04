<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'address',
        'phone',
        'email',
        'price_per_hour',
        'is_active',
        'bracelet_required',
        'fiscal_enabled',
        'bridge_config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'bracelet_required' => 'boolean',
        'fiscal_enabled' => 'boolean',
        'price_per_hour' => 'decimal:2',
        'bridge_config' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(Guardian::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function playSessions(): HasMany
    {
        return $this->hasMany(PlaySession::class);
    }

    public function weeklyRates(): HasMany
    {
        return $this->hasMany(WeeklyRate::class);
    }

    public function specialPeriodRates(): HasMany
    {
        return $this->hasMany(SpecialPeriodRate::class);
    }

    public function birthdayHalls(): HasMany
    {
        return $this->hasMany(BirthdayHall::class);
    }

    public function birthdayPackages(): HasMany
    {
        return $this->hasMany(BirthdayPackage::class);
    }

    public function birthdayReservations(): HasMany
    {
        return $this->hasMany(BirthdayReservation::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
