@extends('layouts.app')

@section('title', 'Gestionare Tarife')
@section('page-title', 'Gestionare Tarife')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestionare Tarife 💰</h1>
                <p class="text-gray-600 text-lg">Configurați tarifele pe oră pentru zilele săptămânii și perioade speciale</p>
            </div>
        </div>
    </div>

    <!-- Location Selector (only for Super Admin and Company Admin) -->
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
                        <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}@if($location->company) ({{ $location->company->name }})@endif
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    @elseif(!$isSuperAdmin && $locations && $locations->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('pricing.index') }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">Selectați Locație</label>
                <select name="location_id" id="location_id" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        onchange="this.form.submit()">
                    <option value="">-- Selectați locație --</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    @endif

    @if($selectedLocation)
    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Tabs">
                <a href="#weekly-rates" 
                   onclick="showTab('weekly-rates'); return false;"
                   class="tab-link flex-1 text-center py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                   id="tab-weekly-rates">
                    <i class="fas fa-calendar-week mr-2"></i>
                    Tarife Săptămânale
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

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Weekly Rates Tab -->
            <div id="content-weekly-rates" class="tab-content">
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Tarife pe Zi a Săptămânii</h2>
                    <p class="text-gray-600">Setați tarife diferite pentru fiecare zi a săptămânii pentru locația <strong>{{ $selectedLocation->name }}</strong></p>
                </div>
                <a href="{{ route('pricing.weekly-rates') }}{{ $isSuperAdmin ? '?location_id=' . $selectedLocation->id : '' }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Editează Tarife Săptămânale
                </a>
            </div>

            <!-- Special Periods Tab -->
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

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-link').forEach(link => {
        link.classList.remove('border-indigo-500', 'text-indigo-600');
        link.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab
    const selectedTab = document.getElementById('tab-' + tabName);
    selectedTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
    selectedTab.classList.add('border-indigo-500', 'text-indigo-600');
}

// Set default tab
document.addEventListener('DOMContentLoaded', function() {
    showTab('weekly-rates');
});
</script>
@endsection

