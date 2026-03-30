<?php

namespace App\Services;

use Illuminate\Support\Collection;

class FiscalReceiptBuilder
{
    /**
     * Format a float hours value as "Xh Ym", "Xh", "Ym", or "0m".
     */
    public function formatHours(float $hours): string
    {
        $hoursInt = (int) floor($hours);
        $minutes = (int) round(($hours - $hoursInt) * 60);

        if ($minutes >= 60) {
            $hoursInt += 1;
            $minutes = 0;
        }

        return $this->formatDuration($hoursInt, $minutes);
    }

    /**
     * Resolve product lines from a session's product relation.
     * Handles missing names by falling back to a DB lookup, then ID.
     *
     * @param Collection $sessionProducts Collection of SessionProduct models (with product.tvaRate eager-loaded)
     * @return array
     */
    public function resolveProductLines(Collection $sessionProducts): array
    {
        return $sessionProducts->map(function ($sp) {
            $productName = null;

            if ($sp->product && $sp->product->name) {
                $productName = trim($sp->product->name);
            }

            if (empty($productName) && $sp->product_id) {
                $product = \App\Models\Product::find($sp->product_id);
                if ($product && $product->name) {
                    $productName = trim($product->name);
                }
            }

            if (empty($productName)) {
                $productName = 'Produs ID: ' . $sp->product_id;
            }

            return [
                'name'        => $productName,
                'quantity'    => $sp->quantity,
                'unit_price'  => (float) $sp->unit_price,
                'total_price' => (float) $sp->total_price,
                'vatClass'    => $sp->product?->tvaRate?->vat_class ?? 1,
            ];
        })->values()->all();
    }

    /**
     * Apply an hours-type voucher across multiple time items.
     * Voucher hours are consumed from the first item, then subsequent items.
     *
     * @param array $timeItems  Each item: ['roundedHours', 'price', 'pricePerHour', 'duration', 'childName', 'sessionId']
     * @param float $voucherHours
     * @return array ['voucherPrice' => float, 'adjustedItems' => array]
     */
    public function applyHoursVoucherToTimeItems(array $timeItems, float $voucherHours): array
    {
        $remainingVoucherHours = $voucherHours;
        $voucherPrice = 0.0;
        $adjustedItems = [];

        foreach ($timeItems as $timeItem) {
            if ($remainingVoucherHours <= 0) {
                $adjustedItems[] = $timeItem;
                continue;
            }

            if ($timeItem['roundedHours'] >= $remainingVoucherHours) {
                // Apply remaining voucher hours to this item
                $adjustedHours = $timeItem['roundedHours'] - $remainingVoucherHours;
                $voucherPrice += $remainingVoucherHours * $timeItem['pricePerHour'];

                if ($adjustedHours > 0) {
                    $adjustedItems[] = array_merge($timeItem, [
                        'duration'     => $this->formatHours($adjustedHours),
                        'roundedHours' => $adjustedHours,
                        'price'        => $adjustedHours * $timeItem['pricePerHour'],
                    ]);
                }

                $remainingVoucherHours = 0;
            } else {
                // This item is fully covered by remaining voucher hours
                $voucherPrice += $timeItem['roundedHours'] * $timeItem['pricePerHour'];
                $remainingVoucherHours -= $timeItem['roundedHours'];
            }
        }

        return [
            'voucherPrice'  => $voucherPrice,
            'adjustedItems' => $adjustedItems,
        ];
    }

    /**
     * Allocate a fixed discount amount proportionally across receipt lines.
     * The last positive-price line absorbs any rounding residue.
     *
     * @param array $lines Each line must have: quantity, unit_price, total_price
     * @param float $discountAmount
     * @return array ['lines' => array, 'discountAmount' => float, 'finalTotal' => float]
     */
    public function allocateAmountDiscount(array $lines, float $discountAmount): array
    {
        $total = 0.0;
        $positiveIndexes = [];

        foreach ($lines as $index => $line) {
            $lineTotal = max(0, round((float) ($line['total_price'] ?? 0), 2));
            $lines[$index]['total_price'] = $lineTotal;
            $lines[$index]['discounted_total_price'] = $lineTotal;
            $lines[$index]['discounted_unit_price'] = (float) ($line['unit_price'] ?? $lineTotal);

            if ($lineTotal > 0) {
                $total += $lineTotal;
                $positiveIndexes[] = $index;
            }
        }

        $discountAmount = max(0, min(round($discountAmount, 2), round($total, 2)));
        $finalTotal = max(0, round($total - $discountAmount, 2));

        if ($discountAmount <= 0 || empty($positiveIndexes)) {
            return [
                'lines'          => $lines,
                'discountAmount' => 0.0,
                'finalTotal'     => $finalTotal,
            ];
        }

        $allocatedTotal = 0.0;
        $lastPositiveIndex = end($positiveIndexes);

        foreach ($positiveIndexes as $index) {
            if ($index === $lastPositiveIndex) {
                $discountedTotal = max(0, round($finalTotal - $allocatedTotal, 2));
            } else {
                $discountedTotal = round(($lines[$index]['total_price'] / $total) * $finalTotal, 2);
            }

            $quantity = max(0, (float) ($lines[$index]['quantity'] ?? 1));
            $lines[$index]['discounted_total_price'] = $discountedTotal;
            $lines[$index]['discounted_unit_price'] = $quantity > 0
                ? round($discountedTotal / $quantity, 6)
                : round($discountedTotal, 6);

            $allocatedTotal += $discountedTotal;
        }

        return [
            'lines'          => $lines,
            'discountAmount' => round($total - $finalTotal, 2),
            'finalTotal'     => $finalTotal,
        ];
    }

    private function formatDuration(int $hours, int $minutes): string
    {
        if ($hours === 0 && $minutes === 0) {
            return '0m';
        }
        if ($hours === 0) {
            return "{$minutes}m";
        }
        if ($minutes === 0) {
            return "{$hours}h";
        }
        return "{$hours}h {$minutes}m";
    }
}
