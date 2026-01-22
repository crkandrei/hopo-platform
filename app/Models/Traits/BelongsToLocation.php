<?php

namespace App\Models\Traits;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToLocation
{
    public static function bootBelongsToLocation(): void
    {
        // Auto-fill location_id la creare
        static::creating(function ($model) {
            if (empty($model->location_id) && auth()->check()) {
                $model->location_id = auth()->user()->location_id;
            }
        });

        // Global scope - izolare automată
        static::addGlobalScope('location', function (Builder $builder) {
            if (!auth()->check()) {
                return;
            }
            
            $user = auth()->user();
            
            // SUPER_ADMIN vede tot
            if ($user->isSuperAdmin()) {
                return;
            }
            
            // COMPANY_ADMIN vede toate locațiile companiei
            if ($user->isCompanyAdmin() && $user->company_id) {
                $locationIds = Location::where('company_id', $user->company_id)
                    ->pluck('id');
                $builder->whereIn($builder->getModel()->getTable() . '.location_id', $locationIds);
                return;
            }
            
            // STAFF vede doar locația sa
            if ($user->location_id) {
                $builder->where($builder->getModel()->getTable() . '.location_id', $user->location_id);
            }
        });
    }
    
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
    
    public function scopeForLocation(Builder $query, int $locationId): Builder
    {
        return $query->withoutGlobalScope('location')
                     ->where('location_id', $locationId);
    }
}
