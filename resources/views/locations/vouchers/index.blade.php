@extends('layouts.app')

@section('title', 'Vouchere')
@section('page-title', 'Vouchere')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Vouchere</h1>
                <p class="text-gray-600">Locație: {{ $location->name }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('locations.vouchers.report', $location) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                    <i class="fas fa-chart-bar mr-2"></i>Raport
                </a>
                <a href="{{ route('locations.vouchers.create', $location) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                    <i class="fas fa-plus mr-2"></i>Generare voucher nou
                </a>
                <a href="{{ route('locations.show', $location) }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Înapoi
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 auto-hide-alert"><p class="text-green-800">{{ session('success') }}</p></div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 auto-hide-alert"><p class="text-red-800">{{ session('error') }}</p></div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('locations.vouchers.index', $location) }}" class="flex flex-wrap gap-4 items-center mb-4">
            <select name="active" class="px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">Toate (activ/inactiv)</option>
                <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Activ</option>
                <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactiv</option>
            </select>
            <select name="expired" class="px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">Toate (expirat/valabil)</option>
                <option value="1" {{ request('expired') === '1' ? 'selected' : '' }}>Expirat</option>
                <option value="0" {{ request('expired') === '0' ? 'selected' : '' }}>Valabil</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">Filtrează</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cod</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tip</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valoare inițială</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sold rămas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expirare</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acțiuni</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($vouchers as $voucher)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 group">
                                    <span class="font-mono font-medium text-gray-900">{{ $voucher->code }}</span>
                                    <button
                                        onclick="navigator.clipboard.writeText('{{ $voucher->code }}').then(() => { this.innerHTML = '<svg xmlns=\'http://www.w3.org/2000/svg\' class=\'w-3.5 h-3.5\' viewBox=\'0 0 20 20\' fill=\'currentColor\'><path fill-rule=\'evenodd\' d=\'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z\' clip-rule=\'evenodd\'/></svg>'; this.classList.add('text-green-500'); setTimeout(() => { this.innerHTML = '<svg xmlns=\'http://www.w3.org/2000/svg\' class=\'w-3.5 h-3.5\' viewBox=\'0 0 20 20\' fill=\'currentColor\'><path d=\'M8 2a2 2 0 00-2 2v1H5a2 2 0 00-2 2v9a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H8zm0 2h4v1H8V4zM5 7h10v9H5V7z\'/></svg>'; this.classList.remove('text-green-500'); }, 1500); })"
                                        title="Copiază codul"
                                        class="text-gray-400 hover:text-gray-700 transition-colors duration-150 cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M8 2a2 2 0 00-2 2v1H5a2 2 0 00-2 2v9a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H8zm0 2h4v1H8V4zM5 7h10v9H5V7z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $voucher->type === 'amount' ? 'Sumă (RON)' : 'Ore' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $voucher->type === 'amount' ? number_format($voucher->initial_value, 2) . ' RON' : number_format($voucher->initial_value, 2) . ' ore' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $voucher->type === 'amount' ? number_format($voucher->remaining_value, 2) . ' RON' : number_format($voucher->remaining_value, 2) . ' ore' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $voucher->expires_at ? $voucher->expires_at->format('d.m.Y') : '—' }}</td>
                            <td class="px-6 py-4">
                                @if(!$voucher->is_active)
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactiv</span>
                                @elseif($voucher->isExpired())
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Expirat</span>
                                @elseif((float)$voucher->remaining_value <= 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">Epuit</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Activ</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('locations.vouchers.show', [$location, $voucher]) }}" class="text-blue-600 hover:text-blue-900 font-medium">Detalii</a>
                                <a href="{{ route('locations.vouchers.edit', [$location, $voucher]) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Editează</a>
                                <form action="{{ route('locations.vouchers.destroy', [$location, $voucher]) }}" method="POST" class="inline" onsubmit="return confirm('Ștergeți acest voucher?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Șterge</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">Niciun voucher. Generați un voucher nou.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vouchers->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">{{ $vouchers->links() }}</div>
        @endif
    </div>
</div>
@endsection
