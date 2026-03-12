<?php

namespace App\Services;

use App\Models\Location;
use App\Models\PlaySession;
use App\Models\StandaloneReceipt;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Http\Request;

class VoucherService
{
    private const CHARSET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    private const CODE_LENGTH = 8;

    public function generateUniqueCode(Location $location): string
    {
        $maxAttempts = 20;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $code = $this->generateCode();
            $exists = Voucher::withoutGlobalScope('location')
                ->where('location_id', $location->id)
                ->where('code', $code)
                ->exists();
            if (!$exists) {
                return $code;
            }
        }
        throw new \RuntimeException('Nu s-a putut genera un cod unic pentru voucher.');
    }

    private function generateCode(): string
    {
        $code = '';
        $charsetLength = strlen(self::CHARSET);
        for ($j = 0; $j < self::CODE_LENGTH; $j++) {
            $code .= self::CHARSET[random_int(0, $charsetLength - 1)];
        }
        return $code;
    }

    /**
     * @param string|null $type 'amount' or 'hours' to restrict validation
     * @return array{valid: bool, message: string, voucher?: Voucher}
     */
    public function validateVoucher(string $code, Location $location, ?string $type = null): array
    {
        $voucher = Voucher::withoutGlobalScope('location')
            ->where('location_id', $location->id)
            ->where('code', strtoupper(trim($code)))
            ->first();

        if (!$voucher) {
            return ['valid' => false, 'message' => 'Cod voucher invalid.'];
        }
        if ($type && $voucher->type !== $type) {
            return ['valid' => false, 'message' => 'Tipul voucherului nu este compatibil (așteptat: ' . $type . ').'];
        }
        if (!$voucher->is_active) {
            return ['valid' => false, 'message' => 'Voucherul nu este activ.'];
        }
        if ($voucher->isExpired()) {
            return ['valid' => false, 'message' => 'Voucherul a expirat.'];
        }
        if ((float) $voucher->remaining_value <= 0) {
            return ['valid' => false, 'message' => 'Voucherul nu mai are sold.'];
        }
        return ['valid' => true, 'message' => 'Voucher valid.', 'voucher' => $voucher];
    }

    /**
     * Resolve voucher from request (voucher_id or voucher_code). Used by both session and standalone payment flows.
     *
     * @param string|null $typeConstraint 'amount' or 'hours' to restrict; null allows both
     * @throws \InvalidArgumentException on validation failure
     */
    public function resolveVoucherFromRequest(Location $location, Request $request, ?string $typeConstraint = null): ?Voucher
    {
        if ($request->filled('voucher_id')) {
            $query = Voucher::withoutGlobalScope('location')
                ->where('id', $request->input('voucher_id'))
                ->where('location_id', $location->id);

            if ($typeConstraint !== null) {
                $query->where('type', $typeConstraint);
            }

            $voucher = $query->first();

            if (!$voucher) {
                throw new \InvalidArgumentException('Voucherul selectat nu a fost găsit pentru această locație.');
            }

            if (!$voucher->is_active) {
                throw new \InvalidArgumentException('Voucherul nu este activ.');
            }

            if ($voucher->isExpired()) {
                throw new \InvalidArgumentException('Voucherul a expirat.');
            }

            if ((float) $voucher->remaining_value <= 0) {
                throw new \InvalidArgumentException('Voucherul nu mai are sold.');
            }

            return $voucher;
        }

        if ($request->filled('voucher_code')) {
            $result = $this->validateVoucher($request->input('voucher_code'), $location, $typeConstraint);
            if (!$result['valid']) {
                throw new \InvalidArgumentException($result['message']);
            }

            return $result['voucher'];
        }

        return null;
    }

    /**
     * Apply voucher: create usage and update remaining value. Receipt (session or standalone) is updated by caller with voucher_id.
     */
    public function applyVoucher(Voucher $voucher, float $amount, PlaySession|StandaloneReceipt $receipt, ?string $notes = null): VoucherUsage
    {
        return $voucher->use($amount, $receipt, $notes);
    }

    /**
     * Stats for a location: total issued value, total used, remaining, revenue impact.
     */
    public function getVoucherStats(Location $location): array
    {
        $vouchers = Voucher::withoutGlobalScope('location')->where('location_id', $location->id)->get();

        $statsByType = [
            'amount' => [
                'issued' => 0.0,
                'used' => 0.0,
                'remaining' => 0.0,
            ],
            'hours' => [
                'issued' => 0.0,
                'used' => 0.0,
                'remaining' => 0.0,
            ],
        ];

        foreach ($vouchers as $voucher) {
            if (!isset($statsByType[$voucher->type])) {
                continue;
            }

            $initialValue = (float) $voucher->initial_value;
            $remainingValue = (float) $voucher->remaining_value;

            $statsByType[$voucher->type]['issued'] += $initialValue;
            $statsByType[$voucher->type]['remaining'] += $remainingValue;
            $statsByType[$voucher->type]['used'] += $initialValue - $remainingValue;
        }

        return [
            'total_issued_count' => $vouchers->count(),
            'amount' => [
                'issued' => round($statsByType['amount']['issued'], 2),
                'used' => round($statsByType['amount']['used'], 2),
                'remaining' => round($statsByType['amount']['remaining'], 2),
            ],
            'hours' => [
                'issued' => round($statsByType['hours']['issued'], 2),
                'used' => round($statsByType['hours']['used'], 2),
                'remaining' => round($statsByType['hours']['remaining'], 2),
            ],
        ];
    }

    /**
     * Vouchers expiring within the next X days.
     */
    public function getExpiringVouchers(Location $location, int $daysUntilExpiry = 7)
    {
        $until = now()->addDays($daysUntilExpiry);
        return Voucher::withoutGlobalScope('location')
            ->where('location_id', $location->id)
            ->where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $until)
            ->where('expires_at', '>=', now())
            ->where('remaining_value', '>', 0)
            ->orderBy('expires_at')
            ->get();
    }
}
