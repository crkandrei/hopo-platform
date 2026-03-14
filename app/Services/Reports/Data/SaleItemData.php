<?php

namespace App\Services\Reports\Data;

class SaleItemData
{
    public function __construct(
        public readonly string $name,
        public readonly int $quantity,
        public readonly float $cashTotal,
        public readonly float $cardTotal,
        public readonly float $voucherTotal,
        public readonly float $total,
    ) {}
}
