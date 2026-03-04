@extends('layouts.app')

@section('title', 'Editează slot')
@section('page-title', 'Editează slot')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center">
            <div>
                <nav class="text-sm text-gray-500 mb-1">
                    <a href="{{ route('locations.show', $location) }}" class="hover:text-gray-700">{{ $location->name }}</a>
                    <span class="mx-1">/</span>
                    <a href="{{ route('locations.birthday-halls.index', $location) }}" class="hover:text-gray-700">Săli</a>
                    <span class="mx-1">/</span>
                    <a href="{{ route('birthday-halls.time-slots.index', $birthdayHall) }}" class="hover:text-gray-700">{{ $birthdayHall->name }}</a>
                    <span class="mx-1">/</span>
                    <span class="text-gray-900 font-medium">Editează slot</span>
                </nav>
                <h1 class="text-3xl font-bold text-gray-900">Editează slot orar</h1>
            </div>
            <a href="{{ route('birthday-halls.time-slots.index', $birthdayHall) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Înapoi
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('birthday-halls.time-slots.update', [$birthdayHall, $timeSlot]) }}">
            @csrf
            @method('PUT')
            <div class="space-y-4 max-w-md">
                <div>
                    <label for="day_of_week" class="block text-sm font-medium text-gray-700 mb-1">Zi</label>
                    <select name="day_of_week" id="day_of_week" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">Orice zi</option>
                        @foreach([0 => 'Luni', 1 => 'Marți', 2 => 'Miercuri', 3 => 'Joi', 4 => 'Vineri', 5 => 'Sâmbătă', 6 => 'Duminică'] as $d => $label)
                            <option value="{{ $d }}" {{ old('day_of_week', $timeSlot->day_of_week) === (string)$d ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Ora început</label>
                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($timeSlot->start_time)->format('H:i')) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @error('start_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">Ora sfârșit</label>
                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time', \Carbon\Carbon::parse($timeSlot->end_time)->format('H:i')) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @error('end_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="max_reservations" class="block text-sm font-medium text-gray-700 mb-1">Max rezervări per slot</label>
                    <input type="number" name="max_reservations" id="max_reservations" value="{{ old('max_reservations', $timeSlot->max_reservations) }}" min="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $timeSlot->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
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
