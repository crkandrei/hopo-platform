@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Cotă TVA nouă</h1>
        <p class="mt-1 text-sm text-gray-500">Adaugă o nouă cotă TVA în nomenclator</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.tva-rates.store') }}">
            @csrf

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nume <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-400 @enderror"
                        placeholder="ex: Cotă standard">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Procent (%) <span class="text-red-500">*</span></label>
                        <input type="number" name="percentage" value="{{ old('percentage') }}" required min="0" max="100" step="0.01"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('percentage') border-red-400 @enderror"
                            placeholder="19.00">
                        @error('percentage') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Clasă VAT (1-9) <span class="text-red-500">*</span></label>
                        <input type="number" name="vat_class" value="{{ old('vat_class') }}" required min="1" max="9"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('vat_class') border-red-400 @enderror"
                            placeholder="1">
                        <p class="mt-1 text-xs text-gray-400">Index unic folosit în bonul fiscal (1–9)</p>
                        @error('vat_class') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        {{ old('is_active', '1') ? 'checked' : '' }}>
                    <label for="is_active" class="text-sm font-medium text-gray-700">Activ</label>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                    class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                    Salvează
                </button>
                <a href="{{ route('admin.tva-rates.index') }}"
                   class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Anulează
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
