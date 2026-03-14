<?php

namespace App\Services\Reports\Data;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyReportData
{
    public function __construct(
        public readonly Company $company,
        public readonly Carbon $date,
        public readonly Collection $locationReports,
        public readonly float $grandTotalMoney,
        public readonly bool $hasActivity,
    ) {}
}
