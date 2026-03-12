@php
$validateRoute = $validateRoute ?? route('vouchers.validate');
$csrfToken = $csrfToken ?? csrf_token();
$bridgeUrl = $bridgeUrl ?? config('services.fiscal_bridge.url', 'http://localhost:9000');
@endphp
<script>
(function() {
    if (typeof window.PaymentWizardConfig === 'undefined') {
        console.warn('PaymentWizard: PaymentWizardConfig not set');
        return;
    }
    const config = window.PaymentWizardConfig;
    const validateRoute = @json($validateRoute);
    const csrfToken = @json($csrfToken) || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '');
    const defaultBridgeUrl = @json($bridgeUrl);

    let currentContext = null;
    let currentStep = 1;
    let paymentType = null;
    let voucherValidated = false;
    let preparedData = null;
    let receiptData = null;

    const modal = document.getElementById('payment-wizard-modal');
    const step1 = document.getElementById('payment-wizard-step-1');
    const step2 = document.getElementById('payment-wizard-step-2');
    const step3 = document.getElementById('payment-wizard-step-3');
    const step4 = document.getElementById('payment-wizard-step-4');
    const voucherInput = document.getElementById('payment-wizard-voucher-code');
    const voucherMessage = document.getElementById('payment-wizard-voucher-message');
    const continueBtn = document.getElementById('payment-wizard-continue');

    function getLocationId() {
        return config.getLocationId ? config.getLocationId(currentContext) : null;
    }

    function updateContinueButton() {
        if (!continueBtn) return;
        continueBtn.disabled = !(paymentType || voucherValidated);
    }

    function showStep(step) {
        currentStep = step;
        [step1, step2, step3, step4].forEach((el, i) => {
            if (el) el.classList.toggle('hidden', (i + 1) !== step);
        });
    }

    function resetUi() {
        paymentType = null;
        voucherValidated = false;
        preparedData = null;
        receiptData = null;
        if (voucherInput) voucherInput.value = '';
        if (voucherMessage) {
            voucherMessage.classList.add('hidden');
            voucherMessage.textContent = '';
        }
        if (continueBtn) {
            continueBtn.disabled = true;
            continueBtn.innerHTML = 'Continuă';
        }
        document.querySelectorAll('#payment-wizard-modal [data-payment-btn]').forEach(btn => {
            btn.classList.remove('bg-indigo-600', 'ring-2', 'ring-indigo-500');
            btn.classList.add('bg-gray-200', 'hover:bg-gray-300');
        });
    }

    function open(context) {
        currentContext = context;
        resetUi();
        showStep(1);
        if (modal) modal.classList.remove('hidden');
    }

    function close() {
        if (modal) modal.classList.add('hidden');
        currentContext = null;
        currentStep = 1;
        resetUi();
        if (config.onClose) config.onClose();
    }

    function validateVoucher() {
        const code = voucherInput ? voucherInput.value.trim() : '';
        const locationId = getLocationId();
        const voucherValidationType = config.getVoucherValidationType ? config.getVoucherValidationType(currentContext) : null;
        if (!voucherInput || !voucherMessage) return;
        if (!code) {
            voucherValidated = false;
            updateContinueButton();
            voucherMessage.textContent = 'Introduceți codul voucher.';
            voucherMessage.className = 'mt-1 text-xs text-amber-600';
            voucherMessage.classList.remove('hidden');
            return;
        }
        if (!locationId) {
            voucherValidated = false;
            updateContinueButton();
            voucherMessage.textContent = 'Locația nu este cunoscută.';
            voucherMessage.className = 'mt-1 text-xs text-red-600';
            voucherMessage.classList.remove('hidden');
            return;
        }
        voucherValidated = false;
        updateContinueButton();
        voucherMessage.textContent = 'Se verifică...';
        voucherMessage.className = 'mt-1 text-xs text-gray-500';
        voucherMessage.classList.remove('hidden');
        fetch(validateRoute, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ code: code, location_id: locationId, type: voucherValidationType })
        })
        .then(r => r.json())
        .then(data => {
            if (data.valid && data.voucher_data) {
                voucherMessage.textContent = 'Voucher valid. Sold: ' + (data.voucher_data.type === 'amount' ? data.voucher_data.remaining_value + ' RON' : data.voucher_data.remaining_value + ' ore');
                voucherMessage.className = 'mt-1 text-xs text-green-600';
                voucherValidated = true;
            } else {
                voucherMessage.textContent = data.message || 'Cod invalid';
                voucherMessage.className = 'mt-1 text-xs text-red-600';
                voucherValidated = false;
            }
            updateContinueButton();
        })
        .catch(() => {
            voucherMessage.textContent = 'Eroare la validare';
            voucherMessage.className = 'mt-1 text-xs text-red-600';
            voucherValidated = false;
            updateContinueButton();
        });
    }

    function selectPayment(type) {
        paymentType = type;
        document.querySelectorAll('#payment-wizard-modal [data-payment-btn]').forEach(btn => {
            btn.classList.remove('bg-indigo-600', 'ring-2', 'ring-indigo-500');
            btn.classList.add('bg-gray-200', 'hover:bg-gray-300');
        });
        const sel = document.querySelector(`#payment-wizard-modal [data-payment-btn="${type}"]`);
        if (sel) {
            sel.classList.remove('bg-gray-200', 'hover:bg-gray-300');
            sel.classList.add('bg-indigo-600', 'ring-2', 'ring-indigo-500');
        }
        updateContinueButton();
    }

    function getVoucherCode() {
        return voucherInput && voucherInput.value ? voucherInput.value.trim() : null;
    }

    async function goToConfirm() {
        if (!paymentType && !voucherValidated) {
            alert('Selectați o metodă de plată sau validați un voucher');
            return;
        }
        if (!config.prepare) {
            alert('Configurare invalidă');
            return;
        }
        const { url, body } = config.prepare(currentContext, paymentType, getVoucherCode());
        if (!url) {
            alert('Sesiune sau bon invalid');
            return;
        }
        const origHtml = continueBtn.innerHTML;
        continueBtn.disabled = true;
        continueBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Se încarcă...';
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Eroare la pregătire');
            if (!data.success || !data.data) throw new Error('Date invalide');
            preparedData = data.data;
            receiptData = data.receipt || {};
            if (config.noReceiptNeeded && config.noReceiptNeeded(receiptData)) {
                if (config.markPaidWithVoucherOnly) {
                    if (currentContext && currentContext.type === 'standalone' && preparedData) {
                        currentContext.voucherId = preparedData.voucher_id || null;
                        currentContext.voucherAmountUsed = preparedData.voucher_discount_amount != null ? preparedData.voucher_discount_amount : null;
                    }
                    await config.markPaidWithVoucherOnly(currentContext, getVoucherCode(), showResult);
                }
                return;
            }
            if (!paymentType) {
                continueBtn.disabled = false;
                continueBtn.innerHTML = origHtml;
                alert('Voucherul nu acoperă întreaga sumă. Selectați și o metodă de plată (Cash sau Card).');
                return;
            }
            if (config.renderReceiptPreview) {
                config.renderReceiptPreview(receiptData, preparedData, document.getElementById('payment-wizard-receipt-items'), document.getElementById('payment-wizard-tenant-name'), document.getElementById('payment-wizard-total-price'), document.getElementById('payment-wizard-payment-method'));
            }
            showStep(2);
        } catch (err) {
            if (config.showError) config.showError(err.message); else alert(err.message);
            continueBtn.disabled = false;
            continueBtn.innerHTML = origHtml;
        }
    }

    function showResult(type, message, file) {
        showStep(4);
        const el = document.getElementById('payment-wizard-result-content');
        if (!el) return;
        if (type === 'success') {
            el.innerHTML = `<div class="mb-4"><i class="fas fa-check-circle text-5xl text-green-500 mb-4"></i></div><h3 class="text-xl font-bold text-gray-900 mb-2">Bon fiscal emis cu succes!</h3><p class="text-gray-700 mb-2">${message}</p>${file ? `<p class="text-sm text-gray-500">Fișier: ${file}</p>` : ''}`;
        } else {
            el.innerHTML = `<div class="mb-4"><i class="fas fa-exclamation-circle text-5xl text-red-500 mb-4"></i></div><h3 class="text-xl font-bold text-gray-900 mb-2">Eroare</h3><p class="text-gray-700">${message}</p>`;
        }
    }

    async function confirmAndPrint() {
        if (!paymentType || !preparedData || !config.confirmAndPrint) return;
        showStep(3);
        try {
            await config.confirmAndPrint(currentContext, { paymentType, preparedData, receiptData, getVoucherCode: getVoucherCode, csrfToken, bridgeUrl: config.getBridgeUrl ? config.getBridgeUrl() : defaultBridgeUrl, showResult });
        } catch (err) {
            showResult('error', err.message || 'Eroare');
        }
    }

    if (voucherInput) {
        voucherInput.addEventListener('input', () => {
            if (!voucherValidated) return;
            voucherValidated = false;
            updateContinueButton();
            if (voucherMessage) {
                voucherMessage.textContent = 'Codul a fost modificat. Apăsați din nou pe Verifică.';
                voucherMessage.className = 'mt-1 text-xs text-amber-600';
                voucherMessage.classList.remove('hidden');
            }
        });
    }
    document.querySelectorAll('#payment-wizard-modal [data-payment-btn]').forEach(btn => {
        btn.addEventListener('click', () => selectPayment(btn.getAttribute('data-payment-btn')));
    });
    if (document.getElementById('payment-wizard-validate-voucher')) {
        document.getElementById('payment-wizard-validate-voucher').addEventListener('click', validateVoucher);
    }
    if (continueBtn) continueBtn.addEventListener('click', goToConfirm);
    if (document.getElementById('payment-wizard-cancel')) document.getElementById('payment-wizard-cancel').addEventListener('click', close);
    if (document.getElementById('payment-wizard-close')) document.getElementById('payment-wizard-close').addEventListener('click', close);
    if (document.getElementById('payment-wizard-back')) document.getElementById('payment-wizard-back').addEventListener('click', () => showStep(1));
    if (document.getElementById('payment-wizard-confirm')) document.getElementById('payment-wizard-confirm').addEventListener('click', confirmAndPrint);
    if (document.getElementById('payment-wizard-done')) document.getElementById('payment-wizard-done').addEventListener('click', () => { if (config.onSuccess) config.onSuccess(currentContext); close(); });

    window.PaymentWizard = { open, close };
})();
</script>
