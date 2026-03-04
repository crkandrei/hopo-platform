@extends('layouts.app')

@section('title', 'Editează sală')
@section('page-title', 'Editează sală')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Editează sală</h1>
                <p class="text-gray-600">Locație: {{ $location->name }}</p>
            </div>
            <a href="{{ route('locations.birthday-halls.index', $location) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Înapoi
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('locations.birthday-halls.update', [$location, $birthdayHall]) }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nume sală <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $birthdayHall->name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descriere</label>
                    <textarea name="description" id="description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('description', $birthdayHall->description) }}</textarea>
                </div>
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">Capacitate (copii) <span class="text-red-500">*</span></label>
                    <input type="number" name="capacity" id="capacity" value="{{ old('capacity', $birthdayHall->capacity) }}" min="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @error('capacity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="booking_mode" class="block text-sm font-medium text-gray-700 mb-1">Mod rezervare <span class="text-red-500">*</span></label>
                    <select name="booking_mode" id="booking_mode" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="slots" {{ old('booking_mode', $birthdayHall->booking_mode ?? 'slots') === 'slots' ? 'selected' : '' }}>Pe sloturi (intervale fixe)</option>
                        <option value="flexible" {{ old('booking_mode', $birthdayHall->booking_mode ?? 'slots') === 'flexible' ? 'selected' : '' }}>Orar liber (orice oră)</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Sloturi: clienții aleg dintr-un interval fix. Orar liber: fără sloturi, clienții aleg ora dorită.</p>
                    @error('booking_mode')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $birthdayHall->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Activ</span>
                    </label>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium">Actualizează</button>
            </div>
        </form>
    </div>
</div>
@endsection
