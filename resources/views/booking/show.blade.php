@extends('layouts.booking')

@section('title', 'Rezervare zi de naștere - ' . $location->name)
@section('header-title', $location->name)

@section('content')
<div class="space-y-5">

    {{-- Errors --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex gap-3">
            <i class="fas fa-exclamation-circle text-red-500 mt-0.5 flex-shrink-0"></i>
            <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('booking.show', $location) }}" id="booking-form">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 divide-y divide-gray-100">

            {{-- 1. Sală și dată --}}
            <div class="p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-hopo-purple"></i>
                    @if($singleHall) Data @else Sală și dată @endif
                </h2>

                @if($singleHall)
                    <input type="hidden" name="birthday_hall_id" id="birthday_hall_id"
                        value="{{ $halls->first()->id }}"
                        data-capacity="{{ $halls->first()->capacity }}">
                    <div>
                        <label for="reservation_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Data <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="reservation_date" id="reservation_date"
                            value="{{ old('reservation_date') }}"
                            min="{{ now()->format('Y-m-d') }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-hopo-purple text-sm">
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="birthday_hall_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Sală <span class="text-red-500">*</span>
                            </label>
                            <select name="birthday_hall_id" id="birthday_hall_id" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-hopo-purple bg-white text-sm">
                                <option value="">Selectați sala</option>
                                @foreach($halls as $h)
                                    <option value="{{ $h->id }}"
                                        data-capacity="{{ $h->capacity }}"
                                        {{ old('birthday_hall_id') == $h->id ? 'selected' : '' }}>
                                        {{ $h->name }} (max {{ $h->capacity }} copii)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="reservation_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Data <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="reservation_date" id="reservation_date"
                                value="{{ old('reservation_date') }}"
                                min="{{ now()->format('Y-m-d') }}" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-hopo-purple text-sm">
                        </div>
                    </div>
                @endif
            </div>

            {{-- 2. Pachet --}}
            <div class="p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-box-open text-hopo-purple"></i>
                    Pachet <span class="text-red-500">*</span>
                </h2>
                <div id="packages-empty-state" class="text-sm text-gray-500 py-2"></div>
                <div id="packages-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-4"></div>
            </div>

            {{-- 3. Disponibilitate și ora de start --}}
            <div class="p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-clock text-hopo-purple"></i>
                    Oră de start <span class="text-red-500">*</span>
                </h2>
                <input type="hidden" name="reservation_time" id="reservation_time" value="{{ old('reservation_time') }}" required>

                <div id="availability-placeholder" class="text-sm text-gray-400 flex items-center gap-2 py-2">
                    <i class="fas fa-info-circle text-gray-300"></i>
                    <span>{{ $singleHall ? 'Selectați data și pachetul pentru a vedea disponibilitatea în ziua respectivă.' : 'Selectați sala, data și pachetul pentru a vedea disponibilitatea în ziua respectivă.' }}</span>
                </div>

                <div id="availability-block" class="hidden">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2.5">Ziua selectată — ce ore sunt libere / ocupate</p>
                    <div class="relative mb-4">
                        <div id="timeline-bar" class="relative w-full h-14 bg-gray-100 rounded-xl overflow-hidden border border-gray-200 flex"></div>
                        <div id="timeline-labels" class="relative w-full h-5 mt-1"></div>
                    </div>
                    <div class="flex flex-wrap items-center gap-6 mb-4 text-xs text-gray-500">
                        <span class="flex items-center gap-2 flex-shrink-0"><span class="inline-block w-3.5 h-3.5 rounded-sm flex-shrink-0" style="background-color:#10b981;"></span>Liber</span>
                        <span class="flex items-center gap-2 flex-shrink-0"><span class="inline-block w-3.5 h-3.5 rounded-sm flex-shrink-0" style="background-color:#9ca3af; background-image: repeating-linear-gradient(45deg, transparent, transparent 3px, rgba(255,255,255,.6) 3px, rgba(255,255,255,.6) 6px);"></span>Ocupat</span>
                        <span class="flex items-center gap-2 flex-shrink-0"><span class="inline-block w-3.5 h-3.5 rounded-sm flex-shrink-0 bg-hopo-purple"></span>Rezervarea ta</span>
                    </div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Alege ora de start (rezervarea = ora aleasă + durata pachetului)</p>
                    <div id="start-times-grid" class="flex flex-wrap gap-2"></div>
                    <div id="selected-summary" class="hidden mt-4 p-4 rounded-xl bg-indigo-50 border-2 border-hopo-purple">
                        <p class="text-sm font-semibold text-gray-800">
                            <i class="fas fa-calendar-check text-hopo-purple mr-1.5"></i>
                            Rezervare: <span id="selected-range-label"></span>
                        </p>
                        <p class="text-xs text-gray-600 mt-1">Din ora de start până la sfârșitul duratei pachetului selectat.</p>
                    </div>
                </div>
            </div>

            {{-- 4. Detalii copil și contact --}}
            <div class="p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-birthday-cake text-hopo-purple"></i>
                    Detalii copil și contact
                </h2>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label for="child_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Numele copilului <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="child_name" id="child_name"
                                value="{{ old('child_name') }}" required
                                placeholder="ex: Maria"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-hopo-purple text-sm">
                        </div>
                        <div>
                            <label for="child_age" class="block text-sm font-medium text-gray-700 mb-1">Vârsta</label>
                            <input type="number" name="child_age" id="child_age"
                                value="{{ old('child_age') }}" min="0" max="18" placeholder="ani"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-hopo-purple text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="guardian_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nume părinte / persoană de contact <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="guardian_name" id="guardian_name"
                            value="{{ old('guardian_name') }}" required placeholder="Nume complet"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-hopo-purple text-sm">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="guardian_phone" class="block text-sm font-medium text-gray-700 mb-1">
                                Telefon <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" name="guardian_phone" id="guardian_phone"
                                value="{{ old('guardian_phone') }}" required placeholder="07xx xxx xxx"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-hopo-purple text-sm">
                        </div>
                        <div>
                            <label for="guardian_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="guardian_email" id="guardian_email"
                                value="{{ old('guardian_email') }}" placeholder="adresa@email.com"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-hopo-purple text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="number_of_children" class="block text-sm font-medium text-gray-700 mb-1">
                                Număr de copii așteptați <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="number" name="number_of_children" id="number_of_children"
                                    value="{{ old('number_of_children', 1) }}" min="1" required
                                    class="w-28 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-hopo-purple text-sm">
                                <p id="number_of_children-hint" class="text-sm text-gray-500">Selectați sala pentru limită.</p>
                            </div>
                        </div>
                        <div>
                            <label for="number_of_adults" class="block text-sm font-medium text-gray-700 mb-1">
                                Număr de adulți așteptați
                            </label>
                            <input type="number" name="number_of_adults" id="number_of_adults"
                                value="{{ old('number_of_adults') }}" min="0"
                                class="w-28 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-hopo-purple text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Observații / mențiuni speciale</label>
                        <textarea name="notes" id="notes" rows="2" placeholder="Alergii, preferințe temă, alte detalii..."
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-hopo-purple text-sm resize-none">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- 5. GDPR + Submit --}}
            <div class="p-6">
                @php $rulesUrl = $location->getEffectiveRulesUrl(); @endphp
                @if($rulesUrl)
                <label class="inline-flex items-start gap-3 cursor-pointer mb-4 block">
                    <input type="checkbox" name="rules_accept" id="rules_accept" value="1"
                        {{ old('rules_accept') ? 'checked' : '' }} required
                        class="mt-0.5 rounded border-gray-300 text-hopo-purple focus:ring-hopo-purple flex-shrink-0">
                    <span class="text-sm text-gray-700">
                        Am citit și accept
                        <a href="{{ $rulesUrl }}" target="_blank" rel="noopener noreferrer" class="text-hopo-purple underline">regulamentul locației</a>.
                        <span class="text-red-500">*</span>
                    </span>
                </label>
                @endif

                <label class="inline-flex items-start gap-3 cursor-pointer mb-5 block">
                    <input type="checkbox" name="gdpr_accept" id="gdpr_accept" value="1"
                        {{ old('gdpr_accept') ? 'checked' : '' }} required
                        class="mt-0.5 rounded border-gray-300 text-hopo-purple focus:ring-hopo-purple flex-shrink-0">
                    <span class="text-sm text-gray-700">
                        Accept prelucrarea datelor cu caracter personal conform politicii de confidențialitate.
                        <span class="text-red-500">*</span>
                    </span>
                </label>

                <div class="flex justify-end gap-4 pt-5 border-t border-gray-100">
                    <button type="submit"
                        class="w-full sm:w-auto bg-hopo-purple hover:bg-hopo-purple-dark text-white px-8 py-3 rounded-lg font-semibold shadow-sm transition-colors flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-paper-plane"></i>
                        Trimite rezervarea
                    </button>
                </div>
            </div>

        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var locationSlug = '{{ $location->slug }}';
    var oldReservationTime = '{{ old('reservation_time') }}';
    var initialPackageId = '{{ old('birthday_package_id') }}';
    var initialPackages = @json($initialPackages);

    var hallSelect = document.getElementById('birthday_hall_id');
    var dateInput = document.getElementById('reservation_date');
    var reservationTimeInput = document.getElementById('reservation_time');
    var packagesGrid = document.getElementById('packages-grid');
    var packagesEmptyState = document.getElementById('packages-empty-state');
    var availabilityPlaceholder = document.getElementById('availability-placeholder');
    var availabilityBlock = document.getElementById('availability-block');
    var timelineBar = document.getElementById('timeline-bar');
    var timelineLabels = document.getElementById('timeline-labels');
    var startTimesGrid = document.getElementById('start-times-grid');
    var selectedSummary = document.getElementById('selected-summary');
    var selectedRangeLabel = document.getElementById('selected-range-label');
    var numberOfChildrenInput = document.getElementById('number_of_children');
    var numberOfChildrenHint = document.getElementById('number_of_children-hint');

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getSelectedHallCapacity() {
        if (!hallSelect || !hallSelect.value) return null;
        // hidden input: capacity stored as data-capacity directly on the element
        if (hallSelect.tagName === 'INPUT') {
            var cap = hallSelect.getAttribute('data-capacity');
            return cap !== null && cap !== '' ? parseInt(cap, 10) : null;
        }
        var opt = hallSelect.options[hallSelect.selectedIndex];
        if (!opt || !opt.value) return null;
        var cap = opt.getAttribute('data-capacity');
        return cap !== null && cap !== '' ? parseInt(cap, 10) : null;
    }
    function getSelectedPackageId() {
        var radio = document.querySelector('.package-radio:checked');
        return radio ? radio.value : null;
    }

    function resetAvailabilitySelection() {
        reservationTimeInput.value = '';
        selectedSummary.classList.add('hidden');
        availabilityBlock.classList.add('hidden');
        availabilityPlaceholder.classList.remove('hidden');
        availabilityPlaceholder.querySelector('span').textContent = '{{ $singleHall ? "Selectați data și pachetul pentru a vedea disponibilitatea în ziua respectivă." : "Selectați sala, data și pachetul pentru a vedea disponibilitatea în ziua respectivă." }}';
    }

    function getPackagesEmptyStateMessage() {
        if (!dateInput.value) {
            return 'Selectați data pentru a vedea pachetele disponibile.';
        }

        return 'Nu există pachete disponibile în ziua selectată.';
    }

    function buildPackageMarkup(packageData, isChecked) {
        var badges = '';

        if (packageData.includes_food) {
            badges += '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700"><i class="fas fa-utensils mr-1 text-xs"></i>Mâncare inclusă</span>';
        }

        if (packageData.includes_decorations) {
            badges += '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-700"><i class="fas fa-star mr-1 text-xs"></i>Decorațiuni incluse</span>';
        }

        var description = packageData.description
            ? '<p class="text-sm text-gray-600 leading-relaxed">' + escapeHtml(packageData.description) + '</p>'
            : '';

        var badgesWrapper = badges
            ? '<div class="flex gap-2 flex-wrap mt-auto pt-1">' + badges + '</div>'
            : '';

        return '<label class="cursor-pointer block">' +
            '<input type="radio" name="birthday_package_id" value="' + packageData.id + '" class="sr-only package-radio" data-duration-minutes="' + packageData.duration_minutes + '"' + (isChecked ? ' checked' : '') + '>' +
            '<div class="package-card border-2 border-gray-200 rounded-xl p-4 hover:border-hopo-purple transition-colors h-full flex flex-col gap-2">' +
                '<div class="flex items-start justify-between gap-2">' +
                    '<h4 class="font-semibold text-gray-900 leading-tight">' + escapeHtml(packageData.name) + '</h4>' +
                '</div>' +
                '<p class="text-xs text-gray-500"><i class="fas fa-clock text-gray-300 mr-1"></i>' + packageData.duration_minutes + ' min' +
                    (packageData.available_from && packageData.available_until ? ' &nbsp;·&nbsp; <i class="fas fa-calendar-clock text-gray-300 mr-1"></i>' + packageData.available_from + ' – ' + packageData.available_until : '') +
                '</p>' +
                description +
                badgesWrapper +
            '</div>' +
        '</label>';
    }

    function bindPackageListeners() {
        document.querySelectorAll('.package-radio').forEach(function (radio) {
            radio.addEventListener('change', function () {
                highlightSelectedPackage();
                loadAvailability();
            });
        });
    }

    function renderPackages(packages, selectedPackageId) {
        packagesGrid.innerHTML = '';

        if (!packages.length) {
            packagesEmptyState.textContent = getPackagesEmptyStateMessage();
            packagesEmptyState.classList.remove('hidden');
            resetAvailabilitySelection();
            highlightSelectedPackage();
            return;
        }

        packagesEmptyState.classList.add('hidden');
        packagesGrid.innerHTML = packages.map(function (packageData) {
            return buildPackageMarkup(packageData, String(packageData.id) === String(selectedPackageId));
        }).join('');

        bindPackageListeners();
        highlightSelectedPackage();
        loadAvailability();
    }

    function getDateValue() {
        if (!dateInput.value) return '';
        if (dateInput.valueAsDate) {
            var d = dateInput.valueAsDate;
            var yyyy = d.getUTCFullYear();
            var mm = String(d.getUTCMonth() + 1).padStart(2, '0');
            var dd = String(d.getUTCDate()).padStart(2, '0');
            return yyyy + '-' + mm + '-' + dd;
        }
        return dateInput.value;
    }

    function loadPackages(preferredPackageId) {
        var dateValue = getDateValue();
        if (!dateValue) {
            renderPackages([], null);
            return;
        }

        var today = new Date();
        var todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
        if (dateValue < todayStr) {
            packagesGrid.innerHTML = '';
            packagesEmptyState.textContent = 'Selectați o dată curentă sau viitoare.';
            packagesEmptyState.classList.remove('hidden');
            resetAvailabilitySelection();
            return;
        }

        packagesGrid.innerHTML = '';
        packagesEmptyState.textContent = 'Se încarcă pachetele disponibile...';
        packagesEmptyState.classList.remove('hidden');
        resetAvailabilitySelection();

        var url = '/booking/' + locationSlug + '/packages?date=' + encodeURIComponent(dateValue);
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(function (data) {
                renderPackages(data.packages || [], preferredPackageId);
            })
            .catch(function (err) {
                packagesGrid.innerHTML = '';
                packagesEmptyState.textContent = 'Eroare la încărcarea pachetelor. Reîncărcați pagina.';
                packagesEmptyState.classList.remove('hidden');
                resetAvailabilitySelection();
                console.error(err);
            });
    }

    function updateNumberOfChildrenLimit() {
        var hallCap = getSelectedHallCapacity();
        if (hallCap === null) {
            numberOfChildrenInput.removeAttribute('max');
            numberOfChildrenHint.textContent = 'Selectați sala pentru limită.';
            return;
        }
        numberOfChildrenInput.setAttribute('max', hallCap);
        numberOfChildrenHint.textContent = 'Max. ' + hallCap + ' copii.';
        var val = parseInt(numberOfChildrenInput.value, 10);
        if (!isNaN(val) && val > hallCap) numberOfChildrenInput.value = hallCap;
    }

    function timeToMinutes(t) {
        var parts = t.split(':');
        return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
    }
    function minutesToTime(m) {
        var h = Math.floor(m / 60);
        var min = m % 60;
        return (h < 10 ? '0' : '') + h + ':' + (min < 10 ? '0' : '') + min;
    }

    function selectStartTime(startTime) {
        reservationTimeInput.value = startTime;
        var duration = parseInt(document.querySelector('.package-radio:checked').getAttribute('data-duration-minutes'), 10);
        var startM = timeToMinutes(startTime);
        var endM = startM + duration;
        var endTime = minutesToTime(endM);
        selectedRangeLabel.textContent = startTime + ' – ' + endTime;
        selectedSummary.classList.remove('hidden');
        startTimesGrid.querySelectorAll('button').forEach(function (btn) {
            if (btn.dataset.start === startTime) {
                btn.classList.remove('border-gray-300', 'bg-white');
                btn.classList.add('border-hopo-purple', 'bg-hopo-purple', 'text-white');
            } else {
                btn.classList.remove('border-hopo-purple', 'bg-hopo-purple', 'text-white');
                btn.classList.add('border-gray-300', 'bg-white');
            }
        });
    }

    function renderAvailability(data) {
        var dayStartM = timeToMinutes(data.day_start);
        var dayEndM = timeToMinutes(data.day_end);
        var totalSpan = dayEndM - dayStartM;
        var duration = data.duration_minutes;
        var occupied = data.occupied || [];

        timelineBar.innerHTML = '';
        timelineLabels.innerHTML = '';

        var freeSegments = [];
        var pos = dayStartM;
        occupied.forEach(function (o) {
            var s = timeToMinutes(o.start);
            var e = timeToMinutes(o.end);
            if (pos < s) freeSegments.push({ start: pos, end: s });
            pos = Math.max(pos, e);
        });
        if (pos < dayEndM) freeSegments.push({ start: pos, end: dayEndM });

        var possibleStarts = [];
        var step = 30;
        freeSegments.forEach(function (seg) {
            for (var t = seg.start; t + duration <= seg.end; t += step) {
                possibleStarts.push(minutesToTime(t));
            }
        });
        possibleStarts = possibleStarts.filter(function (v, i, a) { return a.indexOf(v) === i; }).sort();

        var segs = [];
        var prevEnd = dayStartM;
        occupied.forEach(function (o) {
            var s = timeToMinutes(o.start);
            var e = timeToMinutes(o.end);
            if (s > prevEnd) {
                segs.push({ start: prevEnd, end: s, free: true });
            }
            segs.push({ start: s, end: e, free: false });
            prevEnd = e;
        });
        if (prevEnd < dayEndM) segs.push({ start: prevEnd, end: dayEndM, free: true });

        timelineBar.innerHTML = '';
        segs.forEach(function (seg) {
            var pct = ((seg.end - seg.start) / totalSpan) * 100;
            var div = document.createElement('div');
            div.style.width = pct + '%';
            div.className = 'flex-shrink-0 relative flex items-center justify-center ' + (seg.free ? 'bg-emerald-500' : 'bg-red-400');
            if (seg.free) {
                var pattern = document.createElement('div');
                pattern.className = 'absolute inset-0 opacity-0';
                div.appendChild(pattern);
            } else {
                var pattern = document.createElement('div');
                pattern.className = 'absolute inset-0 opacity-25';
                pattern.style.backgroundImage = 'repeating-linear-gradient(45deg, transparent, transparent 4px, rgba(255,255,255,.5) 4px, rgba(255,255,255,.5) 8px)';
                div.appendChild(pattern);
            }
            timelineBar.appendChild(div);
        });

        var labelStep = totalSpan <= 180 ? 60 : 120;
        for (var m = dayStartM; m <= dayEndM; m += labelStep) {
            if (m > dayStartM && m < dayEndM) {
                var lbl = document.createElement('span');
                lbl.className = 'text-[10px] text-gray-500 font-medium absolute';
                lbl.style.left = ((m - dayStartM) / totalSpan) * 100 + '%';
                lbl.style.transform = 'translateX(-50%)';
                lbl.textContent = minutesToTime(m);
                timelineLabels.appendChild(lbl);
            }
        }
        var lblEnd = document.createElement('span');
        lblEnd.className = 'text-[10px] text-gray-500 font-medium absolute';
        lblEnd.style.right = '0';
        lblEnd.textContent = data.day_end;
        timelineLabels.appendChild(lblEnd);

        startTimesGrid.innerHTML = '';
        possibleStarts.forEach(function (startTime) {
            var endM = timeToMinutes(startTime) + duration;
            var endTime = minutesToTime(endM);
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.start = startTime;
            btn.className = 'start-time-btn px-4 py-2 rounded-lg border-2 border-gray-300 bg-white text-sm font-medium text-gray-700 hover:border-hopo-purple hover:bg-indigo-50 transition-all';
            btn.textContent = startTime + ' – ' + endTime;
            btn.addEventListener('click', function () { selectStartTime(startTime); });
            startTimesGrid.appendChild(btn);
        });

        if (possibleStarts.length === 0) {
            startTimesGrid.innerHTML = '<p class="text-sm text-gray-500">Nu există intervale disponibile în această zi pentru durata pachetului selectat.</p>';
        }

        availabilityPlaceholder.classList.add('hidden');
        availabilityBlock.classList.remove('hidden');
        selectedSummary.classList.add('hidden');
        reservationTimeInput.value = '';
        if (oldReservationTime && possibleStarts.indexOf(oldReservationTime) !== -1) {
            selectStartTime(oldReservationTime);
        }
    }

    function loadAvailability() {
        var date = getDateValue();
        var hallId = hallSelect.value;
        var packageId = getSelectedPackageId();
        if (!date || !hallId || !packageId) {
            availabilityBlock.classList.add('hidden');
            availabilityPlaceholder.classList.remove('hidden');
            availabilityPlaceholder.querySelector('span').textContent = '{{ $singleHall ? "Selectați data și pachetul pentru a vedea disponibilitatea în ziua respectivă." : "Selectați sala, data și pachetul pentru a vedea disponibilitatea în ziua respectivă." }}';
            reservationTimeInput.value = '';
            return;
        }
        availabilityPlaceholder.classList.remove('hidden');
        availabilityPlaceholder.querySelector('span').textContent = 'Se încarcă disponibilitatea...';
        availabilityBlock.classList.add('hidden');
        var url = '/booking/' + locationSlug + '/availability?date=' + encodeURIComponent(date) + '&birthday_hall_id=' + encodeURIComponent(hallId) + '&birthday_package_id=' + encodeURIComponent(packageId);
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(renderAvailability)
            .catch(function (err) {
                availabilityPlaceholder.querySelector('span').textContent = 'Eroare la încărcare. Reîncărcați pagina.';
                availabilityBlock.classList.add('hidden');
                console.error(err);
            });
    }

    function highlightSelectedPackage() {
        document.querySelectorAll('.package-radio').forEach(function (radio) {
            var card = radio.nextElementSibling;
            if (!card) return;
            if (radio.checked) {
                card.classList.remove('border-gray-200');
                card.classList.add('border-hopo-purple', 'bg-indigo-50', 'ring-2', 'ring-indigo-100');
            } else {
                card.classList.remove('border-hopo-purple', 'bg-indigo-50', 'ring-2', 'ring-indigo-100');
                card.classList.add('border-gray-200');
            }
        });
    }

    if (hallSelect.tagName === 'SELECT') {
        hallSelect.addEventListener('change', function () { loadAvailability(); updateNumberOfChildrenLimit(); });
    }
    dateInput.addEventListener('change', function () {
        loadPackages(getSelectedPackageId());
    });
    numberOfChildrenInput.addEventListener('input', updateNumberOfChildrenLimit);

    renderPackages(initialPackages, initialPackageId);
    if (!dateInput.value) {
        renderPackages([], null);
    }

    updateNumberOfChildrenLimit();
});
</script>
@endsection
