<?php

namespace Tests\Unit\Services\Pricing\Resolvers;

use App\Models\Location;
use App\Models\SpecialPeriodRate;
use App\Services\Pricing\Resolvers\SpecialPeriodRateResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SpecialPeriodRateResolverTest extends TestCase
{
    private SpecialPeriodRateResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(SpecialPeriodRateResolver::class);
    }

    // =========================================================
    // Returnare perioadă aplicabilă
    // =========================================================

    public function test_returns_special_period_rate_when_date_is_in_range(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        $period = SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Vacanta',
            'start_date'   => '2024-01-10',
            'end_date'     => '2024-01-20',
            'hourly_rate'  => 50.00,
            'pricing_mode' => 'flat_hourly',
        ]);

        $result = $this->resolver->find($location, Carbon::parse('2024-01-15'));

        $this->assertNotNull($result);
        $this->assertEquals($period->id, $result->id);
    }

    public function test_returns_null_when_no_special_periods_exist(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        $result = $this->resolver->find($location, Carbon::parse('2024-01-15'));

        $this->assertNull($result);
    }

    public function test_returns_null_when_date_before_range(): void
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

        $result = $this->resolver->find($location, Carbon::parse('2024-01-15'));

        $this->assertNull($result);
    }

    public function test_returns_null_when_date_after_range(): void
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

        $result = $this->resolver->find($location, Carbon::parse('2024-01-15'));

        $this->assertNull($result);
    }

    // =========================================================
    // Limite exacte (prima și ultima zi inclusă)
    // =========================================================

    public function test_applies_on_start_date(): void
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

        $result = $this->resolver->find($location, Carbon::parse('2024-01-15'));

        $this->assertNotNull($result);
    }

    public function test_applies_on_end_date(): void
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

        $result = $this->resolver->find($location, Carbon::parse('2024-01-15'));

        $this->assertNotNull($result);
    }

    // =========================================================
    // Perioade suprapuse — câștigă cea mai recent creată
    // =========================================================

    public function test_overlapping_periods_most_recently_created_wins(): void
    {
        $location = Location::factory()->create(['price_per_hour' => 30.00]);

        $older = SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Perioada veche',
            'start_date'   => '2024-01-10',
            'end_date'     => '2024-01-20',
            'hourly_rate'  => 40.00,
            'pricing_mode' => 'flat_hourly',
        ]);
        DB::table('special_period_rates')
            ->where('id', $older->id)
            ->update(['created_at' => now()->subMinutes(5)]);

        $newer = SpecialPeriodRate::create([
            'location_id'  => $location->id,
            'name'         => 'Perioada noua',
            'start_date'   => '2024-01-12',
            'end_date'     => '2024-01-18',
            'hourly_rate'  => 60.00,
            'pricing_mode' => 'flat_hourly',
        ]);

        $result = $this->resolver->find($location, Carbon::parse('2024-01-15'));

        $this->assertNotNull($result);
        $this->assertEquals($newer->id, $result->id);
    }

    // =========================================================
    // Izolare per locație
    // =========================================================

    public function test_period_from_other_location_is_not_returned(): void
    {
        $locationA = Location::factory()->create(['price_per_hour' => 30.00]);
        $locationB = Location::factory()->create(['price_per_hour' => 30.00]);

        SpecialPeriodRate::create([
            'location_id'  => $locationB->id,
            'name'         => 'Vacanta B',
            'start_date'   => '2024-01-10',
            'end_date'     => '2024-01-20',
            'hourly_rate'  => 80.00,
            'pricing_mode' => 'flat_hourly',
        ]);

        $result = $this->resolver->find($locationA, Carbon::parse('2024-01-15'));

        $this->assertNull($result);
    }
}
