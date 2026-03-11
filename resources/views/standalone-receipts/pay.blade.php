@extends('layouts.app')

@section('title', 'Plată Bon Specific')
@section('page-title', 'Plată Bon Specific')

@section('content')
@php
    $location = $standaloneReceipt->location;
    $fiscalEnabled = $location && $location->fiscal_enabled;
@endphp
<div class="space-y-6 max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Bon Specific – Plată</h1>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produs / Pachet</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cant.</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Preț unitar</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($standaloneReceipt->items as $item)
                    <tr>
                        <td class="px-4 py-2 text-gray-900">{{ $item->name }}</td>
                        <td class="px-4 py-2 text-right text-gray-700">{{ $item->quantity }}</td>
                        <td class="px-4 py-2 text-right text-gray-700">{{ number_format($item->unit_price, 2) }} RON</td>
                        <td class="px-4 py-2 text-right font-medium text-gray-900">{{ number_format($item->total_price, 2) }} RON</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200 flex justify-end">
            <p class="text-lg font-bold text-gray-900">Total: {{ number_format($standaloneReceipt->total_amount, 2) }} RON</p>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="button" onclick="openPaymentModal()" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium">
                <i class="fas fa-credit-card mr-2"></i>Plătește (Cash / Card)
            </button>
            <a href="{{ route('sessions.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Înapoi</a>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="payment-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-900">Metodă de plată</h3>
            <button type="button" onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <div class="px-6 py-4">
            <div id="payment-step-1">
                <p class="text-gray-700 mb-4">Cum se plătește?</p>
                <div class="flex gap-4 mb-6">
                    <button type="button" data-method="CASH" onclick="selectPaymentMethod('CASH')" class="flex-1 px-6 py-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors">
                        <i class="fas fa-money-bill-wave mr-2"></i>Cash
                    </button>
                    <button type="button" data-method="CARD" onclick="selectPaymentMethod('CARD')" class="flex-1 px-6 py-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors">
                        <i class="fas fa-credit-card mr-2"></i>Card
                    </button>
                </div>
                <div class="flex justify-end">
                    <button type="button" id="btn-confirm-payment" onclick="confirmPayment()" disabled class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium disabled:opacity-50">
                        Continuă
                    </button>
                </div>
            </div>
            <div id="payment-step-2" class="hidden">
                <p class="text-gray-600 mb-4"><i class="fas fa-spinner fa-spin mr-2"></i>Se procesează plata...</p>
            </div>
            <div id="payment-step-3" class="hidden">
                <div id="payment-result-content"></div>
                <div class="mt-4">
                    <a href="{{ route('sessions.index') }}" class="inline-block px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">Înapoi la Sesiuni</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const receiptId = {{ $standaloneReceipt->id }};
const fiscalEnabled = {{ $fiscalEnabled ? 'true' : 'false' }};
let paymentMethod = null;

function openPaymentModal() {
    document.getElementById('payment-modal').classList.remove('hidden');
    document.getElementById('payment-step-1').classList.remove('hidden');
    document.getElementById('payment-step-2').classList.add('hidden');
    document.getElementById('payment-step-3').classList.add('hidden');
    document.querySelectorAll('[data-method]').forEach(btn => { btn.classList.remove('ring-2', 'ring-indigo-600'); btn.classList.add('bg-gray-200'); });
    document.getElementById('btn-confirm-payment').disabled = true;
    paymentMethod = null;
}

function closePaymentModal() {
    document.getElementById('payment-modal').classList.add('hidden');
}

function selectPaymentMethod(method) {
    paymentMethod = method;
    document.querySelectorAll('[data-method]').forEach(btn => {
        if (btn.dataset.method === method) {
            btn.classList.add('ring-2', 'ring-indigo-600');
            btn.classList.remove('bg-gray-200');
        } else {
            btn.classList.remove('ring-2', 'ring-indigo-600');
            btn.classList.add('bg-gray-200');
        }
    });
    document.getElementById('btn-confirm-payment').disabled = false;
}

async function confirmPayment() {
    if (!paymentMethod) return;
    document.getElementById('payment-step-1').classList.add('hidden');
    document.getElementById('payment-step-2').classList.remove('hidden');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    try {
        if (!fiscalEnabled) {
            const res = await fetch(`/standalone-receipts/${receiptId}/mark-paid-no-fiscal`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ payment_method: paymentMethod })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Eroare');
            showResult('success', 'Plata a fost înregistrată cu succes.');
        } else {
            const prepareRes = await fetch(`/standalone-receipts/${receiptId}/prepare-fiscal-print`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ paymentType: paymentMethod })
            });
            const prepareData = await prepareRes.json();
            if (!prepareRes.ok || !prepareData.success) throw new Error(prepareData.message || 'Eroare la pregătire');
            const bridgeUrl = '{{ config("services.fiscal_bridge.url", "http://localhost:9000") }}';
            const bridgeRes = await fetch(`${bridgeUrl}/print`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(prepareData.data)
            });
            if (!bridgeRes.ok) {
                const errText = await bridgeRes.text();
                let err = errText;
                try { const j = JSON.parse(errText); err = j.message || j.details || errText; } catch (_) {}
                throw new Error(err);
            }
            const bridgeData = await bridgeRes.json();
            await fetch('{{ route("standalone-receipts.save-fiscal-receipt-log") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({
                    standalone_receipt_id: receiptId,
                    filename: bridgeData.file || null,
                    status: bridgeData.status === 'success' ? 'success' : 'error',
                    error_message: bridgeData.status === 'success' ? null : (bridgeData.message || bridgeData.details),
                    payment_method: paymentMethod
                })
            });
            if (bridgeData.status === 'success') {
                showResult('success', 'Bon fiscal emis cu succes!');
            } else {
                showResult('error', bridgeData.message || bridgeData.details || 'Eroare la printare');
            }
        }
    } catch (err) {
        showResult('error', err.message || 'Eroare la plată');
    }
    document.getElementById('payment-step-2').classList.add('hidden');
    document.getElementById('payment-step-3').classList.remove('hidden');
}

function showResult(type, message) {
    const content = document.getElementById('payment-result-content');
    if (type === 'success') {
        content.innerHTML = `<div class="mb-4"><i class="fas fa-check-circle text-5xl text-green-500"></i></div><h3 class="text-xl font-bold text-gray-900 mb-2">Succes</h3><p class="text-gray-700">${message}</p>`;
    } else {
        content.innerHTML = `<div class="mb-4"><i class="fas fa-exclamation-circle text-5xl text-red-500"></i></div><h3 class="text-xl font-bold text-gray-900 mb-2">Eroare</h3><p class="text-gray-700">${message}</p>`;
    }
}
</script>
@endsection
