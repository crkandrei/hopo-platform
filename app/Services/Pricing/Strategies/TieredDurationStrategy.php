<?php

namespace App\Services\Pricing\Strategies;

use App\Models\Location;
use App\Models\PricingTier;
use App\Services\Pricing\Contracts\PricingStrategyInterface;
use Carbon\Carbon;

class TieredDurationStrategy implements PricingStrategyInterface
{
    public function __construct(
        private FlatHourlyStrategy $flatHourlyStrategy
    ) {
    }

    /**
     * Calculate price from tiers: round duration up to next tier, then apply tier price or overflow.
     * If special period applies: use period's tiered prices when mode is tiered, else delegate to flat.
     */
    public function calculatePrice(Location $location, float $durationInHours, $date = null): float
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $specialPeriod = $this->flatHourlyStrategy->getSpecialPeriodForDate($location, $checkDate);

        if ($specialPeriod) {
            if ($specialPeriod->isTiered()) {
                $roundedHours = $this->flatHourlyStrategy->roundToHalfHour($durationInHours);
                return $specialPeriod->calculateTieredPrice($roundedHours);
            }
            return $this->flatHourlyStrategy->calculatePrice($location, $durationInHours, $date);
        }

        $systemDayOfWeek = $this->toSystemDayOfWeek($checkDate);
        $tiers = $this->getTiersForDay($location, $systemDayOfWeek);

        if ($tiers->isEmpty()) {
            return $this->flatHourlyStrategy->calculatePrice($location, $durationInHours, $date);
        }

        $roundedHours = $this->flatHourlyStrategy->roundToHalfHour($durationInHours);
        $tier = $this->findBaseTier($tiers, $roundedHours);

        if ($tier) {
            return $this->calculateTierPrice($tier, $roundedHours);
        }

        return $this->flatHourlyStrategy->calculatePrice($location, $durationInHours, $date);
    }

    public function getHourlyRate(Location $location, $date = null): float
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        if ($this->flatHourlyStrategy->getSpecialPeriodForDate($location, $checkDate)) {
            return $this->flatHourlyStrategy->getHourlyRate($location, $date);
        }

        $overflow = $location->overflow_price_per_hour;
        if ($overflow !== null && $overflow > 0) {
            return (float) $overflow;
        }
        return (float) ($location->price_per_hour ?? 0.00);
    }

    public function getApplicableRates(Location $location, $date = null): array
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $systemDayOfWeek = $this->toSystemDayOfWeek($checkDate);
        $tiers = $this->getTiersForDay($location, $systemDayOfWeek);

        $byDuration = [];
        foreach ($tiers as $tier) {
            $byDuration[(string) $tier->duration_hours] = (float) $tier->price;
        }
        ksort($byDuration, SORT_NUMERIC);

        return [
            'mode' => 'tiered',
            'tiers' => $byDuration,
            'overflow_per_hour' => $location->overflow_price_per_hour ? (float) $location->overflow_price_per_hour : null,
        ];
    }

    private function toSystemDayOfWeek(Carbon $date): int
    {
        $dayOfWeek = $date->dayOfWeek;
        return $dayOfWeek === 0 ? 6 : $dayOfWeek - 1;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, PricingTier>
     */
    private function getTiersForDay(Location $location, int $dayOfWeek): \Illuminate\Database\Eloquent\Collection
    {
        return $location->pricingTiers()
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('duration_hours')
            ->get();
    }

    /**
     * Find largest tier where duration_hours <= roundedHours (base tier for billing).
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, PricingTier> $tiers
     */
    private function findBaseTier(\Illuminate\Database\Eloquent\Collection $tiers, float $roundedHours): ?PricingTier
    {
        $matched = null;
        foreach ($tiers as $tier) {
            if ((float) $tier->duration_hours <= $roundedHours) {
                $matched = $tier;
            }
        }
        return $matched;
    }

    /**
     * Calculate price using base tier + excess billed at base tier's hourly rate.
     * e.g. 2.5h with 2h=50RON tier: 50 + 0.5 * (50/2) = 62.5 RON
     */
    private function calculateTierPrice(PricingTier $tier, float $roundedHours): float
    {
        $tierDuration = (float) $tier->duration_hours;
        $tierPrice = (float) $tier->price;
        $excessHours = $roundedHours - $tierDuration;

        if ($excessHours <= 0) {
            return $tierPrice;
        }

        $hourlyRate = $tierDuration > 0 ? $tierPrice / $tierDuration : 0;
        return round($tierPrice + $excessHours * $hourlyRate, 2);
    }
}
