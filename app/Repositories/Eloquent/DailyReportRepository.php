<?php

namespace App\Repositories\Eloquent;

use App\Models\BirthdayReservation;
use App\Models\Company;
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
            ->with(['child', 'products.product', 'voucherUsages', 'intervals'])
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

    public function getReservationsForCompany(Company $company, Carbon $date): Collection
    {
        $locationIds = $company->locations()->where('is_active', true)->pluck('id');

        return BirthdayReservation::whereIn('location_id', $locationIds)
            ->whereDate('reservation_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->with(['location', 'birthdayPackage', 'birthdayHall', 'timeSlot'])
            ->orderBy('reservation_time')
            ->get();
    }
}
