<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hopo – program de gestiune pentru locuri de joacă: cronometrare automată, brățări RFID, bonuri fiscale ANAF, rapoarte zilnice și rezervări online petreceri. Demo gratuit!">
    <title>Program gestiune & rezervări loc de joacă – Bonuri fiscale & RFID | Hopo</title>
    <link rel="canonical" href="https://hopo.ro/">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://hopo.ro/">
    <meta property="og:title" content="Hopo – Soft gestiune & rezervări loc de joacă | Sesiuni, brățări RFID, bonuri fiscale">
    <meta property="og:description" content="Hopo este soft-ul complet pentru gestiunea locului tău de joacă. Cronometrare sesiuni, brățări RFID, calcul automat tarife, bonuri fiscale conforme ANAF și rezervări online petreceri.">
    <meta property="og:image" content="https://hopo.ro/images/hopo-og-image.png">
    <meta property="og:locale" content="ro_RO">
    <meta property="og:site_name" content="Hopo">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://hopo.ro/">
    <meta name="twitter:title" content="Hopo – Soft gestiune loc de joacă">
    <meta name="twitter:description" content="Cronometrare sesiuni, brățări RFID, calcul automat tarife, bonuri fiscale conforme ANAF și rezervări online petreceri.">
    <meta name="twitter:image" content="https://hopo.ro/images/hopo-og-image.png">
    
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=3">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=3">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}?v=3">
    <link rel="shortcut icon" href="{{ asset('favicon-32x32.png') }}?v=3">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon-96x96.png') }}?v=3">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @if(env('GOOGLE_ANALYTICS_ID'))
    <!-- Google Analytics 4 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('GOOGLE_ANALYTICS_ID') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ env('GOOGLE_ANALYTICS_ID') }}');
    </script>
    @endif
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        [x-cloak] { display: none !important; }

        /* Scroll-triggered animations */
        [data-animate] {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.72s ease, transform 0.72s ease;
        }
        [data-animate].is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        [data-delay="100"] { transition-delay: 0.10s; }
        [data-delay="150"] { transition-delay: 0.15s; }
        [data-delay="200"] { transition-delay: 0.20s; }
        [data-delay="300"] { transition-delay: 0.30s; }
        [data-delay="400"] { transition-delay: 0.40s; }
        [data-delay="500"] { transition-delay: 0.50s; }
        [data-delay="600"] { transition-delay: 0.60s; }

        /* Hero image — load animation */
        @keyframes heroFadeUp {
            from { opacity: 0; transform: translateY(32px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .hero-image-anim {
            animation: heroFadeUp 0.72s ease 0.35s both;
        }
        @media (prefers-reduced-motion: reduce) {
            [data-animate] { opacity: 1; transform: none; transition: none; }
            .hero-image-anim { animation: none; }
        }
    </style>
    <script type="application/ld+json">
    @verbatim
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "Organization",
                "@id": "https://hopo.ro/#organization",
                "name": "Hopo",
                "url": "https://hopo.ro",
                "logo": {
                    "@type": "ImageObject",
                    "url": "https://hopo.ro/images/hopo-logo.png",
                    "width": 200,
                    "height": 60
                },
                "contactPoint": {
                    "@type": "ContactPoint",
                    "telephone": "+40752620694",
                    "contactType": "sales",
                    "email": "contact@hopo.ro",
                    "availableLanguage": "Romanian"
                },
                "sameAs": []
            },
            {
                "@type": "SoftwareApplication",
                "@id": "https://hopo.ro/#software",
                "name": "Hopo",
                "applicationCategory": "BusinessApplication",
                "operatingSystem": "Web",
                "description": "Soft de gestiune și rezervări online pentru locuri de joacă indoor. Cronometrare sesiuni, brățări RFID, calcul automat tarife, bonuri fiscale și rezervări petreceri.",
                "url": "https://hopo.ro",
                "provider": {
                    "@id": "https://hopo.ro/#organization"
                },
                "offers": [
                    {
                        "@type": "Offer",
                        "name": "Plan START",
                        "price": "69",
                        "priceCurrency": "EUR",
                        "priceValidUntil": "2026-12-31",
                        "availability": "https://schema.org/InStock"
                    },
                    {
                        "@type": "Offer",
                        "name": "Plan STANDARD",
                        "price": "99",
                        "priceCurrency": "EUR",
                        "priceValidUntil": "2026-12-31",
                        "availability": "https://schema.org/InStock"
                    },
                    {
                        "@type": "Offer",
                        "name": "Plan PRO",
                        "price": "129",
                        "priceCurrency": "EUR",
                        "priceValidUntil": "2026-12-31",
                        "availability": "https://schema.org/InStock"
                    }
                ],
                "featureList": [
                    "Cronometrare sesiuni de joacă",
                    "Brățări RFID pentru identificare",
                    "Calcul automat preț pe oră",
                    "Tarife diferențiate pe zile și sărbători",
                    "Emitere bonuri fiscale conform ANAF",
                    "Rapoarte zilnice de încasări",
                    "Evidență copii și părinți",
                    "Dashboard live cu sesiuni active",
                    "Integrare case de marcat Datecs",
                    "Rezervări online petreceri și zile de naștere"
                ],
                "screenshot": "https://hopo.ro/images/screenshot-dashboard.png"
            },
            {
                "@type": "WebSite",
                "@id": "https://hopo.ro/#website",
                "url": "https://hopo.ro",
                "name": "Hopo - Soft gestiune loc de joacă",
                "publisher": {
                    "@id": "https://hopo.ro/#organization"
                },
                "inLanguage": "ro-RO"
            },
            {
                "@type": "WebPage",
                "@id": "https://hopo.ro/#webpage",
                "url": "https://hopo.ro",
                "name": "Hopo – Soft gestiune loc de joacă | Sesiuni, brățări RFID, bonuri fiscale",
                "description": "Hopo este soft-ul complet pentru gestiunea locului tău de joacă. Cronometrare sesiuni, brățări RFID, calcul automat tarife și bonuri fiscale conforme ANAF.",
                "isPartOf": {
                    "@id": "https://hopo.ro/#website"
                },
                "about": {
                    "@id": "https://hopo.ro/#software"
                },
                "inLanguage": "ro-RO"
            },
            {
                "@type": "FAQPage",
                "@id": "https://hopo.ro/#faq",
                "mainEntity": [
                    {
                        "@type": "Question",
                        "name": "Ce este HOPO și la ce folosește?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "HOPO este un software pentru locuri de joacă care automatizează accesul copiilor, calculează timpul petrecut în locație și generează automat plata la ieșire. Sistemul elimină calculele manuale și reduce erorile de facturare."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "Cum funcționează sistemul HOPO?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Procesul este simplu: 1) Copilul primește o brățară sau un card. 2) La intrare se scanează codul și începe cronometrul. 3) La ieșire, sistemul calculează automat durata și prețul. 4) Totul este vizibil în timp real în dashboard."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "Pentru ce tipuri de locuri de joacă este potrivit HOPO?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "HOPO este potrivit pentru: locuri de joacă indoor, centre de distracții pentru copii, spații de joacă din restaurante sau malluri, parcuri tematice, săli de evenimente pentru copii."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "HOPO calculează automat tarifele și timpul?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Da. Sistemul calculează automat: durata vizitei, tarife pe oră sau pe intervale, oferte speciale sau pachete. Nu mai este nevoie de calcule manuale la recepție."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "Se poate integra HOPO cu casa de marcat fiscală?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Da. HOPO se poate integra cu casa de marcat, astfel încât bonul fiscal să fie emis automat la finalul vizitei, fără introducere manuală."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "Pot vedea în timp real câți copii sunt în locul de joacă?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Da. Dashboard-ul HOPO arată în timp real: numărul de copii prezenți, durata vizitelor, încasările din ziua curentă."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "Ce echipamente sunt necesare pentru HOPO?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "În mod normal ai nevoie de: un calculator sau tabletă la recepție, imprimantă fiscală (opțional) și, opțional, un scanner de coduri de bare sau cititor RFID."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "Pot testa HOPO înainte de a cumpăra?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Da. Oferim demo gratuit, astfel încât să vezi cum funcționează sistemul în locația ta înainte de a lua o decizie."
                        }
                    }
                ]
            }
        ]
    }
    @endverbatim
    </script>
