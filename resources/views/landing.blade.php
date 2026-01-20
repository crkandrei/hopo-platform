<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hopo - Software de gestiune pentru locuri de joacă. Sesiuni, brățări RFID, bonuri fiscale, rapoarte.">
    <title>Hopo - Software pentru locuri de joacă</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'hopo-purple': '#6366F1',
                        'hopo-purple-dark': '#4F46E5',
                        'hopo-coral': '#F87171',
                        'hopo-coral-dark': '#EF4444',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="font-sans antialiased bg-white text-gray-900">
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 bg-white/80 backdrop-blur-md z-50 border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-6 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center">
                    <img src="{{ asset('images/hopo-logo.png') }}" alt="Hopo" class="h-16">
                </div>
                
                <!-- Nav Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-600 hover:text-hopo-purple transition-colors text-sm font-medium">Funcționalități</a>
                    <a href="#pricing" class="text-gray-600 hover:text-hopo-purple transition-colors text-sm font-medium">Prețuri</a>
                    <a href="#contact" class="text-gray-600 hover:text-hopo-purple transition-colors text-sm font-medium">Contact</a>
                </div>
                
                <!-- CTA -->
                <div class="flex items-center space-x-4">
                    <a href="/login" class="text-gray-600 hover:text-hopo-purple transition-colors text-sm font-medium">Autentificare</a>
                    <a href="#contact" class="bg-hopo-purple hover:bg-hopo-purple-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Solicită demo
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-28 pb-20 overflow-hidden">
        <div class="max-w-6xl mx-auto px-6">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left: Text -->
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-6">
                        Software complet pentru 
                        <span class="gradient-text">locuri de joacă</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        Gestionează sesiunile de joacă, calculează automat prețurile și emite bonuri fiscale. 
                        Totul într-o singură aplicație.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#contact" class="bg-hopo-purple hover:bg-hopo-purple-dark text-white px-6 py-3 rounded-lg font-medium transition-colors text-center">
                            Începe acum
                        </a>
                        <a href="#features" class="border border-gray-300 hover:border-hopo-purple text-gray-700 hover:text-hopo-purple px-6 py-3 rounded-lg font-medium transition-colors text-center">
                            Vezi funcționalități
                        </a>
                    </div>
                </div>
                
                <!-- Right: App Screenshot Mockup -->
                <div class="relative lg:ml-8">
                    <!-- Browser mockup frame -->
                    <div class="bg-gray-900 rounded-xl shadow-2xl overflow-hidden">
                        <!-- Browser top bar -->
                        <div class="bg-gray-800 px-4 py-3 flex items-center gap-2">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            </div>
                            <div class="flex-1 ml-4">
                                <div class="bg-gray-700 rounded-md px-3 py-1 text-gray-400 text-xs max-w-xs">
                                    app.hopo.ro/scan
                                </div>
                            </div>
                        </div>
                        <!-- Screenshot placeholder - replace with actual screenshot -->
                        <div class="bg-gradient-to-br from-gray-100 to-gray-200 aspect-[4/3] flex items-center justify-center">
                            @if(file_exists(public_path('images/screenshot-dashboard.png')))
                                <img src="{{ asset('images/screenshot-dashboard.png') }}" alt="Hopo Dashboard" class="w-full h-full object-cover object-top">
                            @else
                                <!-- Faithful Dashboard Mockup -->
                                <div class="w-full h-full bg-gray-100 p-3 overflow-hidden">
                                    <!-- Sidebar + Content -->
                                    <div class="flex h-full gap-2">
                                        <!-- Mini Sidebar -->
                                        <div class="w-12 bg-gray-900 rounded-lg flex flex-col items-center py-3 gap-3">
                                            <div class="w-7 h-7 bg-hopo-purple rounded-lg"></div>
                                            <div class="w-6 h-6 bg-gray-700 rounded"></div>
                                            <div class="w-6 h-6 bg-gray-700 rounded"></div>
                                            <div class="w-6 h-6 bg-hopo-coral/60 rounded"></div>
                                            <div class="w-6 h-6 bg-gray-700 rounded"></div>
                                        </div>
                                        
                                        <!-- Main Content -->
                                        <div class="flex-1 space-y-2">
                                            <!-- Welcome Bar -->
                                            <div class="bg-white rounded-lg p-2 flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[10px] font-semibold text-gray-800">Bun venit! 👋</span>
                                                    <span class="text-[8px] text-gray-400">FunPark Arena</span>
                                                </div>
                                                <div class="w-6 h-6 bg-gradient-to-br from-hopo-purple to-purple-600 rounded-full"></div>
                                            </div>
                                            
                                            <!-- Stats Cards Row -->
                                            <div class="grid grid-cols-4 gap-1.5">
                                                <!-- Intrări -->
                                                <div class="bg-white rounded-lg p-2">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-[7px] text-gray-500">Intrări Copii</span>
                                                        <div class="w-4 h-4 bg-yellow-100 rounded flex items-center justify-center">
                                                            <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-baseline gap-1">
                                                        <span class="text-sm font-bold text-yellow-600">24</span>
                                                        <span class="text-[8px] text-green-600">12</span>
                                                    </div>
                                                </div>
                                                <!-- Media -->
                                                <div class="bg-white rounded-lg p-2">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-[7px] text-gray-500">Media Azi</span>
                                                        <div class="w-4 h-4 bg-purple-100 rounded flex items-center justify-center">
                                                            <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                                        </div>
                                                    </div>
                                                    <span class="text-sm font-bold text-purple-600">1h 42m</span>
                                                </div>
                                                <!-- Media Total -->
                                                <div class="bg-white rounded-lg p-2">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-[7px] text-gray-500">Media Totală</span>
                                                        <div class="w-4 h-4 bg-indigo-100 rounded flex items-center justify-center">
                                                            <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                                                        </div>
                                                    </div>
                                                    <span class="text-sm font-bold text-indigo-600">1h 38m</span>
                                                </div>
                                                <!-- Încasări -->
                                                <div class="bg-white rounded-lg p-2">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-[7px] text-gray-500">Încasări Azi</span>
                                                        <div class="w-4 h-4 bg-emerald-100 rounded flex items-center justify-center">
                                                            <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                                                        </div>
                                                    </div>
                                                    <span class="text-sm font-bold text-emerald-600">1,240 RON</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Active Sessions -->
                                            <div class="bg-white rounded-lg p-2 flex-1">
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-center gap-1">
                                                        <div class="w-4 h-4 bg-green-100 rounded flex items-center justify-center">
                                                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                        </div>
                                                        <span class="text-[9px] font-semibold text-gray-800">Sesiuni Active</span>
                                                    </div>
                                                    <span class="bg-green-100 text-green-700 text-[8px] font-medium px-1.5 py-0.5 rounded-full">12</span>
                                                </div>
                                                
                                                <!-- Session Cards Grid -->
                                                <div class="grid grid-cols-3 gap-1.5">
                                                    <!-- Card 1 -->
                                                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded p-1.5 border border-green-200">
                                                        <div class="flex items-center gap-1 mb-1">
                                                            <div class="w-4 h-4 bg-blue-200 rounded-full"></div>
                                                            <span class="text-[8px] font-medium text-gray-800">Andrei M.</span>
                                                        </div>
                                                        <div class="text-[10px] font-bold text-green-700">01:24:33</div>
                                                        <div class="text-[7px] text-gray-500">#A7X2K9</div>
                                                    </div>
                                                    <!-- Card 2 -->
                                                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded p-1.5 border border-green-200">
                                                        <div class="flex items-center gap-1 mb-1">
                                                            <div class="w-4 h-4 bg-pink-200 rounded-full"></div>
                                                            <span class="text-[8px] font-medium text-gray-800">Maria P.</span>
                                                        </div>
                                                        <div class="text-[10px] font-bold text-green-700">00:47:12</div>
                                                        <div class="text-[7px] text-gray-500">#B3M8P2</div>
                                                    </div>
                                                    <!-- Card 3 - Paused -->
                                                    <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded p-1.5 border border-amber-200">
                                                        <div class="flex items-center gap-1 mb-1">
                                                            <div class="w-4 h-4 bg-purple-200 rounded-full"></div>
                                                            <span class="text-[8px] font-medium text-gray-800">Alex T.</span>
                                                        </div>
                                                        <div class="text-[10px] font-bold text-amber-600">PAUZĂ</div>
                                                        <div class="text-[7px] text-gray-500">00:52:08</div>
                                                    </div>
                                                    <!-- Card 4 -->
                                                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded p-1.5 border border-green-200">
                                                        <div class="flex items-center gap-1 mb-1">
                                                            <div class="w-4 h-4 bg-yellow-200 rounded-full"></div>
                                                            <span class="text-[8px] font-medium text-gray-800">Diana R.</span>
                                                        </div>
                                                        <div class="text-[10px] font-bold text-green-700">02:15:41</div>
                                                        <div class="text-[7px] text-gray-500">#C5N4R7</div>
                                                    </div>
                                                    <!-- Card 5 -->
                                                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded p-1.5 border border-green-200">
                                                        <div class="flex items-center gap-1 mb-1">
                                                            <div class="w-4 h-4 bg-green-200 rounded-full"></div>
                                                            <span class="text-[8px] font-medium text-gray-800">Luca S.</span>
                                                        </div>
                                                        <div class="text-[10px] font-bold text-green-700">00:33:27</div>
                                                        <div class="text-[7px] text-gray-500">#D9T6W3</div>
                                                    </div>
                                                    <!-- Card 6 -->
                                                    <div class="bg-gradient-to-br from-pink-50 to-rose-50 rounded p-1.5 border border-pink-200">
                                                        <div class="flex items-center gap-1 mb-1">
                                                            <div class="w-4 h-4 bg-rose-200 rounded-full flex items-center justify-center">
                                                                <span class="text-[6px]">🎂</span>
                                                            </div>
                                                            <span class="text-[8px] font-medium text-gray-800">Sofia B.</span>
                                                        </div>
                                                        <div class="text-[10px] font-bold text-pink-600">01:08:55</div>
                                                        <div class="text-[7px] text-pink-500">BIRTHDAY</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Decorative elements -->
                    <div class="absolute -z-10 -top-4 -right-4 w-72 h-72 bg-hopo-purple/10 rounded-full blur-3xl"></div>
                    <div class="absolute -z-10 -bottom-8 -left-8 w-48 h-48 bg-hopo-coral/10 rounded-full blur-2xl"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pain Points Solved -->
    <section class="py-16 px-6 bg-gradient-to-br from-hopo-purple to-indigo-700 text-white relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        
        <div class="max-w-6xl mx-auto relative">
            <div class="text-center mb-12">
                <h2 class="text-xl md:text-2xl font-bold text-white/90 uppercase tracking-wider mb-2">De ce Hopo?</h2>
                <p class="text-white/70 text-lg">Probleme rezolvate din prima zi</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1 -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-white/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-6 hover:border-white/30 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white font-semibold">Preț automat instant</span>
                        </div>
                        <p class="text-white/70 text-sm">Fără calcule manuale pe hârtie</p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-white/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-6 hover:border-white/30 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white font-semibold">Scan brățară = gata</span>
                        </div>
                        <p class="text-white/70 text-sm">Fără căutare manuală în registru</p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-white/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-6 hover:border-white/30 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white font-semibold">Click → Raport complet</span>
                        </div>
                        <p class="text-white/70 text-sm">Fără Excel manual în fiecare zi</p>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="group relative">
                    <div class="absolute inset-0 bg-white/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-6 hover:border-white/30 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white font-semibold">Printare automată</span>
                        </div>
                        <p class="text-white/70 text-sm">Fără bon fiscal tastat manual</p>
                    </div>
                </div>
            </div>

            <!-- Trust badges -->
            <div class="mt-12 pt-8 border-t border-white/20 flex flex-wrap justify-center items-center gap-8">
                <div class="flex items-center gap-2 text-white/80">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span class="text-sm">Conform ANAF</span>
                </div>
                <div class="flex items-center gap-2 text-white/80">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span class="text-sm">GDPR Compliant</span>
                </div>
                <div class="flex items-center gap-2 text-white/80">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span class="text-sm">Case Datecs</span>
                </div>
                <div class="flex items-center gap-2 text-white/80">
                    <svg class="w-5 h-5 text-hopo-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span class="text-sm">Suport rapid</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4">Tot ce ai nevoie</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    De la intrarea copilului până la închiderea zilei - o soluție completă.
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="p-6 rounded-xl border border-gray-100 hover:border-hopo-purple/20 hover:shadow-lg transition-all">
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
                <div class="p-6 rounded-xl border border-gray-100 hover:border-hopo-purple/20 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 bg-hopo-purple/10 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Brățări RFID</h3>
                    <p class="text-gray-600 text-sm">
                        Scanează brățara și sistemul identifică automat copilul și sesiunea.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="p-6 rounded-xl border border-gray-100 hover:border-hopo-purple/20 hover:shadow-lg transition-all">
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
                <div class="p-6 rounded-xl border border-gray-100 hover:border-hopo-purple/20 hover:shadow-lg transition-all">
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
                <div class="p-6 rounded-xl border border-gray-100 hover:border-hopo-purple/20 hover:shadow-lg transition-all">
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
                <div class="p-6 rounded-xl border border-gray-100 hover:border-hopo-purple/20 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 bg-hopo-coral/10 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-hopo-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Evidență copii</h3>
                    <p class="text-gray-600 text-sm">
                        Bază de date cu copii, tutori, istoric sesiuni. GDPR compliant.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section class="py-20 px-6 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4">Simplu de folosit</h2>
                <p class="text-gray-600">Trei pași și gata.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-hopo-purple text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                    <h3 class="font-semibold mb-2">Scanează brățara</h3>
                    <p class="text-gray-600 text-sm">Copilul primește brățara, o scanezi și sesiunea pornește automat.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-hopo-purple text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                    <h3 class="font-semibold mb-2">Copilul se joacă</h3>
                    <p class="text-gray-600 text-sm">Timpul curge automat. Poți pune pauză dacă ies pentru prânz.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-hopo-coral text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                    <h3 class="font-semibold mb-2">Încasezi și emiti bon</h3>
                    <p class="text-gray-600 text-sm">Oprești sesiunea, vezi prețul calculat, încasezi și emiți bonul fiscal.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4">Prețuri simple</h2>
                <p class="text-gray-600">Fără costuri ascunse. Plătești lunar.</p>
            </div>
            
            <div class="max-w-lg mx-auto">
                <div class="bg-white border-2 border-hopo-purple rounded-2xl p-8 shadow-lg">
                    <div class="text-center">
                        <h3 class="text-xl font-semibold mb-2">Hopo Pro</h3>
                        <p class="text-gray-600 mb-6">Tot ce ai nevoie pentru locul tău de joacă</p>
                        <div class="mb-6">
                            <span class="text-4xl font-bold">199</span>
                            <span class="text-gray-600">EUR/lună</span>
                        </div>
                        <ul class="text-left space-y-3 mb-8">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Sesiuni și cronometrare nelimitate
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Integrare casă de marcat
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Rapoarte și statistici
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Până la 5 utilizatori
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Suport prioritar
                            </li>
                        </ul>
                        <a href="#contact" class="block w-full bg-hopo-purple hover:bg-hopo-purple-dark text-white py-3 rounded-lg font-medium transition-colors">
                            Solicită demo gratuit
                        </a>
                    </div>
                </div>
                <p class="text-center text-gray-500 text-sm mt-4">
                    Ai mai multe locații? <a href="#contact" class="text-hopo-purple hover:underline">Contactează-ne</a> pentru ofertă personalizată.
                </p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-6 bg-hopo-purple">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl font-bold text-white mb-4">Gata să simplifici gestiunea?</h2>
            <p class="text-white/80 mb-8">
                Programează un demo gratuit și vezi cum funcționează Hopo pentru locul tău de joacă.
            </p>
            <a href="#contact" class="inline-block bg-white text-hopo-purple hover:bg-gray-100 px-8 py-3 rounded-lg font-medium transition-colors">
                Programează demo
            </a>
        </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12">
                <div>
                    <h2 class="text-3xl font-bold mb-4">Hai să vorbim</h2>
                    <p class="text-gray-600 mb-8">
                        Completează formularul și te contactăm în maxim 24 de ore pentru a programa un demo.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-hopo-purple mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-gray-600">contact@hopo.ro</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-hopo-purple mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="text-gray-600">0770 123 456</span>
                        </div>
                    </div>
                </div>
                <div>
                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nume complet</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-transparent outline-none" placeholder="Ion Popescu">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-transparent outline-none" placeholder="ion@locjoacă.ro">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                            <input type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-transparent outline-none" placeholder="0770 123 456">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Numele locului de joacă</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-transparent outline-none" placeholder="FunPark">
                        </div>
                        <button type="submit" class="w-full bg-hopo-purple hover:bg-hopo-purple-dark text-white py-3 rounded-lg font-medium transition-colors">
                            Trimite cererea
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 px-6 bg-gray-900 text-gray-400">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <img src="{{ asset('images/hopo-logo.png') }}" alt="Hopo" class="h-6 brightness-0 invert opacity-70">
                </div>
                <div class="text-sm">
                    © {{ date('Y') }} Hopo. Toate drepturile rezervate.
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
