@extends('layouts.app')

@section('title', 'Editează Locație')
@section('page-title', 'Editează Locație')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Editează Locație 📍</h1>
                <p class="text-gray-600 text-lg">Actualizați informațiile locației</p>
            </div>
            <a href="{{ route('locations.index') }}" 
               class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium flex items-center shadow-md">
                <i class="fas fa-arrow-left mr-2"></i>
                Înapoi
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('locations.update', $location) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Company Selection (only for Super Admin) -->
                @if(Auth::user()->isSuperAdmin() && $companies)
                <div>
                    <label for="company_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Companie <span class="text-red-500">*</span>
                    </label>
                    <select name="company_id" 
                            id="company_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Selectați companie --</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id', $location->company_id) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nume Locație <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name"
                           value="{{ old('name', $location->name) }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Adresă
                    </label>
                    <textarea name="address" 
                              id="address"
                              rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('address', $location->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Telefon
                    </label>
                    <input type="text" 
                           name="phone" 
                           id="phone"
                           value="{{ old('phone', $location->phone) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email"
                           value="{{ old('email', $location->email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price Per Hour -->
                <div>
                    <label for="price_per_hour" class="block text-sm font-medium text-gray-700 mb-2">
                        Tarif pe Oră (RON) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="price_per_hour" 
                           id="price_per_hour"
                           step="0.01"
                           min="0"
                           value="{{ old('price_per_hour', $location->price_per_hour) }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('price_per_hour')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $location->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Locație activă</span>
                    </label>
                </div>

                <!-- Bracelet Required -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="bracelet_required"
                               value="1"
                               {{ old('bracelet_required', $location->bracelet_required ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Sesiunile necesită brățară</span>
                    </label>
                </div>

                <!-- Fiscal Enabled -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="fiscal_enabled"
                               value="1"
                               {{ old('fiscal_enabled', $location->fiscal_enabled ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Locația folosește fiscalizare (bon fiscal)</span>
                    </label>
                </div>

                <!-- Birthday Concurrent Reservations (Super Admin only) -->
                @if(Auth::user()->isSuperAdmin())
                <div>
                    <label class="flex items-start gap-2">
                        <input type="checkbox"
                               name="birthday_concurrent_reservations"
                               value="1"
                               {{ old('birthday_concurrent_reservations', $location->birthday_concurrent_reservations ?? false) ? 'checked' : '' }}
                               class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">
                            Permite rezervări concomitente pentru zile de naștere
                            <span class="block text-xs text-gray-400 mt-0.5">Părinții pot rezerva orice oră fără a vedea sau fi blocați de alte rezervări existente în aceeași zi.</span>
                        </span>
                    </label>
                </div>
                @endif

                <!-- Submit Button -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('locations.index') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Anulează
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-save mr-2"></i>
                        Actualizează Locație
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
