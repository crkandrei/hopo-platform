@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Istoric Abonamente — {{ $location->name }}</h1>
                <p class="mt-1 text-sm text-gray-500">Toate abonamentele înregistrate pentru această locație</p>
            </div>
            <a href="{{ route('admin.subscriptions.index') }}"
               class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                ← Înapoi
            </a>
        </div>
    </div>

    {{-- Location info card --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="text-base font-semibold text-blue-900">📍 {{ $location->name }}</div>
        <div class="text-sm text-blue-700 mt-1">{{ $location->company->name ?? '—' }}</div>
    </div>

    {{-- Subscriptions table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($subscriptions->isEmpty())
            <div class="px-6 py-12 text-center text-gray-500 text-sm">
                Nu există abonamente pentru această locație.
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perioadă</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preț plătit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metodă plată</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adăugat de</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adăugat la</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($subscriptions as $sub)
                        @php $isCurrent = $currentSubscription && $sub->id === $currentSubscription->id; @endphp
                        <tr class="hover:bg-gray-50 {{ $isCurrent ? 'bg-green-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $sub->starts_at->format('d M Y') }} → {{ $sub->expires_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($isCurrent)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Curent</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-500">Expirat</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $sub->price_paid !== null ? number_format($sub->price_paid, 2) . ' RON' : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                @php
                                    $methodLabels = [
                                        'bank_transfer' => 'Transfer bancar',
                                        'cash'          => 'Cash',
                                        'card'          => 'Card',
                                        'other'         => 'Altele',
                                    ];
                                @endphp
                                {{ $sub->payment_method ? ($methodLabels[$sub->payment_method] ?? $sub->payment_method) : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">
                                {{ $sub->notes ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $sub->createdBy->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $sub->created_at->format('d M Y H:i') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>
@endsection
