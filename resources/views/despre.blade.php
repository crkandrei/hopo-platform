@extends('layouts.landing')

@section('title', 'Despre HOPO – Soft gestiune locuri de joacă indoor din România')
@section('meta_description', 'HOPO este un software românesc pentru gestiunea locurilor de joacă indoor. Cronometrare sesiuni, brățări RFID, bonuri fiscale ANAF și rezervări online.')
@section('canonical', 'https://hopo.ro/despre')

@section('content')
    <section class="pt-28 pb-20 px-6">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-16" data-animate>
                <h1 class="text-3xl md:text-4xl font-bold mb-4">
                    Despre <span class="gradient-text">HOPO</span>
                </h1>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    Software românesc creat special pentru locuri de joacă indoor.
                </p>
            </div>

            <div class="prose prose-lg max-w-none text-gray-600 mb-16" data-animate>
                <p>
                    <strong class="text-gray-900">HOPO</strong> este un program de gestiune creat special pentru locurile de joacă indoor din România.
                    Am construit HOPO pentru a elimina problemele clasice ale operatorilor de locuri de joacă: calcule manuale greșite,
                    timpi pierduți la casă și lipsa de vizibilitate asupra afacerii în timp real.
                </p>
                <p class="mt-6">
                    Cu HOPO, fiecare copil primește o brățară RFID la intrare. Sistemul cronometrează automat sesiunea,
                    calculează tariful corect și generează bonul fiscal conform ANAF — fără intervenție manuală.
                    Tu te concentrezi pe clienți, HOPO se ocupă de rest.
                </p>
                <p class="mt-6">
                    Platforma este <strong class="text-gray-900">cloud-based</strong>, accesibilă de pe orice dispozitiv,
                    și se poate configura complet în mai puțin de 24 de ore.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mb-16">
                <div class="text-center p-6 bg-gray-50 rounded-xl" data-animate>
                    <div class="text-4xl font-bold text-hopo-purple mb-2">24h</div>
                    <p class="text-gray-600 text-sm">Setup complet în mai puțin de 24 de ore</p>
                </div>
                <div class="text-center p-6 bg-gray-50 rounded-xl" data-animate data-delay="150">
                    <div class="text-4xl font-bold text-hopo-purple mb-2">100%</div>
                    <p class="text-gray-600 text-sm">Conform ANAF pentru bonuri fiscale</p>
                </div>
                <div class="text-center p-6 bg-gray-50 rounded-xl" data-animate data-delay="300">
                    <div class="text-4xl font-bold text-hopo-purple mb-2">0 €</div>
                    <p class="text-gray-600 text-sm">Demo gratuit, prima lună gratuită</p>
                </div>
            </div>

            <div class="text-center" data-animate>
                <a href="/contact" class="bg-hopo-purple hover:bg-hopo-purple-dark text-white px-8 py-3 rounded-lg font-medium transition-colors inline-block">
                    Solicită un demo gratuit
                </a>
            </div>
        </div>
    </section>
@endsection
