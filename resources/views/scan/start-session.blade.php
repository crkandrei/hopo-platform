@extends('layouts.app')

@section('title', 'Start Sesiune')
@section('page-title', 'Start Sesiune')

@section('content')
<div class="space-y-6">
    <!-- Sticky search bar -->
    <div class="sticky top-0 z-30 bg-white border-b border-gray-200 py-4 -mx-6 px-6">
        <div class="max-w-6xl mx-auto px-0 space-y-3">
            <!-- Sesiuni active info -->
            <div id="activeSessionsInfo" class="bg-gray-50 border border-gray-200 rounded-md px-4 py-2 text-xs text-gray-600">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span>
                            <i class="fas fa-play-circle text-green-600 mr-1"></i>
                            <span id="activeSessionsCount">0</span> copii activi
                        </span>
                        <span>
                            <i class="fas fa-pause-circle text-amber-600 mr-1"></i>
                            <span id="pausedSessionsCount">0</span> copii în pauză
                        </span>
                    </div>
                    <span id="activeSessionsLastUpdate" class="text-gray-400"></span>
                </div>
            </div>

            <!-- Child Search + New Child button -->
            <div class="flex items-center gap-3 relative">
                <label for="childSearchInput" class="sr-only">Caută copil</label>
                <div class="flex-1 relative">
                    <input id="childSearchInput"
                           type="text"
                           autocomplete="off"
                           maxlength="100"
                           class="w-full h-12 px-4 text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                           placeholder="Caută copil după nume sau telefon părinte...">

                    <!-- Search results dropdown -->
                    <div id="childSearchResults" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-md shadow-lg z-50 max-h-72 overflow-y-auto">
                        <div id="childSearchResultsList" class="py-1"></div>
                    </div>
                </div>

                <button id="newChildBtn"
                        class="h-12 px-5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-md transition flex items-center gap-2 whitespace-nowrap">
                    <i class="fas fa-user-plus"></i>
                    <span>+ Copil nou</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main content area -->
    <div class="max-w-6xl mx-auto px-0 space-y-6">

        <!-- State card (instructions / errors) -->
        <div id="stateCard" class="bg-white border border-gray-300 rounded-lg p-6" aria-live="polite">
            <div class="text-gray-500">Căutați un copil sau apăsați <strong>+ Copil nou</strong> pentru a porni o sesiune.</div>
        </div>

        <!-- Active Session section (hidden by default) -->
        <div id="activeSessionSection" class="hidden bg-white border border-gray-300 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sesiune activă</h3>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Session Info -->
                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-gray-600">Copil</div>
                        <div id="sessionChildName" class="text-2xl font-bold text-gray-900">-</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-600">Sesiune începută la</div>
                        <div id="sessionStartedAt" class="font-medium text-gray-900">-</div>
                    </div>
                </div>

                <!-- Timer & Controls -->
                <div class="flex flex-col items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-50 rounded-lg p-6">
                    <div class="text-sm text-gray-600 mb-2">Timp de joacă</div>
                    <div id="sessionTimer" class="text-5xl font-bold text-indigo-700 mb-6">00:00:00</div>

                    <div class="flex gap-3 w-full">
                        <button
                            id="pauseResumeBtn"
                            class="flex-1 h-12 px-4 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-md transition flex items-center justify-center gap-2">
                            <i class="fas fa-pause"></i>
                            <span>Pauză</span>
                        </button>
                        <button
                            id="stopSessionBtn"
                            class="flex-1 h-12 px-4 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md transition flex items-center justify-center gap-2">
                            <i class="fas fa-stop"></i>
                            <span>Stop</span>
                        </button>
                    </div>

                    <div id="sessionStatus" class="mt-3 text-sm font-medium"></div>
                </div>
            </div>

            <!-- Products Section -->
            <div id="productsSection" class="mt-6 border-t border-gray-200 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-md font-semibold text-gray-900">Produse</h4>
                    <button
                        id="addProductsBtn"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        Adaugă Produse
                    </button>
                </div>

                <div id="productsList" class="space-y-2">
                    <p class="text-sm text-gray-500">Nu sunt produse adăugate</p>
                </div>
            </div>
        </div>

        <!-- Assignment section (hidden by default) -->
        <div id="assignmentSection" class="hidden bg-white border border-gray-300 rounded-lg p-6">
            <!-- Tabs -->
            <div class="mb-4 border-b border-gray-200">
                <nav class="flex gap-2" role="tablist">
                    <button id="tabAssignExisting" type="button" aria-selected="true"
                        class="px-4 py-2 text-base font-semibold rounded-t-md bg-gray-100 text-gray-900">
                        <i class="fas fa-user-check mr-2"></i>Asignează <span class="font-bold">COPIL</span> existent
                    </button>
                    <button id="tabCreateNew" type="button" aria-selected="false"
                        class="px-4 py-2 text-sm font-medium rounded-t-md text-gray-600 hover:text-gray-900">
                        <i class="fas fa-user-plus mr-2"></i>Creează copil nou
                    </button>
                </nav>
            </div>

            <!-- Opțiune 1: Asignează copil existent -->
            <div id="assignExistingPanel" class="mb-8 pb-8 border-b border-gray-200">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Copil selectat:</label>
                        <div id="selectedChildDisplay" class="p-3 bg-indigo-50 border border-indigo-200 rounded-md">
                            <span id="selectedChildName" class="font-semibold text-indigo-900">-</span>
                            <span id="selectedChildGuardian" class="text-sm text-indigo-600 ml-2"></span>
                        </div>
                    </div>

                    <button
                        id="assignChildBtn"
                        disabled
                        class="w-full h-11 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Pornește sesiunea pentru copilul selectat
                    </button>
                    <div id="childSelectionStatus" class="hidden text-sm text-amber-600 mt-2"></div>
                </div>
            </div>

            <!-- Opțiune 2: Creează copil nou -->
            <div id="createNewPanel" class="hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Părinte -->
                    <div class="space-y-3" id="guardianSection">
                        <div class="mb-3">
                            <div class="flex gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="guardianMode" value="existing" id="radioExistingGuardian" checked class="mr-2">
                                    <span class="text-sm font-medium text-gray-700">Existent</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="guardianMode" value="new" id="radioNewGuardian" class="mr-2">
                                    <span class="text-sm font-medium text-gray-700">Nou</span>
                                </label>
                            </div>
                        </div>

                        <!-- Panel: Părinte existent -->
                        <div id="existingGuardianPanel" class="space-y-2">
                            <label class="block text-sm font-semibold text-green-800">Selectează părinte existent</label>
                            <div id="guardianSelectWrapper">
                                <select id="guardianSelect" class="w-full">
                                    <option value="">Caută și selectează părinte...</option>
                                </select>
                            </div>
                            <p class="text-xs text-gray-500">Sugestie: tastează nume sau telefon pentru a căuta rapid</p>
                        </div>

                        <!-- Panel: Părinte nou -->
                        <div id="newGuardianPanel" class="space-y-2 hidden">
                            <label class="block text-sm font-semibold text-green-800">Creează părinte nou</label>
                            <input id="guardianName" type="text" placeholder="Nume complet *" class="w-full h-10 px-3 border border-green-300 rounded-md" style="text-transform: uppercase;">
                            <input id="guardianPhone" type="tel" placeholder="Telefon *" class="w-full h-10 px-3 border border-green-300 rounded-md">
                            <p class="text-xs text-gray-500">Completează minim nume și telefon</p>
                        </div>
                    </div>

                    <!-- Copil (apare după ce ai selectat/completat părintele) -->
                    <div id="childSection" class="space-y-3 hidden">
                        <input id="childFullName" type="text" placeholder="Nume complet copil *"
                            class="w-full h-10 px-3 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            style="text-transform: uppercase;">

                        <!-- Terms and GDPR Acceptance (only for new guardian) -->
                        <div id="termsAcceptanceSection" class="hidden space-y-3 pt-2 border-t border-gray-200">
                            <div class="flex items-start">
                                <input id="terms_accepted" type="checkbox" value="1"
                                    class="mt-1 w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                <label for="terms_accepted" class="ml-2 text-sm text-gray-700">
                                    Accept
                                    <a href="{{ route('legal.terms.public') }}" target="_blank" class="text-green-600 hover:text-green-800 underline">
                                        Termenii și Condițiile
                                    </a>
                                    <span class="text-red-500">*</span>
                                </label>
                            </div>
                            <div class="flex items-start">
                                <input id="gdpr_accepted" type="checkbox" value="1"
                                    class="mt-1 w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                <label for="gdpr_accepted" class="ml-2 text-sm text-gray-700">
                                    Accept
                                    <a href="{{ route('legal.gdpr.public') }}" target="_blank" class="text-green-600 hover:text-green-800 underline">
                                        Politica GDPR
                                    </a>
                                    <span class="text-red-500">*</span>
                                </label>
                            </div>
                        </div>

                        <button
                            id="createAndAssignBtn"
                            class="w-full h-11 bg-green-600 hover:bg-green-700 text-white font-medium rounded-md transition mt-4">
                            Creează și pornește sesiunea
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Labeled divider -->
        <div class="flex items-center gap-3 text-xs uppercase tracking-wider text-gray-500">
            <div class="h-px bg-gray-200 flex-1"></div>
            <span>Istoric recent</span>
            <div class="h-px bg-gray-200 flex-1"></div>
        </div>

        <!-- Recent completed sessions -->
        <div id="recentCompletedSection" class="hidden bg-white border border-gray-300 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-history mr-2"></i>Ultimele sesiuni închise</h3>
            <div id="recentCompletedList" class="divide-y divide-gray-200"></div>
        </div>
    </div>
