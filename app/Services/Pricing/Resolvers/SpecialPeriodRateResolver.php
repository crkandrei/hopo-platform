<?php

namespace App\Services\Pricing\Resolvers;

use App\Models\Location;
use App\Models\SpecialPeriodRate;
use Carbon\Carbon;

class SpecialPeriodRateResolver
{
    /**
     * Find the applicable special period rate for a location on a given date.
     * When multiple periods overlap, the most recently created one wins.
     */
    public function find(Location $location, Carbon $date): ?SpecialPeriodRate
    {
        return SpecialPeriodRate::where('location_id', $location->id)
            ->where('start_date', '<=', $date->format('Y-m-d'))
            ->where('end_date', '>=', $date->format('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
