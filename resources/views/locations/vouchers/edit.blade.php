@extends('layouts.app')

@section('title', 'Editează voucher')
@section('page-title', 'Editează voucher')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Editează voucher {{ $voucher->code }}</h1>
                <p class="text-gray-600">Locație: {{ $location->name }}</p>
            </div>
            <a href="{{ route('locations.vouchers.show', [$location, $voucher]) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Înapoi
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-gray-600 mb-4">Poți modifica doar notele și statusul (activ/inactiv). Valoarea și tipul nu se pot edita.</p>
        <form method="POST" action="{{ route('locations.vouchers.update', [$location, $voucher]) }}">
            @csrf
            @method('PUT')
            <div class="space-y-4 max-w-xl">
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                    <textarea name="notes" id="notes" rows="3" maxlength="500" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('notes', $voucher->notes) }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $voucher->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Voucher activ</label>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium">Salvează</button>
            </div>
        </form>
    </div>
</div>
@endsection
