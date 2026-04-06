<?php

namespace App\Models;

use App\Models\Traits\BelongsToLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialPeriodRate extends Model
{
    use BelongsToLocation;
    protected $fillable = [
        'location_id',
        'name',
        'start_date',
        'end_date',
        'hourly_rate',
        'pricing_mode',
        'price_1h',
        'price_2h',
        'price_3h',
        'price_4h',
        'overflow_price_per_hour',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'price_1h' => 'decimal:2',
        'price_2h' => 'decimal:2',
        'price_3h' => 'decimal:2',
        'price_4h' => 'decimal:2',
        'overflow_price_per_hour' => 'decimal:2',
    ];

    /**
     * Whether this period uses tiered duration pricing (1h, 2h, 3h, 4h).
     */
    public function isTiered(): bool
    {
        return ($this->pricing_mode ?? 'flat_hourly') === 'tiered';
    }

    /**
     * Get tier prices as array: [1 => price, 2 => price, ...], only for defined tiers.
     *
     * @return array<int, float>
     */
    public function getTierPrices(): array
    {
        $tiers = [];
        foreach ([1 => 'price_1h', 2 => 'price_2h', 3 => 'price_3h', 4 => 'price_4h'] as $hours => $attr) {
            $val = $this->{$attr};
            if ($val !== null && $val !== '') {
                $tiers[$hours] = (float) $val;
            }
        }
        ksort($tiers);
        return $tiers;
    }


    /**
     * Check if a date falls within this special period
     */
    public function includesDate($date): bool
    {
        $date = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        return $date->between($this->start_date, $this->end_date);
    }

    /**
     * Calculate price for a given rounded duration when pricing_mode is tiered.
     * Excess above a tier is billed at the incremental rate toward the next tier.
     * Beyond the last tier, the explicit overflow_price_per_hour is used (or the last tier's avg rate).
     *
     * @param float $roundedHours
     * @return float Price in RON
     */
    public function calculateTieredPrice(float $roundedHours): float
    {
        $tiers = $this->getTierPrices();
        if (empty($tiers)) {
            return 0.00;
        }
        ksort($tiers);

        $durations = array_keys($tiers);

        // Find base tier (largest where duration <= roundedHours)
        $baseIdx = null;
        foreach ($durations as $i => $duration) {
            if ((float) $duration <= $roundedHours) {
                $baseIdx = $i;
            }
        }

        if ($baseIdx === null) {
            return (float) $tiers[$durations[0]];
        }

        $baseDuration = (float) $durations[$baseIdx];
        $basePrice    = (float) $tiers[$baseDuration];
        $excessHours  = $roundedHours - $baseDuration;

        if ($excessHours <= 0) {
            return $basePrice;
        }

        $nextIdx = $baseIdx + 1;
        if (isset($durations[$nextIdx])) {
            $nextDuration = (float) $durations[$nextIdx];
            $nextPrice    = (float) $tiers[$nextDuration];
            $hourlyRate   = ($nextPrice - $basePrice) / ($nextDuration - $baseDuration);
        } else {
            // Dincolo de ultima tranșă: folosi overflow_price_per_hour explicit (chiar dacă 0)
            // null înseamnă nesetat → fără taxă suplimentară (0)
            $hourlyRate = $this->overflow_price_per_hour !== null
                ? (float) $this->overflow_price_per_hour
                : 0.00;
        }

        return round($basePrice + $excessHours * $hourlyRate, 2);
    }
}

