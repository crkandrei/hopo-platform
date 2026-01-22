<?php

namespace App\Services;

use App\Models\PlaySession;
use App\Models\Location;
use App\Models\SpecialPeriodRate;
use Carbon\Carbon;

class PricingService
{
    /**
     * Calculate the price for a play session
     * 
     * @param PlaySession $session
     * @return float The calculated price in RON
     */
    public function calculateSessionPrice(PlaySession $session): float
    {
        $location = $session->location;
        if (!$location) {
            return 0.00;
        }

        $hourlyRate = $this->getHourlyRate($location, $session->started_at);
        if ($hourlyRate <= 0) {
            return 0.00;
        }

        $durationInHours = $this->getDurationInHours($session);
        $roundedHours = $this->roundToHalfHour($durationInHours);

        return round($roundedHours * $hourlyRate, 2);
    }

    /**
     * Get the hourly rate for a location
     * Priority: Special Period Rate > Weekly Rate > Default price_per_hour
     * 
     * @param Location $location
     * @param Carbon|null $date Date to check for special period and day of week. If null, uses current date.
     * @return float The hourly rate in RON
     */
    public function getHourlyRate(Location $location, $date = null): float
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();

        // 1. Check for special period rate first
        $specialPeriodRate = SpecialPeriodRate::where('location_id', $location->id)
            ->where('start_date', '<=', $checkDate->format('Y-m-d'))
            ->where('end_date', '>=', $checkDate->format('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->first();

        if ($specialPeriodRate) {
            return (float) $specialPeriodRate->hourly_rate;
        }

        // 2. Check for weekly rate
        // Carbon: 0=Sunday, 1=Monday, ..., 6=Saturday
        // Our system: 0=Monday, 1=Tuesday, ..., 6=Sunday
        $dayOfWeek = $checkDate->dayOfWeek; // Carbon: 0=Sunday, 6=Saturday
        $systemDayOfWeek = $dayOfWeek === 0 ? 6 : $dayOfWeek - 1; // Convert to our system (0=Monday)

        $weeklyRate = $location->weeklyRates()
            ->where('day_of_week', $systemDayOfWeek)
            ->first();

        if ($weeklyRate) {
            return (float) $weeklyRate->hourly_rate;
        }

        // 3. Fallback to default price_per_hour
        return (float) $location->price_per_hour ?? 0.00;
    }

    /**
     * Get the effective duration of a session in hours
     * 
     * @param PlaySession $session
     * @return float Duration in hours
     */
    public function getDurationInHours(PlaySession $session): float
    {
        $seconds = $session->getEffectiveDurationSeconds();
        return $seconds / 3600; // Convert seconds to hours
    }

    /**
     * Round duration according to pricing rules:
     * - First hour: always charged as 1 full hour (regardless of actual duration)
     * - After first hour: specific rounding rules apply for each additional hour and remaining minutes
     * 
     * Rules:
     * - Duration ≤ 1 hour → 1.0 hours (always)
     * - Duration > 1 hour:
     *   - First hour: always 1.0 hours
     *   - Each complete hour after the first: add 1.0 hours
     *   - Remaining minutes after complete hours:
     *     - < 15 minutes → round down (no additional charge)
     *     - ≥ 15 minutes and ≤ 45 minutes → add 0.5 hours
     *     - > 45 minutes → add 1.0 hours
     * 
     * Examples:
     * - 0.17 hours (10 min) -> 1.0 hours (first hour always full)
     * - 0.67 hours (40 min) -> 1.0 hours (first hour always full)
     * - 1.17 hours (1h 10min) -> 1.0 hours (10 min < 15 min, rounded down)
     * - 1.25 hours (1h 15min) -> 1.5 hours (15 min ≥ 15 min, add 0.5 hours)
     * - 1.33 hours (1h 20min) -> 1.5 hours (20 min between 15-45 min, add 0.5 hours)
     * - 1.5 hours (1h 30min) -> 1.5 hours (30 min between 15-45 min, add 0.5 hours)
     * - 1.75 hours (1h 45min) -> 1.5 hours (45 min = 45 min, add 0.5 hours)
     * - 1.77 hours (1h 46min) -> 2.0 hours (46 min ≥ 45 min, add 1 hour)
     * - 2.0 hours (2h 0min) -> 2.0 hours (1 + 1 complete hours)
     * - 2.17 hours (2h 10min) -> 2.0 hours (1 + 1 complete + 10min < 15min)
     * - 2.25 hours (2h 15min) -> 2.5 hours (1 + 1 complete + 15min ≥ 15min = +0.5h)
     * - 2.75 hours (2h 45min) -> 2.5 hours (1 + 1 complete + 45min = 45min = +0.5h)
     * - 2.77 hours (2h 46min) -> 3.0 hours (1 + 1 complete + 46min ≥ 45min = +1h)
     * - 8.0 hours (8h 0min) -> 8.0 hours (1 + 7 complete hours)
     * 
     * @param float $hours Duration in hours
     * @return float Rounded hours according to pricing rules
     */
    public function roundToHalfHour(float $hours): float
    {
        if ($hours <= 0) {
            return 0.0;
        }

        // First hour: always charged as 1 full hour
        if ($hours <= 1.0) {
            return 1.0;
        }

        // Calculate complete hours after the first hour
        $completeHoursAfterFirst = floor($hours - 1.0);
        
        // Calculate remaining minutes after complete hours
        $remainingMinutes = (($hours - 1.0) - $completeHoursAfterFirst) * 60;

        // Start with first hour (always 1.0)
        $totalHours = 1.0;
        
        // Add complete hours after the first
        $totalHours += $completeHoursAfterFirst;

        // Apply rounding rules for remaining minutes
        if ($remainingMinutes < 15) {
            // Less than 15 minutes: round down (no additional charge)
            // Total hours remain as calculated
        } elseif ($remainingMinutes <= 45) {
            // Between 15 and 45 minutes (inclusive): add 0.5 hours
            $totalHours += 0.5;
        } else {
            // More than 45 minutes: add 1 hour
            $totalHours += 1.0;
        }

        return $totalHours;
    }

    /**
     * Format price for display in RON
     * 
     * @param float $price
     * @return string Formatted price string (e.g., "25.50 RON")
     */
    public function formatPrice(float $price): string
    {
        return number_format($price, 2, '.', '') . ' RON';
    }

    /**
     * Calculate and save price for a session
     * This method calculates the price and saves it with the hourly rate at calculation time
     * 
     * @param PlaySession $session
     * @return PlaySession The updated session
     */
    public function calculateAndSavePrice(PlaySession $session): PlaySession
    {
        $location = $session->location;
        if (!$location) {
            return $session;
        }

        $hourlyRate = $this->getHourlyRate($location, $session->started_at);
        $calculatedPrice = $this->calculateSessionPrice($session);

        $session->update([
            'calculated_price' => $calculatedPrice,
            'price_per_hour_at_calculation' => $hourlyRate,
        ]);

        return $session;
    }
}

