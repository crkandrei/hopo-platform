<?php

namespace Tests\Unit\Services;

use App\Models\Voucher;
use App\Services\VoucherService;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests for VoucherService::resolveHoursToUse() and resolveAmountToUse().
 * No DB needed — Voucher instances are constructed in memory.
 */
class VoucherResolveTest extends TestCase
{
    private VoucherService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VoucherService();
    }

    private function makeHoursVoucher(float $remaining): Voucher
    {
        $v = new Voucher(['type' => 'hours', 'remaining_value' => $remaining]);
        return $v;
    }

    private function makeAmountVoucher(float $remaining): Voucher
    {
        $v = new Voucher(['type' => 'amount', 'remaining_value' => $remaining]);
        return $v;
    }

    // =========================================================
    // resolveHoursToUse
    // =========================================================

    public function test_auto_hours_capped_at_session_duration(): void
    {
        $voucher = $this->makeHoursVoucher(remaining: 5.0);

        // Voucher has 5h but session only 2h → cap at 2h
        $result = $this->service->resolveHoursToUse($voucher, maxHours: 2.0, requestedHours: null);

        $this->assertEquals(2.0, $result);
    }

    public function test_auto_hours_capped_at_voucher_balance(): void
    {
        $voucher = $this->makeHoursVoucher(remaining: 1.0);

        // Session is 3h but voucher only has 1h
        $result = $this->service->resolveHoursToUse($voucher, maxHours: 3.0, requestedHours: null);

        $this->assertEquals(1.0, $result);
    }

    public function test_requested_hours_within_limits_accepted(): void
    {
        $voucher = $this->makeHoursVoucher(remaining: 3.0);

        $result = $this->service->resolveHoursToUse($voucher, maxHours: 2.0, requestedHours: 1.5);

        $this->assertEquals(1.5, $result);
    }

    public function test_requested_hours_exceeding_session_throws(): void
    {
        $voucher = $this->makeHoursVoucher(remaining: 10.0);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->resolveHoursToUse($voucher, maxHours: 2.0, requestedHours: 3.0);
    }

    public function test_zero_voucher_balance_throws(): void
    {
        $voucher = $this->makeHoursVoucher(remaining: 0.0);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->resolveHoursToUse($voucher, maxHours: 2.0, requestedHours: null);
    }

    public function test_requested_zero_hours_throws(): void
    {
        $voucher = $this->makeHoursVoucher(remaining: 3.0);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->resolveHoursToUse($voucher, maxHours: 2.0, requestedHours: 0.0);
    }

    public function test_exact_session_hours_accepted(): void
    {
        $voucher = $this->makeHoursVoucher(remaining: 2.0);

        $result = $this->service->resolveHoursToUse($voucher, maxHours: 2.0, requestedHours: 2.0);

        $this->assertEquals(2.0, $result);
    }

    // =========================================================
    // resolveAmountToUse
    // =========================================================

    public function test_auto_amount_capped_at_total_price(): void
    {
        $voucher = $this->makeAmountVoucher(remaining: 999.0);

        $result = $this->service->resolveAmountToUse($voucher, maxAmount: 40.0, requestedAmount: null);

        $this->assertEquals(40.0, $result);
    }

    public function test_auto_amount_capped_at_voucher_balance(): void
    {
        $voucher = $this->makeAmountVoucher(remaining: 15.0);

        $result = $this->service->resolveAmountToUse($voucher, maxAmount: 40.0, requestedAmount: null);

        $this->assertEquals(15.0, $result);
    }

    public function test_requested_amount_within_limits_accepted(): void
    {
        $voucher = $this->makeAmountVoucher(remaining: 50.0);

        $result = $this->service->resolveAmountToUse($voucher, maxAmount: 40.0, requestedAmount: 30.0);

        $this->assertEquals(30.0, $result);
    }

    public function test_requested_amount_exceeding_total_throws(): void
    {
        $voucher = $this->makeAmountVoucher(remaining: 100.0);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->resolveAmountToUse($voucher, maxAmount: 40.0, requestedAmount: 50.0);
    }

    public function test_zero_voucher_amount_balance_throws(): void
    {
        $voucher = $this->makeAmountVoucher(remaining: 0.0);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->resolveAmountToUse($voucher, maxAmount: 40.0, requestedAmount: null);
    }

    public function test_exact_total_amount_accepted(): void
    {
        $voucher = $this->makeAmountVoucher(remaining: 40.0);

        $result = $this->service->resolveAmountToUse($voucher, maxAmount: 40.0, requestedAmount: 40.0);

        $this->assertEquals(40.0, $result);
    }
}
