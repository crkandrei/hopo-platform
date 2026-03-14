@extends('layouts.app')

@section('title', 'Editează Companie')
@section('page-title', 'Editează Companie')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Editează Companie 🏢</h1>
                <p class="text-gray-600 text-lg">Actualizați informațiile companiei</p>
            </div>
            <a href="{{ route('companies.index') }}" 
               class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium flex items-center shadow-md">
                <i class="fas fa-arrow-left mr-2"></i>
                Înapoi
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('companies.update', $company) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nume Companie <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name"
                           value="{{ old('name', $company->name) }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')
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
                           value="{{ old('email', $company->email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('email')
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
                           value="{{ old('phone', $company->phone) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $company->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Companie activă</span>
                    </label>
                </div>

                <!-- Daily Report Enabled -->
                @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin())
                <div class="mt-2 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="daily_report_enabled"
                               value="1"
                               {{ old('daily_report_enabled', $company->daily_report_enabled) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-3">
                            <span class="block text-sm font-medium text-gray-900">Activează raport zilnic pe email</span>
                            <span class="block text-xs text-gray-600 mt-1">
                                Raportul zilei precedente va fi trimis zilnic la 07:00 pe adresa de email a companiei
                            </span>
                        </span>
                    </label>
                </div>
                @endif

                <!-- Submit Button -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('companies.index') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Anulează
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-save mr-2"></i>
                        Actualizează Companie
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
