<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface ChildRepositoryInterface
{
    public function getAllWithBirthdateByLocation(int $locationId): Collection;
}




