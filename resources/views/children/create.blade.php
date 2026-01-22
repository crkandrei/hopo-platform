@extends('layouts.app')

@section('title', 'Adaugă Copil Nou')
@section('page-title', 'Adaugă Copil Nou')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <a href="{{ route('children.index') }}" 
               class="text-gray-400 hover:text-gray-600 mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Adaugă Copil Nou</h1>
                <p class="text-gray-600">Completează informațiile pentru noul copil</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('children.store') }}" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nume complet <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name') }}"
                           maxlength="255"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                           placeholder="Ex: Andrei Popescu"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                <!-- Guardian Selection -->
                <div>
                    <label for="guardian_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Părinte/Tutore <span class="text-red-500">*</span>
                    </label>
                    <select id="guardian_id" 
                            name="guardian_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('guardian_id') border-red-500 @enderror"
                            required>
                        <option value="">Selectează părintele...</option>
                        @foreach($guardians as $guardian)
                            <option value="{{ $guardian->id }}" 
                                {{ (old('guardian_id', $preselectedGuardianId ?? null) == $guardian->id) ? 'selected' : '' }}>
                                {{ $guardian->name }} 
                                @if($guardian->phone)
                                    - {{ $guardian->phone }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('guardian_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        @if($guardians->count() == 0)
                            <span class="text-red-600">Nu există părinți înregistrați. 
                                <a href="{{ route('guardians.create') }}" class="text-indigo-600 hover:text-indigo-500">Adaugă un părinte</a>
                            </span>
                        @else
                            Alegeți părintele copilului
                        @endif
                    </p>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Note (opțional)
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('notes') border-red-500 @enderror"
                          placeholder="Informații suplimentare despre copil...">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Máximo 1000 caractere</p>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('children.index') }}" 
                   class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 font-medium">
                    Anulează
                </a>
                <button type="submit" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium">
                    Adaugă Copilul
                </button>
            </div>
        </form>
    </div>

    <!-- Guardian Quick Add -->
    @if($guardians->count() == 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">
                    Nu există părinți înregistrați
                </h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>Pentru a adăuga un copil, trebuie să existe cel puțin un părinte înregistrat.</p>
                </div>
                <div class="mt-4">
                    <a href="{{ route('guardians.create') }}" 
                       class="bg-yellow-100 text-yellow-800 px-3 py-2 rounded-md text-sm font-medium hover:bg-yellow-200">
                        Adaugă Primul Părinte
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@section('scripts')
<script>
    // Character counter for notes
    const notesTextarea = document.getElementById('notes');
    const maxLength = 1000;
    
    notesTextarea.addEventListener('input', function() {
        const remaining = maxLength - this.value.length;
        const counter = document.getElementById('notes-counter') || createCounter();
        counter.textContent = `${remaining} caractere rămase`;
        
        if (remaining < 50) {
            counter.className = 'mt-1 text-sm text-red-500';
        } else {
            counter.className = 'mt-1 text-sm text-gray-500';
        }
    });

    function createCounter() {
        const counter = document.createElement('p');
        counter.id = 'notes-counter';
        counter.className = 'mt-1 text-sm text-gray-500';
        notesTextarea.parentNode.appendChild(counter);
        return counter;
    }

</script>
@endsection

