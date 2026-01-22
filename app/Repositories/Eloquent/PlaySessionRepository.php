<?php

namespace App\Repositories\Eloquent;

use App\Models\PlaySession;
use App\Repositories\Contracts\PlaySessionRepositoryInterface;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PlaySessionRepository implements PlaySessionRepositoryInterface
{
    public function countActiveSessionsByLocation(int $locationId): int
    {
        return PlaySession::where('location_id', $locationId)
            ->whereNull('ended_at')
            ->count();
    }

    public function countSessionsStartedSince(int $locationId, Carbon $since): int
    {
        return PlaySession::where('location_id', $locationId)
            ->where('started_at', '>=', $since)
            ->count();
    }

    public function countActiveSessionsStartedSince(int $locationId, Carbon $since): int
    {
        return PlaySession::where('location_id', $locationId)
            ->where('started_at', '>=', $since)
            ->whereNull('ended_at')
            ->count();
    }

    public function getSessionsSince(int $locationId, Carbon $since): Collection
    {
        return PlaySession::where('location_id', $locationId)
            ->where('started_at', '>=', $since)
            ->get();
    }

    public function getSessionsBetween(int $locationId, Carbon $start, Carbon $end): Collection
    {
        return PlaySession::where('location_id', $locationId)
            ->where('started_at', '>=', $start)
            ->where('started_at', '<=', $end)
            ->get();
    }

    public function getAllByLocation(int $locationId): Collection
    {
        return PlaySession::where('location_id', $locationId)->get();
    }

    public function getActiveSessionsWithRelations(int $locationId): Collection
    {
        return PlaySession::where('location_id', $locationId)
            ->whereNull('ended_at')
            ->with(['child.guardian'])
            ->get();
    }

    public function paginateSessions(
        int $locationId,
        int $page,
        int $perPage,
        ?string $search,
        string $sortBy,
        string $sortDir,
        Carbon $date
    ): array {
        $sortable = [
            'child_name' => 'children.name',
            'guardian_name' => 'guardians.name',
            'guardian_phone' => 'guardians.phone',
            'started_at' => 'play_sessions.started_at',
            'ended_at' => 'play_sessions.ended_at',
        ];
        $sortColumn = $sortable[$sortBy] ?? 'play_sessions.started_at';

        $dateStart = $date->copy()->startOfDay();
        $dateEnd = $date->copy()->endOfDay();
        
        $query = PlaySession::query()
            ->where('play_sessions.location_id', $locationId)
            ->whereBetween('play_sessions.started_at', [$dateStart, $dateEnd])
            ->leftJoin('children', 'children.id', '=', 'play_sessions.child_id')
            ->leftJoin('guardians', 'guardians.id', '=', 'children.guardian_id')
            ->select([
                'play_sessions.id',
                'play_sessions.started_at',
                'play_sessions.ended_at',
                'play_sessions.status',
                'play_sessions.calculated_price',
                'play_sessions.price_per_hour_at_calculation',
                'play_sessions.paid_at',
                'play_sessions.payment_status',
                'play_sessions.voucher_hours',
                'children.name as child_name',
                'guardians.name as guardian_name',
                'guardians.phone as guardian_phone',
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('children.name', 'like', "%{$search}%")
                    ->orWhere('guardians.name', 'like', "%{$search}%")
                    ->orWhere('guardians.phone', 'like', "%{$search}%");
            });
        }

        $total = (clone $query)->count();

        $rows = $query
            ->orderByRaw('play_sessions.ended_at IS NULL DESC') // Active sessions first
            ->orderByRaw($sortColumn . ' ' . $sortDir)
            ->forPage($page, $perPage)
            ->get()
            ->map(function ($row) {
                $childName = $row->child_name ?? '';
                // Load full session to compute effective time and pause state
                $ps = \App\Models\PlaySession::with(['intervals', 'products'])->find($row->id);
                
                // For ended sessions, use total effective time
                // For active sessions, use ONLY closed intervals (frontend will add current interval)
                if ($row->ended_at) {
                    $effectiveSeconds = $ps ? $ps->getEffectiveDurationSeconds() : 0;
                } else {
                    $effectiveSeconds = $ps ? $ps->getClosedIntervalsDurationSeconds() : 0;
                }
                
                $isPaused = $ps ? $ps->isPaused() : false;
                $currentStart = null;
                $lastPauseEnd = null; // When did the current pause start (last interval ended_at)
                if ($ps && !$row->ended_at) {
                    if ($isPaused) {
                        // Get the last closed interval's ended_at (when pause started)
                        $lastClosedInterval = $ps->intervals()->whereNotNull('ended_at')->latest('ended_at')->first();
                        if ($lastClosedInterval && $lastClosedInterval->ended_at) {
                            $lastPauseEnd = $lastClosedInterval->ended_at->toISOString();
                        }
                    } else {
                        $open = $ps->intervals()->whereNull('ended_at')->latest('started_at')->first();
                        if ($open && $open->started_at) {
                            $currentStart = $open->started_at->toISOString();
                        }
                    }
                }
                
                // Calculate price - use saved price for completed sessions, calculate for active ones
                $price = null;
                $formattedPrice = null;
                if ($row->ended_at && $row->calculated_price !== null) {
                    // Use saved price for completed sessions
                    $price = (float) $row->calculated_price;
                    $formattedPrice = $ps ? $ps->getFormattedPrice() : number_format($price, 2, '.', '') . ' RON';
                } elseif ($ps) {
                    // Calculate estimated price for active sessions
                    $price = $ps->calculatePrice();
                    $formattedPrice = $ps->getFormattedPrice();
                }
                
                // Get products total price
                $productsPrice = 0;
                $productsFormattedPrice = '';
                if ($ps) {
                    $productsPrice = $ps->getProductsTotalPrice();
                    if ($productsPrice > 0) {
                        $productsFormattedPrice = number_format($productsPrice, 2, '.', '') . ' RON';
                    }
                }
                
                // Get current pause duration ONLY if session is currently paused
                // We only care about the CURRENT active pause, not historical pauses
                $currentPauseMinutes = 0;
                if ($ps && $isPaused && !$row->ended_at) {
                    $currentPauseMinutes = $ps->getCurrentPauseMinutes();
                }
                
                // Get pause warning threshold from configuration
                // TODO: Implement location-specific configuration if needed
                $pauseThreshold = 15; // Default
                
                // Only show badge if session is currently paused AND current pause exceeds threshold
                // We don't care about historical pauses, only the current active pause
                $hasLongPause = $isPaused && !$row->ended_at && $currentPauseMinutes >= $pauseThreshold;
                $currentPauseExceedsThreshold = $currentPauseMinutes >= $pauseThreshold;
                
                return [
                    'id' => $row->id,
                    'child_name' => $childName,
                    'guardian_name' => $row->guardian_name,
                    'guardian_phone' => $row->guardian_phone,
                    'started_at' => optional($row->started_at)->toISOString(),
                    'ended_at' => optional($row->ended_at)->toISOString(),
                    'status' => $row->status,
                    'is_paused' => $isPaused,
                    'effective_seconds' => $effectiveSeconds,
                    'current_interval_started_at' => $currentStart,
                    'last_pause_end' => $lastPauseEnd,
                    'price' => $price,
                    'formatted_price' => $formattedPrice,
                    'products_price' => (float) $productsPrice,
                    'products_formatted_price' => $productsFormattedPrice,
                    'price_per_hour_at_calculation' => $row->price_per_hour_at_calculation ? (float) $row->price_per_hour_at_calculation : null,
                    'current_pause_minutes' => $currentPauseMinutes,
                    'has_long_pause' => $hasLongPause,
                    'current_pause_exceeds_threshold' => $currentPauseExceedsThreshold,
                    'pause_threshold' => (int) $pauseThreshold,
                    'paid_at' => optional($row->paid_at)->toISOString(),
                    'is_paid' => !is_null($row->paid_at),
                    'payment_status' => $row->payment_status ?? null,
                    'voucher_hours' => $row->voucher_hours ? (float) $row->voucher_hours : null,
                ];
            });

        return [ 'total' => $total, 'rows' => $rows ];
    }
}


