{{-- Shared payment wizard modal: Cash/Card + voucher, optional receipt preview, loading, result. --}}
<div id="payment-wizard-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Bon Fiscal</h3>
            <button type="button" id="payment-wizard-close" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-6 py-4">
            <div id="payment-wizard-step-1">
                <p class="text-gray-700 mb-4">Cum se plătește?</p>
                <div class="flex gap-4 mb-6">
                    <button type="button" data-payment-btn="CASH" class="flex-1 px-6 py-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors">
                        <i class="fas fa-money-bill-wave mr-2"></i>Cash
                    </button>
                    <button type="button" data-payment-btn="CARD" class="flex-1 px-6 py-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors">
                        <i class="fas fa-credit-card mr-2"></i>Card
                    </button>
                </div>
                <div class="mb-6 pt-4 border-t border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cod voucher (opțional)</label>
                    <div class="flex gap-2">
                        <input type="text" id="payment-wizard-voucher-code" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ex: ABC12XYZ" maxlength="32">
                        <button type="button" id="payment-wizard-validate-voucher" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-md text-sm font-medium">Verifică</button>
                    </div>
                    <p id="payment-wizard-voucher-message" class="mt-1 text-xs hidden"></p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" id="payment-wizard-cancel" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Anulează</button>
                    <button type="button" id="payment-wizard-continue" disabled class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Continuă</button>
                </div>
            </div>
            <div id="payment-wizard-step-2" class="hidden">
                <p class="text-gray-700 mb-4 font-medium">Se va scoate bonul fiscal pentru:</p>
                <div id="payment-wizard-receipt-preview" class="bg-white border-2 border-gray-300 rounded-lg p-4 mb-6 shadow-sm max-h-96 overflow-y-auto">
                    <div class="text-center border-b border-gray-300 pb-2 mb-3">
                        <h4 id="payment-wizard-tenant-name" class="font-bold text-lg text-gray-900">-</h4>
                        <p class="text-xs text-gray-500 mt-1">Bon Fiscal</p>
                    </div>
                    <div id="payment-wizard-receipt-items" class="space-y-2 mb-3"></div>
                    <div class="border-t border-gray-300 pt-2 mt-2">
                        <div class="flex justify-between text-base font-bold">
                            <span class="text-gray-900">TOTAL:</span>
                            <span id="payment-wizard-total-price" class="text-indigo-600">-</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-300">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Plată:</span>
                            <span id="payment-wizard-payment-method" class="font-semibold text-gray-900">-</span>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" id="payment-wizard-back" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Anulează</button>
                    <button type="button" id="payment-wizard-confirm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-check mr-2"></i>Confirmă și Emite
                    </button>
                </div>
            </div>
            <div id="payment-wizard-step-3" class="hidden">
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-4"></i>
                    <p class="text-gray-700 text-lg">Se emite bonul fiscal...</p>
                    <p class="text-gray-500 text-sm mt-2">Vă rugăm să așteptați</p>
                </div>
            </div>
            <div id="payment-wizard-step-4" class="hidden">
                <div id="payment-wizard-result-content" class="text-center py-6"></div>
                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" id="payment-wizard-done" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">Închide</button>
                </div>
            </div>
        </div>
    </div>
</div>
