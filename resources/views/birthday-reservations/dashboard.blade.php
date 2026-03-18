@extends('layouts.app')

@section('title', 'Dashboard Zile de Naștere')
@section('page-title', 'Dashboard Zile de Naștere')

@section('content')
<div class="space-y-6">

    {{-- Header cu selector dată --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Dashboard Rezervări</h1>
                <p class="text-gray-500 mt-1 text-lg">
                    {{ ucfirst(\Carbon\Carbon::parse($date)->locale('ro')->isoFormat('dddd, D MMMM YYYY')) }}
                </p>
            </div>
            <form method="GET" action="{{ route('birthday-reservations.dashboard') }}" class="flex flex-wrap gap-3 items-end">
                @if($locations->count() > 1)
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Locație</label>
                    <select name="location_id" onchange="this.form.submit()" class="px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                        <option value="">Toate locațiile</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">Data</label>
                    <div class="flex gap-2 items-center">
                        <a href="{{ route('birthday-reservations.dashboard', array_merge(request()->query(), ['date' => \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d')])) }}"
                           class="p-2.5 border border-gray-300 rounded-xl hover:bg-gray-50 text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <input type="date" name="date" value="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}"
                               onchange="this.form.submit()"
                               class="px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm min-w-0 flex-1">
                        <a href="{{ route('birthday-reservations.dashboard', array_merge(request()->query(), ['date' => \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d')])) }}"
                           class="p-2.5 border border-gray-300 rounded-xl hover:bg-gray-50 text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('birthday-reservations.dashboard', request()->only('location_id')) }}"
                           class="px-4 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 text-sm font-medium whitespace-nowrap">
                            Azi
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>


    {{-- Timeline orar --}}
    @if($timelineByHall->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-5 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Situație orară
        </h2>
        <div class="space-y-6">
            @foreach($timelineByHall as $hallName => $data)
                @php
                    [$sh, $sm] = explode(':', $data['day_start']);
                    [$eh, $em] = explode(':', $data['day_end']);
                    $dayStartM = (int)$sh * 60 + (int)$sm;
                    $dayEndM   = (int)$eh * 60 + (int)$em;
                    $span      = $dayEndM - $dayStartM;
                    $tickStep  = $span > 360 ? 120 : 60; // la >6h, tick din 2 în 2 ore
                    $firstTick = (int)ceil($dayStartM / $tickStep) * $tickStep;
                    $hourTicks = [];
                    for ($m = $firstTick; $m < $dayEndM; $m += $tickStep) {
                        if ($m > $dayStartM) {
                            $hourTicks[] = [
                                'pct'   => ($m - $dayStartM) / $span * 100,
                                'label' => sprintf('%02d:00', intdiv($m, 60)),
                            ];
                        }
                    }
                @endphp
                <div>
                    @if($timelineByHall->count() > 1)
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $hallName }}</p>
                    @endif
                    @php
                        // Paleta confirmed (culori reci distincte) și pending (culori calde).
                        // color_index este stabil per rezervare (asignat în controller).
                        $confirmedPalette = [
                            ['bg' => '#4f46e5', 'text' => '#ffffff'], // indigo-600
                            ['bg' => '#0284c7', 'text' => '#ffffff'], // sky-600
                            ['bg' => '#7c3aed', 'text' => '#ffffff'], // violet-600
                            ['bg' => '#0891b2', 'text' => '#ffffff'], // cyan-600
                            ['bg' => '#2563eb', 'text' => '#ffffff'], // blue-600
                            ['bg' => '#6d28d9', 'text' => '#ffffff'], // violet-700
                        ];
                        $pendingPalette = [
                            ['bg' => '#f59e0b', 'text' => '#1e293b'], // amber-400
                            ['bg' => '#f97316', 'text' => '#ffffff'], // orange-500
                            ['bg' => '#eab308', 'text' => '#1e293b'], // yellow-500
                            ['bg' => '#d97706', 'text' => '#ffffff'], // amber-600
                            ['bg' => '#ea580c', 'text' => '#ffffff'], // orange-600
                            ['bg' => '#ca8a04', 'text' => '#ffffff'], // yellow-600
                        ];
                    @endphp
                    <div class="relative w-full h-16 rounded-xl overflow-hidden flex border border-gray-200">
                        @foreach($data['columns'] as $col)
                            @if($col['type'] === 'free')
                                <div style="width: {{ $col['pct'] }}%" class="bg-emerald-100 flex-shrink-0"></div>
                            @else
                                <div style="width: {{ $col['pct'] }}%" class="flex flex-col flex-shrink-0">
                                    @foreach($col['bands'] as $band)
                                        @php
                                            $palette   = $band['status'] === 'confirmed' ? $confirmedPalette : $pendingPalette;
                                            $color     = $palette[$band['color_index'] % count($palette)];
                                            $tooltip   = $band['child_name'] . ' · ' . $band['start_lbl'] . '–' . $band['end_lbl'];
                                        @endphp
                                        <div class="flex-1 flex items-center justify-center overflow-hidden cursor-default relative"
                                             style="background-color: {{ $color['bg'] }}; color: {{ $color['text'] }}; border-bottom: 1px solid rgba(255,255,255,0.25);"
                                             title="{{ $tooltip }}">
                                            <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 4px, rgba(255,255,255,.6) 4px, rgba(255,255,255,.6) 8px);"></div>
                                            @if($band['is_start'])
                                                <span class="relative text-xs font-semibold truncate px-2 leading-none text-center">
                                                    {{ $band['child_name'] }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                    {{-- Etichete ore --}}
                    <div class="relative w-full mt-1.5 h-5">
                        <span class="absolute left-0 text-[11px] text-gray-400 font-medium">{{ $data['day_start'] }}</span>
                        @foreach($hourTicks as $tick)
                            <span class="absolute text-[11px] text-gray-400 font-medium" style="left: {{ $tick['pct'] }}%; transform: translateX(-50%)">{{ $tick['label'] }}</span>
                        @endforeach
                        <span class="absolute right-0 text-[11px] text-gray-400 font-medium">{{ $data['day_end'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="flex flex-wrap gap-x-8 gap-y-3 mt-5 pt-4 border-t border-gray-100 text-xs text-gray-500">
            <span class="flex items-center gap-2"><span class="inline-block w-4 h-4 rounded-sm bg-emerald-100 border border-emerald-200"></span>Liber</span>
            <span class="flex items-center gap-2"><span class="inline-block w-4 h-4 rounded-sm" style="background:#4f46e5;"></span>Confirmat</span>
            <span class="flex items-center gap-2"><span class="inline-block w-4 h-4 rounded-sm" style="background:#f59e0b;"></span>În așteptare</span>
            <span class="text-gray-400 italic">Fiecare rezervare are o culoare unică pentru diferențiere</span>
        </div>
    </div>
    @endif

    {{-- Conținut principal --}}
    @if($reservations->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-16 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <p class="text-xl font-semibold text-gray-700">Nicio rezervare în această zi</p>
            <p class="text-gray-400 mt-1">Selectează altă dată sau verifică mai târziu.</p>
        </div>
    @else
        {{-- Grupat pe săli --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($byHall as $hallName => $hallReservations)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    {{-- Header sală --}}
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            </div>
                            <h2 class="text-lg font-bold text-white">{{ $hallName }}</h2>
                        </div>
                        <span class="bg-white/20 text-white text-sm font-semibold px-3 py-1 rounded-full">
                            {{ $hallReservations->count() }} {{ $hallReservations->count() === 1 ? 'rezervare' : 'rezervări' }}
                        </span>
                    </div>

                    {{-- Carduri rezervări --}}
                    <div class="divide-y divide-gray-100">
                        @foreach($hallReservations->sortBy('reservation_time') as $r)
                            @php
                                $statusColor = match($r->status) {
                                    'confirmed' => 'border-l-green-500',
                                    'pending'   => 'border-l-yellow-400',
                                    'cancelled' => 'border-l-red-400',
                                    default     => 'border-l-gray-300',
                                };
                                $statusBg = match($r->status) {
                                    'confirmed' => 'bg-green-50 text-green-700',
                                    'pending'   => 'bg-yellow-50 text-yellow-700',
                                    'cancelled' => 'bg-red-50 text-red-600',
                                    default     => 'bg-gray-100 text-gray-600',
                                };
                                $statusLabel = match($r->status) {
                                    'confirmed' => 'Confirmat',
                                    'pending'   => 'În așteptare',
                                    'cancelled' => 'Anulat',
                                    default     => $r->status,
                                };
                            @endphp
                            <div class="flex border-l-4 {{ $statusColor }} {{ $r->status === 'cancelled' ? 'opacity-50' : '' }}">
                                {{-- Coloana oră --}}
                                <div class="w-20 flex-shrink-0 flex flex-col items-center justify-center py-4 bg-gray-50 border-r border-gray-100">
                                    @if($r->reservation_time)
                                        <span class="text-lg font-bold text-gray-800 leading-none">{{ \Carbon\Carbon::parse($r->reservation_time)->format('H:i') }}</span>
                                    @elseif($r->timeSlot)
                                        <span class="text-lg font-bold text-gray-800 leading-none">{{ $r->timeSlot->start_time ?? '—' }}</span>
                                    @else
                                        <span class="text-sm text-gray-400">—</span>
                                    @endif
                                </div>

                                {{-- Conținut card --}}
                                <div class="flex-1 px-5 py-4">
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <div>
                                            <p class="text-base font-bold text-gray-900 leading-tight">
                                                🎂 {{ $r->child_name }}
                                                <span class="text-sm font-normal text-gray-500">{{ $r->child_age ? '(' . $r->child_age . ' ani)' : '' }}</span>
                                            </p>
                                            @if($r->birthdayPackage)
                                                <p class="text-sm text-indigo-600 font-medium mt-0.5">{{ $r->birthdayPackage->name }}</p>
                                            @endif
                                        </div>
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full flex-shrink-0 {{ $statusBg }}">{{ $statusLabel }}</span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm text-gray-600">
                                        <div class="flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>{{ $r->guardian_name }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span>{{ $r->guardian_phone }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>{{ $r->number_of_children }} copii</span>
                                        </div>
                                        @if($r->location && $locations->count() > 1)
                                        <div class="flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>{{ $r->location->name }}</span>
                                        </div>
                                        @endif
                                    </div>

                                    @if($r->notes)
                                        <p class="mt-2 text-xs text-gray-500 bg-gray-50 rounded-lg px-3 py-1.5 italic">{{ $r->notes }}</p>
                                    @endif
                                </div>

                                {{-- Link detalii --}}
                                <div class="flex items-center pr-4">
                                    <a href="{{ route('birthday-reservations.show', $r) }}" class="text-gray-300 hover:text-indigo-600 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Auto-refresh indicator --}}
    <div class="flex items-center justify-between text-xs text-gray-400 px-1">
        <span>Pagina se reîmprospătează automat la fiecare 5 minute.</span>
        <span id="last-refresh">Ultima actualizare: <span id="refresh-time">{{ now()->format('H:i') }}</span></span>
    </div>
</div>

<script>
// Auto-refresh la 5 minute
setTimeout(function() { location.reload(); }, 5 * 60 * 1000);
</script>
@endsection
