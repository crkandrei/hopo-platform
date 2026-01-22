@extends('layouts.app')

@section('title', 'Scanare Brățară')
@section('page-title', 'Scanare Brățară')

@section('content')
<div class="space-y-6">
    <!-- Sticky input bar -->
    <div class="sticky top-0 z-30 bg-white border-b border-gray-200 py-4 -mx-6 px-6">
        <div class="max-w-6xl mx-auto px-0 space-y-3">
            <!-- Sesiuni active info (mic, discret) -->
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
            
            <!-- RFID Code Search -->
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3 relative">
                    <label for="rfidCode" class="sr-only">Cod RFID</label>
                    <div class="flex-1 relative">
                        <input id="rfidCode" maxlength="50" autocomplete="off"
                               class="w-full h-12 px-4 pr-10 text-2xl tracking-widest font-mono border rounded-md focus:outline-none focus:ring-4 transition-colors"
                               placeholder="Cod brățară">
                        <!-- Validation icon -->
                        <div id="rfidCodeIcon" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                    </div>
                    <button id="searchBtn" disabled
                            class="h-12 px-6 text-lg bg-gray-900 text-white rounded-md disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        Caută
                    </button>
                    
                    <!-- Children search results dropdown -->
                    <div id="childrenSearchResults" class="hidden absolute top-full left-0 mt-1 bg-white border border-gray-300 rounded-md shadow-lg z-50 max-h-60 overflow-y-auto" style="width: calc(100% - 200px);">
                        <div id="childrenSearchResultsList" class="py-1"></div>
                    </div>
                </div>
                <!-- Validation feedback -->
                <div id="rfidCodeError" class="hidden text-xs text-red-600 px-1 flex items-center gap-1">
                    <i class="fas fa-exclamation-circle"></i>
                    <span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main state + actions -->
    <div class="max-w-6xl mx-auto px-0 space-y-6">
        <div id="stateCard" class="bg-white border border-gray-300 rounded-lg p-6" aria-live="polite">
            <div class="text-gray-500">Introduceți sau scanați un cod pentru a începe.</div>
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
                        <div class="text-sm text-gray-600">Cod brățară</div>
                        <div id="sessionBraceletCode" class="text-xl font-mono font-semibold text-gray-900 tracking-wider">-</div>
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
                <nav class="flex gap-2" role="tablist" aria-label="Assignment tabs">
                    <button id="tabAssignExisting" type="button" aria-controls="assignExistingPanel" aria-selected="true"
                        class="px-4 py-2 text-base font-semibold rounded-t-md bg-gray-100 text-gray-900"><i class="fas fa-user-check mr-2"></i>Asignează <span class="font-bold">COPIL</span> existent</button>
                    <button id="tabCreateNew" type="button" aria-controls="createNewPanel" aria-selected="false"
                        class="px-4 py-2 text-sm font-medium rounded-t-md text-gray-600 hover:text-gray-900"><i class="fas fa-user-plus mr-2"></i>Creează copil nou</button>
                </nav>
            </div>
            
            <!-- Opțiune 1: Asignează copil existent -->
            <div id="assignExistingPanel" class="mb-8 pb-8 border-b border-gray-200">
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Selectează copil:
                        </label>
                        <select 
                            id="childSelect" 
                            class="w-full">
                            <option value="">Caută și selectează copil...</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">* Poți scrie în câmp pentru a căuta</p>
                    </div>
                    
                    
                    <button 
                        id="assignChildBtn" 
                        disabled
                        class="w-full h-11 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Asignează copilul selectat
                    </button>
                    <div id="childSelectionStatus" class="hidden text-sm text-amber-600 mt-2"></div>
                </div>
            </div>

            <!-- Opțiune 2: Creează copil nou -->
            <div id="createNewPanel" class="hidden">
                <!-- Informație statică despre pași - în afara zonei afectate de Choices.js -->
                <div class="grid grid-cols-2 gap-6 mb-4">
                    <!-- Banner PASUL 1: PĂRINTE -->
                    <div class="bg-green-50 border-2 border-green-300 rounded-lg p-4" id="guardianInfoBanner" style="position: relative; z-index: 1;">
                        <h5 class="text-xl font-bold text-green-900 mb-2">
                            <i class="fas fa-user-shield mr-2"></i>PASUL 1: PĂRINTE
                        </h5>
                        <p class="text-sm font-semibold text-green-800">
                            ⚠️ ATENȚIE: Aici se creează sau selectează <span class="font-bold text-green-900">PĂRINTELE</span>, NU copilul!
                        </p>
                        <p class="text-xs text-green-700 mt-2">
                            Copilul va apărea în pasul următor, după ce completezi numele și telefonul părintelui.
                        </p>
                    </div>
                    
                    <!-- Banner PASUL 2: COPIL (apare când se completează părintele) -->
                    <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4 opacity-0 pointer-events-none transition-opacity duration-200" id="childInfoBanner" style="position: relative; z-index: 1;">
                        <h5 class="text-xl font-bold text-blue-900 mb-2">
                            <i class="fas fa-child mr-2"></i>PASUL 2: COPIL
                        </h5>
                        <p class="text-sm font-semibold text-blue-800">
                            Acum completează datele copilului
                        </p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Părinte -->
                    <div class="space-y-3" id="guardianSection">
                        <!-- Radio buttons pentru a selecta modul -->
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
                            <input id="guardianName" type="text" placeholder="Nume complet *" class="w-full h-10 px-3 border border-green-300 rounded-md uppercase-input" style="text-transform: uppercase;">
                            <input id="guardianPhone" type="tel" placeholder="Telefon *" class="w-full h-10 px-3 border border-green-300 rounded-md">
                            <p class="text-xs text-gray-500">Completează minim nume și telefon</p>
                        </div>
                    </div>

                    <!-- Copil (apare doar după ce ai selectat/completat părinte) -->
                    <div id="childSection" class="space-y-3 hidden">
                        
                        <input id="childFullName" type="text" placeholder="Nume complet copil *" 
                            class="w-full h-10 px-3 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 uppercase-input"
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
                            Creează și asignează
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

        <!-- Recent completed sessions (hidden until loaded) -->
        <div id="recentCompletedSection" class="hidden bg-white border border-gray-300 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-history mr-2"></i>Ultimele sesiuni închise</h3>
            <div id="recentCompletedList" class="divide-y divide-gray-200"></div>
        </div>
    </div>
</div>

