@props([
    'position' => 'bottom-right'
])

@php
$positionClasses = [
    'bottom-right' => 'bottom-4 right-4',
    'bottom-left' => 'bottom-4 left-4',
    'top-right' => 'top-4 right-4',
    'top-left' => 'top-4 left-4'
];

$positionClass = $positionClasses[$position] ?? $positionClasses['bottom-right'];
@endphp

<div class="fixed {{ $positionClass }} z-50" x-data="{ open: false }">
    <!-- Floating Action Button -->
    <button @click="open = !open"
            class="w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center group">
        <div class="w-6 h-6 transition-transform duration-200" :class="{ 'rotate-45': open }">
            <x-lucide-plus class="w-full h-full" />
        </div>
    </button>
    
    <!-- Quick Actions Menu -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute bottom-16 right-0 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-2 min-w-48"
         @click.away="open = false">
        
        <!-- Search -->
        <a href="{{ route('search') }}" 
           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <x-lucide-search class="w-4 h-4 mr-3 text-gray-500" />
            Find New Matches
        </a>
        
        <!-- Video Call -->
        <a href="{{ route('video-call') }}" 
           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <x-lucide-video class="w-4 h-4 mr-3 text-gray-500" />
            Start Video Call
        </a>
        
        <!-- Emergency -->
        <a href="{{ route('emergency') }}" 
           class="flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
            <x-lucide-shield-alert class="w-4 h-4 mr-3" />
            Emergency
        </a>
        
        <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
        
        <!-- Settings -->
        <a href="{{ route('settings') }}" 
           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <x-lucide-settings class="w-4 h-4 mr-3 text-gray-500" />
            Settings
        </a>
        
        <!-- Notifications -->
        <a href="{{ route('notifications') }}" 
           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <div class="relative mr-3">
                <x-lucide-bell class="w-4 h-4 text-gray-500" />
                @if(auth()->user() && auth()->user()->unreadNotificationsCount() > 0)
                    <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                @endif
            </div>
            Notifications
        </a>
        
        <!-- Insights -->
        <a href="{{ route('insights') }}" 
           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <x-lucide-bar-chart-3 class="w-4 h-4 mr-3 text-gray-500" />
            Insights
        </a>
    </div>
</div>

