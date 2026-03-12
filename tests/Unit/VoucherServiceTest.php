<?php

namespace Tests\Unit;

use App\Models\Location;
use App\Models\PlaySession;
use App\Models\StandaloneReceipt;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherServiceTest extends TestCase
{
    use RefreshDatabase;

    protected VoucherService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(VoucherService::class);
    }

    public function test_generate_unique_code_returns_8_char_string(): void
    {
        $location = Location::factory()->create();
        $code = $this->service->generateUniqueCode($location);
        $this->assertSame(8, strlen($code));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]+$/', $code);
    }

    public function test_generate_unique_code_is_unique_per_location(): void
    {
        $location = Location::factory()->create();
        $code1 = $this->service->generateUniqueCode($location);
        Voucher::withoutGlobalScope('location')->create([
            'location_id' => $location->id,
            'code' => $code1,
            'type' => 'amount',
            'initial_value' => 100,
            'remaining_value' => 100,
            'is_active' => true,
        ]);
        $code2 = $this->service->generateUniqueCode($location);
        $this->assertNotSame($code1, $code2);
    }

    public function test_validate_voucher_returns_invalid_for_unknown_code(): void
    {
        $location = Location::factory()->create();
        $result = $this->service->validateVoucher('UNKNOWN99', $location);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('invalid', strtolower($result['message']));
    }

    public function test_validate_voucher_returns_valid_for_active_voucher(): void
    {
        $location = Location::factory()->create();
        $voucher = Voucher::withoutGlobalScope('location')->create([
            'location_id' => $location->id,
            'code' => 'VALID99',
            'type' => 'amount',
            'initial_value' => 100,
            'remaining_value' => 50,
            'expires_at' => now()->addDays(1),
            'is_active' => true,
        ]);
        $result = $this->service->validateVoucher('VALID99', $location);
        $this->assertTrue($result['valid']);
        $this->assertSame($voucher->id, $result['voucher']->id);
    }

    public function test_validate_voucher_restricts_by_type_when_requested(): void
    {
        $location = Location::factory()->create();
        Voucher::withoutGlobalScope('location')->create([
            'location_id' => $location->id,
            'code' => 'HOURS99',
            'type' => 'hours',
            'initial_value' => 2,
            'remaining_value' => 2,
            'is_active' => true,
        ]);
        $result = $this->service->validateVoucher('HOURS99', $location, 'amount');
        $this->assertFalse($result['valid']);
    }

    public function test_get_voucher_stats_returns_totals_for_location(): void
    {
        $location = Location::factory()->create();
        Voucher::withoutGlobalScope('location')->create([
            'location_id' => $location->id,
            'code' => 'S1',
            'type' => 'amount',
            'initial_value' => 100,
            'remaining_value' => 40,
            'is_active' => true,
        ]);
        Voucher::withoutGlobalScope('location')->create([
            'location_id' => $location->id,
            'code' => 'S2',
            'type' => 'amount',
            'initial_value' => 50,
            'remaining_value' => 50,
            'is_active' => true,
        ]);
        $stats = $this->service->getVoucherStats($location);
        $this->assertSame(2, $stats['total_issued_count']);
        $this->assertEqualsWithDelta(150, $stats['total_initial_value'], 0.01);
        $this->assertEqualsWithDelta(60, $stats['total_used_value'], 0.01);
        $this->assertEqualsWithDelta(90, $stats['total_remaining_value'], 0.01);
    }
}
