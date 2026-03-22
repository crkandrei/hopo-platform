@extends('layouts.landing')

@section('title')@yield('article_title') | Blog HOPO@endsection
@section('meta_description')@yield('article_description')@endsection
@section('canonical')https://hopo.ro/blog/@yield('article_slug')@endsection

@section('content')
    <div class="pt-28 pb-20 px-6">
        <div class="max-w-6xl mx-auto">

            <!-- Breadcrumb -->
            <nav class="text-sm text-gray-500 mb-8" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li><a href="/" class="hover:text-hopo-purple transition-colors">Acasă</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li><a href="/blog" class="hover:text-hopo-purple transition-colors">Blog</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li class="text-gray-700 truncate max-w-xs">@yield('article_title')</li>
                </ol>
            </nav>

            <div class="flex flex-col xl:flex-row gap-12">

                <!-- Article content -->
                <article class="min-w-0 flex-1">
                    <div class="text-sm text-gray-500 mb-6 flex items-center gap-4">
                        <span>@yield('article_date')</span>
                        @hasSection('article_reading_time')
                            <span>·</span>
                            <span>@yield('article_reading_time') lectură</span>
                        @endif
                    </div>

                    @yield('article_content')

                    <!-- Bottom CTA -->
                    <div class="mt-16 p-8 bg-indigo-50 rounded-2xl text-center">
                        <h2 class="text-2xl font-bold text-gray-900 mb-3">Gata să simplifici gestiunea locului de joacă?</h2>
                        <p class="text-gray-600 mb-6">Demo gratuit. Prima lună gratuită. Setup în sub 24h.</p>
                        <a href="https://hopo.ro/contact" class="bg-hopo-purple hover:bg-hopo-purple-dark text-white px-8 py-3 rounded-lg font-medium transition-colors inline-block">
                            Solicită demo gratuit HOPO
                        </a>
                    </div>
                </article>

                <!-- Sidebar -->
                <aside class="hidden xl:block xl:w-72 flex-shrink-0">
                    <div class="sticky top-28 space-y-6">
                        <!-- CTA Card -->
                        <div class="bg-hopo-purple text-white rounded-2xl p-6">
                            <h3 class="text-lg font-bold mb-2">Solicită demo gratuit</h3>
                            <p class="text-indigo-200 text-sm mb-4">Testează HOPO gratuit. Prima lună inclusă.</p>
                            <a href="https://hopo.ro/contact" class="block w-full bg-white text-hopo-purple hover:bg-gray-100 py-2 rounded-lg font-medium text-center transition-colors text-sm">
                                Solicită demo
                            </a>
                        </div>

                        <!-- Features list -->
                        <div class="bg-gray-50 rounded-2xl p-6">
                            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">HOPO include</h3>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Cronometrare sesiuni automată
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Brățări RFID identificare
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Bonuri fiscale ANAF
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Dashboard live
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Rezervări online
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Rapoarte zilnice
                                </li>
                            </ul>
                        </div>

                        <!-- Back to blog -->
                        <a href="/blog" class="flex items-center gap-2 text-sm text-gray-500 hover:text-hopo-purple transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            Înapoi la blog
                        </a>
                    </div>
                </aside>

            </div>
        </div>
    </div>
@endsection
