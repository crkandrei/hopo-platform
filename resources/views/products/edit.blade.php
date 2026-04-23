@extends('layouts.app')

@section('title', 'Editează Produs')
@section('page-title', 'Editează Produs')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Editează Produs</h1>
            <p class="text-gray-600">Actualizează informațiile produsului</p>
        </div>

        <form method="POST" action="{{ route('products.update', $product->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nume Produs <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $product->name) }}" 
                       maxlength="255" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror" 
                       placeholder="Ex: Sosete antiderapante" 
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                    Preț Unitate (RON) <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       id="price" 
                       name="price" 
                       value="{{ old('price', $product->price) }}" 
                       step="0.01" 
                       min="0" 
                       max="999999.99"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('price') border-red-500 @enderror" 
                       placeholder="Ex: 15.50" 
                       required>
                @error('price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Modificarea prețului nu afectează sesiunile existente</p>
            </div>

            <div>
                <label for="tva_rate_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Cotă TVA
                </label>
                <select id="tva_rate_id" name="tva_rate_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">— fără cotă TVA (clasa 1 implicit) —</option>
                    @foreach($tvaRates as $rate)
                        <option value="{{ $rate->id }}" {{ old('tva_rate_id', $product->tva_rate_id) == $rate->id ? 'selected' : '' }}>
                            Clasa {{ $rate->vat_class }} — {{ $rate->name }} ({{ number_format($rate->percentage, 2, ',', '.') }}%)
                        </option>
                    @endforeach
                </select>
                @error('tva_rate_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <div class="flex items-center">
                    <input type="checkbox"
                           id="has_sgr"
                           name="has_sgr"
                           value="1"
                           {{ old('has_sgr', $product->has_sgr) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="has_sgr" class="ml-2 block text-sm text-gray-900">
                        Garantie SGR ({{ number_format(\App\Models\Product::SGR_VALUE, 2, ',', '.') }} RON / buc, TVA 0%)
                    </label>
                </div>
                <p class="mt-1 text-sm text-gray-500">Bifați pentru sucuri, ape și alte băuturi cu recipient SGR. Garantia se adaugă automat pe bon.</p>
            </div>

            <div>
                <div class="flex items-center">
                    <input type="checkbox"
                           id="is_active"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Produs activ (disponibil pentru sesiuni)
                    </label>
                </div>
                <p class="mt-1 text-sm text-gray-500">Produsele inactive nu pot fi adăugate la sesiuni noi</p>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('products.index') }}" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                    Anulează
                </a>
                <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                    <i class="fas fa-save mr-2"></i>
                    Actualizează Produs
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

