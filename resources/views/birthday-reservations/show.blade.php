@extends('layouts.app')

@section('title', 'Detalii rezervare')
@section('page-title', 'Detalii rezervare')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Rezervare #{{ $birthdayReservation->id }}</h1>
                <p class="text-gray-600">{{ $birthdayReservation->reservation_date->format('d.m.Y') }} — {{ $birthdayReservation->birthdayHall->name ?? '-' }}</p>
            </div>
            <div class="flex gap-2 items-center">
                @if($birthdayReservation->status === 'pending')
                    <span class="px-3 py-1 text-sm rounded-full bg-yellow-100 text-yellow-800">În așteptare</span>
                @elseif($birthdayReservation->status === 'confirmed')
                    <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800">Confirmat</span>
                @else
                    <span class="px-3 py-1 text-sm rounded-full bg-red-100 text-red-800">Anulat</span>
                @endif
                <a href="{{ route('birthday-reservations.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Înapoi
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4"><p class="text-green-800">{{ session('success') }}</p></div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Detalii rezervare</h2>
            <dl class="space-y-3">
                <div><dt class="text-sm text-gray-500">Locație</dt><dd class="font-medium text-gray-900">{{ $birthdayReservation->location->name }}</dd></div>
                <div><dt class="text-sm text-gray-500">Sală</dt><dd class="font-medium text-gray-900">{{ $birthdayReservation->birthdayHall->name }}</dd></div>
                <div><dt class="text-sm text-gray-500">Pachet</dt><dd class="font-medium text-gray-900">{{ $birthdayReservation->birthdayPackage->name }} — {{ number_format($birthdayReservation->total_price, 2) }} RON</dd></div>
                <div><dt class="text-sm text-gray-500">Data</dt><dd class="font-medium text-gray-900">{{ $birthdayReservation->reservation_date->format('d.m.Y') }}</dd></div>
                @if($birthdayReservation->timeSlot)
                    <div><dt class="text-sm text-gray-500">Interval</dt><dd class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($birthdayReservation->timeSlot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($birthdayReservation->timeSlot->end_time)->format('H:i') }}</dd></div>
                @elseif($birthdayReservation->reservation_time)
                    <div><dt class="text-sm text-gray-500">Ora</dt><dd class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($birthdayReservation->reservation_time)->format('H:i') }}</dd></div>
                @endif
                <div><dt class="text-sm text-gray-500">Copil</dt><dd class="font-medium text-gray-900">{{ $birthdayReservation->child_name }}@if($birthdayReservation->child_age) ({{ $birthdayReservation->child_age }} ani)@endif</dd></div>
                <div><dt class="text-sm text-gray-500">Nr. copii așteptați</dt><dd class="font-medium text-gray-900">{{ $birthdayReservation->number_of_children }}</dd></div>
                <div><dt class="text-sm text-gray-500">Contact</dt><dd class="font-medium text-gray-900">{{ $birthdayReservation->guardian_name }}, {{ $birthdayReservation->guardian_phone }}@if($birthdayReservation->guardian_email)<br>{{ $birthdayReservation->guardian_email }}@endif</dd></div>
                @if($birthdayReservation->notes)
                    <div><dt class="text-sm text-gray-500">Observații</dt><dd class="text-gray-900">{{ $birthdayReservation->notes }}</dd></div>
                @endif
            </dl>
        </div>

        @if(in_array($birthdayReservation->status, ['pending', 'confirmed']))
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Actualizare status</h2>
            <form method="POST" action="{{ route('birthday-reservations.update', $birthdayReservation) }}">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="pending" {{ old('status', $birthdayReservation->status) === 'pending' ? 'selected' : '' }}>În așteptare</option>
                            <option value="confirmed" {{ old('status', $birthdayReservation->status) === 'confirmed' ? 'selected' : '' }}>Confirmat</option>
                            <option value="cancelled" {{ old('status', $birthdayReservation->status) === 'cancelled' ? 'selected' : '' }}>Anulat</option>
                        </select>
                    </div>
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Observații (opțional)</label>
                        <textarea name="notes" id="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('notes', $birthdayReservation->notes) }}</textarea>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium">Salvează</button>
                </div>
            </form>
            <form method="POST" action="{{ route('birthday-reservations.destroy', $birthdayReservation) }}" class="mt-4" onsubmit="return confirm('Ștergeți această rezervare?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Șterge rezervarea</button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
