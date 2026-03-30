<?php

namespace App\Services\Pricing\Strategies;

use App\Models\Location;
use App\Services\Pricing\Contracts\PricingStrategyInterface;
use App\Services\Pricing\DurationRounder;
use App\Services\Pricing\PricingResult;
use App\Services\Pricing\Resolvers\SpecialPeriodRateResolver;
use Carbon\Carbon;

class FlatHourlyStrategy implements PricingStrategyInterface
{
    public function __construct(
        private DurationRounder $durationRounder,
        private SpecialPeriodRateResolver $specialPeriodRateResolver
    ) {
    }

    public function calculateResult(Location $location, float $durationInHours, $date = null): PricingResult
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $specialPeriodRate = $this->specialPeriodRateResolver->find($location, $checkDate);
        $roundedHours = $this->roundToHalfHour($durationInHours);

        if ($specialPeriodRate) {
            if ($specialPeriodRate->isTiered()) {
                $price = $specialPeriodRate->calculateTieredPrice($roundedHours);
                return new PricingResult($price, $roundedHours);
            }
            $hourlyRate = (float) $specialPeriodRate->hourly_rate;
            $price = $hourlyRate > 0 ? round($roundedHours * $hourlyRate, 2) : 0.00;
            return new PricingResult($price, $roundedHours);
        }

        $hourlyRate = $this->getHourlyRate($location, $date);
        $price = $hourlyRate > 0 ? round($roundedHours * $hourlyRate, 2) : 0.00;
        return new PricingResult($price, $roundedHours);
    }

    /**
     * Calculate price as rounded hours × hourly rate, or from special period tiers when applicable.
     */
    public function calculatePrice(Location $location, float $durationInHours, $date = null): float
    {
        return $this->calculateResult($location, $durationInHours, $date)->price;
    }

    /**
     * Priority: Special Period Rate > Weekly Rate > Default price_per_hour
     */
    public function getHourlyRate(Location $location, $date = null): float
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();

        $specialPeriodRate = $this->specialPeriodRateResolver->find($location, $checkDate);

        if ($specialPeriodRate) {
            if ($specialPeriodRate->isTiered()) {
                $tiers = $specialPeriodRate->getTierPrices();
                if (!empty($tiers)) {
                    $overflow = $specialPeriodRate->overflow_price_per_hour;
                    if ($overflow !== null && $overflow > 0) {
                        return (float) $overflow;
                    }
                    return (float) ($tiers[1] ?? $tiers[array_key_first($tiers)] ?? 0);
                }
            }
            return (float) $specialPeriodRate->hourly_rate;
        }

        $dayOfWeek = $checkDate->dayOfWeek;
        $systemDayOfWeek = $dayOfWeek === 0 ? 6 : $dayOfWeek - 1;

        $weeklyRate = $location->weeklyRates()
            ->where('day_of_week', $systemDayOfWeek)
            ->first();

        if ($weeklyRate) {
            return (float) $weeklyRate->hourly_rate;
        }

        return (float) ($location->price_per_hour ?? 0.00);
    }

    public function getApplicableRates(Location $location, $date = null): array
    {
        return [
            'hourly_rate' => $this->getHourlyRate($location, $date),
            'mode' => 'flat_hourly',
        ];
    }

    /**
     * Round duration according to billing rules (first hour always 1h; then 15/45 min rules).
     */
    public function roundToHalfHour(float $hours): float
    {
        return $this->durationRounder->round($hours);
    }
}
