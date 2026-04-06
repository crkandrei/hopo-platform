<?php

namespace App\Services\Pricing\Strategies;

use App\Models\Location;
use App\Models\PricingTier;
use App\Services\Pricing\Contracts\PricingStrategyInterface;
use App\Services\Pricing\DurationRounder;
use App\Services\Pricing\PricingResult;
use App\Services\Pricing\Resolvers\SpecialPeriodRateResolver;
use Carbon\Carbon;

class TieredDurationStrategy implements PricingStrategyInterface
{
    public function __construct(
        private FlatHourlyStrategy $flatHourlyStrategy,
        private SpecialPeriodRateResolver $specialPeriodRateResolver,
        private DurationRounder $durationRounder
    ) {
    }

    /**
     * Calculate price from tiers: round duration up to next tier, then apply tier price or overflow.
     * If special period applies: use period's tiered prices when mode is tiered, else delegate to flat.
     */
    public function calculateResult(Location $location, float $durationInHours, $date = null): PricingResult
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $specialPeriod = $this->specialPeriodRateResolver->find($location, $checkDate);
        $roundedHours = $this->durationRounder->round($durationInHours);

        if ($specialPeriod) {
            if ($specialPeriod->isTiered()) {
                $price = $specialPeriod->calculateTieredPrice($roundedHours);
                return new PricingResult($price, $roundedHours);
            }
            return $this->flatHourlyStrategy->calculateResult($location, $durationInHours, $date);
        }

        $systemDayOfWeek = $this->toSystemDayOfWeek($checkDate);
        $tiers = $this->getTiersForDay($location, $systemDayOfWeek);

        if ($tiers->isEmpty()) {
            return $this->flatHourlyStrategy->calculateResult($location, $durationInHours, $date);
        }

        $tier = $this->findBaseTier($tiers, $roundedHours);

        if ($tier) {
            $nextTier = $this->findNextTier($tiers, $tier);
            $price = $this->calculateTierPrice($tier, $nextTier, $roundedHours);
            return new PricingResult($price, $roundedHours);
        }

        return $this->flatHourlyStrategy->calculateResult($location, $durationInHours, $date);
    }

    public function calculatePrice(Location $location, float $durationInHours, $date = null): float
    {
        return $this->calculateResult($location, $durationInHours, $date)->price;
    }

    public function getHourlyRate(Location $location, $date = null): float
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        if ($this->specialPeriodRateResolver->find($location, $checkDate)) {
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
     * Find smallest tier above the base tier (next tier up).
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, PricingTier> $tiers
     */
    private function findNextTier(\Illuminate\Database\Eloquent\Collection $tiers, PricingTier $baseTier): ?PricingTier
    {
        foreach ($tiers as $tier) {
            if ((float) $tier->duration_hours > (float) $baseTier->duration_hours) {
                return $tier;
            }
        }
        return null;
    }

    /**
     * Calculate price using base tier + excess billed at the incremental rate toward the next tier.
     * If no next tier exists (beyond last tier), excess is billed at the last tier's average hourly rate.
     * e.g. 1.5h with 1h=40 and 2h=60: 40 + 0.5 * (60-40)/(2-1) = 50 RON
     * e.g. 3.5h with 3h=75 (last tier): 75 + 0.5 * (75/3) = 87.5 RON
     */
    private function calculateTierPrice(PricingTier $tier, ?PricingTier $nextTier, float $roundedHours): float
    {
        $tierDuration = (float) $tier->duration_hours;
        $tierPrice    = (float) $tier->price;
        $excessHours  = $roundedHours - $tierDuration;

        if ($excessHours <= 0) {
            return $tierPrice;
        }

        if ($nextTier !== null) {
            $nextDuration = (float) $nextTier->duration_hours;
            $nextPrice    = (float) $nextTier->price;
            $hourlyRate   = ($nextPrice - $tierPrice) / ($nextDuration - $tierDuration);
        } else {
            $hourlyRate = $tierDuration > 0 ? $tierPrice / $tierDuration : 0;
        }

        return round($tierPrice + $excessHours * $hourlyRate, 2);
    }
}
