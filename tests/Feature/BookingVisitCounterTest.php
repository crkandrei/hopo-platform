<?php

namespace Tests\Feature;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BookingVisitCounterTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_has_booking_visit_count_defaulting_to_zero(): void
    {
        $location = Location::factory()->create();

        $this->assertSame(0, $location->fresh()->booking_visit_count);
    }

    public function test_booking_visit_count_is_cast_to_integer(): void
    {
        $location = Location::factory()->create();

        $this->assertIsInt($location->fresh()->booking_visit_count);
    }
}
