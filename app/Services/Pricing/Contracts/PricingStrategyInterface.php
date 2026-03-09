<?php

namespace App\Services\Pricing\Contracts;

use App\Models\Location;

interface PricingStrategyInterface
{
    /**
     * Calculate the price for a given duration and date.
     *
     * @param Location $location
     * @param float $durationInHours Effective duration in hours
     * @param \Carbon\Carbon|string|null $date Date for rate resolution (e.g. session start)
     * @return float The calculated price in RON
     */
    public function calculatePrice(Location $location, float $durationInHours, $date = null): float;

    /**
     * Get the hourly rate applicable for the location on the given date.
     * Used for display and for storing price_per_hour_at_calculation (flat mode).
     * In tiered mode, may return overflow rate or default for display.
     *
     * @param Location $location
     * @param \Carbon\Carbon|string|null $date
     * @return float Hourly rate in RON
     */
    public function getHourlyRate(Location $location, $date = null): float;

    /**
     * Get applicable rates for display (e.g. UI summary).
     *
     * @param Location $location
     * @param \Carbon\Carbon|string|null $date
     * @return array Structure depends on strategy (e.g. ['hourly_rate' => 25] or ['tiers' => [...]])
     */
    public function getApplicableRates(Location $location, $date = null): array;
}
