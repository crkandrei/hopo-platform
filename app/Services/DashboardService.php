<?php

namespace App\Services;

use App\Models\PlaySession;
use App\Repositories\Contracts\ChildRepositoryInterface;
use App\Repositories\Contracts\PlaySessionRepositoryInterface;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(
        private PlaySessionRepositoryInterface $sessions,
        private ChildRepositoryInterface $children
    )
    {
    }

    public function getStatsForLocation(int $locationId): array
    {
        $now = now();
        $startOfDay = $now->copy()->startOfDay();
        $endOfDay = $now->copy()->endOfDay();
        
        // Same day last week for comparison
        $lastWeekSameDay = $now->copy()->subWeek();
        $lastWeekStart = $lastWeekSameDay->copy()->startOfDay();
        $lastWeekEnd = $lastWeekSameDay->copy()->endOfDay();
        $dayName = $this->getRomanianDayName($now->dayOfWeek);

        $activeSessions = $this->sessions->countActiveSessionsByLocation($locationId);
        $sessionsToday = $this->sessions->countSessionsStartedSince($locationId, $startOfDay);
        $inProgressToday = $this->sessions->countActiveSessionsStartedSince($locationId, $startOfDay);
        
        $todaySessions = $this->sessions->getSessionsSince($locationId, $startOfDay);
        $totalMinutesToday = $todaySessions->reduce(fn($c, $s) => $c + $s->getCurrentDurationMinutes(), 0);
        $avgToday = $todaySessions->count() > 0 ? (int) floor($totalMinutesToday / $todaySessions->count()) : 0;

        // All sessions are normal (no birthday/jungle types)
        $normalSessions = $todaySessions->count();

        $allSessions = $this->sessions->getAllByLocation($locationId);
        $totalMinutesAll = $allSessions->reduce(fn($c, $s) => $c + $s->getCurrentDurationMinutes(), 0);
        $avgAll = $allSessions->count() > 0 ? (int) floor($totalMinutesAll / $allSessions->count()) : 0;

        // Calculate total income for sessions ended today
        $sessionsEndedToday = PlaySession::where('location_id', $locationId)
            ->whereNotNull('ended_at')
            ->whereNotNull('calculated_price')
            ->where('ended_at', '>=', $startOfDay)
            ->where('ended_at', '<=', $endOfDay)
            ->get();
        
        // Calculate payment breakdown: cash, card, voucher
        $cashTotal = 0;
        $cardTotal = 0;
        $voucherTotal = 0;
        
        foreach ($sessionsEndedToday as $session) {
            if ($session->isPaid()) {
                // Get total price (time + products)
                $timePrice = $session->calculated_price ?? $session->calculatePrice();
                $productsPrice = $session->getProductsTotalPrice();
                $totalPrice = $timePrice + $productsPrice;
                
                $voucherPrice = $session->getVoucherPrice();
                
                // Add voucher value
                if ($voucherPrice > 0) {
                    $voucherTotal += $voucherPrice;
                }
                
                // Amount collected = total price - voucher (voucher applies only to time)
                $amountCollected = $totalPrice - $voucherPrice;
                
                // Add cash/card amount based on payment method
                if ($session->payment_method === 'CASH') {
                    $cashTotal += $amountCollected;
                } elseif ($session->payment_method === 'CARD') {
                    $cardTotal += $amountCollected;
                } else {
                    // If no payment method specified but session is paid, assume it's cash
                    // This handles legacy data or sessions paid without fiscal receipt
                    if ($amountCollected > 0) {
                        $cashTotal += $amountCollected;
                    }
                }
            }
        }
        
        // Total income = cash + card + voucher (total value, not just collected)
        $totalIncomeToday = $cashTotal + $cardTotal + $voucherTotal;

        // Get same day last week stats for comparison
        $sessionsLastWeek = PlaySession::where('location_id', $locationId)
            ->where('started_at', '>=', $lastWeekStart)
            ->where('started_at', '<=', $lastWeekEnd)
            ->count();
        
        $incomeLastWeek = $this->calculateIncomeForPeriod($locationId, $lastWeekStart, $lastWeekEnd);
        
        // Calculate comparison percentages
        $sessionsComparison = $this->calculateComparison($sessionsToday, $sessionsLastWeek);
        $incomeComparison = $this->calculateComparison($totalIncomeToday, $incomeLastWeek);

        return [
            'active_sessions' => $activeSessions,
            'sessions_today' => $sessionsToday,
            'sessions_today_in_progress' => $inProgressToday,
            'sessions_normal' => $normalSessions,
            'avg_session_today_minutes' => $avgToday,
            'avg_session_total_minutes' => $avgAll,
            'total_time_today' => $this->formatMinutes($totalMinutesToday),
            'total_income_today' => round($totalIncomeToday, 2),
            'cash_total' => round($cashTotal, 2),
            'card_total' => round($cardTotal, 2),
            'voucher_total' => round($voucherTotal, 2),
            // Comparison with same day last week
            'comparison_day_name' => $dayName,
            'sessions_last_week' => $sessionsLastWeek,
            'sessions_comparison' => $sessionsComparison,
            'income_last_week' => round($incomeLastWeek, 2),
            'income_comparison' => $incomeComparison,
        ];
    }
    
    /**
     * Calculate income for a specific period
     */
    private function calculateIncomeForPeriod(int $locationId, Carbon $start, Carbon $end): float
    {
        $sessions = PlaySession::where('location_id', $locationId)
            ->whereNotNull('ended_at')
            ->whereNotNull('calculated_price')
            ->where('ended_at', '>=', $start)
            ->where('ended_at', '<=', $end)
            ->get();
        
        $total = 0;
        foreach ($sessions as $session) {
            if ($session->isPaid()) {
                $timePrice = $session->calculated_price ?? 0;
                $productsPrice = $session->getProductsTotalPrice();
                $total += $timePrice + $productsPrice;
            }
        }
        
        return $total;
    }
    
    /**
     * Calculate comparison percentage between current and previous values
     */
    private function calculateComparison(float $current, float $previous): array
    {
        if ($previous == 0) {
            return [
                'percent' => $current > 0 ? 100 : 0,
                'direction' => $current > 0 ? 'up' : 'neutral',
                'diff' => $current,
            ];
        }
        
        $diff = $current - $previous;
        $percent = round(($diff / $previous) * 100, 1);
        
        return [
            'percent' => abs($percent),
            'direction' => $percent > 0 ? 'up' : ($percent < 0 ? 'down' : 'neutral'),
            'diff' => $diff,
        ];
    }
    
    /**
     * Get Romanian day name
     */
    private function getRomanianDayName(int $dayOfWeek): string
    {
        $days = [
            0 => 'Duminică',
            1 => 'Luni',
            2 => 'Marți',
            3 => 'Miercuri',
            4 => 'Joi',
            5 => 'Vineri',
            6 => 'Sâmbătă',
        ];
        return $days[$dayOfWeek] ?? '';
    }

    public function getActiveSessions(int $locationId): array
    {
        return $this->sessions->getActiveSessionsWithRelations($locationId)
            ->map(function ($session) {
                $child = $session->child;
                $guardian = $child ? $child->guardian : null;
                $childName = $child ? $child->name : '-';
                
                return [
                    'id' => $session->id,
                    'child_name' => $childName,
                    'parent_name' => $guardian->name ?? '-',
                    'started_at' => $session->started_at ? $session->started_at->toISOString() : null,
                    'duration' => $session->getFormattedDuration(),
                    'duration_minutes' => $session->getCurrentDurationMinutes(),
                    'bracelet_code' => $session->bracelet_code ?? null,
                    'is_paused' => $session->isPaused(),
                ];
            })
            ->toArray();
    }
    
    /**
     * Get alerts for the dashboard (unpaid sessions, long running sessions)
     */
    public function getAlerts(int $locationId): array
    {
        $alerts = [];
        $now = now();
        $startOfDay = $now->copy()->startOfDay();
        
        // 1. Unpaid sessions - sessions ended today but not paid
        $unpaidSessions = PlaySession::where('location_id', $locationId)
            ->whereNotNull('ended_at')
            ->whereNull('paid_at')
            ->where('ended_at', '>=', $startOfDay)
            ->with('child')
            ->get();
        
        if ($unpaidSessions->count() > 0) {
            $unpaidDetails = $unpaidSessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'child_name' => $session->child->name ?? 'Necunoscut',
                    'price' => $session->calculated_price ?? $session->calculatePrice(),
                ];
            })->toArray();
            
            $alerts[] = [
                'type' => 'unpaid',
                'severity' => 'warning',
                'icon' => 'fa-exclamation-triangle',
                'title' => $unpaidSessions->count() . ' sesiuni neplătite',
                'message' => 'Sesiuni finalizate azi care nu au fost încă plătite.',
                'count' => $unpaidSessions->count(),
                'details' => $unpaidDetails,
            ];
        }
        
        // 2. Long running sessions - active sessions > 4 hours
        $longSessions = PlaySession::where('location_id', $locationId)
            ->whereNull('ended_at')
            ->with('child')
            ->get()
            ->filter(function ($session) {
                return $session->getCurrentDurationMinutes() > 240; // > 4 hours
            });
        
        if ($longSessions->count() > 0) {
            $longDetails = $longSessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'child_name' => $session->child->name ?? 'Necunoscut',
                    'duration' => $session->getFormattedDuration(),
                    'duration_minutes' => $session->getCurrentDurationMinutes(),
                ];
            })->toArray();
            
            $alerts[] = [
                'type' => 'long_session',
                'severity' => 'info',
                'icon' => 'fa-clock',
                'title' => $longSessions->count() . ' sesiuni foarte lungi',
                'message' => 'Sesiuni active de peste 4 ore - verifică dacă totul e în regulă.',
                'count' => $longSessions->count(),
                'details' => $longDetails,
            ];
        }
        
        return $alerts;
    }

    public function getReports(int $locationId, ?string $startDate = null, ?string $endDate = null, ?array $weekdays = null): array
    {
        $now = now();
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : $now->copy()->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : $now->copy()->endOfDay();
        if ($end->lessThan($start)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        // Get sessions between dates
        $sessionsQuery = PlaySession::where('location_id', $locationId)
            ->where('started_at', '>=', $start)
            ->where('started_at', '<=', $end);
        
        // Filter by weekdays if specified
        if ($weekdays && !empty($weekdays)) {
            // Convert weekday numbers to array of integers
            $weekdaysArray = array_map('intval', $weekdays);
            // Use SQL DAYOFWEEK function: 1=Sunday, 2=Monday, ..., 7=Saturday
            // But Carbon uses 0=Sunday, 1=Monday, ..., 6=Saturday
            // MySQL DAYOFWEEK returns 1-7, so we need to adjust
            $mysqlWeekdays = array_map(function($day) {
                // Convert Carbon dayOfWeek (0-6) to MySQL DAYOFWEEK (1-7)
                // Carbon: 0=Sunday, 1=Monday, ..., 6=Saturday
                // MySQL: 1=Sunday, 2=Monday, ..., 7=Saturday
                return $day + 1;
            }, $weekdaysArray);
            
            // Use whereRaw with bindings for security
            $placeholders = implode(',', array_fill(0, count($mysqlWeekdays), '?'));
            $sessionsQuery->whereRaw("DAYOFWEEK(started_at) IN ({$placeholders})", $mysqlWeekdays);
        }
        
        $sessionsToday = $sessionsQuery->get();
        
        $totalToday = $sessionsToday->count();

        $buckets = ['<1h' => 0, '1-2h' => 0, '2-3h' => 0, '>3h' => 0];
        
        foreach ($sessionsToday as $s) {
            $mins = $s->getCurrentDurationMinutes();
            
            if ($mins < 60) {
                $buckets['<1h']++;
            } elseif ($mins < 120) {
                $buckets['1-2h']++;
            } elseif ($mins < 180) {
                $buckets['2-3h']++;
            } else {
                $buckets['>3h']++;
            }
        }
        
        $bucketPerc = [];
        foreach ($buckets as $k => $v) {
            $bucketPerc[$k] = $totalToday > 0 ? round(($v / $totalToday) * 100, 1) : 0.0;
        }

        $children = $this->children->getAllWithBirthdateByLocation($locationId);
        $avgAgeYears = 0;
        $avgAgeMonths = 0;
        if ($children->count() > 0) {
            $totalMonths = $children->reduce(function ($carry, $c) {
                try {
                    $birthDate = \Carbon\Carbon::parse($c->birth_date);
                    $now = now();
                    $diff = $birthDate->diff($now);
                    return $carry + ($diff->y * 12) + $diff->m;
                } catch (\Throwable) {
                    return $carry;
                }
            }, 0);
            $avgMonths = (int) round($totalMonths / $children->count());
            $avgAgeYears = (int) ($avgMonths / 12);
            $avgAgeMonths = $avgMonths % 12;
        }

        // Unique vs recurring visitors within the selected range
        $byChild = [];
        foreach ($sessionsToday as $sess) {
            if (!$sess->child_id) { continue; }
            $byChild[$sess->child_id] = ($byChild[$sess->child_id] ?? 0) + 1;
        }
        $uniqueVisitors = 0; // exactly one session in range
        $recurringVisitors = 0; // two or more sessions in range
        foreach ($byChild as $count) {
            if ($count <= 1) { $uniqueVisitors++; }
            else { $recurringVisitors++; }
        }
        $totalVisitors = max(1, $uniqueVisitors + $recurringVisitors);
        $visitorDist = [
            'unique' => ['count' => $uniqueVisitors, 'percent' => round(($uniqueVisitors / $totalVisitors) * 100, 1)],
            'recurring' => ['count' => $recurringVisitors, 'percent' => round(($recurringVisitors / $totalVisitors) * 100, 1)],
        ];

        // Hourly traffic: count sessions by hour of started_at (0-23)
        $hourlyTraffic = array_fill(0, 24, 0);
        foreach ($sessionsToday as $sess) {
            if ($sess->started_at) {
                $hour = (int) $sess->started_at->format('H');
                $hourlyTraffic[$hour]++;
            }
        }

        return [
            'total_today' => $totalToday,
            'buckets_today' => [
                'lt_1h' => [
                    'count' => $buckets['<1h'],
                    'percent' => $bucketPerc['<1h'],
                ],
                'h1_2' => [
                    'count' => $buckets['1-2h'],
                    'percent' => $bucketPerc['1-2h'],
                ],
                'h2_3' => [
                    'count' => $buckets['2-3h'],
                    'percent' => $bucketPerc['2-3h'],
                ],
                'gt_3h' => [
                    'count' => $buckets['>3h'],
                    'percent' => $bucketPerc['>3h'],
                ],
            ],
            'avg_child_age_years' => $avgAgeYears,
            'avg_child_age_months' => $avgAgeMonths,
            'visitor_distribution' => $visitorDist,
            'hourly_traffic' => $hourlyTraffic,
        ];
    }

    /**
     * Get entries (session starts) over time by period type
     * 
     * @param int $locationId
     * @param string $periodType 'daily', 'weekly', or 'monthly'
     * @param int $count Number of periods to show
     * @return array Array with labels, data, and growth indicators
     */
    public function getEntriesOverTime(int $locationId, string $periodType, int $count): array
    {
        $now = now();
        $labels = [];
        $data = [];
        $growth = []; // Growth percentage from previous period
        
        if ($periodType === 'daily') {
            // Get entries for the last $count days
            for ($i = $count - 1; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i)->startOfDay();
                $endDate = $date->copy()->endOfDay();
                
                $entries = PlaySession::where('location_id', $locationId)
                    ->where('started_at', '>=', $date)
                    ->where('started_at', '<=', $endDate)
                    ->count();
                
                $labels[] = $date->format('d.m.Y');
                $data[] = $entries;
                
                // Calculate growth from previous day
                if ($i < $count - 1) {
                    $prevEntries = $data[count($data) - 2];
                    if ($prevEntries > 0) {
                        $growthPercent = round((($entries - $prevEntries) / $prevEntries) * 100, 1);
                    } else {
                        $growthPercent = $entries > 0 ? 100 : 0;
                    }
                } else {
                    $growthPercent = 0;
                }
                $growth[] = $growthPercent;
            }
        } elseif ($periodType === 'weekly') {
            // Get entries for the last $count weeks
            for ($i = $count - 1; $i >= 0; $i--) {
                $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
                $weekEnd = $weekStart->copy()->endOfWeek();
                
                $entries = PlaySession::where('location_id', $locationId)
                    ->where('started_at', '>=', $weekStart)
                    ->where('started_at', '<=', $weekEnd)
                    ->count();
                
                $labels[] = $weekStart->format('d.m') . ' - ' . $weekEnd->format('d.m.Y');
                $data[] = $entries;
                
                // Calculate growth from previous week
                if ($i < $count - 1) {
                    $prevEntries = $data[count($data) - 2];
                    if ($prevEntries > 0) {
                        $growthPercent = round((($entries - $prevEntries) / $prevEntries) * 100, 1);
                    } else {
                        $growthPercent = $entries > 0 ? 100 : 0;
                    }
                } else {
                    $growthPercent = 0;
                }
                $growth[] = $growthPercent;
            }
        } elseif ($periodType === 'monthly') {
            // Get entries for the last $count months
            for ($i = $count - 1; $i >= 0; $i--) {
                $monthStart = $now->copy()->subMonths($i)->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();
                
                $entries = PlaySession::where('location_id', $locationId)
                    ->where('started_at', '>=', $monthStart)
                    ->where('started_at', '<=', $monthEnd)
                    ->count();
                
                $labels[] = $monthStart->format('m.Y');
                $data[] = $entries;
                
                // Calculate growth from previous month
                if ($i < $count - 1) {
                    $prevEntries = $data[count($data) - 2];
                    if ($prevEntries > 0) {
                        $growthPercent = round((($entries - $prevEntries) / $prevEntries) * 100, 1);
                    } else {
                        $growthPercent = $entries > 0 ? 100 : 0;
                    }
                } else {
                    $growthPercent = 0;
                }
                $growth[] = $growthPercent;
            }
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'growth' => $growth,
        ];
    }

    private function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $rem = $minutes % 60;
        return sprintf('%dh %dm', $hours, $rem);
    }
}


