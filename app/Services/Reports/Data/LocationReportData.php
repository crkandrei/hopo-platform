<?php

namespace App\Services\Reports\Data;

use App\Models\Location;

class LocationReportData
{
    public function __construct(
        public readonly Location $location,
        public readonly int $totalSessions,
        public readonly float $cashTotal,
        public readonly float $cardTotal,
        public readonly float $voucherTotal,
        public readonly float $totalMoney,
        public readonly float $totalBilledHours,
    ) {}
}
