@extends('layouts.app')

@section('title', 'Slot-uri orare')
@section('page-title', 'Slot-uri orare')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <nav class="text-sm text-gray-500 mb-1">
                    <a href="{{ route('locations.show', $location) }}" class="hover:text-gray-700">{{ $location->name }}</a>
                    <span class="mx-1">/</span>
                    <a href="{{ route('locations.birthday-halls.index', $location) }}" class="hover:text-gray-700">Săli</a>
                    <span class="mx-1">/</span>
                    <span class="text-gray-900 font-medium">{{ $birthdayHall->name }}</span>
                </nav>
                <h1 class="text-3xl font-bold text-gray-900">Slot-uri orare</h1>
            </div>
            <a href="{{ route('locations.birthday-halls.index', $location) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Înapoi la săli
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4"><p class="text-green-800">{{ session('success') }}</p></div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4"><p class="text-red-800">{{ session('error') }}</p></div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Adaugă slot</h2>
        <form method="POST" action="{{ route('birthday-halls.time-slots.store', $birthdayHall) }}" class="flex flex-wrap gap-4 items-end">
            @csrf
            <div>
                <label for="day_of_week" class="block text-sm font-medium text-gray-700 mb-1">Zi</label>
                <select name="day_of_week" id="day_of_week" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">Orice zi</option>
                    @foreach([0 => 'Luni', 1 => 'Marți', 2 => 'Miercuri', 3 => 'Joi', 4 => 'Vineri', 5 => 'Sâmbătă', 6 => 'Duminică'] as $d => $label)
                        <option value="{{ $d }}" {{ old('day_of_week') === (string)$d ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Început</label>
                <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" required class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                @error('start_time')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">Sfârșit</label>
                <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" required class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                @error('end_time')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="max_reservations" class="block text-sm font-medium text-gray-700 mb-1">Max rezervări/slot</label>
                <input type="number" name="max_reservations" id="max_reservations" value="{{ old('max_reservations', 1) }}" min="1" class="w-24 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Activ</span>
                </label>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">Adaugă</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Zi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Început</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sfârșit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max rezervări</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acțiuni</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($slots as $slot)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-900">{{ $slot->day_name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $slot->max_reservations }}</td>
                            <td class="px-6 py-4">
                                @if($slot->is_active)
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Activ</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactiv</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('birthday-halls.time-slots.edit', [$birthdayHall, $slot]) }}" class="text-blue-600 hover:text-blue-900 font-medium">Editează</a>
                                <form action="{{ route('birthday-halls.time-slots.destroy', [$birthdayHall, $slot]) }}" method="POST" class="inline" onsubmit="return confirm('Ștergeți acest slot?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Șterge</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Niciun slot. Adăugați un interval orar mai sus.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
