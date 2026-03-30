@extends('layouts.app')

@section('title', 'Rezervări Zile de Naștere')
@section('page-title', 'Rezervări Zile de Naștere')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">Rezervări Zile de Naștere</h1>
                <p class="text-gray-600">Lista rezervărilor primite</p>
            </div>
            <a href="{{ route('birthday-reservations.dashboard') }}" class="flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl hover:bg-indigo-700 font-medium shadow-sm whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Dashboard Zi
            </a>
        </div>
        @if($locations->count() > 0)
            <div class="pt-4 border-t border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Link rezervare pentru clienți</label>
                <div class="flex flex-col gap-3">
                    @foreach($locations as $loc)
                        <div class="flex flex-wrap gap-2 items-center">
                            @if($locations->count() > 1)
                                <span class="text-sm text-gray-500 w-full">{{ $loc->name }}</span>
                            @endif
                            <input type="text" readonly value="{{ url('/booking/' . $loc->slug) }}" id="booking-url-{{ $loc->id }}" class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 text-sm">
                            <button type="button" onclick="copyBookingLink({{ $loc->id }}, this)" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium text-sm whitespace-nowrap">
                                Copiază link
                            </button>
                            <button type="button" onclick="openQrModal('{{ url('/booking/' . $loc->slug) }}', {{ json_encode($loc->name) }})" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium text-sm whitespace-nowrap flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                Cod QR
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Modal QR Code --}}
    <div id="qr-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeQrModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl p-8 flex flex-col items-center gap-4 w-80">
            <button onclick="closeQrModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
            <h3 id="qr-modal-title" class="text-lg font-bold text-gray-900 text-center">Cod QR Rezervare</h3>
            <div id="qr-code-container" class="p-3 bg-white border border-gray-200 rounded-xl"></div>
            <p id="qr-modal-url" class="text-xs text-gray-500 text-center break-all"></p>
            <button onclick="downloadQr()" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium w-full flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Descarcă PNG
            </button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
    function copyBookingLink(locationId, btn) {
        navigator.clipboard.writeText(document.getElementById('booking-url-' + locationId).value);
        var original = btn.textContent;
        btn.textContent = 'Copiat!';
        setTimeout(function() { btn.textContent = original; }, 2000);
    }

    function openQrModal(url, locationName) {
        var container = document.getElementById('qr-code-container');
        container.innerHTML = '';
        new QRCode(container, {
            text: url,
            width: 220,
            height: 220,
            colorDark: '#111827',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
        document.getElementById('qr-modal-title').textContent = locationName ? 'Cod QR – ' + locationName : 'Cod QR Rezervare';
        document.getElementById('qr-modal-url').textContent = url;
        document.getElementById('qr-modal').classList.remove('hidden');
    }

    function closeQrModal() {
        document.getElementById('qr-modal').classList.add('hidden');
    }

    function downloadQr() {
        var container = document.getElementById('qr-code-container');
        var canvas = container.querySelector('canvas');
        if (!canvas) return;
        var link = document.createElement('a');
        link.download = 'cod-qr-rezervare.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeQrModal();
    });
    </script>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 auto-hide-alert"><p class="text-green-800">{{ session('success') }}</p></div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 auto-hide-alert"><p class="text-red-800">{{ session('error') }}</p></div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('birthday-reservations.index') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-1">Locație</label>
                <select name="location_id" id="location_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">Toate</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="reservation_date" class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                <input type="text" name="reservation_date" id="reservation_date" value="{{ request('reservation_date') }}" placeholder="zz/ll/aaaa" autocomplete="off" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Caută</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nume părinte sau telefon" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 w-full">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">Toate</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>În așteptare</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmat</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Anulat</option>
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">Filtrează</button>
            <a href="{{ route('birthday-reservations.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 font-medium">Resetează</a>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dată</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Locație / Sală</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pachet</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acțiuni</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reservations as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-gray-900">{{ $r->reservation_date->format('d.m.Y') }}</div>
                                @if($r->reservation_time)
                                    <div class="text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($r->reservation_time)->format('H:i') }}
                                        @if($r->birthdayPackage?->duration_minutes)
                                            – {{ \Carbon\Carbon::parse($r->reservation_time)->addMinutes($r->birthdayPackage->duration_minutes)->format('H:i') }}
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $r->location->name ?? '-' }}</div>
                                <div class="text-sm text-gray-500">{{ $r->birthdayHall->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $r->birthdayPackage->name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $r->guardian_name }}</div>
                                <div class="text-sm text-gray-500">{{ $r->guardian_phone }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($r->status === 'pending')
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">În așteptare</span>
                                @elseif($r->status === 'confirmed')
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Confirmat</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Anulat</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('birthday-reservations.show', $r) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Detalii</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Nicio rezervare găsită.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reservations->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">{{ $reservations->links() }}</div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr('#reservation_date', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        allowInput: true,
        locale: { firstDayOfWeek: 1 },
    });
</script>
@endsection