<!-- Modal pentru acceptare termeni (părinte existent) -->
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
    let currentBracelet = null;
    let currentSession = null;
    let timerInterval = null;
    let assignmentInitialized = false;
    let selectedChildHasActiveSession = false;
    let isProcessing = false; // Flag pentru a preveni dublă-click

    async function apiCall(url, options = {}) {
        // Get CSRF token safely
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
        
        if (!csrfToken) {
            console.error('CSRF token not found. Page may need to be refreshed.');
            throw new Error('Token CSRF lipsă. Te rugăm să reîncarci pagina.');
        }
        
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
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        let data;
        
        // Read response as text first (we can parse it as JSON later if needed)
        const responseText = await response.text();
        
        if (contentType && contentType.includes('application/json')) {
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                // If JSON parsing fails, throw a more descriptive error
                const error = new Error('Răspuns invalid de la server');
                error.data = { message: 'Serverul a returnat un răspuns invalid. Verifică consola pentru detalii.' };
                error.status = response.status;
                error.originalResponse = responseText.substring(0, 200); // First 200 chars
                console.error('JSON parse error:', e);
                console.error('Response text:', responseText.substring(0, 500));
                throw error;
            }
        } else {
            // Response is not JSON (likely HTML error page)
            const error = new Error('Serverul a returnat o eroare HTML');
            error.data = { 
                message: response.status === 404 
                    ? 'Endpoint-ul nu a fost găsit. Verifică dacă ruta există.' 
                    : response.status === 500
                    ? 'Eroare internă a serverului. Verifică logurile pentru detalii.'
                    : `Eroare HTTP ${response.status}: Serverul a returnat HTML în loc de JSON`
            };
            error.status = response.status;
            error.originalResponse = responseText.substring(0, 200); // First 200 chars
            console.error('Non-JSON response received:', response.status, contentType);
            console.error('Response preview:', responseText.substring(0, 500));
            throw error;
        }
        
        // If HTTP status is not OK, throw error
        if (!response.ok) {
            // Handle CSRF token mismatch (419)
            if (response.status === 419) {
                const error = new Error('Token CSRF expirat. Te rugăm să reîncarci pagina și să încerci din nou.');
                error.data = { 
                    message: 'Token CSRF expirat. Te rugăm să reîncarci pagina și să încerci din nou.',
                    csrf_mismatch: true 
                };
                error.status = 419;
                throw error;
            }
            
            const error = new Error(data.message || 'Request failed');
            error.data = data;
            error.status = response.status;
            throw error;
        }
        
        return data;
    }

    function updateStatusChip(text, tone) {
        const chip = document.getElementById('statusChip');
        if (!chip) return;
        chip.textContent = text;
        chip.className = 'ml-2 text-sm px-2 py-1 rounded border ' + (tone === 'error' ? 'border-red-300 text-red-800' : tone === 'warn' ? 'border-amber-300 text-amber-800' : 'border-gray-300 text-gray-700');
    }

    // ===== SESSION TIMER FUNCTIONS =====
    
    function formatTime(seconds) {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    function formatDateTime(isoString) {
        if (!isoString) return '-';
        const date = new Date(isoString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${day}.${month}.${year} ${hours}:${minutes}`;
    }

    function formatDuration(seconds) {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        const p = (n) => String(n).padStart(2, '0');
        return `${p(h)}:${p(m)}:${p(s)}`;
    }

    function updateTimer() {
        if (!currentSession) return;
        
        // effective_seconds de la server deja include timpul până la momentul răspunsului API
        // Trebuie doar să adăugăm timpul scurs DE LA ACEL MOMENT până acum
        let totalSeconds = currentSession.effective_seconds || 0;
        
        if (!currentSession.is_paused && currentSession.fetched_at) {
            // Adăugăm doar timpul scurs de când am primit datele de la server
            const timeSinceFetch = Math.floor((Date.now() - currentSession.fetched_at) / 1000);
            totalSeconds += timeSinceFetch;
        }
        
        document.getElementById('sessionTimer').textContent = formatTime(totalSeconds);
    }

    function startTimer() {
        stopTimer();
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    }

    function stopTimer() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    }

    function renderActiveSession(data) {
        const activeSessionSection = document.getElementById('activeSessionSection');
        
        if (!data.active_session) {
            activeSessionSection.classList.add('hidden');
            stopTimer();
            currentSession = null;
            return;
        }
        
        currentSession = data.active_session;
        // Salvăm momentul când am primit datele pentru calcul corect al timer-ului
        currentSession.fetched_at = Date.now();
        
        // Initialize products for this session
        if (currentSession && currentSession.id) {
            initializeProductsForSession(currentSession.id);
        }
        
        // Update session info
        document.getElementById('sessionChildName').textContent = 
            data.child ? data.child.name : '-';
        document.getElementById('sessionBraceletCode').textContent = 
            data.bracelet_code || data.active_session?.bracelet_code || '-';
        document.getElementById('sessionStartedAt').textContent = 
            formatDateTime(data.active_session.started_at);
        
        // Update timer ÎNTOTDEAUNA (chiar dacă e pe pauză)
        updateTimer();
        
        // Resetează butoanele la starea inițială
        const pauseResumeBtn = document.getElementById('pauseResumeBtn');
        const stopSessionBtn = document.getElementById('stopSessionBtn');
        const statusDiv = document.getElementById('sessionStatus');
        
        // Resetează butonul de stop
        stopSessionBtn.disabled = false;
        stopSessionBtn.innerHTML = '<i class="fas fa-stop"></i><span>Stop</span>';
        
        // Update pause/resume button
        if (data.active_session.is_paused) {
            pauseResumeBtn.disabled = false;
            pauseResumeBtn.innerHTML = '<i class="fas fa-play"></i><span>Reia</span>';
            pauseResumeBtn.className = 'flex-1 h-12 px-4 bg-green-500 hover:bg-green-600 text-white font-medium rounded-md transition flex items-center justify-center gap-2';
            statusDiv.innerHTML = '<span class="text-amber-600">⏸ Sesiune în pauză</span>';
            stopTimer(); // Oprește timer-ul automat, dar valoarea rămâne afișată
        } else {
            pauseResumeBtn.disabled = false;
            pauseResumeBtn.innerHTML = '<i class="fas fa-pause"></i><span>Pauză</span>';
            pauseResumeBtn.className = 'flex-1 h-12 px-4 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-md transition flex items-center justify-center gap-2';
            statusDiv.innerHTML = '<span class="text-green-600">▶ Sesiune activă</span>';
            startTimer(); // Pornește timer-ul care va continua să se actualizeze
        }
        
        activeSessionSection.classList.remove('hidden');
    }

    function renderBraceletInfo(data) {
        const card = document.getElementById('stateCard');
        const assignmentSection = document.getElementById('assignmentSection');
        
        if (!data || !data.success) {
            card.innerHTML = `
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-red-100 text-red-700 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Eroare</h3>
                        <p class="text-red-800">${data?.message || 'A apărut o eroare'}</p>
                    </div>
                </div>`;
            card.classList.remove('hidden');
            assignmentSection.classList.add('hidden');
            renderActiveSession({ active_session: null });
            updateStatusChip('Eroare', 'error');
            return;
        }

        // Store bracelet code from response
        const braceletCode = data.bracelet_code || (data.bracelet && data.bracelet.code);
        currentBracelet = braceletCode ? { code: braceletCode } : null;
        
        // Dacă are sesiune activă, ascunde cardul și secțiunea de asignare
        if (data.active_session) {
            card.classList.add('hidden');
            assignmentSection.classList.add('hidden');
            renderActiveSession(data);
            updateStatusChip('În joc', 'warn');
            // Keep code visible - don't clear it, operator can see which bracelet is active
            // Input will be cleared when starting a new scan
            return;
        }
        
        // Ascunde cardul cu info redundante despre brățară
        card.classList.add('hidden');
        
        // Ascunde sesiunea activă
        renderActiveSession({ active_session: null });
        
        if (data.can_assign) {
            assignmentSection.classList.remove('hidden');
            updateStatusChip('Disponibilă', 'warn');
            // Update button state when assignment section becomes visible
            updateAssignButtonState();
            // Keep code visible in input - operator needs to see it during assignment/creation
            // Code will be cleared after successful assignment/creation
        } else {
            assignmentSection.classList.add('hidden');
            updateStatusChip('OK', undefined);
        }
    }

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

    // ---- DOM bindings ----
    const codeInput = document.getElementById('rfidCode');
    const searchBtn = document.getElementById('searchBtn');
    const childrenSearchResults = document.getElementById('childrenSearchResults');
    const childrenSearchResultsList = document.getElementById('childrenSearchResultsList');
    let childrenSearchTimeout;
    let currentChildrenResults = [];
    let barcodeScanTimeout;
    let lastInputTime = 0;
    let inputLengthBefore = 0;
    let rapidInputStartLength = 0; // Track when rapid input starts
    
    // Function to prepare input for scanning (focus only, don't auto-select)
    function prepareInputForScanning() {
        codeInput.focus();
        // Don't auto-select - let the focus handler do it if needed
    }
    
    // Function to clear input and prepare for next scan
    function clearInputForNextScan() {
        codeInput.value = '';
        updateValidationFeedback('');
        codeInput.dispatchEvent(new Event('input'));
        prepareInputForScanning();
    }
    
    // Validate bracelet code - accept any non-empty code
    function isValidBraceletCode(code) {
        if (!code || typeof code !== 'string') {
            return false;
        }
        const trimmed = code.trim();
        // Accept any non-empty code (no pattern validation)
        return trimmed.length > 0 && trimmed.length <= 50;
    }
    
    function updateValidationFeedback(code) {
        const icon = document.getElementById('rfidCodeIcon');
        const errorDiv = document.getElementById('rfidCodeError');
        const errorSpan = errorDiv.querySelector('span');
        
        if (!code || code.length === 0) {
            // Empty - hide feedback
            icon.classList.add('hidden');
            errorDiv.classList.add('hidden');
            codeInput.classList.remove('border-green-500', 'border-red-500', 'focus:ring-green-500/20', 'focus:ring-red-500/20');
            codeInput.classList.add('border-gray-300', 'focus:ring-gray-900/20');
            return;
        }
        
        // Any non-empty code is valid (no pattern validation)
        icon.classList.remove('hidden');
        errorDiv.classList.add('hidden');
        codeInput.classList.remove('border-gray-300', 'border-red-500', 'focus:ring-gray-900/20', 'focus:ring-red-500/20');
        codeInput.classList.add('border-green-500', 'focus:ring-green-500/20');
    }
    
    // Track input changes to detect barcode scanner (rapid input)
    codeInput.addEventListener('focus', function() {
        // Auto-select content when input gets focus (but only if it has content)
        setTimeout(() => {
            if (codeInput.value.length > 0) {
                codeInput.select();
            }
        }, 10);
    });
    
    codeInput.addEventListener('input', function(e) {
        const now = Date.now();
        const currentLength = e.target.value.length;
        const timeSinceLastInput = now - lastInputTime;
        let fullValue = e.target.value;
        
        // Detect rapid input (barcode scanner) - characters coming in < 50ms apart
        const isRapidInput = timeSinceLastInput < 50 && currentLength > inputLengthBefore;
        
        // Only apply scanner logic if input is truly rapid (scanner) or if it's a complete paste/change
        // For manual typing, allow normal editing without interference
        const isManualTyping = timeSinceLastInput > 100; // Manual typing has gaps > 100ms
        
        if (!isManualTyping && isRapidInput) {
            // This is likely a barcode scanner
            // Track when rapid input starts (after a pause > 200ms)
            if (timeSinceLastInput > 200 && currentLength > inputLengthBefore) {
                // New input sequence starting - mark the start length
                rapidInputStartLength = inputLengthBefore;
            }
            
            // Fix concatenation if it still happened (fallback)
            if (isRapidInput && rapidInputStartLength > 0 && currentLength > rapidInputStartLength + 1) {
                // Scanner concatenated over existing content - extract only new scan
                const newScan = fullValue.substring(rapidInputStartLength);
                e.target.value = newScan;
                inputLengthBefore = newScan.length;
                rapidInputStartLength = 0; // Reset after fixing
                lastInputTime = now;
                fullValue = newScan;
            } else {
                // Normal rapid input - just track
                lastInputTime = now;
                inputLengthBefore = currentLength;
            }
        } else if (isManualTyping && timeSinceLastInput > 200 && inputLengthBefore > 0 && currentLength > inputLengthBefore) {
            // Manual typing after a pause - DON'T clear, just allow normal editing
            // Only clear if user is typing at the beginning (deleting and retyping)
            if (currentLength < inputLengthBefore) {
                // User deleted characters - this is normal editing
                lastInputTime = now;
                inputLengthBefore = currentLength;
                rapidInputStartLength = 0;
            } else {
                // User is adding characters after a pause - normal typing, don't interfere
                lastInputTime = now;
                inputLengthBefore = currentLength;
                rapidInputStartLength = 0;
            }
        } else {
            // Normal input - just track
            lastInputTime = now;
            inputLengthBefore = currentLength;
            // Reset rapid input start if input stopped (pause > 500ms)
            if (timeSinceLastInput > 500) {
                rapidInputStartLength = 0;
            }
        }
        
        // Trim and limit length
        let filteredValue = fullValue.trim().substring(0, 50);
        
        // Update input value with filtered and normalized value
        if (filteredValue !== fullValue) {
            e.target.value = filteredValue;
            fullValue = filteredValue;
        }
        
        // Update validation feedback
        updateValidationFeedback(filteredValue);
        
        // Enable/disable search button based on validation
        const isValid = isValidBraceletCode(filteredValue);
        if (searchBtn) {
            searchBtn.disabled = !isValid || filteredValue.length === 0;
        }
        
        // Clear previous barcode scan timeout
        clearTimeout(barcodeScanTimeout);
        
        // Auto-submit for valid barcode scanner input (only for rapid input)
        if (!isManualTyping && isValid && filteredValue.length >= 9) {
            barcodeScanTimeout = setTimeout(() => {
                const currentValue = codeInput.value.trim();
                if (isValidBraceletCode(currentValue) && searchBtn) {
                    searchBtn.disabled = false;
                    searchBtn.click();
                }
            }, 500);
        } else {
            // If input is not empty but short, search for children
            if (filteredValue.length > 0 && filteredValue.length < 5) {
                clearTimeout(childrenSearchTimeout);
                childrenSearchTimeout = setTimeout(() => {
                    searchChildrenWithSessions(filteredValue);
                }, 300);
            } else {
                hideChildrenSearchResults();
            }
        }
    });
    
    // Handle paste event - allow any text
    codeInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        const trimmed = pastedText.trim();
        
        // Allow paste if text is not empty
        if (trimmed.length > 0) {
            codeInput.value = trimmed.substring(0, 50); // Limit to 50 chars
            codeInput.dispatchEvent(new Event('input'));
        }
    });
    
    // Also handle keyup to ensure validation is updated
    codeInput.addEventListener('keyup', function(e) {
        const value = codeInput.value.trim();
        const isValid = isValidBraceletCode(value);
        if (searchBtn) {
            searchBtn.disabled = !isValid || value.length === 0;
        }
        updateValidationFeedback(value);
    });
    
    
    // Handle change event (for programmatic changes or barcode scanners)
    codeInput.addEventListener('change', function(e) {
        const value = codeInput.value.trim();
        codeInput.value = value;
        updateValidationFeedback(value);
        if (searchBtn) {
            searchBtn.disabled = value.length === 0;
            // Auto-submit if valid
            if (isValid && normalized.length >= 9) {
                setTimeout(() => {
                    searchBtn.click();
                }, 100);
            }
        }
    });
    
    // Hide children search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!codeInput.contains(e.target) && !childrenSearchResults.contains(e.target)) {
            hideChildrenSearchResults();
        }
    });
    
    // Hide children search results when pressing Escape
    codeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideChildrenSearchResults();
        }
    });
    
    searchBtn.addEventListener('click', async function() {
        const code = codeInput.value.trim();
        if (code.length === 0) return;
        
        // Validate code format before submitting
        if (!isValidBraceletCode(code)) {
            updateValidationFeedback(code);
            return; // Don't submit if invalid
        }
        
        this.disabled = true;
        const prev = this.textContent;
        this.textContent = 'Se caută...';
        try {
            const data = await apiCall('/scan-api/lookup', { method: 'POST', body: JSON.stringify({ code }) });
            renderBraceletInfo(data);
            // Load recent completed (in case a previous stop just happened)
            loadRecentCompleted();
            // Don't clear input here - keep code visible until assignment/creation is complete
            // Input will be cleared after successful assignment/creation or when starting a new session
        } catch (err) {
            // Extract exact error message from API response
            let errorMessage = 'Eroare la căutare';
            
            // Handle CSRF token mismatch specifically
            if (err.status === 419 || (err.data && err.data.csrf_mismatch)) {
                errorMessage = 'Token CSRF expirat. Te rugăm să reîncarci pagina și să încerci din nou.';
                console.error('CSRF token mismatch. Consider reloading the page.');
                // Optionally auto-reload after showing error
                setTimeout(() => {
                    if (confirm('Token CSRF expirat. Vrei să reîncarci pagina?')) {
                        window.location.reload();
                    }
                }, 2000);
            } else if (err.status === 400 && err.data && err.data.message) {
                errorMessage = err.data.message;
            } else if (err.data && err.data.message) {
                errorMessage = err.data.message;
            } else if (err.message) {
                errorMessage = err.message;
            }
            
            renderBraceletInfo({ success: false, message: errorMessage });
            // Prepare input for next scan even after error
            setTimeout(() => {
                prepareInputForScanning();
            }, 200);
        } finally {
            this.disabled = false;
            this.textContent = prev;
        }
    });

    // Enter key to search
    codeInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const code = codeInput.value.trim();
            if (code.length > 0 && isValidBraceletCode(code)) {
                // Only allow search if code is valid
                if (searchBtn && !searchBtn.disabled) {
                    searchBtn.click();
                }
            } else {
                // Show validation error
                updateValidationFeedback(code);
            }
            hideChildrenSearchResults();
        } else if (e.key === 'ArrowDown' && childrenSearchResults.classList.contains('hidden') === false && currentChildrenResults.length > 0) {
            e.preventDefault();
            const firstChild = childrenSearchResultsList.querySelector('.child-result-item');
            if (firstChild) firstChild.focus();
        }
    });

    // Escape to clear
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && e.target === codeInput) {
            codeInput.value = '';
            codeInput.dispatchEvent(new Event('input'));
            hideChildrenSearchResults();
        }
    });

    // ===== CHILDREN WITH ACTIVE SESSIONS SEARCH =====
    
    async function searchChildrenWithSessions(query) {
        if (!query || query.length === 0) {
            hideChildrenSearchResults();
            return;
        }
        
        try {
            const url = new URL('/scan-api/children-with-sessions', window.location.origin);
            url.searchParams.set('q', query);
            
            const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            
            if (data.success && data.children && data.children.length > 0) {
                currentChildrenResults = data.children;
                displayChildrenSearchResults(data.children);
            } else {
                hideChildrenSearchResults();
            }
        } catch (e) {
            console.error('Error searching children with sessions:', e);
            hideChildrenSearchResults();
        }
    }
    
    function displayChildrenSearchResults(children) {
        if (!childrenSearchResultsList) return;
        
        childrenSearchResultsList.innerHTML = children.map(child => {
            let guardianInfo = '';
            if (child.guardian_name) {
                guardianInfo = child.guardian_phone 
                    ? `${child.guardian_name} (${child.guardian_phone})`
                    : child.guardian_name;
            }
            
            let sessionInfo = '';
            if (child.session_started_at) {
                const startedAt = new Date(child.session_started_at).toLocaleString('ro-RO');
                const duration = child.session_duration_formatted || '00:00';
                const paused = child.session_is_paused ? ' ⏸ Pauză' : '';
                sessionInfo = `<div class="text-xs text-indigo-600 mt-1">Brățară: ${child.bracelet_code || '-'} • Start: ${startedAt} • Durată: ${duration}${paused}</div>`;
            }
            
            return `
            <div class="child-result-item px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0" 
                 data-child-id="${child.id}"
                 tabindex="0">
                <div class="font-medium text-gray-900">${child.name}</div>
                ${guardianInfo ? `<div class="text-sm text-gray-500">${guardianInfo}</div>` : ''}
                ${sessionInfo}
            </div>
            `;
        }).join('');
        
        // Add click handlers
        childrenSearchResultsList.querySelectorAll('.child-result-item').forEach(item => {
            item.addEventListener('click', function() {
                const childId = this.getAttribute('data-child-id');
                if (childId) {
                    accessChildSession(childId);
                }
            });
            
            item.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const childId = this.getAttribute('data-child-id');
                    if (childId) {
                        accessChildSession(childId);
                    }
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const next = this.nextElementSibling;
                    if (next) next.focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prev = this.previousElementSibling;
                    if (prev) {
                        prev.focus();
                    } else {
                        codeInput.focus();
                    }
                }
            });
        });
        
        childrenSearchResults.classList.remove('hidden');
    }
    
    function hideChildrenSearchResults() {
        if (childrenSearchResults) {
            childrenSearchResults.classList.add('hidden');
        }
        currentChildrenResults = [];
    }
    
    async function accessChildSession(childId) {
        hideChildrenSearchResults();
        
        try {
            const result = await apiCall(`/scan-api/child-session/${childId}`);
            
            if (result.success && result.active_session) {
                // Set current bracelet code from session
                const braceletCode = result.bracelet_code || (result.bracelet && result.bracelet.code);
                currentBracelet = braceletCode ? { code: braceletCode } : null;
                
                // Clear input and prepare for next scan
                clearInputForNextScan();
                
                // Render active session using existing function
                renderBraceletInfo({
                    success: true,
                    bracelet_code: braceletCode,
                    child: result.child,
                    active_session: result.active_session
                });
            } else {
                alert('Eroare: ' + (result.message || 'Nu s-a putut accesa sesiunea'));
            }
        } catch (e) {
            // Extract exact error message from API response
            let errorMessage = 'Eroare la accesarea sesiunii';
            if (e.status === 404 && e.data && e.data.message) {
                errorMessage = e.data.message;
            } else if (e.data && e.data.message) {
                errorMessage = e.data.message;
            } else if (e.message) {
                errorMessage = e.message;
            }
            alert('Eroare: ' + errorMessage);
            console.error(e);
        }
    }

    // Focus input on load
    codeInput.focus();
    // Load recent completed sessions on page load
    loadRecentCompleted();
    // Load active sessions info on page load
    loadActiveSessionsInfo();

    // ===== ACTIVE SESSIONS INFO =====
    let activeSessionsInterval = null;

    async function loadActiveSessionsInfo() {
        try {
            const result = await apiCall('/scan-api/active-sessions');
            if (result.success && result.sessions) {
                const sessions = result.sessions;
                const activeCount = sessions.filter(s => !s.is_paused).length;
                const pausedCount = sessions.filter(s => s.is_paused).length;
                
                document.getElementById('activeSessionsCount').textContent = activeCount;
                document.getElementById('pausedSessionsCount').textContent = pausedCount;
                
                // Update last update time
                const now = new Date();
                const timeStr = now.toLocaleTimeString('ro-RO', { hour: '2-digit', minute: '2-digit' });
                document.getElementById('activeSessionsLastUpdate').textContent = `Actualizat: ${timeStr}`;
            }
        } catch (e) {
            console.error('Error loading active sessions info:', e);
        }
    }

    // Start auto-refresh for active sessions info (every 10 seconds)
    function startActiveSessionsAutoRefresh() {
        if (activeSessionsInterval) {
            clearInterval(activeSessionsInterval);
        }
        activeSessionsInterval = setInterval(() => {
            loadActiveSessionsInfo();
        }, 10000); // Refresh every 10 seconds
    }

    // Stop auto-refresh
    function stopActiveSessionsAutoRefresh() {
        if (activeSessionsInterval) {
            clearInterval(activeSessionsInterval);
            activeSessionsInterval = null;
        }
    }

    // Start auto-refresh on page load
    startActiveSessionsAutoRefresh();

    // ===== SESSION CONTROL BUTTONS =====
    
    document.getElementById('pauseResumeBtn').addEventListener('click', async function() {
        if (!currentSession) return;
        
        // Prevent double-click
        if (isProcessing) {
            return;
        }
        
        const isPaused = currentSession.is_paused;
        const action = isPaused ? 'resume' : 'pause';
        const endpoint = `/scan-api/${action}-session/${currentSession.id}`;
        
        // Disable button immediately
        isProcessing = true;
        this.disabled = true;
        const originalContent = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Se procesează...</span>';
        
        try {
            const result = await apiCall(endpoint, { method: 'POST' });
            
            if (result.success) {
                // Refresh bracelet info to get updated session
                const data = await apiCall('/scan-api/lookup', {
                    method: 'POST',
                    body: JSON.stringify({ code: currentBracelet.code })
                });
                renderBraceletInfo(data);
                // Refresh active sessions info
                loadActiveSessionsInfo();
                // Prepare input for next scan after pause/resume
                setTimeout(() => {
                    prepareInputForScanning();
                }, 200);
            } else {
                alert('Eroare: ' + (result.message || `Nu s-a putut ${isPaused ? 'relua' : 'pune pe pauză'} sesiunea`));
                this.innerHTML = originalContent;
            }
        } catch (e) {
            // Extract exact error message from API response
            let errorMessage = `Eroare la ${isPaused ? 'reluare' : 'pauză'}`;
            if (e.status === 400 && e.data && e.data.message) {
                errorMessage = e.data.message;
            } else if (e.data && e.data.message) {
                errorMessage = e.data.message;
            } else if (e.message) {
                errorMessage = e.message;
            }
            alert('Eroare: ' + errorMessage);
            console.error(e);
            this.innerHTML = originalContent;
        } finally {
            isProcessing = false;
            this.disabled = false;
        }
    });

    document.getElementById('stopSessionBtn').addEventListener('click', async function() {
        if (!currentSession) return;
        
        // Prevent double-click
        if (isProcessing) {
            return;
        }
        
        if (!confirm('Sigur vrei să oprești sesiunea? Această acțiune nu poate fi anulată.')) {
            return;
        }
        
        // Disable button immediately
        isProcessing = true;
        this.disabled = true;
        const originalContent = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Se oprește...</span>';
        
        try {
            // Send bracelet code to verify it matches the session
            const result = await apiCall(`/scan-api/stop-session/${currentSession.id}`, { 
                method: 'POST',
                body: JSON.stringify({
                    bracelet_code: currentBracelet ? currentBracelet.code : null
                })
            });
            
            if (result.success) {
                // Reset button first
                this.innerHTML = originalContent;
                
                // Clear the session view
                renderActiveSession({ active_session: null });
                currentSession = null;
                currentSessionId = null;
                sessionProducts = [];
                renderProductsList();
                
                // Reset bracelet reference
                currentBracelet = null;
                
                // Clear input field and prepare for next scan
                clearInputForNextScan();
                
                // Reset interface - hide assignment section and show ready message
                const card = document.getElementById('stateCard');
                const assignmentSection = document.getElementById('assignmentSection');
                
                card.classList.remove('hidden');
                card.innerHTML = '<div class="text-gray-500">Sesiunea a fost oprită cu succes. Introduceți sau scanați un cod pentru a începe.</div>';
                assignmentSection.classList.add('hidden');
                
                // Reset status chip
                updateStatusChip('Gata', undefined);
                
                // Refresh recent completed list
                loadRecentCompleted();
                // Refresh active sessions info
                loadActiveSessionsInfo();
            } else {
                alert('Eroare: ' + (result.message || 'Nu s-a putut opri sesiunea'));
                this.innerHTML = originalContent;
            }
        } catch (e) {
            // Extract exact error message from API response
            let errorMessage = 'Eroare la oprirea sesiunii';
            if (e.status === 400 && e.data && e.data.message) {
                errorMessage = e.data.message;
            } else if (e.data && e.data.message) {
                errorMessage = e.data.message;
            } else if (e.message) {
                errorMessage = e.message;
            }
            alert('Eroare: ' + errorMessage);
            console.error('Stop session error:', e);
            this.innerHTML = originalContent;
        } finally {
            isProcessing = false;
            this.disabled = false;
        }
    });

    // ===== ASSIGNMENT FUNCTIONS =====

    let childChoices = null;
    let guardianChoices = null;
    let childSearchTimeout = null;
    let guardianSearchTimeout = null;

    // Initialize Choices.js for searchable selects
    function initializeChoices() {
        // Children select with search
        const childSelect = document.getElementById('childSelect');
        if (childSelect && !childChoices) {
            childChoices = new Choices(childSelect, {
                searchEnabled: true,
                searchPlaceholderValue: 'Scrie pentru a căuta...',
                noResultsText: 'Niciun copil găsit',
                noChoicesText: 'Nu există opțiuni',
                itemSelectText: 'Click pentru a selecta',
                loadingText: 'Se încarcă...',
                shouldSort: false,
                searchChoices: false, // Disable local filtering, use server-side only
                searchResultLimit: 1000
            });
            
            // Update button state when child is selected/deselected
            childChoices.passedElement.element.addEventListener('addItem', function(event) {
                updateAssignButtonState();
            });
            
            // Also listen for remove events (when selection is cleared)
            childChoices.passedElement.element.addEventListener('removeItem', function(event) {
                updateAssignButtonState();
            });
            
            // Setup search listener after Choices is initialized
            setTimeout(() => {
                setupChoicesSearch();
            }, 100);
        }

        // Guardian select with search
        const guardianSelect = document.getElementById('guardianSelect');
        if (guardianSelect && !guardianChoices) {
            // Use a wrapper div to isolate Choices.js from affecting sibling elements
            guardianChoices = new Choices(guardianSelect, {
                searchEnabled: true,
                searchPlaceholderValue: 'Scrie pentru a căută...',
                noResultsText: 'Niciun părinte găsit',
                noChoicesText: 'Nu există opțiuni',
                itemSelectText: 'Click pentru a selecta',
                loadingText: 'Se încarcă...',
                shouldSort: false,
                searchChoices: false, // Disable local filtering, use server-side only
                searchResultLimit: 1000,
                // CRITICAL: Prevent Choices.js from modifying parent container
                callbackOnInit: function() {
                    // Ensure tabs still exist after Choices init
                    if (!document.getElementById('subTabExistingGuardian')) {
                        console.error('CRITICAL: subTabExistingGuardian was removed by Choices.js!');
                    }
                    // Ensure guardian info banner still exists
                    if (!document.getElementById('guardianInfoBanner')) {
                        console.error('CRITICAL: guardianInfoBanner was removed by Choices.js!');
                    }
                }
            });
            
            // Setup search listener after Choices is initialized
            setTimeout(() => {
                setupChoicesSearch();
            }, 100);
            
            // Protect guardian info banner from being removed by Choices.js
            // This function can be called multiple times safely
            protectGuardianInfoBanner();
        }
    }

    // Function to protect and restore guardian info banner
    // Use a flag to prevent multiple observers
    if (!window.guardianBannerProtected) {
        window.guardianBannerProtected = true;
        window.guardianBannerObserver = null;
        window.guardianBannerVisibilityObserver = null;
    }
    
    function protectGuardianInfoBanner() {
        const createNewPanel = document.getElementById('createNewPanel');
        if (!createNewPanel) return;
        
        const guardianInfoBanner = document.getElementById('guardianInfoBanner');
        
        // Store banner content if it exists
        let bannerContent = null;
        if (guardianInfoBanner) {
            bannerContent = guardianInfoBanner.innerHTML;
        } else {
            // Banner doesn't exist, create default content
            bannerContent = `
                <h5 class="text-xl font-bold text-green-900 mb-2">
                    <i class="fas fa-user-shield mr-2"></i>PASUL 1: PĂRINTE
                </h5>
                <p class="text-sm font-semibold text-green-800">
                    ⚠️ ATENȚIE: Aici se creează sau selectează <span class="font-bold text-green-900">PĂRINTELE</span>, NU copilul!
                </p>
                <p class="text-xs text-green-700 mt-2">
                    Copilul va apărea în pasul următor, după ce completezi numele și telefonul părintelui.
                </p>
            `;
        }
        
        // Function to ensure banner exists
        const ensureBannerExists = () => {
            if (!createNewPanel) return;
            
            // Find the grid container that should contain both banners
            const gridContainer = createNewPanel.querySelector('.grid.grid-cols-2');
            if (!gridContainer) return;
            
            // Check if banner exists
            let banner = document.getElementById('guardianInfoBanner');
            
            if (!banner) {
                // Banner doesn't exist, create it
                banner = document.createElement('div');
                banner.id = 'guardianInfoBanner';
                banner.className = 'bg-green-50 border-2 border-green-300 rounded-lg p-4';
                banner.style.cssText = 'position: relative; z-index: 1;';
                banner.innerHTML = bannerContent;
                
                // Insert as first child of grid container (first column)
                const childInfoBanner = document.getElementById('childInfoBanner');
                if (childInfoBanner) {
                    // Insert before child banner (first position in grid)
                    gridContainer.insertBefore(banner, childInfoBanner);
                } else {
                    // If child banner doesn't exist, append to grid
                    gridContainer.insertBefore(banner, gridContainer.firstChild);
                }
            } else {
                // Banner exists, check if it's in the right place
                const isInGrid = gridContainer.contains(banner);
                
                if (!isInGrid) {
                    // Banner exists but is not in grid, move it to the correct position
                    const childInfoBanner = document.getElementById('childInfoBanner');
                    if (childInfoBanner) {
                        gridContainer.insertBefore(banner, childInfoBanner);
                    } else {
                        gridContainer.insertBefore(banner, gridContainer.firstChild);
                    }
                } else {
                    // Banner is in grid, ensure it's in the first position
                    const childInfoBanner = document.getElementById('childInfoBanner');
                    if (childInfoBanner && banner.nextSibling !== childInfoBanner) {
                        // Reorder: banner should be before childInfoBanner
                        gridContainer.insertBefore(banner, childInfoBanner);
                    }
                }
                
                // Update content if needed
                if (banner.innerHTML !== bannerContent) {
                    banner.innerHTML = bannerContent;
                }
                
                // Ensure classes and styles are correct
                if (!banner.className.includes('bg-green-50')) {
                    banner.className = 'bg-green-50 border-2 border-green-300 rounded-lg p-4';
                }
                if (banner.style.position !== 'relative' || banner.style.zIndex !== '1') {
                    banner.style.cssText = 'position: relative; z-index: 1;';
                }
            }
        };
        
        // Ensure banner exists immediately
        ensureBannerExists();
        
        // Disconnect existing observers if they exist
        if (window.guardianBannerObserver) {
            window.guardianBannerObserver.disconnect();
        }
        if (window.guardianBannerVisibilityObserver) {
            window.guardianBannerVisibilityObserver.disconnect();
        }
        
        // Monitor createNewPanel for changes
        window.guardianBannerObserver = new MutationObserver(function(mutations) {
            ensureBannerExists();
        });
        
        // Observe createNewPanel for child list changes
        window.guardianBannerObserver.observe(createNewPanel, { 
            childList: true, 
            subtree: true,
            attributes: false
        });
        
        // Also check when panel becomes visible
        window.guardianBannerVisibilityObserver = new MutationObserver(function(mutations) {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    if (!createNewPanel.classList.contains('hidden')) {
                        // Panel is now visible, ensure banner exists
                        setTimeout(ensureBannerExists, 50);
                    }
                }
            });
        });
        
        window.guardianBannerVisibilityObserver.observe(createNewPanel, { 
            attributes: true,
            attributeFilter: ['class']
        });
    }

    // Load and populate children
    async function loadChildren(searchQuery = '') {
        try {
            const url = new URL('/children-search', window.location.origin);
            if (searchQuery) url.searchParams.set('q', searchQuery);
            // Exclude children with active sessions when assigning bracelet
            url.searchParams.set('exclude_active_sessions', '1');
            
            const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            
            if (childChoices && data.success && data.children) {
                const choices = data.children.map(child => {
                    let label = child.name;
                    if (child.guardian_name) {
                        label += ` - ${child.guardian_name}`;
                    }
                    if (child.guardian_phone) {
                        label += ` (${child.guardian_phone})`;
                    }
                    return {
                        value: child.id,
                        label: label,
                        selected: false
                    };
                });
                
                childChoices.clearStore();
                childChoices.setChoices(choices, 'value', 'label', true);
            }
        } catch (e) {
            console.error('Error loading children:', e);
        }
    }

    // Load and populate guardians
    async function loadGuardians(searchQuery = '') {
        try {
            const url = new URL('/guardians-search', window.location.origin);
            if (searchQuery) url.searchParams.set('q', searchQuery);
            
            const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            
            if (guardianChoices && data.success && data.guardians) {
                const choices = data.guardians.map(guardian => ({
                    value: guardian.id,
                    label: `${guardian.name}${guardian.phone ? ' - ' + guardian.phone : ''}`,
                    selected: false
                }));
                
                guardianChoices.clearStore();
                guardianChoices.setChoices(choices, 'value', 'label', true);
            }
        } catch (e) {
            console.error('Error loading guardians:', e);
        }
    }

    // Update assign button state based on child selection
    async function updateAssignButtonState() {
        const assignBtn = document.getElementById('assignChildBtn');
        const statusMessage = document.getElementById('childSelectionStatus');
        if (!assignBtn) return;
        
        let childId = null;
        if (childChoices) {
            const value = childChoices.getValue(true);
            childId = Array.isArray(value) ? value[0] : value;
        } else {
            childId = document.getElementById('childSelect')?.value || null;
        }
        
        // Reset status message
        if (statusMessage) {
            statusMessage.textContent = '';
            statusMessage.classList.add('hidden');
        }
        
        // Basic check: child and bracelet selected
        if (!childId || !currentBracelet) {
            assignBtn.disabled = true;
            selectedChildHasActiveSession = false;
            return;
        }
        
        // Check if child has active session
        try {
            const result = await apiCall(`/scan-api/child-session/${childId}`);
            if (result.success && result.active_session) {
                selectedChildHasActiveSession = true;
                assignBtn.disabled = true;
                if (statusMessage) {
                    const sessionStart = new Date(result.active_session.started_at).toLocaleString('ro-RO');
                    statusMessage.textContent = `⚠️ Acest copil are deja o sesiune activă începută la ${sessionStart}. Te rog oprește sesiunea existentă înainte de a asigna o brățară nouă.`;
                    statusMessage.classList.remove('hidden');
                    statusMessage.className = 'text-sm text-amber-600 mt-2';
                }
                return;
            }
        } catch (e) {
            // If error (404 means no active session), continue
            // This is expected if child doesn't have active session
        }
        
        selectedChildHasActiveSession = false;
        assignBtn.disabled = false;
    }

    // Assign existing child to bracelet
    document.getElementById('assignChildBtn').addEventListener('click', async function() {
        // Prevent double-click
        if (isProcessing) {
            return;
        }
        
        let childId = null;
        if (childChoices) {
            const value = childChoices.getValue(true);
            childId = Array.isArray(value) ? value[0] : value;
        } else {
            childId = document.getElementById('childSelect')?.value || null;
        }
        
        if (!childId) {
            alert('Te rog selectează un copil');
            return;
        }
        
        if (!currentBracelet) {
            alert('Nu există brățară scanată');
            updateAssignButtonState();
            return;
        }
        
        // Final check: prevent assignment if child has active session
        if (selectedChildHasActiveSession) {
            alert('Acest copil are deja o sesiune activă. Te rog oprește sesiunea existentă înainte de a asigna o brățară nouă.');
            return;
        }
        
        // Disable button immediately
        isProcessing = true;
        this.disabled = true;
        this.textContent = 'Se asignează...';
        
        try {
            const result = await apiCall('/scan-api/assign', {
                method: 'POST',
                body: JSON.stringify({
                    bracelet_code: currentBracelet ? currentBracelet.code : null,
                    child_id: childId
                })
            });
            
            if (result.success) {
                // Clear selection
                if (childChoices) {
                    childChoices.clearStore();
                    childChoices.setChoices([{ value: '', label: 'Caută și selectează copil...', selected: true }], 'value', 'label', true);
                }
                // Refresh bracelet info - sesiunea activă va apărea automat
                const data = await apiCall('/scan-api/lookup', {
                    method: 'POST',
                    body: JSON.stringify({ code: currentBracelet.code })
                });
                renderBraceletInfo(data);
                
                // Initialize products if session was created
                if (data.active_session && data.active_session.id) {
                    initializeProductsForSession(data.active_session.id);
                }
                
                // Refresh active sessions info
                loadActiveSessionsInfo();
                // Clear input and prepare for next scan after successful assignment
                setTimeout(() => {
                    clearInputForNextScan();
                }, 300);
            } else {
                alert('Eroare: ' + (result.message || 'Nu s-a putut asigna'));
            }
        } catch (e) {
            // Check if error is about active session
            if (e.status === 400 && e.data && e.data.message && e.data.message.includes('sesiune activă')) {
                alert('Eroare: ' + e.data.message);
                // Refresh button state
                await updateAssignButtonState();
            } else {
                alert('Eroare la asignare: ' + (e.message || 'Eroare necunoscută'));
            }
            console.error(e);
        } finally {
            isProcessing = false;
            this.textContent = 'Asignează copilul selectat';
            await updateAssignButtonState();
        }
    });

    // Create new child and assign
    document.getElementById('createAndAssignBtn').addEventListener('click', async function() {
        // Prevent double-click
        if (isProcessing) {
            return;
        }
        
        const guardianId = document.getElementById('guardianSelect').value;
        const guardianName = document.getElementById('guardianName').value.trim();
        const guardianPhone = document.getElementById('guardianPhone').value.trim();
        
        const childFullName = document.getElementById('childFullName').value.trim();
        
        // Validations
        if (!childFullName) {
            alert('Te rog completează numele complet al copilului');
            return;
        }
        
        // Store full name in first_name (for backward compatibility with backend)
        const childFirstName = childFullName;
        
        // Determine guardian mode based on radio buttons
        const guardianMode = document.getElementById('radioNewGuardian').checked ? 'new' : 'existing';
        if (guardianMode === 'existing') {
            if (!guardianId) {
                alert('Te rog selectează un părinte existent');
                return;
            }
            // Terms check is already done when guardian is selected, so we can proceed
        } else {
            if (!guardianName || !guardianPhone) {
                alert('Te rog completează Nume și Telefon pentru părinte nou');
                return;
            }
            // Validate terms acceptance for new guardian
            const termsAccepted = document.getElementById('terms_accepted').checked;
            const gdprAccepted = document.getElementById('gdpr_accepted').checked;
            if (!termsAccepted || !gdprAccepted) {
                alert('Te rog acceptă termenii și condițiile și politica GDPR');
                return;
            }
        }
        
        if (!currentBracelet) {
            alert('Nu există brățară scanată');
            return;
        }
        
        // Disable button immediately
        isProcessing = true;
        this.disabled = true;
        this.textContent = 'Se creează...';
        
        try {
            const payload = {
                first_name: childFirstName,
                allergies: null,
                bracelet_code: currentBracelet.code
            };
            
            if (guardianMode === 'existing' && guardianId) {
                payload.guardian_id = parseInt(guardianId);
            } else {
                payload.guardian_name = guardianName;
                payload.guardian_phone = guardianPhone;
                payload.terms_accepted = true;
                payload.gdpr_accepted = true;
            }
            
            const result = await apiCall('/scan-api/create-child', {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            
            if (result.success) {
                // Clear form
                const guardianSelectEl = document.getElementById('guardianSelect');
                if (guardianSelectEl) {
                    guardianSelectEl.value = '';
                    // Reset Choices.js if it exists
                    if (guardianChoices) {
                        guardianChoices.clearStore();
                        guardianChoices.setChoices([{ value: '', label: 'Caută și selectează părinte...', selected: true }], 'value', 'label', true);
                    }
                }
                document.getElementById('guardianName').value = '';
                document.getElementById('guardianPhone').value = '';
                document.getElementById('childFullName').value = '';
                // Hide child section again
                childSection.classList.add('hidden');
                const childInfoBanner = document.getElementById('childInfoBanner');
                if (childInfoBanner) {
                    childInfoBanner.classList.add('opacity-0', 'pointer-events-none');
                    childInfoBanner.classList.remove('opacity-100');
                }
                // Reset to existing guardian mode
                const radioExistingGuardianEl = document.getElementById('radioExistingGuardian');
                if (radioExistingGuardianEl) {
                    radioExistingGuardianEl.checked = true;
                    switchGuardianMode('existing');
                }
                
                // Refresh bracelet info - sesiunea activă va apărea automat
                const data = await apiCall('/scan-api/lookup', {
                    method: 'POST',
                    body: JSON.stringify({ code: currentBracelet.code })
                });
                renderBraceletInfo(data);
                // Refresh active sessions info
                loadActiveSessionsInfo();
                // Clear input and prepare for next scan after successful creation
                setTimeout(() => {
                    clearInputForNextScan();
                }, 300);
            } else {
                alert('Eroare: ' + (result.message || 'Nu s-a putut crea'));
            }
        } catch (e) {
            // Extract exact error message from API response
            let errorMessage = 'Eroare la creare';
            if (e.status === 400 && e.data && e.data.message) {
                errorMessage = e.data.message;
            } else if (e.data && e.data.message) {
                errorMessage = e.data.message;
            } else if (e.message) {
                errorMessage = e.message;
            }
            alert('Eroare: ' + errorMessage);
            console.error(e);
        } finally {
            isProcessing = false;
            this.disabled = false;
            this.textContent = 'Creează și asignează';
        }
    });


    // Setup search listeners for Choices.js
    function setupChoicesSearch() {
        // Use event delegation on document to catch input events from Choices.js search fields
        // This approach is safer and doesn't interfere with Choices.js functionality
        
        // Children search - use event delegation
        if (childChoices) {
            // Remove any existing listener first by using a flag
            if (!window.childSearchHandlerAttached) {
                document.addEventListener('input', function childSearchHandler(event) {
                    // Check if the input is from the child choices search field
                    const target = event.target;
                    if (target && target.classList && target.classList.contains('choices__input')) {
                        // Check if it's the child select input by checking if it's in the child select container
                        const childContainer = childChoices?.containerOuter?.element;
                        if (childContainer && childContainer.contains(target)) {
                            const searchValue = target.value || '';
                            clearTimeout(childSearchTimeout);
                            childSearchTimeout = setTimeout(() => {
                                loadChildren(searchValue);
                            }, 300);
                        }
                    }
                });
                window.childSearchHandlerAttached = true;
            }
        }

        // Guardian search - use event delegation
        if (guardianChoices) {
            if (!window.guardianSearchHandlerAttached) {
                document.addEventListener('input', function guardianSearchHandler(event) {
                    const target = event.target;
                    if (target && target.classList && target.classList.contains('choices__input')) {
                        const guardianContainer = guardianChoices?.containerOuter?.element;
                        if (guardianContainer && guardianContainer.contains(target)) {
                            const searchValue = target.value || '';
                            clearTimeout(guardianSearchTimeout);
                            guardianSearchTimeout = setTimeout(() => {
                                loadGuardians(searchValue);
                            }, 300);
                        }
                    }
                });
                window.guardianSearchHandlerAttached = true;
            }
        }
    }

    // Load initial data when assignment section becomes visible
    const assignmentSection = document.getElementById('assignmentSection');
    const tabAssignExisting = document.getElementById('tabAssignExisting');
    const tabCreateNew = document.getElementById('tabCreateNew');
    const panelAssignExisting = document.getElementById('assignExistingPanel');
    const panelCreateNew = document.getElementById('createNewPanel');

    function switchTab(which) {
        if (which === 'assign') {
            tabAssignExisting.setAttribute('aria-selected', 'true');
            tabAssignExisting.className = 'px-4 py-2 text-base font-semibold rounded-t-md bg-gray-100 text-gray-900';
            tabCreateNew.setAttribute('aria-selected', 'false');
            tabCreateNew.className = 'px-4 py-2 text-sm font-medium rounded-t-md text-gray-600 hover:text-gray-900';
            panelAssignExisting.classList.remove('hidden');
            panelCreateNew.classList.add('hidden');
            // ensure children choices initialized and loaded
            initializeChoices();
            setupChoicesSearch();
            loadChildren();
            updateAssignButtonState();
        } else {
            tabAssignExisting.setAttribute('aria-selected', 'false');
            tabAssignExisting.className = 'px-4 py-2 text-base font-semibold rounded-t-md text-gray-600 hover:text-gray-900';
            tabCreateNew.setAttribute('aria-selected', 'true');
            tabCreateNew.className = 'px-4 py-2 text-sm font-medium rounded-t-md bg-gray-100 text-gray-900';
            panelAssignExisting.classList.add('hidden');
            panelCreateNew.classList.remove('hidden');
            // ensure guardian choices initialized and loaded
            initializeChoices();
            setupChoicesSearch();
            loadGuardians();
            // Ensure banner exists when switching to create tab
            setTimeout(() => {
                protectGuardianInfoBanner();
            }, 100);
        }
    }

    // Radio buttons logic for guardian existing/new
    const radioExistingGuardian = document.getElementById('radioExistingGuardian');
    const radioNewGuardian = document.getElementById('radioNewGuardian');
    const existingGuardianPanel = document.getElementById('existingGuardianPanel');
    const newGuardianPanel = document.getElementById('newGuardianPanel');
    const childSection = document.getElementById('childSection');

    // Function to check if child section should be visible
    function checkAndShowChildSection() {
        const isExistingMode = radioExistingGuardian.checked;
        const isNewMode = radioNewGuardian.checked;
        const childInfoBanner = document.getElementById('childInfoBanner');

        const termsAcceptanceSection = document.getElementById('termsAcceptanceSection');
        if (isExistingMode) {
            // Show child section if a guardian is selected
            const guardianSelectEl = document.getElementById('guardianSelect');
            if (guardianSelectEl && guardianSelectEl.value) {
                childSection.classList.remove('hidden');
                if (childInfoBanner) {
                    childInfoBanner.classList.remove('opacity-0', 'pointer-events-none');
                    childInfoBanner.classList.add('opacity-100');
                }
                // Hide terms acceptance section for existing guardian
                if (termsAcceptanceSection) termsAcceptanceSection.classList.add('hidden');
            } else {
                childSection.classList.add('hidden');
                if (childInfoBanner) {
                    childInfoBanner.classList.add('opacity-0', 'pointer-events-none');
                    childInfoBanner.classList.remove('opacity-100');
                }
            }
        } else if (isNewMode) {
            // Show child section if name AND phone are filled
            const guardianName = document.getElementById('guardianName').value.trim();
            const guardianPhone = document.getElementById('guardianPhone').value.trim();
            if (guardianName && guardianPhone) {
                childSection.classList.remove('hidden');
                if (childInfoBanner) {
                    childInfoBanner.classList.remove('opacity-0', 'pointer-events-none');
                    childInfoBanner.classList.add('opacity-100');
                }
                // Show terms acceptance section for new guardian
                if (termsAcceptanceSection) termsAcceptanceSection.classList.remove('hidden');
            } else {
                childSection.classList.add('hidden');
                if (childInfoBanner) {
                    childInfoBanner.classList.add('opacity-0', 'pointer-events-none');
                    childInfoBanner.classList.remove('opacity-100');
                }
                if (termsAcceptanceSection) termsAcceptanceSection.classList.add('hidden');
            }
        }
    }

    function switchGuardianMode(mode) {
        const termsAcceptanceSection = document.getElementById('termsAcceptanceSection');
        if (mode === 'existing') {
            existingGuardianPanel.classList.remove('hidden');
            newGuardianPanel.classList.add('hidden');
            // Hide terms acceptance section for existing guardian
            if (termsAcceptanceSection) termsAcceptanceSection.classList.add('hidden');
            // clear new guardian inputs to avoid accidental submit
            document.getElementById('guardianName').value = '';
            document.getElementById('guardianPhone').value = '';
            // Clear terms checkboxes
            document.getElementById('terms_accepted').checked = false;
            document.getElementById('gdpr_accepted').checked = false;
        } else {
            existingGuardianPanel.classList.add('hidden');
            newGuardianPanel.classList.remove('hidden');
            // Show terms acceptance section for new guardian
            if (termsAcceptanceSection) termsAcceptanceSection.classList.remove('hidden');
            // clear selection in choices for guardian if exists
            const guardianSelectEl = document.getElementById('guardianSelect');
            if (guardianSelectEl) {
                guardianSelectEl.value = '';
                if (guardianChoices) {
                    guardianChoices.clearStore();
                    guardianChoices.setChoices([{ value: '', label: 'Caută și selectează părinte...', selected: true }], 'value', 'label', true);
                }
            }
        }
        // Check if child section should be visible after mode switch
        checkAndShowChildSection();
    }

    if (radioExistingGuardian && radioNewGuardian) {
        radioExistingGuardian.addEventListener('change', () => {
            if (radioExistingGuardian.checked) switchGuardianMode('existing');
        });
        radioNewGuardian.addEventListener('change', () => {
            if (radioNewGuardian.checked) switchGuardianMode('new');
        });
    }

    // Listen for guardian selection changes (existing mode)
    const guardianSelectEl = document.getElementById('guardianSelect');
    if (guardianSelectEl) {
        guardianSelectEl.addEventListener('change', async function() {
            checkAndShowChildSection();
            // Check if selected guardian needs to accept terms
            const guardianId = this.value;
            if (guardianId) {
                const termsCheck = await checkGuardianTerms(guardianId);
                if (!termsCheck.accepted) {
                    // Show modal for terms acceptance
                    const accepted = await showTermsAcceptanceModal();
                    if (accepted) {
                        // Save acceptance
                        const saved = await saveGuardianTermsAcceptance(guardianId);
                        if (!saved) {
                            alert('Eroare la salvarea acceptării termenilor. Te rog încearcă din nou.');
                            // Clear selection
                            if (guardianChoices) {
                                guardianChoices.clearStore();
                                guardianChoices.setChoices([{ value: '', label: 'Caută și selectează părinte...', selected: true }], 'value', 'label', true);
                            }
                            childSection.classList.add('hidden');
                            const childInfoBanner = document.getElementById('childInfoBanner');
                            if (childInfoBanner) {
                    childInfoBanner.classList.add('opacity-0', 'pointer-events-none');
                    childInfoBanner.classList.remove('opacity-100');
                }
                        }
                    } else {
                        // User cancelled - clear selection
                        if (guardianChoices) {
                            guardianChoices.clearStore();
                            guardianChoices.setChoices([{ value: '', label: 'Caută și selectează părinte...', selected: true }], 'value', 'label', true);
                        }
                        childSection.classList.add('hidden');
                        const childInfoBanner = document.getElementById('childInfoBanner');
                        if (childInfoBanner) {
                    childInfoBanner.classList.add('opacity-0', 'pointer-events-none');
                    childInfoBanner.classList.remove('opacity-100');
                }
                    }
                }
            }
        });
    }

    // Listen for input changes in new guardian fields (new mode)
    const guardianNameInput = document.getElementById('guardianName');
    const guardianPhoneInput = document.getElementById('guardianPhone');
    const childFullNameInput = document.getElementById('childFullName');
    
    // Transform to uppercase automatically
    if (guardianNameInput) {
        guardianNameInput.addEventListener('input', function(e) {
            const cursorPosition = e.target.selectionStart;
            e.target.value = e.target.value.toUpperCase();
            e.target.setSelectionRange(cursorPosition, cursorPosition);
            checkAndShowChildSection();
        });
    }
    
    if (childFullNameInput) {
        childFullNameInput.addEventListener('input', function(e) {
            const cursorPosition = e.target.selectionStart;
            e.target.value = e.target.value.toUpperCase();
            e.target.setSelectionRange(cursorPosition, cursorPosition);
        });
    }
    
    if (guardianPhoneInput) {
        guardianPhoneInput.addEventListener('input', checkAndShowChildSection);
    }

    if (tabAssignExisting && tabCreateNew) {
        tabAssignExisting.addEventListener('click', () => switchTab('assign'));
        tabCreateNew.addEventListener('click', () => switchTab('create'));
    }
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                if (!assignmentSection.classList.contains('hidden') && !assignmentInitialized) {
                    // Initialize only once when section becomes visible
                    switchTab('assign');
                    switchGuardianMode('existing');
                    assignmentInitialized = true;
                    // Ensure banner is protected when assignment section is initialized
                    setTimeout(() => {
                        protectGuardianInfoBanner();
                    }, 200);
                }
            }
        });
    });
    observer.observe(assignmentSection, { attributes: true });

    // ===== TERMS ACCEPTANCE FUNCTIONS =====
    
    /**
     * Check if guardian has accepted terms
     */
    async function checkGuardianTerms(guardianId) {
        try {
            const result = await apiCall(`/scan-api/check-guardian-terms`, {
                method: 'POST',
                body: JSON.stringify({ guardian_id: guardianId })
            });
            return result.success ? { accepted: result.accepted, needsTerms: result.needs_terms, needsGdpr: result.needs_gdpr } : { accepted: false };
        } catch (e) {
            console.error('Error checking guardian terms:', e);
            return { accepted: false };
        }
    }

    /**
     * Show terms acceptance modal and return promise
     */
    function showTermsAcceptanceModal() {
        return new Promise((resolve) => {
            const modal = document.getElementById('termsAcceptanceModal');
            const acceptBtn = document.getElementById('acceptTermsModalBtn');
            const cancelBtn = document.getElementById('cancelTermsModalBtn');
            const termsCheckbox = document.getElementById('modal_terms_accepted');
            const gdprCheckbox = document.getElementById('modal_gdpr_accepted');

            // Reset checkboxes
            termsCheckbox.checked = false;
            gdprCheckbox.checked = false;

            // Show modal
            modal.classList.remove('hidden');

            // Handle accept button
            const handleAccept = () => {
                if (!termsCheckbox.checked || !gdprCheckbox.checked) {
                    alert('Te rog acceptă ambele checkbox-uri pentru a continua');
                    return;
                }
                modal.classList.add('hidden');
                acceptBtn.removeEventListener('click', handleAccept);
                cancelBtn.removeEventListener('click', handleCancel);
                resolve(true);
            };

            // Handle cancel button
            const handleCancel = () => {
                modal.classList.add('hidden');
                acceptBtn.removeEventListener('click', handleAccept);
                cancelBtn.removeEventListener('click', handleCancel);
                resolve(false);
            };

            acceptBtn.addEventListener('click', handleAccept);
            cancelBtn.addEventListener('click', handleCancel);
        });
    }

    /**
     * Save guardian terms acceptance
     */
    async function saveGuardianTermsAcceptance(guardianId) {
        try {
            const result = await apiCall('/scan-api/accept-guardian-terms', {
                method: 'POST',
                body: JSON.stringify({ guardian_id: guardianId })
            });
            return result.success;
        } catch (e) {
            console.error('Error saving guardian terms acceptance:', e);
            return false;
        }
    }

    // ===== PRODUCTS MANAGEMENT =====
    
    let currentSessionId = null;
    let availableProducts = [];
    let sessionProducts = [];

    // Load available products
    async function loadAvailableProducts() {
        try {
            const result = await apiCall('/scan-api/available-products');
            if (result.success && result.products) {
                availableProducts = result.products;
            }
        } catch (e) {
            console.error('Error loading products:', e);
        }
    }

    // Load session products
    async function loadSessionProducts(sessionId) {
        if (!sessionId) return;
        try {
            const result = await apiCall(`/scan-api/session-products/${sessionId}`);
            if (result.success && result.products) {
                sessionProducts = result.products;
                renderProductsList();
            }
        } catch (e) {
            console.error('Error loading session products:', e);
            sessionProducts = [];
            renderProductsList();
        }
    }

    // Render products list
    function renderProductsList() {
        const productsListEl = document.getElementById('productsList');
        if (!productsListEl) return;

        if (sessionProducts.length === 0) {
            productsListEl.innerHTML = '<p class="text-sm text-gray-500">Nu sunt produse adăugate</p>';
            return;
        }

        productsListEl.innerHTML = sessionProducts.map(product => `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                <div>
                    <div class="font-medium text-gray-900">${product.product_name}</div>
                    <div class="text-sm text-gray-500">${product.quantity} buc × ${parseFloat(product.unit_price).toFixed(2)} RON</div>
                </div>
                <div class="text-right">
                    <div class="font-semibold text-gray-900">${parseFloat(product.total_price).toFixed(2)} RON</div>
                </div>
            </div>
        `).join('');
    }

    // Open add products modal
    function openAddProductsModal() {
        if (!currentSessionId) {
            alert('Nu există o sesiune activă');
            return;
        }

        const modal = document.getElementById('addProductsModal');
        if (!modal) return;

        // Populate products dropdown
        const productsSelect = document.getElementById('productsSelect');
        if (productsSelect && availableProducts.length > 0) {
            productsSelect.innerHTML = '<option value="">Selectează produs...</option>' +
                availableProducts.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name} - ${parseFloat(p.price).toFixed(2)} RON</option>`).join('');
        }

        // Reset form
        document.getElementById('productQuantity').value = '1';
        productsSelect.value = '';

        modal.classList.remove('hidden');
    }

    // Close add products modal
    function closeAddProductsModal() {
        const modal = document.getElementById('addProductsModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Add product to session
    async function addProductToSession() {
        const productsSelect = document.getElementById('productsSelect');
        const quantityInput = document.getElementById('productQuantity');
        
        if (!productsSelect || !quantityInput || !currentSessionId) {
            return;
        }

        const productId = productsSelect.value;
        const quantity = parseInt(quantityInput.value);

        if (!productId || quantity < 1) {
            alert('Te rog selectează un produs și introdu o cantitate validă');
            return;
        }

        try {
            const result = await apiCall('/scan-api/add-products', {
                method: 'POST',
                body: JSON.stringify({
                    session_id: currentSessionId,
                    products: [{
                        product_id: parseInt(productId),
                        quantity: quantity
                    }]
                })
            });

            if (result.success) {
                // Add to local list
                if (result.products && result.products.length > 0) {
                    sessionProducts.push(...result.products);
                    renderProductsList();
                }
                closeAddProductsModal();
            } else {
                alert('Eroare: ' + (result.message || 'Nu s-a putut adăuga produsul'));
            }
        } catch (e) {
            console.error('Error adding product:', e);
            alert('Eroare la adăugarea produsului');
        }
    }

    // Initialize products when session is loaded
    function initializeProductsForSession(sessionId) {
        currentSessionId = sessionId;
        loadSessionProducts(sessionId);
    }

    // Bind add products button
    const addProductsBtn = document.getElementById('addProductsBtn');
    if (addProductsBtn) {
        addProductsBtn.addEventListener('click', openAddProductsModal);
    }

    // Bind modal close buttons
    const closeAddProductsModalBtn = document.getElementById('closeAddProductsModal');
    const cancelAddProductsBtn = document.getElementById('cancelAddProducts');
    const saveAddProductsBtn = document.getElementById('saveAddProducts');
    const addProductsOverlay = document.getElementById('addProductsOverlay');

    if (closeAddProductsModalBtn) {
        closeAddProductsModalBtn.addEventListener('click', closeAddProductsModal);
    }
    if (cancelAddProductsBtn) {
        cancelAddProductsBtn.addEventListener('click', closeAddProductsModal);
    }
    if (saveAddProductsBtn) {
        saveAddProductsBtn.addEventListener('click', addProductToSession);
    }
    if (addProductsOverlay) {
        addProductsOverlay.addEventListener('click', closeAddProductsModal);
    }

    // Load products on page load
    loadAvailableProducts();


</script>
@endsection
