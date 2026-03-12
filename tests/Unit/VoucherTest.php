<?php

namespace Tests\Unit;

use App\Models\Location;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_expired_returns_true_when_expires_at_is_past(): void
    {
        $location = Location::factory()->create();
        $voucher = Voucher::withoutGlobalScope('location')->create([
            'location_id' => $location->id,
            'code' => 'EXPIRED1',
            'type' => 'amount',
            'initial_value' => 100,
            'remaining_value' => 50,
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);
        $this->assertTrue($voucher->isExpired());
    }

    public function test_is_expired_returns_false_when_expires_at_is_null(): void
    {
        $location = Location::factory()->create();
        $voucher = Voucher::withoutGlobalScope('location')->create([
            'location_id' => $location->id,
            'code' => 'NOEXPIRE',
            'type' => 'amount',
            'initial_value' => 100,
            'remaining_value' => 100,
            'expires_at' => null,
            'is_active' => true,
        ]);
        $this->assertFalse($voucher->isExpired());
    }

    public function test_can_be_used_requires_active_and_not_expired_and_remaining(): void
    {
        $location = Location::factory()->create();
        $voucher = Voucher::withoutGlobalScope('location')->create([
            'location_id' => $location->id,
            'code' => 'VALID1',
            'type' => 'amount',
            'initial_value' => 100,
            'remaining_value' => 50,
            'expires_at' => now()->addDays(7),
            'is_active' => true,
        ]);
        $this->assertTrue($voucher->canBeUsed());
    }

    public function test_can_be_used_returns_false_when_remaining_zero(): void
    {
        $location = Location::factory()->create();
        $voucher = Voucher::withoutGlobalScope('location')->create([
            'location_id' => $location->id,
            'code' => 'ZERO1',
            'type' => 'amount',
            'initial_value' => 100,
            'remaining_value' => 0,
            'expires_at' => null,
            'is_active' => true,
        ]);
        $this->assertFalse($voucher->canBeUsed());
    }

    public function test_get_usage_count_returns_zero_when_no_usages(): void
    {
        $voucher = Voucher::factory()->create(['remaining_value' => 50]);
        $this->assertSame(0, $voucher->getUsageCount());
    }

    public function test_get_total_used_for_amount_type(): void
    {
        $location = Location::factory()->create();
        $voucher = Voucher::withoutGlobalScope('location')->create([
            'location_id' => $location->id,
            'code' => 'AMT1',
            'type' => 'amount',
            'initial_value' => 100,
            'remaining_value' => 60,
            'expires_at' => null,
            'is_active' => true,
        ]);
        $voucher->usages()->create([
            'amount_used' => 40,
            'hours_used' => null,
            'used_at' => now(),
        ]);
        $this->assertEqualsWithDelta(40, $voucher->getTotalUsed(), 0.01);
    }
}
