<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'YorYor') }} - Modern Dating App</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900" rel="stylesheet" />
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#8B5CF6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="YorYor">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <!-- Prevent flash of wrong theme -->
    <script>
        (function() {
            const theme = document.cookie.split('; ').find(row => row.startsWith('theme='))?.split('=')[1] || 
                         localStorage.getItem('theme') || 'system';
            const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-pink-50 via-white to-purple-50 dark:bg-gray-900 transition-colors duration-300">
    
    <!-- Main App Content -->
    <div class="min-h-screen">
        {{ $slot }}
    </div>
    
    <!-- Mobile Navigation -->
    <x-navigation.mobile-nav />
    
    <!-- Quick Actions -->
    <x-navigation.quick-actions />
    
    <!-- Toast Notifications Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
    
    <!-- Alpine.js is loaded via Vite build -->
    
    @livewireScripts
    
    <!-- Minimal JavaScript -->
    <script>
        console.log('Dashboard loaded successfully');
        
        // Basic error handling
        window.addEventListener('error', function(e) {
            console.error('JavaScript error:', e.error);
        });
    </script>
    
    <!-- Analytics and Performance -->
    @stack('scripts')
</body>
</html>
