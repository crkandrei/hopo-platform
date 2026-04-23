@extends('layouts.app')

@section('title', $braceletRequired ? 'Scanare Brățară' : 'Start Sesiune')
@section('page-title', $braceletRequired ? 'Scanare Brățară' : 'Start Sesiune')

@section('content')
<div class="space-y-6">
    <!-- Sticky input bar -->
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

            @if($braceletRequired)
            {{-- ========== BRACELET MODE INPUT ========== --}}
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3 relative">
                    <label for="rfidCode" class="sr-only">Cod RFID</label>
                    <div class="flex-1 relative">
                        <input id="rfidCode" maxlength="50" autocomplete="off"
                               class="w-full h-12 px-4 pr-10 text-2xl tracking-widest font-mono border rounded-md focus:outline-none focus:ring-4 transition-colors"
                               placeholder="Cod brățară">
                        <div id="rfidCodeIcon" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                    </div>
                    <button id="searchBtn" disabled
                            class="h-12 px-6 text-lg bg-gray-900 text-white rounded-md disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        Caută
                    </button>
                    <div id="childrenSearchResults" class="hidden absolute top-full left-0 mt-1 bg-white border border-gray-300 rounded-md shadow-lg z-50 max-h-60 overflow-y-auto" style="width: calc(100% - 200px);">
                        <div id="childrenSearchResultsList" class="py-1"></div>
                    </div>
                </div>
                <div id="rfidCodeError" class="hidden text-xs text-red-600 px-1 flex items-center gap-1">
                    <i class="fas fa-exclamation-circle"></i>
                    <span></span>
                </div>
            </div>
            @else
            {{-- ========== NAME SEARCH MODE INPUT ========== --}}
            <div class="flex items-center gap-3 relative">
                <label for="childSearchInput" class="sr-only">Caută copil</label>
                <div class="flex-1 relative">
                    <input id="childSearchInput"
                           type="text"
                           autocomplete="off"
                           maxlength="100"
                           class="w-full h-12 px-4 text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                           placeholder="Caută copil după nume sau telefon părinte...">
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
            @endif
        </div>
    </div>

    <!-- Main content area -->
    <div class="max-w-6xl mx-auto px-0 space-y-6">

        <!-- State card -->
        <div id="stateCard" class="bg-white border border-gray-300 rounded-lg p-6" aria-live="polite">
            <div class="text-gray-500">
                @if($braceletRequired)
                    Introduceți sau scanați un cod pentru a începe.
                @else
                    Căutați un copil sau apăsați <strong>+ Copil nou</strong> pentru a porni o sesiune.
                @endif
            </div>
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
                    @if($braceletRequired)
                    <div>
                        <div class="text-sm text-gray-600">Cod brățară</div>
                        <div id="sessionBraceletCode" class="text-xl font-mono font-semibold text-gray-900 tracking-wider">-</div>
                    </div>
                    @endif
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
                        <button id="pauseResumeBtn"
                            class="flex-1 h-12 px-4 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-md transition flex items-center justify-center gap-2">
                            <i class="fas fa-pause"></i><span>Pauză</span>
                        </button>
                        <button id="stopSessionBtn"
                            class="flex-1 h-12 px-4 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md transition flex items-center justify-center gap-2">
                            <i class="fas fa-stop"></i><span>Stop</span>
                        </button>
                    </div>
                    <div id="sessionStatus" class="mt-3 text-sm font-medium"></div>
                </div>
            </div>

            <!-- Products Section -->
            <div id="productsSection" class="mt-6 border-t border-gray-200 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-md font-semibold text-gray-900">Produse</h4>
                    <button id="addProductsBtn"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition flex items-center gap-2">
                        <i class="fas fa-plus"></i> Adaugă Produse
                    </button>
                </div>
                <div id="productsList" class="space-y-2">
                    <p class="text-sm text-gray-500">Nu sunt produse adăugate</p>
                </div>
            </div>
        </div>

        <!-- Assignment section (hidden by default) -->
        <div id="assignmentSection" class="hidden bg-white border border-gray-300 rounded-lg p-6">
            @if($braceletRequired)
            <!-- Bracelet code indicator -->
            <div id="assignmentBraceletBadge" class="hidden mb-4 flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-200 rounded-md">
                <i class="fas fa-tag text-indigo-500"></i>
                <span class="text-sm text-gray-600">Brățară:</span>
                <span id="assignmentBraceletCode" class="font-mono font-semibold text-indigo-900 tracking-wider"></span>
            </div>
            @endif
            <!-- Tabs -->
            <div class="mb-4 border-b border-gray-200">
                <nav class="flex gap-2" role="tablist" aria-label="Assignment tabs">
                    <button id="tabAssignExisting" type="button" aria-controls="assignExistingPanel" aria-selected="true"
                        class="px-4 py-2 text-base font-semibold rounded-t-md bg-gray-100 text-gray-900">
                        <i class="fas fa-user-check mr-2"></i>Asignează <span class="font-bold">COPIL</span> existent
                    </button>
                    <button id="tabCreateNew" type="button" aria-controls="createNewPanel" aria-selected="false"
                        class="px-4 py-2 text-sm font-medium rounded-t-md text-gray-600 hover:text-gray-900">
                        <i class="fas fa-user-plus mr-2"></i>Creează copil nou
                    </button>
                </nav>
            </div>

            <!-- Tab 1: Asignează copil existent -->
            <div id="assignExistingPanel" class="mb-8 pb-8 border-b border-gray-200">
                <div class="space-y-3">
                    @if($braceletRequired)
                    {{-- Bracelet mode: child dropdown --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Selectează copil:</label>
                        <select id="childSelect" class="w-full">
                            <option value="">Caută și selectează copil...</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">* Poți scrie în câmp pentru a căuta</p>
                    </div>
                    @else
                    {{-- Name mode: pre-selected child display --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Copil selectat:</label>
                        <div id="selectedChildDisplay" class="p-3 bg-indigo-50 border border-indigo-200 rounded-md">
                            <span id="selectedChildName" class="font-semibold text-indigo-900">-</span>
                            <span id="selectedChildGuardian" class="text-sm text-indigo-600 ml-2"></span>
                        </div>
                    </div>
                    @endif

                    <div class="flex items-center gap-3 p-3 bg-pink-50 border border-pink-200 rounded-lg">
                        <input type="checkbox" id="sessionTypeBirthdayAssign" value="birthday"
                               class="h-5 w-5 text-pink-600 border-pink-300 rounded focus:ring-pink-500">
                        <label for="sessionTypeBirthdayAssign" class="text-sm font-medium text-gray-800 cursor-pointer">
                            🎂 Sesiune Birthday
                        </label>
                    </div>

                    <!-- Pre-checkin QR scan (optional) -->
                    <div class="border-t border-gray-100 pt-3">
                        <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">QR Părinte (opțional)</label>
                        <input type="text" id="preCheckinQrInput"
                               placeholder="Scanează QR de pe telefonul părintelui..."
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                               autocomplete="off">
                        <div id="preCheckinResult" class="hidden mt-1 px-2 py-1 bg-green-50 border border-green-200 rounded text-xs text-green-800"></div>
                        <div id="preCheckinError" class="hidden mt-1 px-2 py-1 bg-red-50 border border-red-200 rounded text-xs text-red-700"></div>
                    </div>

                    <button id="assignChildBtn" disabled
                        class="w-full h-11 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ $braceletRequired ? 'Asignează copilul selectat' : 'Pornește sesiunea pentru copilul selectat' }}
                    </button>
                    <div id="childSelectionStatus" class="hidden text-sm text-amber-600 mt-2"></div>
                </div>
            </div>

            <!-- Tab 2: Creează copil nou -->
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

                        <div id="existingGuardianPanel" class="space-y-2">
                            <label class="block text-sm font-semibold text-green-800">Selectează părinte existent</label>
                            <div id="guardianSelectWrapper">
                                <select id="guardianSelect" class="w-full">
                                    <option value="">Caută și selectează părinte...</option>
                                </select>
                            </div>
                            <p class="text-xs text-gray-500">Sugestie: tastează nume sau telefon pentru a căuta rapid</p>
                        </div>

                        <div id="newGuardianPanel" class="space-y-2 hidden">
                            <label class="block text-sm font-semibold text-green-800">Creează părinte nou</label>
                            <input id="guardianName" type="text" placeholder="Nume complet *"
                                   class="w-full h-10 px-3 border border-green-300 rounded-md uppercase-input" style="text-transform: uppercase;">
                            <input id="guardianPhone" type="tel" placeholder="Telefon *"
                                   class="w-full h-10 px-3 border border-green-300 rounded-md">
                            <p class="text-xs text-gray-500">Completează minim nume și telefon</p>
                        </div>
                    </div>

                    <!-- Copil (apare după ce ai selectat/completat părintele) -->
                    <div id="childSection" class="space-y-3 hidden">
                        <input id="childFullName" type="text" placeholder="Nume complet copil *"
                            class="w-full h-10 px-3 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 uppercase-input"
                            style="text-transform: uppercase;">

                        <div id="termsAcceptanceSection" class="hidden space-y-3 pt-2 border-t border-gray-200">
                            <div class="flex items-start">
                                <input id="terms_accepted" type="checkbox" value="1"
                                    class="mt-1 w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                <label for="terms_accepted" class="ml-2 text-sm text-gray-700">
                                    Accept
                                    <a href="{{ route('legal.terms.public') }}" target="_blank" class="text-green-600 hover:text-green-800 underline">Termenii și Condițiile</a>
                                    <span class="text-red-500">*</span>
                                </label>
                            </div>
                            <div class="flex items-start">
                                <input id="gdpr_accepted" type="checkbox" value="1"
                                    class="mt-1 w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                <label for="gdpr_accepted" class="ml-2 text-sm text-gray-700">
                                    Accept
                                    <a href="{{ route('legal.gdpr.public') }}" target="_blank" class="text-green-600 hover:text-green-800 underline">Politica GDPR</a>
                                    <span class="text-red-500">*</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3 bg-pink-50 border border-pink-200 rounded-lg mt-3">
                            <input type="checkbox" id="sessionTypeBirthdayCreate" value="birthday"
                                   class="h-5 w-5 text-pink-600 border-pink-300 rounded focus:ring-pink-500">
                            <label for="sessionTypeBirthdayCreate" class="text-sm font-medium text-gray-800 cursor-pointer">
                                🎂 Sesiune Birthday
                            </label>
                        </div>

                        <button id="createAndAssignBtn"
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
                    <a href="{{ route('legal.terms.public') }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">Termenii și Condițiile</a>
                    <span class="text-red-500">*</span>
                </label>
            </div>
            <div class="flex items-start">
                <input id="modal_gdpr_accepted" type="checkbox" value="1"
                    class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="modal_gdpr_accepted" class="ml-2 text-sm text-gray-700">
                    Accept
                    <a href="{{ route('legal.gdpr.public') }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">Politica GDPR</a>
                    <span class="text-red-500">*</span>
                </label>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button id="cancelTermsModalBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Anulează</button>
            <button id="acceptTermsModalBtn" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Accept</button>
        </div>
    </div>
</div>

<!-- Modal confirmare QR pre-checkin -->
<div id="qrConfirmModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 text-center">
        <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-child text-green-600 text-2xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-1" id="qrConfirmChildName"></h3>
        <p class="text-sm text-gray-500 mb-1" id="qrConfirmGuardianInfo"></p>
        <p class="text-sm text-gray-600 mt-4 mb-6">Confirmi pornirea sesiunii pentru acest copil?</p>
        <div class="flex flex-col gap-3">
            <button id="qrConfirmBtn"
                class="w-full h-12 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition text-base flex items-center justify-center gap-2">
                <i class="fas fa-play"></i> Pornește sesiunea
            </button>
            <button id="qrCancelBtn"
                class="w-full h-10 text-gray-500 hover:text-gray-700 text-sm font-medium">
                Anulează
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
                <button id="closeAddProductsModal" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-5">

                {{-- Barcode scan section --}}
                <div>
                    <label for="barcodeInput" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-barcode mr-1"></i> Scanează cod de bare
                    </label>
                    <input type="text" id="barcodeInput" autocomplete="off"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Scanează sau tastează codul...">
                    <p id="barcodeError" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                {{-- Manual select --}}
                <div class="space-y-4">
                    <div>
                        <label for="productsSelect" class="block text-sm font-medium text-gray-700 mb-2">
                            Produs <span class="text-red-500">*</span>
                        </label>
                        <select id="productsSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Selectează produs...</option>
                        </select>
                    </div>
                    <div>
                        <label for="productQuantity" class="block text-sm font-medium text-gray-700 mb-2">
                            Cantitate (bucăți) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="productQuantity" min="1" value="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" id="cancelAddProducts" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Anulează</button>
                    <button type="button" id="saveAddProducts" class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                        <i class="fas fa-plus mr-2"></i>Adaugă
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ============================================================
// CONFIG
// ============================================================
const BRACELET_MODE = @json($braceletRequired);

// ============================================================
// STATE
// ============================================================
let currentBracelet = null;       // { code: '...' } — bracelet mode only
let currentChild = null;          // { id, name, guardian_name, guardian_phone }
let currentChildId = null;
let currentSession = null;
let currentSessionId = null;
let timerInterval = null;
let isProcessing = false;
let sessionProducts = [];
let guardianChoices = null;
let guardianSearchTimeout = null;
let selectedChildHasActiveSession = false;
let childChoices = null;          // bracelet mode only — Choices.js for child dropdown
let childSearchTimeout = null;
let assignmentInitialized = false;
let preCheckinTokenValue = null;

// Pre-checkin QR lookup
document.addEventListener('DOMContentLoaded', function() {
    const qrInput = document.getElementById('preCheckinQrInput');
    const qrResult = document.getElementById('preCheckinResult');
    const qrError = document.getElementById('preCheckinError');
    if (!qrInput) return;

    qrInput.addEventListener('change', async function() {
        const token = this.value.trim();
        qrResult.classList.add('hidden');
        qrError.classList.add('hidden');
        preCheckinTokenValue = null;
        if (!token) return;

        try {
            const res = await fetch(`/scan-api/pre-checkin/${encodeURIComponent(token)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const data = await res.json();
            if (data.success) {
                preCheckinTokenValue = token;
                const confirmed = await showQrConfirmModal(data);
                if (confirmed) {
                    qrResult.textContent = `✓ ${data.child.name} — ${data.guardian.name} (${data.guardian.phone})`;
                    qrResult.classList.remove('hidden');
                    if (childChoices && data.child.id) {
                        let label = data.child.name;
                        if (data.guardian && data.guardian.name) label += ` - ${data.guardian.name}`;
                        if (data.guardian && data.guardian.phone) label += ` (${data.guardian.phone})`;
                        childChoices.clearStore();
                        childChoices.setChoices(
                            [{ value: String(data.child.id), label, selected: true }],
                            'value', 'label', true
                        );
                        await updateAssignButtonState();
                    }
                    const assignBtn = document.getElementById('assignChildBtn');
                    if (assignBtn && !assignBtn.disabled) {
                        assignBtn.click();
                    }
                } else {
                    qrInput.value = '';
                    preCheckinTokenValue = null;
                }
            } else {
                qrError.textContent = data.message || 'Cod invalid';
                qrError.classList.remove('hidden');
                qrInput.value = '';
            }
        } catch(e) {
            qrError.textContent = 'Eroare la verificarea codului';
            qrError.classList.remove('hidden');
        }
    });
});

function showQrConfirmModal(data) {
    return new Promise((resolve) => {
        const modal = document.getElementById('qrConfirmModal');
        const confirmBtn = document.getElementById('qrConfirmBtn');
        const cancelBtn = document.getElementById('qrCancelBtn');

        document.getElementById('qrConfirmChildName').textContent = data.child.name;
        document.getElementById('qrConfirmGuardianInfo').textContent =
            `${data.guardian.name} · ${data.guardian.phone}`;

        modal.classList.remove('hidden');
        setTimeout(() => confirmBtn.focus(), 50);

        function handleConfirm() { close(true); }
        function handleCancel() { close(false); }
        function handleBackdrop(e) { if (e.target === modal) close(false); }
        function handleEscape(e) { if (e.key === 'Escape') close(false); }

        function close(result) {
            modal.classList.add('hidden');
            confirmBtn.removeEventListener('click', handleConfirm);
            cancelBtn.removeEventListener('click', handleCancel);
            modal.removeEventListener('click', handleBackdrop);
            document.removeEventListener('keydown', handleEscape);
            resolve(result);
        }

        confirmBtn.addEventListener('click', handleConfirm);
        cancelBtn.addEventListener('click', handleCancel);
        modal.addEventListener('click', handleBackdrop);
        document.addEventListener('keydown', handleEscape);
    });
}

// ============================================================
// HELPERS
// ============================================================

function getBraceletCode() {
    return currentBracelet ? currentBracelet.code : null;
}

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
        err.data = {
            message: response.status === 404
                ? 'Endpoint-ul nu a fost găsit.'
                : response.status === 500
                ? 'Eroare internă a serverului.'
                : `Eroare HTTP ${response.status}`
        };
        err.status = response.status;
        throw err;
    }

    if (!response.ok) {
        if (response.status === 419) {
            const err = new Error('Token CSRF expirat.');
            err.data = { message: 'Token CSRF expirat. Te rugăm să reîncarci pagina.', csrf_mismatch: true };
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
    return formatTime(seconds);
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

function extractErrorMessage(e, fallback) {
    if (e.data && e.data.message) return e.data.message;
    if (e.message) return e.message;
    return fallback || 'Eroare necunoscută';
}

// ============================================================
// TIMER
// ============================================================

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

// ============================================================
// RENDER ACTIVE SESSION
// ============================================================

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

    if (BRACELET_MODE) {
        const braceletCodeEl = document.getElementById('sessionBraceletCode');
        if (braceletCodeEl) {
            braceletCodeEl.textContent = data.bracelet_code || data.active_session?.bracelet_code || '-';
        }
    }

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

    if (currentSessionId) initializeProductsForSession(currentSessionId);
    section.classList.remove('hidden');
}

// ============================================================
// BRACELET MODE: renderBraceletInfo
// ============================================================

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
        return;
    }

    const braceletCode = data.bracelet_code || (data.bracelet && data.bracelet.code);
    currentBracelet = braceletCode ? { code: braceletCode } : null;

    if (data.active_session) {
        card.classList.add('hidden');
        assignmentSection.classList.add('hidden');
        renderActiveSession(data);
        return;
    }

    card.classList.add('hidden');
    renderActiveSession({ active_session: null });

    if (data.can_assign) {
        assignmentSection.classList.remove('hidden');
        const badge = document.getElementById('assignmentBraceletBadge');
        const codeEl = document.getElementById('assignmentBraceletCode');
        if (badge && codeEl && braceletCode) {
            codeEl.textContent = braceletCode;
            badge.classList.remove('hidden');
        }
        updateAssignButtonState();
    } else {
        assignmentSection.classList.add('hidden');
    }
}

// ============================================================
// NAME MODE: showAssignPanelForChild
// ============================================================

function showAssignPanelForChild(child) {
    currentChild = child;
    currentChildId = child.id;

    const nameEl = document.getElementById('selectedChildName');
    const guardianEl = document.getElementById('selectedChildGuardian');
    if (nameEl) nameEl.textContent = child.name;
    if (guardianEl) {
        const info = child.guardian_name
            ? (child.guardian_phone ? `${child.guardian_name} (${child.guardian_phone})` : child.guardian_name)
            : '';
        guardianEl.textContent = info;
    }

    switchTab('assign');
    document.getElementById('stateCard').classList.add('hidden');
    document.getElementById('activeSessionSection').classList.add('hidden');
    document.getElementById('assignmentSection').classList.remove('hidden');
    document.getElementById('assignChildBtn').disabled = false;
}

// ============================================================
// TAB SWITCHING & ASSIGNMENT SECTION
// ============================================================

const tabAssignExisting = document.getElementById('tabAssignExisting');
const tabCreateNew = document.getElementById('tabCreateNew');
const panelAssignExisting = document.getElementById('assignExistingPanel');
const panelCreateNew = document.getElementById('createNewPanel');
const assignmentSection = document.getElementById('assignmentSection');

function switchTab(which) {
    if (which === 'assign') {
        tabAssignExisting.setAttribute('aria-selected', 'true');
        tabAssignExisting.className = 'px-4 py-2 text-base font-semibold rounded-t-md bg-gray-100 text-gray-900';
        tabCreateNew.setAttribute('aria-selected', 'false');
        tabCreateNew.className = 'px-4 py-2 text-sm font-medium rounded-t-md text-gray-600 hover:text-gray-900';
        panelAssignExisting.classList.remove('hidden');
        panelCreateNew.classList.add('hidden');
        if (BRACELET_MODE) {
            initBraceletChildChoices();
        }
    } else {
        tabAssignExisting.setAttribute('aria-selected', 'false');
        tabAssignExisting.className = 'px-4 py-2 text-sm font-medium rounded-t-md text-gray-600 hover:text-gray-900';
        tabCreateNew.setAttribute('aria-selected', 'true');
        tabCreateNew.className = 'px-4 py-2 text-base font-semibold rounded-t-md bg-gray-100 text-gray-900';
        panelAssignExisting.classList.add('hidden');
        panelCreateNew.classList.remove('hidden');
        initGuardianChoices();
    }
}

tabAssignExisting.addEventListener('click', () => switchTab('assign'));
tabCreateNew.addEventListener('click', () => {
    switchTab('create');
    resetCreateForm();
});

// Lazy-init assignment section on first show (bracelet mode)
if (BRACELET_MODE) {
    const assignObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class' && !assignmentSection.classList.contains('hidden') && !assignmentInitialized) {
                switchTab('assign');
                switchGuardianMode('existing');
                assignmentInitialized = true;
            }
        });
    });
    assignObserver.observe(assignmentSection, { attributes: true });
}

// ============================================================
// GUARDIAN MODE SWITCHING
// ============================================================

const radioExistingGuardian = document.getElementById('radioExistingGuardian');
const radioNewGuardian = document.getElementById('radioNewGuardian');
const existingGuardianPanel = document.getElementById('existingGuardianPanel');
const newGuardianPanel = document.getElementById('newGuardianPanel');
const childSection = document.getElementById('childSection');
const termsAcceptanceSection = document.getElementById('termsAcceptanceSection');

function switchGuardianMode(mode) {
    if (mode === 'existing') {
        existingGuardianPanel.classList.remove('hidden');
        newGuardianPanel.classList.add('hidden');
        if (termsAcceptanceSection) termsAcceptanceSection.classList.add('hidden');
        document.getElementById('guardianName').value = '';
        document.getElementById('guardianPhone').value = '';
        document.getElementById('terms_accepted').checked = false;
        document.getElementById('gdpr_accepted').checked = false;
    } else {
        existingGuardianPanel.classList.add('hidden');
        newGuardianPanel.classList.remove('hidden');
        if (termsAcceptanceSection) termsAcceptanceSection.classList.remove('hidden');
        const guardianSelectEl = document.getElementById('guardianSelect');
        if (guardianSelectEl) {
            guardianSelectEl.value = '';
            if (guardianChoices) {
                guardianChoices.clearStore();
                guardianChoices.setChoices([{ value: '', label: 'Caută și selectează părinte...', selected: true }], 'value', 'label', true);
            }
        }
    }
    checkAndShowChildSection();
}

function checkAndShowChildSection() {
    const isExistingMode = radioExistingGuardian.checked;
    if (isExistingMode) {
        const guardianSelectEl = document.getElementById('guardianSelect');
        if (guardianSelectEl && guardianSelectEl.value) {
            childSection.classList.remove('hidden');
            if (termsAcceptanceSection) termsAcceptanceSection.classList.add('hidden');
        } else {
            childSection.classList.add('hidden');
        }
    } else {
        const guardianName = document.getElementById('guardianName').value.trim();
        const guardianPhone = document.getElementById('guardianPhone').value.trim();
        if (guardianName && guardianPhone) {
            childSection.classList.remove('hidden');
            if (termsAcceptanceSection) termsAcceptanceSection.classList.remove('hidden');
        } else {
            childSection.classList.add('hidden');
            if (termsAcceptanceSection) termsAcceptanceSection.classList.add('hidden');
        }
    }
}

radioExistingGuardian.addEventListener('change', function() { if (this.checked) switchGuardianMode('existing'); });
radioNewGuardian.addEventListener('change', function() { if (this.checked) switchGuardianMode('new'); });

const guardianNameInput = document.getElementById('guardianName');
const guardianPhoneInput = document.getElementById('guardianPhone');
const childFullNameInput = document.getElementById('childFullName');

if (guardianNameInput) {
    guardianNameInput.addEventListener('input', function(e) {
        const pos = e.target.selectionStart;
        e.target.value = e.target.value.toUpperCase();
        e.target.setSelectionRange(pos, pos);
        checkAndShowChildSection();
    });
}
if (guardianPhoneInput) guardianPhoneInput.addEventListener('input', checkAndShowChildSection);
if (childFullNameInput) {
    childFullNameInput.addEventListener('input', function(e) {
        const pos = e.target.selectionStart;
        e.target.value = e.target.value.toUpperCase();
        e.target.setSelectionRange(pos, pos);
    });
}

function resetCreateForm() {
    radioExistingGuardian.checked = true;
    radioNewGuardian.checked = false;
    existingGuardianPanel.classList.remove('hidden');
    newGuardianPanel.classList.add('hidden');
    childSection.classList.add('hidden');
    if (termsAcceptanceSection) termsAcceptanceSection.classList.add('hidden');
    document.getElementById('guardianName').value = '';
    document.getElementById('guardianPhone').value = '';
    document.getElementById('childFullName').value = '';
    document.getElementById('terms_accepted').checked = false;
    document.getElementById('gdpr_accepted').checked = false;
    const birthdayCreate = document.getElementById('sessionTypeBirthdayCreate');
    if (birthdayCreate) birthdayCreate.checked = false;
    if (guardianChoices) {
        guardianChoices.clearChoices();
        guardianChoices.setChoices([], 'value', 'label', true);
        guardianChoices.clearInput();
    }
}

// ============================================================
// CHOICES.JS INITIALIZATION
// ============================================================

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

        // Server-side search
        if (!window._guardianSearchHandler) {
            document.addEventListener('input', function(event) {
                const target = event.target;
                if (target && target.classList.contains('choices__input') && guardianChoices?.containerOuter?.element?.contains(target)) {
                    const q = target.value || '';
                    clearTimeout(guardianSearchTimeout);
                    guardianSearchTimeout = setTimeout(() => loadGuardians(q), 300);
                }
            });
            window._guardianSearchHandler = true;
        }

        guardianSelect.addEventListener('change', async function() {
            checkAndShowChildSection();
            const guardianId = this.value;
            if (guardianId) {
                const termsResult = await checkGuardianTerms(guardianId);
                if (!termsResult.accepted) {
                    const accepted = await showTermsAcceptanceModal();
                    if (accepted) {
                        const saved = await saveGuardianTermsAcceptance(guardianId);
                        if (!saved) {
                            alert('Eroare la salvarea acceptării termenilor.');
                            clearGuardianSelection();
                        }
                    } else {
                        clearGuardianSelection();
                    }
                }
            }
        });
    }
}

function clearGuardianSelection() {
    if (guardianChoices) {
        guardianChoices.clearStore();
        guardianChoices.setChoices([{ value: '', label: 'Caută și selectează părinte...', selected: true }], 'value', 'label', true);
    }
    childSection.classList.add('hidden');
    document.getElementById('childFullName').value = '';
}

function initBraceletChildChoices() {
    const childSelect = document.getElementById('childSelect');
    if (!childSelect || childChoices) return;

    childChoices = new Choices(childSelect, {
        searchEnabled: true,
        searchPlaceholderValue: 'Scrie pentru a căuta...',
        noResultsText: 'Niciun copil găsit',
        noChoicesText: 'Nu există opțiuni',
        itemSelectText: 'Click pentru a selecta',
        loadingText: 'Se încarcă...',
        shouldSort: false,
        searchChoices: false,
        searchResultLimit: 1000
    });

    childChoices.passedElement.element.addEventListener('addItem', () => updateAssignButtonState());
    childChoices.passedElement.element.addEventListener('removeItem', () => updateAssignButtonState());

    if (!window._childSearchHandler) {
        document.addEventListener('input', function(event) {
            const target = event.target;
            if (target && target.classList.contains('choices__input') && childChoices?.containerOuter?.element?.contains(target)) {
                const q = target.value || '';
                clearTimeout(childSearchTimeout);
                childSearchTimeout = setTimeout(() => loadChildren(q), 300);
            }
        });
        window._childSearchHandler = true;
    }

    loadChildren();
}

async function loadChildren(searchQuery) {
    try {
        const url = new URL('/children-search', window.location.origin);
        if (searchQuery) url.searchParams.set('q', searchQuery);
        url.searchParams.set('exclude_active_sessions', '1');
        const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
        const data = await res.json();
        if (childChoices && data.success && data.children) {
            const choices = data.children.map(child => {
                let label = child.name;
                if (child.guardian_name) label += ` - ${child.guardian_name}`;
                if (child.guardian_phone) label += ` (${child.guardian_phone})`;
                return { value: child.id, label, selected: false };
            });
            childChoices.clearStore();
            childChoices.setChoices(choices, 'value', 'label', true);
        }
    } catch (e) { console.error('Error loading children:', e); }
}

async function loadGuardians(searchQuery) {
    try {
        const url = new URL('/guardians-search', window.location.origin);
        if (searchQuery) url.searchParams.set('q', searchQuery);
        url.searchParams.set('limit', '15');
        const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
        const data = await res.json();
        if (guardianChoices && data.success && data.guardians) {
            const choices = data.guardians.map(g => ({
                value: String(g.id),
                label: `${g.name}${g.phone ? ' · ' + g.phone : ''}`,
            }));
            guardianChoices.clearChoices();
            guardianChoices.setChoices(choices, 'value', 'label', false);
        }
    } catch (e) { console.error('Error loading guardians:', e); }
}

// ============================================================
// ASSIGN BUTTON STATE (bracelet mode)
// ============================================================

async function updateAssignButtonState() {
    const assignBtn = document.getElementById('assignChildBtn');
    const statusMessage = document.getElementById('childSelectionStatus');
    if (!assignBtn) return;

    if (BRACELET_MODE) {
        let childId = null;
        if (childChoices) {
            const value = childChoices.getValue(true);
            childId = Array.isArray(value) ? value[0] : value;
        }
        if (statusMessage) { statusMessage.textContent = ''; statusMessage.classList.add('hidden'); }
        if (!childId || !currentBracelet) {
            assignBtn.disabled = true;
            selectedChildHasActiveSession = false;
            return;
        }
        try {
            const result = await apiCall(`/scan-api/child-session/${childId}`);
            if (result.success && result.active_session) {
                selectedChildHasActiveSession = true;
                assignBtn.disabled = true;
                if (statusMessage) {
                    const sessionStart = new Date(result.active_session.started_at).toLocaleString('ro-RO');
                    statusMessage.textContent = `⚠️ Acest copil are deja o sesiune activă începută la ${sessionStart}.`;
                    statusMessage.classList.remove('hidden');
                }
                return;
            }
        } catch (e) { /* 404 = no session, ok */ }
        selectedChildHasActiveSession = false;
        assignBtn.disabled = false;
    }
    // Name mode: button state managed by showAssignPanelForChild
}

// ============================================================
// ASSIGN EXISTING CHILD
// ============================================================

document.getElementById('assignChildBtn').addEventListener('click', async function() {
    if (isProcessing) return;

    let childId = null;
    if (BRACELET_MODE) {
        if (childChoices) {
            const value = childChoices.getValue(true);
            childId = Array.isArray(value) ? value[0] : value;
        }
        if (!childId) { alert('Te rog selectează un copil'); return; }
        if (!currentBracelet) { alert('Nu există brățară scanată'); return; }
        if (selectedChildHasActiveSession) { alert('Acest copil are deja o sesiune activă.'); return; }
    } else {
        if (!currentChild) return;
        childId = currentChild.id;
    }

    isProcessing = true;
    this.disabled = true;
    const orig = this.innerHTML;
    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Se pornește...';

    try {
        const sessionType = document.getElementById('sessionTypeBirthdayAssign')?.checked ? 'birthday' : 'normal';
        const preCheckinToken = document.getElementById('preCheckinQrInput')?.value.trim() || null;
        const result = await apiCall('/scan-api/assign', {
            method: 'POST',
            body: JSON.stringify({ child_id: childId, bracelet_code: getBraceletCode(), session_type: sessionType, pre_checkin_token: preCheckinToken })
        });

        if (result.success) {
            const birthdayToggle = document.getElementById('sessionTypeBirthdayAssign');
            if (birthdayToggle) birthdayToggle.checked = false;

            if (BRACELET_MODE) {
                if (childChoices) {
                    childChoices.clearStore();
                    childChoices.setChoices([{ value: '', label: 'Caută și selectează copil...', selected: true }], 'value', 'label', true);
                }
                const data = await apiCall('/scan-api/lookup', { method: 'POST', body: JSON.stringify({ code: currentBracelet.code }) });
                renderBraceletInfo(data);
                setTimeout(() => clearInputForNextScan(), 300);
            } else {
                const sessionData = await apiCall(`/scan-api/child-session/${currentChild.id}`);
                document.getElementById('assignmentSection').classList.add('hidden');
                renderActiveSession({ child: currentChild, active_session: sessionData.active_session });
            }
            loadRecentCompleted();
            loadActiveSessionsInfo();
        } else {
            alert('Eroare: ' + (result.message || 'Nu s-a putut porni sesiunea'));
            this.innerHTML = orig;
        }
    } catch (e) {
        alert('Eroare: ' + extractErrorMessage(e, 'Eroare la pornirea sesiunii'));
        this.innerHTML = orig;
    } finally {
        isProcessing = false;
        this.disabled = false;
        this.innerHTML = orig;
        if (BRACELET_MODE) await updateAssignButtonState();
    }
});

// ============================================================
// CREATE NEW CHILD
// ============================================================

document.getElementById('createAndAssignBtn').addEventListener('click', async function() {
    if (isProcessing) return;

    const guardianMode = radioNewGuardian.checked ? 'new' : 'existing';
    const childName = document.getElementById('childFullName').value.trim();
    if (!childName) { alert('Completează numele copilului.'); return; }

    const sessionType = document.getElementById('sessionTypeBirthdayCreate')?.checked ? 'birthday' : 'normal';
    let payload = { first_name: childName, bracelet_code: getBraceletCode(), session_type: sessionType };

    if (guardianMode === 'existing') {
        const guardianSelectEl = document.getElementById('guardianSelect');
        const guardianId = guardianSelectEl ? guardianSelectEl.value : null;
        if (!guardianId) { alert('Selectează un părinte sau alege "Nou" pentru a crea unul.'); return; }
        payload.guardian_id = parseInt(guardianId);
    } else {
        const gName = document.getElementById('guardianName').value.trim();
        const gPhone = document.getElementById('guardianPhone').value.trim();
        if (!gName || !gPhone) { alert('Completează numele și telefonul părintelui.'); return; }
        const termsAccepted = document.getElementById('terms_accepted').checked;
        const gdprAccepted = document.getElementById('gdpr_accepted').checked;
        if (!termsAccepted || !gdprAccepted) { alert('Trebuie să acceptați termenii și condițiile și politica GDPR.'); return; }
        payload.guardian_name = gName;
        payload.guardian_phone = gPhone;
        payload.terms_accepted = true;
        payload.gdpr_accepted = true;
    }

    if (BRACELET_MODE && !currentBracelet) { alert('Nu există brățară scanată'); return; }

    isProcessing = true;
    this.disabled = true;
    const orig = this.innerHTML;
    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Se creează...';

    try {
        const result = await apiCall('/scan-api/create-child', { method: 'POST', body: JSON.stringify(payload) });

        if (result.success) {
            resetCreateForm();

            if (BRACELET_MODE) {
                const data = await apiCall('/scan-api/lookup', { method: 'POST', body: JSON.stringify({ code: currentBracelet.code }) });
                renderBraceletInfo(data);
                setTimeout(() => clearInputForNextScan(), 300);
            } else {
                const childData = result.data;
                currentChild = { id: childData.child.id, name: childData.child.name };
                currentChildId = childData.child.id;
                const sessionData = await apiCall(`/scan-api/child-session/${currentChild.id}`);
                document.getElementById('assignmentSection').classList.add('hidden');
                renderActiveSession({ child: currentChild, active_session: sessionData.active_session });
            }
            loadRecentCompleted();
            loadActiveSessionsInfo();
        } else {
            alert('Eroare: ' + (result.message || 'Nu s-a putut crea'));
            this.innerHTML = orig;
        }
    } catch (e) {
        alert('Eroare: ' + extractErrorMessage(e, 'Eroare la creare'));
        this.innerHTML = orig;
    } finally {
        isProcessing = false;
        this.disabled = false;
    }
});

// ============================================================
// SESSION CONTROLS: PAUSE / RESUME
// ============================================================

document.getElementById('pauseResumeBtn').addEventListener('click', async function() {
    if (!currentSession || isProcessing) return;

    const isPaused = currentSession.is_paused;
    const action = isPaused ? 'resume' : 'pause';

    isProcessing = true;
    this.disabled = true;
    const orig = this.innerHTML;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Se procesează...</span>';

    try {
        const result = await apiCall(`/scan-api/${action}-session/${currentSession.id}`, { method: 'POST' });

        if (result.success) {
            if (BRACELET_MODE && currentBracelet) {
                const data = await apiCall('/scan-api/lookup', { method: 'POST', body: JSON.stringify({ code: currentBracelet.code }) });
                renderBraceletInfo(data);
                setTimeout(() => { const input = document.getElementById('rfidCode'); if (input) input.focus(); }, 200);
            } else if (currentChildId) {
                const sessionData = await apiCall(`/scan-api/child-session/${currentChildId}`);
                renderActiveSession({ child: currentChild, active_session: sessionData.active_session });
            }
            loadActiveSessionsInfo();
        } else {
            alert('Eroare: ' + (result.message || `Nu s-a putut ${isPaused ? 'relua' : 'pune pe pauză'} sesiunea`));
            this.innerHTML = orig;
        }
    } catch (e) {
        alert('Eroare: ' + extractErrorMessage(e, `Eroare la ${isPaused ? 'reluare' : 'pauză'}`));
        this.innerHTML = orig;
    } finally {
        isProcessing = false;
        this.disabled = false;
    }
});

// ============================================================
// SESSION CONTROLS: STOP
// ============================================================

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
            body: JSON.stringify({ bracelet_code: getBraceletCode() })
        });

        if (result.success) {
            this.innerHTML = orig;
            renderActiveSession({ active_session: null });
            currentSession = null;
            currentSessionId = null;
            sessionProducts = [];
            renderProductsList();

            const card = document.getElementById('stateCard');
            document.getElementById('assignmentSection').classList.add('hidden');
            card.classList.remove('hidden');

            if (BRACELET_MODE) {
                currentBracelet = null;
                clearInputForNextScan();
                card.innerHTML = '<div class="text-gray-500">Sesiunea a fost oprită cu succes. Introduceți sau scanați un cod pentru a începe.</div>';
            } else {
                currentChild = null;
                currentChildId = null;
                card.innerHTML = '<div class="text-gray-500">Sesiunea a fost oprită cu succes. Căutați un copil pentru a porni o nouă sesiune.</div>';
            }

            loadRecentCompleted();
            loadActiveSessionsInfo();
        } else {
            alert('Eroare: ' + (result.message || 'Nu s-a putut opri sesiunea'));
            this.innerHTML = orig;
        }
    } catch (e) {
        alert('Eroare: ' + extractErrorMessage(e, 'Eroare la oprirea sesiunii'));
        this.innerHTML = orig;
    } finally {
        isProcessing = false;
        this.disabled = false;
    }
});

// ============================================================
// TERMS ACCEPTANCE
// ============================================================

async function checkGuardianTerms(guardianId) {
    try {
        const result = await apiCall('/scan-api/check-guardian-terms', {
            method: 'POST',
            body: JSON.stringify({ guardian_id: guardianId })
        });
        return result.success ? { accepted: result.accepted } : { accepted: false };
    } catch (e) {
        console.error('Error checking guardian terms:', e);
        return { accepted: false };
    }
}

function showTermsAcceptanceModal() {
    return new Promise((resolve) => {
        const modal = document.getElementById('termsAcceptanceModal');
        const acceptBtn = document.getElementById('acceptTermsModalBtn');
        const cancelBtn = document.getElementById('cancelTermsModalBtn');
        const termsCheckbox = document.getElementById('modal_terms_accepted');
        const gdprCheckbox = document.getElementById('modal_gdpr_accepted');

        termsCheckbox.checked = false;
        gdprCheckbox.checked = false;
        modal.classList.remove('hidden');

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

async function saveGuardianTermsAcceptance(guardianId) {
    try {
        const result = await apiCall('/scan-api/accept-guardian-terms', {
            method: 'POST',
            body: JSON.stringify({ guardian_id: guardianId })
        });
        return result.success;
    } catch (e) {
        console.error('Error saving guardian terms:', e);
        return false;
    }
}

// ============================================================
// PRODUCTS
// ============================================================

let availableProducts = [];

async function loadAvailableProducts() {
    try {
        const result = await apiCall('/scan-api/available-products');
        if (result.success && result.products) availableProducts = result.products;
    } catch (e) { console.error('Error loading products:', e); }
}

async function initializeProductsForSession(sessionId) {
    currentSessionId = sessionId;
    sessionProducts = [];
    try {
        const result = await apiCall(`/scan-api/session-products/${sessionId}`);
        if (result.success) sessionProducts = result.products || [];
        renderProductsList();
    } catch (e) {
        console.error('Error loading session products:', e);
        renderProductsList();
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

function openAddProductsModal() {
    if (!currentSessionId) { alert('Nu există o sesiune activă'); return; }
    const modal = document.getElementById('addProductsModal');
    const productsSelect = document.getElementById('productsSelect');
    if (productsSelect && availableProducts.length > 0) {
        productsSelect.innerHTML = '<option value="">Selectează produs...</option>' +
            availableProducts.map(p => `<option value="${p.id}">${p.name} - ${parseFloat(p.price).toFixed(2)} RON</option>`).join('');
    }
    document.getElementById('productQuantity').value = '1';
    if (productsSelect) productsSelect.value = '';
    resetBarcodeState();
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    setTimeout(() => document.getElementById('barcodeInput')?.focus(), 100);
}

function closeProductsModal() {
    const modal = document.getElementById('addProductsModal');
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    resetBarcodeState();
}

function resetBarcodeState() {
    const inp = document.getElementById('barcodeInput');
    const err = document.getElementById('barcodeError');
    if (inp) inp.value = '';
    if (err) { err.textContent = ''; err.classList.add('hidden'); }
}

async function lookupBarcode(barcode) {
    const err = document.getElementById('barcodeError');
    err.classList.add('hidden');

    try {
        const result = await apiCall(`/scan-api/product-by-barcode?barcode=${encodeURIComponent(barcode)}`);
        if (result.success && result.product) {
            const productsSelect = document.getElementById('productsSelect');
            const product = result.product;

            let option = productsSelect.querySelector(`option[value="${product.id}"]`);
            if (!option) {
                option = document.createElement('option');
                option.value = product.id;
                option.dataset.price = product.price;
                let label = `${product.name} - ${parseFloat(product.price).toFixed(2)} RON`;
                if (product.has_sgr) {
                    label += ` + ${parseFloat(product.sgr_value).toFixed(2)} RON SGR`;
                }
                option.textContent = label;
                productsSelect.appendChild(option);
            }

            productsSelect.value = product.id;
            document.getElementById('barcodeInput').value = '';
            document.getElementById('productQuantity').focus();
        } else {
            err.textContent = 'Produsul nu a fost găsit pentru acest cod de bare.';
            err.classList.remove('hidden');
        }
    } catch (e) {
        err.textContent = 'Produsul nu a fost găsit pentru acest cod de bare.';
        err.classList.remove('hidden');
    }
}

async function addProductToSession() {
    const productId = document.getElementById('productsSelect').value;
    const quantity = parseInt(document.getElementById('productQuantity').value);
    if (!productId || quantity < 1) { alert('Te rog selectează un produs și introdu o cantitate validă'); return; }
    try {
        const result = await apiCall('/scan-api/add-products', {
            method: 'POST',
            body: JSON.stringify({ session_id: currentSessionId, products: [{ product_id: parseInt(productId), quantity }] })
        });
        if (result.success) {
            closeProductsModal();
            await initializeProductsForSession(currentSessionId);
        } else {
            alert('Eroare: ' + (result.message || 'Nu s-au putut adăuga produsele'));
        }
    } catch (e) { alert('Eroare: ' + extractErrorMessage(e, 'Eroare la adăugarea produselor')); }
}

document.getElementById('addProductsBtn')?.addEventListener('click', openAddProductsModal);
document.getElementById('closeAddProductsModal')?.addEventListener('click', closeProductsModal);
document.getElementById('cancelAddProducts')?.addEventListener('click', closeProductsModal);
document.getElementById('saveAddProducts')?.addEventListener('click', addProductToSession);
document.getElementById('addProductsOverlay')?.addEventListener('click', closeProductsModal);

document.getElementById('barcodeInput')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const barcode = this.value.trim();
        if (barcode) lookupBarcode(barcode);
    }
});


// ============================================================
// RECENT COMPLETED SESSIONS
// ============================================================

async function loadRecentCompleted() {
    try {
        const res = await apiCall('/scan-api/recent-completed');
        if (!res.success) return;
        const list = res.recent || [];
        const container = document.getElementById('recentCompletedList');
        const section = document.getElementById('recentCompletedSection');
        if (list.length === 0) { section.classList.add('hidden'); container.innerHTML = ''; return; }
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
    } catch (e) { console.error('Failed to load recent completed sessions', e); }
}

// ============================================================
// ACTIVE SESSIONS INFO
// ============================================================

let activeSessionsInterval = null;

async function loadActiveSessionsInfo() {
    try {
        const result = await apiCall('/scan-api/active-sessions');
        if (result.success && result.sessions) {
            document.getElementById('activeSessionsCount').textContent = result.sessions.filter(s => !s.is_paused).length;
            document.getElementById('pausedSessionsCount').textContent = result.sessions.filter(s => s.is_paused).length;
            const now = new Date();
            document.getElementById('activeSessionsLastUpdate').textContent = `Actualizat: ${now.toLocaleTimeString('ro-RO', { hour: '2-digit', minute: '2-digit' })}`;
        }
    } catch (e) { console.error('Error loading active sessions info:', e); }
}

activeSessionsInterval = setInterval(loadActiveSessionsInfo, 10000);

// ============================================================
// BRACELET MODE: RFID INPUT HANDLING
// ============================================================

if (BRACELET_MODE) {
    const codeInput = document.getElementById('rfidCode');
    const searchBtn = document.getElementById('searchBtn');
    const childrenSearchResults = document.getElementById('childrenSearchResults');
    const childrenSearchResultsList = document.getElementById('childrenSearchResultsList');
    let barcodeScanTimeout;
    let lastInputTime = 0;
    let inputLengthBefore = 0;
    let rapidInputStartLength = 0;

    window.clearInputForNextScan = function() {
        codeInput.value = '';
        updateValidationFeedback('');
        codeInput.dispatchEvent(new Event('input'));
        codeInput.focus();
        // Reset QR pre-checkin input
        const qrEl = document.getElementById('preCheckinQrInput');
        const qrResult = document.getElementById('preCheckinResult');
        const qrError = document.getElementById('preCheckinError');
        if (qrEl) qrEl.value = '';
        if (qrResult) qrResult.classList.add('hidden');
        if (qrError) qrError.classList.add('hidden');
        preCheckinTokenValue = null;
    };

    function isValidBraceletCode(code) {
        if (!code || typeof code !== 'string') return false;
        const trimmed = code.trim();
        return trimmed.length >= 9 && trimmed.length <= 50 && /^[A-Za-z0-9]+$/.test(trimmed);
    }

    function updateValidationFeedback(code) {
        const icon = document.getElementById('rfidCodeIcon');
        const errorDiv = document.getElementById('rfidCodeError');
        if (!code || code.length === 0) {
            icon.classList.add('hidden');
            errorDiv.classList.add('hidden');
            codeInput.classList.remove('border-green-500', 'border-red-500', 'focus:ring-green-500/20', 'focus:ring-red-500/20');
            codeInput.classList.add('border-gray-300', 'focus:ring-gray-900/20');
            return;
        }
        icon.classList.remove('hidden');
        errorDiv.classList.add('hidden');
        codeInput.classList.remove('border-gray-300', 'border-red-500', 'focus:ring-gray-900/20', 'focus:ring-red-500/20');
        codeInput.classList.add('border-green-500', 'focus:ring-green-500/20');
    }

    codeInput.addEventListener('focus', function() {
        this.classList.remove('border-amber-400', 'focus:ring-amber-400/20');
        updateValidationFeedback(this.value.trim());
        setTimeout(() => { if (codeInput.value.length > 0) codeInput.select(); }, 10);
    });

    codeInput.addEventListener('blur', function() {
        if (!isProcessing) {
            this.classList.remove('border-gray-300', 'border-green-500', 'border-red-500',
                'focus:ring-gray-900/20', 'focus:ring-green-500/20', 'focus:ring-red-500/20');
            this.classList.add('border-amber-400', 'focus:ring-amber-400/20');
        }
    });

    codeInput.addEventListener('input', function(e) {
        const now = Date.now();
        const currentLength = e.target.value.length;
        const timeSinceLastInput = now - lastInputTime;
        let fullValue = e.target.value;
        const isRapidInput = timeSinceLastInput < 50 && currentLength > inputLengthBefore;
        const isManualTyping = timeSinceLastInput > 100;

        if (!isManualTyping && isRapidInput) {
            if (timeSinceLastInput > 200 && currentLength > inputLengthBefore) rapidInputStartLength = inputLengthBefore;
            if (isRapidInput && rapidInputStartLength > 0 && currentLength > rapidInputStartLength + 1) {
                const newScan = fullValue.substring(rapidInputStartLength);
                e.target.value = newScan;
                inputLengthBefore = newScan.length;
                rapidInputStartLength = 0;
                fullValue = newScan;
            }
        }
        lastInputTime = now;
        inputLengthBefore = e.target.value.length;
        if (timeSinceLastInput > 500) rapidInputStartLength = 0;

        let filteredValue = fullValue.trim().substring(0, 50);
        if (filteredValue !== fullValue) { e.target.value = filteredValue; fullValue = filteredValue; }
        updateValidationFeedback(filteredValue);
        searchBtn.disabled = !isValidBraceletCode(filteredValue);

        // If a UUID is scanned while the assignment section is open, it's a pre-checkin QR —
        // redirect it to the QR input instead of treating it as a bracelet code.
        const qrInputEl = document.getElementById('preCheckinQrInput');
        const assignSection = document.getElementById('assignmentSection');
        if (qrInputEl && assignSection && !assignSection.classList.contains('hidden')) {
            const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
            if (uuidRegex.test(filteredValue)) {
                e.target.value = '';
                updateValidationFeedback('');
                searchBtn.disabled = true;
                qrInputEl.value = filteredValue;
                qrInputEl.dispatchEvent(new Event('change'));
                return;
            }
        }

        clearTimeout(barcodeScanTimeout);
        if (!isManualTyping && isValidBraceletCode(filteredValue) && filteredValue.length >= 9) {
            barcodeScanTimeout = setTimeout(() => {
                if (isValidBraceletCode(codeInput.value.trim())) searchBtn.click();
            }, 500);
        } else if (filteredValue.length > 0 && filteredValue.length < 5) {
            clearTimeout(childSearchTimeout);
            childSearchTimeout = setTimeout(() => searchChildrenWithSessions(filteredValue), 300);
        } else {
            hideChildrenSearchResults();
        }
    });

    codeInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text').trim();
        if (pastedText.length > 0) { codeInput.value = pastedText.substring(0, 50); codeInput.dispatchEvent(new Event('input')); }
    });

    codeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (isValidBraceletCode(codeInput.value.trim()) && !searchBtn.disabled) searchBtn.click();
            hideChildrenSearchResults();
        } else if (e.key === 'Escape') {
            codeInput.value = '';
            codeInput.dispatchEvent(new Event('input'));
            hideChildrenSearchResults();
        } else if (e.key === 'ArrowDown' && !childrenSearchResults.classList.contains('hidden')) {
            e.preventDefault();
            const first = childrenSearchResultsList.querySelector('.child-result-item');
            if (first) first.focus();
        }
    });

    searchBtn.addEventListener('click', async function() {
        const code = codeInput.value.trim();
        if (!code.length || !isValidBraceletCode(code) || isProcessing) return;
        isProcessing = true;
        clearTimeout(barcodeScanTimeout);
        codeInput.value = '';
        codeInput.disabled = true;
        updateValidationFeedback('');
        this.disabled = true;
        const prev = this.textContent;
        this.textContent = 'Se caută...';
        try {
            const data = await apiCall('/scan-api/lookup', { method: 'POST', body: JSON.stringify({ code }) });
            // Reset QR pre-checkin for the new bracelet scan
            const qrEl = document.getElementById('preCheckinQrInput');
            const qrResult = document.getElementById('preCheckinResult');
            const qrError = document.getElementById('preCheckinError');
            if (qrEl) qrEl.value = '';
            if (qrResult) qrResult.classList.add('hidden');
            if (qrError) qrError.classList.add('hidden');
            preCheckinTokenValue = null;
            renderBraceletInfo(data);
            loadRecentCompleted();
        } catch (err) {
            if (err.status === 419) {
                setTimeout(() => { if (confirm('Token CSRF expirat. Vrei să reîncarci pagina?')) window.location.reload(); }, 1000);
            }
            renderBraceletInfo({ success: false, message: extractErrorMessage(err, 'Eroare la căutare') });
        } finally {
            isProcessing = false;
            codeInput.disabled = false;
            this.disabled = false;
            this.textContent = prev;
            codeInput.focus();
        }
    });

    document.addEventListener('click', function(e) {
        if (!codeInput.contains(e.target) && !childrenSearchResults.contains(e.target)) hideChildrenSearchResults();
    });

    // Children with active sessions search (short bracelet code input)
    async function searchChildrenWithSessions(query) {
        if (!query) { hideChildrenSearchResults(); return; }
        try {
            const url = new URL('/scan-api/children-with-sessions', window.location.origin);
            url.searchParams.set('q', query);
            const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            if (data.success && data.children && data.children.length > 0) {
                displayChildrenSearchResults(data.children);
            } else {
                hideChildrenSearchResults();
            }
        } catch (e) { hideChildrenSearchResults(); }
    }

    function displayChildrenSearchResults(children) {
        childrenSearchResultsList.innerHTML = children.map(child => {
            let guardianInfo = child.guardian_name ? (child.guardian_phone ? `${child.guardian_name} (${child.guardian_phone})` : child.guardian_name) : '';
            let sessionInfo = '';
            if (child.session_started_at) {
                const startedAt = new Date(child.session_started_at).toLocaleString('ro-RO');
                const duration = child.session_duration_formatted || '00:00';
                const paused = child.session_is_paused ? ' ⏸ Pauză' : '';
                sessionInfo = `<div class="text-xs text-indigo-600 mt-1">Brățară: ${child.bracelet_code || '-'} • Start: ${startedAt} • Durată: ${duration}${paused}</div>`;
            }
            return `<div class="child-result-item px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0" data-child-id="${child.id}" tabindex="0">
                <div class="font-medium text-gray-900">${child.name}</div>
                ${guardianInfo ? `<div class="text-sm text-gray-500">${guardianInfo}</div>` : ''}${sessionInfo}
            </div>`;
        }).join('');

        childrenSearchResultsList.querySelectorAll('.child-result-item').forEach(item => {
            item.addEventListener('click', () => accessChildSession(item.getAttribute('data-child-id')));
            item.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') accessChildSession(this.getAttribute('data-child-id'));
                else if (e.key === 'ArrowDown') { e.preventDefault(); this.nextElementSibling?.focus(); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); (this.previousElementSibling || codeInput).focus(); }
            });
        });
        childrenSearchResults.classList.remove('hidden');
    }

    function hideChildrenSearchResults() {
        childrenSearchResults.classList.add('hidden');
    }

    async function accessChildSession(childId) {
        hideChildrenSearchResults();
        try {
            const result = await apiCall(`/scan-api/child-session/${childId}`);
            if (result.success && result.active_session) {
                const braceletCode = result.bracelet_code || (result.bracelet && result.bracelet.code);
                currentBracelet = braceletCode ? { code: braceletCode } : null;
                clearInputForNextScan();
                renderBraceletInfo({ success: true, bracelet_code: braceletCode, child: result.child, active_session: result.active_session });
            }
        } catch (e) { alert('Eroare: ' + extractErrorMessage(e, 'Eroare la accesarea sesiunii')); }
    }

    codeInput.focus();

    // Re-focus pe input dacă operatorul dă click pe o zonă neinteractivă
    document.addEventListener('click', function(e) {
        if (isProcessing) return;
        if (!e.target.closest('input, textarea, select, button, a, [tabindex], .choices, [role="dialog"]')) {
            setTimeout(() => codeInput.focus(), 50);
        }
    });
}

// ============================================================
// NAME MODE: CHILD SEARCH HANDLING
// ============================================================

if (!BRACELET_MODE) {
    const searchInput = document.getElementById('childSearchInput');
    const searchResults = document.getElementById('childSearchResults');
    const searchResultsList = document.getElementById('childSearchResultsList');
    let nameSearchTimeout = null;

    window.clearInputForNextScan = function() {};

    searchInput.addEventListener('input', function() {
        const q = this.value.trim();
        clearTimeout(nameSearchTimeout);
        if (q.length < 1) { hideNameSearch(); return; }
        nameSearchTimeout = setTimeout(() => performNameSearch(q), 300);
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') { hideNameSearch(); this.value = ''; }
        else if (e.key === 'ArrowDown') {
            e.preventDefault();
            const first = searchResultsList.querySelector('.cs-item');
            if (first) first.focus();
        }
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) hideNameSearch();
    });

    async function performNameSearch(q) {
        try {
            const url = new URL('/children-search', window.location.origin);
            url.searchParams.set('q', q);
            url.searchParams.set('limit', '15');
            const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            if (data.success && data.children && data.children.length > 0) {
                renderNameSearchResults(data.children);
            } else {
                searchResultsList.innerHTML = `<div class="px-4 py-3 text-sm text-gray-500 italic">Niciun copil găsit pentru "${q}"</div>`;
                searchResults.classList.remove('hidden');
            }
        } catch (e) { hideNameSearch(); }
    }

    function renderNameSearchResults(children) {
        searchResultsList.innerHTML = children.map(child => {
            const guardian = child.guardian_name ? (child.guardian_phone ? `${child.guardian_name} · ${child.guardian_phone}` : child.guardian_name) : '';
            return `<div class="cs-item px-4 py-3 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0 focus:outline-none focus:bg-gray-100"
                 tabindex="0" data-child-id="${child.id}" data-child-name="${child.name}"
                 data-guardian-name="${child.guardian_name || ''}" data-guardian-phone="${child.guardian_phone || ''}">
                <div class="font-medium text-gray-900">${child.name}</div>
                ${guardian ? `<div class="text-sm text-gray-500">${guardian}</div>` : ''}
            </div>`;
        }).join('');

        searchResultsList.querySelectorAll('.cs-item').forEach(item => {
            item.addEventListener('click', () => selectChildFromSearch(item));
            item.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') selectChildFromSearch(item);
                else if (e.key === 'ArrowDown') { e.preventDefault(); this.nextElementSibling?.focus(); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); (this.previousElementSibling || searchInput).focus(); }
            });
        });
        searchResults.classList.remove('hidden');
    }

    function hideNameSearch() {
        searchResults.classList.add('hidden');
        searchResultsList.innerHTML = '';
    }

    async function selectChildFromSearch(item) {
        hideNameSearch();
        const childId = item.getAttribute('data-child-id');
        const childName = item.getAttribute('data-child-name');
        const guardianName = item.getAttribute('data-guardian-name');
        const guardianPhone = item.getAttribute('data-guardian-phone');
        searchInput.value = '';

        try {
            const result = await apiCall(`/scan-api/child-session/${childId}`);
            if (result.success && result.active_session) {
                currentChild = result.child;
                currentChildId = parseInt(childId);
                document.getElementById('stateCard').classList.add('hidden');
                document.getElementById('assignmentSection').classList.add('hidden');
                renderActiveSession({ child: result.child, active_session: result.active_session });
            }
        } catch (e) {
            if (e.status === 404) {
                showAssignPanelForChild({ id: parseInt(childId), name: childName, guardian_name: guardianName, guardian_phone: guardianPhone });
            } else {
                showStateError(extractErrorMessage(e, 'Eroare la accesarea datelor copilului'));
            }
        }
    }

    // + Copil nou button
    document.getElementById('newChildBtn').addEventListener('click', function() {
        currentChild = null;
        currentChildId = null;
        document.getElementById('stateCard').classList.add('hidden');
        document.getElementById('activeSessionSection').classList.add('hidden');
        document.getElementById('assignmentSection').classList.remove('hidden');
        switchTab('create');
        resetCreateForm();
    });

    searchInput.focus();
}

// ============================================================
// INIT
// ============================================================

loadRecentCompleted();
loadActiveSessionsInfo();
loadAvailableProducts();

</script>
@endsection
