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

        // Grand totals across all locations
        public readonly float $grandCash,
        public readonly float $grandCard,
        public readonly float $grandVoucher,
        public readonly float $grandTotal,

        public readonly bool $hasActivity,

        // Today's reservations (date the email is sent)
        public readonly Collection $todayReservations,
        public readonly bool $hasReservations,
    ) {}
}
