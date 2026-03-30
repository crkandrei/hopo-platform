<?php

namespace App\Services;

use App\Models\PlaySession;
use App\Models\Location;
use App\Services\Pricing\DurationRounder;
use App\Services\Pricing\PricingStrategyFactory;

class PricingService
{
    public function __construct(
        private PricingStrategyFactory $strategyFactory,
        private DurationRounder $durationRounder
    ) {
    }

    /**
     * Calculate the price for a play session
     *
     * @param PlaySession $session
     * @return float The calculated price in RON
     */
    public function calculateSessionPrice(PlaySession $session): float
    {
        if ($session->is_free || $session->session_type === 'birthday') {
            return 0.00;
        }

        $location = $session->location;
        if (!$location) {
            return 0.00;
        }

        $strategy = $this->strategyFactory->make($location);
        $durationInHours = $this->getDurationInHours($session);

        return $strategy->calculateResult($location, $durationInHours, $session->started_at)->price;
    }

    /**
     * Get the hourly rate for a location (for display and for storing at calculation time).
     * In tiered mode, returns effective rate when used after calculation; otherwise overflow or default.
     *
     * @param Location $location
     * @param \Carbon\Carbon|string|null $date
     * @return float The hourly rate in RON
     */
    public function getHourlyRate(Location $location, $date = null): float
    {
        $strategy = $this->strategyFactory->make($location);
        return $strategy->getHourlyRate($location, $date);
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
        return $seconds / 3600;
    }

    /**
     * Round duration according to pricing rules (first hour always 1h; then 15/45 min rules).
     *
     * @param float $hours Duration in hours
     * @return float Rounded hours according to pricing rules
     */
    public function roundToHalfHour(float $hours): float
    {
        return $this->durationRounder->round($hours);
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
     * Calculate and save price for a session (single strategy call).
     *
     * @param PlaySession $session
     * @return PlaySession The updated session
     */
    public function calculateAndSavePrice(PlaySession $session): PlaySession
    {
        if ($session->is_free || $session->session_type === 'birthday') {
            $session->update([
                'calculated_price' => 0,
                'price_per_hour_at_calculation' => 0,
            ]);
            return $session;
        }

        $location = $session->location;
        if (!$location) {
            return $session;
        }

        $strategy = $this->strategyFactory->make($location);
        $durationInHours = $this->getDurationInHours($session);
        $result = $strategy->calculateResult($location, $durationInHours, $session->started_at);

        $session->update([
            'calculated_price' => $result->price,
            'price_per_hour_at_calculation' => $result->effectiveHourlyRate,
        ]);

        return $session;
    }
}
