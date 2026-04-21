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
        <form method="POST" action="{{ route('companies.update', $company) }}" enctype="multipart/form-data">
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

                <!-- Logo -->
                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                        Logo Companie
                    </label>

                    @if($company->logo_path)
                        <div class="mb-3 flex items-center gap-4">
                            <img src="{{ $company->logoUrl() }}" alt="Logo actual" class="h-14 w-auto rounded border border-gray-200 p-1">
                            <button type="button"
                                    onclick="if(confirm('Ești sigur că vrei să ștergi logo-ul?')) document.getElementById('delete-logo-form').submit();"
                                    class="text-sm text-red-600 hover:text-red-800 underline">
                                Șterge logo
                            </button>
                        </div>
                    @endif

                    <input type="file"
                           name="logo"
                           id="logo"
                           accept="image/png,image/jpeg,image/webp"
                           class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500">PNG, JPG sau WebP, max 2 MB. Lasă gol pentru a păstra logo-ul existent.</p>
                    @error('logo')
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

                <!-- Planuri abonament disponibile (super admin only) -->
                @if(Auth::user()->isSuperAdmin() && $allPlans->isNotEmpty())
                <div class="mt-2 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                    <label class="block text-sm font-medium text-gray-900 mb-1">
                        Planuri de abonament disponibile
                    </label>
                    <p class="text-xs text-gray-500 mb-3">
                        Selectează ce planuri poate alege această companie la checkout. Dacă nu selectezi nimic, vor fi disponibile toate planurile active.
                    </p>
                    <div class="space-y-2">
                        @foreach($allPlans as $plan)
                            <label class="flex items-center gap-3">
                                <input type="checkbox"
                                       name="subscription_plan_ids[]"
                                       value="{{ $plan->id }}"
                                       {{ in_array($plan->id, $selectedPlanIds) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">
                                    <span class="font-medium">{{ $plan->name }}</span>
                                    <span class="text-gray-400 ml-1">— {{ number_format($plan->price, 0, ',', '.') }} RON / {{ $plan->duration_months }} {{ $plan->duration_months === 1 ? 'lună' : 'luni' }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
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

@if($company->logo_path)
{{-- Formular separat pentru ștergere logo — în afara formularului principal pentru a evita forme imbricate --}}
<form id="delete-logo-form" method="POST" action="{{ route('companies.logo.delete', $company) }}" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endif

@endsection
