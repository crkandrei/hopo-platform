@extends('layouts.app')

@section('title', 'Săli Zile de Naștere')
@section('page-title', 'Săli Zile de Naștere')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Săli Zile de Naștere</h1>
                <p class="text-gray-600">Locație: {{ $location->name }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('locations.birthday-packages.index', $location) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                    <i class="fas fa-gift mr-2"></i>Pachete
                </a>
                <a href="{{ route('locations.birthday-halls.create', $location) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                    <i class="fas fa-plus mr-2"></i>Adaugă sală
                </a>
                <a href="{{ route('locations.show', $location) }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Înapoi
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4"><p class="text-green-800">{{ session('success') }}</p></div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4"><p class="text-red-800">{{ session('error') }}</p></div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nume sală</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mod rezervare</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Capacitate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slot-uri</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rezervări</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acțiuni</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($halls as $hall)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $hall->name }}</td>
                            <td class="px-6 py-4 text-gray-600">
                                @if(($hall->booking_mode ?? 'slots') === 'flexible')
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Orar liber</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800">Pe sloturi</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $hall->capacity }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $hall->time_slots_count }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $hall->birthday_reservations_count }}</td>
                            <td class="px-6 py-4">
                                @if($hall->is_active)
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Activ</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactiv</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('birthday-halls.time-slots.index', $hall) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Slot-uri</a>
                                <a href="{{ route('locations.birthday-halls.edit', [$location, $hall]) }}" class="text-blue-600 hover:text-blue-900 font-medium">Editează</a>
                                <form action="{{ route('locations.birthday-halls.destroy', [$location, $hall]) }}" method="POST" class="inline" onsubmit="return confirm('Ștergeți această sală?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Șterge</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">Nicio sală. Adăugați o sală pentru zile de naștere.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
