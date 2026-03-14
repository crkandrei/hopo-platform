<?php

namespace App\Events;

use App\Services\Reports\Data\DailyReportData;

class DailyReportGenerated
{
    public function __construct(
        public readonly DailyReportData $reportData,
    ) {}
}
