<?php

namespace App\Services\Pricing;

use App\Models\Location;
use App\Services\Pricing\Contracts\PricingStrategyInterface;
use App\Services\Pricing\Strategies\FlatHourlyStrategy;
use App\Services\Pricing\Strategies\TieredDurationStrategy;

class PricingStrategyFactory
{
    public function __construct(
        private FlatHourlyStrategy $flatHourlyStrategy,
        private TieredDurationStrategy $tieredDurationStrategy
    ) {
    }

    public function make(Location $location): PricingStrategyInterface
    {
        $mode = $location->pricing_mode ?? 'flat_hourly';

        return match ($mode) {
            'tiered' => $this->tieredDurationStrategy,
            default => $this->flatHourlyStrategy,
        };
    }
}
