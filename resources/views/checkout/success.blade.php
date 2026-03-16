@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto px-4 py-16 text-center">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-10">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-3">Plată procesată</h1>
        <p class="text-gray-600 mb-2">
            Plata a fost procesată cu succes. Abonamentul tău va fi activat automat în câteva secunde.
        </p>
        <p class="text-sm text-gray-400 mb-8">
            Vei primi o confirmare prin email. Dacă abonamentul nu apare activ în câteva minute, contactează-ne la contact@hopo.ro.
        </p>
        <a href="{{ route('dashboard') }}"
           class="inline-block px-6 py-3 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
            Înapoi la dashboard
        </a>
    </div>
</div>
@endsection
