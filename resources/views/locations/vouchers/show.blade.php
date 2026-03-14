@extends('layouts.app')

@section('title', 'Detalii voucher')
@section('page-title', 'Detalii voucher')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Voucher {{ $voucher->code }}</h1>
                <p class="text-gray-600">Locație: {{ $location->name }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('locations.vouchers.edit', [$location, $voucher]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                    <i class="fas fa-edit mr-2"></i>Editează
                </a>
                <form action="{{ route('locations.vouchers.destroy', [$location, $voucher]) }}" method="POST" class="inline" onsubmit="return confirm('Ștergeți acest voucher?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 font-medium"><i class="fas fa-trash mr-2"></i>Șterge</button>
                </form>
                <a href="{{ route('locations.vouchers.index', $location) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Înapoi
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 auto-hide-alert"><p class="text-green-800">{{ session('success') }}</p></div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Informații</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><span class="text-gray-600">Cod:</span> <span class="font-mono font-semibold">{{ $voucher->code }}</span></div>
            <div><span class="text-gray-600">Tip:</span> {{ $voucher->type === 'amount' ? 'Sumă (RON)' : 'Ore' }}</div>
            <div><span class="text-gray-600">Valoare inițială:</span> {{ $voucher->type === 'amount' ? number_format($voucher->initial_value, 2) . ' RON' : number_format($voucher->initial_value, 2) . ' ore' }}</div>
            <div><span class="text-gray-600">Sold rămas:</span> {{ $voucher->type === 'amount' ? number_format($voucher->remaining_value, 2) . ' RON' : number_format($voucher->remaining_value, 2) . ' ore' }}</div>
            <div><span class="text-gray-600">Expirare:</span> {{ $voucher->expires_at ? $voucher->expires_at->format('d.m.Y H:i') : '—' }}</div>
            <div>
                <span class="text-gray-600">Status:</span>
                @if(!$voucher->is_active)
                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactiv</span>
                @elseif($voucher->isExpired())
                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Expirat</span>
                @else
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Activ</span>
                @endif
            </div>
            @if($voucher->notes)
                <div class="md:col-span-2"><span class="text-gray-600">Note:</span> {{ $voucher->notes }}</div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <h2 class="text-xl font-bold text-gray-900 p-6 pb-2">Istoric utilizări</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dată</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tip bon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detalii</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sumă/ore folosite</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($voucher->usages as $usage)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-900">{{ $usage->used_at->format('d.m.Y H:i') }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $usage->getReceiptType() === 'session' ? 'Sesiune' : ($usage->getReceiptType() === 'standalone' ? 'Bon specific' : '—') }}</td>
                            <td class="px-6 py-4 text-gray-600">
                                @if($usage->playSession && $usage->playSession->child)
                                    {{ $usage->playSession->child->name }}
                                @elseif($usage->standaloneReceipt)
                                    Bon #{{ $usage->standaloneReceipt->id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900">
                                @if($voucher->type === 'amount')
                                    {{ number_format($usage->amount_used ?? 0, 2) }} RON
                                @else
                                    {{ number_format($usage->hours_used ?? 0, 2) }} ore
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">Nicio utilizare înregistrată.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