</head>
<body class="font-sans antialiased bg-white text-gray-900">
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 bg-white/80 backdrop-blur-md z-50 border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-6 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center">
                    <img src="{{ asset('images/hopo-logo.png') }}" alt="Hopo - Logo soft gestiune locuri de joacă" loading="eager" class="h-16 w-auto">
                </div>
                
                <!-- Nav Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-600 hover:text-hopo-purple transition-colors text-sm font-medium">Funcționalități</a>
                    <a href="#pricing" class="text-gray-600 hover:text-hopo-purple transition-colors text-sm font-medium">Prețuri</a>
                    <a href="#faq" class="text-gray-600 hover:text-hopo-purple transition-colors text-sm font-medium">FAQ</a>
                    <a href="#contact" class="text-gray-600 hover:text-hopo-purple transition-colors text-sm font-medium">Contact</a>
                </div>
                
                <!-- CTA -->
                <div class="flex items-center space-x-4">
                    <a href="https://app.hopo.ro/login" class="text-gray-600 hover:text-hopo-purple transition-colors text-sm font-medium">Autentificare</a>
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
                    <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-3">
                        Program de gestiune și rezervări pentru 
                        <span class="gradient-text">locuri de joacă</span>
                    </h1>
                    <p class="text-2xl md:text-3xl font-normal text-gray-600 mb-4">Fără caiete, fără greșeli, fără stres</p>
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        HOPO este un <strong>program de gestiune</strong> pentru locuri de joacă. 
                        Sesiuni, prețuri, bonuri fiscale și <strong>rezervări online</strong> — totul într-o singură aplicație.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#contact" class="bg-hopo-purple hover:bg-hopo-purple-dark text-white px-6 py-3 rounded-lg font-medium transition-colors text-center">
                            Solicită un demo gratuit
                        </a>
                        <a href="#features" class="border border-gray-300 hover:border-hopo-purple text-gray-700 hover:text-hopo-purple px-6 py-3 rounded-lg font-medium transition-colors text-center">
                            Vezi funcționalități
                        </a>
                    </div>
                </div>
                
                <!-- Right: App Screenshot Mockup -->
                <div class="relative lg:ml-8 hero-image-anim">
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
                                <img src="{{ asset('images/screenshot-dashboard.png') }}" alt="Dashboard Hopo - sesiuni active, încasări și statistici loc de joacă" width="800" height="600" loading="lazy" class="w-full h-full object-cover object-top">
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

    <!-- Social Proof Stats -->
    <section class="py-12 px-6 bg-gray-50" id="stats-section">
        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 sm:gap-0 sm:divide-x sm:divide-gray-200">

                <!-- Stat 1 – Locuri de joacă -->
                <div class="flex flex-col items-center text-center px-6 gap-3">
                    <div class="w-14 h-14 rounded-2xl bg-hopo-purple/10 flex items-center justify-center">
                        <svg class="w-7 h-7 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-3xl md:text-4xl font-bold gradient-text leading-none">
                            <span class="stat-counter" data-target="20" data-suffix="+">0</span>
                        </div>
                        <div class="text-sm font-medium text-gray-700 mt-1">locuri de joacă</div>
                        <div class="text-xs text-gray-400">care folosesc HOPO</div>
                    </div>
                </div>

                <!-- Stat 2 – Copii -->
                <div class="flex flex-col items-center text-center px-6 gap-3">
                    <div class="w-14 h-14 rounded-2xl bg-hopo-purple/10 flex items-center justify-center">
                        <svg class="w-7 h-7 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-3xl md:text-4xl font-bold gradient-text leading-none">
                            <span class="stat-counter" data-target="15000" data-suffix="+" data-separator=".">0</span>
                        </div>
                        <div class="text-sm font-medium text-gray-700 mt-1">copii înregistrați</div>
                        <div class="text-xs text-gray-400">în platformă</div>
                    </div>
                </div>

                <!-- Stat 3 – Ore de joacă -->
                <div class="flex flex-col items-center text-center px-6 gap-3">
                    <div class="w-14 h-14 rounded-2xl bg-hopo-purple/10 flex items-center justify-center">
                        <svg class="w-7 h-7 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-3xl md:text-4xl font-bold gradient-text leading-none">
                            <span class="stat-counter" data-target="15000" data-suffix="+" data-separator=".">0</span>
                        </div>
                        <div class="text-sm font-medium text-gray-700 mt-1">ore de joacă</div>
                        <div class="text-xs text-gray-400">gestionate lunar</div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <script>
    (function () {
        function animateCounter(el) {
            const target = parseInt(el.dataset.target, 10);
            const suffix = el.dataset.suffix || '';
            const separator = el.dataset.separator || '';
            const duration = 1800;
            const startTime = performance.now();

            function format(n) {
                if (!separator) return n + suffix;
                return n.toLocaleString('ro-RO').replace(/\./g, separator) + suffix;
            }

            function step(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // ease-out cubic
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(eased * target);
                el.textContent = format(current);
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        }

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.querySelectorAll('.stat-counter').forEach(animateCounter);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        const section = document.getElementById('stats-section');
        if (section) observer.observe(section);
    })();
    </script>

    <!-- Pain Points Solved -->
    <section class="py-16 px-6 bg-gradient-to-br from-hopo-purple to-indigo-700 text-white relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        
        <div class="max-w-6xl mx-auto relative">
            <div class="text-center mb-12" data-animate>
                <h2 class="text-xl md:text-2xl font-bold text-white/90 uppercase tracking-wider mb-2">De ce ai nevoie de un soft pentru locul de joacă?</h2>
                <p class="text-white/70 text-lg">Probleme rezolvate din prima zi</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1 – Siguranță / Stop pierderi bani -->
                <div class="group relative" data-animate data-delay="100">
                    <div class="absolute inset-0 bg-white/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-6 hover:border-white/30 transition-all h-full flex flex-col">
                        <div class="flex items-center gap-3 mb-3">
                            <!-- Shield icon – Siguranță -->
                            <svg class="w-6 h-6 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span class="text-white font-semibold">Stop pierderilor de bani la încasare</span>
                        </div>
                        <p class="text-white/70 text-sm leading-relaxed">Fiecare secundă e contorizată digital. Zero calcule manuale, zero „uitări". Recuperezi până la 15% din încasările care înainte se pierdeau prin erori.</p>
                    </div>
                </div>

                <!-- Card 2 – Viteză / Adio haos -->
                <div class="group relative" data-animate data-delay="200">
                    <div class="absolute inset-0 bg-white/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-6 hover:border-white/30 transition-all h-full flex flex-col">
                        <div class="flex items-center gap-3 mb-3">
                            <!-- Bolt icon – Viteză -->
                            <svg class="w-6 h-6 text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span class="text-white font-semibold">Adio, haos la orele de vârf!</span>
                        </div>
                        <p class="text-white/70 text-sm leading-relaxed">10 copii ies deodată? Nicio problemă. Check-out în 5 secunde per copil — angajații rămân calmi, părinții pleacă mulțumiți.</p>
                    </div>
                </div>

                <!-- Card 3 – Cloud / Libertate -->
                <div class="group relative" data-animate data-delay="300">
                    <div class="absolute inset-0 bg-white/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-6 hover:border-white/30 transition-all h-full flex flex-col">
                        <div class="flex items-center gap-3 mb-3">
                            <!-- Cloud icon – Libertate/Cloud -->
                            <svg class="w-6 h-6 text-blue-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                            </svg>
                            <span class="text-white font-semibold">Afacerea ta, în buzunarul tău</span>
                        </div>
                        <p class="text-white/70 text-sm leading-relaxed">Nu mai suni la recepție să afli cum merge ziua. Încasări live și câți copii sunt în locație — direct pe telefonul tău, oricând.</p>
                    </div>
                </div>

                <!-- Card 4 – Bon fiscal / Conformitate ANAF -->
                <div class="group relative" data-animate data-delay="400">
                    <div class="absolute inset-0 bg-white/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-6 hover:border-white/30 transition-all h-full flex flex-col">
                        <div class="flex items-center gap-3 mb-3">
                            <!-- Receipt/document icon – Conformitate -->
                            <svg class="w-6 h-6 text-orange-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="text-white font-semibold">Bonuri fiscale fără bătaie de cap</span>
                        </div>
                        <p class="text-white/70 text-sm leading-relaxed">Tasezi manual fiecare bon? Risc de amendă și timp pierdut. HOPO printează automat, conform ANAF, la fiecare plată.</p>
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
            
            <!-- Social proof text -->
            <div class="mt-6 text-center">
                <p class="text-white/60 text-sm">
                    <!-- PLACEHOLDER: Actualizează cu date reale -->
                    ✓ Folosit în locații reale din România · ✓ Setup în sub 24h · ✓ Suport în limba română
                </p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16" data-animate>
                <h2 class="text-3xl font-bold mb-4">Funcționalități pentru gestiunea și rezervările locului de joacă</h2>
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
                <h2 class="text-3xl font-bold mb-4">Cum funcționează Hopo în 3 pași</h2>
                <p class="text-gray-600">Trei pași și gata.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center" data-animate>
                    <div class="w-16 h-16 bg-hopo-purple text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                    <h3 class="font-semibold mb-2">Pornește sesiunea</h3>
                    <p class="text-gray-600 text-sm">Scanează brățara RFID sau selectează copilul manual — sesiunea pornește instant.</p>
                </div>
                <div class="text-center" data-animate data-delay="150">
                    <div class="w-16 h-16 bg-hopo-purple text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                    <h3 class="font-semibold mb-2">Copilul se joacă</h3>
                    <p class="text-gray-600 text-sm">Timpul curge automat. Poți pune pauză dacă ies pentru prânz.</p>
                </div>
                <div class="text-center" data-animate data-delay="300">
                    <div class="w-16 h-16 bg-hopo-coral text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                    <h3 class="font-semibold mb-2">Încasezi și emiti bon</h3>
                    <p class="text-gray-600 text-sm">Oprești sesiunea, vezi prețul calculat, încasezi și emiți bonul fiscal.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Callout Banner – Autoritate / Competitivitate -->
    <section class="py-16 px-6 bg-indigo-50 border-y border-indigo-100">
        <div class="max-w-4xl mx-auto text-center" data-animate>
            <!-- Icon cluster -->
            <div class="flex justify-center items-center gap-4 mb-6">
                <!-- Shield – Siguranță -->
                <div class="w-12 h-12 rounded-full bg-white shadow-sm border border-indigo-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <!-- Bolt – Viteză -->
                <div class="w-14 h-14 rounded-full bg-white shadow-md border border-indigo-200 flex items-center justify-center">
                    <svg class="w-7 h-7 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <!-- Cloud – Libertate -->
                <div class="w-12 h-12 rounded-full bg-white shadow-sm border border-indigo-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                    </svg>
                </div>
            </div>

            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
                Luptă de la egal la egal cu marile francize
            </h2>
            <p class="text-gray-600 text-lg leading-relaxed max-w-2xl mx-auto">
                Nu lăsa tehnologia să fie motivul pentru care pierzi clienți în fața mall-urilor.
                HOPO îți oferă aceleași instrumente premium&nbsp;— brățări, scanare, bonuri fiscale rapide&nbsp;—
                la un cost adaptat pentru afacerea ta locală. Arată-le părinților că locația ta este
                <strong class="text-gray-800">sigură, modernă și digitalizată</strong>.
            </p>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12" data-animate>
                <h2 class="text-3xl font-bold mb-4">Avem diferite pachete în funcție de nevoie</h2>
                <p class="text-gray-600 text-lg">Contactează-ne pentru mai multe detalii și o ofertă personalizată.</p>
                <a href="#contact" class="inline-block mt-6 bg-hopo-purple hover:bg-hopo-purple-dark text-white px-8 py-3 rounded-lg font-medium transition-colors">
                    Solicită o ofertă
                </a>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mb-8">
                <!-- START Package -->
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 shadow-lg hover:border-hopo-purple/50 transition-all flex flex-col" data-animate>
                    <div class="text-center flex flex-col flex-1">
                        <h3 class="text-xl font-semibold mb-2">START</h3>
                        {{-- PRET ASCUNS TEMPORAR
                        <div class="mb-6">
                            <div class="flex items-center justify-center gap-2">
                                <span class="text-2xl font-bold text-gray-400 line-through">99 €</span>
                                <span class="text-4xl font-bold text-green-600">69</span>
                                <span class="text-gray-600">€ / lună</span>
                            </div>
                        </div>
                        --}}
                        <ul class="text-left space-y-3 flex-1">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Check-in / check-out sub 10 secunde</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Identificare digitală copii & părinți</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Cronometrare automată + calcul tarif</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Dashboard Live — copii prezenți & încasări azi</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Plăți multi-metodă (Cash / Card / Voucher)</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Raport zilnic + raport managerial la închidere</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Suport email & documentație</span>
                            </li>
                        </ul>
                        <a href="#contact" class="block w-full bg-gray-700 hover:bg-gray-800 text-white py-3 rounded-lg font-medium transition-colors mt-auto">
                            Solicită demo gratuit
                        </a>
                    </div>
                </div>

                <!-- STANDARD Package -->
                <div class="bg-white border-2 border-hopo-purple rounded-2xl p-8 shadow-lg relative flex flex-col" data-animate data-delay="150">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-hopo-purple text-white px-4 py-1 rounded-full text-sm font-medium">
                        Popular
                    </div>
                    <div class="text-center flex flex-col flex-1">
                        <h3 class="text-xl font-semibold mb-2">STANDARD</h3>
                        {{-- PRET ASCUNS TEMPORAR
                        <div class="mb-6">
                            <div class="flex items-center justify-center gap-2">
                                <span class="text-2xl font-bold text-gray-400 line-through">129 €</span>
                                <span class="text-4xl font-bold text-green-600">99</span>
                                <span class="text-gray-600">€ / lună</span>
                            </div>
                        </div>
                        --}}
                        <ul class="text-left space-y-3 flex-1">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm"><strong>Tot din START</strong></span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Bon fiscal automat</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Vânzări suplimentare (șosete, apă, snacks)</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Acces cloud de oriunde</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Statistici WoW (săptămână vs. săptămână)</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Rapoarte pe durate sesiune</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Funcția Pauză</span>
                            </li>
                        </ul>
                        <a href="#contact" class="block w-full bg-hopo-purple hover:bg-hopo-purple-dark text-white py-3 rounded-lg font-medium transition-colors mt-auto">
                            Solicită demo gratuit
                        </a>
                    </div>
                </div>

                <!-- PRO Package -->
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 shadow-lg hover:border-hopo-coral/50 transition-all flex flex-col" data-animate data-delay="300">
                    <div class="text-center flex flex-col flex-1">
                        <h3 class="text-xl font-semibold mb-2">PRO</h3>
                        {{-- PRET ASCUNS TEMPORAR
                        <div class="mb-6">
                            <div class="flex items-center justify-center gap-2">
                                <span class="text-2xl font-bold text-gray-400 line-through">159 €</span>
                                <span class="text-4xl font-bold text-green-600">129</span>
                                <span class="text-gray-600">€ / lună</span>
                            </div>
                        </div>
                        --}}
                        <ul class="text-left space-y-3 flex-1">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm"><strong>Tot din STANDARD</strong></span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Brățări NFC / cod de bare</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Log-uri antifraudă detaliate</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Rezervări petreceri & zile de naștere</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Grafice trafic pe ore</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Sistem fidelizare avansat (voucher sau abonament)</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Link de rezervare online — clienții tăi rezervă singuri</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Suport prioritar + setup asistat</span>
                            </li>
                        </ul>
                        <a href="#contact" class="block w-full bg-gray-700 hover:bg-gray-800 text-white py-3 rounded-lg font-medium transition-colors mt-auto">
                            Solicită demo gratuit
                        </a>
                    </div>
                </div>
            </div>

            {{-- OFERTA ASCUNSA TEMPORAR
            <p class="text-center text-gray-600 text-sm">
                🎁 <strong>Ofertă de lansare:</strong> Prima lună gratuită, apoi 50% reducere în luna a doua.
            </p>
            --}}
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 px-6 bg-gradient-to-br from-gray-50 to-white relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-20 left-0 w-72 h-72 bg-hopo-purple/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-0 w-64 h-64 bg-hopo-coral/5 rounded-full blur-3xl"></div>
        
        <div class="max-w-4xl mx-auto relative">
            <div class="text-center mb-12" data-animate>
                <span class="inline-block px-4 py-1.5 bg-hopo-purple/10 text-hopo-purple text-sm font-medium rounded-full mb-4">Întrebări frecvente</span>
                <h2 class="text-3xl md:text-4xl font-bold mb-4">
                    Află mai multe despre 
                    <span class="gradient-text">HOPO</span>
                </h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Răspunsuri la cele mai frecvente întrebări despre software-ul pentru locuri de joacă
                </p>
            </div>
            
            <!-- FAQ Accordion -->
            <div class="space-y-4" x-data="{ activeAccordion: null }">
                <!-- FAQ Item 1 -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <button 
                        @click="activeAccordion = activeAccordion === 1 ? null : 1"
                        class="w-full px-6 py-5 flex items-center justify-between text-left"
                    >
                        <span class="font-semibold text-gray-900 pr-4">Ce este HOPO și la ce folosește?</span>
                        <div class="flex-shrink-0 w-8 h-8 bg-hopo-purple/10 rounded-full flex items-center justify-center transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === 1 }">
                            <svg class="w-4 h-4 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <div 
                        x-show="activeAccordion === 1" 
                        x-collapse
                        x-cloak
                    >
                        <div class="px-6 pb-5 text-gray-600 border-t border-gray-50 pt-4">
                            HOPO este un software pentru locuri de joacă care automatizează accesul copiilor, calculează timpul petrecut în locație și generează automat plata la ieșire. Sistemul elimină calculele manuale și reduce erorile de facturare.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <button 
                        @click="activeAccordion = activeAccordion === 2 ? null : 2"
                        class="w-full px-6 py-5 flex items-center justify-between text-left"
                    >
                        <span class="font-semibold text-gray-900 pr-4">Cum funcționează sistemul HOPO?</span>
                        <div class="flex-shrink-0 w-8 h-8 bg-hopo-purple/10 rounded-full flex items-center justify-center transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === 2 }">
                            <svg class="w-4 h-4 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <div 
                        x-show="activeAccordion === 2" 
                        x-collapse
                        x-cloak
                    >
                        <div class="px-6 pb-5 text-gray-600 border-t border-gray-50 pt-4">
                            <p class="mb-3">Procesul este simplu:</p>
                            <ul class="space-y-2">
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-hopo-purple/10 text-hopo-purple text-xs font-bold rounded-full mr-3 flex-shrink-0">1</span>
                                    <span>Copilul primește o brățară sau un card.</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-hopo-purple/10 text-hopo-purple text-xs font-bold rounded-full mr-3 flex-shrink-0">2</span>
                                    <span>La intrare se scanează codul și începe cronometrul.</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-hopo-purple/10 text-hopo-purple text-xs font-bold rounded-full mr-3 flex-shrink-0">3</span>
                                    <span>La ieșire, sistemul calculează automat durata și prețul.</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-hopo-purple/10 text-hopo-purple text-xs font-bold rounded-full mr-3 flex-shrink-0">4</span>
                                    <span>Totul este vizibil în timp real în dashboard.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <button 
                        @click="activeAccordion = activeAccordion === 3 ? null : 3"
                        class="w-full px-6 py-5 flex items-center justify-between text-left"
                    >
                        <span class="font-semibold text-gray-900 pr-4">Pentru ce tipuri de locuri de joacă este potrivit HOPO?</span>
                        <div class="flex-shrink-0 w-8 h-8 bg-hopo-purple/10 rounded-full flex items-center justify-center transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === 3 }">
                            <svg class="w-4 h-4 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <div 
                        x-show="activeAccordion === 3" 
                        x-collapse
                        x-cloak
                    >
                        <div class="px-6 pb-5 text-gray-600 border-t border-gray-50 pt-4">
                            <p class="mb-3">HOPO este potrivit pentru:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Locuri de joacă indoor</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Centre de distracții pentru copii</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Spații de joacă din restaurante sau malluri</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Parcuri tematice</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Săli de evenimente pentru copii</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <button 
                        @click="activeAccordion = activeAccordion === 4 ? null : 4"
                        class="w-full px-6 py-5 flex items-center justify-between text-left"
                    >
                        <span class="font-semibold text-gray-900 pr-4">HOPO calculează automat tarifele și timpul?</span>
                        <div class="flex-shrink-0 w-8 h-8 bg-hopo-purple/10 rounded-full flex items-center justify-center transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === 4 }">
                            <svg class="w-4 h-4 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <div 
                        x-show="activeAccordion === 4" 
                        x-collapse
                        x-cloak
                    >
                        <div class="px-6 pb-5 text-gray-600 border-t border-gray-50 pt-4">
                            <p class="mb-3"><strong class="text-gray-900">Da.</strong> Sistemul calculează automat:</p>
                            <ul class="space-y-2">
                                <li class="flex items-center">
                                    <div class="w-2 h-2 bg-hopo-purple rounded-full mr-3"></div>
                                    <span>Durata vizitei</span>
                                </li>
                                <li class="flex items-center">
                                    <div class="w-2 h-2 bg-hopo-purple rounded-full mr-3"></div>
                                    <span>Tarife pe oră sau pe intervale</span>
                                </li>
                                <li class="flex items-center">
                                    <div class="w-2 h-2 bg-hopo-purple rounded-full mr-3"></div>
                                    <span>Oferte speciale sau pachete</span>
                                </li>
                            </ul>
                            <p class="mt-3 text-sm bg-green-50 text-green-700 px-3 py-2 rounded-lg">
                                ✓ Nu mai este nevoie de calcule manuale la recepție.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <button 
                        @click="activeAccordion = activeAccordion === 5 ? null : 5"
                        class="w-full px-6 py-5 flex items-center justify-between text-left"
                    >
                        <span class="font-semibold text-gray-900 pr-4">Se poate integra HOPO cu casa de marcat fiscală?</span>
                        <div class="flex-shrink-0 w-8 h-8 bg-hopo-purple/10 rounded-full flex items-center justify-center transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === 5 }">
                            <svg class="w-4 h-4 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <div 
                        x-show="activeAccordion === 5" 
                        x-collapse
                        x-cloak
                    >
                        <div class="px-6 pb-5 text-gray-600 border-t border-gray-50 pt-4">
                            <strong class="text-gray-900">Da.</strong> HOPO se poate integra cu casa de marcat, astfel încât bonul fiscal să fie emis automat la finalul vizitei, fără introducere manuală.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 6 -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <button 
                        @click="activeAccordion = activeAccordion === 6 ? null : 6"
                        class="w-full px-6 py-5 flex items-center justify-between text-left"
                    >
                        <span class="font-semibold text-gray-900 pr-4">Pot vedea în timp real câți copii sunt în locul de joacă?</span>
                        <div class="flex-shrink-0 w-8 h-8 bg-hopo-purple/10 rounded-full flex items-center justify-center transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === 6 }">
                            <svg class="w-4 h-4 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <div 
                        x-show="activeAccordion === 6" 
                        x-collapse
                        x-cloak
                    >
                        <div class="px-6 pb-5 text-gray-600 border-t border-gray-50 pt-4">
                            <p class="mb-3"><strong class="text-gray-900">Da.</strong> Dashboard-ul HOPO arată în timp real:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-center">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-yellow-800 font-medium">Numărul de copii prezenți</span>
                                </div>
                                <div class="bg-purple-50 border border-purple-200 rounded-xl p-3 text-center">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-purple-800 font-medium">Durata vizitelor</span>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-center">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-green-800 font-medium">Încasările din ziua curentă</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 7 -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <button 
                        @click="activeAccordion = activeAccordion === 7 ? null : 7"
                        class="w-full px-6 py-5 flex items-center justify-between text-left"
                    >
                        <span class="font-semibold text-gray-900 pr-4">Ce echipamente sunt necesare pentru HOPO?</span>
                        <div class="flex-shrink-0 w-8 h-8 bg-hopo-purple/10 rounded-full flex items-center justify-center transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === 7 }">
                            <svg class="w-4 h-4 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <div 
                        x-show="activeAccordion === 7" 
                        x-collapse
                        x-cloak
                    >
                        <div class="px-6 pb-5 text-gray-600 border-t border-gray-50 pt-4">
                            <p class="mb-3">În mod normal ai nevoie de:</p>
                            <div class="space-y-3">
                                <div class="flex items-center p-3 bg-gray-50 rounded-xl">
                                    <div class="w-10 h-10 bg-hopo-purple/10 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <span>Un calculator sau tabletă la recepție</span>
                                </div>
                                <div class="flex items-center p-3 bg-gray-50 rounded-xl">
                                    <div class="w-10 h-10 bg-hopo-coral/10 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-hopo-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <span>Scanner de coduri de bare sau cititor RFID</span>
                                        <span class="ml-2 text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">opțional</span>
                                    </div>
                                </div>
                                <div class="flex items-center p-3 bg-gray-50 rounded-xl">
                                    <div class="w-10 h-10 bg-hopo-coral/10 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-hopo-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <span>Imprimantă fiscală</span>
                                        <span class="ml-2 text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">opțional</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 8 -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <button 
                        @click="activeAccordion = activeAccordion === 8 ? null : 8"
                        class="w-full px-6 py-5 flex items-center justify-between text-left"
                    >
                        <span class="font-semibold text-gray-900 pr-4">Pot testa HOPO înainte de a cumpăra?</span>
                        <div class="flex-shrink-0 w-8 h-8 bg-hopo-purple/10 rounded-full flex items-center justify-center transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === 8 }">
                            <svg class="w-4 h-4 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <div 
                        x-show="activeAccordion === 8" 
                        x-collapse
                        x-cloak
                    >
                        <div class="px-6 pb-5 text-gray-600 border-t border-gray-50 pt-4">
                            <div class="bg-gradient-to-r from-hopo-purple/10 to-hopo-coral/10 rounded-xl p-4 border border-hopo-purple/20">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-10 h-10 bg-white rounded-full flex items-center justify-center mr-4 shadow-sm">
                                        <svg class="w-5 h-5 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 mb-1">Da! Oferim demo gratuit + prima lună gratuită</p>
                                        <p class="text-gray-600">Astfel poți vedea cum funcționează sistemul în locația ta înainte de a lua o decizie.</p>
                                    </div>
                                </div>
                                <a href="#contact" class="mt-4 inline-flex items-center text-hopo-purple font-medium hover:text-hopo-purple-dark transition-colors">
                                    Solicită demo gratuit
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 9 -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    <button 
                        @click="activeAccordion = activeAccordion === 9 ? null : 9"
                        class="w-full px-6 py-5 flex items-center justify-between text-left"
                    >
                        <span class="font-semibold text-gray-900 pr-4">Pot primi rezervări online de la clienți?</span>
                        <div class="flex-shrink-0 w-8 h-8 bg-hopo-purple/10 rounded-full flex items-center justify-center transition-transform duration-300" :class="{ 'rotate-180': activeAccordion === 9 }">
                            <svg class="w-4 h-4 text-hopo-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <div 
                        x-show="activeAccordion === 9" 
                        x-collapse
                        x-cloak
                    >
                        <div class="px-6 pb-5 text-gray-600 border-t border-gray-50 pt-4">
                            <p class="mb-3"><strong class="text-gray-900">Da!</strong> Cu pachetul <strong class="text-gray-900">PRO</strong>, HOPO generează un link unic de rezervare pe care îl poți distribui pe site, Facebook sau WhatsApp.</p>
                            <div class="space-y-2 mb-3">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-hopo-purple rounded-full mr-3"></div>
                                    <span>Clienții aleg sala, pachetul, data și ora</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-hopo-purple rounded-full mr-3"></div>
                                    <span>Completează datele copilului și tutorelui</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-hopo-purple rounded-full mr-3"></div>
                                    <span>Tu primești cererea și o confirmi cu un click</span>
                                </div>
                            </div>
                            <p class="text-sm bg-purple-50 text-purple-700 px-3 py-2 rounded-lg">
                                Fără telefoane, fără mesaje — rezervările vin direct în sistem.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom CTA -->
            <div class="mt-12 text-center">
                <p class="text-gray-600 mb-4">Nu ai găsit răspunsul căutat?</p>
                <a href="#contact" class="inline-flex items-center bg-hopo-purple hover:bg-hopo-purple-dark text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    Contactează-ne
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-6 bg-hopo-purple">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl font-bold text-white mb-4">Simplifică gestiunea locului tău de joacă</h2>
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
                <div data-animate>
                    <h2 class="text-3xl font-bold mb-4">Solicită un demo gratuit</h2>
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
                            <span class="text-gray-600">0752 620 694</span>
                        </div>
                    </div>
                </div>
                <div data-animate data-delay="150">
                    <form id="contact-form" class="space-y-4" method="POST" action="/contact">
                        @csrf
                        
                        <!-- Success message -->
                        <div id="contact-success" class="hidden p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 mb-4">
                            <p class="font-medium">Mulțumim pentru mesaj!</p>
                            <p class="text-sm">Te vom contacta în cel mai scurt timp.</p>
                        </div>
                        
                        <!-- Error message -->
                        <div id="contact-error" class="hidden p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 mb-4">
                            <p class="font-medium">Eroare</p>
                            <p id="contact-error-message" class="text-sm"></p>
                        </div>
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nume complet</label>
                            <input type="text" id="name" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-transparent outline-none" placeholder="Ion Popescu" value="{{ old('name') }}">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-transparent outline-none" placeholder="ion@locjoacă.ro" value="{{ old('email') }}">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon</label>
                            <input type="tel" id="phone" name="phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-transparent outline-none" placeholder="0752 620 694" value="{{ old('phone') }}">
                            @error('phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="playground_name" class="block text-sm font-medium text-gray-700 mb-1">Numele locului de joacă</label>
                            <input type="text" id="playground_name" name="playground_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-hopo-purple focus:border-transparent outline-none" placeholder="FunPark" value="{{ old('playground_name') }}">
                            @error('playground_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" id="contact-submit" class="w-full bg-hopo-purple hover:bg-hopo-purple-dark text-white py-3 rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="contact-submit-text">Trimite cererea</span>
                            <span id="contact-submit-loading" class="hidden">Se trimite...</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 px-6 bg-gray-900 text-gray-400">
        <div class="max-w-6xl mx-auto">
            <div class="grid md:grid-cols-3 gap-8 mb-8">
                <div>
                    <img src="{{ asset('images/hopo-logo.png') }}" alt="Hopo - Logo soft gestiune locuri de joacă" width="200" height="60" loading="lazy" class="h-8 brightness-0 invert opacity-70 mb-4">
                    <p class="text-sm">Soft de gestiune pentru locuri de joacă indoor. Sesiuni, identificare rapidă, bonuri fiscale.</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Contact</h4>
                    <p class="text-sm mb-2">📧 contact@hopo.ro</p>
                    <p class="text-sm mb-2">📞 0752 620 694</p>
                    <p class="text-sm">📍 București, România</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Legal</h4>
                    <p class="text-sm mb-2"><a href="/legal/terms" class="hover:text-white">Termeni și condiții</a></p>
                    <p class="text-sm"><a href="/legal/gdpr" class="hover:text-white">Politica GDPR</a></p>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
                <div class="text-sm mb-4 md:mb-0">
                    © {{ date('Y') }} Hopo. Toate drepturile rezervate.
                </div>
                <div class="text-sm text-gray-500">
                    <!-- PLACEHOLDER: Adaugă CUI și numele firmei când sunt disponibile -->
                    <!-- Exemplu: SC HOPO TECH SRL | CUI: RO12345678 -->
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Contact form handling
        document.addEventListener('DOMContentLoaded', function() {
            const contactForm = document.getElementById('contact-form');
            const successMessage = document.getElementById('contact-success');
            const errorMessage = document.getElementById('contact-error');
            const errorMessageText = document.getElementById('contact-error-message');
            const submitButton = document.getElementById('contact-submit');
            const submitText = document.getElementById('contact-submit-text');
            const submitLoading = document.getElementById('contact-submit-loading');

            if (contactForm) {
                contactForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    // Hide previous messages
                    successMessage.classList.add('hidden');
                    errorMessage.classList.add('hidden');
                    
                    // Disable submit button
                    submitButton.disabled = true;
                    submitText.classList.add('hidden');
                    submitLoading.classList.remove('hidden');
                    
                    // Get form data
                    const formData = new FormData(contactForm);
                    
                    // Get CSRF token from meta tag
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    
                    try {
                        const response = await fetch(contactForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            credentials: 'same-origin'
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok && data.success) {
                            // Show success message
                            successMessage.classList.remove('hidden');
                            
                            // Reset form
                            contactForm.reset();
                            
                            // Scroll to success message
                            successMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        } else {
                            // Show error message
                            errorMessageText.textContent = data.message || 'A apărut o eroare. Te rugăm să încerci din nou.';
                            errorMessage.classList.remove('hidden');
                            
                            // Scroll to error message
                            errorMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                    } catch (error) {
                        // Show error message
                        errorMessageText.textContent = 'A apărut o eroare la trimiterea mesajului. Te rugăm să încerci din nou sau să ne contactezi direct la contact@hopo.ro';
                        errorMessage.classList.remove('hidden');
                        errorMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } finally {
                        // Re-enable submit button
                        submitButton.disabled = false;
                        submitText.classList.remove('hidden');
                        submitLoading.classList.add('hidden');
                    }
                });
            }
        });
    </script>

    <script>
    (function () {
        var animObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    animObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll('[data-animate]').forEach(function (el) {
            animObserver.observe(el);
        });
    })();
    </script>
</body>
</html>
