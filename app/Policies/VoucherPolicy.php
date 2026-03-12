<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;
use App\Models\Voucher;

class VoucherPolicy
{
    public function viewAny(User $user, Location $location): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return $user->isCompanyAdmin() && $user->canAccessLocation($location->id);
    }

    public function create(User $user, Location $location): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return $user->isCompanyAdmin() && $user->canAccessLocation($location->id);
    }

    public function update(User $user, Voucher $voucher): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return $user->isCompanyAdmin() && $user->canAccessLocation($voucher->location_id);
    }

    public function delete(User $user, Voucher $voucher): bool
    {
        return $this->update($user, $voucher);
    }

    public function view(User $user, Voucher $voucher): bool
    {
        return $user->canAccessLocation($voucher->location_id);
    }

    public function validateAt(User $user, Location $location): bool
    {
        return $user->isSuperAdmin() || $user->canAccessLocation($location->id);
    }
}
