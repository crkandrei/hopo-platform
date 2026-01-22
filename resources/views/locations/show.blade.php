@extends('layouts.app')

@section('title', 'Detalii Locație')
@section('page-title', 'Detalii Locație')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $location->name }} 📍</h1>
                <p class="text-gray-600 text-lg">Detalii locație și statistici</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('locations.edit', $location) }}" 
                   class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all duration-200 font-medium flex items-center shadow-md">
                    <i class="fas fa-edit mr-2"></i>
                    Editează
                </a>
                <a href="{{ route('locations.index') }}" 
                   class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium flex items-center shadow-md">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Înapoi
                </a>
            </div>
        </div>
    </div>

    <!-- Location Details -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Informații Locație</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Nume</label>
                <p class="text-lg text-gray-900">{{ $location->name }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Companie</label>
                <p class="text-lg text-gray-900">
                    @if($location->company)
                        <a href="{{ route('companies.show', $location->company) }}" class="text-indigo-600 hover:text-indigo-900">
                            {{ $location->company->name }}
                        </a>
                    @else
                        -
                    @endif
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Slug</label>
                <p class="text-lg text-gray-900">{{ $location->slug }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Adresă</label>
                <p class="text-lg text-gray-900">{{ $location->address ?? '-' }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Telefon</label>
                <p class="text-lg text-gray-900">{{ $location->phone ?? '-' }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                <p class="text-lg text-gray-900">{{ $location->email ?? '-' }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Tarif pe Oră</label>
                <p class="text-lg text-gray-900">{{ number_format($location->price_per_hour, 2) }} RON</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                @if($location->is_active)
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Activ
                    </span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                        Inactiv
                    </span>
                @endif
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Data creării</label>
                <p class="text-lg text-gray-900">{{ $location->created_at->format('d.m.Y H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                    <i class="fas fa-users text-indigo-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Copii</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $location->children->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                    <i class="fas fa-calendar-check text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Sesiuni</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $location->playSessions->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                    <i class="fas fa-user-tie text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Utilizatori</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $location->users->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Users -->
    @if($location->users->count() > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Utilizatori Asociați ({{ $location->users->count() }})</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nume</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($location->users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->role->name ?? '-' }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
