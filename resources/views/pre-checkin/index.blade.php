@extends('layouts.booking')

@section('title', 'Înregistrare rapidă - ' . $location->name)
@section('header-title', $location->name)

@section('content')
<div class="max-w-sm mx-auto mt-10 px-4">

    @if(!($show_new_form ?? false))
        <h1 class="text-2xl font-bold text-center mb-2">Bun venit!</h1>
        <p class="text-center text-gray-600 mb-8">Înregistrează-te rapid pentru a reduce timpul de așteptare la recepție.</p>

        <form method="POST" action="{{ route('pre-checkin.lookup', $location) }}">
            @csrf
            <input type="text" name="website" value="" style="display:none;visibility:hidden;" tabindex="-1" autocomplete="off">

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                    @foreach($errors->all() as $error)
                        <p class="text-red-600 text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Număr de telefon *</label>
                <input type="tel" name="guardian_phone"
                       value="{{ old('guardian_phone', session('guardian_phone', '')) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                       autofocus required>
            </div>

            @if(session('show_terms'))
                @php $rulesUrl = $location->getEffectiveRulesUrl(); @endphp
                <div class="mb-3">
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
            @endif

            <button type="submit"
                    class="w-full py-4 bg-blue-600 text-white text-lg font-semibold rounded-xl hover:bg-blue-700 transition">
                Continuă →
            </button>
        </form>

    @else
        <h1 class="text-2xl font-bold text-center mb-2">Bun venit!</h1>
        <p class="text-center text-gray-600 mb-8">Completează datele pentru a te înregistra.</p>

        @include('pre-checkin.new', [
            'location' => $location,
            'prefill_phone' => $prefill_phone ?? '',
        ])
    @endif

</div>
@endsection
