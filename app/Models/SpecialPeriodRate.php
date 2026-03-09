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
     * Rounded hours must already be computed (e.g. via FlatHourlyStrategy::roundToHalfHour).
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
        foreach ($tiers as $duration => $price) {
            if ($roundedHours <= (float) $duration) {
                return (float) $price;
            }
        }
        $lastDuration = (float) array_key_last($tiers);
        $lastPrice = (float) $tiers[$lastDuration];
        $overflowRate = $this->overflow_price_per_hour ? (float) $this->overflow_price_per_hour : 0.00;
        $overflowHours = $roundedHours - $lastDuration;
        return round($lastPrice + $overflowHours * $overflowRate, 2);
    }
}

