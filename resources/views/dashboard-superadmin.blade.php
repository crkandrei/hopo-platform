@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Welcome -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">Bun venit, {{ Auth::user()->name }}!</h1>
                <p class="text-gray-500 text-sm mt-1">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    <span id="currentDateTime"></span>
                </p>
            </div>
            <div class="hidden md:flex items-center gap-2 text-sm text-gray-500 bg-gray-50 px-4 py-2 rounded-lg border border-gray-200">
                <i class="fas fa-shield-alt text-sky-500"></i>
                Super Administrator
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 mb-1">Companii</p>
            <p class="text-3xl font-bold text-sky-600">{{ $stats['companies'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 mb-1">Locații</p>
            <p class="text-3xl font-bold text-indigo-600">{{ $stats['locations'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 mb-1">Utilizatori</p>
            <p class="text-3xl font-bold text-purple-600">{{ $stats['users'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 mb-1">Active acum</p>
            <p class="text-3xl font-bold text-green-600">{{ $stats['active_now'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 mb-1">Sesiuni azi</p>
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['sessions_today'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 mb-1">Încasări azi</p>
            <p class="text-2xl font-bold text-emerald-600">{{ number_format($stats['income_today'], 0, ',', '.') }} <span class="text-base font-normal text-gray-400">RON</span></p>
        </div>
    </div>

    <!-- Per-company breakdown -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-building text-sky-500"></i>
                Companii
            </h2>
            <a href="{{ route('onboarding.create') }}"
               class="text-sm bg-green-600 text-white px-4 py-1.5 rounded-lg hover:bg-green-700 flex items-center gap-1.5 font-medium">
                <i class="fas fa-plus text-xs"></i>
                Client Nou
            </a>
        </div>

        @if($companies->isEmpty())
        <div class="px-6 py-12 text-center text-gray-400">
            <i class="fas fa-building text-4xl mb-3 block"></i>
            <p>Nu există companii înregistrate.</p>
            <a href="{{ route('onboarding.create') }}" class="mt-3 inline-block text-sm text-green-600 hover:underline">Adaugă primul client</a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wide border-b border-gray-100">
                        <th class="text-left px-6 py-3">Companie</th>
                        <th class="text-center px-4 py-3">Locații active</th>
                        <th class="text-center px-4 py-3">Utilizatori</th>
                        <th class="text-center px-4 py-3">Active acum</th>
                        <th class="text-center px-4 py-3">Sesiuni azi</th>
                        <th class="text-right px-6 py-3">Încasări azi</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($companies as $company)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-900">{{ $company->name }}</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="{{ $company->active_locations > 0 ? 'text-gray-800 font-medium' : 'text-gray-400' }}">
                                {{ $company->active_locations }}
                            </span>
                            @if($company->locations_count > $company->active_locations)
                                <span class="text-xs text-gray-400">/{{ $company->locations_count }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center text-gray-600">{{ $company->users_count }}</td>
                        <td class="px-4 py-4 text-center">
                            @if($company->active_now > 0)
                                <span class="inline-flex items-center gap-1 text-green-700 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse inline-block"></span>
                                    {{ $company->active_now }}
                                </span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($company->sessions_today > 0)
                                <span class="text-yellow-700 font-medium">{{ $company->sessions_today }}</span>
                                @if($company->precheckin_today > 0)
                                    <span class="text-xs text-blue-500 ml-1">({{ $company->precheckin_today }} PC)</span>
                                @endif
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($company->income_today > 0)
                                <span class="text-emerald-700 font-semibold">{{ number_format($company->income_today, 2, ',', '.') }} RON</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($company->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Activă</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Inactivă</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('companies.show', $company) }}"
                               class="text-sky-600 hover:text-sky-800 text-xs font-medium">
                                Detalii →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

<script>
function updateDateTime() {
    const now = new Date();
    const options = { weekday: 'long', day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' };
    const el = document.getElementById('currentDateTime');
    if (el) el.textContent = now.toLocaleDateString('ro-RO', options);
}
document.addEventListener('DOMContentLoaded', function () {
    updateDateTime();
    setInterval(updateDateTime, 60000);
});
</script>
@endsection
