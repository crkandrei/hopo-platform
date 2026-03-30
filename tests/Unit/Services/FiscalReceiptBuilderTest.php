<?php

namespace Tests\Unit\Services;

use App\Services\FiscalReceiptBuilder;
use PHPUnit\Framework\TestCase;

class FiscalReceiptBuilderTest extends TestCase
{
    private FiscalReceiptBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new FiscalReceiptBuilder();
    }

    // =========================================================
    // formatHours — afișare durată din ore float
    // =========================================================

    public function test_format_hours_exact_integer(): void
    {
        $this->assertEquals('2h', $this->builder->formatHours(2.0));
    }

    public function test_format_hours_half_hour_only(): void
    {
        $this->assertEquals('30m', $this->builder->formatHours(0.5));
    }

    public function test_format_hours_hours_and_minutes(): void
    {
        $this->assertEquals('1h 30m', $this->builder->formatHours(1.5));
    }

    public function test_format_hours_zero(): void
    {
        $this->assertEquals('0m', $this->builder->formatHours(0.0));
    }

    public function test_format_hours_rounds_minutes_correctly(): void
    {
        // 2h + 15min = 2.25h
        $this->assertEquals('2h 15m', $this->builder->formatHours(2.25));
    }

    public function test_format_hours_handles_minute_overflow(): void
    {
        // Edge case: rounding pushes minutes to 60 → carry into hours
        // 1h 59.5min would round to 60min → should become 2h
        $this->assertEquals('2h', $this->builder->formatHours(1.0 + 59.5 / 60));
    }

    // =========================================================
    // allocateAmountDiscount — repartizare proporțională reducere
    // =========================================================

    public function test_allocate_splits_discount_proportionally(): void
    {
        $lines = [
            ['type' => 'time', 'quantity' => 1, 'unit_price' => 60.0, 'total_price' => 60.0],
            ['type' => 'product', 'quantity' => 2, 'unit_price' => 20.0, 'total_price' => 40.0],
        ];

        // Total = 100, discount = 20 → 80% din fiecare
        // time: 60 * 0.8 = 48, product: 40 * 0.8 = 32
        $result = $this->builder->allocateAmountDiscount($lines, 20.0);

        $this->assertEquals(20.0, $result['discountAmount']);
        $this->assertEquals(80.0, $result['finalTotal']);
        $this->assertEquals(48.0, $result['lines'][0]['discounted_total_price']);
        $this->assertEquals(32.0, $result['lines'][1]['discounted_total_price']);
    }

    public function test_allocate_zero_discount_leaves_lines_unchanged(): void
    {
        $lines = [
            ['type' => 'time', 'quantity' => 1, 'unit_price' => 50.0, 'total_price' => 50.0],
        ];

        $result = $this->builder->allocateAmountDiscount($lines, 0.0);

        $this->assertEquals(0.0, $result['discountAmount']);
        $this->assertEquals(50.0, $result['finalTotal']);
        $this->assertEquals(50.0, $result['lines'][0]['discounted_total_price']);
    }

    public function test_allocate_discount_capped_at_total(): void
    {
        $lines = [
            ['type' => 'time', 'quantity' => 1, 'unit_price' => 30.0, 'total_price' => 30.0],
        ];

        // Discount mai mare decât totalul → se limitează la total
        $result = $this->builder->allocateAmountDiscount($lines, 999.0);

        $this->assertEquals(30.0, $result['discountAmount']);
        $this->assertEquals(0.0, $result['finalTotal']);
        $this->assertEquals(0.0, $result['lines'][0]['discounted_total_price']);
    }

    public function test_allocate_last_line_absorbs_rounding_error(): void
    {
        // Total = 100, discount = 10 → final = 90
        // 3 linii de 33.33 fiecare → ultima linie trebuie să absoarbă restul de rotunjire
        $lines = [
            ['type' => 'time', 'quantity' => 1, 'unit_price' => 33.33, 'total_price' => 33.33],
            ['type' => 'time', 'quantity' => 1, 'unit_price' => 33.33, 'total_price' => 33.33],
            ['type' => 'time', 'quantity' => 1, 'unit_price' => 33.34, 'total_price' => 33.34],
        ];

        $result = $this->builder->allocateAmountDiscount($lines, 10.0);

        // Sum of discounted totals = finalTotal (no rounding leakage)
        $sumDiscounted = array_sum(array_column($result['lines'], 'discounted_total_price'));
        $this->assertEqualsWithDelta($result['finalTotal'], $sumDiscounted, 0.01);
    }

    public function test_allocate_single_line_full_discount(): void
    {
        $lines = [
            ['type' => 'time', 'quantity' => 1, 'unit_price' => 40.0, 'total_price' => 40.0],
        ];

        $result = $this->builder->allocateAmountDiscount($lines, 40.0);

        $this->assertEquals(0.0, $result['finalTotal']);
        $this->assertEquals(0.0, $result['lines'][0]['discounted_total_price']);
    }

    public function test_allocate_skips_zero_price_lines(): void
    {
        $lines = [
            ['type' => 'time', 'quantity' => 1, 'unit_price' => 60.0, 'total_price' => 60.0],
            ['type' => 'product', 'quantity' => 1, 'unit_price' => 0.0, 'total_price' => 0.0], // skip
        ];

        $result = $this->builder->allocateAmountDiscount($lines, 10.0);

        // Toată reducerea cade pe prima linie
        $this->assertEquals(10.0, $result['discountAmount']);
        $this->assertEquals(50.0, $result['lines'][0]['discounted_total_price']);
        $this->assertEquals(0.0, $result['lines'][1]['discounted_total_price']);
    }

    // =========================================================
    // applyHoursVoucherToTimeItems — distribuire ore voucher pe sesiuni multiple
    // =========================================================

    public function test_hours_voucher_fully_covers_one_session(): void
    {
        $timeItems = [
            ['duration' => '2h', 'roundedHours' => 2.0, 'price' => 40.0, 'pricePerHour' => 20.0, 'childName' => 'Ion', 'sessionId' => 1],
        ];

        // Voucher: 2h → acoperă integral prima sesiune → 2h × 20 RON = 40 RON reducere
        $result = $this->builder->applyHoursVoucherToTimeItems($timeItems, 2.0);

        $this->assertEqualsWithDelta(40.0, $result['voucherPrice'], 0.01);
        $this->assertEmpty($result['adjustedItems']); // sesiunea e complet acoperită → nu apare în bon
    }

    public function test_hours_voucher_partially_covers_one_session(): void
    {
        $timeItems = [
            ['duration' => '3h', 'roundedHours' => 3.0, 'price' => 60.0, 'pricePerHour' => 20.0, 'childName' => 'Ion', 'sessionId' => 1],
        ];

        // Voucher: 1h → rămân 2h de plată
        $result = $this->builder->applyHoursVoucherToTimeItems($timeItems, 1.0);

        $this->assertEqualsWithDelta(20.0, $result['voucherPrice'], 0.01); // 1h × 20 RON
        $this->assertCount(1, $result['adjustedItems']);
        $this->assertEqualsWithDelta(2.0, $result['adjustedItems'][0]['roundedHours'], 0.001);
        $this->assertEqualsWithDelta(40.0, $result['adjustedItems'][0]['price'], 0.01);
    }

    public function test_hours_voucher_covers_first_session_and_partial_second(): void
    {
        $timeItems = [
            ['duration' => '1h', 'roundedHours' => 1.0, 'price' => 20.0, 'pricePerHour' => 20.0, 'childName' => 'Ion', 'sessionId' => 1],
            ['duration' => '2h', 'roundedHours' => 2.0, 'price' => 40.0, 'pricePerHour' => 20.0, 'childName' => 'Ana', 'sessionId' => 2],
        ];

        // Voucher: 1.5h → acoperă complet prima sesiune (1h) + 0.5h din a doua
        $result = $this->builder->applyHoursVoucherToTimeItems($timeItems, 1.5);

        $this->assertEqualsWithDelta(30.0, $result['voucherPrice'], 0.01); // 1.5h × 20 RON
        $this->assertCount(1, $result['adjustedItems']); // doar sesiunea 2 mai are ceva de plătit
        $this->assertEqualsWithDelta(1.5, $result['adjustedItems'][0]['roundedHours'], 0.001); // 2 - 0.5 = 1.5h
        $this->assertEqualsWithDelta(30.0, $result['adjustedItems'][0]['price'], 0.01);
    }

    public function test_hours_voucher_zero_leaves_items_unchanged(): void
    {
        $timeItems = [
            ['duration' => '2h', 'roundedHours' => 2.0, 'price' => 40.0, 'pricePerHour' => 20.0, 'childName' => 'Ion', 'sessionId' => 1],
        ];

        $result = $this->builder->applyHoursVoucherToTimeItems($timeItems, 0.0);

        $this->assertEquals(0.0, $result['voucherPrice']);
        $this->assertCount(1, $result['adjustedItems']);
        $this->assertEquals($timeItems[0]['roundedHours'], $result['adjustedItems'][0]['roundedHours']);
    }
}
