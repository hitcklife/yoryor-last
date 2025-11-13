<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $title ?? 'Complete Your Profile - YorYor' }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* Salient-inspired Bluish Color Scheme - SAME AS /START */
        :root {
            --primary: #0f172a;     /* Deep slate */
            --secondary: #1e40af;   /* Rich blue */
            --accent: #60a5fa;      /* Light blue */
            --neutral: #64748b;     /* Slate gray */
            --primary-50: #f8fafc;
            --primary-100: #f1f5f9;
            --primary-500: #0f172a;
            --primary-600: #020617;
            --primary-700: #020617;
            --blue-50: #eff6ff;
            --blue-100: #dbeafe;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
            --blue-900: #1e3a8a;
        }
        
        .bg-primary { background-color: var(--primary); }
        .bg-secondary { background-color: var(--secondary); }
        .bg-accent { background-color: var(--accent); }
        .text-primary { color: var(--primary); }
        .text-secondary { color: var(--secondary); }
        .border-primary { border-color: var(--primary); }
        .hover\:bg-primary:hover { background-color: var(--primary); }
        .focus\:ring-primary:focus { --tw-ring-color: var(--primary); }
        
        /* Salient-style gradients */
        .gradient-primary {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--accent) 100%);
        }
        
        .gradient-slate {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        }
        
        /* Enhanced Liquid Glass Effects - Salient Style */
        .glass-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(96, 165, 250, 0.2);
            box-shadow: 
                0 20px 25px -5px rgba(0, 0, 0, 0.1),
                0 10px 10px -5px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        
        .glass-logo {
            background: rgba(96, 165, 250, 0.1);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(96, 165, 250, 0.3);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        .glass-stats {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(96, 165, 250, 0.15);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }
        
        /* Salient-style text effects */
        .text-shadow {
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        }
        
        .text-shadow-lg {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.6);
        }
        
        /* Salient-style form inputs */
        .input-salient {
            background: rgba(248, 250, 252, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.3);
            transition: all 0.2s ease;
        }
        
        .input-salient:focus {
            background: rgba(255, 255, 255, 1);
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
        }
        
        /* Salient-style buttons */
        .btn-salient {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--accent) 100%);
            border: 0;
            box-shadow: 0 4px 14px 0 rgba(30, 64, 175, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-salient:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px 0 rgba(30, 64, 175, 0.4);
        }
    </style>
</head>
<body class="min-h-screen gradient-background font-sans antialiased m-0 p-0 overflow-x-hidden">
    <div class="min-h-screen w-full flex m-0 p-0">
        <!-- Left Panel - Hero Image & Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden gradient-slate">
            <!-- Background Image -->
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-30" 
                 style="background-image: url('/assets/images/538664-married-couple.jpg');">
            </div>
            
            <!-- Reduced Overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-slate-900/40 via-blue-900/20 to-slate-800/30"></div>
            
            <!-- Subtle Pattern Overlay -->
            <div class="absolute inset-0 opacity-5" 
                 style="background-image: radial-gradient(circle at 1px 1px, rgba(96, 165, 250, 0.3) 1px, transparent 0); background-size: 20px 20px;">
            </div>
            
            <!-- Content -->
            <div class="relative z-10 flex flex-col h-full px-12 py-16 text-white">
                <!-- Logo Header - Top -->
                <div class="mb-8 animate-fade-in">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 glass-logo rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold text-shadow-lg">YorYor</h1>
                    </div>
                </div>

                <!-- Center Content - Main Glass Card -->
                <div class="flex-1 flex flex-col justify-center">
                    <div class="max-w-md glass-card rounded-3xl p-8">
                    <h2 class="text-4xl font-bold mb-6 leading-tight text-shadow-lg">
                        Complete your profile
                    </h2>
                    <p class="text-xl text-white/90 mb-8 leading-relaxed text-shadow">
                        You're almost there! Complete your profile to start finding meaningful connections.
                    </p>
                    
                    <!-- Stats with Glass Effect -->
                    <div class="grid grid-cols-3 gap-4 mb-8">
                        <div class="glass-stats rounded-2xl p-4 text-center">
                            <div class="text-2xl font-bold text-accent text-shadow">10k+</div>
                            <div class="text-sm text-white/80 text-shadow">Active Users</div>
                        </div>
                        <div class="glass-stats rounded-2xl p-4 text-center">
                            <div class="text-2xl font-bold text-accent text-shadow">95%</div>
                            <div class="text-sm text-white/80 text-shadow">Success Rate</div>
                        </div>
                        <div class="glass-stats rounded-2xl p-4 text-center">
                            <div class="text-2xl font-bold text-accent text-shadow">24/7</div>
                            <div class="text-sm text-white/80 text-shadow">Support</div>
                        </div>
                    </div>
                    
                    <!-- Progress Bar (Desktop Only) -->
                    @isset($currentStep)
                    <div class="glass-stats rounded-2xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-white/90 text-shadow">Step {{ $currentStep }} of {{ $totalSteps ?? 9 }}</span>
                            <span class="text-sm text-white/70 text-shadow">{{ round(($currentStep / ($totalSteps ?? 9)) * 100) }}%</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-2">
                            <div class="bg-gradient-to-r from-white/70 to-white/90 h-2 rounded-full transition-all duration-500" style="width: {{ round(($currentStep / ($totalSteps ?? 9)) * 100) }}%"></div>
                        </div>
                    </div>
                    @endisset
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Onboarding Form -->
        <div class="w-full lg:w-1/2 flex flex-col gradient-background m-0 p-0">
            <!-- Mobile Logo -->
            <div class="lg:hidden mb-8 text-center">
                <div class="flex items-center justify-center space-x-3">
                    <div class="w-10 h-10 gradient-primary rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">YorYor</span>
                </div>
            </div>

            <!-- Progress moved to bottom of left panel -->

            <!-- Main Content -->
            <div class="flex-1 px-6 py-4 lg:px-12 lg:py-8">
                {{ $slot }}
            </div>

            <!-- Footer - NO SIGN IN LINK FOR AUTHENTICATED USERS -->
            <div class="px-6 py-4 lg:px-12 bg-transparent">
                <div class="flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
                    <p class="text-sm text-gray-600 text-center sm:text-left">
                        Welcome to YorYor! You're almost done.
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
    
    <!-- Alpine.js for dark mode detection -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>