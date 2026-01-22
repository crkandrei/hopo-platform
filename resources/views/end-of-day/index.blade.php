@extends('layouts.app')

@section('title', 'Final de Zi')
@section('page-title', 'Final de Zi')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Final de Zi 📊</h1>
                <p class="text-gray-600 text-lg">Statistici și rapoarte pentru <span id="selected-date-text">{{ $selectedDateFormatted }}</span></p>
            </div>
            <div class="flex items-center gap-3">
                <label for="date-selector" class="text-sm font-medium text-gray-700">Selectează data:</label>
                <input type="date" 
                       id="date-selector" 
                       name="date" 
                       value="{{ $selectedDate }}" 
                       max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900">
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Sesiuni</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $totalSessions }}</p>
                    <p class="text-xs text-gray-500 mt-1">Pentru data selectată</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-stopwatch text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>


        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Bani</p>
                    <p class="text-3xl font-bold text-emerald-600">{{ number_format($totalMoney, 2, '.', '') }} RON</p>
                    <p class="text-xs text-gray-500 mt-1">Încasări pentru data selectată</p>
                    @if($cashTotal > 0 || $cardTotal > 0 || $voucherTotal > 0)
                    <div class="text-xs text-gray-500 mt-2 space-y-0.5">
                        @if($cashTotal > 0)
                        <div>Cash: {{ number_format($cashTotal, 2, '.', '') }} RON</div>
                        @endif
                        @if($cardTotal > 0)
                        <div>Card: {{ number_format($cardTotal, 2, '.', '') }} RON</div>
                        @endif
                        @if($voucherTotal > 0)
                        <div>Voucher: {{ number_format($voucherTotal, 2, '.', '') }} RON</div>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-emerald-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Rapoarte</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button id="print-z-report-btn" 
                    class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-6 py-4 rounded-lg hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200 font-medium flex items-center justify-center shadow-md">
                <i class="fas fa-file-alt mr-2"></i>
                Raport Z
            </button>
            
            <button id="print-non-fiscal-btn" 
                    class="bg-gradient-to-r from-green-600 to-green-700 text-white px-6 py-4 rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-200 font-medium flex items-center justify-center shadow-md">
                <i class="fas fa-print mr-2"></i>
                Raport Nefiscal
            </button>
        </div>
    </div>
</div>

<!-- Z Report Modal -->
<div id="z-report-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="if(event.target === this) closeZReportModal()">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <!-- Step 1: Loading -->
            <div id="z-report-step-loading">
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-4xl text-indigo-600 mb-4"></i>
                    <p class="text-gray-700 text-lg font-semibold">Se generează raportul Z...</p>
                    <p class="text-gray-500 text-sm mt-2">Vă rugăm să așteptați</p>
                </div>
            </div>

            <!-- Step 2: Result (Success/Error) -->
            <div id="z-report-step-result" class="hidden">
                <div id="z-report-result-content" class="text-center py-6">
                    <!-- Success or Error icon and message will be inserted here -->
                </div>
                <div class="flex justify-end gap-3 mt-4">
                    <button 
                        onclick="closeZReportModal()"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Închide
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Z Report Modal Functions
function openZReportModal() {
    document.getElementById('z-report-modal').classList.remove('hidden');
    document.getElementById('z-report-step-loading').classList.remove('hidden');
    document.getElementById('z-report-step-result').classList.add('hidden');
}

function closeZReportModal() {
    document.getElementById('z-report-modal').classList.add('hidden');
}

