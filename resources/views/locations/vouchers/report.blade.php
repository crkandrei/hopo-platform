@extends('layouts.app')

@section('title', 'Raport vouchere')
@section('page-title', 'Raport vouchere')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Raport vouchere</h1>
                <p class="text-gray-600">Locație: {{ $location->name }}</p>
            </div>
            <a href="{{ route('locations.vouchers.index', $location) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Înapoi la listă
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Total vouchere emise</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_issued_count'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Vouchere sumă (RON)</p>
            <div class="space-y-1">
                <p class="text-sm text-gray-600">Emis: <span class="font-semibold text-gray-900">{{ number_format($stats['amount']['issued'], 2) }} RON</span></p>
                <p class="text-sm text-gray-600">Folosit: <span class="font-semibold text-amber-600">{{ number_format($stats['amount']['used'], 2) }} RON</span></p>
                <p class="text-sm text-gray-600">Rămas: <span class="font-semibold text-green-600">{{ number_format($stats['amount']['remaining'], 2) }} RON</span></p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Vouchere ore</p>
            <div class="space-y-1">
                <p class="text-sm text-gray-600">Emis: <span class="font-semibold text-gray-900">{{ number_format($stats['hours']['issued'], 2) }} ore</span></p>
                <p class="text-sm text-gray-600">Folosit: <span class="font-semibold text-amber-600">{{ number_format($stats['hours']['used'], 2) }} ore</span></p>
                <p class="text-sm text-gray-600">Rămas: <span class="font-semibold text-green-600">{{ number_format($stats['hours']['remaining'], 2) }} ore</span></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('locations.vouchers.report', $location) }}" class="flex flex-wrap gap-4 items-center mb-4">
            <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">Toate tipurile</option>
                <option value="amount" {{ request('type') === 'amount' ? 'selected' : '' }}>Sumă</option>
                <option value="hours" {{ request('type') === 'hours' ? 'selected' : '' }}>Ore</option>
            </select>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">Toate statusurile</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activ</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expirat</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactiv</option>
                <option value="depleted" {{ request('status') === 'depleted' ? 'selected' : '' }}>Epuit</option>
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="px-3 py-2 border border-gray-300 rounded-lg" placeholder="Emis de la">
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
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valoare inițială</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valoare folosită</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sold rămas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expirare</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Utilizări</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($vouchers as $v)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-mono font-medium text-gray-900">{{ $v->code }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $v->type === 'amount' ? 'Sumă' : 'Ore' }}</td>
                            <td class="px-6 py-4 text-right text-gray-900">{{ $v->type === 'amount' ? number_format($v->initial_value, 2) . ' RON' : number_format($v->initial_value, 2) . ' ore' }}</td>
                            <td class="px-6 py-4 text-right text-amber-600">{{ $v->type === 'amount' ? number_format($v->getTotalUsed(), 2) . ' RON' : number_format($v->getTotalUsed(), 2) . ' ore' }}</td>
                            <td class="px-6 py-4 text-right text-gray-900">{{ $v->type === 'amount' ? number_format($v->remaining_value, 2) . ' RON' : number_format($v->remaining_value, 2) . ' ore' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $v->expires_at ? $v->expires_at->format('d.m.Y') : '—' }}</td>
                            <td class="px-6 py-4 text-center text-gray-600">{{ $v->usages_count }}</td>
                            <td class="px-6 py-4">
                                @if(!$v->is_active)
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactiv</span>
                                @elseif($v->isExpired())
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Expirat</span>
                                @elseif((float)$v->remaining_value <= 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">Epuit</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Activ</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-8 text-center text-gray-500">Niciun voucher.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
