<?php

namespace Tests\Unit\Services\Pricing;

use App\Services\Pricing\PricingResult;
use PHPUnit\Framework\TestCase;

class PricingResultTest extends TestCase
{
    // =========================================================
    // Construcție și acces la valori
    // =========================================================

    public function test_holds_price_and_rounded_hours(): void
    {
        $result = new PricingResult(price: 60.00, roundedHours: 2.0);

        $this->assertEquals(60.00, $result->price);
        $this->assertEquals(2.0, $result->roundedHours);
    }

    public function test_effective_hourly_rate_is_price_divided_by_rounded_hours(): void
    {
        $result = new PricingResult(price: 60.00, roundedHours: 2.0);

        $this->assertEquals(30.00, $result->effectiveHourlyRate);
    }

    public function test_effective_hourly_rate_rounds_to_two_decimals(): void
    {
        // 50 RON / 3h = 16.666... → 16.67
        $result = new PricingResult(price: 50.00, roundedHours: 3.0);

        $this->assertEquals(16.67, $result->effectiveHourlyRate);
    }

    public function test_effective_hourly_rate_is_zero_when_rounded_hours_is_zero(): void
    {
        $result = new PricingResult(price: 0.00, roundedHours: 0.0);

        $this->assertEquals(0.00, $result->effectiveHourlyRate);
    }

    public function test_zero_price_with_nonzero_hours_gives_zero_rate(): void
    {
        $result = new PricingResult(price: 0.00, roundedHours: 2.0);

        $this->assertEquals(0.00, $result->effectiveHourlyRate);
    }

    // =========================================================
    // Imutabilitate
    // =========================================================

    public function test_properties_are_readonly(): void
    {
        $result = new PricingResult(price: 40.00, roundedHours: 1.0);

        $this->expectException(\Error::class);

        $result->price = 99.00; // @phpstan-ignore-line
    }
}
