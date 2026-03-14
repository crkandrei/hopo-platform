<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Spatie\Health\Models\HealthCheckResultHistoryItem;

class MonitoringController extends Controller
{
    public function index()
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Acces interzis');
        }

        return view('admin.monitoring.index', [
            'healthResults' => $this->getHealthResults(),
            'pulseMetrics'  => $this->getPulseMetrics(),
            'recentEvents'  => $this->getRecentEvents(),
        ]);
    }

    private function getHealthResults(): array
    {
        try {
            return HealthCheckResultHistoryItem::query()
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('check_name')
                ->values()
                ->map(fn ($item) => [
                    'check_name'    => $item->check_name,
                    'check_label'   => $item->check_label,
                    'status'        => $item->status instanceof \BackedEnum
                                            ? $item->status->value
                                            : (string) $item->status,
                    'short_summary' => $item->short_summary,
                    'ended_at'      => $item->ended_at,
                ])
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function getPulseMetrics(): array
    {
        $since = now()->subHours(24)->timestamp;

        $metrics = [
            'exceptions_count'  => null,
            'failed_jobs_count' => null,
            'slowest_request'   => null,
            'slowest_query'     => null,
        ];

        try {
            $metrics['exceptions_count'] = DB::table('pulse_entries')
                ->where('type', 'exception')
                ->where('timestamp', '>=', $since)
                ->count();
        } catch (\Throwable) {}

        try {
            $metrics['failed_jobs_count'] = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subHours(24))
                ->count();
        } catch (\Throwable) {}

        try {
            $entry = DB::table('pulse_entries')
                ->where('type', 'slow_request')
                ->where('timestamp', '>=', $since)
                ->orderBy('value', 'desc')
                ->first();

            if ($entry) {
                $metrics['slowest_request'] = [
                    'route'       => $entry->key,
                    'duration_ms' => $entry->value,
                ];
            }
        } catch (\Throwable) {}

        try {
            $entry = DB::table('pulse_entries')
                ->where('type', 'slow_query')
                ->where('timestamp', '>=', $since)
                ->orderBy('value', 'desc')
                ->first();

            if ($entry) {
                $metrics['slowest_query'] = [
                    'sql'         => $entry->key,
                    'duration_ms' => $entry->value,
                ];
            }
        } catch (\Throwable) {}

        return $metrics;
    }

    private function getRecentEvents(): array
    {
        $since  = now()->subHours(24)->timestamp;
        $events = [];

        try {
            $pulseEvents = DB::table('pulse_entries')
                ->whereIn('type', ['exception', 'slow_query', 'slow_request'])
                ->where('timestamp', '>=', $since)
                ->orderBy('timestamp', 'desc')
                ->limit(8)
                ->get()
                ->map(fn ($e) => [
                    'type'      => $e->type,
                    'message'   => $e->key,
                    'value'     => $e->value,
                    'timestamp' => $e->timestamp,
                ])
                ->toArray();

            $events = array_merge($events, $pulseEvents);
        } catch (\Throwable) {}

        try {
            $failedJobs = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subHours(24))
                ->orderBy('failed_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($job) {
                    $payload  = json_decode($job->payload, true);
                    $jobClass = $payload['displayName'] ?? ($payload['job'] ?? 'Unknown Job');

                    return [
                        'type'      => 'failed_job',
                        'message'   => $jobClass,
                        'value'     => null,
                        'timestamp' => strtotime($job->failed_at),
                    ];
                })
                ->toArray();

            $events = array_merge($events, $failedJobs);
        } catch (\Throwable) {}

        usort($events, fn ($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return array_slice($events, 0, 10);
    }
}
