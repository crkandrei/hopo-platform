@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Editează Abonament ✏️</h1>
                <p class="mt-1 text-sm text-gray-500">Modificați datele abonamentului existent</p>
            </div>
            <a href="{{ route('admin.subscriptions.history', $subscription->location) }}"
               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                ← Înapoi
            </a>
        </div>
    </div>

    {{-- Location info card --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="text-base font-semibold text-blue-900">📍 {{ $subscription->location->name }}</div>
        <div class="text-sm text-blue-700 mt-1">{{ $subscription->location->company->name ?? '—' }}</div>
    </div>

    {{-- Form card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}">
            @csrf
            @method('PUT')

            {{-- Plan selector --}}
            <div class="mb-6">
                <label for="plan_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Plan abonament <span class="text-red-500">*</span>
                </label>
                @if($plans->isEmpty())
                    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
                        Nu există planuri active. Adăugați planuri în secțiunea Abonamente → Planuri.
                    </div>
                @else
                    <select id="plan_id" name="plan_id" required
                            onchange="onPlanChange(this)"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('plan_id') border-red-500 @enderror">
                        <option value="">— Selectați un plan —</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}"
                                    data-price="{{ $plan->price }}"
                                    data-months="{{ $plan->duration_months }}"
                                    {{ old('plan_id', $subscription->plan_id) == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} — {{ number_format($plan->price, 2) }} RON / {{ $plan->duration_months }} luni
                            </option>
                        @endforeach
                    </select>
                    <div id="plan-info" class="hidden mt-2 px-3 py-2 bg-indigo-50 border border-indigo-200 rounded-lg text-sm text-indigo-800"></div>
                @endif
                @error('plan_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- starts_at --}}
                <div>
                    <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-1">
                        Data de început <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="starts_at" name="starts_at"
                           value="{{ old('starts_at', $subscription->starts_at->toDateString()) }}"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('starts_at') border-red-500 @enderror">
                    @error('starts_at')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- expires_at --}}
                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">
                        Data de expirare <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="expires_at" name="expires_at"
                           value="{{ old('expires_at', $subscription->expires_at->toDateString()) }}"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('expires_at') border-red-500 @enderror">
                    @error('expires_at')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="mt-2 flex gap-2">
                        <button type="button" onclick="setDuration(1)"
                                class="px-3 py-1 text-xs font-medium rounded-md border border-indigo-300 text-indigo-700 hover:bg-indigo-50 transition-colors">
                            +1 lună
                        </button>
                        <button type="button" onclick="setDuration(2)"
                                class="px-3 py-1 text-xs font-medium rounded-md border border-indigo-300 text-indigo-700 hover:bg-indigo-50 transition-colors">
                            +2 luni
                        </button>
                        <button type="button" onclick="setDuration(12)"
                                class="px-3 py-1 text-xs font-medium rounded-md border border-indigo-300 text-indigo-700 hover:bg-indigo-50 transition-colors">
                            +1 an
                        </button>
                    </div>
                </div>

                {{-- payment_method --}}
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">
                        Metodă de plată
                    </label>
                    <select id="payment_method" name="payment_method"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('payment_method') border-red-500 @enderror">
                        <option value="">— Selectați —</option>
                        <option value="bank_transfer" {{ old('payment_method', $subscription->payment_method) === 'bank_transfer' ? 'selected' : '' }}>Transfer bancar</option>
                        <option value="cash" {{ old('payment_method', $subscription->payment_method) === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ old('payment_method', $subscription->payment_method) === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="other" {{ old('payment_method', $subscription->payment_method) === 'other' ? 'selected' : '' }}>Altele</option>
                    </select>
                    @error('payment_method')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- notes --}}
            <div class="mt-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                    Note
                </label>
                <textarea id="notes" name="notes" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('notes') border-red-500 @enderror">{{ old('notes', $subscription->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Footer --}}
            <div class="mt-8 flex items-center justify-between gap-3">
                <form method="POST"
                      action="{{ route('admin.subscriptions.suspend', $subscription) }}"
                      onsubmit="return confirm('Ești sigur că vrei să suspendezi acest abonament?')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium border border-red-300 text-red-700 hover:bg-red-50 transition-colors">
                        ⏸ Suspendă abonament
                    </button>
                </form>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.subscriptions.history', $subscription->location) }}"
                       class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Anulează
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-5 py-2 rounded-lg text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                        💾 Salvează modificările
                    </button>
                </div>
            </div>

        </form>
    </div>

</div>

<script>
function onPlanChange(select) {
    const option = select.options[select.selectedIndex];
    const price = option.dataset.price;
    const months = option.dataset.months;
    const info = document.getElementById('plan-info');

    if (select.value) {
        info.textContent = `Preț: ${parseFloat(price).toFixed(2)} RON · Durată: ${months} luni`;
        info.classList.remove('hidden');
    } else {
        info.classList.add('hidden');
    }
}

function setDuration(months) {
    const start = document.getElementById('starts_at').value;
    if (!start) return;
    const date = new Date(start);
    date.setMonth(date.getMonth() + months);
    document.getElementById('expires_at').value = date.toISOString().split('T')[0];
}

// Show plan info on load if a plan is pre-selected
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('plan_id');
    if (select && select.value) onPlanChange(select);
});
</script>
@endsection