async function saveZReportLog(data) {
    try {
        const response = await fetch('{{ route("end-of-day.save-z-report-log") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
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
        console.error('Error saving Z report log:', error);
        throw error;
    }
}

function showZReportResult(type, message, file) {
    // Hide loading, show result
    document.getElementById('z-report-step-loading').classList.add('hidden');
    document.getElementById('z-report-step-result').classList.remove('hidden');
    
    // Build result content
    const resultContent = document.getElementById('z-report-result-content');
    
    if (type === 'success') {
        resultContent.innerHTML = `
            <div class="mb-4">
                <i class="fas fa-check-circle text-5xl text-green-500 mb-4"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Raport Z generat cu succes!</h3>
            <p class="text-gray-700 mb-2 font-semibold text-lg">${message}</p>
            ${file ? `<p class="text-sm text-gray-500 mt-2">Fișier: ${file}</p>` : ''}
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

document.addEventListener('DOMContentLoaded', function() {
    // Print Z Report button
    const printZReportBtn = document.getElementById('print-z-report-btn');
    if (printZReportBtn) {
        printZReportBtn.addEventListener('click', async function() {
            const originalBtnText = printZReportBtn.innerHTML;
            printZReportBtn.disabled = true;
            printZReportBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Se procesează...';

            // Open modal with loading state
            openZReportModal();

            try {
                // Step 1: Send directly to local bridge from browser (same as print)
                const bridgeUrl = 'http://localhost:9000';
                const bridgeResponse = await fetch(`${bridgeUrl}/z-report`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
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

                // Step 2: Save log to Laravel backend
                if (bridgeData.status === 'success') {
                    // Success case - save log and show result
                    try {
                        await saveZReportLog({
                            filename: bridgeData.file || null,
                            status: 'success',
                            error_message: null
                        });
                    } catch (logError) {
                        console.error('Error saving Z report log:', logError);
                        // Don't fail the request if logging fails
                    }
                    showZReportResult('success', 'Z;1', bridgeData.file || null);
                } else {
                    // Error case - save log and show error
                    const errorMessage = bridgeData.message || bridgeData.details || 'Eroare necunoscută';
                    try {
                        await saveZReportLog({
                            filename: bridgeData.file || null,
                            status: 'error',
                            error_message: errorMessage
                        });
                    } catch (logError) {
                        console.error('Error saving Z report log:', logError);
                        // Don't fail the request if logging fails
                    }
                    showZReportResult('error', errorMessage, null);
                }
            } catch (error) {
                console.error('Error:', error);
                // Network error or other exception
                let errorMessage;
                if (error.message && (error.message.includes('fetch') || error.message.includes('Failed to fetch'))) {
                    errorMessage = 'Nu s-a putut conecta la bridge-ul fiscal. Asigură-te că bridge-ul rulează pe localhost:9000';
                } else {
                    errorMessage = error.message || 'Eroare necunoscută';
                }
                
                // Try to save error log
                try {
                    await saveZReportLog({
                        filename: null,
                        status: 'error',
                        error_message: errorMessage
                    });
                } catch (logError) {
                    console.error('Error saving Z report log:', logError);
                }
                
                showZReportResult('error', errorMessage, null);
            } finally {
                printZReportBtn.disabled = false;
                printZReportBtn.innerHTML = originalBtnText;
            }
        });
    }

    // Print Non-Fiscal Report button
    const printNonFiscalBtn = document.getElementById('print-non-fiscal-btn');
    if (printNonFiscalBtn) {
        printNonFiscalBtn.addEventListener('click', function() {
            // Get selected date
            const dateSelector = document.getElementById('date-selector');
            const selectedDate = dateSelector ? dateSelector.value : '';
            // Open print page in new window with selected date
            const printUrl = '{{ route("end-of-day.print-non-fiscal") }}' + (selectedDate ? '?date=' + encodeURIComponent(selectedDate) : '');
            window.open(printUrl, '_blank', 'width=400,height=600');
        });
    }

    // Date selector change handler - reload page with new date
    const dateSelector = document.getElementById('date-selector');
    if (dateSelector) {
        dateSelector.addEventListener('change', function() {
            const selectedDate = this.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('date', selectedDate);
            window.location.href = currentUrl.toString();
        });
    }
});
</script>
@endsection

