@extends('layouts.app')

@section('title', 'Monitoring Sistem')
@section('page-title', 'Monitoring Sistem')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">Monitoring Sistem</h1>
                <p class="text-gray-500 text-sm mt-1">
                    <i class="fas fa-sync-alt mr-1"></i>
                    Actualizat la: <span id="last-refresh">{{ now()->format('H:i:s') }}</span>
                    &nbsp;&bull;&nbsp; refresh automat la fiecare 60s
                </p>
            </div>
            <button onclick="location.reload()"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-sky-50 text-sky-700 border border-sky-200 rounded-lg hover:bg-sky-100 transition-colors">
                <i class="fas fa-sync-alt"></i>
                <span>Refresh acum</span>
            </button>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 1. HEALTH CHECKS --}}
    {{-- ============================================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-heartbeat text-sky-500"></i>
            Health Checks
        </h2>

        @if(empty($healthResults))
            <p class="text-sm text-gray-400 italic">Date indisponibile — rulați <code class="bg-gray-100 px-1 rounded">php artisan health:check</code> pentru a popula rezultatele.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $healthIcons = [
                        'DatabaseCheck'       => 'fa-database',
                        'ScheduleCheck'       => 'fa-calendar-check',
                        'UsedDiskSpaceCheck'  => 'fa-hdd',
                        'QueueCheck'          => 'fa-layer-group',
                    ];
                    $healthLabels = [
                        'DatabaseCheck'       => 'Bază de date',
                        'ScheduleCheck'       => 'Scheduler',
                        'UsedDiskSpaceCheck'  => 'Spațiu Disk',
                        'QueueCheck'          => 'Queue Worker',
                    ];
                    $statusConfig = [
                        'ok'      => ['bg' => 'bg-green-50',  'border' => 'border-green-200', 'badge' => 'bg-green-100 text-green-800',  'dot' => 'bg-green-500',  'icon' => 'fa-check-circle text-green-500',  'label' => 'OK'],
                        'warning' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200','badge' => 'bg-yellow-100 text-yellow-800','dot' => 'bg-yellow-500', 'icon' => 'fa-exclamation-circle text-yellow-500','label' => 'Avertisment'],
                        'failed'  => ['bg' => 'bg-red-50',    'border' => 'border-red-200',   'badge' => 'bg-red-100 text-red-800',     'dot' => 'bg-red-500',    'icon' => 'fa-times-circle text-red-500',     'label' => 'Eroare'],
                        'crashed' => ['bg' => 'bg-red-50',    'border' => 'border-red-200',   'badge' => 'bg-red-100 text-red-800',     'dot' => 'bg-red-500',    'icon' => 'fa-times-circle text-red-500',     'label' => 'Căzut'],
                        'skipped' => ['bg' => 'bg-gray-50',   'border' => 'border-gray-200',  'badge' => 'bg-gray-100 text-gray-600',   'dot' => 'bg-gray-400',   'icon' => 'fa-minus-circle text-gray-400',    'label' => 'Omis'],
                    ];
                @endphp

                @foreach($healthResults as $result)
                    @php
                        $status = $result['status'] ?? 'skipped';
                        $cfg    = $statusConfig[$status] ?? $statusConfig['skipped'];
                        $icon   = $healthIcons[$result['check_name']] ?? 'fa-shield-alt';
                        $label  = $healthLabels[$result['check_name']] ?? ($result['check_label'] ?? $result['check_name']);
                    @endphp
                    <div class="rounded-lg border {{ $cfg['border'] }} {{ $cfg['bg'] }} p-4 flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas {{ $icon }} text-gray-500 text-sm"></i>
                                <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                            </div>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $cfg['badge'] }}">
                                {{ $cfg['label'] }}
                            </span>
                        </div>
                        @if(!empty($result['short_summary']))
                            <p class="text-xs text-gray-500 leading-snug">{{ $result['short_summary'] }}</p>
                        @endif
                        @if($result['ended_at'])
                            <p class="text-xs text-gray-400">
                                <i class="fas fa-clock mr-1"></i>
                                {{ \Carbon\Carbon::parse($result['ended_at'])->diffForHumans() }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ============================================================ --}}
    {{-- 2. PULSE METRICS --}}
    {{-- ============================================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-chart-line text-sky-500"></i>
            Pulse — ultimele 24h
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Exceptions --}}
            <div class="rounded-lg border border-gray-200 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                        <i class="fas fa-bug text-red-500 text-sm"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Exceptions</span>
                </div>
                @if($pulseMetrics['exceptions_count'] === null)
                    <p class="text-sm text-gray-400 italic">Date indisponibile</p>
                @else
                    <p class="text-3xl font-bold {{ $pulseMetrics['exceptions_count'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $pulseMetrics['exceptions_count'] }}
                    </p>
                @endif
            </div>

            {{-- Failed jobs --}}
            <div class="rounded-lg border border-gray-200 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                        <i class="fas fa-times-circle text-orange-500 text-sm"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Failed Jobs</span>
                </div>
                @if($pulseMetrics['failed_jobs_count'] === null)
                    <p class="text-sm text-gray-400 italic">Date indisponibile</p>
                @else
                    <p class="text-3xl font-bold {{ $pulseMetrics['failed_jobs_count'] > 0 ? 'text-orange-600' : 'text-gray-900' }}">
                        {{ $pulseMetrics['failed_jobs_count'] }}
                    </p>
                @endif
            </div>

            {{-- Slowest request --}}
            <div class="rounded-lg border border-gray-200 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-bolt text-yellow-500 text-sm"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Cel mai lent request</span>
                </div>
                @if($pulseMetrics['slowest_request'] === null)
                    <p class="text-sm text-gray-400 italic">Niciun request lent</p>
                @else
                    <p class="text-2xl font-bold text-yellow-600">
                        {{ number_format($pulseMetrics['slowest_request']['duration_ms']) }}
                        <span class="text-sm font-normal text-gray-500">ms</span>
                    </p>
                    <p class="text-xs text-gray-400 mt-1 truncate" title="{{ $pulseMetrics['slowest_request']['route'] }}">
                        {{ Str::limit($pulseMetrics['slowest_request']['route'], 40) }}
                    </p>
                @endif
            </div>

            {{-- Slowest query --}}
            <div class="rounded-lg border border-gray-200 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-database text-purple-500 text-sm"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Cel mai lent query</span>
                </div>
                @if($pulseMetrics['slowest_query'] === null)
                    <p class="text-sm text-gray-400 italic">Niciun query lent</p>
                @else
                    <p class="text-2xl font-bold text-purple-600">
                        {{ number_format($pulseMetrics['slowest_query']['duration_ms']) }}
                        <span class="text-sm font-normal text-gray-500">ms</span>
                    </p>
                    <p class="text-xs text-gray-400 mt-1 truncate" title="{{ $pulseMetrics['slowest_query']['sql'] }}">
                        {{ Str::limit($pulseMetrics['slowest_query']['sql'], 40) }}
                    </p>
                @endif
            </div>

        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 3. EVENIMENTE RECENTE --}}
    {{-- ============================================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-stream text-sky-500"></i>
            Evenimente recente — ultimele 24h
        </h2>

        @if(empty($recentEvents))
            <p class="text-sm text-gray-400 italic">Niciun eveniment înregistrat în ultimele 24h.</p>
        @else
            <div class="overflow-x-auto -mx-4 md:-mx-6 px-4 md:px-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-2 pr-4 text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">Tip</th>
                            <th class="text-left py-2 pr-4 text-xs font-semibold text-gray-500 uppercase tracking-wide">Mesaj</th>
                            <th class="text-right py-2 pr-4 text-xs font-semibold text-gray-500 uppercase tracking-wide w-20">Durată</th>
                            <th class="text-right py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide w-32">Timp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($recentEvents as $event)
                            @php
                                $eventConfig = [
                                    'exception'    => ['badge' => 'bg-red-100 text-red-700',    'icon' => 'fa-bug',            'label' => 'Exception'],
                                    'slow_query'   => ['badge' => 'bg-purple-100 text-purple-700', 'icon' => 'fa-database',    'label' => 'Slow Query'],
                                    'slow_request' => ['badge' => 'bg-yellow-100 text-yellow-700', 'icon' => 'fa-bolt',        'label' => 'Slow Request'],
                                    'failed_job'   => ['badge' => 'bg-orange-100 text-orange-700', 'icon' => 'fa-times-circle','label' => 'Failed Job'],
                                ];
                                $ec = $eventConfig[$event['type']] ?? ['badge' => 'bg-gray-100 text-gray-600', 'icon' => 'fa-circle', 'label' => $event['type']];
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-2.5 pr-4">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full {{ $ec['badge'] }}">
                                        <i class="fas {{ $ec['icon'] }} text-[10px]"></i>
                                        {{ $ec['label'] }}
                                    </span>
                                </td>
                                <td class="py-2.5 pr-4 text-gray-700 max-w-xs">
                                    <span class="block truncate" title="{{ $event['message'] }}">
                                        {{ Str::limit($event['message'], 80) }}
                                    </span>
                                </td>
                                <td class="py-2.5 pr-4 text-right text-gray-500">
                                    @if($event['value'])
                                        <span class="{{ $event['value'] > 2000 ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                            {{ number_format($event['value']) }}ms
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="py-2.5 text-right text-gray-400 text-xs whitespace-nowrap">
                                    {{ \Carbon\Carbon::createFromTimestamp($event['timestamp'])->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ============================================================ --}}
    {{-- 4. LINKURI RAPIDE --}}
    {{-- ============================================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-external-link-alt text-sky-500"></i>
            Linkuri rapide
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">

            <a href="/pulse" target="_blank"
               class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 hover:border-sky-300 hover:bg-sky-50 transition-colors group">
                <div class="w-10 h-10 rounded-lg bg-sky-100 flex items-center justify-center group-hover:bg-sky-200 transition-colors">
                    <i class="fas fa-tachometer-alt text-sky-600"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Pulse Dashboard</span>
            </a>

            <a href="/health-check-results" target="_blank"
               class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-colors group">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <i class="fas fa-heartbeat text-green-600"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Health JSON</span>
            </a>

            @if(config('services.sentry.org_url'))
            <a href="{{ config('services.sentry.org_url') }}" target="_blank" rel="noopener noreferrer"
               class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-colors group">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <i class="fas fa-bug text-purple-600"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">Sentry</span>
            </a>
            @else
            <div class="flex flex-col items-center gap-2 p-4 rounded-lg border border-dashed border-gray-200 opacity-50 cursor-not-allowed">
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-bug text-gray-400"></i>
                </div>
                <span class="text-xs font-medium text-gray-400 text-center">Sentry</span>
                <span class="text-[10px] text-gray-400">(SENTRY_ORG_URL nesetat)</span>
            </div>
            @endif

            <a href="https://uptimerobot.com" target="_blank" rel="noopener noreferrer"
               class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-colors group">
                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                    <i class="fas fa-satellite-dish text-orange-600"></i>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center">UptimeRobot</span>
            </a>

        </div>
    </div>

</div>

<script>
    // Auto-refresh la 60 de secunde
    setTimeout(function () {
        location.reload();
    }, 60000);

    // Countdown vizual în header
    let seconds = 60;
    const el = document.getElementById('last-refresh');
    if (el) {
        setInterval(function () {
            seconds--;
            if (seconds <= 0) seconds = 60;
            const ts = '{{ now()->format("H:i:s") }}';
            el.textContent = ts + ' (refresh în ' + seconds + 's)';
        }, 1000);
    }
</script>
@endsection
