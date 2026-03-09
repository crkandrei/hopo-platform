@extends('layouts.app')

@section('title', 'Tarife Săptămânale')
@section('page-title', 'Tarife Săptămânale')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Tarife Săptămânale 📅</h1>
                <p class="text-gray-600 text-lg">Setați tarife diferite pentru fiecare zi a săptămânii pentru <strong>{{ $location->name }}</strong></p>
            </div>
            <a href="{{ route('pricing.index') }}{{ Auth::user()->isSuperAdmin() ? '?location_id=' . $location->id : '' }}" 
               class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium flex items-center shadow-md">
                <i class="fas fa-arrow-left mr-2"></i>
                Înapoi
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('pricing.weekly-rates.update') }}">
            @csrf
            <input type="hidden" name="location_id" value="{{ $location->id }}">

            <div class="space-y-4">
                @php
                    $days = [
                        0 => 'Luni',
                        1 => 'Marți',
                        2 => 'Miercuri',
                        3 => 'Joi',
                        4 => 'Vineri',
                        5 => 'Sâmbătă',
                        6 => 'Duminică',
                    ];
                @endphp

                @foreach($days as $dayNum => $dayName)
                <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="w-32">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $dayName }}</label>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <input type="number" 
                                   name="rates[{{ $dayNum }}]" 
                                   id="rate_{{ $dayNum }}"
                                   value="{{ $weeklyRates[$dayNum] ?? '' }}"
                                   step="0.01" 
                                   min="0"
                                   placeholder="0.00"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <span class="text-gray-600 font-medium">RON/oră</span>
                        </div>
                        @if(isset($weeklyRates[$dayNum]))
                            <p class="text-xs text-gray-500 mt-1">Tarif actual: {{ number_format($weeklyRates[$dayNum], 2, '.', '') }} RON/oră</p>
                        @else
                            <p class="text-xs text-gray-500 mt-1">Nu este setat. Se va folosi tariful implicit: {{ number_format($location->price_per_hour ?? 0, 2, '.', '') }} RON/oră</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end gap-4">
                <a href="{{ route('pricing.index') }}{{ Auth::user()->isSuperAdmin() ? '?location_id=' . $location->id : '' }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Anulează
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                    <i class="fas fa-save mr-2"></i>
                    Salvează Tarife
                </button>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
            <div>
                <h3 class="font-medium text-blue-900 mb-2">Informații importante</h3>
                <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                    <li>Tarifele săptămânale sunt folosite ca tarif implicit pentru fiecare zi a săptămânii</li>
                    <li>Dacă nu setați un tarif pentru o zi, se va folosi tariful implicit al locației ({{ number_format($location->price_per_hour ?? 0, 2, '.', '') }} RON/oră)</li>
                    <li>Perioadele speciale au prioritate peste tarifele săptămânale</li>
                    <li>Pentru a șterge un tarif, lăsați câmpul gol</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

