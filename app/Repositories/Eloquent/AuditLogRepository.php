<?php

namespace App\Repositories\Eloquent;

use App\Models\AuditLog;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Support\Collection;

class AuditLogRepository implements AuditLogRepositoryInterface
{
    public function latestByLocation(int $locationId, int $limit = 20): Collection
    {
        return AuditLog::where('location_id', $locationId)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }
}




