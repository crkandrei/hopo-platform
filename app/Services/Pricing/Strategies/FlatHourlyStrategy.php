<?php

namespace App\Services\Pricing\Strategies;

use App\Models\Location;
use App\Models\SpecialPeriodRate;
use App\Services\Pricing\Contracts\PricingStrategyInterface;
use Carbon\Carbon;

class FlatHourlyStrategy implements PricingStrategyInterface
{
    /**
     * Calculate price as rounded hours × hourly rate, or from special period tiers when applicable.
     */
    public function calculatePrice(Location $location, float $durationInHours, $date = null): float
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $specialPeriodRate = $this->getSpecialPeriodForDate($location, $checkDate);

        if ($specialPeriodRate) {
            if ($specialPeriodRate->isTiered()) {
                $roundedHours = $this->roundToHalfHour($durationInHours);
                return $specialPeriodRate->calculateTieredPrice($roundedHours);
            }
            $hourlyRate = (float) $specialPeriodRate->hourly_rate;
            if ($hourlyRate <= 0) {
                return 0.00;
            }
            $roundedHours = $this->roundToHalfHour($durationInHours);
            return round($roundedHours * $hourlyRate, 2);
        }

        $hourlyRate = $this->getHourlyRate($location, $date);
        if ($hourlyRate <= 0) {
            return 0.00;
        }
        $roundedHours = $this->roundToHalfHour($durationInHours);
        return round($roundedHours * $hourlyRate, 2);
    }

    /**
     * Priority: Special Period Rate > Weekly Rate > Default price_per_hour
     */
    public function getHourlyRate(Location $location, $date = null): float
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();

        $specialPeriodRate = $this->getSpecialPeriodForDate($location, $checkDate);

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
        if ($hours <= 0) {
            return 0.0;
        }
        if ($hours <= 1.0) {
            return 1.0;
        }
        $completeHoursAfterFirst = floor($hours - 1.0);
        $remainingMinutes = (($hours - 1.0) - $completeHoursAfterFirst) * 60;
        $totalHours = 1.0 + $completeHoursAfterFirst;
        if ($remainingMinutes < 15) {
            // no extra
        } elseif ($remainingMinutes <= 45) {
            $totalHours += 0.5;
        } else {
            $totalHours += 1.0;
        }
        return $totalHours;
    }

    /**
     * Get the special period rate applicable for a location on the given date, if any.
     */
    public function getSpecialPeriodForDate(Location $location, Carbon $date): ?SpecialPeriodRate
    {
        return SpecialPeriodRate::where('location_id', $location->id)
            ->where('start_date', '<=', $date->format('Y-m-d'))
            ->where('end_date', '>=', $date->format('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
