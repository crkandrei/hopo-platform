<form method="POST" action="{{ route('pre-checkin.submit-new', $location) }}">
    @csrf
    <input type="text" name="website" value="" style="display:none;visibility:hidden;" tabindex="-1" autocomplete="off">

    <h2 class="text-xl font-bold mb-4">Prima vizită</h2>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
            @foreach($errors->all() as $error)
                <p class="text-red-600 text-sm">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Numele părintelui *</label>
        <input type="text" name="guardian_name" value="{{ strtoupper(old('guardian_name', '')) }}"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
               oninput="this.value=this.value.toUpperCase()"
               required>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Număr de telefon *</label>
        <input type="tel" name="guardian_phone" id="new_guardian_phone" value="{{ old('guardian_phone') }}"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
               required>
        <div id="phone-exists-hint" class="hidden mt-2 p-3 bg-yellow-50 border border-yellow-300 rounded-lg text-sm text-yellow-800">
            Se pare că ai mai fost la noi! Mergi la
            <button type="button" id="hint-switch-btn" class="font-semibold underline">Am mai fost aici</button>
            pentru a genera QR-ul direct. Sau continuă dacă vrei să înregistrezi un copil nou.
        </div>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Numele copilului *</label>
        <input type="text" name="child_name" value="{{ strtoupper(old('child_name', '')) }}"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
               oninput="this.value=this.value.toUpperCase()"
               required>
    </div>

    <div class="mb-3">
        @php $rulesUrl = $location->getEffectiveRulesUrl(); @endphp
        <label class="flex items-start gap-2">
            <input type="checkbox" name="terms_accept" value="1" class="mt-1" {{ old('terms_accept') ? 'checked' : '' }} required>
            @if($rulesUrl)
                <span class="text-sm text-gray-700">Am citit și accept <a href="{{ $rulesUrl }}" target="_blank" rel="noopener noreferrer" class="underline text-blue-600">regulamentul locației</a> *</span>
            @else
                <span class="text-sm text-gray-700">Accept <a href="{{ route('legal.terms.public') }}" target="_blank" class="underline text-blue-600">Termenii și Condițiile</a> *</span>
            @endif
        </label>
    </div>

    <div class="mb-6">
        <label class="flex items-start gap-2">
            <input type="checkbox" name="gdpr_accept" value="1" class="mt-1" {{ old('gdpr_accept') ? 'checked' : '' }} required>
            <span class="text-sm text-gray-700">Accept <a href="{{ route('legal.gdpr.public') }}" target="_blank" class="underline text-blue-600">Politica GDPR</a> *</span>
        </label>
    </div>

    <button type="submit"
            class="w-full py-3 bg-green-600 text-white text-lg font-semibold rounded-xl hover:bg-green-700 transition">
        Generează cod QR
    </button>
</form>
