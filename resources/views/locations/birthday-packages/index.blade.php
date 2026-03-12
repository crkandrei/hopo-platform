@extends('layouts.app')

@section('title', 'Pachete Zile de Naștere')
@section('page-title', 'Pachete Zile de Naștere')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Pachete Zile de Naștere</h1>
                <p class="text-gray-600">Locație: {{ $location->name }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('locations.birthday-halls.index', $location) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                    <i class="fas fa-door-open mr-2"></i>Săli
                </a>
                <a href="{{ route('locations.birthday-packages.create', $location) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                    <i class="fas fa-plus mr-2"></i>Adaugă pachet
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nume</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durată</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Zile disponibile</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acțiuni</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($packages as $package)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $package->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $package->duration_minutes }} min</td>
                            <td class="px-6 py-4 text-gray-600">
                                @if(count($package->available_day_names))
                                    {{ implode(', ', $package->available_day_names) }}
                                @else
                                    <span class="text-red-600">Nicio zi setată</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($package->is_active)
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Activ</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactiv</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('locations.birthday-packages.edit', [$location, $package]) }}" class="text-blue-600 hover:text-blue-900 font-medium">Editează</a>
                                <form action="{{ route('locations.birthday-packages.destroy', [$location, $package]) }}" method="POST" class="inline" onsubmit="return confirm('Ștergeți acest pachet?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Șterge</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Niciun pachet. Adăugați un pachet pentru zile de naștere.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
