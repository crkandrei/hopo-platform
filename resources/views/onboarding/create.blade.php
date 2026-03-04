@extends('layouts.app')

@section('title', 'Client Nou')
@section('page-title', 'Client Nou')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Onboarding Client Nou</h1>
                <p class="text-gray-600 text-lg">Creează companie, locație și administrator într-un singur pas</p>
            </div>
            <a href="{{ route('companies.index') }}"
               class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium flex items-center shadow-md">
                <i class="fas fa-arrow-left mr-2"></i>
                Înapoi
            </a>
        </div>
    </div>

    <!-- Progress indicator -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            @foreach([1 => 'Companie', 2 => 'Locație', 3 => 'Administrator', 4 => 'Sumar'] as $num => $label)
            <div class="flex items-center {{ $num < 4 ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center">
                    <div id="step-indicator-{{ $num }}"
                         class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all
                                {{ $num === 1 ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-400 border-gray-300' }}">
                        {{ $num }}
                    </div>
                    <span id="step-label-{{ $num }}"
                          class="mt-1 text-xs font-medium {{ $num === 1 ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $label }}
                    </span>
                </div>
                @if($num < 4)
                <div id="step-line-{{ $num }}" class="flex-1 h-0.5 mx-3 transition-all {{ $num === 1 ? 'bg-gray-200' : 'bg-gray-200' }}"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- Validation errors (server-side) -->
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li class="text-sm text-red-600">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Wizard Form -->
    <form method="POST" action="{{ route('onboarding.store') }}" id="onboarding-form">
        @csrf

        <!-- STEP 1: Company -->
        <div id="step-1" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-building text-green-600"></i>
                Pasul 1 — Companie
            </h2>

            <div>
                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nume Companie <span class="text-red-500">*</span>
                </label>
                <input type="text" name="company_name" id="company_name"
                       value="{{ old('company_name') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                @error('company_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="company_email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email Companie
                </label>
                <input type="email" name="company_email" id="company_email"
                       value="{{ old('company_email') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                @error('company_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="company_phone" class="block text-sm font-medium text-gray-700 mb-2">
                    Telefon Companie
                </label>
                <input type="text" name="company_phone" id="company_phone"
                       value="{{ old('company_phone') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                @error('company_phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="button" onclick="goToStep(2)"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium flex items-center gap-2">
                    Continuă
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- STEP 2: Location -->
        <div id="step-2" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-green-600"></i>
                Pasul 2 — Prima Locație
            </h2>

            <div>
                <label for="location_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nume Locație <span class="text-red-500">*</span>
                </label>
                <input type="text" name="location_name" id="location_name"
                       value="{{ old('location_name') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                @error('location_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="location_address" class="block text-sm font-medium text-gray-700 mb-2">
                    Adresă
                </label>
                <input type="text" name="location_address" id="location_address"
                       value="{{ old('location_address') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                @error('location_address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="location_phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Telefon Locație
                    </label>
                    <input type="text" name="location_phone" id="location_phone"
                           value="{{ old('location_phone') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    @error('location_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="location_email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Locație
                    </label>
                    <input type="email" name="location_email" id="location_email"
                           value="{{ old('location_email') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    @error('location_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="price_per_hour" class="block text-sm font-medium text-gray-700 mb-2">
                    Preț / Oră (RON) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="price_per_hour" id="price_per_hour"
                       value="{{ old('price_per_hour') }}"
                       step="0.01" min="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                @error('price_per_hour')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-3">
                <label class="flex items-center gap-3">
                    <input type="hidden" name="bracelet_required" value="0">
                    <input type="checkbox" name="bracelet_required" id="bracelet_required" value="1"
                           {{ old('bracelet_required') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="text-sm text-gray-700">Brățări RFID obligatorii</span>
                </label>

                <label class="flex items-center gap-3">
                    <input type="hidden" name="fiscal_enabled" value="0">
                    <input type="checkbox" name="fiscal_enabled" id="fiscal_enabled" value="1"
                           {{ old('fiscal_enabled') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="text-sm text-gray-700">Fiscal activat</span>
                </label>
            </div>

            <div class="flex justify-between">
                <button type="button" onclick="goToStep(1)"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Înapoi
                </button>
                <button type="button" onclick="goToStep(3)"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium flex items-center gap-2">
                    Continuă
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- STEP 3: Admin user -->
        <div id="step-3" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-user-shield text-green-600"></i>
                Pasul 3 — Administrator
            </h2>

            <div>
                <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nume Complet <span class="text-red-500">*</span>
                </label>
                <input type="text" name="admin_name" id="admin_name"
                       value="{{ old('admin_name') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                @error('admin_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="admin_username" class="block text-sm font-medium text-gray-700 mb-2">
                    Username <span class="text-red-500">*</span>
                </label>
                <input type="text" name="admin_username" id="admin_username"
                       value="{{ old('admin_username') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <p class="mt-1 text-xs text-gray-500">Doar litere, cifre și underscore (_)</p>
                @error('admin_username')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="admin_email" id="admin_email"
                       value="{{ old('admin_email') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                @error('admin_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Parolă <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="admin_password" id="admin_password"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <p class="mt-1 text-xs text-gray-500">Minim 8 caractere</p>
                    @error('admin_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="admin_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirmă Parola <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="admin_password_confirmation" id="admin_password_confirmation"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <div class="flex justify-between">
                <button type="button" onclick="goToStep(2)"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Înapoi
                </button>
                <button type="button" onclick="goToStep(4)"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium flex items-center gap-2">
                    Continuă
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- STEP 4: Summary -->
        <div id="step-4" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-check-circle text-green-600"></i>
                Pasul 4 — Sumar
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Company summary -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-building text-green-600 text-sm"></i>
                        Companie
                    </h3>
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-gray-500">Nume</dt>
                            <dd id="summary-company-name" class="font-medium text-gray-900">—</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Email</dt>
                            <dd id="summary-company-email" class="font-medium text-gray-900">—</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Telefon</dt>
                            <dd id="summary-company-phone" class="font-medium text-gray-900">—</dd>
                        </div>
                    </dl>
                </div>

                <!-- Location summary -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-green-600 text-sm"></i>
                        Locație
                    </h3>
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-gray-500">Nume</dt>
                            <dd id="summary-location-name" class="font-medium text-gray-900">—</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Adresă</dt>
                            <dd id="summary-location-address" class="font-medium text-gray-900">—</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Preț/oră</dt>
                            <dd id="summary-price" class="font-medium text-gray-900">—</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Brățări / Fiscal</dt>
                            <dd id="summary-toggles" class="font-medium text-gray-900">—</dd>
                        </div>
                    </dl>
                </div>

                <!-- Admin summary -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-user-shield text-green-600 text-sm"></i>
                        Administrator
                    </h3>
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-gray-500">Nume</dt>
                            <dd id="summary-admin-name" class="font-medium text-gray-900">—</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Username</dt>
                            <dd id="summary-admin-username" class="font-medium text-gray-900">—</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Email</dt>
                            <dd id="summary-admin-email" class="font-medium text-gray-900">—</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="flex justify-between">
                <button type="button" onclick="goToStep(3)"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Înapoi
                </button>
                <button type="submit"
                        class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold flex items-center gap-2 shadow-md">
                    <i class="fas fa-check"></i>
                    Finalizează &amp; Creează Client
                </button>
            </div>
        </div>

    </form>
</div>

<script>
let currentStep = 1;

function detectErrorStep() {
    @if($errors->any())
        @php $firstKey = $errors->keys()[0] ?? ''; @endphp
        @if(in_array($firstKey, ['company_name', 'company_email', 'company_phone']))
            return 1;
        @elseif(in_array($firstKey, ['location_name', 'location_address', 'location_phone', 'location_email', 'price_per_hour', 'bracelet_required', 'fiscal_enabled']))
            return 2;
        @else
            return 3;
        @endif
    @endif
    return 1;
}

function goToStep(step) {
    // Client-side validation before advancing
    if (step > currentStep) {
        if (!validateStep(currentStep)) return;
    }

    // Hide current, show new
    document.getElementById('step-' + currentStep).classList.add('hidden');
    document.getElementById('step-' + step).classList.remove('hidden');

    // Update progress indicators
    for (let i = 1; i <= 4; i++) {
        const indicator = document.getElementById('step-indicator-' + i);
        const label = document.getElementById('step-label-' + i);
        if (i < step) {
            indicator.className = 'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all bg-green-600 text-white border-green-600';
            label.className = 'mt-1 text-xs font-medium text-green-600';
            indicator.innerHTML = '<i class="fas fa-check text-xs"></i>';
        } else if (i === step) {
            indicator.className = 'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all bg-green-600 text-white border-green-600';
            label.className = 'mt-1 text-xs font-medium text-green-600';
            indicator.innerHTML = i;
        } else {
            indicator.className = 'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all bg-white text-gray-400 border-gray-300';
            label.className = 'mt-1 text-xs font-medium text-gray-400';
            indicator.innerHTML = i;
        }
    }

    // Update connector lines
    for (let i = 1; i <= 3; i++) {
        const line = document.getElementById('step-line-' + i);
        line.className = 'flex-1 h-0.5 mx-3 transition-all ' + (i < step ? 'bg-green-600' : 'bg-gray-200');
    }

    if (step === 4) populateSummary();
    currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validateStep(step) {
    const errors = [];

    if (step === 1) {
        const name = document.getElementById('company_name').value.trim();
        if (!name) errors.push('Numele companiei este obligatoriu.');
    }

    if (step === 2) {
        const name = document.getElementById('location_name').value.trim();
        const price = document.getElementById('price_per_hour').value.trim();
        if (!name) errors.push('Numele locației este obligatoriu.');
        if (!price || isNaN(price) || parseFloat(price) < 0) errors.push('Prețul/oră este obligatoriu și trebuie să fie un număr pozitiv.');
    }

    if (step === 3) {
        const name = document.getElementById('admin_name').value.trim();
        const username = document.getElementById('admin_username').value.trim();
        const email = document.getElementById('admin_email').value.trim();
        const password = document.getElementById('admin_password').value;
        const confirm = document.getElementById('admin_password_confirmation').value;
        if (!name) errors.push('Numele administratorului este obligatoriu.');
        if (!username) errors.push('Username-ul este obligatoriu.');
        if (!email) errors.push('Email-ul administratorului este obligatoriu.');
        if (!password || password.length < 8) errors.push('Parola trebuie să aibă cel puțin 8 caractere.');
        if (password !== confirm) errors.push('Parolele nu coincid.');
    }

    if (errors.length > 0) {
        alert(errors.join('\n'));
        return false;
    }
    return true;
}

function populateSummary() {
    const val = (id) => document.getElementById(id).value.trim() || '—';
    const checked = (id) => document.getElementById(id).checked;

    document.getElementById('summary-company-name').textContent = val('company_name');
    document.getElementById('summary-company-email').textContent = val('company_email');
    document.getElementById('summary-company-phone').textContent = val('company_phone');

    document.getElementById('summary-location-name').textContent = val('location_name');
    document.getElementById('summary-location-address').textContent = val('location_address');
    document.getElementById('summary-price').textContent = val('price_per_hour') !== '—' ? val('price_per_hour') + ' RON' : '—';

    const bracelet = checked('bracelet_required') ? 'Da' : 'Nu';
    const fiscal = checked('fiscal_enabled') ? 'Da' : 'Nu';
    document.getElementById('summary-toggles').textContent = `Brățări: ${bracelet} / Fiscal: ${fiscal}`;

    document.getElementById('summary-admin-name').textContent = val('admin_name');
    document.getElementById('summary-admin-username').textContent = val('admin_username');
    document.getElementById('summary-admin-email').textContent = val('admin_email');
}

// Initialize: if there are server-side errors, go to the right step
document.addEventListener('DOMContentLoaded', function () {
    const errorStep = detectErrorStep();
    if (errorStep !== 1) {
        document.getElementById('step-1').classList.add('hidden');
        document.getElementById('step-' + errorStep).classList.remove('hidden');
        // Update indicators without validation
        for (let i = 1; i <= 4; i++) {
            const indicator = document.getElementById('step-indicator-' + i);
            const label = document.getElementById('step-label-' + i);
            if (i < errorStep) {
                indicator.className = 'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all bg-green-600 text-white border-green-600';
                label.className = 'mt-1 text-xs font-medium text-green-600';
                indicator.innerHTML = '<i class="fas fa-check text-xs"></i>';
            } else if (i === errorStep) {
                indicator.className = 'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all bg-red-500 text-white border-red-500';
                label.className = 'mt-1 text-xs font-medium text-red-500';
                indicator.innerHTML = i;
            } else {
                indicator.className = 'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all bg-white text-gray-400 border-gray-300';
                label.className = 'mt-1 text-xs font-medium text-gray-400';
                indicator.innerHTML = i;
            }
        }
        for (let i = 1; i <= 3; i++) {
            const line = document.getElementById('step-line-' + i);
            line.className = 'flex-1 h-0.5 mx-3 transition-all ' + (i < errorStep ? 'bg-green-600' : 'bg-gray-200');
        }
        currentStep = errorStep;
    }
});
</script>
@endsection
