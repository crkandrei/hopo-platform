@extends('layouts.app')

@section('title', 'Adaugă pachet')
@section('page-title', 'Adaugă pachet')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Adaugă pachet</h1>
                <p class="text-gray-600">Locație: {{ $location->name }}</p>
            </div>
            <a href="{{ route('locations.birthday-packages.index', $location) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Înapoi
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('locations.birthday-packages.store', $location) }}">
            @csrf
            <div class="space-y-4 max-w-xl">
                @php($availableWeekdays = collect(old('available_weekdays', []))->map(fn ($day) => (string) $day)->all())
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nume pachet <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descriere</label>
                    <textarea name="description" id="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-1">Durată (min) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', 90) }}" min="15" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @error('duration_minutes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program disponibil</label>
                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <label for="available_from" class="block text-xs text-gray-500 mb-1">De la</label>
                            <input type="time" name="available_from" id="available_from" value="{{ old('available_from') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <span class="mt-5 text-gray-400">—</span>
                        <div class="flex-1">
                            <label for="available_until" class="block text-xs text-gray-500 mb-1">Până la</label>
                            <input type="time" name="available_until" id="available_until" value="{{ old('available_until') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Opțional. Dacă e setat, părinții pot rezerva doar în acest interval.</p>
                    @error('available_from')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @error('available_until')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <p class="block text-sm font-medium text-gray-700 mb-2">Zile disponibile <span class="text-red-500">*</span></p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach(\App\Models\BirthdayPackage::DAY_NAMES as $dayOfWeek => $dayLabel)
                            <label class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2">
                                <input type="checkbox" name="available_weekdays[]" value="{{ $dayOfWeek }}"
                                    {{ in_array((string) $dayOfWeek, $availableWeekdays, true) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">{{ $dayLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Selectați cel puțin o zi în care acest pachet poate fi rezervat.</p>
                    @error('available_weekdays')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @error('available_weekdays.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="includes_food" value="1" {{ old('includes_food') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Include mâncare</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="includes_decorations" value="1" {{ old('includes_decorations') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Include decorațiuni</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Activ</span>
                    </label>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium">Salvează</button>
            </div>
        </form>
    </div>
</div>
@endsection
