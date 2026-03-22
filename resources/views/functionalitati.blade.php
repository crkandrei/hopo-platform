@extends('layouts.landing')

@section('title', 'Funcționalități HOPO – Gestiune locuri de joacă | Sesiuni, RFID, Bonuri Fiscale')
@section('meta_description', 'Descoperă funcționalitățile HOPO: cronometrare sesiuni, brățări RFID, calcul automat tarife, bonuri fiscale ANAF, rezervări online și rapoarte zilnice.')
@section('canonical', 'https://hopo.ro/functionalitati')

@section('content')
    <!-- Features Section -->
    <section id="features" class="pt-28 pb-20 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16" data-animate>
                <h1 class="text-3xl font-bold mb-4">Funcționalități pentru gestiunea și rezervările locului de joacă</h1>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    De la intrarea copilului până la închiderea zilei - o soluție completă.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="p-6 rounded-xl border border-gray-100 hover:border-hopo-purple/20 hover:shadow-lg transition-all" data-animate>
                    <div class="w-12 h-12 bg-hopo-purple/10 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Cronometrare sesiuni</h3>
                    <p class="text-gray-600 text-sm">
                        Start, pauză, reluare, stop. Timpul se calculează automat, fără erori.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="p-6 rounded-xl border border-gray-100 hover:border-hopo-purple/20 hover:shadow-lg transition-all" data-animate data-delay="100">
                    <div class="w-12 h-12 bg-hopo-purple/10 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Identificare rapidă</h3>
                    <p class="text-gray-600 text-sm">
                        Cu brățară RFID sau manual — sistemul identifică copilul și pornește sesiunea în secunde.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="p-6 rounded-xl border border-gray-100 hover:border-hopo-purple/20 hover:shadow-lg transition-all" data-animate data-delay="200">
                    <div class="w-12 h-12 bg-hopo-purple/10 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Calcul automat preț</h3>
                    <p class="text-gray-600 text-sm">
                        Tarife diferite pe zile, perioade speciale, vouchere - toate calculate automat.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="p-6 rounded-xl border border-gray-100 hover:border-hopo-purple/20 hover:shadow-lg transition-all" data-animate data-delay="300">
                    <div class="w-12 h-12 bg-hopo-coral/10 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-hopo-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Bonuri fiscale</h3>
                    <p class="text-gray-600 text-sm">
                        Integrare cu casa de marcat. Bon fiscal emis în secunde, conform ANAF.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="p-6 rounded-xl border border-gray-100 hover:border-hopo-purple/20 hover:shadow-lg transition-all" data-animate data-delay="400">
                    <div class="w-12 h-12 bg-hopo-coral/10 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-hopo-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Rapoarte zilnice</h3>
                    <p class="text-gray-600 text-sm">
                        Încasări, sesiuni, trafic pe ore - toate datele de care ai nevoie.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="p-6 rounded-xl border border-gray-100 hover:border-hopo-purple/20 hover:shadow-lg transition-all" data-animate data-delay="500">
                    <div class="w-12 h-12 bg-hopo-coral/10 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-hopo-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Rezervări online</h3>
                    <p class="text-gray-600 text-sm">
                        Trimite un link clienților și aceștia rezervă singuri petrecerea. Tu doar confirmi.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section class="py-20 px-6 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16" data-animate>
                <h2 class="text-3xl font-bold mb-4">Cum funcționează HOPO în 3 pași</h2>
                <p class="text-gray-600">Trei pași și gata.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center" data-animate>
                    <div class="w-16 h-16 bg-hopo-purple text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                    <h3 class="font-semibold mb-2">Pornești sesiunea</h3>
                    <p class="text-gray-600 text-sm">Scanezi brățara RFID sau selectezi copilul manual — sesiunea pornește instant.</p>
                </div>
                <div class="text-center" data-animate data-delay="150">
                    <div class="w-16 h-16 bg-hopo-purple text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                    <h3 class="font-semibold mb-2">Copilul se joacă</h3>
                    <p class="text-gray-600 text-sm">Timpul curge automat. Poți pune pauză dacă ies pentru prânz.</p>
                </div>
                <div class="text-center" data-animate data-delay="300">
                    <div class="w-16 h-16 bg-hopo-coral text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                    <h3 class="font-semibold mb-2">Încasezi și emiti bon</h3>
                    <p class="text-gray-600 text-sm">Oprești sesiunea, vezi prețul calculat, încasezi și emiti bonul fiscal.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 px-6 bg-hopo-purple text-white text-center">
        <div class="max-w-2xl mx-auto" data-animate>
            <h2 class="text-3xl font-bold mb-4">Gata să simplifici gestiunea locului de joacă?</h2>
            <p class="text-indigo-200 mb-8">Setup în sub 24h. Prima lună gratuită.</p>
            <a href="/contact" class="bg-white text-hopo-purple hover:bg-gray-100 px-8 py-3 rounded-lg font-medium transition-colors inline-block">
                Solicită demo gratuit
            </a>
        </div>
    </section>
@endsection
