<?php

namespace App\Repositories\Contracts;

use App\Models\Company;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface DailyReportRepositoryInterface
{
    public function getSessionsForLocationAndDate(Location $location, Carbon $date): Collection;

    public function getStandaloneReceiptsForLocationAndDate(Location $location, Carbon $date): Collection;

    public function getReservationsForCompany(Company $company, Carbon $date): Collection;
}
