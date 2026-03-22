@extends('layouts.landing')

@section('title', 'Blog HOPO – Resurse pentru proprietari de locuri de joacă indoor')
@section('meta_description', 'Articole și resurse utile pentru proprietarii de locuri de joacă indoor: gestiune, tarifare, marketing și digitalizare.')
@section('canonical', 'https://hopo.ro/blog')

@section('content')
    <section class="pt-28 pb-20 px-6">
        <div class="max-w-4xl mx-auto text-center">
            <div data-animate>
                <span class="inline-block px-4 py-1.5 bg-hopo-purple/10 text-hopo-purple text-sm font-medium rounded-full mb-4">Blog</span>
                <h1 class="text-3xl md:text-4xl font-bold mb-4">Articole în curând</h1>
                <p class="text-gray-600 text-lg mb-8">
                    Pregătim articole despre gestiunea locurilor de joacă, digitalizare și sfaturi practice pentru proprietari.
                </p>
                <a href="/contact" class="bg-hopo-purple hover:bg-hopo-purple-dark text-white px-8 py-3 rounded-lg font-medium transition-colors inline-block">
                    Contactează-ne
                </a>
            </div>
        </div>
    </section>
@endsection
