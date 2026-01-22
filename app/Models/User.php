<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'company_id',
        'location_id',
        'role_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the company that owns the user (for COMPANY_ADMIN).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the location that owns the user (for STAFF).
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
    
    /**
     * Obține compania efectivă (directă sau prin locație)
     */
    public function getEffectiveCompanyId(): ?int
    {
        if ($this->company_id) {
            return $this->company_id;
        }
        
        return $this->location?->company_id;
    }

    /**
     * Verifică dacă user-ul poate accesa o locație
     */
    public function canAccessLocation(int $locationId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        if ($this->location_id === $locationId) {
            return true;
        }
        
        if ($this->isCompanyAdmin() && $this->company_id) {
            return Location::where('id', $locationId)
                       ->where('company_id', $this->company_id)
                       ->exists();
        }
        
        return false;
    }

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the audit logs for the user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role && $this->role->isSuperAdmin();
    }

    /**
     * Check if user is company admin
     */
    public function isCompanyAdmin(): bool
    {
        return $this->role && $this->role->isCompanyAdmin();
    }

    /**
     * Check if user is staff
     */
    public function isStaff(): bool
    {
        return $this->role && $this->role->isStaff();
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