</div>

<!-- Modal acceptare termeni (părinte existent) -->
<div id="termsAcceptanceModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Acceptare Termeni și Condiții</h3>
        <p class="text-sm text-gray-600 mb-4">
            Pentru a continua, trebuie să acceptați termenii și condițiile și politica GDPR.
        </p>
        <div class="space-y-3 mb-4">
            <div class="flex items-start">
                <input id="modal_terms_accepted" type="checkbox" value="1"
                    class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="modal_terms_accepted" class="ml-2 text-sm text-gray-700">
                    Accept
                    <a href="{{ route('legal.terms.public') }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">
                        Termenii și Condițiile
                    </a>
                    <span class="text-red-500">*</span>
                </label>
            </div>
            <div class="flex items-start">
                <input id="modal_gdpr_accepted" type="checkbox" value="1"
                    class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="modal_gdpr_accepted" class="ml-2 text-sm text-gray-700">
                    Accept
                    <a href="{{ route('legal.gdpr.public') }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">
                        Politica GDPR
                    </a>
                    <span class="text-red-500">*</span>
                </label>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button id="cancelTermsModalBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                Anulează
            </button>
            <button id="acceptTermsModalBtn" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                Accept
            </button>
        </div>
    </div>
</div>

<!-- Add Products Modal -->
<div id="addProductsModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div id="addProductsOverlay" class="fixed inset-0 bg-black bg-opacity-50"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Adaugă Produs la Sesiune</h3>
                <button id="closeAddProductsModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label for="productsSelect" class="block text-sm font-medium text-gray-700 mb-2">
                            Selectează Produs <span class="text-red-500">*</span>
                        </label>
                        <select id="productsSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Selectează produs...</option>
                        </select>
                    </div>
                    <div>
                        <label for="productQuantity" class="block text-sm font-medium text-gray-700 mb-2">
                            Cantitate (bucăți) <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               id="productQuantity"
                               min="1"
                               value="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" id="cancelAddProducts" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                        Anulează
                    </button>
                    <button type="button" id="saveAddProducts" class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                        <i class="fas fa-plus mr-2"></i>
                        Adaugă
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // ===== STATE =====
    let currentSession = null;
    let currentChildId = null;
    let currentChild = null;
    let timerInterval = null;
    let isProcessing = false;
    let sessionProducts = [];
    let currentSessionId = null;
    let guardianChoices = null;
    let guardianSearchTimeout = null;
    let pendingTermsGuardianId = null;

    // bracelet_code is always null for this page
    const BRACELET_CODE = null;

    // ===== HELPERS =====

    async function apiCall(url, options = {}) {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
        if (!csrfToken) throw new Error('Token CSRF lipsă. Te rugăm să reîncarci pagina.');

        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            credentials: 'same-origin'
        });

        const contentType = response.headers.get('content-type');
        const responseText = await response.text();
        let data;

        if (contentType && contentType.includes('application/json')) {
            try { data = JSON.parse(responseText); }
            catch (e) {
                const err = new Error('Răspuns invalid de la server');
                err.data = { message: 'Serverul a returnat un răspuns invalid.' };
                err.status = response.status;
                throw err;
            }
        } else {
            const err = new Error('Serverul a returnat o eroare HTML');
            err.data = { message: response.status === 404 ? 'Endpoint-ul nu a fost găsit.' : `Eroare HTTP ${response.status}` };
            err.status = response.status;
            throw err;
        }

        if (!response.ok) {
            if (response.status === 419) {
                const err = new Error('Token CSRF expirat. Te rugăm să reîncarci pagina.');
                err.data = { message: 'Token CSRF expirat.', csrf_mismatch: true };
                err.status = 419;
                throw err;
            }
            const err = new Error(data.message || 'Request failed');
            err.data = data;
            err.status = response.status;
            throw err;
        }

        return data;
    }

    function formatTime(seconds) {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    }

    function formatDateTime(isoString) {
        if (!isoString) return '-';
        const d = new Date(isoString);
        return `${String(d.getDate()).padStart(2,'0')}.${String(d.getMonth()+1).padStart(2,'0')}.${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
    }

    function formatDuration(seconds) {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    }

    // ===== TIMER =====

    function updateTimer() {
        if (!currentSession) return;
        let totalSeconds = currentSession.effective_seconds || 0;
        if (!currentSession.is_paused && currentSession.fetched_at) {
            totalSeconds += Math.floor((Date.now() - currentSession.fetched_at) / 1000);
        }
        document.getElementById('sessionTimer').textContent = formatTime(totalSeconds);
    }

    function startTimer() {
        stopTimer();
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    }

    function stopTimer() {
        if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
    }

    // ===== RENDER ACTIVE SESSION =====

    function renderActiveSession(data) {
        const section = document.getElementById('activeSessionSection');

        if (!data || !data.active_session) {
            section.classList.add('hidden');
            stopTimer();
            currentSession = null;
            currentSessionId = null;
            return;
        }

        currentSession = { ...data.active_session, fetched_at: Date.now() };
        currentSessionId = currentSession.id;

        document.getElementById('sessionChildName').textContent = data.child ? data.child.name : '-';
        document.getElementById('sessionStartedAt').textContent = formatDateTime(currentSession.started_at);

        updateTimer();

        const pauseResumeBtn = document.getElementById('pauseResumeBtn');
        const stopSessionBtn = document.getElementById('stopSessionBtn');
        const statusDiv = document.getElementById('sessionStatus');

        stopSessionBtn.disabled = false;
        stopSessionBtn.innerHTML = '<i class="fas fa-stop"></i><span>Stop</span>';

        if (currentSession.is_paused) {
            pauseResumeBtn.disabled = false;
            pauseResumeBtn.innerHTML = '<i class="fas fa-play"></i><span>Reia</span>';
            pauseResumeBtn.className = 'flex-1 h-12 px-4 bg-green-500 hover:bg-green-600 text-white font-medium rounded-md transition flex items-center justify-center gap-2';
            statusDiv.innerHTML = '<span class="text-amber-600">⏸ Sesiune în pauză</span>';
            stopTimer();
        } else {
            pauseResumeBtn.disabled = false;
            pauseResumeBtn.innerHTML = '<i class="fas fa-pause"></i><span>Pauză</span>';
            pauseResumeBtn.className = 'flex-1 h-12 px-4 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-md transition flex items-center justify-center gap-2';
            statusDiv.innerHTML = '<span class="text-green-600">▶ Sesiune activă</span>';
            startTimer();
        }

        // Load products
        if (currentSessionId) initializeProductsForSession(currentSessionId);

        section.classList.remove('hidden');
    }

    // ===== SHOW ASSIGN PANEL FOR A SELECTED CHILD =====

    function showAssignPanelForChild(child) {
        currentChild = child;
        currentChildId = child.id;

        // Update selected child display
        document.getElementById('selectedChildName').textContent = child.name;
        const guardianInfo = child.guardian_name
            ? (child.guardian_phone ? `${child.guardian_name} (${child.guardian_phone})` : child.guardian_name)
            : '';
        document.getElementById('selectedChildGuardian').textContent = guardianInfo;

        // Activate tab 1
        activateTab('existing');

        document.getElementById('stateCard').classList.add('hidden');
        document.getElementById('activeSessionSection').classList.add('hidden');
        document.getElementById('assignmentSection').classList.remove('hidden');
        document.getElementById('assignChildBtn').disabled = false;
    }

    function activateTab(tab) {
        const existingPanel = document.getElementById('assignExistingPanel');
        const createPanel = document.getElementById('createNewPanel');
        const tabExisting = document.getElementById('tabAssignExisting');
        const tabCreate = document.getElementById('tabCreateNew');

        if (tab === 'existing') {
            existingPanel.classList.remove('hidden');
            createPanel.classList.add('hidden');
            tabExisting.className = 'px-4 py-2 text-base font-semibold rounded-t-md bg-gray-100 text-gray-900';
            tabCreate.className = 'px-4 py-2 text-sm font-medium rounded-t-md text-gray-600 hover:text-gray-900';
            tabExisting.setAttribute('aria-selected', 'true');
            tabCreate.setAttribute('aria-selected', 'false');
        } else {
            existingPanel.classList.add('hidden');
            createPanel.classList.remove('hidden');
            tabExisting.className = 'px-4 py-2 text-sm font-medium rounded-t-md text-gray-600 hover:text-gray-900';
            tabCreate.className = 'px-4 py-2 text-base font-semibold rounded-t-md bg-gray-100 text-gray-900';
            tabExisting.setAttribute('aria-selected', 'false');
            tabCreate.setAttribute('aria-selected', 'true');

            // Init Choices.js for guardian select
            initGuardianChoices();
        }
    }

    // ===== CHILD SEARCH =====

    const childSearchInput = document.getElementById('childSearchInput');
    const childSearchResults = document.getElementById('childSearchResults');
    const childSearchResultsList = document.getElementById('childSearchResultsList');
    let childSearchTimeout = null;

    childSearchInput.addEventListener('input', function() {
        const q = this.value.trim();
        clearTimeout(childSearchTimeout);
        if (q.length < 1) {
            hideChildSearch();
            return;
        }
        childSearchTimeout = setTimeout(() => performChildSearch(q), 300);
    });

    childSearchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideChildSearch();
            this.value = '';
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            const first = childSearchResultsList.querySelector('.cs-item');
            if (first) first.focus();
        }
    });

    document.addEventListener('click', function(e) {
        if (!childSearchInput.contains(e.target) && !childSearchResults.contains(e.target)) {
            hideChildSearch();
        }
    });

    async function performChildSearch(q) {
        try {
            const url = new URL('/children-search', window.location.origin);
            url.searchParams.set('q', q);
            url.searchParams.set('limit', '15');
            const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();

            if (data.success && data.children && data.children.length > 0) {
                renderChildSearchResults(data.children);
            } else {
                renderChildSearchNoResults(q);
            }
        } catch (e) {
            console.error('Child search error:', e);
            hideChildSearch();
        }
    }

    function renderChildSearchResults(children) {
        childSearchResultsList.innerHTML = children.map(child => {
            const guardian = child.guardian_name
                ? (child.guardian_phone ? `${child.guardian_name} · ${child.guardian_phone}` : child.guardian_name)
                : '';
            return `
            <div class="cs-item px-4 py-3 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0 focus:outline-none focus:bg-gray-100"
                 tabindex="0"
                 data-child-id="${child.id}"
                 data-child-name="${child.name}"
                 data-guardian-name="${child.guardian_name || ''}"
                 data-guardian-phone="${child.guardian_phone || ''}">
                <div class="font-medium text-gray-900">${child.name}</div>
                ${guardian ? `<div class="text-sm text-gray-500">${guardian}</div>` : ''}
            </div>`;
        }).join('');

        childSearchResultsList.querySelectorAll('.cs-item').forEach(item => {
            item.addEventListener('click', () => selectChildFromSearch(item));
            item.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') selectChildFromSearch(item);
                else if (e.key === 'ArrowDown') { e.preventDefault(); const n = item.nextElementSibling; if (n) n.focus(); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); const p = item.previousElementSibling; if (p) p.focus(); else childSearchInput.focus(); }
            });
        });

        childSearchResults.classList.remove('hidden');
    }

    function renderChildSearchNoResults(q) {
        childSearchResultsList.innerHTML = `<div class="px-4 py-3 text-sm text-gray-500 italic">Niciun copil găsit pentru "${q}"</div>`;
        childSearchResults.classList.remove('hidden');
    }

    function hideChildSearch() {
        childSearchResults.classList.add('hidden');
        childSearchResultsList.innerHTML = '';
    }

    async function selectChildFromSearch(item) {
        hideChildSearch();

        const childId = item.getAttribute('data-child-id');
        const childName = item.getAttribute('data-child-name');
        const guardianName = item.getAttribute('data-guardian-name');
        const guardianPhone = item.getAttribute('data-guardian-phone');

        childSearchInput.value = '';

        // Look up the child's session state
        try {
            const result = await apiCall(`/scan-api/child-session/${childId}`);

            // Child HAS an active session → show session card
            if (result.success && result.active_session) {
                currentChild = result.child;
                currentChildId = parseInt(childId);

                document.getElementById('stateCard').classList.add('hidden');
                document.getElementById('assignmentSection').classList.add('hidden');
                renderActiveSession({
                    child: result.child,
                    active_session: result.active_session
                });
            }
        } catch (e) {
            // 404 = child has no active session → show assign panel
            if (e.status === 404) {
                showAssignPanelForChild({
                    id: parseInt(childId),
                    name: childName,
                    guardian_name: guardianName,
                    guardian_phone: guardianPhone,
                });
            } else {
                let msg = 'Eroare la accesarea datelor copilului';
                if (e.data && e.data.message) msg = e.data.message;
                else if (e.message) msg = e.message;
                showStateError(msg);
            }
        }
    }

    function showStateError(msg) {
        const card = document.getElementById('stateCard');
        card.innerHTML = `
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-red-100 text-red-700 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Eroare</h3>
                    <p class="text-red-800">${msg}</p>
                </div>
            </div>`;
        card.classList.remove('hidden');
    }

    // ===== RESET CREATE FORM =====

    function resetCreateForm() {
        // Radio → back to "Existent"
        document.getElementById('radioExistingGuardian').checked = true;
        document.getElementById('radioNewGuardian').checked = false;

        // Show/hide correct panels
        document.getElementById('existingGuardianPanel').classList.remove('hidden');
        document.getElementById('newGuardianPanel').classList.add('hidden');
        document.getElementById('childSection').classList.add('hidden');
        document.getElementById('termsAcceptanceSection').classList.add('hidden');

        // Clear text fields
        document.getElementById('guardianName').value = '';
        document.getElementById('guardianPhone').value = '';
        document.getElementById('childFullName').value = '';
        document.getElementById('terms_accepted').checked = false;
        document.getElementById('gdpr_accepted').checked = false;

        // Reset Choices.js guardian select
        if (guardianChoices) {
            guardianChoices.clearChoices();
            guardianChoices.setChoices([], 'value', 'label', true);
            guardianChoices.clearInput();
        }
    }

    // ===== NEW CHILD BUTTON (opens create panel directly) =====

    document.getElementById('newChildBtn').addEventListener('click', function() {
        currentChild = null;
        currentChildId = null;

        document.getElementById('stateCard').classList.add('hidden');
        document.getElementById('activeSessionSection').classList.add('hidden');
        document.getElementById('assignmentSection').classList.remove('hidden');

        activateTab('create');
        resetCreateForm();
    });

    // ===== TAB SWITCHING =====

    document.getElementById('tabAssignExisting').addEventListener('click', () => activateTab('existing'));
    document.getElementById('tabCreateNew').addEventListener('click', function() {
        activateTab('create');
        resetCreateForm();
    });

    // ===== ASSIGN EXISTING CHILD =====

    document.getElementById('assignChildBtn').addEventListener('click', async function() {
        if (!currentChild) return;
        if (isProcessing) return;

        isProcessing = true;
        this.disabled = true;
        const orig = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Se pornește...';

        try {
            const result = await apiCall('/scan-api/assign', {
                method: 'POST',
                body: JSON.stringify({
                    child_id: currentChild.id,
                    bracelet_code: BRACELET_CODE
                })
            });

            if (result.success) {
                // Load the active session
                const sessionData = await apiCall(`/scan-api/child-session/${currentChild.id}`);
                document.getElementById('assignmentSection').classList.add('hidden');
                renderActiveSession({ child: currentChild, active_session: sessionData.active_session });
                loadRecentCompleted();
                loadActiveSessionsInfo();
            } else {
                alert('Eroare: ' + (result.message || 'Nu s-a putut porni sesiunea'));
                this.innerHTML = orig;
            }
        } catch (e) {
            let msg = 'Eroare la pornirea sesiunii';
            if (e.data && e.data.message) msg = e.data.message;
            else if (e.message) msg = e.message;
            alert('Eroare: ' + msg);
            this.innerHTML = orig;
        } finally {
            isProcessing = false;
            this.disabled = false;
        }
    });

    // ===== GUARDIAN RADIO BUTTONS =====

    document.getElementById('radioExistingGuardian').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('existingGuardianPanel').classList.remove('hidden');
            document.getElementById('newGuardianPanel').classList.add('hidden');
            document.getElementById('termsAcceptanceSection').classList.add('hidden');

            // Dacă există deja un guardian selectat în Choices.js, păstrăm childSection vizibil
            const guardianSelectEl = document.getElementById('guardianSelect');
            const hasGuardian = guardianSelectEl && guardianSelectEl.value;
            if (hasGuardian) {
                document.getElementById('childSection').classList.remove('hidden');
            } else {
                document.getElementById('childSection').classList.add('hidden');
            }
        }
    });

    document.getElementById('radioNewGuardian').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('existingGuardianPanel').classList.add('hidden');
            document.getElementById('newGuardianPanel').classList.remove('hidden');
            document.getElementById('childSection').classList.remove('hidden');
            document.getElementById('termsAcceptanceSection').classList.remove('hidden');
        }
    });

    // ===== GUARDIAN SELECT (Choices.js) =====

    function initGuardianChoices() {
        const guardianSelect = document.getElementById('guardianSelect');
        if (guardianSelect && !guardianChoices) {
            guardianChoices = new Choices(guardianSelect, {
                searchEnabled: true,
                searchPlaceholderValue: 'Scrie pentru a căuta...',
                noResultsText: 'Niciun părinte găsit',
                noChoicesText: 'Nu există opțiuni',
                itemSelectText: 'Click pentru a selecta',
                loadingText: 'Se încarcă...',
                shouldSort: false,
                searchChoices: false,
                searchResultLimit: 1000,
            });

            // Listen for search input
            guardianSelect.addEventListener('search', function(e) {
                const q = e.detail.value;
                clearTimeout(guardianSearchTimeout);
                if (!q || q.length < 1) return;
                guardianSearchTimeout = setTimeout(() => searchGuardians(q), 300);
            });

            // When guardian selected, show child section
            guardianSelect.addEventListener('change', function(e) {
                const selectedId = e.target.value;
                if (selectedId) {
                    document.getElementById('childSection').classList.remove('hidden');
                    document.getElementById('termsAcceptanceSection').classList.add('hidden');

                    // Check if guardian needs terms acceptance
                    checkGuardianTerms(parseInt(selectedId));
                } else {
                    document.getElementById('childSection').classList.add('hidden');
                }
            });
        }
    }

    async function searchGuardians(q) {
        try {
            const url = new URL('/guardians-search', window.location.origin);
            url.searchParams.set('q', q);
            url.searchParams.set('limit', '15');
            const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();

            if (data.success && guardianChoices) {
                const choices = (data.guardians || []).map(g => ({
                    value: String(g.id),
                    label: `${g.name}${g.phone ? ' · ' + g.phone : ''}`,
                }));
                guardianChoices.clearChoices();
                guardianChoices.setChoices(choices, 'value', 'label', false);
            }
        } catch (e) {
            console.error('Guardian search error:', e);
        }
    }

    async function checkGuardianTerms(guardianId) {
        try {
            const result = await apiCall('/scan-api/check-guardian-terms', {
                method: 'POST',
                body: JSON.stringify({ guardian_id: guardianId })
            });

            if (result.success && !result.data.accepted) {
                // Guardian needs to accept terms - show modal
                pendingTermsGuardianId = guardianId;
                document.getElementById('termsAcceptanceModal').classList.remove('hidden');
            }
        } catch (e) {
            console.error('Check guardian terms error:', e);
        }
    }

    // ===== TERMS MODAL =====

    document.getElementById('cancelTermsModalBtn').addEventListener('click', function() {
        document.getElementById('termsAcceptanceModal').classList.add('hidden');
        pendingTermsGuardianId = null;
        // Reset guardian selection and hide child section explicitly (nu ne bazăm pe evenimentul change)
        if (guardianChoices) {
            guardianChoices.clearChoices();
            guardianChoices.setChoices([], 'value', 'label', true);
            guardianChoices.clearInput();
        }
        document.getElementById('childSection').classList.add('hidden');
        document.getElementById('childFullName').value = '';
    });

    document.getElementById('acceptTermsModalBtn').addEventListener('click', async function() {
        const termsChecked = document.getElementById('modal_terms_accepted').checked;
        const gdprChecked = document.getElementById('modal_gdpr_accepted').checked;

        if (!termsChecked || !gdprChecked) {
            alert('Trebuie să acceptați ambii termeni pentru a continua.');
            return;
        }

        if (!pendingTermsGuardianId) return;

        try {
            await apiCall('/scan-api/accept-guardian-terms', {
                method: 'POST',
                body: JSON.stringify({ guardian_id: pendingTermsGuardianId })
            });
            document.getElementById('termsAcceptanceModal').classList.add('hidden');
            pendingTermsGuardianId = null;
        } catch (e) {
            alert('Eroare la salvarea acceptării termenilor: ' + (e.data?.message || e.message));
        }
    });

    // ===== CREATE CHILD & START SESSION =====

    document.getElementById('createAndAssignBtn').addEventListener('click', async function() {
        if (isProcessing) return;

        const mode = document.querySelector('input[name="guardianMode"]:checked').value;
        const childName = document.getElementById('childFullName').value.trim();

        if (!childName) {
            alert('Completează numele copilului.');
            return;
        }

        let payload = {
            first_name: childName,
            bracelet_code: BRACELET_CODE,
        };

        if (mode === 'existing') {
            const guardianSelectEl = document.getElementById('guardianSelect');
            const guardianId = guardianSelectEl ? guardianSelectEl.value : null;
            if (!guardianId) {
                alert('Selectează un părinte sau alege "Nou" pentru a crea unul.');
                return;
            }
            payload.guardian_id = parseInt(guardianId);
        } else {
            const guardianName = document.getElementById('guardianName').value.trim();
            const guardianPhone = document.getElementById('guardianPhone').value.trim();
            const termsAccepted = document.getElementById('terms_accepted').checked;
            const gdprAccepted = document.getElementById('gdpr_accepted').checked;

            if (!guardianName || !guardianPhone) {
                alert('Completează numele și telefonul părintelui.');
                return;
            }
            if (!termsAccepted || !gdprAccepted) {
                alert('Trebuie să acceptați termenii și condițiile și politica GDPR.');
                return;
            }
            payload.guardian_name = guardianName;
            payload.guardian_phone = guardianPhone;
            payload.terms_accepted = true;
            payload.gdpr_accepted = true;
        }

        isProcessing = true;
        this.disabled = true;
        const orig = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Se creează...';

        try {
            const result = await apiCall('/scan-api/create-child', {
                method: 'POST',
                body: JSON.stringify(payload)
            });

            if (result.success) {
                const data = result.data;
                currentChild = { id: data.child.id, name: data.child.name };
                currentChildId = data.child.id;

                // Load active session
                const sessionData = await apiCall(`/scan-api/child-session/${currentChild.id}`);
                document.getElementById('assignmentSection').classList.add('hidden');
                renderActiveSession({ child: currentChild, active_session: sessionData.active_session });
                loadRecentCompleted();
                loadActiveSessionsInfo();

                // Reset create form complet
                resetCreateForm();
            } else {
                alert('Eroare: ' + (result.message || 'Nu s-a putut crea copilul'));
                this.innerHTML = orig;
            }
        } catch (e) {
            let msg = 'Eroare la crearea copilului';
            if (e.data && e.data.message) msg = e.data.message;
            else if (e.message) msg = e.message;
            alert('Eroare: ' + msg);
            this.innerHTML = orig;
        } finally {
            isProcessing = false;
            this.disabled = false;
        }
    });

    // ===== PAUSE / RESUME =====

    document.getElementById('pauseResumeBtn').addEventListener('click', async function() {
        if (!currentSession || isProcessing) return;

        const isPaused = currentSession.is_paused;
        const action = isPaused ? 'resume' : 'pause';
        const endpoint = `/scan-api/${action}-session/${currentSession.id}`;

        isProcessing = true;
        this.disabled = true;
        const orig = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Se procesează...</span>';

        try {
            const result = await apiCall(endpoint, { method: 'POST' });

            if (result.success) {
                const sessionData = await apiCall(`/scan-api/child-session/${currentChildId}`);
                renderActiveSession({ child: currentChild, active_session: sessionData.active_session });
                loadActiveSessionsInfo();
            } else {
                alert('Eroare: ' + (result.message || `Nu s-a putut ${isPaused ? 'relua' : 'pune pe pauză'} sesiunea`));
                this.innerHTML = orig;
            }
        } catch (e) {
            let msg = `Eroare la ${isPaused ? 'reluare' : 'pauză'}`;
            if (e.data && e.data.message) msg = e.data.message;
            else if (e.message) msg = e.message;
            alert('Eroare: ' + msg);
            this.innerHTML = orig;
        } finally {
            isProcessing = false;
            this.disabled = false;
        }
    });

    // ===== STOP SESSION =====

    document.getElementById('stopSessionBtn').addEventListener('click', async function() {
        if (!currentSession || isProcessing) return;
        if (!confirm('Sigur vrei să oprești sesiunea? Această acțiune nu poate fi anulată.')) return;

        isProcessing = true;
        this.disabled = true;
        const orig = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Se oprește...</span>';

        try {
            const result = await apiCall(`/scan-api/stop-session/${currentSession.id}`, {
                method: 'POST',
                body: JSON.stringify({ bracelet_code: BRACELET_CODE })
            });

            if (result.success) {
                this.innerHTML = orig;
                renderActiveSession({ active_session: null });
                currentChild = null;
                currentChildId = null;
                sessionProducts = [];
                renderProductsList();

                const card = document.getElementById('stateCard');
                card.classList.remove('hidden');
                card.innerHTML = '<div class="text-gray-500">Sesiunea a fost oprită cu succes. Căutați un copil pentru a porni o nouă sesiune.</div>';
                document.getElementById('assignmentSection').classList.add('hidden');

                loadRecentCompleted();
                loadActiveSessionsInfo();
            } else {
                alert('Eroare: ' + (result.message || 'Nu s-a putut opri sesiunea'));
                this.innerHTML = orig;
            }
        } catch (e) {
            let msg = 'Eroare la oprirea sesiunii';
            if (e.data && e.data.message) msg = e.data.message;
            else if (e.message) msg = e.message;
            alert('Eroare: ' + msg);
            this.innerHTML = orig;
        } finally {
            isProcessing = false;
            this.disabled = false;
        }
    });

    // ===== RECENT COMPLETED SESSIONS =====

    async function loadRecentCompleted() {
        try {
            const res = await apiCall('/scan-api/recent-completed');
            if (!res.success) return;
            const list = res.recent || [];
            const container = document.getElementById('recentCompletedList');
            const section = document.getElementById('recentCompletedSection');
            if (list.length === 0) {
                section.classList.add('hidden');
                container.innerHTML = '';
                return;
            }
            container.innerHTML = list.map(item => `
                <div class="py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center">
                            <i class="fas fa-child"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">${item.child_name || '-'}</div>
                            <div class="text-xs text-gray-500">Start: ${formatDateTime(item.started_at)} • Sfârșit: ${formatDateTime(item.ended_at)}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-indigo-700 font-semibold flex items-center gap-2"><i class="fas fa-hourglass-end"></i>${formatDuration(item.effective_seconds || 0)}</div>
                        <a href="/sessions/${item.id}/show" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition">
                            <i class="fas fa-eye mr-1"></i>View
                        </a>
                    </div>
                </div>
            `).join('');
            section.classList.remove('hidden');
        } catch (e) {
            console.error('Failed to load recent completed sessions', e);
        }
    }

    // ===== ACTIVE SESSIONS INFO =====

    let activeSessionsInterval = null;

    async function loadActiveSessionsInfo() {
        try {
            const result = await apiCall('/scan-api/active-sessions');
            if (result.success && result.sessions) {
                const sessions = result.sessions;
                document.getElementById('activeSessionsCount').textContent = sessions.filter(s => !s.is_paused).length;
                document.getElementById('pausedSessionsCount').textContent = sessions.filter(s => s.is_paused).length;
                const now = new Date();
                document.getElementById('activeSessionsLastUpdate').textContent = `Actualizat: ${now.toLocaleTimeString('ro-RO', { hour: '2-digit', minute: '2-digit' })}`;
            }
        } catch (e) {
            console.error('Error loading active sessions info:', e);
        }
    }

    activeSessionsInterval = setInterval(loadActiveSessionsInfo, 10000);

    // ===== PRODUCTS =====

    async function initializeProductsForSession(sessionId) {
        currentSessionId = sessionId;
        sessionProducts = [];

        try {
            const result = await apiCall(`/scan-api/session-products/${sessionId}`);
            if (result.success) {
                sessionProducts = result.products || [];
                renderProductsList();
            }
        } catch (e) {
            console.error('Error loading session products:', e);
        }
    }

    function renderProductsList() {
        const list = document.getElementById('productsList');
        if (!list) return;

        if (!sessionProducts || sessionProducts.length === 0) {
            list.innerHTML = '<p class="text-sm text-gray-500">Nu sunt produse adăugate</p>';
            return;
        }

        list.innerHTML = sessionProducts.map(p => `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                <div>
                    <span class="font-medium text-gray-900">${p.product_name}</span>
                    <span class="text-sm text-gray-500 ml-2">x${p.quantity}</span>
                </div>
                <div class="text-sm font-medium text-gray-900">${parseFloat(p.total_price).toFixed(2)} RON</div>
            </div>
        `).join('');
    }

    // Add products modal
    const addProductsBtn = document.getElementById('addProductsBtn');
    const addProductsModal = document.getElementById('addProductsModal');
    const closeAddProductsModal = document.getElementById('closeAddProductsModal');
    const cancelAddProducts = document.getElementById('cancelAddProducts');
    const saveAddProducts = document.getElementById('saveAddProducts');
    const addProductsOverlay = document.getElementById('addProductsOverlay');
    let productsChoices = null;

    async function openAddProductsModal() {
        addProductsModal.classList.remove('hidden');
        addProductsModal.setAttribute('aria-hidden', 'false');

        // Load available products
        try {
            const result = await apiCall('/scan-api/available-products');
            if (result.success && result.products) {
                const productsSelect = document.getElementById('productsSelect');
                productsSelect.innerHTML = '<option value="">Selectează produs...</option>' +
                    result.products.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name} - ${parseFloat(p.price).toFixed(2)} RON</option>`).join('');
            }
        } catch (e) {
            console.error('Error loading products:', e);
        }
    }

    function closeModal() {
        addProductsModal.classList.add('hidden');
        addProductsModal.setAttribute('aria-hidden', 'true');
        document.getElementById('productsSelect').value = '';
        document.getElementById('productQuantity').value = '1';
    }

    if (addProductsBtn) addProductsBtn.addEventListener('click', openAddProductsModal);
    if (closeAddProductsModal) closeAddProductsModal.addEventListener('click', closeModal);
    if (cancelAddProducts) cancelAddProducts.addEventListener('click', closeModal);
    if (addProductsOverlay) addProductsOverlay.addEventListener('click', closeModal);

    if (saveAddProducts) {
        saveAddProducts.addEventListener('click', async function() {
            if (!currentSessionId) return;

            const productId = document.getElementById('productsSelect').value;
            const quantity = parseInt(document.getElementById('productQuantity').value);

            if (!productId) { alert('Selectează un produs.'); return; }
            if (!quantity || quantity < 1) { alert('Cantitatea trebuie să fie cel puțin 1.'); return; }

            try {
                const result = await apiCall('/scan-api/add-products', {
                    method: 'POST',
                    body: JSON.stringify({
                        session_id: currentSessionId,
                        products: [{ product_id: parseInt(productId), quantity }]
                    })
                });

                if (result.success) {
                    closeModal();
                    await initializeProductsForSession(currentSessionId);
                } else {
                    alert('Eroare: ' + (result.message || 'Nu s-au putut adăuga produsele'));
                }
            } catch (e) {
                let msg = 'Eroare la adăugarea produselor';
                if (e.data && e.data.message) msg = e.data.message;
                else if (e.message) msg = e.message;
                alert('Eroare: ' + msg);
            }
        });
    }

    // ===== INIT =====
    childSearchInput.focus();
    loadRecentCompleted();
    loadActiveSessionsInfo();
</script>
@endsection
