<?php

namespace App\Models;

use App\Models\Traits\BelongsToLocation;
use App\Services\PricingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PlaySession extends Model
{
    use HasFactory, BelongsToLocation;
    public $timestamps = false;
    protected $fillable = [
        'location_id',
        'child_id',
        'bracelet_code',
        'started_at',
        'ended_at',
        'calculated_price',
        'price_per_hour_at_calculation',
        'paid_at',
        'voucher_hours',
        'payment_status',
        'payment_method',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'calculated_price' => 'decimal:2',
        'price_per_hour_at_calculation' => 'decimal:2',
        'paid_at' => 'datetime',
        'voucher_hours' => 'decimal:2',
    ];


    /**
     * Get the child for this play session.
     */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }


    /** Intervals for this session */
    public function intervals(): HasMany
    {
        return $this->hasMany(PlaySessionInterval::class);
    }

    /** Products for this session */
    public function products(): HasMany
    {
        return $this->hasMany(PlaySessionProduct::class);
    }

    /** Determine if the session is currently active. */
    public function isActive(): bool
    {
        return is_null($this->ended_at);
    }

    /** Determine if the session is currently paused. */
    public function isPaused(): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        // Paused = active session with no open interval
        return !$this->intervals()->whereNull('ended_at')->exists();
    }

    /** Determine if the session has been paid. */
    public function isPaid(): bool
    {
        return !is_null($this->paid_at);
    }

    /**
     * Get the current duration in minutes
     */
    public function getCurrentDurationMinutes(): int
    {
        return (int) floor($this->getEffectiveDurationSeconds() / 60);
    }

    /** Total effective duration excluding pauses, in seconds. */
    public function getEffectiveDurationSeconds(): int
    {
        $seconds = 0;
        $intervals = $this->relationLoaded('intervals') ? $this->intervals : $this->intervals()->get();
        
        // If we have intervals, sum their durations (this excludes pauses)
        if ($intervals->isNotEmpty()) {
            foreach ($intervals as $iv) {
                $end = $iv->ended_at ?: now();
                if ($iv->started_at) {
                    $seconds += $iv->started_at->diffInSeconds($end);
                }
            }
        } else {
            // Fallback: if no intervals exist (shouldn't happen for new sessions, but might for old data),
            // use the session's started_at and ended_at as a single interval
            if ($this->started_at) {
                $end = $this->ended_at ?: now();
                $seconds = $this->started_at->diffInSeconds($end);
            }
        }
        
        return $seconds;
    }

    /** 
     * Get effective duration in seconds for CLOSED intervals only.
     * Excludes the current active interval (if any).
     * This is useful for live timer display on frontend.
     */
    public function getClosedIntervalsDurationSeconds(): int
    {
        $seconds = 0;
        $intervals = $this->relationLoaded('intervals') ? $this->intervals : $this->intervals()->get();
        foreach ($intervals as $iv) {
            // Only count closed intervals
            if ($iv->ended_at && $iv->started_at) {
                $seconds += $iv->started_at->diffInSeconds($iv->ended_at);
            }
        }
        return $seconds;
    }

    /**
     * Get the maximum pause duration in minutes between intervals
     * Returns the longest gap between consecutive intervals
     * Includes current pause if session is paused
     * 
     * @return int Maximum pause duration in minutes (0 if no pauses or only one interval)
     */
    public function getMaxPauseMinutes(): int
    {
        $intervals = $this->relationLoaded('intervals') ? $this->intervals : $this->intervals()->get();
        
        if ($intervals->count() < 2 && !$this->isPaused()) {
            return 0; // No pauses if less than 2 intervals and not paused
        }

        // Sort intervals by started_at
        $sortedIntervals = $intervals->sortBy('started_at')->values();
        
        $maxPauseMinutes = 0;
        
        // Check gaps between consecutive intervals
        for ($i = 0; $i < $sortedIntervals->count() - 1; $i++) {
            $currentInterval = $sortedIntervals[$i];
            $nextInterval = $sortedIntervals[$i + 1];
            
            // Both intervals must be closed to calculate pause
            if ($currentInterval->ended_at && $nextInterval->started_at) {
                $pauseSeconds = $currentInterval->ended_at->diffInSeconds($nextInterval->started_at);
                $pauseMinutes = (int) floor($pauseSeconds / 60);
                
                if ($pauseMinutes > $maxPauseMinutes) {
                    $maxPauseMinutes = $pauseMinutes;
                }
            }
        }
        
        // If session is currently paused, check current pause duration
        if ($this->isPaused() && $sortedIntervals->count() > 0) {
            $lastInterval = $sortedIntervals->last();
            if ($lastInterval && $lastInterval->ended_at) {
                $currentPauseSeconds = $lastInterval->ended_at->diffInSeconds(now());
                $currentPauseMinutes = (int) floor($currentPauseSeconds / 60);
                
                if ($currentPauseMinutes > $maxPauseMinutes) {
                    $maxPauseMinutes = $currentPauseMinutes;
                }
            }
        }
        
        return $maxPauseMinutes;
    }

    /**
     * Get current pause duration in minutes if session is paused
     * Returns 0 if session is not paused
     * 
     * @return int Current pause duration in minutes
     */
    public function getCurrentPauseMinutes(): int
    {
        if (!$this->isPaused()) {
            return 0;
        }

        $intervals = $this->relationLoaded('intervals') ? $this->intervals : $this->intervals()->get();
        $sortedIntervals = $intervals->sortBy('started_at')->values();
        
        if ($sortedIntervals->count() === 0) {
            return 0;
        }

        $lastInterval = $sortedIntervals->last();
        if ($lastInterval && $lastInterval->ended_at) {
            $currentPauseSeconds = $lastInterval->ended_at->diffInSeconds(now());
            return (int) floor($currentPauseSeconds / 60);
        }
        
        return 0;
    }

    /**
     * Get formatted duration string
     */
    public function getFormattedDuration(): string
    {
        $minutes = $this->getCurrentDurationMinutes();
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $remainingMinutes);
        }
        
        return sprintf('%dm', $remainingMinutes);
    }

    /**
     * Start a new play session
     */
    public static function startSession(Location $location, Child $child, ?string $braceletCode): self
    {
        $session = self::create([
            'location_id' => $location->id,
            'child_id' => $child->id,
            'bracelet_code' => $braceletCode,
            'started_at' => now(),
        ]);

        // Create initial open interval
        $session->intervals()->create([
            'started_at' => $session->started_at,
        ]);

        return $session;
    }

    /**
     * End the play session
     */
    public function endSession(): self
    {
        $now = now();
        // Close any open interval
        $open = $this->intervals()->whereNull('ended_at')->latest('started_at')->first();
        if ($open) {
            $duration = $open->started_at ? $open->started_at->diffInSeconds($now) : null;
            $open->update([
                'ended_at' => $now,
                'duration_seconds' => $duration,
            ]);
        }

        $this->update([
            'ended_at' => $now,
        ]);

        // Refresh intervals relationship to ensure we have the latest data including the just-closed interval
        $this->unsetRelation('intervals');

        // Calculate and save price
        $this->saveCalculatedPrice();

        return $this;
    }

    /** Pause the session by closing the open interval */
    public function pause(): self
    {
        if (!$this->isActive()) {
            throw new \Exception('Sesiunea este deja închisă');
        }
        if ($this->isPaused()) {
            return $this; // already paused
        }
        $now = now();
        $open = $this->intervals()->whereNull('ended_at')->latest('started_at')->first();
        if ($open) {
            $duration = $open->started_at ? $open->started_at->diffInSeconds($now) : null;
            $open->update([
                'ended_at' => $now,
                'duration_seconds' => $duration,
            ]);
        }
        // Session is paused when there's no open interval
        return $this;
    }

    /** Resume the session by starting a new interval */
    public function resume(): self
    {
        if (!$this->isActive()) {
            throw new \Exception('Sesiunea este deja închisă');
        }
        if (!$this->isPaused()) {
            return $this; // already running
        }
        $this->intervals()->create(['started_at' => now()]);
        return $this;
    }

    /**
     * Restart a stopped session (only if not paid)
     * This reactivates the session and creates a new interval
     * Note: started_at is NOT changed to preserve the original hourly rate
     */
    public function restart(): self
    {
        // Verify session is stopped
        if ($this->isActive()) {
            throw new \Exception('Sesiunea este deja activă');
        }

        // Verify session is not paid
        if ($this->isPaid()) {
            throw new \Exception('Nu se poate reporni o sesiune plătită');
        }

        // Reactivate session: clear ended_at and calculated_price
        // Note: started_at stays the same to preserve the original hourly rate
        $this->update([
            'ended_at' => null,
            'calculated_price' => null,
        ]);

        // Create a new interval for the continued play
        $this->intervals()->create([
            'started_at' => now(),
        ]);

        return $this;
    }

    /**
     * Calculate the price for this session
     * Uses PricingService to calculate based on effective duration and tenant's hourly rate
     * 
     * @return float The calculated price in RON
     */
    public function calculatePrice(): float
    {
        $pricingService = app(PricingService::class);
        return $pricingService->calculateSessionPrice($this);
    }

    /**
     * Get formatted price string for display
     * 
     * @return string Formatted price (e.g., "25.50 RON")
     */
    public function getFormattedPrice(): string
    {
        $price = $this->calculated_price ?? $this->calculatePrice();
        $pricingService = app(PricingService::class);
        return $pricingService->formatPrice($price);
    }

    /**
     * Calculate total price for all products in this session
     * 
     * @return float Total products price in RON
     */
    public function getProductsTotalPrice(): float
    {
        $products = $this->relationLoaded('products') ? $this->products : $this->products()->get();
        $total = 0;
        foreach ($products as $product) {
            $total += $product->total_price;
        }
        return round($total, 2);
    }

    /**
     * Get total session price including time and products
     * 
     * @return float Total price in RON
     */
    public function getTotalPrice(): float
    {
        $timePrice = $this->calculated_price ?? $this->calculatePrice();
        $productsPrice = $this->getProductsTotalPrice();
        return round($timePrice + $productsPrice, 2);
    }

    /**
     * Get formatted total price string including products
     * 
     * @return string Formatted price (e.g., "35.50 RON")
     */
    public function getFormattedTotalPrice(): string
    {
        $pricingService = app(PricingService::class);
        return $pricingService->formatPrice($this->getTotalPrice());
    }

    /**
     * Save the calculated price with the hourly rate at calculation time
     * This preserves historical pricing even if the tenant's price changes later
     * 
     * @return self
     */
    public function saveCalculatedPrice(): self
    {
        $pricingService = app(PricingService::class);
        $pricingService->calculateAndSavePrice($this);
        return $this;
    }

    /**
     * Get billed duration (rounded hours) formatted string
     * This is the duration that was actually billed (excluding voucher hours if any)
     * 
     * @return string Formatted duration (e.g., "2h 30m" or "1h")
     */
    public function getFormattedBilledDuration(): string
    {
        $pricingService = app(PricingService::class);
        $durationInHours = $pricingService->getDurationInHours($this);
        $roundedHours = $pricingService->roundToHalfHour($durationInHours);
        
        // Subtract voucher hours if any
        $billedHours = max(0, $roundedHours - ($this->voucher_hours ?? 0));
        
        $hoursInt = floor($billedHours);
        $minutesInt = round(($billedHours - $hoursInt) * 60);
        if ($minutesInt >= 60) {
            $hoursInt += 1;
            $minutesInt = 0;
        }
        
        if ($hoursInt === 0 && $minutesInt === 0) {
            return '0m';
        }
        
        if ($hoursInt === 0) {
            return "{$minutesInt}m";
        }
        
        if ($minutesInt === 0) {
            return "{$hoursInt}h";
        }
        
        return "{$hoursInt}h {$minutesInt}m";
    }

    /**
     * Get total billed duration (rounded hours) - before voucher discount
     * 
     * @return string Formatted duration (e.g., "2h 30m" or "1h")
     */
    public function getFormattedTotalBilledDuration(): string
    {
        $pricingService = app(PricingService::class);
        $durationInHours = $pricingService->getDurationInHours($this);
        $roundedHours = $pricingService->roundToHalfHour($durationInHours);
        
        $hoursInt = floor($roundedHours);
        $minutesInt = round(($roundedHours - $hoursInt) * 60);
        if ($minutesInt >= 60) {
            $hoursInt += 1;
            $minutesInt = 0;
        }
        
        if ($hoursInt === 0 && $minutesInt === 0) {
            return '0m';
        }
        
        if ($hoursInt === 0) {
            return "{$minutesInt}m";
        }
        
        if ($minutesInt === 0) {
            return "{$hoursInt}h";
        }
        
        return "{$hoursInt}h {$minutesInt}m";
    }

    /**
     * Get amount collected (cash/card payment)
     * Voucher applies ONLY to time, not to products
     * 
     * @return float Amount collected in RON
     */
    public function getAmountCollected(): float
    {
        if (!$this->isPaid()) {
            return 0.0;
        }
        
        $timePrice = $this->calculated_price ?? $this->calculatePrice();
        $productsPrice = $this->getProductsTotalPrice();
        $voucherPrice = 0.0;
        
        // Calculate voucher price if voucher was used (voucher applies only to time)
        if ($this->voucher_hours && $this->voucher_hours > 0 && $this->price_per_hour_at_calculation) {
            $voucherPrice = $this->voucher_hours * $this->price_per_hour_at_calculation;
        }
        
        // Amount collected = final time price (after voucher) + products price
        // Products are never affected by voucher
        $finalTimePrice = max(0, $timePrice - $voucherPrice);
        return $finalTimePrice + $productsPrice;
    }

    /**
     * Get voucher price if voucher was used
     * 
     * @return float Voucher price in RON
     */
    public function getVoucherPrice(): float
    {
        if (!$this->voucher_hours || $this->voucher_hours <= 0 || !$this->price_per_hour_at_calculation) {
            return 0.0;
        }
        
        return $this->voucher_hours * $this->price_per_hour_at_calculation;
    }
}

