@props([
    'currentStep' => 1,
    'totalSteps' => 7,
    'title' => 'Join YorYor - Find Your Perfect Match',
    'stepData' => null,
])

@php
$steps = [
    1 => ['label' => 'Verify Phone', 'description' => 'Phone verification'],
    2 => ['label' => 'Basic Info', 'description' => 'Tell us about yourself'],
    3 => ['label' => 'Location', 'description' => 'Where are you based?'],
    4 => ['label' => 'Photos', 'description' => 'Show your best self'],
    5 => ['label' => 'Profile', 'description' => 'Share your story'],
    6 => ['label' => 'Preferences', 'description' => 'What matters to you?'],
    7 => ['label' => 'Review', 'description' => 'Complete your profile'],
];

$currentStepData = $steps[$currentStep] ?? $steps[1];
$progressPercentage = round(($currentStep / $totalSteps) * 100);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="registrationApp()">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $title }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="min-h-screen gradient-background font-sans antialiased m-0 p-0 overflow-x-hidden">
    <div class="min-h-screen w-full flex m-0 p-0">
        <!-- Left Panel - Hero Section (Hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
            <!-- Dynamic Background -->
            <div class="absolute inset-0 gradient-primary opacity-90"></div>
            
            <!-- Background Image -->
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-30" 
                 style="background-image: url('/assets/images/pexels-asadphoto-169196.jpg');">
            </div>
            
            <!-- Animated Pattern Overlay -->
            <div class="absolute inset-0 opacity-10" 
                 style="background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.3) 1px, transparent 0); background-size: 24px 24px;">
            </div>
            
            <!-- Content -->
            <div class="relative z-10 flex flex-col h-full px-12 py-16 text-white">
                <!-- Logo Header - Top -->
                <div class="mb-8 animate-fade-in">
                    <div class="flex items-center space-x-4 mb-6">
                        <x-ui.glass-card padding="sm" class="w-16 h-16 flex items-center justify-center">
                            <svg class="w-10 h-10 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </x-ui.glass-card>
                        <h1 class="text-4xl font-bold drop-shadow-lg">YorYor</h1>
                    </div>
                    <h2 class="text-2xl font-semibold mb-4 drop-shadow-md">Find Your Perfect Match</h2>
                    <p class="text-lg opacity-90 max-w-md drop-shadow-sm">
                        Join thousands of people who have found love through YorYor. Your journey to meaningful connections starts here.
                    </p>
                </div>

                <!-- Center Content - Progress Section -->
                <div class="flex-1 flex flex-col justify-center">
                    <x-ui.glass-card padding="lg" class="max-w-md mb-8 animate-slide-up">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold drop-shadow-md">{{ $currentStepData['label'] }}</h3>
                            <p class="text-white/80 text-sm drop-shadow-sm">{{ $currentStepData['description'] }}</p>
                        </div>
                        <x-ui.progress-ring 
                            :progress="$progressPercentage" 
                            size="base" 
                            color="accent"
                            class="text-white"
                        />
                    </div>
                    
                    <!-- Step Progress Bar -->
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-2 text-white/80">
                            <span>Step {{ $currentStep }} of {{ $totalSteps }}</span>
                            <span>{{ $progressPercentage }}%</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
                            <div class="bg-gradient-to-r from-white/80 to-white h-2 rounded-full transition-all duration-500 ease-out shadow-sm" 
                                 style="width: {{ $progressPercentage }}%"></div>
                        </div>
                    </div>
                </x-ui.glass-card>
                </div>
                
                <!-- Features - Bottom -->
                <div class="space-y-4 max-w-sm animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-white/90 drop-shadow-sm">Smart compatibility matching</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-white/90 drop-shadow-sm">Privacy & security first</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-white/90 drop-shadow-sm">Real-time messaging</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Registration Form -->
        <div class="w-full lg:w-1/2 flex flex-col min-h-screen m-0 p-0 gradient-background">
            <!-- Mobile Header -->
            <div class="lg:hidden p-6 bg-transparent">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 gradient-primary rounded-xl flex items-center justify-center shadow-md">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </div>
                        <span class="text-2xl font-bold text-gradient-primary">YorYor</span>
                    </div>
                    <x-ui.progress-ring 
                        :progress="$progressPercentage" 
                        size="sm" 
                        color="primary"
                    />
                </div>
            </div>

            <!-- Mobile Progress Bar -->
            <div class="lg:hidden px-6 py-4 bg-transparent">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-600">{{ $currentStepData['label'] }}</span>
                    <span class="text-sm text-gray-500">{{ $progressPercentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                    <div class="gradient-primary h-2 rounded-full transition-all duration-500" 
                         style="width: {{ $progressPercentage }}%"></div>
                </div>
            </div>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto">
                <div class="max-w-2xl mx-auto px-6 py-8 lg:px-12 lg:py-12">
                    <div class="animate-fade-in">
                        {{ $slot }}
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="p-6 lg:px-12 bg-transparent">
                <div class="max-w-2xl mx-auto">
                    <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                        <p class="text-sm text-gray-600 text-center sm:text-left">
                            Already have an account? 
                            <a href="{{ route('login') }}" class="text-pink-600 hover:text-pink-700 font-medium transition-colors">Sign in</a>
                        </p>
                        <div class="flex items-center space-x-6 text-xs text-gray-500">
                            <a href="{{ route('privacy') }}" class="hover:text-gray-700 transition-colors">Privacy</a>
                            <a href="{{ route('terms') }}" class="hover:text-gray-700 transition-colors">Terms</a>
                            <a href="{{ route('help') }}" class="hover:text-gray-700 transition-colors">Help</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function registrationApp() {
            return {
                currentStep: {{ $currentStep }},
                totalSteps: {{ $totalSteps }},
                isLoading: false,
                
                init() {
                    console.log('Registration app initialized', {
                        currentStep: this.currentStep,
                        totalSteps: this.totalSteps
                    });
                },
                
                showLoading() {
                    this.isLoading = true;
                },
                
                hideLoading() {
                    this.isLoading = false;
                },
                
                nextStep() {
                    if (this.currentStep < this.totalSteps) {
                        this.currentStep++;
                    }
                },
                
                previousStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                    }
                }
            }
        }
    </script>
</body>
</html>