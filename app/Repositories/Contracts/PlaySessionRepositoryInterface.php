<?php

namespace App\Repositories\Contracts;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PlaySessionRepositoryInterface
{
    public function countActiveSessionsByLocation(int $locationId): int;
    
    public function countSessionsStartedSince(int $locationId, Carbon $since): int;

    public function countActiveSessionsStartedSince(int $locationId, Carbon $since): int;

    public function getSessionsSince(int $locationId, Carbon $since): Collection;

    public function getSessionsBetween(int $locationId, Carbon $start, Carbon $end): Collection;

    public function getAllByLocation(int $locationId): Collection;

    public function getActiveSessionsWithRelations(int $locationId): Collection;

    /**
     * Paginate sessions with search/sort.
     * Returns [total => int, rows => Collection<array>]
     */
    public function paginateSessions(
        int $locationId,
        int $page,
        int $perPage,
        ?string $search,
        string $sortBy,
        string $sortDir,
        Carbon $date
    ): array;
}


