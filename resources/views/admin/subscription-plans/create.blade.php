@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Plan nou de abonament</h1>
        <p class="mt-1 text-sm text-gray-500">La salvare, planul va fi sincronizat automat cu Stripe.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.subscription-plans.store') }}">
            @csrf

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nume plan <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-400 @enderror"
                        placeholder="ex: STANDARD">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Preț (RON) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price') }}" required min="0" step="0.01"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('price') border-red-400 @enderror"
                            placeholder="199.00">
                        @error('price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Durată (luni) <span class="text-red-500">*</span></label>
                        <input type="number" name="duration_months" value="{{ old('duration_months') }}" required min="1"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('duration_months') border-red-400 @enderror"
                            placeholder="12">
                        @error('duration_months') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordine afișare</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Funcționalități (câte una pe linie)
                    </label>
                    <textarea name="features" rows="5"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Acces complet la platformă&#10;Suport email prioritar&#10;Statistici avansate">{{ old('features') }}</textarea>
                    <p class="mt-1 text-xs text-gray-400">Fiecare linie devine un punct în lista de funcționalități.</p>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                    class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                    Creează plan
                </button>
                <a href="{{ route('admin.subscription-plans.index') }}"
                   class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Anulează
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
