@extends('layouts.landing')

@section('title', 'Blog HOPO – Resurse pentru proprietari de locuri de joacă')
@section('meta_description', 'Ghiduri, sfaturi și strategii pentru proprietarii de locuri de joacă indoor din România.')
@section('canonical', 'https://hopo.ro/blog')

@section('content')
@php
$articles = [
    [
        'slug'         => 'rezervari-online-zile-nastere-loc-de-joaca',
        'title'        => 'Cum gestionezi rezervările online pentru zilele de naștere la locul de joacă',
        'description'  => 'Zilele de naștere generează 500–1.500 RON per eveniment. Gestionarea prin telefon și WhatsApp consumă timp și generează confuzii. Iată cum automatizezi tot procesul.',
        'date'         => '30 martie 2026',
        'reading_time' => '11 min',
        'tag'          => 'Operațional',
    ],
    [
        'slug'         => 'bratari-barcode-rfid-loc-de-joaca',
        'title'        => 'Brățări barcode și RFID pentru loc de joacă: ce alegi și de ce',
        'description'  => 'Fără identificare digitală nu știi exact cât timp a stat fiecare copil și erorile de facturare apar inevitabil. Ghid complet: diferențe, costuri și cum le integrezi cu softul.',
        'date'         => '25 martie 2026',
        'reading_time' => '9 min',
        'tag'          => 'Echipamente',
    ],
    [
        'slug'         => 'bon-fiscal-automat-loc-de-joaca',
        'title'        => 'Bon fiscal automat pentru loc de joacă: obligații ANAF 2026',
        'description'  => 'Amenzi între 2.000 și 4.000 RON per abatere pentru neemiterea bonului fiscal. Ce casă de marcat ai nevoie, cum o integrezi cu softul și cum automatizezi complet procesul.',
        'date'         => '20 martie 2026',
        'reading_time' => '10 min',
        'tag'          => 'Fiscal',
    ],
    [
        'slug'         => 'cum-sa-deschizi-loc-de-joaca-pentru-copii-romania',
        'title'        => 'Cum să deschizi un loc de joacă pentru copii în România [Ghid Complet 2026]',
        'description'  => 'Ghid pas cu pas pentru deschiderea unui loc de joacă indoor în România: autorizații ISU/DSP, echipamente, costuri, software de gestiune. Tot ce trebuie să știi în 2026.',
        'date'         => '20 martie 2026',
        'reading_time' => '15 min',
        'tag'          => 'Ghid',
    ],
];
@endphp

    <section class="pt-28 pb-20 px-6">
        <div class="max-w-6xl mx-auto">

            <div class="text-center mb-16">
                <span class="inline-block px-4 py-1.5 bg-hopo-purple/10 text-hopo-purple text-sm font-medium rounded-full mb-4">Blog</span>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Resurse pentru proprietari de locuri de joacă</h1>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">Ghiduri practice, sfaturi operaționale și strategii de creștere pentru locuri de joacă indoor din România.</p>
            </div>

            @if(count($articles) > 0)
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($articles as $article)
                        <a href="/blog/{{ $article['slug'] }}" class="group block bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:border-hopo-purple/20 transition-all overflow-hidden">
                            <!-- Card header color band -->
                            <div class="h-2 bg-gradient-to-r from-hopo-purple to-indigo-400"></div>
                            <div class="p-6">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="text-xs font-medium text-hopo-purple bg-hopo-purple/10 px-2 py-0.5 rounded-full">{{ $article['tag'] }}</span>
                                    <span class="text-xs text-gray-400">{{ $article['reading_time'] }} lectură</span>
                                </div>
                                <h2 class="text-lg font-bold text-gray-900 mb-3 group-hover:text-hopo-purple transition-colors leading-snug">
                                    {{ $article['title'] }}
                                </h2>
                                <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ $article['description'] }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400">{{ $article['date'] }}</span>
                                    <span class="text-hopo-purple text-sm font-medium group-hover:underline">Citește →</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 text-gray-500">
                    <p class="text-lg mb-2">Articole în curând.</p>
                    <p class="text-sm">Pregătim conținut util pentru proprietarii de locuri de joacă.</p>
                </div>
            @endif

        </div>
    </section>
@endsection
