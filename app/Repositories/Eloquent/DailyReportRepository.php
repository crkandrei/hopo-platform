<?php

namespace App\Repositories\Eloquent;

use App\Models\Location;
use App\Models\PlaySession;
use App\Models\StandaloneReceipt;
use App\Repositories\Contracts\DailyReportRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyReportRepository implements DailyReportRepositoryInterface
{
    public function getSessionsForLocationAndDate(Location $location, Carbon $date): Collection
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        return PlaySession::where('location_id', $location->id)
            ->whereBetween('started_at', [$startOfDay, $endOfDay])
            ->with(['child', 'products.product', 'voucherUsages'])
            ->get();
    }

    public function getStandaloneReceiptsForLocationAndDate(Location $location, Carbon $date): Collection
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        return StandaloneReceipt::where('location_id', $location->id)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$startOfDay, $endOfDay])
            ->with(['items', 'voucherUsages'])
            ->get();
    }
}
