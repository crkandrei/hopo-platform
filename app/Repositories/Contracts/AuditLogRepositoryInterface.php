<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface AuditLogRepositoryInterface
{
    public function latestByLocation(int $locationId, int $limit = 20): Collection;
}




