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
                $user = auth()->user();
                
                // For COMPANY_ADMIN, use location_id from user (set when switching locations)
                if ($user->isCompanyAdmin() && $user->company_id) {
                    if ($user->location_id) {
                        // Verify location belongs to company
                        $location = Location::where('id', $user->location_id)
                            ->where('company_id', $user->company_id)
                            ->where('is_active', true)
                            ->first();
                        if ($location) {
                            $model->location_id = $user->location_id;
                            return;
                        }
                    }
                    // Fallback: use first location from company
                    $firstLocation = Location::where('company_id', $user->company_id)
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->first();
                    if ($firstLocation) {
                        $model->location_id = $firstLocation->id;
                        return;
                    }
                }
                
                // For STAFF, use their assigned location
                if ($user->location_id) {
                    $model->location_id = $user->location_id;
                }
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
            
            // COMPANY_ADMIN vede locația setată pe user (location_id)
            if ($user->isCompanyAdmin() && $user->company_id) {
                // Use location_id from user (updated when switching locations)
                if ($user->location_id) {
                    // Verify location belongs to company
                    $location = Location::where('id', $user->location_id)
                        ->where('company_id', $user->company_id)
                        ->where('is_active', true)
                        ->first();
                    
                    if ($location) {
                        // Filter by user's location_id
                        $builder->where($builder->getModel()->getTable() . '.location_id', $user->location_id);
                        return;
                    }
                }
                
                // Fallback: show all locations from company if no location_id set
                $locationIds = Location::where('company_id', $user->company_id)
                    ->where('is_active', true)
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
