<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

    @vite(['resources/css/app.css', 'resources/css/components.css', 'resources/css/scrollbar.css', 'resources/js/app.js', 'resources/js/landing.js'])

</head>
<body class="bg-white antialiased transition-colors duration-300">
    <!-- Header -->
    @livewire('components.header')

    <!-- Main Content -->
    <main class="pt-16 relative">
        <!-- Subtle background pattern -->
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
    @livewire('components.footer')

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

</body>
</html>
