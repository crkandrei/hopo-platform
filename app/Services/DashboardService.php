<?php

namespace App\Services;

use App\Models\Child;
use App\Models\PlaySession;
use App\Models\StandaloneReceipt;
use App\Repositories\Contracts\PlaySessionRepositoryInterface;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(
        private PlaySessionRepositoryInterface $sessions,
    ) {
    }

    public function getStatsForLocation(int $locationId): array
    {
        $now         = now();
        $startOfDay  = $now->copy()->startOfDay();
        $endOfDay    = $now->copy()->endOfDay();
        $lastWeekStart = $now->copy()->subWeek()->startOfDay();
        $lastWeekEnd   = $now->copy()->subWeek()->endOfDay();
        $dayName       = $this->getRomanianDayName($now->dayOfWeek);

        // ── 1 query: active sessions (needed for the live counter) ─────────────
        $activeSessions = $this->sessions->countActiveSessionsByLocation($locationId);

        // ── 1 query: all today's session aggregates ────────────────────────────
        // Replaces: countSessionsStartedSince + countActiveSessionsStartedSince
        //           + getSessionsSince (for avg + total time)
        // avg_seconds excludes sessions under 10 minutes (600s) — too short to be meaningful
        $todayAgg = PlaySession::where('location_id', $locationId)
            ->where('started_at', '>=', $startOfDay)
            ->selectRaw('
                COUNT(*)                                                                                        AS total,
                SUM(ended_at IS NULL)                                                                           AS in_progress,
                SUM(TIMESTAMPDIFF(SECOND, started_at, COALESCE(ended_at, NOW())))                               AS total_seconds,
                AVG(CASE WHEN TIMESTAMPDIFF(SECOND, started_at, COALESCE(ended_at, NOW())) >= 600
                         THEN TIMESTAMPDIFF(SECOND, started_at, COALESCE(ended_at, NOW()))
                         ELSE NULL END)                                                                         AS avg_seconds
            ')
            ->first();

        $sessionsToday   = (int) ($todayAgg->total       ?? 0);
        $inProgressToday = (int) ($todayAgg->in_progress ?? 0);
        $totalMinutesToday = (int) floor(($todayAgg->total_seconds ?? 0) / 60);
        $avgToday          = $todayAgg->avg_seconds !== null
            ? (int) floor((float) $todayAgg->avg_seconds / 60)
            : 0;
        $normalSessions    = $sessionsToday;

        // ── 1 query: all-time average duration ─────────────────────────────────
        // Replaces: getAllByLocation() which loaded the entire table into memory.
        // Excludes sessions under 10 minutes (600s) — consistent with daily average logic.
        $avgSecondsAll = PlaySession::where('location_id', $locationId)
            ->whereNotNull('ended_at')
            ->selectRaw('AVG(CASE WHEN TIMESTAMPDIFF(SECOND, started_at, ended_at) >= 600
                                  THEN TIMESTAMPDIFF(SECOND, started_at, ended_at)
                                  ELSE NULL END) AS avg_seconds')
            ->value('avg_seconds');
        $avgAll = (int) floor(($avgSecondsAll ?? 0) / 60);

        // ── 1 query: sessions ended today for income calculation ───────────────
        // with('products') added to prevent N+1 on getProductsTotalPrice().
        $sessionsEndedToday = PlaySession::where('location_id', $locationId)
            ->whereNotNull('ended_at')
            ->whereNotNull('calculated_price')
            ->where('ended_at', '>=', $startOfDay)
            ->where('ended_at', '<=', $endOfDay)
            ->with(['products', 'voucher', 'voucherUsages'])
            ->get();

        $cashTotal    = 0;
        $cardTotal    = 0;
        $voucherTotal = 0;

        foreach ($sessionsEndedToday as $session) {
            if ($session->isPaid() && ! $session->is_free) {
                $timePrice     = $session->calculated_price ?? $session->calculatePrice();
                $productsPrice = $session->getProductsTotalPrice(); // no query — products eager loaded
                $totalPrice    = $timePrice + $productsPrice;
                $voucherPrice  = $session->getVoucherDiscount();

                if ($voucherPrice > 0) {
                    $voucherTotal += $voucherPrice;
                }

                $amountCollected = $totalPrice - $voucherPrice;

                if ($session->payment_method === 'CASH') {
                    $cashTotal += $amountCollected;
                } elseif ($session->payment_method === 'CARD') {
                    $cardTotal += $amountCollected;
                } else {
                    if ($amountCollected > 0) {
                        $cashTotal += $amountCollected;
                    }
                }
            }
        }

        // ── 1 query: standalone receipts paid today ────────────────────────────
        $standaloneToday = StandaloneReceipt::where('location_id', $locationId)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$startOfDay, $endOfDay])
            ->with('voucherUsages')
            ->get();

        foreach ($standaloneToday as $receipt) {
            $amountCollected = max(0.0, (float) $receipt->total_amount - $receipt->getVoucherDiscount());
            if ($receipt->payment_method === 'CASH') {
                $cashTotal += $amountCollected;
            } elseif ($receipt->payment_method === 'CARD') {
                $cardTotal += $amountCollected;
            } else {
                $cashTotal += $amountCollected;
            }
        }

        // Total = cash + card only (voucher is not real money collected)
        $totalIncomeToday = $cashTotal + $cardTotal;

        // ── 1 query: same day last week count for comparison ───────────────────
        $sessionsLastWeek = PlaySession::where('location_id', $locationId)
            ->where('started_at', '>=', $lastWeekStart)
            ->where('started_at', '<=', $lastWeekEnd)
            ->count();

        $incomeLastWeek     = $this->calculateIncomeForPeriod($locationId, $lastWeekStart, $lastWeekEnd);
        $sessionsComparison = $this->calculateComparison($sessionsToday, $sessionsLastWeek);
        $incomeComparison   = $this->calculateComparison($totalIncomeToday, $incomeLastWeek);

        return [
            'active_sessions'           => $activeSessions,
            'sessions_today'            => $sessionsToday,
            'sessions_today_in_progress' => $inProgressToday,
            'sessions_normal'           => $normalSessions,
            'avg_session_today_minutes' => $avgToday,
            'avg_session_total_minutes' => $avgAll,
            'total_time_today'          => $this->formatMinutes($totalMinutesToday),
            'total_income_today'        => round($totalIncomeToday, 2),
            'cash_total'                => round($cashTotal, 2),
            'card_total'                => round($cardTotal, 2),
            'voucher_total'             => round($voucherTotal, 2),
            'comparison_day_name'       => $dayName,
            'sessions_last_week'        => $sessionsLastWeek,
            'sessions_comparison'       => $sessionsComparison,
            'income_last_week'          => round($incomeLastWeek, 2),
            'income_comparison'         => $incomeComparison,
        ];
    }

    /**
     * Calculate cash+card income for a specific period (excludes voucher value).
     * Used for week-over-week comparison — must be consistent with getStatsForLocation().
     *
     * with('products') prevents N+1 when getProductsTotalPrice() is called per session.
     */
    private function calculateIncomeForPeriod(int $locationId, Carbon $start, Carbon $end): float
    {
        $sessions = PlaySession::where('location_id', $locationId)
            ->whereNotNull('ended_at')
            ->whereNotNull('calculated_price')
            ->where('ended_at', '>=', $start)
            ->where('ended_at', '<=', $end)
            ->with(['products', 'voucher', 'voucherUsages'])
            ->get();

        $total = 0;
        foreach ($sessions as $session) {
            if ($session->isPaid() && ! $session->is_free) {
                $timePrice     = $session->calculated_price ?? 0;
                $productsPrice = $session->getProductsTotalPrice(); // no query — products eager loaded
                $voucherPrice  = $session->getVoucherDiscount();
                // Subtract voucher: only cash+card is real income
                $total        += $timePrice + $productsPrice - $voucherPrice;
            }
        }

        // Standalone receipts: subtract voucher discount to get only cash+card amount
        $standaloneReceipts = StandaloneReceipt::where('location_id', $locationId)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end])
            ->get();

        foreach ($standaloneReceipts as $receipt) {
            $total += max(0.0, (float) $receipt->total_amount - $receipt->getVoucherDiscount());
        }

        return $total;
    }

    /**
     * Calculate comparison percentage between current and previous values.
     */
    private function calculateComparison(float $current, float $previous): array
    {
        if ($previous == 0) {
            return [
                'percent'   => $current > 0 ? 100 : 0,
                'direction' => $current > 0 ? 'up' : 'neutral',
                'diff'      => $current,
            ];
        }

        $diff    = $current - $previous;
        $percent = round(($diff / $previous) * 100, 1);

        return [
            'percent'   => abs($percent),
            'direction' => $percent > 0 ? 'up' : ($percent < 0 ? 'down' : 'neutral'),
            'diff'      => $diff,
        ];
    }

    /**
     * Get Romanian day name.
     */
    private function getRomanianDayName(int $dayOfWeek): string
    {
        return [
            0 => 'Duminică',
            1 => 'Luni',
            2 => 'Marți',
            3 => 'Miercuri',
            4 => 'Joi',
            5 => 'Vineri',
            6 => 'Sâmbătă',
        ][$dayOfWeek] ?? '';
    }

    public function getActiveSessions(int $locationId): array
    {
        return $this->sessions->getActiveSessionsWithRelations($locationId)
            ->map(function ($session) {
                $child    = $session->child;
                $guardian = $child ? $child->guardian : null;

                return [
                    'id'               => $session->id,
                    'child_name'       => $child ? $child->name : '-',
                    'parent_name'      => $guardian->name ?? '-',
                    'started_at'       => $session->started_at ? $session->started_at->toISOString() : null,
                    'duration'         => $session->getFormattedDuration(),
                    'duration_minutes' => $session->getCurrentDurationMinutes(),
                    'bracelet_code'    => $session->bracelet_code ?? null,
                    'is_paused'        => $session->isPaused(),
                ];
            })
            ->toArray();
    }

    /**
     * Get alerts for the dashboard (unpaid sessions, long running sessions).
     */
    public function getAlerts(int $locationId): array
    {
        $alerts     = [];
        $startOfDay = now()->startOfDay();

        // 1. Unpaid sessions — ended today but not yet paid
        $unpaidSessions = PlaySession::where('location_id', $locationId)
            ->whereNotNull('ended_at')
            ->whereNull('paid_at')
            ->where('ended_at', '>=', $startOfDay)
            ->with('child')
            ->get();

        if ($unpaidSessions->count() > 0) {
            $unpaidDetails = $unpaidSessions->map(fn($session) => [
                'id'         => $session->id,
                'child_name' => $session->child->name ?? 'Necunoscut',
                'price'      => $session->calculated_price ?? $session->calculatePrice(),
            ])->toArray();

            $alerts[] = [
                'type'     => 'unpaid',
                'severity' => 'warning',
                'icon'     => 'fa-exclamation-triangle',
                'title'    => $unpaidSessions->count() . ' sesiuni neplătite',
                'message'  => 'Sesiuni finalizate azi care nu au fost încă plătite.',
                'count'    => $unpaidSessions->count(),
                'details'  => $unpaidDetails,
            ];
        }

        // 2. Long running sessions — active sessions over 4 hours.
        // Filter pushed to SQL instead of loading all active sessions and filtering in PHP.
        $longSessions = PlaySession::where('location_id', $locationId)
            ->whereNull('ended_at')
            ->where('started_at', '<=', now()->subMinutes(240))
            ->with('child')
            ->get();

        if ($longSessions->count() > 0) {
            $longDetails = $longSessions->map(fn($session) => [
                'id'               => $session->id,
                'child_name'       => $session->child->name ?? 'Necunoscut',
                'duration'         => $session->getFormattedDuration(),
                'duration_minutes' => $session->getCurrentDurationMinutes(),
            ])->toArray();

            $alerts[] = [
                'type'     => 'long_session',
                'severity' => 'info',
                'icon'     => 'fa-clock',
                'title'    => $longSessions->count() . ' sesiuni foarte lungi',
                'message'  => 'Sesiuni active de peste 4 ore - verifică dacă totul e în regulă.',
                'count'    => $longSessions->count(),
                'details'  => $longDetails,
            ];
        }

        return $alerts;
    }

    public function getReports(int $locationId, ?string $startDate = null, ?string $endDate = null, ?array $weekdays = null): array
    {
        $now   = now();
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : $now->copy()->startOfDay();
        $end   = $endDate   ? Carbon::parse($endDate)->endOfDay()     : $now->copy()->endOfDay();

        if ($end->lessThan($start)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        $sessionsQuery = PlaySession::where('location_id', $locationId)
            ->where('started_at', '>=', $start)
            ->where('started_at', '<=', $end)
            ->with('intervals'); // prevent N+1 on getCurrentDurationMinutes()

        if ($weekdays && ! empty($weekdays)) {
            $weekdaysArray = array_map('intval', $weekdays);
            // MySQL DAYOFWEEK: 1=Sunday … 7=Saturday; Carbon dayOfWeek: 0=Sunday … 6=Saturday
            $mysqlWeekdays = array_map(fn($day) => $day + 1, $weekdaysArray);
            $placeholders  = implode(',', array_fill(0, count($mysqlWeekdays), '?'));
            $sessionsQuery->whereRaw("DAYOFWEEK(started_at) IN ({$placeholders})", $mysqlWeekdays);
        }

        $sessionsToday = $sessionsQuery->get();
        $totalToday    = $sessionsToday->count();

        $buckets = ['<1h' => 0, '1-2h' => 0, '2-3h' => 0, '>3h' => 0];

        foreach ($sessionsToday as $s) {
            $mins = $s->getCurrentDurationMinutes();

            if ($mins < 60)       { $buckets['<1h']++;  }
            elseif ($mins < 120)  { $buckets['1-2h']++; }
            elseif ($mins < 180)  { $buckets['2-3h']++; }
            else                  { $buckets['>3h']++;  }
        }

        $bucketPerc = [];
        foreach ($buckets as $k => $v) {
            $bucketPerc[$k] = $totalToday > 0 ? round(($v / $totalToday) * 100, 1) : 0.0;
        }

        // ── 1 query: average child age ─────────────────────────────────────────
        // Replaces: getAllWithBirthdateByLocation() which loaded every child into memory
        // just to run a reduce() in PHP. SQL does this in a single aggregate.
        $avgMonthsRaw = Child::where('location_id', $locationId)
            ->whereNotNull('birth_date')
            ->selectRaw('AVG(TIMESTAMPDIFF(MONTH, birth_date, NOW())) AS avg_months')
            ->value('avg_months');

        $avgAgeYears  = 0;
        $avgAgeMonths = 0;

        if ($avgMonthsRaw !== null) {
            $avgMonths    = (int) round($avgMonthsRaw);
            $avgAgeYears  = (int) ($avgMonths / 12);
            $avgAgeMonths = $avgMonths % 12;
        }

        // Unique vs recurring visitors within the selected range
        $byChild = [];
        foreach ($sessionsToday as $sess) {
            if (! $sess->child_id) {
                continue;
            }
            $byChild[$sess->child_id] = ($byChild[$sess->child_id] ?? 0) + 1;
        }

        $uniqueVisitors    = 0;
        $recurringVisitors = 0;
        foreach ($byChild as $count) {
            if ($count <= 1) {
                $uniqueVisitors++;
            } else {
                $recurringVisitors++;
            }
        }

        $totalVisitors = max(1, $uniqueVisitors + $recurringVisitors);
        $visitorDist   = [
            'unique'    => ['count' => $uniqueVisitors,    'percent' => round(($uniqueVisitors    / $totalVisitors) * 100, 1)],
            'recurring' => ['count' => $recurringVisitors, 'percent' => round(($recurringVisitors / $totalVisitors) * 100, 1)],
        ];

        // Hourly traffic: count sessions by hour of started_at (0-23)
        $hourlyTraffic = array_fill(0, 24, 0);
        foreach ($sessionsToday as $sess) {
            if ($sess->started_at) {
                $hourlyTraffic[(int) $sess->started_at->format('H')]++;
            }
        }

        return [
            'total_today'         => $totalToday,
            'buckets_today'       => [
                'lt_1h' => ['count' => $buckets['<1h'],  'percent' => $bucketPerc['<1h']],
                'h1_2'  => ['count' => $buckets['1-2h'], 'percent' => $bucketPerc['1-2h']],
                'h2_3'  => ['count' => $buckets['2-3h'], 'percent' => $bucketPerc['2-3h']],
                'gt_3h' => ['count' => $buckets['>3h'],  'percent' => $bucketPerc['>3h']],
            ],
            'avg_child_age_years'   => $avgAgeYears,
            'avg_child_age_months'  => $avgAgeMonths,
            'visitor_distribution'  => $visitorDist,
            'hourly_traffic'        => $hourlyTraffic,
        ];
    }

    /**
     * Get entries (session starts) over time by period type.
     *
     * Replaces N individual COUNT queries (one per period) with a single GROUP BY query.
     *
     * @param int    $locationId
     * @param string $periodType 'daily', 'weekly', or 'monthly'
     * @param int    $count      Number of periods to show
     */
    public function getEntriesOverTime(int $locationId, string $periodType, int $count): array
    {
        $now    = now();
        $labels = [];
        $data   = [];
        $growth = [];

        if ($periodType === 'daily') {
            $startRange  = $now->copy()->subDays($count - 1)->startOfDay();
            $countsByDay = PlaySession::where('location_id', $locationId)
                ->where('started_at', '>=', $startRange)
                ->selectRaw("DATE(started_at) AS period, COUNT(*) AS cnt")
                ->groupBy('period')
                ->pluck('cnt', 'period'); // keyed by 'YYYY-MM-DD'

            for ($i = $count - 1; $i >= 0; $i--) {
                $date    = $now->copy()->subDays($i)->startOfDay();
                $entries = (int) ($countsByDay->get($date->toDateString(), 0));
                $labels[] = $date->format('d.m.Y');
                $data[]   = $entries;
                $growth[] = $this->calculateGrowthPercent($data);
            }
        } elseif ($periodType === 'weekly') {
            // YEARWEEK with mode 3 = ISO 8601 (week starts Monday, year of majority of week)
            $startRange   = $now->copy()->subWeeks($count - 1)->startOfWeek(Carbon::MONDAY);
            $countsByWeek = PlaySession::where('location_id', $locationId)
                ->where('started_at', '>=', $startRange)
                ->selectRaw("YEARWEEK(started_at, 3) AS period, COUNT(*) AS cnt")
                ->groupBy('period')
                ->pluck('cnt', 'period'); // keyed by numeric YYYYWW

            for ($i = $count - 1; $i >= 0; $i--) {
                $weekStart = $now->copy()->subWeeks($i)->startOfWeek(Carbon::MONDAY);
                $weekEnd   = $weekStart->copy()->endOfWeek();
                // ISO year + zero-padded week number matches YEARWEEK(..., 3)
                $yearWeek  = (int) $weekStart->format('oW');
                $entries   = (int) ($countsByWeek->get($yearWeek, 0));
                $labels[]  = $weekStart->format('d.m') . ' - ' . $weekEnd->format('d.m.Y');
                $data[]    = $entries;
                $growth[]  = $this->calculateGrowthPercent($data);
            }
        } elseif ($periodType === 'monthly') {
            $startRange    = $now->copy()->subMonths($count - 1)->startOfMonth();
            $countsByMonth = PlaySession::where('location_id', $locationId)
                ->where('started_at', '>=', $startRange)
                ->selectRaw("DATE_FORMAT(started_at, '%Y%m') AS period, COUNT(*) AS cnt")
                ->groupBy('period')
                ->pluck('cnt', 'period'); // keyed by 'YYYYMM'

            for ($i = $count - 1; $i >= 0; $i--) {
                $monthStart = $now->copy()->subMonths($i)->startOfMonth();
                // PHP date('Ym') and MySQL DATE_FORMAT('%Y%m') produce the same 'YYYYMM' string
                $ym       = $monthStart->format('Ym');
                $entries  = (int) ($countsByMonth->get($ym, 0));
                $labels[] = $monthStart->format('m.Y');
                $data[]   = $entries;
                $growth[] = $this->calculateGrowthPercent($data);
            }
        }

        return [
            'labels' => $labels,
            'data'   => $data,
            'growth' => $growth,
        ];
    }

    /**
     * Compute growth percentage for the last two entries in $data.
     * Extracted to avoid duplicating the identical logic across all three period branches.
     */
    private function calculateGrowthPercent(array $data): float
    {
        $count = count($data);

        if ($count < 2) {
            return 0.0;
        }

        $current  = $data[$count - 1];
        $previous = $data[$count - 2];

        if ($previous > 0) {
            return round((($current - $previous) / $previous) * 100, 1);
        }

        return $current > 0 ? 100.0 : 0.0;
    }

    private function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $rem   = $minutes % 60;
        return sprintf('%dh %dm', $hours, $rem);
    }
}
