<?php

namespace App\Services\Pricing;

class DurationRounder
{
    /**
     * Rotunjește durata conform regulilor de facturare:
     *  - Durată zero/negativă → 0h
     *  - Orice durată pozitivă: rotunjire în sus la cel mai apropiat multiplu de 15 minute
     *  - Minim 1h (orice durată ≤ 1h → 1h)
     */
    public function round(float $hours): float
    {
        if ($hours <= 0) {
            return 0.0;
        }

        $totalMinutes  = $hours * 60;
        $roundedMinutes = ceil($totalMinutes / 15) * 15;
        $roundedHours   = $roundedMinutes / 60;

        return max(1.0, $roundedHours);
    }
}
