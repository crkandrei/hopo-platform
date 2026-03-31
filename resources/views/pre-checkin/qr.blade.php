@extends('layouts.booking')

@section('title', 'Codul tău QR - ' . $location->name)
@section('header-title', $location->name)

@section('content')
<div class="max-w-sm mx-auto mt-8 px-4 text-center">
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
        <p class="text-green-800 font-semibold">Înregistrare completă!</p>
        <p class="text-green-700 text-sm mt-1">Arată codul de mai jos la receptie.</p>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-4 inline-block">
        <div id="qr-container"></div>
    </div>

    <p class="text-gray-500 text-sm mb-2">Valabil 60 de minute</p>
    <p class="text-gray-400 text-xs font-mono">{{ $preCheckinToken->token }}</p>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
new QRCode(document.getElementById('qr-container'), {
    text: '{{ $preCheckinToken->token }}',
    width: 220,
    height: 220,
    correctLevel: QRCode.CorrectLevel.H
});
</script>
@endsection
