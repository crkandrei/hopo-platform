@extends('layouts.booking')

@section('title', 'Selectează copilul - ' . $location->name)
@section('header-title', $location->name)

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-sm mx-auto mt-8 px-4">
    <h1 class="text-xl font-bold mb-2">Bun venit, {{ $guardian->name }}!</h1>
    <p class="text-gray-600 text-sm mb-6">Selectează copilul pentru care generezi codul QR.</p>

    @if($children->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
            <p class="text-yellow-800">Nu ai copii înregistrați. Te rugăm să mergi la receptie sau să te înregistrezi ca client nou.</p>
        </div>
    @else
        <div class="flex flex-col gap-3">
            @foreach($children as $child)
            <div class="border border-gray-200 rounded-xl p-4 bg-white">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-gray-800">{{ $child->name }}</span>
                    <button
                        class="generate-qr-btn text-sm bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition"
                        data-child-id="{{ $child->id }}"
                        data-phone="{{ $guardian->phone }}"
                        data-url="{{ route('pre-checkin.generate-token', $location) }}">
                        Generează QR
                    </button>
                </div>
                <div class="qr-section hidden mt-4 text-center" id="qr-section-{{ $child->id }}">
                    <div id="qr-{{ $child->id }}" class="inline-block p-3 bg-white border border-gray-200 rounded-lg"></div>
                    <p class="text-xs text-gray-500 mt-2">Valabil 60 de minute</p>
                    <button class="dismiss-qr mt-2 text-sm text-gray-400 underline" data-child-id="{{ $child->id }}">
                        × Închide
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
const qrInstances = {};

document.querySelectorAll('.generate-qr-btn').forEach(function(btn) {
    btn.addEventListener('click', async function() {
        const childId = this.dataset.childId;
        const phone = this.dataset.phone;
        const url = this.dataset.url;
        const self = this;

        self.disabled = true;
        self.textContent = 'Se generează...';

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ guardian_phone: phone, child_id: parseInt(childId) }),
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Eroare server');

            const section = document.getElementById('qr-section-' + childId);
            const container = document.getElementById('qr-' + childId);

            container.innerHTML = '';
            qrInstances[childId] = new QRCode(container, {
                text: data.token,
                width: 200,
                height: 200,
                correctLevel: QRCode.CorrectLevel.H,
            });

            section.classList.remove('hidden');
        } catch (e) {
            alert('A apărut o eroare. Încearcă din nou.');
        } finally {
            self.disabled = false;
            self.textContent = 'Regenerează QR';
        }
    });
});

document.querySelectorAll('.dismiss-qr').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const childId = this.dataset.childId;
        document.getElementById('qr-section-' + childId).classList.add('hidden');
        document.querySelector('[data-child-id="' + childId + '"].generate-qr-btn').textContent = 'Generează QR';
    });
});
</script>
@endsection
