<?php

namespace App\Services\Pricing;

class DurationRounder
{
    /**
     * Rotunjește durata conform regulilor de facturare:
     *  - Prima oră: minim 1h (orice durată ≤ 1h → 1h)
     *  - După prima oră, restul față de orele întregi:
     *      < 15 min  → ignorat
     *      15–45 min → +0.5h
     *      > 45 min  → +1h
     */
    public function round(float $hours): float
    {
        if ($hours <= 0) {
            return 0.0;
        }
        if ($hours <= 1.0) {
            return 1.0;
        }

        $completeHoursAfterFirst = floor($hours - 1.0);
        $remainingMinutes        = (($hours - 1.0) - $completeHoursAfterFirst) * 60;
        $totalHours              = 1.0 + $completeHoursAfterFirst;

        if ($remainingMinutes < 15) {
            // ignorat
        } elseif ($remainingMinutes <= 45) {
            $totalHours += 0.5;
        } else {
            $totalHours += 1.0;
        }

        return $totalHours;
    }
}
