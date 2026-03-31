<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'pricing_mode',
        'overflow_price_per_hour',
        'is_active',
        'bracelet_required',
        'fiscal_enabled',
        'birthday_concurrent_reservations',
        'pre_checkin_enabled',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'bracelet_required' => 'boolean',
        'fiscal_enabled' => 'boolean',
        'birthday_concurrent_reservations' => 'boolean',
        'pre_checkin_enabled' => 'boolean',
        'price_per_hour' => 'decimal:2',
        'overflow_price_per_hour' => 'decimal:2',
        'booking_visit_count' => 'integer',
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

    public function pricingTiers(): HasMany
    {
        return $this->hasMany(PricingTier::class);
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

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function standaloneReceipts(): HasMany
    {
        return $this->hasMany(StandaloneReceipt::class);
    }

    public function bridge(): HasOne
    {
        return $this->hasOne(LocationBridge::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(LocationSubscription::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
