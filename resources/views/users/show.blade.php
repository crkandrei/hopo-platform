@extends('layouts.app')

@section('title', 'Detalii Utilizator')
@section('page-title', 'Detalii Utilizator')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Detalii Utilizator 👤</h1>
                <p class="text-gray-600 text-lg">Informații despre utilizator</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('users.edit', $user) }}" 
                   class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all duration-200 font-medium flex items-center shadow-md">
                    <i class="fas fa-edit mr-2"></i>
                    Editează
                </a>
                <a href="{{ route('users.index') }}" 
                   class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium flex items-center shadow-md">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Înapoi
                </a>
            </div>
        </div>
    </div>

    <!-- User Details -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Nume Complet</label>
                <p class="text-lg font-semibold text-gray-900">{{ $user->name }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Username</label>
                <p class="text-lg font-semibold text-gray-900">{{ $user->username }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                <p class="text-lg text-gray-900">{{ $user->email ?? '-' }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Rol</label>
                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                    @if($user->role->name === 'SUPER_ADMIN') bg-purple-100 text-purple-800
                    @elseif($user->role->name === 'COMPANY_ADMIN') bg-blue-100 text-blue-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ $user->role->display_name }}
                </span>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Companie</label>
                <p class="text-lg text-gray-900">{{ $user->company->name ?? ($user->location->company->name ?? '-') }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Locație</label>
                <p class="text-lg text-gray-900">{{ $user->location->name ?? '-' }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                @if($user->status === 'active')
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                        Activ
                    </span>
                @else
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                        Inactiv
                    </span>
                @endif
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Data Creării</label>
                <p class="text-lg text-gray-900">{{ $user->created_at->format('d.m.Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
