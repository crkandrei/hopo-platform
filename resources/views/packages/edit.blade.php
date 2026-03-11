@extends('layouts.app')

@section('title', 'Editează pachet Bon Specific')
@section('page-title', 'Editează pachet')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Editează pachet</h1>
                <p class="text-gray-600">Locație: {{ $location->name }}</p>
            </div>
            <a href="{{ route('locations.packages.index', $location) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Înapoi
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('locations.packages.update', [$location, $package]) }}">
            @csrf
            @method('PUT')
            <div class="space-y-4 max-w-xl">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nume pachet <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $package->name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descriere</label>
                    <textarea name="description" id="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ old('description', $package->description) }}</textarea>
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Preț (RON) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" id="price" value="{{ old('price', $package->price) }}" step="0.01" min="0" max="999999.99" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    @error('price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $package->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Activ</span>
                    </label>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium">Actualizează</button>
            </div>
        </form>
    </div>
</div>
@endsection
