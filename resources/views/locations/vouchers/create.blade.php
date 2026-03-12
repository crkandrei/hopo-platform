@extends('layouts.app')

@section('title', 'Generare voucher')
@section('page-title', 'Generare voucher')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Generare voucher nou</h1>
                <p class="text-gray-600">Locație: {{ $location->name }}</p>
            </div>
            <a href="{{ route('locations.vouchers.index', $location) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Înapoi
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('locations.vouchers.store', $location) }}">
            @csrf
            <div class="space-y-4 max-w-xl">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tip <span class="text-red-500">*</span></label>
                    <select name="type" id="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="amount" {{ old('type') === 'amount' ? 'selected' : '' }}>Sumă (RON)</option>
                        <option value="hours" {{ old('type') === 'hours' ? 'selected' : '' }}>Ore</option>
                    </select>
                    @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="initial_value" class="block text-sm font-medium text-gray-700 mb-1">Valoare <span class="text-red-500">*</span></label>
                    <input type="number" name="initial_value" id="initial_value" value="{{ old('initial_value') }}" step="0.01" min="0.01" max="9999.99" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Ex: 50 sau 2.5">
                    @error('initial_value')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-sm text-gray-500">RON pentru tip Sumă, ore pentru tip Ore (ex: 2.5).</p>
                </div>
                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">Data expirare</label>
                    <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at') }}" min="{{ now()->addDay()->format('Y-m-d') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @error('expires_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                    <textarea name="notes" id="notes" rows="2" maxlength="500" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Opțional">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium">Generează</button>
            </div>
        </form>
        <p class="mt-4 text-sm text-gray-500">Codul voucher va fi generat automat (8 caractere, unic per locație).</p>
    </div>
</div>
@endsection
