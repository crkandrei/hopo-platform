<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;

class LocationPolicy
{
    /**
     * Determine if the user can view any locations.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isCompanyAdmin();
    }

    /**
     * Determine if the user can view the location.
     */
    public function view(User $user, Location $location): bool
    {
        return $user->canAccessLocation($location->id);
    }

    /**
     * Determine if the user can create locations.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isCompanyAdmin();
    }

    /**
     * Determine if the user can update the location.
     */
    public function update(User $user, Location $location): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->isCompanyAdmin() && 
               $user->company_id === $location->company_id;
    }

    /**
     * Determine if the user can delete the location.
     */
    public function delete(User $user, Location $location): bool
    {
        return $user->isSuperAdmin();
    }
}
