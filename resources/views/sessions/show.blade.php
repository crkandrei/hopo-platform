@extends('layouts.app')

@section('title', 'Detalii Sesiune')
@section('page-title', 'Detalii Sesiune')

@section('content')
<div class="space-y-6">
    <!-- Back button -->
    <div>
        <a href="{{ route('sessions.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800">
            <i class="fas fa-arrow-left mr-2"></i>
            Înapoi la sesiuni
        </a>
    </div>

    <!-- Session Overview -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 card-hover">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-stopwatch text-indigo-600"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Informații Sesiune</h2>
                </div>
                <div class="flex items-center gap-3">
                    @if($session->ended_at && Auth::user() && Auth::user()->isSuperAdmin() && !$session->isPaid() && $session->session_type !== 'birthday')
                    <button onclick="openRestartModal()" class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-redo mr-2"></i>
                        Repornește Sesiunea
                    </button>
                    @endif
                    @if($session->session_type === 'birthday')
                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-pink-100 text-pink-800 inline-flex items-center">
                        <i class="fas fa-birthday-cake mr-1"></i>Birthday
                    </span>
                    @endif
                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $session->ended_at ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $session->ended_at ? 'Închisă' : 'Activă' }}
                    </span>
                    @if($session->ended_at)
                        @if($session->isPaid())
                        <span id="payment-status-badge" class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800 inline-flex items-center">
                            <i class="fas fa-check-circle mr-1"></i>
                            @php
                                $voucherPrice = $session->getVoucherPrice();
                                $hasVoucher = $voucherPrice > 0 || $session->payment_status === 'paid_voucher';
                                $paymentMethodLabel = $session->payment_method === 'CASH' ? 'Cash' : ($session->payment_method === 'CARD' ? 'Card' : null);
                            @endphp
                            @if($hasVoucher && $paymentMethodLabel)
                                Plătit (Voucher + {{ $paymentMethodLabel }})
                            @elseif($hasVoucher)
                                Plătit (Voucher)
                            @elseif($paymentMethodLabel)
                                Plătit ({{ $paymentMethodLabel }})
                            @else
                                Plătit
                            @endif
                        </span>
                        @else
                        <span id="payment-status-badge" class="px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800 inline-flex items-center">
                            <i class="fas fa-clock mr-1"></i>Neplătit
                        </span>
                        @endif
                        @if(Auth::user() && Auth::user()->isSuperAdmin() && $session->session_type !== 'birthday')
                        <button id="toggle-payment-status-btn" 
                                onclick="togglePaymentStatus({{ $session->id }})"
                                class="ml-2 px-3 py-1 text-sm font-medium rounded-lg {{ $session->isPaid() ? 'bg-gray-200 hover:bg-gray-300 text-gray-700' : 'bg-green-200 hover:bg-green-300 text-green-700' }} transition-colors">
                            <i class="fas {{ $session->isPaid() ? 'fa-undo' : 'fa-check' }} mr-1"></i>
                            {{ $session->isPaid() ? 'Marchează Neplătit' : 'Marchează Plătit' }}
                        </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <div class="text-sm text-gray-600 mb-1">Copil</div>
                    <div class="text-lg font-semibold text-gray-900">
                        {{ $session->child ? $session->child->name : '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Părinte/Tutor</div>
                    <div class="text-lg font-semibold text-gray-900">
                        {{ $session->child && $session->child->guardian ? $session->child->guardian->name : '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Brățară</div>
                    <div class="text-lg font-semibold text-gray-900 font-mono">
                        {{ $session->bracelet_code ?: '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Început</div>
                    <div class="text-lg font-semibold text-gray-900">
                        {{ $session->started_at ? $session->started_at->format('d.m.Y H:i') : '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Sfârșit</div>
                    <div class="text-lg font-semibold text-gray-900">
                        {{ $session->ended_at ? $session->ended_at->format('d.m.Y H:i') : '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">Durata totală efectivă</div>
                    <div class="text-lg font-semibold text-indigo-600">
                        {{ $session->getFormattedDuration() }}
                    </div>
                </div>
                @if($session->ended_at && $session->isPaid())
                <div>
                    <div class="text-sm text-gray-600 mb-1">Durata facturată</div>
                    <div class="text-lg font-semibold text-blue-600">
                        {{ $session->getFormattedTotalBilledDuration() }}
                    </div>
                </div>
                @endif
                @if($session->ended_at)
                <div>
                    <div class="text-sm text-gray-600 mb-1">Total</div>
                    <div class="text-lg font-semibold text-green-600">
                        {{ $session->getFormattedTotalPrice() }}
                    </div>
                    @if($session->price_per_hour_at_calculation)
                    <div class="text-xs text-gray-500 mt-1">
                        Preț/ora: {{ number_format($session->price_per_hour_at_calculation, 2, '.', '') }} RON
                    </div>
                    @endif
                </div>
                @else
                <div>
                    <div class="text-sm text-gray-600 mb-1">Preț estimat</div>
                    <div class="text-lg font-semibold text-amber-600">
                        {{ $session->getFormattedPrice() }}
                    </div>
                </div>
                @endif
                @if($session->ended_at && $session->isPaid())
                <div>
                    <div class="text-sm text-gray-600 mb-1">Încasat</div>
                    <div class="text-lg font-semibold text-green-600">
                        @php
                            $amountCollected = $session->getAmountCollected();
                            $voucherPrice = $session->getVoucherPrice();
                        @endphp
                        {{ number_format($amountCollected, 2, '.', '') }} RON
                        @if($session->payment_method)
                            <span class="text-sm font-normal text-gray-600">({{ $session->payment_method === 'CASH' ? 'Cash' : 'Card' }})</span>
                        @endif
                        @if($voucherPrice > 0)
                            <span class="text-sm font-normal text-gray-600">+ voucher ({{ number_format($voucherPrice, 2, '.', '') }} RON)</span>
                        @endif
                    </div>
                </div>
                @endif
                @if($session->products && $session->products->count() > 0)
                <div class="col-span-full">
                    <div class="text-sm text-gray-600 mb-1">Total Produse</div>
                    <div class="text-lg font-semibold text-purple-600">
                        {{ number_format($session->getProductsTotalPrice(), 2, '.', '') }} RON
                    </div>
                </div>
                @endif
                @if($session->ended_at && $session->products && $session->products->count() > 0)
                <div class="col-span-full">
                    <div class="text-sm text-gray-600 mb-1">Total General</div>
                    <div class="text-lg font-semibold text-green-600">
                        {{ $session->getFormattedTotalPrice() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 card-hover">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-box text-purple-600"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Produse</h2>
                </div>
                @if(!$session->isPaid())
                <button id="addProductsBtn" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Adaugă Produse
                </button>
                @endif
            </div>
        </div>
        <div class="p-6">
            @if($session->products && $session->products->count() > 0)
                <div class="space-y-3">
                    @foreach($session->products as $sessionProduct)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div class="font-medium text-gray-900">{{ $sessionProduct->product->name ?? 'Produs' }}</div>
                            <div class="text-sm text-gray-500 mt-1">
                                {{ $sessionProduct->quantity }} buc × {{ number_format($sessionProduct->unit_price, 2, '.', '') }} RON
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">{{ number_format($sessionProduct->total_price, 2, '.', '') }} RON</div>
                        </div>
                    </div>
                    @endforeach
                    <div class="pt-3 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="font-semibold text-gray-900">Subtotal Produse:</div>
                            <div class="font-semibold text-gray-900">{{ number_format($session->getProductsTotalPrice(), 2, '.', '') }} RON</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-box text-4xl mb-3"></i>
                    <p>Nu sunt produse adăugate la această sesiune.</p>
                    @if(!$session->ended_at)
                    <p class="text-sm mt-2">Click pe "Adaugă Produse" pentru a adăuga produse.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Activity Log -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 card-hover">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-list-ul text-emerald-600"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Log Activitate</h2>
            </div>
        </div>
        <div class="p-6">
            @php
                $events = [];
                
                // Adaugă evenimentele din intervale (Start joc și Pauză)
                if ($session->intervals && $session->intervals->count() > 0) {
                    foreach ($session->intervals as $interval) {
                        // Start interval
                        if ($interval->started_at) {
                            $events[] = [
                                'type' => 'start',
                                'time' => $interval->started_at,
                                'label' => 'Start'
                            ];
                        }
                        
                        // Stop interval (pauză)
                        if ($interval->ended_at) {
                            $events[] = [
                                'type' => 'pause',
                                'time' => $interval->ended_at,
                                'label' => 'Pauză',
                                'duration' => $interval->duration_seconds
                            ];
                        }
                    }
                }
                
                // Sortează evenimentele cronologic
                usort($events, function($a, $b) {
                    return $a['time']->timestamp <=> $b['time']->timestamp;
                });
            @endphp
            
            @if(count($events) > 0)
                <div class="space-y-3">
                    @foreach($events as $event)
                        <div class="flex items-center gap-4 py-3 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                            <!-- Icon -->
                            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 
                                {{ $event['type'] === 'start' ? 'bg-green-100 text-green-600' : 'bg-amber-100 text-amber-600' }}">
                                <i class="fas {{ $event['type'] === 'start' ? 'fa-play' : 'fa-pause' }}"></i>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-gray-900">
                                        {{ $event['label'] }}: {{ $event['time']->format('H:i:s') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-info-circle text-4xl mb-3"></i>
                    <p>Nu există evenimente înregistrate pentru această sesiune.</p>
                </div>
            @endif
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
@php
$sessionProductsJson = $session->products->map(function($sp) {
    return [
        'id' => $sp->id,
        'product_id' => $sp->product_id,
        'product_name' => $sp->product->name ?? 'Produs',
        'quantity' => $sp->quantity,
        'unit_price' => $sp->unit_price,
        'total_price' => $sp->total_price,
    ];
})->values();
@endphp

<!-- Restart Session Confirmation Modal (for Super Admin) -->
<div id="restart-session-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-yellow-50 rounded-t-xl">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-redo text-yellow-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Repornește Sesiunea</h3>
            </div>
            <button onclick="closeRestartModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-6">
            <p class="text-gray-700 text-lg mb-2">Sigur vrei să repornești această sesiune?</p>
            <p class="text-gray-600 mb-4">Copil: <strong>{{ $session->child ? $session->child->name : '-' }}</strong></p>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5"></i>
                    <div class="text-sm text-yellow-800">
                        <p class="font-semibold mb-1">Atenție:</p>
                        <ul class="list-disc ml-4 space-y-1">
                            <li>Sesiunea va fi reactivată și va continua să numere timpul</li>
                            <li>Durata anterioară se va păstra (intervalele vechi rămân)</li>
                            <li>Prețul se va recalcula la noua oprire pe baza duratei totale</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-3">
                <button 
                    onclick="closeRestartModal()" 
                    class="px-5 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors font-medium">
                    Anulează
                </button>
                <button 
                    id="restart-modal-confirm-btn"
                    onclick="confirmRestartSession()"
                    class="px-5 py-2.5 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors font-medium flex items-center gap-2">
                    <i class="fas fa-redo"></i>
                    <span>Repornește</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Method Modal (for Super Admin) -->
<div id="payment-method-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Selectează Metoda de Plată</h3>
            <button onclick="closePaymentMethodModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-4">
            <p class="text-gray-700 mb-4">Cum se plătește?</p>
            <div class="flex gap-4 mb-6">
                <button 
                    data-payment-method-btn="CASH"
                    onclick="selectPaymentMethod('CASH')"
                    class="flex-1 px-6 py-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    Cash
                </button>
                <button 
                    data-payment-method-btn="CARD"
                    onclick="selectPaymentMethod('CARD')"
                    class="flex-1 px-6 py-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors">
                    <i class="fas fa-credit-card mr-2"></i>
                    Card
                </button>
            </div>
            
            <div class="flex justify-end gap-3">
                <button onclick="closePaymentMethodModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    Anulează
                </button>
                <button 
                    id="confirm-payment-method-btn"
                    onclick="confirmPaymentMethod()"
                    disabled
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Confirmă
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let printInProgress = false;
const sessionId = {{ $session->id }};
const sessionIsPaid = {{ $session->isPaid() ? 'true' : 'false' }};
let availableProducts = [];
let sessionProducts = @json($sessionProductsJson);

// ===== PAYMENT STATUS TOGGLE (Super Admin Only) =====

let paymentMethodModalSessionId = null;
let selectedPaymentMethod = null;

function togglePaymentStatus(sessionId) {
    // Check if session is currently paid using the PHP variable
    if (sessionIsPaid) {
        // Session is paid, mark as unpaid directly (no modal needed)
        if (!confirm('Sigur doriți să marcați sesiunea ca neplătită?')) {
            return;
        }
        markPaymentStatus(sessionId, null);
    } else {
        // Session is unpaid, show modal to select payment method
        openPaymentMethodModal(sessionId);
    }
}

function openPaymentMethodModal(sessionId) {
    paymentMethodModalSessionId = sessionId;
    selectedPaymentMethod = null;
    
    // Reset modal state
    document.querySelectorAll('[data-payment-method-btn]').forEach(btn => {
        btn.classList.remove('bg-indigo-600', 'ring-2', 'ring-indigo-500');
        btn.classList.add('bg-gray-200', 'hover:bg-gray-300');
    });
    
    // Reset confirm button
    const confirmBtn = document.getElementById('confirm-payment-method-btn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
    }
    
    // Show modal
    document.getElementById('payment-method-modal').classList.remove('hidden');
}

function closePaymentMethodModal() {
    document.getElementById('payment-method-modal').classList.add('hidden');
    paymentMethodModalSessionId = null;
    selectedPaymentMethod = null;
}

function selectPaymentMethod(method) {
    selectedPaymentMethod = method;
    
    // Update UI
    document.querySelectorAll('[data-payment-method-btn]').forEach(btn => {
        btn.classList.remove('bg-indigo-600', 'ring-2', 'ring-indigo-500');
        btn.classList.add('bg-gray-200', 'hover:bg-gray-300');
    });
    
    const selectedBtn = document.querySelector(`[data-payment-method-btn="${method}"]`);
    if (selectedBtn) {
        selectedBtn.classList.remove('bg-gray-200', 'hover:bg-gray-300');
        selectedBtn.classList.add('bg-indigo-600', 'ring-2', 'ring-indigo-500');
    }
    
    // Enable confirm button
    document.getElementById('confirm-payment-method-btn').disabled = false;
}

async function confirmPaymentMethod() {
    if (!selectedPaymentMethod || !paymentMethodModalSessionId) {
        alert('Selectați o metodă de plată');
        return;
    }
    
    // Save values before closing modal (since closePaymentMethodModal sets them to null)
    const sessionIdToUse = paymentMethodModalSessionId;
    const paymentMethodToUse = selectedPaymentMethod;
    
    // Close modal
    closePaymentMethodModal();
    
    // Mark as paid with selected payment method
    markPaymentStatus(sessionIdToUse, paymentMethodToUse);
}

async function markPaymentStatus(sessionId, paymentMethod) {
    const btn = document.getElementById('toggle-payment-status-btn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Se procesează...';

    // Ensure sessionId is a number
    const sessionIdNum = parseInt(sessionId, 10);
    if (isNaN(sessionIdNum)) {
        alert('ID sesiune invalid: ' + sessionId);
        btn.disabled = false;
        btn.innerHTML = originalText;
        return;
    }

    try {
        const url = `/sessions/${sessionIdNum}/toggle-payment-status`;
        console.log('Sending request to:', url, 'with payment_method:', paymentMethod);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                payment_method: paymentMethod
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Eroare la actualizarea statusului');
        }

        const result = await response.json();
        
        if (result.success) {
            // Reload the page to reflect changes
            window.location.reload();
        } else {
            throw new Error(result.message || 'Eroare necunoscută');
        }
    } catch (error) {
        console.error('Error updating payment status:', error);
        alert('Eroare: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ===== RESTART SESSION (Super Admin Only) =====

function openRestartModal() {
    document.getElementById('restart-session-modal').classList.remove('hidden');
}

function closeRestartModal() {
    document.getElementById('restart-session-modal').classList.add('hidden');
    
    // Reset button state
    const btn = document.getElementById('restart-modal-confirm-btn');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-redo"></i><span>Repornește</span>';
    }
}

async function confirmRestartSession() {
    const btn = document.getElementById('restart-modal-confirm-btn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Se procesează...</span>';

    try {
        const response = await fetch(`/sessions/${sessionId}/restart`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Eroare la repornirea sesiunii');
        }

        const result = await response.json();
        
        if (result.success) {
            // Close modal and reload page to show updated session
            closeRestartModal();
            window.location.reload();
        } else {
            throw new Error(result.message || 'Eroare necunoscută');
        }
    } catch (error) {
        console.error('Error restarting session:', error);
        alert('Eroare: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}


// ===== PRODUCTS MANAGEMENT =====

async function apiCall(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            ...options.headers
        },
        credentials: 'same-origin'
    });
    
    const contentType = response.headers.get('content-type');
    let data;
    
    const responseText = await response.text();
    
    if (contentType && contentType.includes('application/json')) {
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            const error = new Error('Răspuns invalid de la server');
            error.data = { message: 'Serverul a returnat un răspuns invalid.' };
            error.status = response.status;
            throw error;
        }
    } else {
        data = { success: response.ok, message: responseText };
    }
    
    if (!response.ok) {
        const error = new Error(data.message || 'Eroare de la server');
        error.status = response.status;
        error.data = data;
        throw error;
    }
    
    return data;
}

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

// Render products list
function renderProductsList() {
    const productsSection = document.querySelector('#productsSection .p-6');
    if (!productsSection) return;

    if (sessionProducts.length === 0) {
        productsSection.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-box text-4xl mb-3"></i>
                <p>Nu sunt produse adăugate la această sesiune.</p>
                @if(!$session->ended_at)
                <p class="text-sm mt-2">Click pe "Adaugă Produse" pentru a adăuga produse.</p>
                @endif
            </div>
        `;
        return;
    }

    let html = '<div class="space-y-3">';
    sessionProducts.forEach(product => {
        html += `
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <div class="font-medium text-gray-900">${product.product_name}</div>
                    <div class="text-sm text-gray-500 mt-1">
                        ${product.quantity} buc × ${parseFloat(product.unit_price).toFixed(2)} RON
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-semibold text-gray-900">${parseFloat(product.total_price).toFixed(2)} RON</div>
                </div>
            </div>
        `;
    });
    
    const subtotal = sessionProducts.reduce((sum, p) => sum + parseFloat(p.total_price), 0);
    html += `
        <div class="pt-3 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="font-semibold text-gray-900">Subtotal Produse:</div>
                <div class="font-semibold text-gray-900">${subtotal.toFixed(2)} RON</div>
            </div>
        </div>
    </div>
    `;
    
    productsSection.innerHTML = html;
}

// Open add products modal
function openAddProductsModal() {
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
    
    if (!productsSelect || !quantityInput) {
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
                session_id: sessionId,
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
                
                // Reload page to update totals
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
            closeAddProductsModal();
        } else {
            alert('Eroare: ' + (result.message || 'Nu s-a putut adăuga produsul'));
        }
    } catch (e) {
        console.error('Error adding product:', e);
        alert('Eroare la adăugarea produsului: ' + (e.data?.message || e.message || 'Eroare necunoscută'));
    }
}

// Bind events
@if(!$session->isPaid())
const addProductsBtn = document.getElementById('addProductsBtn');
if (addProductsBtn) {
    addProductsBtn.addEventListener('click', openAddProductsModal);
}

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
@endif

// Load products on page load
loadAvailableProducts();
</script>
@endsection

