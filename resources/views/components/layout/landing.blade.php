<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': darkMode }">
<head>
    <!-- Basic Meta Tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Page Title -->
    <title>{{ $title ?? 'YorYor - Find Your Perfect Match' }}</title>
    <meta name="description" content="{{ $description ?? 'Join YorYor, the modern dating app that helps you find meaningful connections. Swipe right on love today!' }}">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $title ?? 'YorYor - Find Your Perfect Match' }}">
    <meta property="og:description" content="{{ $description ?? 'Join YorYor, the modern dating app that helps you find meaningful connections.' }}">
    <meta property="og:image" content="{{ asset('assets/images/yoryor-og-image.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'YorYor - Find Your Perfect Match' }}">
    <meta name="twitter:description" content="{{ $description ?? 'Join YorYor, the modern dating app that helps you find meaningful connections.' }}">
    <meta name="twitter:image" content="{{ asset('assets/images/yoryor-og-image.jpg') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/css/components.css', 'resources/css/scrollbar.css', 'resources/js/app.js', 'resources/js/landing.js'])

    <script>
        // Multiple attempts to initialize Lucide icons
        function initLucideIcons() {
            if (window.lucide && window.lucide.createIcons && window.lucide.icons) {
                try {
                    window.lucide.createIcons({ icons: window.lucide.icons });
                    console.log('Lucide icons initialized');
                    return true;
                } catch (error) {
                    console.error('Failed to initialize Lucide icons:', error);
                    return false;
                }
            }
            return false;
        }

        // Watch for DOM changes and reinitialize icons
        const observer = new MutationObserver(function(mutations) {
            let shouldInit = false;
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // Check if any added nodes contain data-lucide attributes
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            if (node.hasAttribute && node.hasAttribute('data-lucide')) {
                                shouldInit = true;
                            } else if (node.querySelector && node.querySelector('[data-lucide]')) {
                                shouldInit = true;
                            }
                        }
                    });
                }
            });
            if (shouldInit) {
                console.log('New Lucide icons detected, initializing...');
                initLucideIcons();
            }
        });

        // Start observing
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Initialize immediately
        initLucideIcons();

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM ready, initializing icons...');
            initLucideIcons();

            // Try again after a short delay
            setTimeout(initLucideIcons, 100);
            setTimeout(initLucideIcons, 300);
            setTimeout(initLucideIcons, 500);
            setTimeout(initLucideIcons, 1000);
        });

        // Initialize on Livewire events
        document.addEventListener('livewire:navigated', initLucideIcons);
        document.addEventListener('livewire:updated', initLucideIcons);
        document.addEventListener('livewire:loaded', initLucideIcons);

        // Force initialization on page load
        window.addEventListener('load', function() {
            console.log('Page loaded, final icon initialization...');
            initLucideIcons();
            setTimeout(initLucideIcons, 200);
        });
    </script>

    <!-- Additional Head Content -->
    @if (isset($head))
        {{ $head }}
    @endif
</head>
<body class="bg-white dark:bg-gray-900 antialiased transition-colors duration-300">
    <!-- Header -->
    @if (isset($header))
        {{ $header }}
    @else
        @livewire('components.header')
    @endif

    <!-- Main Content -->
    <main class="pt-16 relative">
        <!-- Background Pattern -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-gradient-to-br from-pink-500/5 to-purple-500/5 rounded-full blur-3xl animate-float-slow"></div>
            <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-gradient-to-br from-purple-500/5 to-indigo-500/5 rounded-full blur-3xl animate-float-reverse"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-gradient-to-br from-indigo-500/3 to-pink-500/3 rounded-full blur-3xl animate-float"></div>
        </div>

        <!-- Content wrapper with improved glass effects -->
        <div class="relative z-10">
            {{ $slot }}
        </div>
    </main>

    <!-- Footer -->
    @if (isset($footer))
        {{ $footer }}
    @else
        @livewire('components.footer')
    @endif

    <!-- Enhanced Back to Top Button -->
    <button id="backToTop" class="fixed bottom-8 right-8 bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-600 hover:from-pink-600 hover:via-purple-600 hover:to-indigo-700 text-white p-4 rounded-2xl shadow-2xl shadow-purple-500/30 hover:shadow-purple-500/50 transition-all duration-500 opacity-0 invisible transform hover:scale-110 hover:-translate-y-1 group">
        <svg class="w-6 h-6 transform transition-transform duration-300 group-hover:-translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
        <!-- Animated pulse ring -->
        <div class="absolute inset-0 rounded-2xl border-2 border-white/30 animate-ping"></div>
        <!-- Subtle glow effect -->
        <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-pink-400/20 via-purple-400/20 to-indigo-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    </button>

    <!-- Additional Scripts -->
    @if (isset($scripts))
        {{ $scripts }}
    @endif
</body>
</html>
