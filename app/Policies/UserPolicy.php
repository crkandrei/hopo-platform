<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isCompanyAdmin();
    }

    /**
     * Determine if the user can view the user.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isCompanyAdmin()) {
            $modelCompanyId = $model->company_id ?? $model->location?->company_id;
            return $modelCompanyId === $user->company_id;
        }
        
        return false;
    }

    /**
     * Determine if the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isCompanyAdmin();
    }

    /**
     * Determine if the user can update the user.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isCompanyAdmin()) {
            $modelCompanyId = $model->company_id ?? $model->location?->company_id;
            return $modelCompanyId === $user->company_id;
        }
        
        return false;
    }

    /**
     * Determine if the user can delete the user.
     */
    public function delete(User $user, User $model): bool
    {
        // Nu permite ștergerea propriului cont
        if ($user->id === $model->id) {
            return false;
        }
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isCompanyAdmin()) {
            $modelCompanyId = $model->company_id ?? $model->location?->company_id;
            return $modelCompanyId === $user->company_id;
        }
        
        return false;
    }
}
