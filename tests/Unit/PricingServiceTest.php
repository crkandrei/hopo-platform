<?php

namespace Tests\Unit;

use App\Models\PlaySession;
use App\Models\Location;
use App\Services\PricingService;
use Tests\TestCase;
use Mockery;

class PricingServiceTest extends TestCase
{
    protected PricingService $pricingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingService = new PricingService();
    }

    /**
     * Test că prima oră se taxează întotdeauna ca 1 oră completă
     */
    public function test_first_hour_always_charged_as_full_hour(): void
    {
        // 10 minute -> 1.0 ore
        $this->assertEquals(1.0, $this->pricingService->roundToHalfHour(10 / 60));
        
        // 20 minute -> 1.0 ore
        $this->assertEquals(1.0, $this->pricingService->roundToHalfHour(20 / 60));
        
        // 40 minute -> 1.0 ore
        $this->assertEquals(1.0, $this->pricingService->roundToHalfHour(40 / 60));
        
        // 59 minute -> 1.0 ore
        $this->assertEquals(1.0, $this->pricingService->roundToHalfHour(59 / 60));
        
        // Exact 1 oră -> 1.0 ore
        $this->assertEquals(1.0, $this->pricingService->roundToHalfHour(1.0));
    }

    /**
     * Test că timpul sub 15 minute peste prima oră se rotunjește în jos
     */
    public function test_under_15_minutes_over_first_hour_rounds_down(): void
    {
        // 1:05 (1h 5min) -> 1.0 ore
        $this->assertEquals(1.0, $this->pricingService->roundToHalfHour(1.0 + 5 / 60));
        
        // 1:10 (1h 10min) -> 1.0 ore
        $this->assertEquals(1.0, $this->pricingService->roundToHalfHour(1.0 + 10 / 60));
        
        // 1:14 (1h 14min) -> 1.0 ore
        $this->assertEquals(1.0, $this->pricingService->roundToHalfHour(1.0 + 14 / 60));
        
        // 1:14.99 (1h 14.99min) -> 1.0 ore
        $this->assertEquals(1.0, $this->pricingService->roundToHalfHour(1.0 + 14.99 / 60));
    }

    /**
     * Test că timpul între 15 și 44 minute (inclusiv) se taxează ca jumătate de oră
     */
    public function test_between_15_and_44_minutes_charges_half_hour(): void
    {
        // 1:15 (1h 15min) -> 1.5 ore
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour(1.0 + 15 / 60));
        
        // 1:20 (1h 20min) -> 1.5 ore
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour(1.0 + 20 / 60));
        
        // 1:25 (1h 25min) -> 1.5 ore
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour(1.0 + 25 / 60));
        
        // 1:30 (1h 30min) -> 1.5 ore
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour(1.0 + 30 / 60));
        
        // 1:40 (1h 40min) -> 1.5 ore
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour(1.0 + 40 / 60));
        
        // 1:44 (1h 44min) -> 1.5 ore
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour(1.0 + 44 / 60));
        
        // 1:44.99 (1h 44.99min) -> 1.5 ore
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour(1.0 + 44.99 / 60));
    }

    /**
     * Test că timpul de la 45 minute în sus se taxează ca o oră completă
     */
    public function test_45_minutes_and_over_charges_full_hour(): void
    {
        // 1:45 (1h 45min) -> 1.5 ore (45 min = prag, se rotunjește la 0.5h)
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour(1.0 + 45 / 60));
        
        // 1:46 (1h 46min) -> 2.0 ore (peste 45 min)
        $this->assertEquals(2.0, $this->pricingService->roundToHalfHour(1.0 + 46 / 60));
        
        // 1:50 (1h 50min) -> 2.0 ore
        $this->assertEquals(2.0, $this->pricingService->roundToHalfHour(1.0 + 50 / 60));
        
        // 1:59 (1h 59min) -> 2.0 ore
        $this->assertEquals(2.0, $this->pricingService->roundToHalfHour(1.0 + 59 / 60));
        
        // Exact 2 ore -> 2.0 ore (nu ar trebui să se întâmple în practică pentru că 1h + 60min = 2h)
        $this->assertEquals(2.0, $this->pricingService->roundToHalfHour(2.0));
    }

    /**
     * Test că durata zero sau negativă returnează 0
     */
    public function test_zero_or_negative_duration_returns_zero(): void
    {
        $this->assertEquals(0.0, $this->pricingService->roundToHalfHour(0.0));
        $this->assertEquals(0.0, $this->pricingService->roundToHalfHour(-1.0));
    }

    /**
     * Test calcul preț complet pentru o sesiune
     */
    public function test_calculate_session_price(): void
    {
        $location = new Location();
        $location->price_per_hour = 50.00;

        $session = Mockery::mock(PlaySession::class)->makePartial();
        $session->shouldAllowMockingProtectedMethods();
        $session->location = $location;
        $session->shouldReceive('getEffectiveDurationSeconds')
            ->andReturn(3600); // 1 oră exactă

        $price = $this->pricingService->calculateSessionPrice($session);
        
        // 1 oră * 50 RON = 50 RON
        $this->assertEquals(50.00, $price);
    }

    /**
     * Test calcul preț pentru sesiune de 1:15 (1.5 ore)
     */
    public function test_calculate_session_price_one_hour_fifteen_minutes(): void
    {
        $location = new Location();
        $location->price_per_hour = 50.00;

        $session = Mockery::mock(PlaySession::class)->makePartial();
        $session->shouldAllowMockingProtectedMethods();
        $session->location = $location;
        $session->shouldReceive('getEffectiveDurationSeconds')
            ->andReturn(4500); // 1:15 = 75 minute = 4500 secunde

        $price = $this->pricingService->calculateSessionPrice($session);
        
        // 1.5 ore * 50 RON = 75 RON
        $this->assertEquals(75.00, $price);
    }

    /**
     * Test calcul preț pentru sesiune de 1:10 (1.0 ore)
     */
    public function test_calculate_session_price_one_hour_ten_minutes(): void
    {
        $location = new Location();
        $location->price_per_hour = 50.00;

        $session = Mockery::mock(PlaySession::class)->makePartial();
        $session->shouldAllowMockingProtectedMethods();
        $session->location = $location;
        $session->shouldReceive('getEffectiveDurationSeconds')
            ->andReturn(4200); // 1:10 = 70 minute = 4200 secunde

        $price = $this->pricingService->calculateSessionPrice($session);
        
        // 1.0 ore * 50 RON = 50 RON (rotunjit în jos)
        $this->assertEquals(50.00, $price);
    }

    /**
     * Test calcul preț pentru sesiune de 1:35 (1.5 ore)
     */
    public function test_calculate_session_price_one_hour_thirty_five_minutes(): void
    {
        $location = new Location();
        $location->price_per_hour = 50.00;

        $session = Mockery::mock(PlaySession::class)->makePartial();
        $session->shouldAllowMockingProtectedMethods();
        $session->location = $location;
        $session->shouldReceive('getEffectiveDurationSeconds')
            ->andReturn(5700); // 1:35 = 95 minute = 5700 secunde

        $price = $this->pricingService->calculateSessionPrice($session);
        
        // 1.5 ore * 50 RON = 75 RON
        $this->assertEquals(75.00, $price);
    }

    /**
     * Test că sesiunea fără tenant returnează preț 0
     */
    public function test_session_without_location_returns_zero_price(): void
    {
        $session = Mockery::mock(PlaySession::class)->makePartial();
        $session->shouldAllowMockingProtectedMethods();
        $session->location = null;

        $price = $this->pricingService->calculateSessionPrice($session);
        
        $this->assertEquals(0.00, $price);
    }

    /**
     * Test că sesiunea cu locație fără preț returnează preț 0
     */
    public function test_session_with_location_without_price_returns_zero_price(): void
    {
        $location = new Location();
        $location->price_per_hour = null;

        $session = Mockery::mock(PlaySession::class)->makePartial();
        $session->shouldAllowMockingProtectedMethods();
        $session->location = $location;

        $price = $this->pricingService->calculateSessionPrice($session);
        
        $this->assertEquals(0.00, $price);
    }

    /**
     * Test că duratele mai lungi de 2 ore se calculează corect
     */
    public function test_longer_durations_calculated_correctly(): void
    {
        // 2h 10min -> 2.0 ore (1 + 1 complete + 10min < 15min)
        $this->assertEquals(2.0, $this->pricingService->roundToHalfHour(2.0 + 10 / 60));
        
        // 2h 15min -> 2.5 ore (1 + 1 complete + 15min ≥ 15min = +0.5h)
        $this->assertEquals(2.5, $this->pricingService->roundToHalfHour(2.0 + 15 / 60));
        
        // 2h 20min -> 2.5 ore (1 + 1 complete + 20min între 15-44min = +0.5h)
        $this->assertEquals(2.5, $this->pricingService->roundToHalfHour(2.0 + 20 / 60));
        
        // 2h 30min -> 2.5 ore (1 + 1 complete + 30min între 15-44min = +0.5h)
        $this->assertEquals(2.5, $this->pricingService->roundToHalfHour(2.0 + 30 / 60));
        
        // 2h 35min -> 2.5 ore (1 + 1 complete + 35min între 15-44min = +0.5h)
        $this->assertEquals(2.5, $this->pricingService->roundToHalfHour(2.0 + 35 / 60));
        
        // 2h 45min -> 2.5 ore (1 + 1 complete + 45min = 45min = +0.5h)
        $this->assertEquals(2.5, $this->pricingService->roundToHalfHour(2.0 + 45 / 60));
        
        // 2h 46min -> 3.0 ore (1 + 1 complete + 46min ≥ 45min = +1h)
        $this->assertEquals(3.0, $this->pricingService->roundToHalfHour(2.0 + 46 / 60));
        
        // Exact 3 ore -> 3.0 ore (1 + 2 complete hours)
        $this->assertEquals(3.0, $this->pricingService->roundToHalfHour(3.0));
        
        // 3h 10min -> 3.0 ore (1 + 2 complete + 10min < 15min)
        $this->assertEquals(3.0, $this->pricingService->roundToHalfHour(3.0 + 10 / 60));
        
        // 3h 20min -> 3.5 ore (1 + 2 complete + 20min între 15-44min = +0.5h)
        $this->assertEquals(3.5, $this->pricingService->roundToHalfHour(3.0 + 20 / 60));
        
        // Exact 8 ore -> 8.0 ore (1 + 7 complete hours)
        $this->assertEquals(8.0, $this->pricingService->roundToHalfHour(8.0));
        
        // 8h 10min -> 8.0 ore (1 + 7 complete + 10min < 15min)
        $this->assertEquals(8.0, $this->pricingService->roundToHalfHour(8.0 + 10 / 60));
        
        // 8h 20min -> 8.5 ore (1 + 7 complete + 20min între 15-44min = +0.5h)
        $this->assertEquals(8.5, $this->pricingService->roundToHalfHour(8.0 + 20 / 60));
        
        // 8h 35min -> 8.5 ore (1 + 7 complete + 35min între 15-44min = +0.5h)
        $this->assertEquals(8.5, $this->pricingService->roundToHalfHour(8.0 + 35 / 60));
        
        // 8h 45min -> 8.5 ore (1 + 7 complete + 45min = 45min = +0.5h)
        $this->assertEquals(8.5, $this->pricingService->roundToHalfHour(8.0 + 45 / 60));
        
        // 8h 46min -> 9.0 ore (1 + 7 complete + 46min ≥ 45min = +1h)
        $this->assertEquals(9.0, $this->pricingService->roundToHalfHour(8.0 + 46 / 60));
    }

    /**
     * Test calcul preț pentru sesiune de 8 ore cu tarif 40 RON/oră
     */
    public function test_calculate_session_price_eight_hours(): void
    {
        $location = new Location();
        $location->price_per_hour = 40.00;

        $session = Mockery::mock(PlaySession::class)->makePartial();
        $session->shouldAllowMockingProtectedMethods();
        $session->location = $location;
        $session->shouldReceive('getEffectiveDurationSeconds')
            ->andReturn(28800); // 8 ore = 28800 secunde

        $price = $this->pricingService->calculateSessionPrice($session);
        
        // 8.0 ore * 40 RON = 320 RON
        $this->assertEquals(320.00, $price);
    }

    /**
     * Test calcul preț pentru sesiune de 2h 20min cu tarif 40 RON/oră
     */
    public function test_calculate_session_price_two_hours_twenty_minutes(): void
    {
        $location = new Location();
        $location->price_per_hour = 40.00;

        $session = Mockery::mock(PlaySession::class)->makePartial();
        $session->shouldAllowMockingProtectedMethods();
        $session->location = $location;
        $session->shouldReceive('getEffectiveDurationSeconds')
            ->andReturn(8400); // 2h 20min = 8400 secunde

        $price = $this->pricingService->calculateSessionPrice($session);
        
        // 2.5 ore * 40 RON = 100 RON
        $this->assertEquals(100.00, $price);
    }

    /**
     * Test cazuri edge la limitele de rotunjire
     */
    public function test_edge_cases_at_rounding_boundaries(): void
    {
        // Exact 14.99 minute peste prima oră -> rotunjit în jos
        $this->assertEquals(1.0, $this->pricingService->roundToHalfHour(1.0 + 14.99 / 60));
        
        // Exact 15.00 minute peste prima oră -> +0.5h
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour(1.0 + 15.00 / 60));
        
        // Exact 15.01 minute peste prima oră -> +0.5h
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour(1.0 + 15.01 / 60));
        
        // Exact 44.99 minute peste prima oră -> +0.5h
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour(1.0 + 44.99 / 60));
        
        // Exact 45.00 minute peste prima oră -> +0.5h (45 min = prag, se rotunjește la 0.5h)
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour(1.0 + 45.00 / 60));
        
        // Exact 45.01 minute peste prima oră -> +1h
        $this->assertEquals(2.0, $this->pricingService->roundToHalfHour(1.0 + 45.01 / 60));
        
        // Exact 59.99 minute peste prima oră -> +1h
        $this->assertEquals(2.0, $this->pricingService->roundToHalfHour(1.0 + 59.99 / 60));
    }

    /**
     * Test durate foarte mici
     */
    public function test_very_small_durations(): void
    {
        // 1 secundă -> 1.0 ore (prima oră întotdeauna completă)
        $this->assertEquals(1.0, $this->pricingService->roundToHalfHour(1 / 3600));
        
        // 1 minut -> 1.0 ore
        $this->assertEquals(1.0, $this->pricingService->roundToHalfHour(1 / 60));
        
        // 5 minute -> 1.0 ore
        $this->assertEquals(1.0, $this->pricingService->roundToHalfHour(5 / 60));
    }

    /**
     * Test durate foarte mari
     */
    public function test_very_large_durations(): void
    {
        // 24 ore exacte -> 24.0 ore
        $this->assertEquals(24.0, $this->pricingService->roundToHalfHour(24.0));
        
        // 24h 10min -> 24.0 ore (10min < 15min)
        $this->assertEquals(24.0, $this->pricingService->roundToHalfHour(24.0 + 10 / 60));
        
        // 24h 20min -> 24.5 ore (20min între 15-44min)
        $this->assertEquals(24.5, $this->pricingService->roundToHalfHour(24.0 + 20 / 60));
        
        // 24h 35min -> 24.5 ore (35min între 15-44min)
        $this->assertEquals(24.5, $this->pricingService->roundToHalfHour(24.0 + 35 / 60));
        
        // 24h 45min -> 24.5 ore (45min = prag, se rotunjește la 0.5h)
        $this->assertEquals(24.5, $this->pricingService->roundToHalfHour(24.0 + 45 / 60));
        
        // 24h 46min -> 25.0 ore (46min ≥ 45min)
        $this->assertEquals(25.0, $this->pricingService->roundToHalfHour(24.0 + 46 / 60));
        
        // 100 ore exacte -> 100.0 ore
        $this->assertEquals(100.0, $this->pricingService->roundToHalfHour(100.0));
    }

    /**
     * Test prețuri cu zecimale
     */
    public function test_decimal_prices(): void
    {
        $tenant = new Tenant();
        $tenant->price_per_hour = 33.33;

        $session = Mockery::mock(PlaySession::class)->makePartial();
        $session->shouldAllowMockingProtectedMethods();
        $session->tenant = $tenant;
        $session->shouldReceive('getEffectiveDurationSeconds')
            ->andReturn(3600); // 1 oră exactă

        $price = $this->pricingService->calculateSessionPrice($session);
        
        // 1.0 ore * 33.33 RON = 33.33 RON (rotunjit la 2 zecimale)
        $this->assertEquals(33.33, $price);
    }

    /**
     * Test prețuri cu zecimale pentru durate mai lungi
     */
    public function test_decimal_prices_with_longer_durations(): void
    {
        $tenant = new Tenant();
        $tenant->price_per_hour = 25.50;

        $session = Mockery::mock(PlaySession::class)->makePartial();
        $session->shouldAllowMockingProtectedMethods();
        $session->tenant = $tenant;
        $session->shouldReceive('getEffectiveDurationSeconds')
            ->andReturn(5400); // 1h 30min = 5400 secunde

        $price = $this->pricingService->calculateSessionPrice($session);
        
        // 1.5 ore * 25.50 RON = 38.25 RON
        $this->assertEquals(38.25, $price);
    }

    /**
     * Test pentru durata exactă de 1h 14min 59sec (sub 15 min)
     */
    public function test_one_hour_fourteen_minutes_fifty_nine_seconds(): void
    {
        // 1h 14min 59sec = 1 + 14/60 + 59/3600 = 1.249722... ore
        $hours = 1.0 + 14/60 + 59/3600;
        $this->assertEquals(1.0, $this->pricingService->roundToHalfHour($hours));
    }

    /**
     * Test pentru durata exactă de 1h 15min 00sec (exact 15 min)
     */
    public function test_one_hour_fifteen_minutes_exact(): void
    {
        // 1h 15min = 1.25 ore exact
        $hours = 1.0 + 15/60;
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour($hours));
    }

    /**
     * Test pentru durata exactă de 1h 30min 00sec (exact 30 min)
     */
    public function test_one_hour_thirty_minutes_exact(): void
    {
        // 1h 30min = 1.5 ore exact
        $hours = 1.0 + 30/60;
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour($hours));
    }

    /**
     * Test pentru durata exactă de 1h 45min 00sec (exact 45 min)
     */
    public function test_one_hour_forty_five_minutes_exact(): void
    {
        // 1h 45min = 1.75 ore exact -> 1.5 ore (45 min = prag, se rotunjește la 0.5h)
        $hours = 1.0 + 45/60;
        $this->assertEquals(1.5, $this->pricingService->roundToHalfHour($hours));
    }

    /**
     * Test pentru durata exactă de 1h 45min 01sec (peste 45 min)
     */
    public function test_one_hour_forty_five_minutes_one_second(): void
    {
        // 1h 45min 1sec = 1 + 45/60 + 1/3600 = 1.750278... ore
        $hours = 1.0 + 45/60 + 1/3600;
        $this->assertEquals(2.0, $this->pricingService->roundToHalfHour($hours));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

