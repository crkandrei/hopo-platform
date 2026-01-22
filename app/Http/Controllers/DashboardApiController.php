<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Services\DashboardService;
use App\Support\ApiResponder;
use Illuminate\Support\Facades\Auth;

class DashboardApiController extends Controller
{
    /** @var DashboardService */
    protected $dashboard;

    /** @var AuditLogRepositoryInterface */
    protected $auditLogs;

    public function __construct(DashboardService $dashboard, AuditLogRepositoryInterface $auditLogs)
    {
        $this->dashboard = $dashboard;
        $this->auditLogs = $auditLogs;
    }

    /** Return dashboard stats for the authenticated user's location */
    public function stats()
    {
        $user = Auth::user();
        if (!$user || !$user->location) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $locationId = $user->location->id;

        $stats = $this->dashboard->getStatsForLocation($locationId);
        return ApiResponder::success(['stats' => $stats]);
    }

    /** Return recent activity for location (from audit logs if available) */
    public function recentActivity()
    {
        $user = Auth::user();
        if (!$user || !$user->location) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $locationId = $user->location->id;

        $logs = $this->auditLogs->latestByLocation($locationId, 20)
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'type' => $log->action,
                    'description' => $log->entity_type . ' #' . $log->entity_id,
                    'created_at' => optional($log->created_at)->toISOString(),
                ];
            });

        return ApiResponder::success(['activities' => $logs->toArray()]);
    }

    /** Duration buckets and average child age */
    public function reports()
    {
        $user = Auth::user();
        if (!$user || !$user->location) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $locationId = $user->location->id;

        $start = request()->query('start');
        $end = request()->query('end');
        $weekdays = request()->query('weekdays'); // Can be array or single value

        // Convert weekdays to array if provided
        $weekdaysArray = null;
        if ($weekdays) {
            $weekdaysArray = is_array($weekdays) ? $weekdays : [$weekdays];
        }

        $reports = $this->dashboard->getReports($locationId, $start, $end, $weekdaysArray);
        return ApiResponder::success(['reports' => $reports]);
    }

    /** Get entries over time report */
    public function entriesReport()
    {
        $user = Auth::user();
        if (!$user || !$user->location) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $locationId = $user->location->id;

        $periodType = request()->query('period', 'daily'); // daily, weekly, monthly
        $count = (int) request()->query('count', 7); // Number of periods

        // Validate period type
        if (!in_array($periodType, ['daily', 'weekly', 'monthly'])) {
            return ApiResponder::error('Tip perioadă invalid. Trebuie să fie: daily, weekly sau monthly', 400);
        }

        // Validate count
        if ($count < 1 || $count > 365) {
            return ApiResponder::error('Numărul de perioade trebuie să fie între 1 și 365', 400);
        }

        $entriesData = $this->dashboard->getEntriesOverTime($locationId, $periodType, $count);
        return ApiResponder::success(['entries' => $entriesData]);
    }
    
    /** Get alerts for dashboard (unpaid sessions, long sessions) */
    public function alerts()
    {
        $user = Auth::user();
        if (!$user || !$user->location) {
            return ApiResponder::error('Neautentificat', 401);
        }
        $locationId = $user->location->id;

        $alerts = $this->dashboard->getAlerts($locationId);
        return ApiResponder::success(['alerts' => $alerts]);
    }
}


