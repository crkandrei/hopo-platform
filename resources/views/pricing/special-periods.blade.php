@extends('layouts.app')

@section('title', 'Perioade Speciale')
@section('page-title', 'Perioade Speciale')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Perioade Speciale 🎉</h1>
                <p class="text-gray-600 text-lg">Gestionați tarife pentru perioade speciale (ex. ziua de deschidere) pentru <strong>{{ $location->name }}</strong></p>
            </div>
            <a href="{{ route('pricing.index') }}{{ Auth::user()->isSuperAdmin() ? '?location_id=' . $location->id : '' }}" 
               class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium flex items-center shadow-md">
                <i class="fas fa-arrow-left mr-2"></i>
                Înapoi
            </a>
        </div>
    </div>

    <!-- Add New Period Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Adaugă Perioadă Specială</h2>
        
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('pricing.special-periods.store') }}">
            @csrf
            <input type="hidden" name="location_id" value="{{ $location->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nume perioadă *</label>
                    <input type="text" 
                           name="name" 
                           id="name"
                           value="{{ old('name') }}"
                           required
                           placeholder="ex. Ziua de deschidere"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="hourly_rate" class="block text-sm font-medium text-gray-700 mb-1">Tarif pe oră (RON) *</label>
                    <div class="flex items-center gap-2">
                        <input type="number" 
                               name="hourly_rate" 
                               id="hourly_rate"
                               value="{{ old('hourly_rate') }}"
                               step="0.01" 
                               min="0"
                               required
                               placeholder="0.00"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <span class="text-gray-600 font-medium whitespace-nowrap">RON/oră</span>
                    </div>
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Data început *</label>
                    <input type="date" 
                           name="start_date" 
                           id="start_date"
                           value="{{ old('start_date') }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Data sfârșit *</label>
                    <input type="date" 
                           name="end_date" 
                           id="end_date"
                           value="{{ old('end_date') }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                    <i class="fas fa-plus mr-2"></i>
                    Adaugă Perioadă
                </button>
            </div>
        </form>
    </div>

    <!-- Existing Periods -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Perioade Speciale Configurate</h2>

        @if($specialPeriods->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nume</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perioadă</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarif</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acțiuni</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($specialPeriods as $period)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $period->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $period->start_date->format('d.m.Y') }} - {{ $period->end_date->format('d.m.Y') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ number_format($period->hourly_rate, 2, '.', '') }} RON/oră
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button onclick="editPeriod({{ $period->id }}, '{{ $period->name }}', '{{ $period->start_date->format('Y-m-d') }}', '{{ $period->end_date->format('Y-m-d') }}', {{ $period->hourly_rate }})" 
                                        class="text-yellow-600 hover:text-yellow-900">
                                    <i class="fas fa-edit"></i> Editează
                                </button>
                                <form method="POST" 
                                      action="{{ route('pricing.special-periods.destroy', $period->id) }}" 
                                      class="inline"
                                      onsubmit="return confirm('Sigur vrei să ștergi această perioadă specială?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i> Șterge
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-calendar-times text-gray-400 text-5xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nu există perioade speciale</h3>
            <p class="text-gray-600">Adăugați prima perioadă specială folosind formularul de mai sus</p>
        </div>
        @endif
    </div>

    <!-- Info Box -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
            <div>
                <h3 class="font-medium text-blue-900 mb-2">Informații importante</h3>
                <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                    <li>Perioadele speciale au prioritate peste tarifele săptămânale</li>
                    <li>Nu pot exista perioade speciale care se suprapun pentru același tenant</li>
                    <li>Dacă o sesiune începe într-o perioadă specială, se va folosi tariful perioadei speciale</li>
                    <li>Pentru a edita o perioadă, apăsați butonul "Editează" și completați formularul de mai sus</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeEditModal()"></div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full w-full">
            <form id="editForm" method="POST" class="bg-white">
                @csrf
                @method('PUT')
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Editează Perioadă Specială</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nume perioadă *</label>
                        <input type="text" name="name" id="edit_name" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tarif pe oră (RON) *</label>
                        <input type="number" name="hourly_rate" id="edit_hourly_rate" step="0.01" min="0" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data început *</label>
                        <input type="date" name="start_date" id="edit_start_date" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data sfârșit *</label>
                        <input type="date" name="end_date" id="edit_end_date" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Anulează
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Salvează
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editPeriod(id, name, startDate, endDate, hourlyRate) {
    document.getElementById('editForm').action = '{{ url("/pricing/special-periods") }}/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_start_date').value = startDate;
    document.getElementById('edit_end_date').value = endDate;
    document.getElementById('edit_hourly_rate').value = hourlyRate;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection

