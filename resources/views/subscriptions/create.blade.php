@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Abonament Nou 💳</h1>
                <p class="mt-1 text-sm text-gray-500">Adăugați un abonament nou pentru locație</p>
            </div>
            <a href="{{ route('admin.subscriptions.index') }}"
               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                ← Înapoi
            </a>
        </div>
    </div>

    {{-- Location info card --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="text-base font-semibold text-blue-900">📍 {{ $location->name }}</div>
        <div class="text-sm text-blue-700 mt-1">{{ $location->company->name ?? '—' }}</div>
    </div>

    {{-- Form card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.subscriptions.store', $location) }}">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- starts_at --}}
                <div>
                    <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-1">
                        Data de început <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="starts_at" name="starts_at"
                           value="{{ old('starts_at', now()->toDateString()) }}"
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
                           value="{{ old('expires_at') }}"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('expires_at') border-red-500 @enderror">
                    @error('expires_at')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- price_paid --}}
                <div>
                    <label for="price_paid" class="block text-sm font-medium text-gray-700 mb-1">
                        Preț plătit (RON)
                    </label>
                    <input type="number" id="price_paid" name="price_paid"
                           value="{{ old('price_paid') }}"
                           step="0.01" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('price_paid') border-red-500 @enderror">
                    @error('price_paid')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- payment_method --}}
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">
                        Metodă de plată
                    </label>
                    <select id="payment_method" name="payment_method"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('payment_method') border-red-500 @enderror">
                        <option value="">— Selectați —</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Transfer bancar</option>
                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Altele</option>
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
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Footer --}}
            <div class="mt-8 flex items-center justify-end gap-3">
                <a href="{{ route('admin.subscriptions.index') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                    Anulează
                </a>
                <button type="submit"
                        class="inline-flex items-center px-5 py-2 rounded-lg text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                    💾 Salvează abonament
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
