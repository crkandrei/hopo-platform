<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherUsage extends Model
{
    protected $fillable = [
        'voucher_id',
        'play_session_id',
        'standalone_receipt_id',
        'amount_used',
        'hours_used',
        'used_at',
        'notes',
    ];

    protected $casts = [
        'amount_used' => 'decimal:2',
        'hours_used' => 'decimal:2',
        'used_at' => 'datetime',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function playSession(): BelongsTo
    {
        return $this->belongsTo(PlaySession::class);
    }

    public function standaloneReceipt(): BelongsTo
    {
        return $this->belongsTo(StandaloneReceipt::class);
    }

    public function getReceiptType(): string
    {
        if ($this->play_session_id !== null) {
            return 'session';
        }
        if ($this->standalone_receipt_id !== null) {
            return 'standalone';
        }
        return 'unknown';
    }

    /**
     * Total amount of the receipt where the voucher was used.
     */
    public function getReceiptAmount(): float
    {
        if ($this->playSession) {
            return (float) ($this->playSession->calculated_price ?? 0) + $this->playSession->getProductsTotalPrice();
        }
        if ($this->standaloneReceipt) {
            return (float) $this->standaloneReceipt->total_amount;
        }
        return 0;
    }
}
