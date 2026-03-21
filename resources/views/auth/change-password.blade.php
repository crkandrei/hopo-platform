@extends('layouts.app')

@section('title', 'Schimbă Parola')
@section('page-title', 'Schimbă Parola')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-6">
            <i class="fas fa-key text-indigo-600 mr-3 text-xl"></i>
            <h2 class="text-lg font-semibold text-gray-900">Schimbă Parola</h2>
        </div>

        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="/change-password" class="space-y-5">
            @csrf

            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700">Parola curentă</label>
                <input type="password" id="current_password" name="current_password" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('current_password') border-red-500 @enderror">
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700">Parola nouă</label>
                <input type="password" id="new_password" name="new_password" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('new_password') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Minim 8 caractere.</p>
            </div>

            <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">Confirmă parola nouă</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ url()->previous() }}" class="text-sm text-gray-500 hover:text-gray-700">
                    <i class="fas fa-arrow-left mr-1"></i>Înapoi
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Schimbă Parola
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
