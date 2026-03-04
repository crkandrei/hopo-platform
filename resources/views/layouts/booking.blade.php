<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Rezervare')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen font-sans">

    <header class="bg-white border-b border-gray-100 shadow-sm py-3">
        <div class="w-full max-w-2xl mx-auto px-4 sm:px-6 flex items-center gap-3">
            <a href="/">
                <img src="{{ asset('images/hopo-logo.png') }}" alt="Hopo" class="h-12 w-auto">
            </a>
            <div class="border-l border-gray-200 pl-3">
                <p class="text-[11px] font-semibold text-hopo-purple uppercase tracking-widest">Rezervare zi de naștere</p>
                <h1 class="text-base font-semibold text-gray-900 leading-tight">@yield('header-title', 'Rezervare')</h1>
            </div>
        </div>
    </header>

    <main class="w-full max-w-2xl mx-auto px-4 sm:px-6 py-8 lg:py-10">
        @yield('content')
    </main>

    <footer class="text-center text-xs text-gray-400 py-6 mt-4">
        &copy; {{ date('Y') }} Hopo &mdash; Platformă de management pentru locuri de joacă
    </footer>
</body>
</html>
