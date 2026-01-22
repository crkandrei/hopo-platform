<?php

namespace App\Repositories\Eloquent;

use App\Models\Child;
use App\Repositories\Contracts\ChildRepositoryInterface;
use Illuminate\Support\Collection;

class ChildRepository implements ChildRepositoryInterface
{
    public function getAllWithBirthdateByLocation(int $locationId): Collection
    {
        return Child::where('location_id', $locationId)
            ->whereNotNull('birth_date')
            ->get();
    }
}




