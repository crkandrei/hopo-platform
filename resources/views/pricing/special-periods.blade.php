@extends('layouts.app')

@section('title', 'Perioade Speciale')
@section('page-title', 'Perioade Speciale')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
        <div class="flex flex-wrap justify-between items-start gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Perioade Speciale</h1>
                <p class="text-gray-600 text-lg">Gestionați tarife pentru perioade speciale (sărbători, evenimente) pentru <strong>{{ $location->name }}</strong>. Puteți seta tarif pe oră sau tarife pe durate (1h, 2h, 3h, 4h).</p>
            </div>
            <a href="{{ route('pricing.index') }}{{ Auth::user()->isSuperAdmin() ? '?location_id=' . $location->id : '' }}"
               class="shrink-0 bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium flex items-center shadow-md">
                <i class="fas fa-arrow-left mr-2"></i>
                Înapoi
            </a>
        </div>
    </div>

    <!-- Info Box -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
            <div>
                <h3 class="font-medium text-blue-900 mb-2">Informații importante</h3>
                <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                    <li>Perioadele speciale au prioritate peste tarifele săptămânale și tarifele pe durate ale locației</li>
                    <li>Puteți alege tarif pe oră (clasic) sau tarife pe durate (1h, 2h, 3h, 4h) pentru fiecare perioadă</li>
                    <li>Nu pot exista perioade speciale care se suprapun pentru aceeași locație</li>
                    <li>Dacă o sesiune începe într-o perioadă specială, se folosește tariful perioadei speciale</li>
                </ul>
            </div>
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
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg auto-hide-alert">
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('pricing.special-periods.store') }}" id="addForm">
            @csrf
            <input type="hidden" name="location_id" value="{{ $location->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nume perioadă *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           placeholder="ex. Sărbători de iarnă"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tip tarifare *</label>
                    <div class="flex flex-wrap gap-6">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="pricing_mode" value="flat_hourly" {{ old('pricing_mode', 'flat_hourly') === 'flat_hourly' ? 'checked' : '' }} class="form-pricing-mode">
                            <span>Tarif pe oră</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="pricing_mode" value="tiered" {{ old('pricing_mode') === 'tiered' ? 'checked' : '' }} class="form-pricing-mode">
                            <span>Tarife pe durate (1h, 2h, 3h, 4h)</span>
                        </label>
                    </div>
                </div>

                <div id="add-flat-rate" class="flat-fields">
                    <label for="hourly_rate" class="block text-sm font-medium text-gray-700 mb-1">Tarif pe oră (RON) *</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="hourly_rate" id="hourly_rate" value="{{ old('hourly_rate') }}"
                               step="0.01" min="0" placeholder="0.00"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <span class="text-gray-600 font-medium whitespace-nowrap">RON/oră</span>
                    </div>
                </div>

                <div id="add-tiered-rates" class="tiered-fields hidden space-y-3">
                    <p class="text-sm text-gray-600">Preț total per sesiune (RON)</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label for="price_1h" class="block text-xs font-medium text-gray-600 mb-1">1 oră</label>
                            <input type="number" name="price_1h" id="price_1h" value="{{ old('price_1h') }}" step="0.01" min="0" placeholder="—"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="price_2h" class="block text-xs font-medium text-gray-600 mb-1">2 ore</label>
                            <input type="number" name="price_2h" id="price_2h" value="{{ old('price_2h') }}" step="0.01" min="0" placeholder="—"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="price_3h" class="block text-xs font-medium text-gray-600 mb-1">3 ore</label>
                            <input type="number" name="price_3h" id="price_3h" value="{{ old('price_3h') }}" step="0.01" min="0" placeholder="—"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="price_4h" class="block text-xs font-medium text-gray-600 mb-1">4 ore</label>
                            <input type="number" name="price_4h" id="price_4h" value="{{ old('price_4h') }}" step="0.01" min="0" placeholder="—"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label for="overflow_price_per_hour" class="block text-sm font-medium text-gray-700 mb-1">Preț/oră peste 4h (RON)</label>
                        <input type="number" name="overflow_price_per_hour" id="overflow_price_per_hour" value="{{ old('overflow_price_per_hour') }}"
                               step="0.01" min="0" placeholder="0.00"
                               class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <span class="text-gray-600 text-sm ml-2">RON/oră</span>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Perioadă *</label>
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex-1 min-w-[140px]">
                            <label for="start_date" class="block text-xs font-medium text-gray-500 mb-1">Data început</label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="flex-1 min-w-[140px]">
                            <label for="end_date" class="block text-xs font-medium text-gray-500 mb-1">Data sfârșit</label>
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tip</th>
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
                            @if($period->isTiered())
                                <span class="text-xs font-medium text-indigo-700">Pe durate</span>
                            @else
                                <span class="text-xs font-medium text-gray-600">Pe oră</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($period->isTiered())
                                <div class="text-sm text-gray-900">
                                    @php $tiers = $period->getTierPrices(); @endphp
                                    @if(!empty($tiers))
                                        @foreach($tiers as $h => $p)
                                            <span class="mr-2">{{ $h }}h: {{ number_format($p, 2, '.', '') }} RON</span>
                                        @endforeach
                                        @if($period->overflow_price_per_hour)
                                            <span class="text-gray-500">| peste: {{ number_format($period->overflow_price_per_hour, 2, '.', '') }} RON/oră</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </div>
                            @else
                                <div class="text-sm font-medium text-gray-900">
                                    {{ number_format($period->hourly_rate, 2, '.', '') }} RON/oră
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button type="button" class="text-yellow-600 hover:text-yellow-900 edit-period-btn"
                                        data-id="{{ $period->id }}"
                                        data-name="{{ e($period->name) }}"
                                        data-start="{{ $period->start_date->format('Y-m-d') }}"
                                        data-end="{{ $period->end_date->format('Y-m-d') }}"
                                        data-mode="{{ $period->pricing_mode ?? 'flat_hourly' }}"
                                        data-hourly="{{ $period->hourly_rate }}"
                                        data-price-1h="{{ $period->price_1h ?? '' }}"
                                        data-price-2h="{{ $period->price_2h ?? '' }}"
                                        data-price-3h="{{ $period->price_3h ?? '' }}"
                                        data-price-4h="{{ $period->price_4h ?? '' }}"
                                        data-overflow="{{ $period->overflow_price_per_hour ?? '' }}">
                                    <i class="fas fa-edit"></i> Editează
                                </button>
                                <form method="POST" action="{{ route('pricing.special-periods.destroy', $period->id) }}" class="inline"
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tip tarifare *</label>
                        <div class="flex flex-wrap gap-6">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="pricing_mode" value="flat_hourly" id="edit_mode_flat" class="edit-pricing-mode">
                                <span>Tarif pe oră</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="pricing_mode" value="tiered" id="edit_mode_tiered" class="edit-pricing-mode">
                                <span>Tarife pe durate (1h, 2h, 3h, 4h)</span>
                            </label>
                        </div>
                    </div>
                    <div id="edit-flat-rate" class="edit-flat-fields">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tarif pe oră (RON) *</label>
                        <input type="number" name="hourly_rate" id="edit_hourly_rate" step="0.01" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div id="edit-tiered-rates" class="edit-tiered-fields hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preț total per sesiune (RON)</label>
                        <div class="grid grid-cols-4 gap-2 mb-2">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">1h</label>
                                <input type="number" name="price_1h" id="edit_price_1h" step="0.01" min="0" placeholder="—"
                                       class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">2h</label>
                                <input type="number" name="price_2h" id="edit_price_2h" step="0.01" min="0" placeholder="—"
                                       class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">3h</label>
                                <input type="number" name="price_3h" id="edit_price_3h" step="0.01" min="0" placeholder="—"
                                       class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">4h</label>
                                <input type="number" name="price_4h" id="edit_price_4h" step="0.01" min="0" placeholder="—"
                                       class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Preț/oră peste 4h (RON)</label>
                            <input type="number" name="overflow_price_per_hour" id="edit_overflow" step="0.01" min="0" placeholder="0"
                                   class="w-28 px-2 py-1.5 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Perioadă *</label>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Data început</label>
                                <input type="date" name="start_date" id="edit_start_date" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Data sfârșit</label>
                                <input type="date" name="end_date" id="edit_end_date" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Anulează
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Salvează
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var addForm = document.getElementById('addForm');
    if (addForm) {
        var modeRadios = addForm.querySelectorAll('.form-pricing-mode');
        var flatBlock = document.getElementById('add-flat-rate');
        var tieredBlock = document.getElementById('add-tiered-rates');
        var hourlyInput = document.getElementById('hourly_rate');

        function toggleAddMode() {
            var tiered = addForm.querySelector('input[name="pricing_mode"]:checked').value === 'tiered';
            if (tiered) {
                flatBlock.classList.add('hidden');
                tieredBlock.classList.remove('hidden');
                if (hourlyInput) hourlyInput.removeAttribute('required');
            } else {
                flatBlock.classList.remove('hidden');
                tieredBlock.classList.add('hidden');
                if (hourlyInput) hourlyInput.setAttribute('required', 'required');
            }
        }
        modeRadios.forEach(function(r) { r.addEventListener('change', toggleAddMode); });
        toggleAddMode();
    }

    document.querySelectorAll('.edit-period-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var name = this.getAttribute('data-name');
            var start = this.getAttribute('data-start');
            var end = this.getAttribute('data-end');
            var mode = this.getAttribute('data-mode') || 'flat_hourly';
            var hourly = this.getAttribute('data-hourly');
            var p1 = this.getAttribute('data-price-1h') || '';
            var p2 = this.getAttribute('data-price-2h') || '';
            var p3 = this.getAttribute('data-price-3h') || '';
            var p4 = this.getAttribute('data-price-4h') || '';
            var overflow = this.getAttribute('data-overflow') || '';

            document.getElementById('editForm').action = '{{ url("/pricing/special-periods") }}/' + id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_start_date').value = start;
            document.getElementById('edit_end_date').value = end;
            document.getElementById('edit_hourly_rate').value = hourly || '';
            document.getElementById('edit_price_1h').value = p1;
            document.getElementById('edit_price_2h').value = p2;
            document.getElementById('edit_price_3h').value = p3;
            document.getElementById('edit_price_4h').value = p4;
            document.getElementById('edit_overflow').value = overflow;

            document.getElementById('edit_mode_flat').checked = (mode === 'flat_hourly');
            document.getElementById('edit_mode_tiered').checked = (mode === 'tiered');

            var editFlat = document.getElementById('edit-flat-rate');
            var editTiered = document.getElementById('edit-tiered-rates');
            if (mode === 'tiered') {
                editFlat.classList.add('hidden');
                editTiered.classList.remove('hidden');
                document.getElementById('edit_hourly_rate').removeAttribute('required');
            } else {
                editFlat.classList.remove('hidden');
                editTiered.classList.add('hidden');
                document.getElementById('edit_hourly_rate').setAttribute('required', 'required');
            }
            document.getElementById('editModal').classList.remove('hidden');
        });
    });

    var editModeRadios = document.querySelectorAll('.edit-pricing-mode');
    if (editModeRadios.length) {
        editModeRadios.forEach(function(r) {
            r.addEventListener('change', function() {
                var tiered = document.getElementById('edit_mode_tiered').checked;
                var editFlat = document.getElementById('edit-flat-rate');
                var editTiered = document.getElementById('edit-tiered-rates');
                var editHourly = document.getElementById('edit_hourly_rate');
                if (tiered) {
                    editFlat.classList.add('hidden');
                    editTiered.classList.remove('hidden');
                    editHourly.removeAttribute('required');
                } else {
                    editFlat.classList.remove('hidden');
                    editTiered.classList.add('hidden');
                    editHourly.setAttribute('required', 'required');
                }
            });
        });
    }
});

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection
