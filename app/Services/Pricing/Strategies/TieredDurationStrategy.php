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
        $tier = $this->findMatchingTier($tiers, $roundedHours);

        if ($tier) {
            return (float) $tier->price;
        }

        return $this->calculateOverflowPrice($location, $tiers, $roundedHours);
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
     * Find smallest tier where duration_hours >= roundedHours (round up to next tier).
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, PricingTier> $tiers
     */
    private function findMatchingTier(\Illuminate\Database\Eloquent\Collection $tiers, float $roundedHours): ?PricingTier
    {
        foreach ($tiers as $tier) {
            if ((float) $tier->duration_hours >= $roundedHours) {
                return $tier;
            }
        }
        return null;
    }

    private function calculateOverflowPrice(Location $location, \Illuminate\Database\Eloquent\Collection $tiers, float $roundedHours): float
    {
        $lastTier = $tiers->sortByDesc('duration_hours')->first();
        $lastDuration = (float) $lastTier->duration_hours;
        $lastPrice = (float) $lastTier->price;
        $overflowHours = $roundedHours - $lastDuration;
        $overflowRate = $location->overflow_price_per_hour ? (float) $location->overflow_price_per_hour : 0.00;
        $overflowAmount = $overflowHours * $overflowRate;
        return round($lastPrice + $overflowAmount, 2);
    }
}
