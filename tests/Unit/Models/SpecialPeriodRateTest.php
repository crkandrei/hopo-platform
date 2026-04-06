<?php

namespace Tests\Unit\Models;

use App\Models\SpecialPeriodRate;
use PHPUnit\Framework\TestCase;

/**
 * Testează calculateTieredPrice() în izolare completă, fără bază de date.
 * Metoda calculează prețul pe baza tranșelor configurate (price_1h...price_4h)
 * și a tarifului de overflow pentru ce depășește ultima tranșă.
 */
class SpecialPeriodRateTest extends TestCase
{
    private function makeRate(array $prices, ?float $overflow = null): SpecialPeriodRate
    {
        $rate = new SpecialPeriodRate();
        $rate->pricing_mode = 'tiered';
        foreach ($prices as $hours => $price) {
            $rate->{'price_' . $hours . 'h'} = $price;
        }
        $rate->overflow_price_per_hour = $overflow;
        return $rate;
    }

    // =========================================================
    // Selecție tranșă corectă
    // =========================================================

    public function test_price_exactly_at_first_tier(): void
    {
        $rate = $this->makeRate([1 => 20.00, 2 => 35.00]);

        $this->assertEquals(20.00, $rate->calculateTieredPrice(1.0));
    }

    public function test_price_exactly_at_second_tier(): void
    {
        $rate = $this->makeRate([1 => 20.00, 2 => 35.00]);

        $this->assertEquals(35.00, $rate->calculateTieredPrice(2.0));
    }

    public function test_price_between_tiers_uses_incremental_rate(): void
    {
        $rate = $this->makeRate([1 => 20.00, 2 => 35.00, 3 => 45.00]);

        // 1.5h → bază 1h (20) + 0.5 × rata incrementală (35-20)/(2-1) = 27.50 RON
        $this->assertEquals(27.50, $rate->calculateTieredPrice(1.5));
    }

    public function test_price_just_above_tier_uses_incremental_rate(): void
    {
        $rate = $this->makeRate([1 => 20.00, 2 => 35.00]);

        // 1.01h → bază 1h (20) + 0.01 × rata incrementală (35-20)/1 = 20.15 RON
        $this->assertEquals(20.15, $rate->calculateTieredPrice(1.01));
    }

    // =========================================================
    // Overflow deasupra ultimei tranșe
    // =========================================================

    public function test_overflow_above_last_tier_charges_extra(): void
    {
        $rate = $this->makeRate([1 => 20.00, 2 => 35.00], overflow: 10.00);

        // 2.5h → deasupra ultimei tranșe (2h=35): 35 + 0.5×10 = 40 RON
        $this->assertEquals(40.00, $rate->calculateTieredPrice(2.5));
    }

    public function test_overflow_charges_multiple_extra_hours(): void
    {
        $rate = $this->makeRate([1 => 20.00, 2 => 35.00], overflow: 10.00);

        // 4h → 35 + 2×10 = 55 RON
        $this->assertEquals(55.00, $rate->calculateTieredPrice(4.0));
    }

    public function test_zero_overflow_no_extra_charge_above_last_tier(): void
    {
        $rate = $this->makeRate([1 => 20.00, 2 => 35.00], overflow: 0.00);

        // 3h → deasupra ultimei tranșe, overflow=0: 35 + 1×0 = 35 RON
        $this->assertEquals(35.00, $rate->calculateTieredPrice(3.0));
    }

    public function test_null_overflow_no_extra_charge_above_last_tier(): void
    {
        $rate = $this->makeRate([1 => 20.00, 2 => 35.00], overflow: null);

        // 3h → overflow=null tratată ca 0: 35 RON
        $this->assertEquals(35.00, $rate->calculateTieredPrice(3.0));
    }

    // =========================================================
    // Cazuri limită
    // =========================================================

    public function test_no_tiers_returns_zero(): void
    {
        $rate = new SpecialPeriodRate();
        $rate->pricing_mode = 'tiered';

        $this->assertEquals(0.00, $rate->calculateTieredPrice(2.0));
    }

    public function test_single_tier_configured_used_for_all_durations_below_it(): void
    {
        $rate = $this->makeRate([2 => 35.00], overflow: 10.00);

        // 1h → sub singura tranșă (2h): nicio tranșă ≤ 1h → returnează prima tranșă = 35 RON
        $this->assertEquals(35.00, $rate->calculateTieredPrice(1.0));
    }

    public function test_four_tiers_all_configured(): void
    {
        $rate = $this->makeRate([
            1 => 20.00,
            2 => 35.00,
            3 => 45.00,
            4 => 55.00,
        ], overflow: 12.00);

        $this->assertEquals(20.00, $rate->calculateTieredPrice(1.0));
        // 1.5h → bază 1h (20) + 0.5 × (35-20)/1 = 27.50 RON
        $this->assertEquals(27.50, $rate->calculateTieredPrice(1.5));
        $this->assertEquals(35.00, $rate->calculateTieredPrice(2.0));
        // 2.5h → bază 2h (35) + 0.5 × (45-35)/1 = 40.00 RON
        $this->assertEquals(40.00, $rate->calculateTieredPrice(2.5));
        $this->assertEquals(45.00, $rate->calculateTieredPrice(3.0));
        $this->assertEquals(55.00, $rate->calculateTieredPrice(4.0));
        $this->assertEquals(67.00, $rate->calculateTieredPrice(5.0)); // 55 + 1×12 = 67
    }

    public function test_half_hour_steps_overflow(): void
    {
        $rate = $this->makeRate([1 => 20.00, 2 => 35.00], overflow: 10.00);

        $this->assertEquals(40.00, $rate->calculateTieredPrice(2.5));  // 35 + 0.5×10 = 40
        $this->assertEquals(45.00, $rate->calculateTieredPrice(3.0));  // 35 + 1.0×10 = 45
        $this->assertEquals(50.00, $rate->calculateTieredPrice(3.5));  // 35 + 1.5×10 = 50
    }

    // =========================================================
    // Intrări invalide / limită
    // =========================================================

    /**
     * Comportament documentat: calculateTieredPrice(0) returnează prețul primei tranșe
     * deoarece 0 <= orice durată de tranșă. Aceasta nu apare în flux normal
     * (roundToHalfHour(0) = 0, dar PricingService returnează 0 înainte să ajungă aici
     * pentru sesiuni fără durată). Test documentează comportamentul actual.
     */
    public function test_zero_hours_returns_first_tier_price(): void
    {
        $rate = $this->makeRate([1 => 20.00, 2 => 35.00]);

        // 0h → 0 <= 1 → returnează prima tranșă
        $this->assertEquals(20.00, $rate->calculateTieredPrice(0.0));
    }

    public function test_tier_with_zero_price(): void
    {
        $rate = $this->makeRate([1 => 0.00, 2 => 35.00]);

        // 1h → tranșa 1h cu preț 0 → 0 RON
        $this->assertEquals(0.00, $rate->calculateTieredPrice(1.0));
    }

    public function test_very_large_duration_with_overflow(): void
    {
        $rate = $this->makeRate([1 => 20.00, 2 => 35.00], overflow: 10.00);

        // 10h → 35 + 8×10 = 115 RON
        $this->assertEquals(115.00, $rate->calculateTieredPrice(10.0));
    }
}
