<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'HOPO – program de gestiune pentru locuri de joacă indoor. Sesiuni automate, brățări RFID, bonuri fiscale ANAF, rezervări online.')">
    <title>@yield('title', 'HOPO – Soft gestiune & rezervări loc de joacă | Bonuri Fiscale ANAF')</title>
    <link rel="canonical" href="@yield('canonical', 'https://hopo.ro/')">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://hopo.ro/">
    <meta property="og:title" content="HOPO – Soft gestiune & rezervări loc de joacă">
    <meta property="og:description" content="HOPO este soft-ul complet pentru gestiunea locului tău de joacă. Cronometrare sesiuni, brățări RFID, bonuri fiscale ANAF și rezervări online.">
    <meta property="og:image" content="https://hopo.ro/images/hopo-og-image.png">
    <meta property="og:locale" content="ro_RO">
    <meta property="og:site_name" content="HOPO">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="HOPO – Soft gestiune loc de joacă">
    <meta name="twitter:description" content="Cronometrare sesiuni, brățări RFID, calcul automat tarife, bonuri fiscale ANAF și rezervări online.">
    <meta name="twitter:image" content="https://hopo.ro/images/hopo-og-image.png">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=3">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=3">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}?v=3">
    <link rel="shortcut icon" href="{{ asset('favicon-32x32.png') }}?v=3">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon-96x96.png') }}?v=3">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(env('GOOGLE_ANALYTICS_ID'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('GOOGLE_ANALYTICS_ID') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ env('GOOGLE_ANALYTICS_ID') }}');
    </script>
    @endif

    <style>
        .gradient-text {
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        [x-cloak] { display: none !important; }

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

        @media (prefers-reduced-motion: reduce) {
            [data-animate] { opacity: 1; transform: none; transition: none; }
        }
    </style>

    @stack('head_scripts')
</head>
<body class="font-sans antialiased bg-white text-gray-900">

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 bg-white/80 backdrop-blur-md z-50 border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-6 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/">
                        <picture>
                            <source srcset="{{ asset('images/hopo-logo.webp') }}" type="image/webp">
                            <img src="{{ asset('images/hopo-logo-optimized.png') }}" alt="Hopo - Logo soft gestiune locuri de joacă" loading="eager" fetchpriority="high" width="600" height="281" class="h-16 w-auto">
                        </picture>
                    </a>
                </div>

                <!-- Nav Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/functionalitati" class="text-gray-600 hover:text-hopo-purple transition-colors text-sm font-medium">Funcționalități</a>
                    <a href="/preturi" class="text-gray-600 hover:text-hopo-purple transition-colors text-sm font-medium">Prețuri</a>
                    <a href="/#faq" class="text-gray-600 hover:text-hopo-purple transition-colors text-sm font-medium">FAQ</a>
                    <a href="/contact" class="text-gray-600 hover:text-hopo-purple transition-colors text-sm font-medium">Contact</a>
                </div>

                <!-- CTA -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-hopo-purple transition-colors text-sm font-medium">Autentificare</a>
                    <a href="/contact" class="bg-hopo-purple hover:bg-hopo-purple-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Solicită demo
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-12 px-6 bg-gray-900 text-gray-400">
        <div class="max-w-6xl mx-auto">
            <div class="grid md:grid-cols-3 gap-8 mb-8">
                <div>
                    <img src="{{ asset('images/hopo-logo.png') }}" alt="Hopo - Logo soft gestiune locuri de joacă" loading="lazy" class="h-8 w-auto brightness-0 invert opacity-70 mb-4">
                    <p class="text-sm">Soft de gestiune pentru locuri de joacă indoor. Sesiuni, identificare rapidă, bonuri fiscale.</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Contact</h4>
                    <p class="text-sm mb-2">📧 contact@hopo.ro</p>
                    <p class="text-sm mb-2">📞 <a href="tel:0752620694" class="hover:text-white transition-colors">0752 620 694</a></p>
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
            </div>
        </div>
    </footer>

    <script>
        // Scroll animations
        document.addEventListener('DOMContentLoaded', function () {
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                    }
                });
            }, { threshold: 0.1 });
            document.querySelectorAll('[data-animate]').forEach(function (el) {
                observer.observe(el);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
