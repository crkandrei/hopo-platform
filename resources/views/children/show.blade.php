@extends('layouts.app')

@section('title', 'Detalii Copil')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('children.index') }}" class="text-gray-400 hover:text-gray-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $child->name }}</h1>
                    <p class="text-gray-600">Detalii copil și informații asociate</p>
                </div>
            </div>
            @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin() || Auth::user()->isStaff())
            <div class="flex items-center space-x-3">
                <a href="{{ route('children.edit', $child) }}" 
                   class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 font-medium">
                    Editează
                </a>
                @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin())
                <form method="POST" action="{{ route('children.destroy', $child) }}" 
                      class="inline" onsubmit="return confirm('Sigur vrei să ștergi acest copil?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 font-medium">
                        Șterge
                    </button>
                </form>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Child Details -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Informații Personale</h2>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Data Înregistrare</dt>
                    <dd class="text-gray-900">{{ $child->created_at->format('d.m.Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Părinte/Tutore</dt>
                    <dd class="text-gray-900">
                        @if($child->guardian)
                            <a href="{{ route('guardians.show', ['guardian' => $child->guardian->id, 'from_child' => $child->id]) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $child->guardian->name }}
                            </a>
                            @if($child->guardian->phone)
                                <span class="text-gray-500"> • </span>
                                <a href="tel:{{ $child->guardian->phone }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $child->guardian->phone }}
                                </a>
                            @endif
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </dd>
                </div>
            </dl>
            
            @if($child->allergies)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Alergii</h3>
                            <div class="mt-2 text-sm text-red-700">{{ $child->allergies }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($child->notes)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <dt class="text-sm font-medium text-gray-500 mb-2">Note</dt>
                <dd class="text-gray-900 whitespace-pre-line">{{ $child->notes }}</dd>
            </div>
            @endif
        </div>
    </div>

    <!-- Play Sessions History -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <button type="button" id="toggle-sessions-history" class="flex items-center justify-between w-full text-left hover:text-indigo-600 transition-colors">
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Istoric Sesiuni 
                        <span class="text-sm font-normal text-gray-500">
                            ({{ isset($playSessions) ? $playSessions->count() : 0 }})
                        </span>
                    </h2>
                    @if(isset($totalPrice) && $totalPrice > 0)
                        <span class="text-sm font-semibold text-green-600">
                            Total: {{ number_format($totalPrice, 2, '.', '') }} RON
                        </span>
                    @endif
                </div>
                <svg id="sessions-arrow" class="w-5 h-5 text-gray-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>
        <div id="sessions-table-container" class="hidden">
            <div class="p-6">
                @if(isset($playSessions) && $playSessions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Ora Început</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durata</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preț</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($playSessions as $session)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($session['started_at'])->format('d.m.Y H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($session['is_active'])
                                            <span id="duration-{{ $session['id'] }}">
                                                @php
                                                    $hours = floor($session['effective_seconds'] / 3600);
                                                    $minutes = floor(($session['effective_seconds'] % 3600) / 60);
                                                    $seconds = $session['effective_seconds'] % 60;
                                                @endphp
                                                @if($hours > 0)
                                                    {{ $hours }}h {{ $minutes }}m {{ $seconds }}s
                                                @elseif($minutes > 0)
                                                    {{ $minutes }}m {{ $seconds }}s
                                                @else
                                                    {{ $seconds }}s
                                                @endif
                                            </span>
                                        @else
                                            @php
                                                $hours = floor($session['effective_seconds'] / 3600);
                                                $minutes = floor(($session['effective_seconds'] % 3600) / 60);
                                            @endphp
                                            @if($hours > 0)
                                                <span>{{ $hours }}h {{ $minutes }}m</span>
                                            @elseif($minutes > 0)
                                                <span>{{ $minutes }}m</span>
                                            @else
                                                <span>0m</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($session['is_active'])
                                            <span class="font-semibold text-amber-600">{{ $session['formatted_price'] }}</span>
                                        @else
                                            <span class="font-semibold text-green-600">{{ $session['formatted_price'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($session['status'] === 'active' || $session['is_active'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Activă
                                            </span>
                                        @elseif($session['status'] === 'completed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Completată
                                            </span>
                                        @elseif($session['status'] === 'cancelled')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Anulată
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ ucfirst($session['status']) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">Nu există sesiuni de joc pentru acest copil.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
@if(isset($playSessions) && $playSessions->count() > 0)
<script>
(function() {
    // Function to format milliseconds to HMS format
    function msToHMS(ms) {
        const totalSeconds = Math.max(0, Math.floor(ms / 1000));
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        if (hours > 0) {
            return `${hours}h ${minutes}m ${seconds}s`;
        }
        return `${minutes}m ${seconds}s`;
    }

    const activeTimers = new Map();

    function clearAllTimers() {
        activeTimers.forEach((intervalId) => clearInterval(intervalId));
        activeTimers.clear();
    }

    // Initialize timers for active sessions
    @foreach($playSessions as $session)
        @if($session['is_active'])
            (function() {
                const sessionId = {{ $session['id'] }};
                const baseSeconds = {{ $session['effective_seconds'] ?? 0 }};
                const isPaused = {{ $session['is_paused'] ? 'true' : 'false' }};
                const currentIntervalStart = @json($session['current_interval_started_at'] ? $session['current_interval_started_at'] : null);
                
                const el = document.getElementById('duration-' + sessionId);
                if (!el) return;

                const update = () => {
                    let totalSeconds = baseSeconds;
                    // If not paused and we have a current interval, add elapsed time
                    if (!isPaused && currentIntervalStart) {
                        const elapsed = Math.max(0, Math.floor((Date.now() - new Date(currentIntervalStart).getTime()) / 1000));
                        totalSeconds += elapsed;
                    }
                    el.textContent = msToHMS(totalSeconds * 1000);
                };

                update();
                const intervalId = setInterval(update, 1000);
                activeTimers.set(sessionId, intervalId);
            })();
        @endif
    @endforeach

    // Cleanup timers on page unload
    window.addEventListener('beforeunload', clearAllTimers);

    // Toggle sessions history table
    const toggleBtn = document.getElementById('toggle-sessions-history');
    const tableContainer = document.getElementById('sessions-table-container');
    const arrow = document.getElementById('sessions-arrow');
    
    if (toggleBtn && tableContainer) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = tableContainer.classList.contains('hidden');
            if (isHidden) {
                tableContainer.classList.remove('hidden');
                arrow.classList.add('rotate-180');
            } else {
                tableContainer.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        });
    }
})();
</script>
@endif
@endsection

