<?php

namespace Tests\Unit\Services\Pricing;

use App\Models\Location;
use App\Models\PricingTier;
use App\Models\SpecialPeriodRate;
use App\Services\Pricing\Strategies\TieredDurationStrategy;
use Carbon\Carbon;
use Tests\TestCase;

class TieredDurationStrategyTest extends TestCase
{
    private TieredDurationStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = app(TieredDurationStrategy::class);
    }

    private function makeTieredLocation(float $defaultRate = 30.00, ?float $overflow = null): Location
    {
        return Location::factory()->create([
            'price_per_hour'          => $defaultRate,
            'pricing_mode'            => 'tiered',
            'overflow_price_per_hour' => $overflow,
        ]);
    }

    private function addTier(Location $location, float $durationHours, float $price, int $dayOfWeek = 0): void
    {
        PricingTier::create([
            'location_id'    => $location->id,
            'day_of_week'    => $dayOfWeek,
            'duration_hours' => $durationHours,
            'price'          => $price,
        ]);
    }

    // =========================================================
    // Matching tranșă exactă și cu exces
    // =========================================================

    public function test_exact_tier_match_returns_tier_price(): void
    {
        $location = $this->makeTieredLocation();
        $this->addTier($location, 1.0, 20.00);
        $this->addTier($location, 2.0, 35.00);

        // Exact 2h → tranșa 2h = 35 RON
        $price = $this->strategy->calculatePrice($location, 2.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(35.00, $price);
    }

    public function test_duration_between_tiers_charges_excess_above_base_tier(): void
    {
        $location = $this->makeTieredLocation();
        $this->addTier($location, 1.0, 20.00);
        $this->addTier($location, 2.0, 35.00);

        // 1.5h → bază = tranșa 1h (20 RON) + 0.5h exces × (20/1) = 30 RON
        $price = $this->strategy->calculatePrice($location, 1.5, Carbon::parse('2024-01-15'));

        $this->assertEquals(30.00, $price);
    }

    public function test_duration_above_all_tiers_charges_excess_at_highest_tier_rate(): void
    {
        $location = $this->makeTieredLocation();
        $this->addTier($location, 1.0, 20.00);
        $this->addTier($location, 2.0, 35.00);

        // 2.5h → bază = tranșa 2h (35) + 0.5h × (35/2 = 17.5) = 43.75 RON
        $price = $this->strategy->calculatePrice($location, 2.5, Carbon::parse('2024-01-15'));

        $this->assertEquals(43.75, $price);
    }

    public function test_exactly_one_hour_with_one_hour_tier(): void
    {
        $location = $this->makeTieredLocation();
        $this->addTier($location, 1.0, 20.00);
        $this->addTier($location, 2.0, 35.00);
        $this->addTier($location, 3.0, 45.00);

        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(20.00, $price);
    }

    // =========================================================
    // Fallback la flat hourly
    // =========================================================

    public function test_duration_below_lowest_tier_falls_back_to_flat_hourly(): void
    {
        $location = $this->makeTieredLocation(defaultRate: 30.00);
        $this->addTier($location, 2.0, 35.00); // doar tranșa de 2h

        // 1h → nicio tranșă ≤ 1h → fallback flat: 1h × 30 = 30 RON
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(30.00, $price);
    }

    public function test_no_tiers_configured_falls_back_to_flat_hourly(): void
    {
        $location = $this->makeTieredLocation(defaultRate: 30.00);
        // fără nicio tranșă

        // Fallback flat: 2h × 30 = 60 RON
        $price = $this->strategy->calculatePrice($location, 2.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(60.00, $price);
    }

    public function test_tiers_for_different_day_do_not_apply(): void
    {
        $location = $this->makeTieredLocation(defaultRate: 30.00);

        // Tranșe doar pentru Vineri (system day 4), nu Luni
        $this->addTier($location, 1.0, 20.00, dayOfWeek: 4);
        $this->addTier($location, 2.0, 35.00, dayOfWeek: 4);

        // Luni 2024-01-15 → nicio tranșă pentru Luni → fallback flat: 2h × 30 = 60
        $price = $this->strategy->calculatePrice($location, 2.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(60.00, $price);
    }

    // =========================================================
    // Zile diferite au tranșe independente
    // =========================================================

    public function test_different_days_have_independent_tier_prices(): void
    {
        $location = $this->makeTieredLocation();
        $this->addTier($location, 2.0, 35.00, dayOfWeek: 0); // Luni
        $this->addTier($location, 2.0, 50.00, dayOfWeek: 4); // Vineri

        $mondayPrice = $this->strategy->calculatePrice($location, 2.0, Carbon::parse('2024-01-15')); // Luni
        $fridayPrice = $this->strategy->calculatePrice($location, 2.0, Carbon::parse('2024-01-19')); // Vineri

        $this->assertEquals(35.00, $mondayPrice);
        $this->assertEquals(50.00, $fridayPrice);
    }

    // =========================================================
    // Perioadă specială override
    // =========================================================

    public function test_special_period_flat_overrides_daily_tiers(): void
    {
        $location = $this->makeTieredLocation(defaultRate: 30.00);
        $this->addTier($location, 2.0, 35.00); // tranșa Luni

        SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Sarbatoare',
            'start_date'   => '2024-01-10',
            'end_date'     => '2024-01-20',
            'hourly_rate'  => 60.00,
            'pricing_mode' => 'flat_hourly',
        ]);

        // Perioadă specială flat câștigă: 2h × 60 = 120 RON (nu 35 din tranșă)
        $price = $this->strategy->calculatePrice($location, 2.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(120.00, $price);
    }

    public function test_special_period_tiered_uses_period_tiers_not_daily_tiers(): void
    {
        $location = $this->makeTieredLocation();
        $this->addTier($location, 2.0, 35.00); // tranșa Luni: 2h=35

        SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Vacanta',
            'start_date'   => '2024-01-10',
            'end_date'     => '2024-01-20',
            'hourly_rate'  => 0.00,
            'pricing_mode' => 'tiered',
            'price_1h'     => 25.00,
            'price_2h'     => 45.00,
            'overflow_price_per_hour' => 15.00,
        ]);

        // Perioadă specială tiered câștigă: 2h → 45 RON (nu 35 din tranșa zilnică)
        $price = $this->strategy->calculatePrice($location, 2.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(45.00, $price);
    }

    // =========================================================
    // Rotunjire aplicată înainte de lookup tranșă
    // =========================================================

    public function test_duration_is_rounded_before_tier_lookup(): void
    {
        $location = $this->makeTieredLocation();
        $this->addTier($location, 1.0, 20.00);
        $this->addTier($location, 2.0, 35.00);

        // 1h 20min → rotunjit 1.5h → bază tranșa 1h (20) + 0.5 × 20 = 30 RON
        $price = $this->strategy->calculatePrice($location, 1.0 + 20.0 / 60, Carbon::parse('2024-01-15'));

        $this->assertEquals(30.00, $price);
    }

    public function test_three_tiers_full_scenario(): void
    {
        $location = $this->makeTieredLocation();
        $this->addTier($location, 1.0, 20.00);
        $this->addTier($location, 2.0, 35.00);
        $this->addTier($location, 3.0, 45.00);

        $this->assertEquals(20.00, $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15')));
        $this->assertEquals(35.00, $this->strategy->calculatePrice($location, 2.0, Carbon::parse('2024-01-15')));
        $this->assertEquals(45.00, $this->strategy->calculatePrice($location, 3.0, Carbon::parse('2024-01-15')));

        // 3.5h → bază tranșa 3h (45) + 0.5 × (45/3 = 15) = 52.5 RON
        $this->assertEquals(52.50, $this->strategy->calculatePrice($location, 3.5, Carbon::parse('2024-01-15')));
    }

    // =========================================================
    // Durate invalide / zero
    // =========================================================

    public function test_zero_duration_returns_zero_price(): void
    {
        $location = $this->makeTieredLocation(defaultRate: 30.00);
        $this->addTier($location, 1.0, 20.00);
        $this->addTier($location, 2.0, 35.00);

        // 0 ore → roundToHalfHour(0) = 0 → nicio tranșă ≤ 0 → fallback flat → 0 × 30 = 0
        $price = $this->strategy->calculatePrice($location, 0.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(0.00, $price);
    }

    public function test_location_with_no_price_and_no_tiers_returns_zero(): void
    {
        $location = $this->makeTieredLocation(defaultRate: 0.00);
        // fără tranșe, fără tarif → 0 RON, nu excepție

        $price = $this->strategy->calculatePrice($location, 2.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(0.00, $price);
    }

    // =========================================================
    // Limita tranșei — tranșă cu preț 0
    // =========================================================

    public function test_tier_with_zero_price_returns_zero(): void
    {
        $location = $this->makeTieredLocation();
        $this->addTier($location, 1.0, 0.00); // tranșa de 1h configurată cu preț 0
        $this->addTier($location, 2.0, 35.00);

        // 1h → tranșa de 1h → 0 RON
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(0.00, $price);
    }
}
