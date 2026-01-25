<?php

namespace App\Http\Middleware;

use App\Models\Location;
use Closure;
use Illuminate\Http\Request;

class SetLocationContext
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // For COMPANY_ADMIN, use location_id from user (updated when switching locations)
            $selectedLocation = null;
            if ($user->isCompanyAdmin() && $user->company_id) {
                // Use location_id from user if set
                if ($user->location_id) {
                    $selectedLocation = Location::where('id', $user->location_id)
                        ->where('company_id', $user->company_id)
                        ->where('is_active', true)
                        ->first();
                }
                
                // If no valid location_id on user, use first location from company
                if (!$selectedLocation) {
                    $selectedLocation = Location::where('company_id', $user->company_id)
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->first();
                    
                    // Update user's location_id if we found a location
                    if ($selectedLocation) {
                        $user->location_id = $selectedLocation->id;
                        $user->save();
                    }
                }
            } else {
                // For STAFF, use their assigned location
                $selectedLocation = $user->location;
            }
            
            // Bind pentru acces ușor în cod
            app()->instance('current.user', $user);
            app()->instance('current.location', $selectedLocation);
            app()->instance('current.company', $user->company ?? $selectedLocation?->company);
        }
        
        return $next($request);
    }
}
