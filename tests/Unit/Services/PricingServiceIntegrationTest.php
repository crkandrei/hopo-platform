<?php

namespace Tests\Unit\Services;

use App\Models\Child;
use App\Models\Location;
use App\Models\PlaySession;
use App\Models\PricingTier;
use App\Models\SpecialPeriodRate;
use App\Services\PricingService;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Integration tests for PricingService::calculateAndSavePrice().
 * Each test exercises the full pipeline: strategy selection → price calculation → DB persistence.
 * DB required for location, tiers, and special periods.
 */
class PricingServiceIntegrationTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    private PricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(PricingService::class);
    }

    private function makeSession(Location $location, int $durationSeconds, array $extra = []): PlaySession
    {
        $startedAt = Carbon::parse('2024-01-15 10:00:00'); // Monday
        return PlaySession::factory()->create(array_merge([
            'location_id' => $location->id,
            'started_at'  => $startedAt,
            'ended_at'    => $startedAt->copy()->addSeconds($durationSeconds),
        ], $extra));
    }

    // =========================================================
    // Flat hourly — basic price + rate persistence
    // =========================================================

    public function test_flat_hourly_saves_correct_price(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);
        $session = $this->makeSession($location, 2 * 3600); // exactly 2h

        $this->service->calculateAndSavePrice($session);

        $session->refresh();
        $this->assertEquals('60.00', $session->calculated_price);
    }

    public function test_flat_hourly_saves_effective_hourly_rate(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);
        $session = $this->makeSession($location, 2 * 3600); // exactly 2h

        $this->service->calculateAndSavePrice($session);

        $session->refresh();
        $this->assertEquals('30.00', $session->price_per_hour_at_calculation);
    }

    public function test_flat_hourly_duration_rounds_up_to_first_hour(): void
    {
        // 40 minutes → rounded to 1h (first hour always billed as full hour)
        $location = Location::factory()->create(['price_per_hour' => 30.00]);
        $session = $this->makeSession($location, 40 * 60);

        $this->service->calculateAndSavePrice($session);

        $session->refresh();
        $this->assertEquals('30.00', $session->calculated_price);
    }

    public function test_flat_hourly_duration_rounds_fractional_hour(): void
    {
        // 1h 20min → 1.5h (15–45 min over boundary rounds to +0.5h) → 40 × 1.5 = 60
        $location = Location::factory()->create(['price_per_hour' => 40.00]);
        $session = $this->makeSession($location, (60 + 20) * 60);

        $this->service->calculateAndSavePrice($session);

        $session->refresh();
        $this->assertEquals('60.00', $session->calculated_price);
    }

    // =========================================================
    // Free and birthday sessions → price = 0
    // =========================================================

    public function test_free_session_saves_zero_price(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);
        $session = $this->makeSession($location, 2 * 3600, ['is_free' => true]);

        $this->service->calculateAndSavePrice($session);

        $session->refresh();
        $this->assertEquals('0.00', $session->calculated_price);
        $this->assertEquals('0.00', $session->price_per_hour_at_calculation);
    }

    public function test_birthday_session_saves_zero_price(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);
        $session = $this->makeSession($location, 2 * 3600, ['session_type' => 'birthday']);

        $this->service->calculateAndSavePrice($session);

        $session->refresh();
        $this->assertEquals('0.00', $session->calculated_price);
        $this->assertEquals('0.00', $session->price_per_hour_at_calculation);
    }

    // =========================================================
    // Tiered pricing — tier lookup + excess billing
    // =========================================================

    public function test_tiered_exact_tier_saves_tier_price(): void
    {
        $location = Location::factory()->create([
            'price_per_hour' => 30.00,
            'pricing_mode'   => 'tiered',
        ]);
        // Monday tiers (day_of_week = 0 in system)
        PricingTier::create(['location_id' => $location->id, 'day_of_week' => 0, 'duration_hours' => 1.0, 'price' => 20.00]);
        PricingTier::create(['location_id' => $location->id, 'day_of_week' => 0, 'duration_hours' => 2.0, 'price' => 35.00]);

        $session = $this->makeSession($location, 2 * 3600); // exactly 2h → tier 35.00

        $this->service->calculateAndSavePrice($session);

        $session->refresh();
        $this->assertEquals('35.00', $session->calculated_price);
    }

    public function test_tiered_saves_effective_hourly_rate(): void
    {
        $location = Location::factory()->create([
            'price_per_hour' => 30.00,
            'pricing_mode'   => 'tiered',
        ]);
        PricingTier::create(['location_id' => $location->id, 'day_of_week' => 0, 'duration_hours' => 2.0, 'price' => 40.00]);

        $session = $this->makeSession($location, 2 * 3600); // 2h at 40 → 20 RON/h effective

        $this->service->calculateAndSavePrice($session);

        $session->refresh();
        $this->assertEquals('40.00', $session->calculated_price);
        $this->assertEquals('20.00', $session->price_per_hour_at_calculation);
    }

    public function test_tiered_fallback_to_flat_when_no_tiers(): void
    {
        $location = Location::factory()->create([
            'price_per_hour' => 25.00,
            'pricing_mode'   => 'tiered',
        ]);
        // No tiers → falls back to flat hourly

        $session = $this->makeSession($location, 2 * 3600);

        $this->service->calculateAndSavePrice($session);

        $session->refresh();
        $this->assertEquals('50.00', $session->calculated_price);
    }

    // =========================================================
    // Special period — overrides flat rate
    // =========================================================

    public function test_special_period_flat_rate_overrides_location_default(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);
        // Special flat rate on 2024-01-15
        SpecialPeriodRate::create([
            'location_id' => $location->id,
            'name'        => 'Test period',
            'start_date'  => '2024-01-15',
            'end_date'    => '2024-01-15',
            'hourly_rate' => 50.00,
        ]);

        $session = $this->makeSession($location, 2 * 3600); // session on 2024-01-15

        $this->service->calculateAndSavePrice($session);

        $session->refresh();
        $this->assertEquals('100.00', $session->calculated_price);
    }

    // =========================================================
    // Return value consistency
    // =========================================================

    public function test_returns_updated_session_instance(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 20.00]);
        $session = $this->makeSession($location, 3600);

        $result = $this->service->calculateAndSavePrice($session);

        $this->assertInstanceOf(PlaySession::class, $result);
        $this->assertEquals('20.00', $result->calculated_price);
    }

    public function test_calculate_session_price_matches_calculated_and_save_price(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 20.00]);
        $session = $this->makeSession($location, 2 * 3600);

        $calculatedPrice = $this->service->calculateSessionPrice($session);
        $this->service->calculateAndSavePrice($session);
        $session->refresh();

        $this->assertEquals($calculatedPrice, (float) $session->calculated_price);
    }
}
