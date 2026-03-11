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
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Cantitate</label>
                            <input type="number" min="0" value="0" data-source-type="product" data-source-id="{{ $prod->id }}" data-name="{{ $prod->name }}" data-price="{{ $prod->price }}" class="item-qty w-20 px-2 py-1 border border-gray-300 rounded-md" name="qty_product_{{ $prod->id }}">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('standalone-receipt-form');
    const totalDisplay = document.getElementById('total-display');
    const btnSubmit = document.getElementById('btn-submit');
    const formError = document.getElementById('form-error');
    const formErrorText = document.getElementById('form-error-text');

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
            if (data.success && data.payment_url) {
                window.location.href = data.payment_url;
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
