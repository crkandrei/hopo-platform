<?php

namespace App\Services\Pricing;

class PricingResult
{
    public readonly float $effectiveHourlyRate;

    public function __construct(
        public readonly float $price,
        public readonly float $roundedHours,
    ) {
        $this->effectiveHourlyRate = $roundedHours > 0
            ? round($price / $roundedHours, 2)
            : 0.00;
    }
}
