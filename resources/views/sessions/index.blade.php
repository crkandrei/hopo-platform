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
            <a href="{{ route('standalone-receipts.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition flex items-center gap-2">
                <i class="fas fa-receipt"></i>Bon Specific
            </a>
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
                <i class="fas fa-receipt mr-2"></i><span id="combinedReceiptLabel">Generează bon combinat</span> (<span id="selectedCount">0</span>)
            </button>
        </div>
        <div class="flex items-center gap-2">
            <button id="prevPage" class="px-3 py-2 border rounded-md text-sm disabled:opacity-50">Înapoi</button>
            <span id="pageLabel" class="text-sm text-gray-700"></span>
            <button id="nextPage" class="px-3 py-2 border rounded-md text-sm disabled:opacity-50">Înainte</button>
        </div>
    </div>
</div>

@include('partials.payment-wizard')

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

        // Update location-level fiscal flag (all rows belong to same location)
        if (rows.length > 0) {
            fiscalEnabled = rows[0].fiscal_enabled !== false;
        }
        // Update combined receipt button label based on fiscal setting
        const combinedReceiptLabel = document.getElementById('combinedReceiptLabel');
        if (combinedReceiptLabel) {
            combinedReceiptLabel.textContent = fiscalEnabled ? 'Generează bon combinat' : 'Plată combinată';
        }

        // Check if there's a just-stopped session to highlight
        const justStoppedSessionId = sessionStorage.getItem('justStoppedSessionId');

        rows.forEach(row => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-session-id', row.id);
            
            // Check if this session can be selected (ended, unpaid, not birthday)
            const canSelect = row.ended_at && !row.is_paid && row.session_type !== 'birthday';
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
                    ${row.formatted_price !== null && row.formatted_price !== undefined ? `
                        <span class="font-semibold ${row.ended_at ? 'text-green-600' : 'text-amber-600'}">${row.formatted_price}</span>
                        ${row.products_formatted_price ? `<span class="font-semibold text-purple-600 ml-1">+ ${row.products_formatted_price}</span>` : ''}
                        ${row.is_free ? `<span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-slate-200 text-slate-800">Gratuit</span>` : ''}
                        <button type="button" data-toggle-birthday="${row.id}" class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full cursor-pointer transition-colors ${row.session_type === 'birthday' ? 'bg-pink-100 text-pink-800 hover:bg-pink-200' : 'bg-gray-100 text-gray-400 hover:bg-pink-50 hover:text-pink-600'}" title="${row.session_type === 'birthday' ? 'Click pentru a dezactiva Birthday' : 'Click pentru a marca ca Birthday'}">
                            <i class="fas fa-birthday-cake mr-0.5"></i>${row.session_type === 'birthday' ? 'Birthday' : 'Birthday'}
                        </button>
                    ` : '-'}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    ${row.ended_at ? `
                        ${row.is_paid ? `
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 inline-flex items-center w-fit">
                                <i class="fas fa-check-circle mr-1"></i>${(() => { const hasVoucher = row.payment_status === 'paid_voucher' || (row.voucher_hours && row.voucher_hours > 0); const method = row.payment_method === 'CASH' ? 'Cash' : (row.payment_method === 'CARD' ? 'Card' : null); if (hasVoucher && method) return `Plătit (Voucher + ${method})`; if (hasVoucher) return 'Plătit (Voucher)'; if (method) return `Plătit (${method})`; return 'Plătit'; })()}
                            </span>
                        ` : `
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-800 inline-flex items-center w-fit">
                                <i class="fas fa-clock mr-1"></i>Neplătit
                            </span>
                        `}
                    ` : '-'}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm">
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="/sessions/${row.id}/show" class="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs transition-colors">
                            <i class="fas fa-eye mr-1"></i>Detalii
                        </a>
                        ${row.ended_at && !row.is_paid && !row.is_free && row.session_type !== 'birthday' ? `
                            <button onclick="window.PaymentWizard && window.PaymentWizard.open({ type: 'session', sessionId: ${row.id}, locationId: ${row.location_id || 'null'}, fiscalEnabled: ${row.fiscal_enabled !== false} })" class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs transition-colors">
                                <i class="fas fa-receipt mr-1"></i>${row.fiscal_enabled ? 'Bon' : 'Plată'}
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
            if (row.location_id) window.sessionIdToLocationId[row.id] = row.location_id;
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
            const markFreeBtn = tr.querySelector(`[data-mark-free="${row.id}"]`);
            if (markFreeBtn) markFreeBtn.addEventListener('click', async () => {
                if (markFreeBtn.disabled) return;
                if (!confirm('Marcați această sesiune ca gratuită? Copilul nu va mai fi facturat.')) return;
                markFreeBtn.disabled = true;
                markFreeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                try {
                    const res = await fetch(`/sessions/${row.id}/mark-free`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                        body: JSON.stringify({})
                    });
                    const data = await res.json();
                    if (data.success) fetchData();
                    else alert(data.message || 'Eroare');
                } catch (e) {
                    alert('Eroare de rețea');
                }
                markFreeBtn.disabled = false;
                markFreeBtn.innerHTML = '<i class="fas fa-gift mr-1"></i>Gratuit';
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
                const childName = stopBtn.getAttribute('data-child-name') || row.child_name || 'Copil necunoscut';
                openStopModal(row.id, childName);
            });
            const birthdayBtn = tr.querySelector(`[data-toggle-birthday="${row.id}"]`);
            if (birthdayBtn) birthdayBtn.addEventListener('click', async () => {
                if (birthdayBtn.disabled) return;
                birthdayBtn.disabled = true;
                const originalHTML = birthdayBtn.innerHTML;
                birthdayBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                try {
                    const res = await fetch(`/sessions/${row.id}/toggle-session-type`, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        credentials: 'same-origin'
                    });
                    const data = await res.json();
                    if (data.success) {
                        fetchData();
                    } else {
                        alert(data.message || 'Eroare la schimbarea tipului sesiunii');
                        birthdayBtn.disabled = false;
                        birthdayBtn.innerHTML = originalHTML;
                    }
                } catch (e) {
                    alert('Eroare de rețea');
                    birthdayBtn.disabled = false;
                    birthdayBtn.innerHTML = originalHTML;
                }
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
        const firstId = Array.from(selectedSessions)[0];
        const locationId = window.sessionIdToLocationId[firstId] || null;
        if (window.PaymentWizard) {
            window.PaymentWizard.open({ type: 'combined', sessionIds: Array.from(selectedSessions), locationId: locationId, fiscalEnabled: true });
        }
    }

    window.sessionIdToLocationId = window.sessionIdToLocationId || {};
    window.PaymentWizardConfig = {
        getLocationId: function(ctx) {
            if (ctx.type === 'session') return ctx.locationId || window.sessionIdToLocationId[ctx.sessionId] || null;
            if (ctx.type === 'combined') return ctx.locationId || null;
            if (ctx.type === 'standalone') return ctx.locationId || null;
            return null;
        },
        getVoucherValidationType: function(ctx) {
            if (ctx.type === 'standalone') return 'amount';
            return null;
        },
        prepare: function(ctx, paymentType, voucherCode) {
            if (ctx.type === 'session') {
                return { url: `/sessions/${ctx.sessionId}/prepare-fiscal-print`, body: { paymentType: paymentType, voucher_code: voucherCode || null } };
            }
            if (ctx.type === 'combined') {
                return { url: '/sessions/prepare-combined-fiscal-print', body: { session_ids: ctx.sessionIds, paymentType: paymentType, voucher_code: voucherCode || null } };
            }
            if (ctx.type === 'standalone') {
                return { url: `/standalone-receipts/${ctx.receiptId}/prepare-fiscal-print`, body: { paymentType: paymentType, voucher_code: voucherCode || null } };
            }
            return { url: null, body: {} };
        },
        noReceiptNeeded: function(receipt) { return receipt && receipt.noReceiptNeeded === true; },
        markPaidWithVoucherOnly: async function(ctx, voucherCode, showResult) {
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            if (ctx.type === 'session') {
                const res = await fetch(`/sessions/${ctx.sessionId}/mark-paid-with-voucher`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(voucherCode ? { voucher_code: voucherCode } : {}) });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Eroare');
                showResult('success', 'Valoarea a fost acoperită de voucher. Sesiunea a fost marcată plătită fără bon fiscal.', null);
                setTimeout(() => { fetchData(); if (window.PaymentWizard) window.PaymentWizard.close(); }, 5000);
                return;
            }
            if (ctx.type === 'combined') {
                const res = await fetch('/sessions/mark-combined-paid-with-voucher', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify({ session_ids: ctx.sessionIds, voucher_code: voucherCode || null }) });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Eroare');
                showResult('success', 'Valoarea a fost acoperită de voucher. Sesiunile au fost marcate plătite fără bon fiscal.', null);
                setTimeout(() => { selectedSessions.clear(); updateCombinedButton(); fetchData(); if (window.PaymentWizard) window.PaymentWizard.close(); }, 5000);
                return;
            }
            if (ctx.type === 'standalone') {
                const payload = { payment_method: null, voucher_code: voucherCode || null };
                if (ctx.voucherId) payload.voucher_id = ctx.voucherId;
                if (ctx.voucherAmountUsed != null) payload.voucher_amount_used = ctx.voucherAmountUsed;
                const res = await fetch(`/standalone-receipts/${ctx.receiptId}/mark-paid-no-fiscal`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(payload) });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Eroare');
                showResult('success', 'Valoarea a fost acoperită de voucher. Bonul a fost marcat plătit.', null);
                if (ctx.onStandaloneSuccess) ctx.onStandaloneSuccess();
                return;
            }
        },
        renderReceiptPreview: function(receipt, data, itemsEl, tenantEl, totalEl, methodEl) {
            if (!itemsEl) return;
            itemsEl.innerHTML = '';
            if (tenantEl) tenantEl.textContent = (receipt && receipt.locationName) || (receipt && receipt.tenantName) || '-';
            if (methodEl) methodEl.textContent = data.paymentType === 'CASH' ? 'Cash' : 'Card';
            if (receipt && receipt.originalTimeItems) {
                receipt.originalTimeItems.forEach(function(t) {
                    const d = document.createElement('div');
                    d.className = 'flex justify-between text-sm';
                    d.innerHTML = '<div><span class="font-medium text-gray-900">Ora de joacă (' + t.duration + ')</span><span class="text-gray-500 ml-2 text-xs">- ' + (t.childName || '') + '</span></div><span class="font-semibold text-gray-900">' + parseFloat(t.price).toFixed(2) + ' RON</span>';
                    itemsEl.appendChild(d);
                });
                if (receipt.products && receipt.products.length) {
                    receipt.products.forEach(function(p) {
                        const d = document.createElement('div');
                        d.className = 'flex justify-between text-sm';
                        d.innerHTML = '<div><span class="font-medium text-gray-900">' + p.name + '</span><span class="text-gray-500 ml-2">×' + p.quantity + '</span></div><span class="font-semibold text-gray-900">' + parseFloat(p.total_price).toFixed(2) + ' RON</span>';
                        itemsEl.appendChild(d);
                    });
                }
                const sub = document.createElement('div');
                sub.className = 'flex justify-between text-sm border-t border-gray-300 pt-2 mt-2';
                sub.innerHTML = '<span class="font-medium text-gray-700">Subtotal:</span><span class="font-semibold text-gray-900">' + parseFloat(receipt.totalPrice || 0).toFixed(2) + ' RON</span>';
                itemsEl.appendChild(sub);
                if ((receipt.voucherHours > 0 || receipt.voucher_id) && receipt.voucherPrice > 0) {
                    const v = document.createElement('div');
                    v.className = 'flex justify-between text-sm text-green-600';
                    v.innerHTML = '<span class="font-medium">Voucher' + (receipt.voucherHours > 0 ? ' (' + receipt.voucherHours + 'h):' : ':') + '</span><span class="font-semibold">-' + parseFloat(receipt.voucherPrice).toFixed(2) + ' RON</span>';
                    itemsEl.appendChild(v);
                }
            } else if (!data || !data.items || !data.items.length) {
                if (receipt && receipt.timePrice > 0) {
                    const d = document.createElement('div');
                    d.className = 'flex justify-between text-sm';
                    d.innerHTML = '<div><span class="font-medium text-gray-900">' + (data.productName || 'Ora de joacă') + '</span><span class="text-gray-500 ml-2">' + (receipt.durationFiscalized || data.duration || '') + '</span></div><span class="font-semibold text-gray-900">' + parseFloat(receipt.timePrice).toFixed(2) + ' RON</span>';
                    itemsEl.appendChild(d);
                }
                if (receipt && receipt.products && receipt.products.length) {
                    receipt.products.forEach(function(p) {
                        const d = document.createElement('div');
                        d.className = 'flex justify-between text-sm';
                        d.innerHTML = '<div><span class="font-medium text-gray-900">' + p.name + '</span><span class="text-gray-500 ml-2">×' + p.quantity + '</span></div><span class="font-semibold text-gray-900">' + parseFloat(p.total_price).toFixed(2) + ' RON</span>';
                        itemsEl.appendChild(d);
                    });
                }
                if (receipt && (receipt.voucherHours > 0 || receipt.voucher_id) && receipt.voucherPrice > 0) {
                    const v = document.createElement('div');
                    v.className = 'flex justify-between text-sm text-green-600 border-t border-gray-300 pt-2 mt-2';
                    v.innerHTML = '<div><span class="font-medium">Voucher' + (receipt.voucherHours > 0 ? ' (' + receipt.voucherHours + 'h)' : '') + '</span></div><span class="font-semibold">-' + parseFloat(receipt.voucherPrice).toFixed(2) + ' RON</span>';
                    itemsEl.appendChild(v);
                }
            } else if (data && data.items && data.items.length) {
                data.items.forEach(function(item) {
                    var d = document.createElement('div');
                    d.className = 'flex justify-between text-sm';
                    var lineTotal = (item.quantity || 1) * (parseFloat(item.price) || 0);
                    d.innerHTML = '<div><span class="font-medium text-gray-900">' + (item.name || '') + '</span><span class="text-gray-500 ml-2">×' + (item.quantity || 1) + '</span></div><span class="font-semibold text-gray-900">' + lineTotal.toFixed(2) + ' RON</span>';
                    itemsEl.appendChild(d);
                });
                if (receipt && receipt.discount_amount > 0) {
                    var v = document.createElement('div');
                    v.className = 'flex justify-between text-sm text-green-600 border-t border-gray-300 pt-2 mt-2';
                    v.innerHTML = '<span class="font-medium">Voucher</span><span class="font-semibold">-' + parseFloat(receipt.discount_amount).toFixed(2) + ' RON</span>';
                    itemsEl.appendChild(v);
                }
            }
            var total = (receipt && receipt.finalPrice != null) ? receipt.finalPrice : (data && data.price != null ? data.price : 0);
            if (totalEl) totalEl.textContent = parseFloat(total).toFixed(2) + ' RON';
        },
        confirmAndPrint: async function(ctx, opts) {
            const paymentType = opts.paymentType;
            const preparedData = opts.preparedData;
            const receiptData = opts.receiptData || {};
            const getVoucherCode = opts.getVoucherCode;
            const csrfToken = opts.csrfToken;
            const bridgeUrl = opts.bridgeUrl;
            const showResult = opts.showResult;
            const isCombined = ctx.type === 'combined';
            const isSingle = ctx.type === 'session';
            const fiscalEnabled = ctx.type === 'standalone' ? (ctx.fiscalEnabled !== false) : (ctx.fiscalEnabled !== false);

            if (!fiscalEnabled) {
                const voucherHours = preparedData.voucherHours || 0;
                const voucherId = preparedData.voucher_id || null;
                const voucherCode = preparedData.voucher_code || (getVoucherCode && getVoucherCode()) || null;
                const voucherAmountUsed = preparedData.voucher_discount_amount || null;
                let url, body;
                if (isCombined) {
                    url = '/sessions/mark-combined-paid-no-fiscal';
                    body = { session_ids: ctx.sessionIds, payment_method: paymentType, voucher_hours: voucherHours > 0 ? voucherHours : null, voucher_id: voucherId, voucher_code: voucherCode, voucher_amount_used: voucherAmountUsed > 0 ? voucherAmountUsed : null };
                } else if (ctx.type === 'standalone') {
                    url = `/standalone-receipts/${ctx.receiptId}/mark-paid-no-fiscal`;
                    body = { payment_method: paymentType, voucher_code: voucherCode || null, voucher_id: preparedData.voucher_id || null, voucher_amount_used: (preparedData.voucher_discount_amount || 0) > 0 ? preparedData.voucher_discount_amount : null };
                } else {
                    url = `/sessions/${ctx.sessionId}/mark-paid-no-fiscal`;
                    body = { payment_method: paymentType, voucher_hours: voucherHours > 0 ? voucherHours : null, voucher_id: voucherId, voucher_code: voucherCode, voucher_amount_used: voucherAmountUsed > 0 ? voucherAmountUsed : null };
                }
                const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: JSON.stringify(body) });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Eroare');
                showResult('success', 'Plata a fost înregistrată cu succes.', null);
                if (isCombined) { selectedSessions.clear(); updateCombinedButton(); }
                if (ctx.type === 'standalone' && ctx.onStandaloneSuccess) ctx.onStandaloneSuccess();
                else fetchData();
                return;
            }
            const bridgeRes = await fetch(bridgeUrl + '/print', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(preparedData) });
            if (!bridgeRes.ok) {
                var errText = await bridgeRes.text();
                try { var err = JSON.parse(errText); errText = err.message || err.details || errText; } catch (_) {}
                throw new Error(errText || 'Eroare bridge');
            }
            const bridgeData = await bridgeRes.json();
            var saveLogUrl = ctx.type === 'standalone' ? '{{ route("standalone-receipts.save-fiscal-receipt-log") }}' : (isCombined ? '{{ route("sessions.save-combined-fiscal-receipt-log") }}' : '{{ route("sessions.save-fiscal-receipt-log") }}');
            var logBody = ctx.type === 'standalone' ? { standalone_receipt_id: ctx.receiptId, filename: bridgeData.file || null, status: bridgeData.status === 'success' ? 'success' : 'error', error_message: bridgeData.status === 'success' ? null : (bridgeData.message || bridgeData.details), payment_method: paymentType, voucher_id: preparedData.voucher_id || null, voucher_amount_used: preparedData.voucher_discount_amount || null } : (isCombined ? { play_session_ids: ctx.sessionIds, filename: bridgeData.file || null, status: bridgeData.status === 'success' ? 'success' : 'error', error_message: bridgeData.status === 'success' ? null : (bridgeData.message || bridgeData.details), voucher_hours: (preparedData.voucherHours || 0) > 0 ? preparedData.voucherHours : null, voucher_id: preparedData.voucher_id || null, voucher_amount_used: preparedData.voucher_discount_amount || null, payment_status: (preparedData.voucher_id || (preparedData.voucherHours || 0) > 0) ? 'paid_voucher' : 'paid', payment_method: paymentType } : { play_session_id: ctx.sessionId, filename: bridgeData.file || null, status: bridgeData.status === 'success' ? 'success' : 'error', error_message: bridgeData.status === 'success' ? null : (bridgeData.message || bridgeData.details), voucher_hours: (preparedData.voucherHours || 0) > 0 ? preparedData.voucherHours : null, voucher_id: preparedData.voucher_id || null, voucher_amount_used: preparedData.voucher_discount_amount || null, payment_status: (preparedData.voucher_id || (preparedData.voucherHours || 0) > 0) ? 'paid_voucher' : 'paid', payment_method: paymentType });
            await fetch(saveLogUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: JSON.stringify(logBody) });
            if (bridgeData.status === 'success') {
                showResult('success', 'Bon fiscal emis cu succes!', bridgeData.file || null);
                if (isCombined) { selectedSessions.clear(); updateCombinedButton(); }
                if (ctx.type === 'standalone' && ctx.onStandaloneSuccess) ctx.onStandaloneSuccess();
            } else {
                showResult('error', bridgeData.message || bridgeData.details || 'Eroare', null);
            }
            if (ctx.type !== 'standalone') fetchData();
        },
        onSuccess: function() {
            fetchData();
        },
        showError: function(msg) { alert(msg); }
    };
</script>
@include('partials.payment-wizard-script')
<script>
</script>
@endsection




