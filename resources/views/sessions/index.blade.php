@extends('layouts.app')

@section('title', 'Sesiuni')
@section('page-title', 'Sesiuni')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-stopwatch text-purple-600"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Lista sesiunilor</h2>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div>
                <label class="text-sm text-gray-600 mr-2" for="dateFilter">Dată</label>
                <input id="dateFilter" type="date" class="px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label class="text-sm text-gray-600 mr-2" for="perPage">Afișează</label>
                <select id="perPage" class="px-3 py-2 border border-gray-300 rounded-md">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="relative">
                <input id="searchInput" type="text" placeholder="Caută copil" class="w-64 px-3 py-2 border border-gray-300 rounded-md pr-8">
                <i class="fas fa-search absolute right-2 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                        <input type="checkbox" id="selectAllCheckbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" onchange="toggleSelectAll()">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" data-sort="child_name">Copil <span class="sort-ind" data-col="child_name"></span></th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durată live</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preț</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Plată</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acțiuni</th>
                </tr>
            </thead>
            <tbody id="tableBody" class="bg-white divide-y divide-gray-200"></tbody>
        </table>
    </div>

    <div class="flex items-center justify-between mt-4">
        <div class="flex items-center gap-3">
            <div class="text-sm text-gray-600" id="resultsInfo"></div>
            <button id="combinedReceiptBtn" class="hidden px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm transition-colors" onclick="openCombinedFiscalModal()">
                <i class="fas fa-receipt mr-2"></i>Generează bon combinat (<span id="selectedCount">0</span>)
            </button>
        </div>
        <div class="flex items-center gap-2">
            <button id="prevPage" class="px-3 py-2 border rounded-md text-sm disabled:opacity-50">Înapoi</button>
            <span id="pageLabel" class="text-sm text-gray-700"></span>
            <button id="nextPage" class="px-3 py-2 border rounded-md text-sm disabled:opacity-50">Înainte</button>
        </div>
    </div>
</div>

<!-- Fiscal Receipt Modal -->
<div id="fiscal-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Bon Fiscal</h3>
            <button onclick="closeFiscalModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-4">
            <!-- Step 1: Payment Type Selection -->
            <div id="fiscal-modal-step-1">
                <p class="text-gray-700 mb-4">Cum se plătește?</p>
                <div class="flex gap-4 mb-6">
                    <button 
                        data-payment-btn="CASH"
                        onclick="selectPaymentType('CASH')"
                        class="flex-1 px-6 py-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors">
                        <i class="fas fa-money-bill-wave mr-2"></i>
                        Cash
                    </button>
                    <button 
                        data-payment-btn="CARD"
                        onclick="selectPaymentType('CARD')"
                        class="flex-1 px-6 py-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors">
                        <i class="fas fa-credit-card mr-2"></i>
                        Card
                    </button>
                </div>
                
                <!-- Voucher Toggle -->
                <div class="mb-6 pt-4 border-t border-gray-200">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               id="voucher-toggle"
                               onchange="toggleVoucherInput()"
                               class="mr-3 w-5 h-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <span class="text-gray-700 font-medium">Folosește Voucher</span>
                    </label>
                    
                    <!-- Voucher Hours Input (hidden by default) -->
                    <div id="voucher-input-container" class="hidden mt-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Ore Voucher <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="voucher-hours-input"
                               min="0"
                               step="0.5"
                               value="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Ex: 1">
                        <p class="mt-1 text-xs text-gray-500">Introduceți numărul de ore de pe voucher</p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button onclick="closeFiscalModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        Anulează
                    </button>
                    <button 
                        id="fiscal-continue-btn"
                        onclick="goToConfirmStep()"
                        disabled
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Continuă
                    </button>
                </div>
            </div>

            <!-- Step 2: Confirmation -->
            <div id="fiscal-modal-step-2" class="hidden">
                <p class="text-gray-700 mb-4 font-medium">Se va scoate bonul fiscal pentru:</p>
                
                <!-- Virtual Receipt Preview -->
                <div id="fiscal-receipt-preview" class="bg-white border-2 border-gray-300 rounded-lg p-4 mb-6 shadow-sm max-h-96 overflow-y-auto">
                    <!-- Receipt Header -->
                    <div class="text-center border-b border-gray-300 pb-2 mb-3">
                        <h4 id="receipt-tenant-name" class="font-bold text-lg text-gray-900">-</h4>
                        <p class="text-xs text-gray-500 mt-1">Bon Fiscal</p>
                    </div>
                    
                    <!-- Receipt Items -->
                    <div id="receipt-items" class="space-y-2 mb-3">
                        <!-- Time item will be inserted here -->
                    </div>
                    
                    <!-- Receipt Totals -->
                    <div class="border-t border-gray-300 pt-2 mt-2">
                        <div class="flex justify-between text-base font-bold">
                            <span class="text-gray-900">TOTAL:</span>
                            <span id="receipt-total-price" class="text-indigo-600">-</span>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="mt-3 pt-3 border-t border-gray-300">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Plată:</span>
                            <span id="receipt-payment-method" class="font-semibold text-gray-900">-</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button onclick="closeFiscalModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        Anulează
                    </button>
                    <button 
                        onclick="confirmAndPrint()"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-check mr-2"></i>
                        Confirmă și Emite
                    </button>
                </div>
            </div>

            <!-- Step 3: Loading -->
            <div id="fiscal-modal-step-3" class="hidden">
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-4"></i>
                    <p class="text-gray-700 text-lg">Se emite bonul fiscal...</p>
                    <p class="text-gray-500 text-sm mt-2">Vă rugăm să așteptați</p>
                </div>
            </div>

            <!-- Step 4: Result (Success/Error) -->
            <div id="fiscal-modal-step-4" class="hidden">
                <div id="fiscal-result-content" class="text-center py-6">
                    <!-- Success or Error icon and message will be inserted here -->
                </div>
                <div class="flex justify-end gap-3 mt-4">
                    <button 
                        onclick="closeFiscalModal()"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Închide
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stop Session Confirmation Modal -->
<div id="stop-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 transform transition-all">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-red-50 rounded-t-xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-stop-circle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Oprește Sesiunea</h3>
            </div>
            <button onclick="closeStopModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-6">
            <p class="text-gray-700 text-lg mb-2">Sigur vrei să oprești sesiunea pentru:</p>
            <p id="stop-modal-child-name" class="text-2xl font-bold text-gray-900 mb-6"></p>
            
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                    <p class="text-sm text-amber-800">
                        Această acțiune va finaliza sesiunea de joacă. După oprire, va trebui să emiteți bonul fiscal pentru plată.
                    </p>
                </div>
            </div>
            
            <div class="flex justify-end gap-3">
                <button 
                    onclick="closeStopModal()" 
                    class="px-5 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors font-medium">
                    Anulează
                </button>
                <button 
                    id="stop-modal-confirm-btn"
                    onclick="confirmStopSession()"
                    class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium flex items-center gap-2">
                    <i class="fas fa-stop-circle"></i>
                    <span>Oprește Sesiunea</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Styles for session row animations -->
