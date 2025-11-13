<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $title ?? 'Join YorYor - Find Your Perfect Match' }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-pink-50 via-purple-50 to-indigo-50 antialiased min-h-screen font-sans">
    <div class="min-h-screen flex">
        <!-- Left Panel - Brand/Image -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-pink-500 via-purple-600 to-indigo-600 relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-20">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><circle cx="7" cy="7" r="7"/><circle cx="53" cy="7" r="7"/><circle cx="7" cy="53" r="7"/><circle cx="53" cy="53" r="7"/></g></g></svg>');"></div>
            </div>
            
            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center items-center text-white p-12 text-center">
                <!-- Logo -->
                <div class="flex items-center space-x-4 mb-8">
                    <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold">YorYor</h1>
                </div>
                
                <!-- Tagline -->
                <h2 class="text-2xl font-semibold mb-4">Find Your Perfect Match</h2>
                <p class="text-lg opacity-90 max-w-md mb-8">
                    Join thousands of people who have found love through YorYor. Your journey to meaningful connections starts here.
                </p>
                
                <!-- Features -->
                <div class="space-y-4 max-w-sm">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-white/90">Smart matching algorithm</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-white/90">Safe and verified profiles</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-white/90">Real-time messaging</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Registration Form -->
        <div class="w-full lg:w-1/2 flex flex-col">
            <!-- Header -->
            <div class="p-6 lg:hidden">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-pink-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent">YorYor</span>
                </div>
            </div>

            <!-- Progress Bar -->
            @isset($currentStep)
            <div class="px-6 pb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600">Step {{ $currentStep }} of {{ $totalSteps ?? 7 }}</span>
                    <span class="text-sm text-gray-500">{{ round(($currentStep / ($totalSteps ?? 7)) * 100) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-pink-500 to-purple-600 h-2 rounded-full transition-all duration-300" style="width: {{ round(($currentStep / ($totalSteps ?? 7)) * 100) }}%"></div>
                </div>
            </div>
            @endisset

            <!-- Main Content -->
            <div class="flex-1 px-6 py-4 lg:px-12 lg:py-8">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 lg:px-12 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
                    <p class="text-sm text-gray-600 text-center sm:text-left">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="text-pink-600 hover:text-pink-700 font-medium">Sign in</a>
                    </p>
                    <div class="flex items-center space-x-4 text-xs text-gray-500">
                        <a href="{{ route('privacy') }}" class="hover:text-gray-700">Privacy</a>
                        <a href="{{ route('terms') }}" class="hover:text-gray-700">Terms</a>
                        <a href="{{ route('help') }}" class="hover:text-gray-700">Help</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>