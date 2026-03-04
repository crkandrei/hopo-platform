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

        {{-- 1. Sală și dată ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-5">
            <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-door-open text-hopo-purple"></i>
                Sală și dată
            </h2>
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
        </div>

        {{-- 2. Pachet ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-5">
            <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-box-open text-hopo-purple"></i>
                Pachet <span class="text-red-500">*</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($packages as $p)
                <label class="cursor-pointer block">
                    <input type="radio" name="birthday_package_id" value="{{ $p->id }}"
                        class="sr-only package-radio"
                        data-price="{{ $p->price }}"
                        data-max-children="{{ $p->max_children }}"
                        data-duration-minutes="{{ $p->duration_minutes }}"
                        {{ old('birthday_package_id') == $p->id ? 'checked' : '' }}>
                    <div class="package-card border-2 border-gray-200 rounded-xl p-4 hover:border-hopo-purple transition-colors h-full flex flex-col gap-2 {{ old('birthday_package_id') == $p->id ? 'border-hopo-purple bg-indigo-50 ring-2 ring-indigo-100' : '' }}">
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="font-semibold text-gray-900 leading-tight">{{ $p->name }}</h4>
                            <div class="text-right flex-shrink-0">
                                <div class="text-xl font-bold text-hopo-purple leading-none">{{ number_format($p->price, 0) }} <span class="text-xs font-medium">RON</span></div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-clock text-gray-300 mr-1"></i>{{ $p->duration_minutes }} min
                            &nbsp;·&nbsp;
                            <i class="fas fa-child text-gray-300 mr-1"></i>max {{ $p->max_children }} copii
                        </p>
                        @if($p->description)
                            <p class="text-sm text-gray-600 leading-relaxed">{{ $p->description }}</p>
                        @endif
                        @if($p->includes_food || $p->includes_decorations)
                            <div class="flex gap-2 flex-wrap mt-auto pt-1">
                                @if($p->includes_food)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                                        <i class="fas fa-utensils mr-1 text-xs"></i>Mâncare inclusă
                                    </span>
                                @endif
                                @if($p->includes_decorations)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-700">
                                        <i class="fas fa-star mr-1 text-xs"></i>Decorațiuni incluse
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- 3. Disponibilitate și ora de start ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-5">
            <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-clock text-hopo-purple"></i>
                Disponibilitate și ora de start <span class="text-red-500">*</span>
            </h2>
            <input type="hidden" name="reservation_time" id="reservation_time" value="{{ old('reservation_time') }}" required>

            <div id="availability-placeholder" class="text-sm text-gray-400 flex items-center gap-2 py-2">
                <i class="fas fa-info-circle text-gray-300"></i>
                <span>Selectați sala, data și pachetul pentru a vedea disponibilitatea în ziua respectivă.</span>
            </div>

            <div id="availability-block" class="hidden">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2.5">Ziua selectată — ce ore sunt libere / ocupate</p>
                <div class="relative mb-4">
                    <div id="timeline-bar" class="relative w-full h-14 bg-gray-100 rounded-xl overflow-hidden border border-gray-200 flex"></div>
                    <div id="timeline-labels" class="relative w-full h-5 mt-1"></div>
                </div>
                <div class="flex flex-wrap gap-x-5 gap-y-1 mb-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded-sm bg-emerald-500"></span>
                        Liber
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded-sm bg-red-400"></span>
                        Ocupat
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded-sm bg-hopo-purple"></span>
                        Rezervarea ta
                    </span>
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

        {{-- 4. Detalii copil și contact ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-5">
            <h2 class="text-base font-semibold text-gray-800 mb-5 flex items-center gap-2">
                <i class="fas fa-birthday-cake text-hopo-purple"></i>
                Detalii copil și contact
            </h2>

            <div class="space-y-4">
                {{-- Copil --}}
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

                <div class="border-t border-gray-100 pt-4">
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

                <div>
                    <label for="number_of_children" class="block text-sm font-medium text-gray-700 mb-1">
                        Număr de copii așteptați <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="number_of_children" id="number_of_children"
                            value="{{ old('number_of_children', 1) }}" min="1" required
                            class="w-28 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-hopo-purple text-sm">
                        <p id="number_of_children-hint" class="text-sm text-gray-500">Selectați sala și pachetul pentru limită.</p>
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Observații / mențiuni speciale</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="Alergii, preferințe temă, alte detalii..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-hopo-purple text-sm resize-none">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── GDPR + Submit ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <label class="inline-flex items-start gap-3 cursor-pointer mb-5 block">
                <input type="checkbox" name="gdpr_accept" id="gdpr_accept" value="1"
                    {{ old('gdpr_accept') ? 'checked' : '' }} required
                    class="mt-0.5 rounded border-gray-300 text-hopo-purple focus:ring-hopo-purple flex-shrink-0">
                <span class="text-sm text-gray-700">
                    Accept prelucrarea datelor cu caracter personal conform politicii de confidențialitate.
                    <span class="text-red-500">*</span>
                </span>
            </label>

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-5 border-t border-gray-100">
                <div id="total-line" class="text-base font-semibold text-gray-700"></div>
                <button type="submit"
                    class="w-full sm:w-auto bg-hopo-purple hover:bg-hopo-purple-dark text-white px-8 py-3 rounded-lg font-semibold shadow-sm transition-colors flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-paper-plane"></i>
                    Trimite rezervarea
                </button>
            </div>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var locationSlug = '{{ $location->slug }}';
    var oldReservationTime = '{{ old('reservation_time') }}';

    var hallSelect = document.getElementById('birthday_hall_id');
    var dateInput = document.getElementById('reservation_date');
    var reservationTimeInput = document.getElementById('reservation_time');
    var availabilityPlaceholder = document.getElementById('availability-placeholder');
    var availabilityBlock = document.getElementById('availability-block');
    var timelineBar = document.getElementById('timeline-bar');
    var timelineLabels = document.getElementById('timeline-labels');
    var startTimesGrid = document.getElementById('start-times-grid');
    var selectedSummary = document.getElementById('selected-summary');
    var selectedRangeLabel = document.getElementById('selected-range-label');
    var totalLine = document.getElementById('total-line');
    var numberOfChildrenInput = document.getElementById('number_of_children');
    var numberOfChildrenHint = document.getElementById('number_of_children-hint');

    function getSelectedHallCapacity() {
        var opt = hallSelect.options[hallSelect.selectedIndex];
        if (!opt || !opt.value) return null;
        var cap = opt.getAttribute('data-capacity');
        return cap !== null && cap !== '' ? parseInt(cap, 10) : null;
    }
    function getSelectedPackageMaxChildren() {
        var radio = document.querySelector('.package-radio:checked');
        if (!radio) return null;
        var max = radio.getAttribute('data-max-children');
        return max !== null && max !== '' ? parseInt(max, 10) : null;
    }
    function getSelectedPackageId() {
        var radio = document.querySelector('.package-radio:checked');
        return radio ? radio.value : null;
    }

    function updateNumberOfChildrenLimit() {
        var hallCap = getSelectedHallCapacity();
        var pkgMax = getSelectedPackageMaxChildren();
        if (hallCap === null && pkgMax === null) {
            numberOfChildrenInput.removeAttribute('max');
            numberOfChildrenHint.textContent = 'Selectați sala și pachetul pentru limită.';
            return;
        }
        var effectiveMax = hallCap !== null && pkgMax !== null ? Math.min(hallCap, pkgMax) : (hallCap !== null ? hallCap : pkgMax);
        numberOfChildrenInput.setAttribute('max', effectiveMax);
        numberOfChildrenHint.textContent = 'Max. ' + effectiveMax + ' copii.';
        var val = parseInt(numberOfChildrenInput.value, 10);
        if (!isNaN(val) && val > effectiveMax) numberOfChildrenInput.value = effectiveMax;
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
        var date = dateInput.value;
        var hallId = hallSelect.value;
        var packageId = getSelectedPackageId();
        if (!date || !hallId || !packageId) {
            availabilityBlock.classList.add('hidden');
            availabilityPlaceholder.classList.remove('hidden');
            availabilityPlaceholder.querySelector('span').textContent = 'Selectați sala, data și pachetul pentru a vedea disponibilitatea în ziua respectivă.';
            reservationTimeInput.removeAttribute('value');
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

    function updatePrice() {
        var radio = document.querySelector('.package-radio:checked');
        if (!radio) { totalLine.textContent = ''; return; }
        var price = parseFloat(radio.getAttribute('data-price'));
        totalLine.innerHTML = 'Total: <strong class="text-hopo-purple">' + Math.round(price) + ' RON</strong>';
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

    hallSelect.addEventListener('change', function () { loadAvailability(); updateNumberOfChildrenLimit(); });
    dateInput.addEventListener('change', loadAvailability);
    numberOfChildrenInput.addEventListener('input', updateNumberOfChildrenLimit);
    document.querySelectorAll('.package-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            highlightSelectedPackage();
            updatePrice();
            updateNumberOfChildrenLimit();
            loadAvailability();
        });
    });

    loadAvailability();
    updateNumberOfChildrenLimit();
    highlightSelectedPackage();
    updatePrice();
});
</script>
@endsection