<style>
    /* Highlight animation for just-stopped sessions - simple and clean */
    @keyframes session-stopped-highlight {
        0% { 
            background-color: rgba(34, 197, 94, 0.4);
        }
        30% { 
            background-color: rgba(34, 197, 94, 0.3);
        }
        100% { 
            background-color: transparent;
        }
    }
    
    .session-just-stopped {
        animation: session-stopped-highlight 3s ease-out forwards;
    }
    
    /* Fade animation before row moves - no transforms to avoid layout issues */
    @keyframes session-stopping-fade {
        0% { 
            background-color: rgba(239, 68, 68, 0.15);
        }
        40% { 
            background-color: rgba(239, 68, 68, 0.25);
        }
        70% {
            background-color: rgba(251, 191, 36, 0.2);
        }
        100% { 
            background-color: rgba(34, 197, 94, 0.25);
        }
    }
    
    .session-stopping {
        animation: session-stopping-fade 1s ease-out forwards;
    }
</style>

@endsection

@section('scripts')
<script>
    
    // Get current date in local timezone (not UTC)
    function getCurrentLocalDate() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    let state = {
        page: 1,
        per_page: 10,
        sort_by: 'started_at',
        sort_dir: 'desc',
        search: '',
        date: getCurrentLocalDate() // Current date in local timezone (YYYY-MM-DD format)
    };
    let timerIntervals = new Map();
    let pauseWarningIntervals = new Map();
    let selectedSessions = new Set(); // Track selected session IDs
    
    // ===== STOP SESSION MODAL =====
    let stopModalSessionId = null;
    let stopModalChildName = null;

    function openStopModal(sessionId, childName) {
        stopModalSessionId = sessionId;
        stopModalChildName = childName;
        
        // Set child name in modal
        document.getElementById('stop-modal-child-name').textContent = childName || 'Copil necunoscut';
        
        // Reset confirm button state
        const confirmBtn = document.getElementById('stop-modal-confirm-btn');
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-stop-circle"></i><span>Oprește Sesiunea</span>';
            confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
        
        // Show modal
        document.getElementById('stop-modal').classList.remove('hidden');
    }

    function closeStopModal() {
        document.getElementById('stop-modal').classList.add('hidden');
        stopModalSessionId = null;
        stopModalChildName = null;
    }

    async function confirmStopSession() {
        if (!stopModalSessionId) {
            alert('Eroare: Sesiune invalidă');
            closeStopModal();
            return;
        }
        
        const sessionId = stopModalSessionId;
        const childName = stopModalChildName;
        const confirmBtn = document.getElementById('stop-modal-confirm-btn');
        
        // Disable button and show loading state
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Se oprește...</span>';
            confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
        
        try {
            // Find and highlight the row being stopped
            const tableRow = document.querySelector(`[data-stop="${sessionId}"]`)?.closest('tr');
            if (tableRow) {
                tableRow.classList.add('session-stopping');
            }
            
            // Make the API call
            const res = await fetch(`/dashboard-api/sessions/${sessionId}/stop`, { 
                method: 'POST', 
                headers: { 
                    'Accept': 'application/json', 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                }, 
                credentials: 'same-origin' 
            });
            
            const data = await res.json();
            
            if (data.success) {
                // Store the stopped session ID for highlighting after refresh
                sessionStorage.setItem('justStoppedSessionId', sessionId);
                sessionStorage.setItem('justStoppedChildName', childName || '');
                
                // Close modal
                closeStopModal();
                
                // Wait for the "stopping" animation to complete (1s), then refresh
                // This gives the user time to see the visual feedback before the row moves
                setTimeout(() => {
                    fetchData();
                }, 1000);
            } else {
                alert(data.message || 'Nu s-a putut opri sesiunea');
                // Reset button state
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fas fa-stop-circle"></i><span>Oprește Sesiunea</span>';
                    confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
                // Remove stopping class
                if (tableRow) {
                    tableRow.classList.remove('session-stopping');
                }
            }
        } catch (error) {
            console.error('Error stopping session:', error);
            alert('Eroare de rețea la oprirea sesiunii');
            // Reset button state
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-stop-circle"></i><span>Oprește Sesiunea</span>';
                confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    }

    function clearAllTimers() {
        timerIntervals.forEach((intv) => clearInterval(intv));
        timerIntervals.clear();
        pauseWarningIntervals.forEach((intv) => clearInterval(intv));
        pauseWarningIntervals.clear();
    }

    function formatDateTime(iso) {
        if (!iso) return '-';
        const d = new Date(iso);
        const y = d.getFullYear();
        const m = String(d.getMonth()+1).padStart(2,'0');
        const day = String(d.getDate()).padStart(2,'0');
        const hh = String(d.getHours()).padStart(2,'0');
        const mm = String(d.getMinutes()).padStart(2,'0');
        return `${day}.${m}.${y} ${hh}:${mm}`;
    }

    function formatHms(totalSeconds) {
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        const hh = String(hours).padStart(2, '0');
        const mm = String(minutes).padStart(2, '0');
        const ss = String(seconds).padStart(2, '0');
        return `${hh}:${mm}:${ss}`;
    }

    function startLiveTimer(row) {
        const el = document.getElementById(`timer-${row.id}`);
        if (!el) return;
        // If ended -> show fixed effective seconds (server already computed excluding pauses)
        if (row.ended_at) {
            const secs = Math.max(0, parseInt(row.effective_seconds || 0, 10));
            el.textContent = formatHms(secs);
            return;
        }
        // If paused -> show static effective time
        if (row.is_paused) {
            const secs = Math.max(0, parseInt(row.effective_seconds || 0, 10));
            el.textContent = formatHms(secs);
            return;
        }
        // Running -> base + live accumulation
        const base = Math.max(0, parseInt(row.effective_seconds || 0, 10));
        const startMs = row.current_interval_started_at ? Date.parse(row.current_interval_started_at) : null;
        const tick = () => {
            if (!startMs) { el.textContent = formatHms(base); return; }
            const now = Date.now();
            const secs = base + Math.max(0, Math.floor((now - startMs) / 1000));
            el.textContent = formatHms(secs);
        };
        tick();
        const intv = setInterval(tick, 1000);
        timerIntervals.set(row.id, intv);
    }

    function startPauseWarningCheck(row) {
        console.log('Checking pause warning for session:', row.id, {
            ended_at: row.ended_at,
            is_paused: row.is_paused,
            last_pause_end: row.last_pause_end,
            pause_threshold: row.pause_threshold,
            current_pause_minutes: row.current_pause_minutes
        });
        
        // Only check for active sessions that are paused
        if (row.ended_at) {
            console.log(`Session ${row.id} is ended, skipping pause check`);
            return;
        }
        
        if (!row.is_paused) {
            console.log(`Session ${row.id} is not paused, skipping pause check`);
            return;
        }
        
        if (!row.last_pause_end) {
            console.log(`Session ${row.id} has no last_pause_end, skipping pause check`);
            return;
        }

        const warningEl = document.getElementById(`pause-warning-${row.id}`);
        if (!warningEl) {
            console.warn(`Warning element not found for session ${row.id}`);
            return;
        }

        const pauseThreshold = row.pause_threshold || 15;
        const pauseEndMs = Date.parse(row.last_pause_end);
        
        if (!pauseEndMs || isNaN(pauseEndMs)) {
            console.warn(`Invalid pause end time for session ${row.id}:`, row.last_pause_end);
            return;
        }

        console.log(`Starting pause check for session ${row.id}, threshold: ${pauseThreshold} minutes`);

        const checkPause = () => {
            const now = Date.now();
            const pauseSeconds = Math.max(0, Math.floor((now - pauseEndMs) / 1000));
            const pauseMinutes = Math.floor(pauseSeconds / 60);

            console.log(`Session ${row.id} pause check: ${pauseMinutes} minutes (threshold: ${pauseThreshold})`);

            if (pauseMinutes >= pauseThreshold) {
                // Show warning badge with live update (appears when pause equals or exceeds threshold)
                warningEl.innerHTML = `<span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-800 animate-pulse" title="Pauză lungă: ${pauseMinutes} minute (threshold: ${pauseThreshold} minute)"><i class="fas fa-exclamation-triangle mr-1"></i>Pauză ${pauseMinutes}m</span>`;
                console.log(`Showing pause warning for session ${row.id}: ${pauseMinutes} minutes`);
            } else {
                // Clear warning if pause is below threshold
                warningEl.innerHTML = '';
            }
        };

        // Check immediately
        checkPause();
        
        // Check every 10 seconds for live updates (will update badge when threshold is exceeded)
        const intv = setInterval(checkPause, 10000);
        pauseWarningIntervals.set(row.id, intv);
        console.log(`Started pause warning interval for session ${row.id}`);
    }

    async function fetchData() {
        const url = new URL(`{{ route('sessions.data') }}`, window.location.origin);
        url.searchParams.set('page', state.page);
        url.searchParams.set('per_page', state.per_page);
        url.searchParams.set('sort_by', state.sort_by);
        url.searchParams.set('sort_dir', state.sort_dir);
        if (state.search) url.searchParams.set('search', state.search);
        if (state.date) url.searchParams.set('date', state.date);

        const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (!data.success) return;
        renderTable(data.data);
        renderMeta(data.meta);
    }

    function renderTable(rows) {
        clearAllTimers();
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        // Check if there's a just-stopped session to highlight
        const justStoppedSessionId = sessionStorage.getItem('justStoppedSessionId');
        
        rows.forEach(row => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-session-id', row.id);
            
            // Check if this session can be selected (ended, unpaid)
            const canSelect = row.ended_at && !row.is_paid;
            const isSelected = selectedSessions.has(row.id);
            
            // Apply highlight animation if this is the just-stopped session
            if (justStoppedSessionId && parseInt(justStoppedSessionId) === row.id) {
                tr.classList.add('session-just-stopped');
            }
            
            tr.innerHTML = `
                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                    ${canSelect ? `
                        <input type="checkbox" 
                               class="session-checkbox w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" 
                               data-session-id="${row.id}"
                               ${isSelected ? 'checked' : ''}
                               onchange="toggleSessionSelection(${row.id}, this.checked)">
                    ` : ''}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    <div class="flex items-center">
                        <span>${row.child_name || '-'}</span>
                        <span id="pause-warning-${row.id}" class="ml-2">
                            ${(() => {
                                // Only show badge for CURRENT active pause that exceeds threshold
                                // Don't show for historical pauses or if session is not paused
                                if (row.is_paused && !row.ended_at && row.current_pause_minutes >= (row.pause_threshold || 15)) {
                                    return `<span class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-800 animate-pulse" title="Pauză lungă: ${row.current_pause_minutes} minute (threshold: ${row.pause_threshold || 15} minute)"><i class="fas fa-exclamation-triangle mr-1"></i>Pauză ${row.current_pause_minutes}m</span>`;
                                }
                                return '';
                            })()}
                        </span>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-mono" id="timer-${row.id}">--:--:--</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    ${row.formatted_price ? `
                        <span class="font-semibold ${row.ended_at ? 'text-green-600' : 'text-amber-600'}">${row.formatted_price}</span>
                        ${row.products_formatted_price ? `<span class="font-semibold text-purple-600 ml-1">+ ${row.products_formatted_price}</span>` : ''}
                    ` : '-'}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    ${row.ended_at ? `
                        ${row.is_paid ? `
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 inline-flex items-center w-fit">
                                <i class="fas fa-check-circle mr-1"></i>${row.payment_status === 'paid_voucher' ? 'Plătit (Voucher)' : 'Plătit'}
                            </span>
                        ` : `
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-800 inline-flex items-center w-fit">
                                <i class="fas fa-clock mr-1"></i>Neplătit
                            </span>
                        `}
                    ` : '-'}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <div class="flex items-center gap-2">
                        <a href="/sessions/${row.id}/show" class="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs transition-colors">
                            <i class="fas fa-eye mr-1"></i>Detalii
                        </a>
                        ${row.ended_at && !row.is_paid && row.products_price > 0 ? `
                            <button onclick="openFiscalModal(${row.id})" class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs transition-colors">
                                <i class="fas fa-receipt mr-1"></i>Bon
                            </button>
                        ` : ''}
                        ${row.ended_at ? '' : `
                            ${row.is_paused ? `
                                <button data-resume="${row.id}" class="px-2 py-1 bg-green-600 text-white rounded text-xs">Reia</button>
                            ` : `
                                <button data-pause="${row.id}" class="px-2 py-1 bg-yellow-600 text-white rounded text-xs">Pauză</button>
                            `}
                            <button data-stop="${row.id}" data-child-name="${(row.child_name || '').replace(/"/g, '&quot;')}" class="px-2 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs transition-colors">Oprește</button>
                        `}
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
            startLiveTimer(row);
            
            // Debug: log row data for paused sessions
            if (row.is_paused && !row.ended_at) {
                console.log('Paused session found:', {
                    id: row.id,
                    child_name: row.child_name,
                    is_paused: row.is_paused,
                    last_pause_end: row.last_pause_end,
                    pause_threshold: row.pause_threshold,
                    current_pause_minutes: row.current_pause_minutes,
                    has_long_pause: row.has_long_pause
                });
            }
            
            startPauseWarningCheck(row);

            const pauseBtn = tr.querySelector(`[data-pause="${row.id}"]`);
            if (pauseBtn) pauseBtn.addEventListener('click', async () => {
                // Prevent double-click
                if (pauseBtn.disabled) return;
                
                // Disable button immediately and show loader
                pauseBtn.disabled = true;
                const originalContent = pauseBtn.innerHTML;
                pauseBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                pauseBtn.classList.add('opacity-50', 'cursor-not-allowed');
                
                try {
                    const res = await fetch(`/dashboard-api/sessions/${row.id}/pause`, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, credentials: 'same-origin' });
                    const data = await res.json();
                    if (data.success) {
                        fetchData();
                    } else {
                        alert(data.message || 'Nu s-a putut pune pe pauză');
                        pauseBtn.disabled = false;
                        pauseBtn.innerHTML = originalContent;
                        pauseBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                } catch (error) {
                    alert('Eroare de rețea la pauză');
                    pauseBtn.disabled = false;
                    pauseBtn.innerHTML = originalContent;
                    pauseBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
            const resumeBtn = tr.querySelector(`[data-resume="${row.id}"]`);
            if (resumeBtn) resumeBtn.addEventListener('click', async () => {
                // Prevent double-click
                if (resumeBtn.disabled) return;
                
                // Hide pause warning badge immediately
                const warningEl = document.getElementById(`pause-warning-${row.id}`);
                if (warningEl) {
                    warningEl.innerHTML = '';
                }
                
                // Stop pause warning interval for this session
                const pauseInterval = pauseWarningIntervals.get(row.id);
                if (pauseInterval) {
                    clearInterval(pauseInterval);
                    pauseWarningIntervals.delete(row.id);
                }
                
                // Disable button immediately and show loader
                resumeBtn.disabled = true;
                const originalContent = resumeBtn.innerHTML;
                resumeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                resumeBtn.classList.add('opacity-50', 'cursor-not-allowed');
                
                try {
                    const res = await fetch(`/dashboard-api/sessions/${row.id}/resume`, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }, credentials: 'same-origin' });
                    const data = await res.json();
                    if (data.success) {
                        fetchData();
                    } else {
                        alert(data.message || 'Nu s-a putut relua');
                        resumeBtn.disabled = false;
                        resumeBtn.innerHTML = originalContent;
                        resumeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                } catch (error) {
                    alert('Eroare de rețea la reluare');
                    resumeBtn.disabled = false;
                    resumeBtn.innerHTML = originalContent;
                    resumeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
            const stopBtn = tr.querySelector(`[data-stop="${row.id}"]`);
            if (stopBtn) stopBtn.addEventListener('click', () => {
                // Open confirmation modal instead of direct action
                const childName = stopBtn.getAttribute('data-child-name') || row.child_name || 'Copil necunoscut';
                openStopModal(row.id, childName);
            });
        });
        
        // Clear the just-stopped session from storage after rendering and scroll to it
        if (justStoppedSessionId) {
            // Find the row with the just-stopped session and scroll to it smoothly
            const stoppedRow = document.querySelector(`tr[data-session-id="${justStoppedSessionId}"]`);
            if (stoppedRow) {
                // Wait a tiny bit for the DOM to settle, then scroll
                setTimeout(() => {
                    stoppedRow.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }, 50);
            }
            
            // Clear storage after animation completes (3 seconds for the highlight)
            setTimeout(() => {
                sessionStorage.removeItem('justStoppedSessionId');
                sessionStorage.removeItem('justStoppedChildName');
            }, 3000);
        }
    }

    function renderMeta(meta) {
        const info = document.getElementById('resultsInfo');
        const pageLabel = document.getElementById('pageLabel');
        const prev = document.getElementById('prevPage');
        const next = document.getElementById('nextPage');

        const from = (meta.page - 1) * meta.per_page + 1;
        const to = Math.min(meta.page * meta.per_page, meta.total);
        info.textContent = meta.total ? `Afișate ${from}-${to} din ${meta.total}` : 'Nu există rezultate';
        pageLabel.textContent = `Pagina ${meta.page} / ${meta.total_pages || 1}`;
        prev.disabled = meta.page <= 1;
        next.disabled = meta.page >= (meta.total_pages || 1);

        // Update sort indicators
        document.querySelectorAll('.sort-ind').forEach(el => {
            const col = el.getAttribute('data-col');
            el.textContent = col === state.sort_by ? (state.sort_dir === 'asc' ? '▲' : '▼') : '';
        });
    }

    // Events
    document.getElementById('perPage').addEventListener('change', (e) => {
        state.per_page = parseInt(e.target.value, 10);
        state.page = 1;
        fetchData();
    });

    let searchDebounce;
    document.getElementById('searchInput').addEventListener('input', (e) => {
        const v = e.target.value.trim();
        if (searchDebounce) clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            state.search = v;
            state.page = 1;
            fetchData();
        }, 300);
    });

    document.getElementById('dateFilter').addEventListener('change', (e) => {
        state.date = e.target.value;
        state.page = 1;
        fetchData();
    });

    document.getElementById('prevPage').addEventListener('click', () => {
        if (state.page > 1) {
            state.page -= 1;
            fetchData();
        }
    });
    document.getElementById('nextPage').addEventListener('click', () => {
        state.page += 1;
        fetchData();
    });

    document.querySelectorAll('th[data-sort]').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.getAttribute('data-sort');
            if (state.sort_by === col) {
                state.sort_dir = state.sort_dir === 'asc' ? 'desc' : 'asc';
            } else {
                state.sort_by = col;
                state.sort_dir = 'asc';
            }
            state.page = 1;
            fetchData();
        });
    });

    // Set default date in input
    function initDateFilter() {
        const dateInput = document.getElementById('dateFilter');
        if (dateInput) {
            dateInput.value = state.date;
        }
    }
    
    // Initialize date filter when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDateFilter);
    } else {
        initDateFilter();
    }

    // Initial load
    fetchData();
    
    // ===== MULTIPLE SESSION SELECTION =====
    
    function toggleSessionSelection(sessionId, checked) {
        if (checked) {
            selectedSessions.add(sessionId);
        } else {
            selectedSessions.delete(sessionId);
        }
        updateCombinedButton();
    }
    
    function toggleSelectAll() {
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const checkboxes = document.querySelectorAll('.session-checkbox');
        const checked = selectAllCheckbox.checked;
        
        checkboxes.forEach(checkbox => {
            if (checkbox.checked !== checked) {
                checkbox.checked = checked;
                const sessionId = parseInt(checkbox.getAttribute('data-session-id'));
                toggleSessionSelection(sessionId, checked);
            }
        });
    }
    
    function updateCombinedButton() {
        const btn = document.getElementById('combinedReceiptBtn');
        const countSpan = document.getElementById('selectedCount');
        const count = selectedSessions.size;
        
        if (count >= 2) {
            btn.classList.remove('hidden');
            countSpan.textContent = count;
        } else {
            btn.classList.add('hidden');
        }
        
        // Update select all checkbox state
        const checkboxes = document.querySelectorAll('.session-checkbox');
        const checkedCheckboxes = Array.from(checkboxes).filter(cb => cb.checked);
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = checkboxes.length > 0 && checkedCheckboxes.length === checkboxes.length;
            selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < checkboxes.length;
        }
    }
    
    function openCombinedFiscalModal() {
        if (selectedSessions.size < 2) {
            alert('Selectați minim 2 sesiuni pentru bon combinat');
            return;
        }
        
        fiscalModalSessionId = null; // Clear single session ID
        fiscalModalCurrentStep = 1;
        fiscalModalPaymentType = null;
        fiscalModalData = null;
        fiscalModalReceiptData = null;
        
        // Reset modal state
        document.getElementById('fiscal-modal-step-1').classList.remove('hidden');
        document.getElementById('fiscal-modal-step-2').classList.add('hidden');
        document.getElementById('fiscal-modal-step-3').classList.add('hidden');
        document.getElementById('fiscal-modal-step-4').classList.add('hidden');
        
        // Reset continue button state
        const continueBtn = document.getElementById('fiscal-continue-btn');
        if (continueBtn) {
            continueBtn.disabled = true;
            continueBtn.innerHTML = 'Continuă';
        }
        
        // Reset payment buttons selection
        document.querySelectorAll('[data-payment-btn]').forEach(btn => {
            btn.classList.remove('bg-indigo-600', 'ring-2', 'ring-indigo-500');
            btn.classList.add('bg-gray-200', 'hover:bg-gray-300');
        });
        
        // Reset voucher toggle and input
        const voucherToggle = document.getElementById('voucher-toggle');
        const voucherInput = document.getElementById('voucher-hours-input');
        const voucherContainer = document.getElementById('voucher-input-container');
        if (voucherToggle) {
            voucherToggle.checked = false;
        }
        if (voucherInput) {
            voucherInput.value = '0';
        }
        if (voucherContainer) {
            voucherContainer.classList.add('hidden');
        }
        
        // Show modal
        document.getElementById('fiscal-modal').classList.remove('hidden');
    }
    
    // ===== FISCAL RECEIPT MODAL =====

    let fiscalModalCurrentStep = 1;
    let fiscalModalSessionId = null;
    let fiscalModalPaymentType = null;
    let fiscalModalData = null;
    let fiscalModalReceiptData = null;

    function openFiscalModal(sessionId) {
        fiscalModalSessionId = sessionId;
        fiscalModalCurrentStep = 1;
        fiscalModalPaymentType = null;
        fiscalModalData = null;
        fiscalModalReceiptData = null;
        
        // Reset modal state
        document.getElementById('fiscal-modal-step-1').classList.remove('hidden');
        document.getElementById('fiscal-modal-step-2').classList.add('hidden');
        document.getElementById('fiscal-modal-step-3').classList.add('hidden');
        document.getElementById('fiscal-modal-step-4').classList.add('hidden');
        
        // Reset continue button state
        const continueBtn = document.getElementById('fiscal-continue-btn');
        if (continueBtn) {
            continueBtn.disabled = true;
            continueBtn.innerHTML = 'Continuă';
        }
        
        // Reset payment buttons selection
        document.querySelectorAll('[data-payment-btn]').forEach(btn => {
            btn.classList.remove('bg-indigo-600', 'ring-2', 'ring-indigo-500');
            btn.classList.add('bg-gray-200', 'hover:bg-gray-300');
        });
        
        // Reset voucher toggle and input
        const voucherToggle = document.getElementById('voucher-toggle');
        const voucherInput = document.getElementById('voucher-hours-input');
        const voucherContainer = document.getElementById('voucher-input-container');
        if (voucherToggle) {
            voucherToggle.checked = false;
        }
        if (voucherInput) {
            voucherInput.value = '0';
        }
        if (voucherContainer) {
            voucherContainer.classList.add('hidden');
        }
        
        // Show modal
        document.getElementById('fiscal-modal').classList.remove('hidden');
    }

    function closeFiscalModal() {
        document.getElementById('fiscal-modal').classList.add('hidden');
        fiscalModalCurrentStep = 1;
        // Don't clear fiscalModalSessionId if it was a combined receipt (it's already null)
        // Only clear if it was a single session receipt
        if (fiscalModalSessionId !== null) {
            fiscalModalSessionId = null;
        }
        fiscalModalPaymentType = null;
        fiscalModalData = null;
        fiscalModalReceiptData = null;
        
        // Reset continue button state
        const continueBtn = document.getElementById('fiscal-continue-btn');
        if (continueBtn) {
            continueBtn.disabled = true;
            continueBtn.innerHTML = 'Continuă';
        }
    }

    function toggleVoucherInput() {
        const voucherToggle = document.getElementById('voucher-toggle');
        const voucherContainer = document.getElementById('voucher-input-container');
        const voucherInput = document.getElementById('voucher-hours-input');
        
        if (voucherToggle && voucherContainer) {
            if (voucherToggle.checked) {
                voucherContainer.classList.remove('hidden');
                if (voucherInput) {
                    voucherInput.focus();
                }
            } else {
                voucherContainer.classList.add('hidden');
                if (voucherInput) {
                    voucherInput.value = '0';
                }
            }
        }
    }

    function selectPaymentType(type) {
        fiscalModalPaymentType = type;
        
        // Update UI
        document.querySelectorAll('[data-payment-btn]').forEach(btn => {
            btn.classList.remove('bg-indigo-600', 'ring-2', 'ring-indigo-500');
            btn.classList.add('bg-gray-200', 'hover:bg-gray-300');
        });
        
        const selectedBtn = document.querySelector(`[data-payment-btn="${type}"]`);
        if (selectedBtn) {
            selectedBtn.classList.remove('bg-gray-200', 'hover:bg-gray-300');
            selectedBtn.classList.add('bg-indigo-600', 'ring-2', 'ring-indigo-500');
        }
        
        // Enable continue button
        document.getElementById('fiscal-continue-btn').disabled = false;
    }

    async function goToConfirmStep() {
        if (!fiscalModalPaymentType) {
            alert('Selectați o metodă de plată');
            return;
        }
        
        // Check if single session or multiple sessions
        const isCombined = !fiscalModalSessionId && selectedSessions.size >= 2;
        const isSingle = fiscalModalSessionId !== null;
        
        if (!isSingle && !isCombined) {
            alert(isCombined ? 'Selectați minim 2 sesiuni' : 'Sesiune invalidă');
            return;
        }
        
        // Get voucher hours if voucher is enabled
        const voucherToggle = document.getElementById('voucher-toggle');
        const voucherInput = document.getElementById('voucher-hours-input');
        let voucherHours = 0;
        
        if (voucherToggle && voucherToggle.checked && voucherInput) {
            voucherHours = parseFloat(voucherInput.value) || 0;
            if (voucherHours < 0) {
                alert('Orele de voucher trebuie să fie pozitive');
                return;
            }
        }
        
        // Show loading state
        const continueBtn = document.getElementById('fiscal-continue-btn');
        const originalBtnText = continueBtn.innerHTML;
        continueBtn.disabled = true;
        continueBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Se încarcă...';
        
        try {
            // Get prepared data from server
            const url = isCombined 
                ? '/sessions/prepare-combined-fiscal-print'
                : `/sessions/${fiscalModalSessionId}/prepare-fiscal-print`;
            
            const body = isCombined
                ? {
                    session_ids: Array.from(selectedSessions),
                    paymentType: fiscalModalPaymentType,
                    voucherHours: voucherHours > 0 ? voucherHours : null
                }
                : {
                    paymentType: fiscalModalPaymentType,
                    voucherHours: voucherHours > 0 ? voucherHours : null
                };
            
            const prepareResponse = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(body)
            });

            if (!prepareResponse.ok) {
                const errorData = await prepareResponse.json();
                throw new Error(errorData.message || 'Eroare la pregătirea datelor');
            }

            const prepareData = await prepareResponse.json();
            
            if (!prepareData.success || !prepareData.data) {
                throw new Error('Date invalide de la server');
            }

            fiscalModalData = prepareData.data;
            fiscalModalReceiptData = prepareData.receipt || {};

            // Check if no receipt is needed (voucher covers everything)
            if (fiscalModalReceiptData.noReceiptNeeded) {
                // Mark as paid with voucher directly
                await markPaidWithVoucherDirectly(voucherHours);
                return;
            }

            // Update receipt preview with data
            const receipt = fiscalModalReceiptData;
            
            // Tenant name
            document.getElementById('receipt-tenant-name').textContent = receipt.tenantName || '-';
            
            // Receipt items
            const receiptItems = document.getElementById('receipt-items');
            receiptItems.innerHTML = '';
            
            // For combined receipts, show all original time items with full hours and prices
            // Then show subtotal, voucher (if any), and final total
            // For single receipts, show single time item
            if (isCombined && receipt.originalTimeItems) {
                // Show all original time items with full hours (before voucher)
                const originalTimeItems = receipt.originalTimeItems;
                
                originalTimeItems.forEach((timeItem) => {
                    const timeItemDiv = document.createElement('div');
                    timeItemDiv.className = 'flex justify-between text-sm';
                    timeItemDiv.innerHTML = `
                        <div>
                            <span class="font-medium text-gray-900">Ora de joacă (${timeItem.duration})</span>
                            <span class="text-gray-500 ml-2 text-xs">- ${timeItem.childName}</span>
                        </div>
                        <span class="font-semibold text-gray-900">${parseFloat(timeItem.price).toFixed(2)} RON</span>
                    `;
                    receiptItems.appendChild(timeItemDiv);
                });
                
                // Show products items
                if (receipt.products && receipt.products.length > 0) {
                    receipt.products.forEach(product => {
                        const productItem = document.createElement('div');
                        productItem.className = 'flex justify-between text-sm';
                        productItem.innerHTML = `
                            <div>
                                <span class="font-medium text-gray-900">${product.name}</span>
                                <span class="text-gray-500 ml-2">×${product.quantity}</span>
                            </div>
                            <span class="font-semibold text-gray-900">${parseFloat(product.total_price).toFixed(2)} RON</span>
                        `;
                        receiptItems.appendChild(productItem);
                    });
                }
                
                // Show subtotal (before voucher)
                const subtotalDiv = document.createElement('div');
                subtotalDiv.className = 'flex justify-between text-sm border-t border-gray-300 pt-2 mt-2';
                subtotalDiv.innerHTML = `
                    <span class="font-medium text-gray-700">Subtotal:</span>
                    <span class="font-semibold text-gray-900">${parseFloat(receipt.totalPrice || 0).toFixed(2)} RON</span>
                `;
                receiptItems.appendChild(subtotalDiv);
                
                // Show voucher discount (if any)
                if (receipt.voucherHours > 0 && receipt.voucherPrice > 0) {
                    const voucherDiv = document.createElement('div');
                    voucherDiv.className = 'flex justify-between text-sm text-green-600';
                    voucherDiv.innerHTML = `
                        <span class="font-medium">Voucher (${receipt.voucherHours}h):</span>
                        <span class="font-semibold">-${parseFloat(receipt.voucherPrice).toFixed(2)} RON</span>
                    `;
                    receiptItems.appendChild(voucherDiv);
                }
                
                // Set total price for combined receipt
                document.getElementById('receipt-total-price').textContent = `${parseFloat(receipt.finalPrice || prepareData.data.price || 0).toFixed(2)} RON`;
            } else {
                // Single receipt - show time item
                // Time item - ALWAYS show original price (timePrice) with original duration (durationFiscalized) in preview
                // This is the price BEFORE voucher discount
                // Even if voucher covers all time, show it in preview (but it won't appear on actual receipt)
                if (receipt.timePrice > 0) {
                    const timeItem = document.createElement('div');
                    timeItem.className = 'flex justify-between text-sm';
                    timeItem.innerHTML = `
                        <div>
                            <span class="font-medium text-gray-900">${prepareData.data.productName}</span>
                            <span class="text-gray-500 ml-2">${receipt.durationFiscalized || prepareData.data.duration}</span>
                        </div>
                        <span class="font-semibold text-gray-900">${parseFloat(receipt.timePrice || 0).toFixed(2)} RON</span>
                    `;
                    receiptItems.appendChild(timeItem);
                }
                
                // Single receipt - also show products items
                if (receipt.products && receipt.products.length > 0) {
                    receipt.products.forEach(product => {
                        const productItem = document.createElement('div');
                        productItem.className = 'flex justify-between text-sm';
                        productItem.innerHTML = `
                            <div>
                                <span class="font-medium text-gray-900">${product.name}</span>
                                <span class="text-gray-500 ml-2">×${product.quantity}</span>
                            </div>
                            <span class="font-semibold text-gray-900">${parseFloat(product.total_price).toFixed(2)} RON</span>
                        `;
                        receiptItems.appendChild(productItem);
                    });
                }
                
                // Single receipt - show voucher discount line if voucher was used
                if (receipt.voucherHours > 0 && receipt.voucherPrice > 0) {
                    const voucherItem = document.createElement('div');
                    voucherItem.className = 'flex justify-between text-sm text-green-600 border-t border-gray-300 pt-2 mt-2';
                    voucherItem.innerHTML = `
                        <div>
                            <span class="font-medium">Voucher (${receipt.voucherHours}h)</span>
                        </div>
                        <span class="font-semibold">-${parseFloat(receipt.voucherPrice).toFixed(2)} RON</span>
                    `;
                    receiptItems.appendChild(voucherItem);
                }
                
                // Total price (final price after voucher) - this is what will be on the receipt
                document.getElementById('receipt-total-price').textContent = `${parseFloat(receipt.finalPrice || prepareData.data.price || 0).toFixed(2)} RON`;
            }
            
            // Payment method
            document.getElementById('receipt-payment-method').textContent = fiscalModalPaymentType === 'CASH' ? 'Cash' : 'Card';
            
            // Go to confirmation step
            fiscalModalCurrentStep = 2;
            document.getElementById('fiscal-modal-step-1').classList.add('hidden');
            document.getElementById('fiscal-modal-step-2').classList.remove('hidden');
        } catch (error) {
            console.error('Error:', error);
            // Show error in modal instead of alert
            showFiscalResult('error', error.message, null);
            continueBtn.disabled = false;
            continueBtn.innerHTML = originalBtnText;
        }
    }

    async function markPaidWithVoucherDirectly(voucherHours) {
        try {
            const response = await fetch(`/sessions/${fiscalModalSessionId}/mark-paid-with-voucher`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    voucher_hours: voucherHours
                })
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Eroare la marcarea sesiunii ca plătită');
            }

            const result = await response.json();
            
            if (result.success) {
                showFiscalResult('success', 'Valoarea orelor a fost acoperită de voucher. Sesiunea va fi trecută plătită fără a mai scoate bon fiscal.', null);
                setTimeout(() => {
                    fetchData();
                    closeFiscalModal();
                }, 5000); // 5 seconds to allow operator to read the message
            } else {
                throw new Error(result.message || 'Eroare necunoscută');
            }
        } catch (error) {
            console.error('Error:', error);
            showFiscalResult('error', error.message, null);
        }
    }

    async function confirmAndPrint() {
        if (!fiscalModalPaymentType || !fiscalModalData) {
            alert('Date incomplete');
            return;
        }
        
        // Check if single session or multiple sessions
        const isCombined = !fiscalModalSessionId && selectedSessions.size >= 2;
        const isSingle = fiscalModalSessionId !== null;
        
        if (!isSingle && !isCombined) {
            alert('Date incomplete');
            return;
        }
        
        // Go to loading step
        fiscalModalCurrentStep = 3;
        document.getElementById('fiscal-modal-step-2').classList.add('hidden');
        document.getElementById('fiscal-modal-step-3').classList.remove('hidden');
        
        try {
            // Use already prepared data from goToConfirmStep
            const prepareData = {
                success: true,
                data: fiscalModalData
            };

            // Step 2: Send directly to local bridge from browser
            const bridgeUrl = '{{ config("services.fiscal_bridge.url", "http://localhost:9000") }}';
            const bridgeResponse = await fetch(`${bridgeUrl}/print`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(prepareData.data)
            });

            if (!bridgeResponse.ok) {
                const errorText = await bridgeResponse.text();
                let errorData;
                try {
                    errorData = JSON.parse(errorText);
                } catch {
                    throw new Error(`Eroare HTTP ${bridgeResponse.status}: ${errorText.substring(0, 100)}`);
                }
                throw new Error(errorData.message || errorData.details || 'Eroare de la bridge-ul fiscal');
            }

            const bridgeData = await bridgeResponse.json();

            // Save log to database
            try {
                const voucherHours = fiscalModalData.voucherHours || 0;
                if (isCombined) {
                    await saveCombinedFiscalReceiptLog({
                        play_session_ids: Array.from(selectedSessions),
                        filename: bridgeData.file || null,
                        status: bridgeData.status === 'success' ? 'success' : 'error',
                        error_message: bridgeData.status === 'success' ? null : (bridgeData.message || bridgeData.details || 'Eroare necunoscută'),
                        voucher_hours: voucherHours > 0 ? voucherHours : null,
                        payment_status: voucherHours > 0 ? 'paid_voucher' : 'paid',
                        payment_method: fiscalModalPaymentType,
                    });
                } else {
                    await saveFiscalReceiptLog({
                        play_session_id: fiscalModalSessionId,
                        filename: bridgeData.file || null,
                        status: bridgeData.status === 'success' ? 'success' : 'error',
                        error_message: bridgeData.status === 'success' ? null : (bridgeData.message || bridgeData.details || 'Eroare necunoscută'),
                        voucher_hours: voucherHours > 0 ? voucherHours : null,
                        payment_status: voucherHours > 0 ? 'paid_voucher' : 'paid',
                        payment_method: fiscalModalPaymentType,
                    });
                }
            } catch (logError) {
                console.error('Error saving log:', logError);
                // Don't block the UI if log saving fails
            }

            // Show result in modal
            if (bridgeData.status === 'success') {
                showFiscalResult('success', 'Bon fiscal emis cu succes!', bridgeData.file || null);
                // Clear selected sessions if combined receipt
                if (isCombined) {
                    selectedSessions.clear();
                    updateCombinedButton();
                }
                // Refresh table to reflect payment status changes
                fetchData();
            } else {
                const errorMessage = bridgeData.message || bridgeData.details || 'Eroare necunoscută';
                showFiscalResult('error', errorMessage, null);
                // Refresh table even on error to ensure consistency
                fetchData();
            }
        } catch (error) {
            console.error('Error:', error);
            
            // Save error log to database
            try {
                const voucherHours = fiscalModalData?.voucherHours || 0;
                if (isCombined) {
                    await saveCombinedFiscalReceiptLog({
                        play_session_ids: Array.from(selectedSessions),
                        filename: null,
                        status: 'error',
                        error_message: error.message.includes('Failed to fetch') || error.message.includes('NetworkError')
                            ? 'Nu s-a putut conecta la bridge-ul fiscal local. Verifică că serviciul Node.js rulează pe calculatorul tău.'
                            : error.message,
                        voucher_hours: voucherHours > 0 ? voucherHours : null,
                        payment_status: voucherHours > 0 ? 'paid_voucher' : 'paid',
                        payment_method: fiscalModalPaymentType,
                    });
                } else {
                    await saveFiscalReceiptLog({
                        play_session_id: fiscalModalSessionId,
                        filename: null,
                        status: 'error',
                        error_message: error.message.includes('Failed to fetch') || error.message.includes('NetworkError')
                            ? 'Nu s-a putut conecta la bridge-ul fiscal local. Verifică că serviciul Node.js rulează pe calculatorul tău.'
                            : error.message,
                        voucher_hours: voucherHours > 0 ? voucherHours : null,
                        payment_status: voucherHours > 0 ? 'paid_voucher' : 'paid',
                        payment_method: fiscalModalPaymentType,
                    });
                }
            } catch (logError) {
                console.error('Error saving log:', logError);
                // Don't block the UI if log saving fails
            }
            
            // Show error in modal
            const errorMessage = error.message.includes('Failed to fetch') || error.message.includes('NetworkError')
                ? 'Nu s-a putut conecta la bridge-ul fiscal local. Verifică că serviciul Node.js rulează pe calculatorul tău.'
                : error.message;
            
            showFiscalResult('error', errorMessage, null);
            // Refresh table even on error to ensure consistency
            fetchData();
        }
    }

    async function saveFiscalReceiptLog(data) {
        try {
            const response = await fetch('{{ route("sessions.save-fiscal-receipt-log") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Eroare la salvarea logului');
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Error saving fiscal receipt log:', error);
            throw error;
        }
    }

    async function saveCombinedFiscalReceiptLog(data) {
        try {
            const response = await fetch('{{ route("sessions.save-combined-fiscal-receipt-log") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Eroare la salvarea logului');
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Error saving combined fiscal receipt log:', error);
            throw error;
        }
    }

function showFiscalResult(type, message, file) {
    fiscalModalCurrentStep = 4;
    
    // Hide all steps
    document.getElementById('fiscal-modal-step-1').classList.add('hidden');
    document.getElementById('fiscal-modal-step-2').classList.add('hidden');
    document.getElementById('fiscal-modal-step-3').classList.add('hidden');
    document.getElementById('fiscal-modal-step-4').classList.remove('hidden');
    
    // Build result content
    const resultContent = document.getElementById('fiscal-result-content');
    
    if (type === 'success') {
        resultContent.innerHTML = `
            <div class="mb-4">
                <i class="fas fa-check-circle text-5xl text-green-500 mb-4"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Bon fiscal emis cu succes!</h3>
            <p class="text-gray-700 mb-2">${message}</p>
            ${file ? `<p class="text-sm text-gray-500">Fișier: ${file}</p>` : ''}
        `;
    } else {
        resultContent.innerHTML = `
            <div class="mb-4">
                <i class="fas fa-exclamation-circle text-5xl text-red-500 mb-4"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Eroare</h3>
            <p class="text-gray-700">${message}</p>
        `;
    }
}

</script>
@endsection




