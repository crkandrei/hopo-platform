@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Abonamente 💳</h1>
                <p class="mt-1 text-sm text-gray-500">Gestionați abonamentele active per locație</p>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm auto-hide-alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Status filters --}}
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('admin.subscriptions.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                  {{ $statusFilter === null ? 'bg-sky-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
            Toate
        </a>
        <a href="{{ route('admin.subscriptions.index', ['status' => 'active']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                  {{ $statusFilter === 'active' ? 'bg-sky-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
            Active
        </a>
        <a href="{{ route('admin.subscriptions.index', ['status' => 'grace']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                  {{ $statusFilter === 'grace' ? 'bg-sky-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
            În grație
        </a>
        <a href="{{ route('admin.subscriptions.index', ['status' => 'expired']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                  {{ $statusFilter === 'expired' ? 'bg-sky-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
            Expirate
        </a>
        <a href="{{ route('admin.subscriptions.index', ['status' => 'none']) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                  {{ $statusFilter === 'none' ? 'bg-sky-600 text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
            Fără abonament
        </a>
    </div>

    {{-- Table card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Locație</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Companie</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiră la</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zile rămase</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acțiuni</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($locations as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-semibold text-gray-900">{{ $item['location']->name }}</div>
                            <div class="text-xs text-gray-400">{{ $item['location']->slug }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $item['location']->company->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php $status = $item['status']; @endphp
                            @if($status === 'active')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">● Activ</span>
                            @elseif($status === 'grace')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">⚠ În grație</span>
                            @elseif($status === 'expired')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">✕ Expirat</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600">— Fără abonament</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $item['expires_at'] ? $item['expires_at']->format('d M Y') : '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $item['days_remaining'] !== null ? $item['days_remaining'] : '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('admin.subscriptions.history', $item['location']) }}"
                               class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                                Istoric
                            </a>
                            <a href="{{ route('admin.subscriptions.create', $item['location']) }}"
                               class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                                + Abonament
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">
                            Nu există locații disponibile.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
