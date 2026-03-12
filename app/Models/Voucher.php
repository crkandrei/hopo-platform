<?php

namespace App\Models;

use App\Models\Traits\BelongsToLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Voucher extends Model
{
    use BelongsToLocation, HasFactory;

    protected $fillable = [
        'location_id',
        'code',
        'type',
        'initial_value',
        'remaining_value',
        'expires_at',
        'is_active',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'initial_value' => 'decimal:2',
        'remaining_value' => 'decimal:2',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    public function canBeUsed(): bool
    {
        return $this->is_active
            && !$this->isExpired()
            && (float) $this->remaining_value > 0;
    }

    public function getUsageCount(): int
    {
        return $this->usages()->count();
    }

    public function getTotalUsed(): float
    {
        if ($this->type === 'amount') {
            return (float) $this->usages()->sum('amount_used');
        }
        return (float) $this->usages()->sum('hours_used');
    }

    /**
     * Consume from voucher balance, create VoucherUsage, validate expiry and balance.
     * Uses a transaction with row-level locking so balance checks and deduction are atomic.
     *
     * @param float $amount For type 'amount' the RON to deduct; for type 'hours' the hours to deduct
     * @param PlaySession|StandaloneReceipt $receipt
     */
    public function use(float $amount, PlaySession|StandaloneReceipt $receipt, ?string $notes = null): VoucherUsage
    {
        $usage = DB::transaction(function () use ($amount, $receipt, $notes) {
            $voucher = self::where('id', $this->id)->lockForUpdate()->firstOrFail();

            if ($voucher->isExpired()) {
                throw new \InvalidArgumentException('Voucherul a expirat');
            }
            if (!$voucher->is_active) {
                throw new \InvalidArgumentException('Voucherul nu este activ');
            }
            if ((float) $voucher->remaining_value < $amount) {
                throw new \InvalidArgumentException('Sold insuficient pe voucher');
            }

            $amountUsed = null;
            $hoursUsed = null;
            if ($voucher->type === 'amount') {
                $amountUsed = $amount;
            } else {
                $hoursUsed = $amount;
            }

            $usage = $voucher->usages()->create([
                'play_session_id' => $receipt instanceof PlaySession ? $receipt->id : null,
                'standalone_receipt_id' => $receipt instanceof StandaloneReceipt ? $receipt->id : null,
                'amount_used' => $amountUsed,
                'hours_used' => $hoursUsed,
                'used_at' => now(),
                'notes' => $notes,
            ]);

            $voucher->decrement('remaining_value', $amount);

            return $usage;
        });

        $this->refresh();

        return $usage;
    }

    public function canUseAmount(float $amount): bool
    {
        return $this->canBeUsed() && (float) $this->remaining_value >= $amount;
    }

    public function getUsageHistory()
    {
        return $this->usages()->orderByDesc('used_at')->get();
    }
}
