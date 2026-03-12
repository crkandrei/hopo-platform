<?php

namespace App\Models;

use App\Models\Traits\BelongsToLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandaloneReceipt extends Model
{
    use BelongsToLocation;

    protected $fillable = [
        'location_id',
        'created_by',
        'payment_method',
        'payment_status',
        'paid_at',
        'total_amount',
        'voucher_id',
        'notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StandaloneReceiptItem::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function voucherUsages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class, 'standalone_receipt_id');
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    /**
     * Apply voucher (amount only) to this receipt.
     */
    public function applyVoucher(Voucher $voucher, float $amount): VoucherUsage
    {
        $usage = $voucher->use($amount, $this);
        $this->update(['voucher_id' => $voucher->id]);
        return $usage;
    }

    public function getVoucherDiscount(): float
    {
        if (!$this->voucher_id) {
            return 0.0;
        }
        return (float) $this->voucherUsages()->where('voucher_id', $this->voucher_id)->sum('amount_used');
    }

    public function getFinalPrice(): float
    {
        return max(0, (float) $this->total_amount - $this->getVoucherDiscount());
    }
}
