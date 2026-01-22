@extends('layouts.app')

@section('title', 'Editează Utilizator')
@section('page-title', 'Editează Utilizator')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Editează Utilizator 👤</h1>
                <p class="text-gray-600 text-lg">Modificați informațiile utilizatorului</p>
            </div>
            <a href="{{ route('users.index') }}" 
               class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium flex items-center shadow-md">
                <i class="fas fa-arrow-left mr-2"></i>
                Înapoi
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nume Complet <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name"
                           value="{{ old('name', $user->name) }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="username" 
                           id="username"
                           value="{{ old('username', $user->username) }}"
                           required
                           pattern="[a-zA-Z0-9_]+"
                           minlength="3"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Doar litere, cifre și underscore, minim 3 caractere</p>
                    @error('username')
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
                           value="{{ old('email', $user->email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Parolă Nouă (lăsați gol pentru a păstra parola existentă)
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password"
                           minlength="8"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Minim 8 caractere (opțional)</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirmă Parola Nouă
                    </label>
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation"
                           minlength="8"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Role -->
                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Rol <span class="text-red-500">*</span>
                    </label>
                    <select name="role_id" 
                            id="role_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Selectează rolul</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }} ({{ $role->name }})
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Company -->
                <div id="company-field">
                    <label for="company_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Companie
                    </label>
                    <select name="company_id" 
                            id="company_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Selectează compania</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location -->
                <div id="location-field">
                    <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Locație
                    </label>
                    <select name="location_id" 
                            id="location_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Selectează locația</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" 
                                    data-company-id="{{ $location->company_id }}"
                                    {{ old('location_id', $user->location_id) == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" 
                            id="status"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Activ</option>
                        <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactiv</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('users.index') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Anulează
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-save mr-2"></i>
                        Actualizează Utilizator
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Show/hide company and location fields based on role
    const roleSelect = document.getElementById('role_id');
    const companyField = document.getElementById('company-field');
    const locationField = document.getElementById('location-field');
    const companySelect = document.getElementById('company_id');
    const locationSelect = document.getElementById('location_id');
    
    // Get all location options (store original options)
    const allLocationOptions = Array.from(locationSelect.options).filter(opt => opt.value !== '');
    
    function updateFieldsVisibility() {
        const roleText = roleSelect.options[roleSelect.selectedIndex]?.text || '';
        
        if (roleText.includes('SUPER_ADMIN')) {
            companyField.style.display = 'none';
            locationField.style.display = 'none';
            companySelect.value = '';
            locationSelect.value = '';
        } else if (roleText.includes('COMPANY_ADMIN')) {
            companyField.style.display = 'block';
            locationField.style.display = 'none';
            locationSelect.value = '';
            // Reset locations when switching to COMPANY_ADMIN
            resetLocations();
        } else if (roleText.includes('STAFF')) {
            companyField.style.display = 'none';
            locationField.style.display = 'block';
            companySelect.value = '';
            // Show all available locations for STAFF
            showAllLocations();
        } else {
            companyField.style.display = 'block';
            locationField.style.display = 'block';
            // Filter by company if one is selected
            filterLocations();
        }
    }
    
    // Show all available locations (for STAFF role)
    function showAllLocations() {
        locationSelect.innerHTML = '<option value="">Selectează locația</option>';
        allLocationOptions.forEach(option => {
            locationSelect.appendChild(option.cloneNode(true));
        });
    }
    
    // Reset locations to empty
    function resetLocations() {
        locationSelect.innerHTML = '<option value="">Selectează locația</option>';
    }
    
    // Filter locations based on selected company
    function filterLocations() {
        const companyId = companySelect.value;
        
        // Clear current options except the first one
        locationSelect.innerHTML = '<option value="">Selectează locația</option>';
        
        // If no company selected, show all locations
        if (!companyId) {
            showAllLocations();
            return;
        }
        
        // Add filtered locations
        allLocationOptions.forEach(option => {
            if (option.dataset.companyId === companyId) {
                locationSelect.appendChild(option.cloneNode(true));
            }
        });
    }
    
    roleSelect.addEventListener('change', updateFieldsVisibility);
    companySelect.addEventListener('change', filterLocations);
    
    // Initialize on page load
    updateFieldsVisibility();
</script>
@endsection
