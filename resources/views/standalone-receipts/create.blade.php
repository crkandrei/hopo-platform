@extends('layouts.app')

@section('title', 'Bon Specific')
@section('page-title', 'Bon Specific')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Bon Specific</h1>
                <p class="text-gray-600">Selectați pachete și/sau produse, setați cantitățile, apoi plătiți.</p>
            </div>
            <a href="{{ route('sessions.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Înapoi la Sesiuni
            </a>
        </div>
    </div>

    <div id="form-error" class="hidden bg-red-50 border border-red-200 rounded-lg p-4"><p id="form-error-text" class="text-red-800"></p></div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form id="standalone-receipt-form">
            @csrf

            @if($packages->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Pachete</h2>
                <div class="space-y-3">
                    @foreach($packages as $pkg)
                    <div class="flex items-center justify-between gap-4 py-2 border-b border-gray-100">
                        <div>
                            <span class="font-medium text-gray-900">{{ $pkg->name }}</span>
                            <span class="text-gray-500 text-sm ml-2">{{ number_format($pkg->price, 2) }} RON / buc</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Cantitate</label>
                            <input type="number" min="0" value="0" data-source-type="package" data-source-id="{{ $pkg->id }}" data-name="{{ $pkg->name }}" data-price="{{ $pkg->price }}" class="item-qty w-20 px-2 py-1 border border-gray-300 rounded-md" name="qty_package_{{ $pkg->id }}">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($products->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Produse</h2>
                <div class="space-y-3">
                    @foreach($products as $prod)
                    <div class="flex items-center justify-between gap-4 py-2 border-b border-gray-100">
                        <div>
                            <span class="font-medium text-gray-900">{{ $prod->name }}</span>
                            <span class="text-gray-500 text-sm ml-2">{{ number_format($prod->price, 2) }} RON / buc</span>
                            @if($prod->has_sgr)
                                <span class="text-xs text-blue-600 ml-1">(+ {{ number_format(\App\Models\Product::SGR_VALUE, 2) }} RON SGR)</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Cantitate</label>
                            <input type="number" min="0" value="0"
                                   data-source-type="product"
                                   data-source-id="{{ $prod->id }}"
                                   data-name="{{ $prod->name }}"
                                   data-price="{{ $prod->price }}"
                                   data-has-sgr="{{ $prod->has_sgr ? '1' : '0' }}"
                                   data-sgr-value="{{ \App\Models\Product::SGR_VALUE }}"
                                   class="item-qty w-20 px-2 py-1 border border-gray-300 rounded-md"
                                   name="qty_product_{{ $prod->id }}">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($packages->isEmpty() && $products->isEmpty())
            <p class="text-gray-600">Nu există pachete sau produse active. Adăugați din setările locației.</p>
            @else
            <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                <div>
                    <p class="text-sm text-gray-600">Total: <span id="total-display" class="font-bold text-gray-900">0.00</span> RON</p>
                </div>
                <button type="submit" id="btn-submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium disabled:opacity-50" disabled>
                    <i class="fas fa-receipt mr-2"></i>Plătește
                </button>
            </div>
            @endif
        </form>
    </div>
</div>

@include('partials.payment-wizard')
@endsection

@section('scripts')
<script>
window.PaymentWizardConfig = {
    getLocationId: function(ctx) { return ctx.type === 'standalone' ? (ctx.locationId || null) : null; },
    getVoucherValidationType: function(ctx) { return ctx.type === 'standalone' ? 'amount' : null; },
    prepare: function(ctx, paymentType, voucherCode) {
        if (ctx.type === 'standalone') {
            return { url: '/standalone-receipts/' + ctx.receiptId + '/prepare-fiscal-print', body: { paymentType: paymentType, voucher_code: voucherCode || null } };
        }
        return { url: null, body: {} };
    },
    noReceiptNeeded: function(receipt) { return receipt && receipt.noReceiptNeeded === true; },
    markPaidWithVoucherOnly: async function(ctx, voucherCode, showResult) {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        if (ctx.type === 'standalone') {
            const payload = { payment_method: null, voucher_code: voucherCode || null };
            if (ctx.voucherId) payload.voucher_id = ctx.voucherId;
            if (ctx.voucherAmountUsed != null) payload.voucher_amount_used = ctx.voucherAmountUsed;
            const res = await fetch('/standalone-receipts/' + ctx.receiptId + '/mark-paid-no-fiscal', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(payload) });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Eroare');
            showResult('success', 'Valoarea a fost acoperită de voucher. Bonul a fost marcat plătit.', null);
            if (ctx.onStandaloneSuccess) ctx.onStandaloneSuccess();
        }
    },
    renderReceiptPreview: function(receipt, data, itemsEl, tenantEl, totalEl, methodEl) {
        if (!itemsEl) return;
        itemsEl.innerHTML = '';
        if (tenantEl) tenantEl.textContent = (receipt && receipt.locationName) || (receipt && receipt.tenantName) || '-';
        if (methodEl) methodEl.textContent = data.paymentType === 'CASH' ? 'Cash' : 'Card';
        if (data && data.items && data.items.length) {
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
        var paymentType = opts.paymentType;
        var preparedData = opts.preparedData;
        var getVoucherCode = opts.getVoucherCode;
        var csrfToken = opts.csrfToken;
        var bridgeUrl = opts.bridgeUrl;
        var showResult = opts.showResult;
        var fiscalEnabled = ctx.fiscalEnabled !== false;
        if (!fiscalEnabled) {
            var url = '/standalone-receipts/' + ctx.receiptId + '/mark-paid-no-fiscal';
            var body = { payment_method: paymentType, voucher_code: (getVoucherCode && getVoucherCode()) || null, voucher_id: preparedData.voucher_id || null, voucher_amount_used: (preparedData.voucher_discount_amount || 0) > 0 ? preparedData.voucher_discount_amount : null };
            var res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: JSON.stringify(body) });
            var data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Eroare');
            showResult('success', 'Plata a fost înregistrată cu succes.', null);
            if (ctx.onStandaloneSuccess) ctx.onStandaloneSuccess();
            return;
        }
        var bridgeRes = await fetch(bridgeUrl + '/print', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(preparedData) });
        if (!bridgeRes.ok) {
            var errText = await bridgeRes.text();
            try { var err = JSON.parse(errText); errText = err.message || err.details || errText; } catch (_) {}
            throw new Error(errText || 'Eroare bridge');
        }
        var bridgeData = await bridgeRes.json();
        await fetch('{{ route("standalone-receipts.save-fiscal-receipt-log") }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: JSON.stringify({ standalone_receipt_id: ctx.receiptId, filename: bridgeData.file || null, status: bridgeData.status === 'success' ? 'success' : 'error', error_message: bridgeData.status === 'success' ? null : (bridgeData.message || bridgeData.details), payment_method: paymentType, voucher_id: preparedData.voucher_id || null, voucher_amount_used: preparedData.voucher_discount_amount || null }) });
        if (bridgeData.status === 'success') {
            showResult('success', 'Bon fiscal emis cu succes!', bridgeData.file || null);
            if (ctx.onStandaloneSuccess) ctx.onStandaloneSuccess();
        } else {
            showResult('error', bridgeData.message || bridgeData.details || 'Eroare', null);
        }
    },
    onSuccess: function() {},
    showError: function(msg) { alert(msg); }
};
</script>
@include('partials.payment-wizard-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('standalone-receipt-form');
    const totalDisplay = document.getElementById('total-display');
    const btnSubmit = document.getElementById('btn-submit');
    const formError = document.getElementById('form-error');
    const formErrorText = document.getElementById('form-error-text');
    const locationId = {{ $location->id }};
    const fiscalEnabled = {{ $location->fiscal_enabled ? 'true' : 'false' }};

    function getSelectedItems() {
        const items = [];
        document.querySelectorAll('.item-qty').forEach(function(input) {
            const qty = parseInt(input.value, 10) || 0;
            if (qty > 0) {
                items.push({
                    source_type: input.dataset.sourceType,
                    source_id: parseInt(input.dataset.sourceId, 10),
                    quantity: qty
                });
            }
        });
        return items;
    }

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.item-qty').forEach(function(input) {
            const qty = parseInt(input.value, 10) || 0;
            const price = parseFloat(input.dataset.price) || 0;
            total += qty * price;
            if (input.dataset.hasSgr === '1') {
                total += qty * (parseFloat(input.dataset.sgrValue) || 0);
            }
        });
        totalDisplay.textContent = total.toFixed(2);
        btnSubmit.disabled = total <= 0;
    }

    document.querySelectorAll('.item-qty').forEach(function(input) {
        input.addEventListener('change', updateTotal);
        input.addEventListener('input', updateTotal);
    });
    updateTotal();

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const items = getSelectedItems();
        if (items.length === 0) {
            formError.classList.remove('hidden');
            formErrorText.textContent = 'Selectați cel puțin un item cu cantitate > 0.';
            return;
        }
        formError.classList.add('hidden');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Se creează...';
        try {
            const res = await fetch('{{ route("standalone-receipts.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ items: items })
            });
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Eroare la crearea bonului');
            }
            if (data.success && data.receipt_id) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-receipt mr-2"></i>Plătește';
                if (window.PaymentWizard) {
                    window.PaymentWizard.open({
                        type: 'standalone',
                        receiptId: data.receipt_id,
                        locationId: locationId,
                        fiscalEnabled: fiscalEnabled,
                        onStandaloneSuccess: function() {
                            window.location.href = '{{ route('sessions.index') }}';
                        }
                    });
                } else {
                    window.location.href = '/standalone-receipts/' + data.receipt_id + '/pay';
                }
            } else {
                throw new Error('Răspuns invalid de la server');
            }
        } catch (err) {
            formError.classList.remove('hidden');
            formErrorText.textContent = err.message;
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-receipt mr-2"></i>Plătește';
        }
    });
});
</script>
@endsection
