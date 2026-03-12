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
                <p class="text-gray-600 text-lg">Hub de configurare pentru locația selectată</p>
            </div>
            <div class="flex gap-3">
                @if(Auth::user() && (Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin()))
                <a href="{{ route('pricing.index', ['location_id' => $location->id]) }}" 
                   class="inline-flex items-center px-6 py-3 rounded-lg font-medium shadow-md transition-all duration-200 bg-indigo-600 text-white hover:bg-indigo-700 border border-indigo-700">
                    <i class="fas fa-dollar-sign mr-2" aria-hidden="true"></i>
                    <span>Configurare Tarife</span>
                </a>
                @endif
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

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-2">Catalog și Monetizare</h2>
        <p class="text-sm text-gray-600 mb-4">Gestionează produsele, tarifele și resursele folosite în fluxurile de plată pentru această locație.</p>
        <div class="flex flex-wrap gap-4 items-center mb-4">
            <a href="{{ route('products.index') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                <i class="fas fa-box mr-2"></i>Produse
            </a>
            <a href="{{ route('pricing.index', ['location_id' => $location->id]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                <i class="fas fa-dollar-sign mr-2"></i>Tarife
            </a>
            <a href="{{ route('locations.packages.index', $location) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                <i class="fas fa-file-invoice-dollar mr-2"></i>Pachete Bon Specific
            </a>
            <a href="{{ route('locations.vouchers.index', $location) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                <i class="fas fa-ticket-alt mr-2"></i>Vouchere
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-2">Aniversări și Booking</h2>
        <p class="text-sm text-gray-600 mb-4">Configurează oferta de aniversări și urmărește rezervările pentru locația curentă.</p>
        <div class="flex flex-wrap gap-4 items-center mb-4">
            <a href="{{ route('locations.birthday-halls.index', $location) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                <i class="fas fa-door-open mr-2"></i>Gestionează săli
            </a>
            <a href="{{ route('locations.birthday-packages.index', $location) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                <i class="fas fa-gift mr-2"></i>Gestionează pachete
            </a>
            <a href="{{ route('birthday-reservations.index') }}?location_id={{ $location->id }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                <i class="fas fa-calendar-check mr-2"></i>Vezi rezervări
            </a>
        </div>
        <div class="pt-4 border-t border-gray-200">
            <label class="block text-sm font-medium text-gray-700 mb-1">URL public pentru clienți</label>
            <div class="flex flex-wrap gap-2 items-center">
                <input type="text" readonly value="{{ url('/booking/' . $location->slug) }}" id="booking-url" class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                <button type="button" id="copy-booking-link-btn" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                    Copiază link pentru clienți
                </button>
                <script>
                document.getElementById('copy-booking-link-btn').addEventListener('click', function() {
                    var btn = this;
                    navigator.clipboard.writeText(document.getElementById('booking-url').value);
                    btn.textContent = 'Copiat!';
                    setTimeout(function() { btn.textContent = 'Copiază link pentru clienți'; }, 2000);
                });
                </script>
            </div>
        </div>
    </div>

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
