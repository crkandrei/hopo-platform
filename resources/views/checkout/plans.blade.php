@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Alege un plan de abonament</h1>
        <p class="mt-1 text-sm text-gray-500">Selectează planul potrivit pentru locația ta. Plata se procesează securizat prin Stripe.</p>
    </div>

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    @if($plans->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center text-gray-500">
            <p>Nu există planuri disponibile momentan. Contactați administratorul.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($plans as $plan)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
                    <div class="mb-4">
                        <h2 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h2>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="text-3xl font-bold text-indigo-600">{{ number_format($plan->price, 0, ',', '.') }}</span>
                            <span class="text-gray-500 text-sm">RON / {{ $plan->duration_months }} {{ $plan->duration_months === 1 ? 'lună' : 'luni' }}</span>
                        </div>
                    </div>

                    @if($plan->features && count($plan->features) > 0)
                        <ul class="mb-6 space-y-2 flex-1">
                            @foreach($plan->features as $feature)
                                <li class="flex items-start gap-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="flex-1"></div>
                    @endif

                    <form method="POST" action="{{ route('checkout.session') }}">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <button type="submit"
                            class="w-full py-2.5 px-4 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                            Plătește acum
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    <p class="mt-6 text-center text-xs text-gray-400">
        Plata este procesată securizat prin Stripe. Nu stocăm datele cardului tău.
    </p>

</div>
@endsection
