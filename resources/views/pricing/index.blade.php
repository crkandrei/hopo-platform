@extends('layouts.app')

@section('title', 'Gestionare Tarife')
@section('page-title', 'Gestionare Tarife')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestionare Tarife</h1>
                <p class="text-gray-600 text-lg">Configurați tarifele pentru zilele săptămânii, pe durate și perioade speciale</p>
            </div>
        </div>
    </div>

    <!-- Location Selector -->
    @if($isSuperAdmin && $locations)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('pricing.index') }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">Selectați Locație</label>
                <select name="location_id" id="location_id" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        onchange="this.form.submit()">
                    <option value="">-- Selectați locație --</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ ($selectedLocation && $selectedLocation->id == $location->id) || request('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}@if($location->company) ({{ $location->company->name }})@endif
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    @elseif(!$isSuperAdmin && $locations && $locations->count() > 1)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('pricing.index') }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">Selectați Locație</label>
                <select name="location_id" id="location_id" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        onchange="this.form.submit()">
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ ($selectedLocation && $selectedLocation->id == $location->id) || request('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    @endif

    @if($selectedLocation)
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
        $pricingMode = $selectedLocation->pricing_mode ?? 'flat_hourly';
        $durations = $durations ?? [1, 2, 3, 4];
        $tieredGrid = $tieredGrid ?? [];
        $weeklyRatesByDay = $weeklyRatesByDay ?? [];
    @endphp

    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Tabs">
                <a href="#tarife" 
                   onclick="showTab('tarife'); return false;"
                   class="tab-link flex-1 text-center py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                   id="tab-tarife">
                    <i class="fas fa-dollar-sign mr-2"></i>
                    Tarife
                </a>
                <a href="#special-periods" 
                   onclick="showTab('special-periods'); return false;"
                   class="tab-link flex-1 text-center py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                   id="tab-special-periods">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Perioade Speciale
                </a>
            </nav>
        </div>

        <div class="p-6">
            <!-- Tab Tarife -->
            <div id="content-tarife" class="tab-content">
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Mod tarifare</h2>
                    <p class="text-gray-600 mb-4">Alegeți cum se calculează prețul: tarif pe oră (per zi) sau tarife pe durate (1h, 2h, 3h, 4h per zi).</p>
                    <form method="POST" action="{{ route('pricing.mode.update') }}" class="inline-flex items-center gap-4">
                        @csrf
                        <input type="hidden" name="location_id" value="{{ $selectedLocation->id }}">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="pricing_mode" value="flat_hourly" {{ $pricingMode === 'flat_hourly' ? 'checked' : '' }} onchange="this.form.submit()">
                            <span>Tarif pe oră (clasic)</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="pricing_mode" value="tiered" {{ $pricingMode === 'tiered' ? 'checked' : '' }} onchange="this.form.submit()">
                            <span>Tarife pe durate (1h, 2h, 3h, 4h)</span>
                        </label>
                    </form>
                </div>

                @if($pricingMode === 'flat_hourly')
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Tarife săptămânale (RON/oră)</h3>
                    <p class="text-gray-600 text-sm mb-4">Dacă nu setați un tarif pentru o zi, se folosește tariful implicit al locației: {{ number_format($selectedLocation->price_per_hour ?? 0, 2, '.', '') }} RON/oră.</p>
                    <form method="POST" action="{{ route('pricing.weekly-rates.update') }}">
                        @csrf
                        <input type="hidden" name="location_id" value="{{ $selectedLocation->id }}">
                        <div class="space-y-3">
                            @foreach($days as $dayNum => $dayName)
                            <div class="flex items-center gap-4 p-3 border border-gray-200 rounded-lg">
                                <div class="w-28 text-sm font-medium text-gray-700">{{ $dayName }}</div>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="rates[{{ $dayNum }}]" 
                                           value="{{ $weeklyRatesByDay[$dayNum] ?? '' }}"
                                           step="0.01" min="0" placeholder="0.00"
                                           class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <span class="text-gray-600 text-sm">RON/oră</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                                <i class="fas fa-save mr-2"></i>Salvează tarife săptămânale
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Tarife pe durate (RON per sesiune)</h3>
                    <p class="text-gray-600 text-sm mb-4">Preț total pentru 1h, 2h, 3h, 4h. Durata sesiunii se rotunjește la tier-ul superior (ex. 2h30 → preț 3h).</p>
                    <form method="POST" action="{{ route('pricing.tiered-rates.update') }}" id="form-tiered">
                        @csrf
                        <input type="hidden" name="location_id" value="{{ $selectedLocation->id }}">
                        @foreach($durations as $d)
                        <input type="hidden" name="durations[]" value="{{ $d }}">
                        @endforeach
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preț/oră pentru timp peste ultimul tier (ex. 5h când max e 4h)</label>
                            <input type="number" name="overflow_price_per_hour" 
                                   value="{{ $selectedLocation->overflow_price_per_hour !== null ? $selectedLocation->overflow_price_per_hour : '' }}"
                                   step="0.01" min="0" placeholder="0.00"
                                   class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <span class="text-gray-600 text-sm ml-2">RON/oră</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border-b">Zi</th>
                                        @foreach($durations as $d)
                                        <th class="px-4 py-2 text-center text-sm font-medium text-gray-700 border-b">{{ $d }}h</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($days as $dayNum => $dayName)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm font-medium text-gray-700">{{ $dayName }}</td>
                                        @foreach($durations as $d)
                                        <td class="px-2 py-1">
                                            <input type="number" 
                                                   name="tiers[{{ $dayNum }}][{{ $d }}]" 
                                                   data-day="{{ $dayNum }}" data-dur="{{ $d }}"
                                                   value="{{ isset($tieredGrid[$dayNum][$d]) && $tieredGrid[$dayNum][$d] !== null ? $tieredGrid[$dayNum][$d] : '' }}"
                                                   step="0.01" min="0" placeholder="—"
                                                   class="tier-input w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500">
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <button type="button" id="copy-monday-btn" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium">
                                Copiază Luni pe toate zilele
                            </button>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                                <i class="fas fa-save mr-2"></i>Salvează tarife pe durate
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>

            <!-- Tab Perioade Speciale -->
            <div id="content-special-periods" class="tab-content hidden">
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Perioade Speciale</h2>
                    <p class="text-gray-600">Gestionați tarife pentru perioade speciale (ex. ziua de deschidere) pentru locația <strong>{{ $selectedLocation->name }}</strong></p>
                </div>
                <a href="{{ route('pricing.special-periods') }}{{ $isSuperAdmin ? '?location_id=' . $selectedLocation->id : '' }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Gestionare Perioade Speciale
                </a>
            </div>
        </div>
    </div>

    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <i class="fas fa-info-circle text-gray-400 text-5xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">
            @if($isSuperAdmin)
                Selectați o locație
            @else
                Nu există locație asociată
            @endif
        </h3>
        <p class="text-gray-600">
            @if($isSuperAdmin)
                Selectați o locație din lista de mai sus pentru a gestiona tarifele
            @else
                Contactați administratorul pentru a vă asocia o locație
            @endif
        </p>
    </div>
    @endif
</div>

@if($selectedLocation ?? false)
<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(function(content) {
        content.classList.add('hidden');
    });
    document.querySelectorAll('.tab-link').forEach(function(link) {
        link.classList.remove('border-indigo-500', 'text-indigo-600');
        link.classList.add('border-transparent', 'text-gray-500');
    });
    var content = document.getElementById('content-' + tabName);
    if (content) content.classList.remove('hidden');
    var tab = document.getElementById('tab-' + tabName);
    if (tab) {
        tab.classList.remove('border-transparent', 'text-gray-500');
        tab.classList.add('border-indigo-500', 'text-indigo-600');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    showTab('tarife');
    var copyBtn = document.getElementById('copy-monday-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            var inputs = document.querySelectorAll('.tier-input[data-day="0"]');
            var values = [];
            inputs.forEach(function(inp) {
                values.push(inp.value || '');
            });
            for (var day = 1; day <= 6; day++) {
                document.querySelectorAll('.tier-input[data-day="' + day + '"]').forEach(function(inp, i) {
                    inp.value = values[i] || '';
                });
            }
        });
    }
});
</script>
@endif
@endsection
