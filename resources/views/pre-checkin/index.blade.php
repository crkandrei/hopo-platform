@extends('layouts.booking')

@section('title', 'Înregistrare rapidă - ' . $location->name)
@section('header-title', $location->name)

@section('content')
<div class="max-w-sm mx-auto mt-10 px-4">
    <h1 class="text-2xl font-bold text-center mb-2">Bun venit!</h1>
    <p class="text-center text-gray-600 mb-8">Înregistrează-te rapid pentru a reduce timpul de așteptare la receptie.</p>

    <div id="choice-buttons" class="flex flex-col gap-4">
        <button type="button" id="btn-existing"
           class="w-full text-center py-4 px-6 bg-blue-600 text-white text-lg font-semibold rounded-xl hover:bg-blue-700 transition">
            Am mai fost aici
        </button>
        <button type="button" id="btn-new"
           class="w-full text-center py-4 px-6 bg-green-600 text-white text-lg font-semibold rounded-xl hover:bg-green-700 transition">
            Prima vizită
        </button>
    </div>

    <div id="existing-client-form" class="mt-8 hidden">
        <form method="POST" action="{{ route('pre-checkin.submit-existing', $location) }}">
            @csrf
            <input type="text" name="website" value="" style="display:none;visibility:hidden;" tabindex="-1" autocomplete="off">
            <h2 class="text-xl font-bold mb-4">Am mai fost aici</h2>
            @if($errors->has('guardian_phone'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                    <p class="text-red-600 text-sm">{{ $errors->first('guardian_phone') }}</p>
                </div>
            @endif
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Număr de telefon *</label>
                <input type="tel" name="guardian_phone" value="{{ old('guardian_phone') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>
            <button type="submit"
                    class="w-full py-3 bg-blue-600 text-white text-lg font-semibold rounded-xl hover:bg-blue-700 transition">
                Continuă
            </button>
        </form>
    </div>

    <div id="new-client-form" class="mt-8 hidden">
        @include('pre-checkin.new', ['location' => $location])
    </div>
</div>

<script>
function showExistingForm() {
    document.getElementById('new-client-form').classList.add('hidden');
    document.getElementById('existing-client-form').classList.remove('hidden');
    document.getElementById('existing-client-form').scrollIntoView({ behavior: 'smooth' });
    const phoneInput = document.querySelector('#existing-client-form input[name="guardian_phone"]');
    if (phoneInput) {
        const newPhone = document.getElementById('new_guardian_phone');
        if (newPhone && newPhone.value) phoneInput.value = newPhone.value;
        setTimeout(() => phoneInput.focus(), 100);
    }
}

document.getElementById('btn-new').addEventListener('click', function() {
    document.getElementById('existing-client-form').classList.add('hidden');
    document.getElementById('new-client-form').classList.remove('hidden');
    document.getElementById('new-client-form').scrollIntoView({ behavior: 'smooth' });
});
document.getElementById('btn-existing').addEventListener('click', showExistingForm);

// Live phone check in "Prima vizită" form
const newPhoneInput = document.getElementById('new_guardian_phone');
if (newPhoneInput) {
    let checkTimeout;
    newPhoneInput.addEventListener('blur', function() {
        const phone = this.value.trim();
        const hint = document.getElementById('phone-exists-hint');
        if (!phone || phone.length < 7) { hint.classList.add('hidden'); return; }
        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(async function() {
            try {
                const res = await fetch('{{ route('pre-checkin.check-phone', $location) }}?phone=' + encodeURIComponent(phone));
                const data = await res.json();
                if (data.exists) {
                    hint.classList.remove('hidden');
                } else {
                    hint.classList.add('hidden');
                }
            } catch (e) {}
        }, 200);
    });

    const hintBtn = document.getElementById('hint-switch-btn');
    if (hintBtn) hintBtn.addEventListener('click', showExistingForm);
}
</script>
@endsection
