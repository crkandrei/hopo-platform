@extends('layouts.app')

@section('title', 'Analiză Trafic')
@section('page-title', 'Analiză Trafic')

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Reports and Traffic Section with Filters -->
    <div class="relative">
        <!-- Sticky Filters Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 card-hover sticky top-0 md:top-4 z-10 mb-4 md:mb-6">
            <div class="p-3 md:p-4 lg:p-6">
                <div class="flex flex-col md:flex-row md:items-end md:space-x-3 space-y-3 md:space-y-0">
                    <div class="w-full md:w-auto">
                        <label class="block text-sm text-gray-600 mb-1">Data start</label>
                        <input type="date" id="reportStart" class="w-full md:w-auto px-3 py-2.5 md:py-2 border border-gray-300 rounded-md text-base md:text-sm" />
                    </div>
                    <div class="w-full md:w-auto">
                        <label class="block text-sm text-gray-600 mb-1">Data stop</label>
                        <input type="date" id="reportEnd" class="w-full md:w-auto px-3 py-2.5 md:py-2 border border-gray-300 rounded-md text-base md:text-sm" />
                    </div>
                    <div class="w-full md:flex-1">
                        <label class="block text-sm text-gray-600 mb-1">Zile săptămână</label>
                        <div class="flex flex-wrap gap-1.5 md:gap-2">
                            <label class="flex items-center px-2.5 md:px-3 py-1.5 bg-gray-50 rounded-md cursor-pointer hover:bg-gray-100 border border-gray-300 text-xs md:text-sm">
                                <input type="checkbox" name="weekdays" value="1" class="mr-1.5 md:mr-2 w-4 h-4 md:w-auto md:h-auto" checked>
                                <span>Luni</span>
                            </label>
                            <label class="flex items-center px-2.5 md:px-3 py-1.5 bg-gray-50 rounded-md cursor-pointer hover:bg-gray-100 border border-gray-300 text-xs md:text-sm">
                                <input type="checkbox" name="weekdays" value="2" class="mr-1.5 md:mr-2 w-4 h-4 md:w-auto md:h-auto" checked>
                                <span>Marți</span>
                            </label>
                            <label class="flex items-center px-2.5 md:px-3 py-1.5 bg-gray-50 rounded-md cursor-pointer hover:bg-gray-100 border border-gray-300 text-xs md:text-sm">
                                <input type="checkbox" name="weekdays" value="3" class="mr-1.5 md:mr-2 w-4 h-4 md:w-auto md:h-auto" checked>
                                <span>Miercuri</span>
                            </label>
                            <label class="flex items-center px-2.5 md:px-3 py-1.5 bg-gray-50 rounded-md cursor-pointer hover:bg-gray-100 border border-gray-300 text-xs md:text-sm">
                                <input type="checkbox" name="weekdays" value="4" class="mr-1.5 md:mr-2 w-4 h-4 md:w-auto md:h-auto" checked>
                                <span>Joi</span>
                            </label>
                            <label class="flex items-center px-2.5 md:px-3 py-1.5 bg-gray-50 rounded-md cursor-pointer hover:bg-gray-100 border border-gray-300 text-xs md:text-sm">
                                <input type="checkbox" name="weekdays" value="5" class="mr-1.5 md:mr-2 w-4 h-4 md:w-auto md:h-auto" checked>
                                <span>Vineri</span>
                            </label>
                            <label class="flex items-center px-2.5 md:px-3 py-1.5 bg-gray-50 rounded-md cursor-pointer hover:bg-gray-100 border border-gray-300 text-xs md:text-sm">
                                <input type="checkbox" name="weekdays" value="6" class="mr-1.5 md:mr-2 w-4 h-4 md:w-auto md:h-auto" checked>
                                <span>Sâmbătă</span>
                            </label>
                            <label class="flex items-center px-2.5 md:px-3 py-1.5 bg-gray-50 rounded-md cursor-pointer hover:bg-gray-100 border border-gray-300 text-xs md:text-sm">
                                <input type="checkbox" name="weekdays" value="0" class="mr-1.5 md:mr-2 w-4 h-4 md:w-auto md:h-auto" checked>
                                <span>Duminică</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 hidden md:block">Selectează zilele săptămânii pentru filtrare</p>
                    </div>
                    <div class="w-full md:w-auto">
                        <label class="block text-sm text-transparent mb-1">&nbsp;</label>
                        <button id="reloadReports" class="w-full md:w-auto px-4 py-2.5 md:py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 text-base md:text-sm font-medium">Reîncarcă</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reports Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 card-hover mb-4 md:mb-6">
            <div class="px-4 md:px-6 py-3 md:py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-2 md:mr-3">
                            <i class="fas fa-chart-pie text-indigo-600 text-sm md:text-base"></i>
                        </div>
                        <h2 class="text-lg md:text-xl font-bold text-gray-900">Rapoarte</h2>
                    </div>
                    <div id="totalSessions" class="text-sm md:text-base text-gray-600 font-medium">-</div>
                </div>
            </div>
            <div class="p-4 md:p-6">
                <div id="reports" class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                    <div class="bg-gray-50 rounded p-3 md:p-4 text-center">
                        <div class="text-xs md:text-sm text-gray-600">% sesiuni < 1h</div>
                        <div id="bucket_lt1h_percent" class="text-xl md:text-2xl font-bold mt-1">-</div>
                        <div id="bucket_lt1h_count" class="text-sm text-gray-700 mt-1">-</div>
                        <div id="bucket_lt1h_types" class="text-xs text-gray-600 mt-1">-</div>
                    </div>
                    <div class="bg-gray-50 rounded p-3 md:p-4 text-center">
                        <div class="text-xs md:text-sm text-gray-600">% sesiuni 1-2h</div>
                        <div id="bucket_1_2_percent" class="text-xl md:text-2xl font-bold mt-1">-</div>
                        <div id="bucket_1_2_count" class="text-sm text-gray-700 mt-1">-</div>
                        <div id="bucket_1_2_types" class="text-xs text-gray-600 mt-1">-</div>
                    </div>
                    <div class="bg-gray-50 rounded p-3 md:p-4 text-center">
                        <div class="text-xs md:text-sm text-gray-600">% sesiuni 2-3h</div>
                        <div id="bucket_2_3_percent" class="text-xl md:text-2xl font-bold mt-1">-</div>
                        <div id="bucket_2_3_count" class="text-sm text-gray-700 mt-1">-</div>
                        <div id="bucket_2_3_types" class="text-xs text-gray-600 mt-1">-</div>
                    </div>
                    <div class="bg-gray-50 rounded p-3 md:p-4 text-center">
                        <div class="text-xs md:text-sm text-gray-600">% sesiuni > 3h</div>
                        <div id="bucket_gt3h_percent" class="text-xl md:text-2xl font-bold mt-1">-</div>
                        <div id="bucket_gt3h_count" class="text-sm text-gray-700 mt-1">-</div>
                        <div id="bucket_gt3h_types" class="text-xs text-gray-600 mt-1">-</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Traffic Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 card-hover">
            <div class="px-4 md:px-6 py-3 md:py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-emerald-100 rounded-lg flex items-center justify-center mr-2 md:mr-3">
                            <i class="fas fa-chart-bar text-emerald-600 text-sm md:text-base"></i>
                        </div>
                        <h2 class="text-lg md:text-xl font-bold text-gray-900">Trafic pe Ore</h2>
                    </div>
                    <button id="exportTrafficPdf" class="flex items-center px-3 md:px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700 text-sm md:text-base font-medium">
                        <i class="fas fa-file-pdf mr-2"></i>
                        <span class="hidden sm:inline">Exportă PDF</span>
                        <span class="sm:hidden">PDF</span>
                    </button>
                </div>
            </div>
            <div class="p-3 md:p-4 lg:p-6">
                <div class="w-full overflow-x-auto">
                    <div class="min-w-full" style="min-height: 250px;">
                        <canvas id="hourlyTrafficChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Entries Report Section (separate) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 card-hover">
        <div class="px-4 md:px-6 py-3 md:py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-2 md:mr-3">
                        <i class="fas fa-chart-line text-purple-600 text-sm md:text-base"></i>
                    </div>
                    <h2 class="text-lg md:text-xl font-bold text-gray-900">Raport Intrări</h2>
                </div>
            </div>
        </div>
        <div class="p-3 md:p-4 lg:p-6">
            <div class="flex flex-col md:flex-row md:items-end md:space-x-4 space-y-3 md:space-y-0 mb-4 md:mb-6">
                <div class="w-full md:w-auto">
                    <label class="block text-sm text-gray-600 mb-1">Tip perioadă</label>
                    <select id="entriesPeriodType" class="w-full md:w-auto px-3 py-2.5 md:py-2 border border-gray-300 rounded-md text-base md:text-sm">
                        <option value="daily">Zilnic</option>
                        <option value="weekly">Săptămânal</option>
                        <option value="monthly">Lunar</option>
                    </select>
                </div>
                <div class="w-full md:w-auto">
                    <label class="block text-sm text-gray-600 mb-1">Număr perioade</label>
                    <input type="number" id="entriesCount" min="1" max="365" value="7" class="w-full md:w-24 px-3 py-2.5 md:py-2 border border-gray-300 rounded-md text-base md:text-sm" />
                </div>
                <div class="w-full md:w-auto">
                    <label class="block text-sm text-transparent mb-1">&nbsp;</label>
                    <button id="loadEntriesReport" class="w-full md:w-auto px-4 py-2.5 md:py-2 rounded-md bg-purple-600 text-white hover:bg-purple-700 text-base md:text-sm font-medium">Încarcă</button>
                </div>
            </div>
            <div class="w-full overflow-x-auto">
                <div class="min-w-full" style="min-height: 250px;">
                    <canvas id="entriesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
    async function apiCall(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                ...options.headers
            },
            credentials: 'same-origin'
        });
        return response.json();
    }

    function getDateParam(dateInput) {
        const v = dateInput.value;
        if (!v) return null;
        return v;
    }

    async function loadReports() {
        try {
            const start = getDateParam(document.getElementById('reportStart'));
            const end = getDateParam(document.getElementById('reportEnd'));
            
            // Get selected weekdays
            const weekdayCheckboxes = document.querySelectorAll('input[name="weekdays"]:checked');
            const selectedWeekdays = Array.from(weekdayCheckboxes).map(cb => parseInt(cb.value));
            
            const qs = new URLSearchParams();
            if (start) qs.append('start', start);
            if (end) qs.append('end', end);
            // Only add weekdays filter if at least one is selected and not all are selected
            if (selectedWeekdays.length > 0 && selectedWeekdays.length < 7) {
                selectedWeekdays.forEach(day => qs.append('weekdays[]', day));
            }

            const reportsData = await apiCall('/reports-api/reports' + (qs.toString() ? ('?' + qs.toString()) : ''));
            
            // Helper function to format session types distribution
            const formatSessionTypes = (bucket) => {
                // All sessions are normal (no birthday/jungle types)
                return '';
            };
            
            const setReports = (r) => {
                // Total sessions in header
                const totalSessions = r.total_today || 0;
                document.getElementById('totalSessions').textContent = totalSessions + ' sesiuni';
                
                // Bucket < 1h
                const lt1h = r.buckets_today.lt_1h || {};
                document.getElementById('bucket_lt1h_percent').textContent = (lt1h.percent || 0) + '%';
                document.getElementById('bucket_lt1h_count').textContent = (lt1h.count || 0) + ' sesiuni';
                document.getElementById('bucket_lt1h_types').textContent = formatSessionTypes(lt1h);
                
                // Bucket 1-2h
                const h1_2 = r.buckets_today.h1_2 || {};
                document.getElementById('bucket_1_2_percent').textContent = (h1_2.percent || 0) + '%';
                document.getElementById('bucket_1_2_count').textContent = (h1_2.count || 0) + ' sesiuni';
                document.getElementById('bucket_1_2_types').textContent = formatSessionTypes(h1_2);
                
                // Bucket 2-3h
                const h2_3 = r.buckets_today.h2_3 || {};
                document.getElementById('bucket_2_3_percent').textContent = (h2_3.percent || 0) + '%';
                document.getElementById('bucket_2_3_count').textContent = (h2_3.count || 0) + ' sesiuni';
                document.getElementById('bucket_2_3_types').textContent = formatSessionTypes(h2_3);
                
                // Bucket > 3h
                const gt3h = r.buckets_today.gt_3h || {};
                document.getElementById('bucket_gt3h_percent').textContent = (gt3h.percent || 0) + '%';
                document.getElementById('bucket_gt3h_count').textContent = (gt3h.count || 0) + ' sesiuni';
                document.getElementById('bucket_gt3h_types').textContent = formatSessionTypes(gt3h);
                
                renderHourlyTrafficChart(r.hourly_traffic || Array(24).fill(0));
            };
            if (reportsData.success) {
                setReports(reportsData.reports);
            } else {
                const emptyBucket = { percent: 0, count: 0 };
                setReports({
                    total_today: 0,
                    buckets_today: {
                        lt_1h: emptyBucket,
                        h1_2: emptyBucket,
                        h2_3: emptyBucket,
                        gt_3h: emptyBucket
                    },
                    hourly_traffic: Array(24).fill(0)
                });
            }
        } catch (e) {
            console.error(e);
        }
    }

    let hourlyTrafficChart;
    let currentHourlyTrafficData = []; // Store current traffic data for PDF export
    
    function renderHourlyTrafficChart(hourlyData) {
        const ctx = document.getElementById('hourlyTrafficChart').getContext('2d');
        const isMobile = window.innerWidth < 768;
        
        // Store data for PDF export (full data)
        currentHourlyTrafficData = hourlyData;
        
        // Filter to only show hours 8-23
        const filteredData = [];
        const labels = [];
        for (let i = 8; i <= 23; i++) {
            const nextHour = i + 1;
            labels.push(`${i}-${nextHour}`);
            filteredData.push(hourlyData[i] || 0);
        }
        
        const data = {
            labels: labels,
            datasets: [{
                label: 'Număr sesiuni',
                data: filteredData,
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        };
        
        if (hourlyTrafficChart) {
            hourlyTrafficChart.data = data;
            hourlyTrafficChart.options.scales.x.ticks.maxRotation = isMobile ? 90 : 45;
            hourlyTrafficChart.options.scales.x.ticks.minRotation = isMobile ? 90 : 45;
            hourlyTrafficChart.options.scales.x.ticks.font.size = isMobile ? 10 : 12;
            hourlyTrafficChart.update();
        } else {
            hourlyTrafficChart = new Chart(ctx, {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    return 'Interval: ' + context[0].label;
                                },
                                label: function(context) {
                                    return 'Sesiuni: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0,
                                font: {
                                    size: isMobile ? 10 : 12
                                }
                            },
                            title: {
                                display: true,
                                text: 'Număr sesiuni',
                                font: {
                                    size: isMobile ? 12 : 14
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Interval orar',
                                font: {
                                    size: isMobile ? 12 : 14
                                }
                            },
                            ticks: {
                                maxRotation: isMobile ? 90 : 45,
                                minRotation: isMobile ? 90 : 45,
                                font: {
                                    size: isMobile ? 10 : 12
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // Prefill inputs with today range
    (function initDateDefaults(){
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth()+1).padStart(2,'0');
        const dd = String(today.getDate()).padStart(2,'0');
        const iso = `${yyyy}-${mm}-${dd}`;
        const s = document.getElementById('reportStart');
        const e = document.getElementById('reportEnd');
        if (s && !s.value) s.value = iso;
        if (e && !e.value) e.value = iso;
    })();

    document.getElementById('reloadReports').addEventListener('click', function(){
        loadReports();
    });

    loadReports();

    // Entries Report Chart
    let entriesChart;
    async function loadEntriesReport() {
        try {
            const periodType = document.getElementById('entriesPeriodType').value;
            const count = parseInt(document.getElementById('entriesCount').value) || 7;

            const qs = new URLSearchParams();
            qs.append('period', periodType);
            qs.append('count', count);

            const entriesData = await apiCall('/reports-api/entries?' + qs.toString());
            
            if (entriesData.success && entriesData.entries) {
                renderEntriesChart(entriesData.entries);
            } else {
                console.error('Eroare la încărcarea raportului de intrări:', entriesData);
            }
        } catch (e) {
            console.error('Eroare:', e);
        }
    }

    function renderEntriesChart(entriesData) {
        const ctx = document.getElementById('entriesChart').getContext('2d');
        const isMobile = window.innerWidth < 768;
        
        const labels = entriesData.labels || [];
        const data = entriesData.data || [];
        const growth = entriesData.growth || [];

        // Create background colors based on growth
        const backgroundColors = growth.map((g, index) => {
            if (index === 0) return 'rgba(99, 102, 241, 0.8)'; // First period - neutral
            if (g > 0) return 'rgba(34, 197, 94, 0.8)'; // Growth - green
            if (g < 0) return 'rgba(239, 68, 68, 0.8)'; // Decline - red
            return 'rgba(99, 102, 241, 0.8)'; // No change - neutral
        });

        const borderColors = growth.map((g, index) => {
            if (index === 0) return 'rgba(99, 102, 241, 1)';
            if (g > 0) return 'rgba(34, 197, 94, 1)';
            if (g < 0) return 'rgba(239, 68, 68, 1)';
            return 'rgba(99, 102, 241, 1)';
        });

        const chartData = {
            labels: labels,
            datasets: [{
                label: 'Număr intrări',
                data: data,
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 2,
                pointBackgroundColor: backgroundColors,
                pointBorderColor: borderColors,
                pointBorderWidth: 2,
                pointRadius: isMobile ? 4 : 6,
                pointHoverRadius: isMobile ? 6 : 8,
                fill: true,
                tension: 0.4
            }]
        };

        if (entriesChart) {
            entriesChart.data = chartData;
            entriesChart.options.scales.x.ticks.maxRotation = isMobile ? 90 : 45;
            entriesChart.options.scales.x.ticks.minRotation = isMobile ? 90 : 45;
            entriesChart.options.scales.x.ticks.font.size = isMobile ? 10 : 12;
            entriesChart.options.scales.y.ticks.font.size = isMobile ? 10 : 12;
            entriesChart.options.scales.y.title.font.size = isMobile ? 12 : 14;
            entriesChart.options.scales.x.title.font.size = isMobile ? 12 : 14;
            entriesChart.update();
        } else {
            entriesChart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    return context[0].label;
                                },
                                label: function(context) {
                                    const index = context.dataIndex;
                                    const entryCount = context.parsed.y;
                                    const growthValue = growth[index];
                                    let growthText = '';
                                    if (index === 0) {
                                        growthText = ' (perioadă inițială)';
                                    } else if (growthValue > 0) {
                                        growthText = ' (+' + growthValue + '% vs perioada anterioară)';
                                    } else if (growthValue < 0) {
                                        growthText = ' (' + growthValue + '% vs perioada anterioară)';
                                    } else {
                                        growthText = ' (fără schimbare)';
                                    }
                                    return 'Intrări: ' + entryCount + growthText;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0,
                                font: {
                                    size: isMobile ? 10 : 12
                                }
                            },
                            title: {
                                display: true,
                                text: 'Număr intrări',
                                font: {
                                    size: isMobile ? 12 : 14
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Perioadă',
                                font: {
                                    size: isMobile ? 12 : 14
                                }
                            },
                            ticks: {
                                maxRotation: isMobile ? 90 : 45,
                                minRotation: isMobile ? 90 : 45,
                                font: {
                                    size: isMobile ? 10 : 12
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // Load entries report on button click
    document.getElementById('loadEntriesReport').addEventListener('click', function(){
        loadEntriesReport();
    });

    // Load entries report on period type change
    document.getElementById('entriesPeriodType').addEventListener('change', function(){
        loadEntriesReport();
    });

    // Load initial entries report
    loadEntriesReport();

    // Handle window resize to update charts
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            if (hourlyTrafficChart) {
                hourlyTrafficChart.resize();
            }
            if (entriesChart) {
                entriesChart.resize();
            }
        }, 250);
    });

    // Export PDF function
    function exportTrafficPdf() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4',
            compress: true
        });
        
        // Get date range
        const startDate = document.getElementById('reportStart').value;
        const endDate = document.getElementById('reportEnd').value;
        
        // Get selected weekdays
        const weekdayCheckboxes = document.querySelectorAll('input[name="weekdays"]:checked');
        const weekdayNames = ['Duminică', 'Luni', 'Marți', 'Miercuri', 'Joi', 'Vineri', 'Sâmbătă'];
        const selectedWeekdays = Array.from(weekdayCheckboxes).map(cb => {
            const dayIndex = parseInt(cb.value);
            return weekdayNames[dayIndex];
        });
        
        // Format dates with manual formatting
        let dateRangeText = '';
        if (startDate && endDate) {
            const start = new Date(startDate + 'T00:00:00');
            const end = new Date(endDate + 'T00:00:00');
            const formatDate = (date) => {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return day + '.' + month + '.' + year;
            };
            dateRangeText = formatDate(start) + ' - ' + formatDate(end);
        } else {
            dateRangeText = 'Toate datele';
        }
        
        // Function to remove Romanian diacritics
        function removeDiacritics(text) {
            if (!text) return text;
            return text
                .replace(/ă/g, 'a').replace(/Ă/g, 'A')
                .replace(/â/g, 'a').replace(/Â/g, 'A')
                .replace(/î/g, 'i').replace(/Î/g, 'I')
                .replace(/ș/g, 's').replace(/Ș/g, 'S')
                .replace(/ț/g, 't').replace(/Ț/g, 'T');
        }
        
        // Format weekdays text - remove diacritics
        let weekdaysText = '';
        if (selectedWeekdays.length === 7) {
            weekdaysText = 'Toate zilele saptamanii';
        } else if (selectedWeekdays.length > 0) {
            const weekdayNamesNoDiacritics = ['Duminica', 'Luni', 'Marti', 'Miercuri', 'Joi', 'Vineri', 'Sambata'];
            const selectedWeekdaysNoDiacritics = Array.from(weekdayCheckboxes).map(cb => {
                const dayIndex = parseInt(cb.value);
                return weekdayNamesNoDiacritics[dayIndex];
            });
            weekdaysText = selectedWeekdaysNoDiacritics.join(', ');
        } else {
            weekdaysText = 'Nu sunt zile selectate';
        }
        
        // Use helvetica font consistently
        doc.setFont('helvetica');
        
        // Title section - all with same font settings, no diacritics
        doc.setFontSize(16);
        doc.setFont('helvetica', 'bold');
        doc.text('Traficul pe ore in intervalul:', 14, 20);
        
        doc.setFontSize(12);
        doc.setFont('helvetica', 'normal');
        doc.text(dateRangeText, 14, 28);
        
        // Use same font for weekdays text - no diacritics
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(12);
        doc.text('pentru zilele de: ' + weekdaysText, 14, 36);
        
        // Prepare table data - only hours 8-23
        const tableData = [];
        for (let i = 8; i <= 23; i++) {
            const nextHour = i + 1;
            const interval = `${i}-${nextHour}`;
            const count = currentHourlyTrafficData[i] || 0;
            tableData.push([interval, count.toString()]);
        }
        
        // Create table with autoTable plugin - compact with reduced spacing, no diacritics
        doc.autoTable({
            head: [['Interval ore', 'Numar intrari']], // No diacritics
            body: tableData,
            startY: 50,
            theme: 'grid', // Adds grid lines between all rows and columns
            styles: {
                font: 'helvetica',
                fontStyle: 'normal',
                fontSize: 9, // Reduced font size
                textColor: [0, 0, 0],
                lineColor: [0, 0, 0],
                lineWidth: 0.1,
                cellPadding: 2, // Reduced padding for compact table
                overflow: 'linebreak',
                minCellHeight: 5 // Reduced row height
            },
            headStyles: {
                fillColor: [240, 240, 240],
                textColor: [0, 0, 0],
                fontStyle: 'bold',
                fontSize: 10, // Reduced header font size
                lineColor: [0, 0, 0],
                lineWidth: 0.2,
                cellPadding: 2 // Reduced header padding
            },
            bodyStyles: {
                fontSize: 9,
                cellPadding: 2,
                minCellHeight: 5 // Reduced body row height
            },
            columnStyles: {
                0: { cellWidth: 90, halign: 'left' },
                1: { cellWidth: 90, halign: 'center' }
            },
            margin: { left: 14, right: 14 },
            // Reduced row height for compact table
            tableLineWidth: 0.1,
            tableLineColor: [0, 0, 0]
        });
        
        // Save PDF
        const fileName = 'trafic_ore_' + (startDate || 'all') + '_' + (endDate || 'all') + '.pdf';
        doc.save(fileName);
    }
    
    // Attach export PDF button event
    document.getElementById('exportTrafficPdf').addEventListener('click', function() {
        if (currentHourlyTrafficData.length === 0) {
            alert('Nu există date de trafic disponibile. Te rog să încarci mai întâi raportul.');
            return;
        }
        exportTrafficPdf();
    });
</script>
@endsection




