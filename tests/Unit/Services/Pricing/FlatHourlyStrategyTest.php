<?php

namespace Tests\Unit\Services\Pricing;

use App\Models\Location;
use App\Models\SpecialPeriodRate;
use App\Models\WeeklyRate;
use App\Services\Pricing\Strategies\FlatHourlyStrategy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FlatHourlyStrategyTest extends TestCase
{
    private FlatHourlyStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = app(FlatHourlyStrategy::class);
    }

    // =========================================================
    // Prioritatea tarifelor: special period > weekly rate > default
    // =========================================================

    public function test_uses_location_default_price_when_no_weekly_rate(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        // Luni 2024-01-15, fără tarif configurat pe zi
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(30.00, $price);
    }

    public function test_uses_weekly_rate_over_location_default(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        // Luni = system day 0
        WeeklyRate::create(['location_id' => $location->id, 'day_of_week' => 0, 'hourly_rate' => 50.00]);

        // Luni 2024-01-15 → tarif zilnic 50 în loc de default 30
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(50.00, $price);
    }

    public function test_weekly_rate_does_not_apply_for_different_day(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        // Tarif configurat doar pentru Vineri (system day 4)
        WeeklyRate::create(['location_id' => $location->id, 'day_of_week' => 4, 'hourly_rate' => 50.00]);

        // Luni → nu e Vineri → fallback la default 30
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(30.00, $price);
    }

    public function test_special_period_flat_rate_overrides_weekly_rate(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        WeeklyRate::create(['location_id' => $location->id, 'day_of_week' => 0, 'hourly_rate' => 50.00]);

        SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Vacanta test',
            'start_date'   => '2024-01-10',
            'end_date'     => '2024-01-20',
            'hourly_rate'  => 40.00,
            'pricing_mode' => 'flat_hourly',
        ]);

        // Perioadă specială (40) câștigă față de tarif zilnic (50) și default (30)
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(40.00, $price);
    }

    public function test_special_period_outside_date_range_does_not_apply(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Vacanta Februarie',
            'start_date'   => '2024-02-01',
            'end_date'     => '2024-02-28',
            'hourly_rate'  => 40.00,
            'pricing_mode' => 'flat_hourly',
        ]);

        // Data din Ianuarie, perioada specială e în Februarie → nu se aplică
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(30.00, $price);
    }

    // =========================================================
    // Calcul flat hourly: ore rotunjite × tarif
    // =========================================================

    public function test_flat_calculates_rounded_hours_times_rate(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 40.00]);

        // 1.5h rotunjite × 40 = 60 RON
        $price = $this->strategy->calculatePrice($location, 1.5, Carbon::parse('2024-01-15'));

        $this->assertEquals(60.00, $price);
    }

    public function test_flat_with_special_period_rate_calculates_correctly(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Sarbatoare',
            'start_date'   => '2024-01-10',
            'end_date'     => '2024-01-20',
            'hourly_rate'  => 50.00,
            'pricing_mode' => 'flat_hourly',
        ]);

        // 2h × 50 RON = 100 RON
        $price = $this->strategy->calculatePrice($location, 2.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(100.00, $price);
    }

    public function test_zero_hourly_rate_returns_zero_price(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 0.00]);

        $price = $this->strategy->calculatePrice($location, 2.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(0.00, $price);
    }

    // =========================================================
    // Perioadă specială cu modul tiered
    // =========================================================

    public function test_special_period_tiered_uses_period_tier_prices(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        SpecialPeriodRate::create([
            'location_id'           => $location->id,
            'name'                  => 'Vacanta Tiered',
            'start_date'            => '2024-01-10',
            'end_date'              => '2024-01-20',
            'hourly_rate'           => 0.00,
            'pricing_mode'          => 'tiered',
            'price_1h'              => 20.00,
            'price_2h'              => 35.00,
            'overflow_price_per_hour' => 10.00,
        ]);

        // 2h → tranșa 2h = 35 RON (nu 30 default)
        $price = $this->strategy->calculatePrice($location, 2.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(35.00, $price);
    }

    public function test_special_period_tiered_overflow_charges_correctly(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        SpecialPeriodRate::create([
            'location_id'           => $location->id,
            'name'                  => 'Vacanta Tiered',
            'start_date'            => '2024-01-10',
            'end_date'              => '2024-01-20',
            'hourly_rate'           => 0.00,
            'pricing_mode'          => 'tiered',
            'price_1h'              => 20.00,
            'price_2h'              => 35.00,
            'overflow_price_per_hour' => 10.00,
        ]);

        // 2.5h → 35 RON + 0.5h × 10 RON = 40 RON
        $price = $this->strategy->calculatePrice($location, 2.5, Carbon::parse('2024-01-15'));

        $this->assertEquals(40.00, $price);
    }

    // =========================================================
    // Mapare zi a săptămânii (Carbon → sistem)
    // =========================================================

    public function test_monday_maps_to_system_day_zero(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        WeeklyRate::create(['location_id' => $location->id, 'day_of_week' => 0, 'hourly_rate' => 60.00]);

        // 2024-01-15 = Luni
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(60.00, $price);
    }

    public function test_sunday_maps_to_system_day_six(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        WeeklyRate::create(['location_id' => $location->id, 'day_of_week' => 6, 'hourly_rate' => 70.00]);

        // 2024-01-21 = Duminică
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-21'));

        $this->assertEquals(70.00, $price);
    }

    public function test_friday_maps_to_system_day_four(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        WeeklyRate::create(['location_id' => $location->id, 'day_of_week' => 4, 'hourly_rate' => 55.00]);

        // 2024-01-19 = Vineri
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-19'));

        $this->assertEquals(55.00, $price);
    }

    // =========================================================
    // Durate invalide / zero
    // =========================================================

    public function test_zero_duration_returns_zero_price(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 50.00]);

        // 0 secunde de joacă → 0 ore → rotunjit 0 → preț 0
        $price = $this->strategy->calculatePrice($location, 0.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(0.00, $price);
    }

    public function test_location_with_zero_price_and_no_rates_returns_zero(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 0.00]);

        // Niciun tarif configurat nicăieri → 0 RON, nu excepție
        $price = $this->strategy->calculatePrice($location, 2.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(0.00, $price);
    }

    // =========================================================
    // Limite exacte ale perioadei speciale (prima și ultima zi)
    // =========================================================

    public function test_special_period_applies_on_start_date(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Vacanta',
            'start_date'   => '2024-01-15',
            'end_date'     => '2024-01-20',
            'hourly_rate'  => 50.00,
            'pricing_mode' => 'flat_hourly',
        ]);

        // Exact prima zi a perioadei → se aplică
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(50.00, $price);
    }

    public function test_special_period_applies_on_end_date(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Vacanta',
            'start_date'   => '2024-01-10',
            'end_date'     => '2024-01-15',
            'hourly_rate'  => 50.00,
            'pricing_mode' => 'flat_hourly',
        ]);

        // Exact ultima zi a perioadei → se aplică
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(50.00, $price);
    }

    public function test_special_period_does_not_apply_day_after_end_date(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Vacanta',
            'start_date'   => '2024-01-10',
            'end_date'     => '2024-01-14',
            'hourly_rate'  => 50.00,
            'pricing_mode' => 'flat_hourly',
        ]);

        // 2024-01-15 = ziua de după ultima zi a perioadei → nu se aplică
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(30.00, $price);
    }

    public function test_special_period_does_not_apply_day_before_start_date(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Vacanta',
            'start_date'   => '2024-01-16',
            'end_date'     => '2024-01-20',
            'hourly_rate'  => 50.00,
            'pricing_mode' => 'flat_hourly',
        ]);

        // 2024-01-15 = ziua înainte de start → nu se aplică
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(30.00, $price);
    }

    // =========================================================
    // Perioade speciale suprapuse — comportament documentat:
    // câștigă cea mai recent creată (orderBy created_at desc)
    // =========================================================

    public function test_overlapping_special_periods_most_recently_created_wins(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        // Creată prima — forțăm created_at mai vechi via DB direct (Eloquent ignoră created_at la update)
        $older = SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Perioada 1 (veche)',
            'start_date'   => '2024-01-10',
            'end_date'     => '2024-01-20',
            'hourly_rate'  => 40.00,
            'pricing_mode' => 'flat_hourly',
        ]);
        DB::table('special_period_rates')
            ->where('id', $older->id)
            ->update(['created_at' => now()->subMinutes(5)]);

        // Creată a doua — created_at mai recent
        SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Perioada 2 (recenta)',
            'start_date'   => '2024-01-12',
            'end_date'     => '2024-01-18',
            'hourly_rate'  => 60.00,
            'pricing_mode' => 'flat_hourly',
        ]);

        // Comportament actual documentat: câștigă cea mai recent creată (60 RON)
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(60.00, $price);
    }

    public function test_overlapping_special_periods_only_one_in_range_applies(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Perioada 1',
            'start_date'   => '2024-01-10',
            'end_date'     => '2024-01-14',
            'hourly_rate'  => 40.00,
            'pricing_mode' => 'flat_hourly',
        ]);

        SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Perioada 2',
            'start_date'   => '2024-01-16',
            'end_date'     => '2024-01-20',
            'hourly_rate'  => 60.00,
            'pricing_mode' => 'flat_hourly',
        ]);

        // 2024-01-15 cade între cele două perioade → nu intră în niciuna → default 30
        $price = $this->strategy->calculatePrice($location, 1.0, Carbon::parse('2024-01-15'));

        $this->assertEquals(30.00, $price);
    }
}
