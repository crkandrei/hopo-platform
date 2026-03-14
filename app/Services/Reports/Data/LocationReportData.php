<?php

namespace App\Services\Reports\Data;

use App\Models\Location;
use Illuminate\Support\Collection;

class LocationReportData
{
    public function __construct(
        public readonly Location $location,

        // Session data (time billing + session products)
        public readonly int $totalSessions,
        public readonly float $cashTotal,
        public readonly float $cardTotal,
        public readonly float $voucherTotal,
        public readonly float $totalMoney,
        public readonly float $totalBilledHours,

        // Standalone product sales
        public readonly Collection $productSales,
        public readonly float $productsCash,
        public readonly float $productsCard,
        public readonly float $productsVoucher,
        public readonly float $productsTotal,

        // Standalone package sales
        public readonly Collection $packageSales,
        public readonly float $packagesCash,
        public readonly float $packagesCard,
        public readonly float $packagesVoucher,
        public readonly float $packagesTotal,

        // Grand total across sessions + products + packages
        public readonly float $grandCash,
        public readonly float $grandCard,
        public readonly float $grandVoucher,
        public readonly float $grandTotal,
    ) {}
}
