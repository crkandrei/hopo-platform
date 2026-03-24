<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'logo_path',
        'is_active',
        'daily_report_enabled',
        'daily_report_email',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'daily_report_enabled' => 'boolean',
        'daily_report_last_sent_at' => 'datetime',
    ];

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function subscriptionPlans(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'company_subscription_plans');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    
    public function admins(): HasMany
    {
        return $this->users()->whereHas('role', fn($q) =>
            $q->where('name', 'COMPANY_ADMIN')
        );
    }

    public function isDailyReportEnabled(): bool
    {
        return $this->daily_report_enabled;
    }

    public function getDailyReportEmail(): ?string
    {
        return $this->daily_report_email ?? $this->email;
    }

    public function markDailyReportSent(): void
    {
        $this->update(['daily_report_last_sent_at' => now()]);
    }

    public function logoUrl(): string
    {
        return $this->logo_path
            ? Storage::disk('public')->url($this->logo_path)
            : asset('images/hopo-logo.png');
    }
}
