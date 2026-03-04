@extends('layouts.app')

@section('title', 'Rezervări Zile de Naștere')
@section('page-title', 'Rezervări Zile de Naștere')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Rezervări Zile de Naștere</h1>
        <p class="text-gray-600">Lista rezervărilor primite</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4"><p class="text-green-800">{{ session('success') }}</p></div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4"><p class="text-red-800">{{ session('error') }}</p></div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('birthday-reservations.index') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-1">Locație</label>
                <select name="location_id" id="location_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">Toate</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="reservation_date" class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                <input type="date" name="reservation_date" id="reservation_date" value="{{ request('reservation_date') }}" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">Toate</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>În așteptare</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmat</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Anulat</option>
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">Filtrează</button>
            <a href="{{ route('birthday-reservations.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 font-medium">Resetează</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dată</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Locație / Sală</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pachet</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acțiuni</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reservations as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $r->reservation_date->format('d.m.Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $r->location->name ?? '-' }}</div>
                                <div class="text-sm text-gray-500">{{ $r->birthdayHall->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $r->birthdayPackage->name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $r->guardian_name }}</div>
                                <div class="text-sm text-gray-500">{{ $r->guardian_phone }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($r->status === 'pending')
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">În așteptare</span>
                                @elseif($r->status === 'confirmed')
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Confirmat</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Anulat</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('birthday-reservations.show', $r) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Detalii</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Nicio rezervare găsită.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reservations->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">{{ $reservations->links() }}</div>
        @endif
    </div>
</div>
@endsection
