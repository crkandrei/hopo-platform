@extends('layouts.app')

@section('page-title', 'Abonament')

@section('content')
<div class="p-4 md:p-6 max-w-2xl">

    {{-- Flash success --}}
    @if(session('success'))
    <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle text-green-500"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Abonament activ</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $location->name }}</p>
        </div>

        <div class="px-6 py-6">
            @if($subscription && $subscription->plan)

                {{-- Plan name --}}
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-500">Plan</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $subscription->plan->name }}</span>
                </div>

                {{-- Price paid --}}
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-500">Preț plătit</span>
                    <span class="text-sm text-gray-900">{{ number_format($subscription->price_paid, 2) }} RON</span>
                </div>

                {{-- Last payment date --}}
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-gray-500">Data ultimei plăți</span>
                    <span class="text-sm text-gray-900">{{ $subscription->starts_at->format('d M Y') }}</span>
                </div>

                {{-- Expiry date with color --}}
                <div class="flex items-center justify-between mb-6">
                    <span class="text-sm font-medium text-gray-500">Data expirării</span>
                    @php
                        $daysLeft = (int) ceil(now()->floatDiffInDays($subscription->expires_at, false));
                    @endphp
                    @if($status === 'expired')
                        <span class="text-sm font-semibold text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $subscription->expires_at->format('d M Y') }} (expirat)
                        </span>
                    @elseif($status === 'grace' || $daysLeft <= 30)
                        <span class="text-sm font-semibold text-amber-600">
                            <i class="fas fa-clock mr-1"></i>
                            {{ $subscription->expires_at->format('d M Y') }}
                            @if($daysLeft > 0) ({{ $daysLeft }} zile rămase) @endif
                        </span>
                    @else
                        <span class="text-sm font-semibold text-green-600">
                            <i class="fas fa-check-circle mr-1"></i>
                            {{ $subscription->expires_at->format('d M Y') }} ({{ $daysLeft }} zile rămase)
                        </span>
                    @endif
                </div>

            @else

                {{-- No subscription --}}
                <div class="text-center py-6">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-credit-card text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500 text-sm mb-1">Niciun abonament activ</p>
                    <p class="text-gray-400 text-xs">Activează un abonament pentru a accesa toate funcționalitățile.</p>
                </div>

            @endif

            {{-- Renewal button --}}
            <a href="{{ route('checkout.plans') }}"
               class="block w-full text-center bg-sky-600 hover:bg-sky-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                <i class="fas fa-sync-alt mr-2"></i>
                {{ $subscription ? 'Reînnoire abonament' : 'Activează abonament' }}
            </a>
        </div>
    </div>
</div>
@endsection
