@props([
    'activeRoute' => null
])

<nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-40 lg:hidden">
    <div class="grid grid-cols-5 h-16">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" 
           class="flex flex-col items-center justify-center space-y-1 px-2 py-2 text-xs font-medium transition-colors
                  {{ request()->routeIs('dashboard') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            <i data-lucide="home" class="w-5 h-5"></i>
            <span>Home</span>
        </a>
        
        <!-- Matches -->
        <a href="{{ route('matches') }}" 
           class="flex flex-col items-center justify-center space-y-1 px-2 py-2 text-xs font-medium transition-colors
                  {{ request()->routeIs('matches') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            <div class="relative">
                <i data-lucide="heart" class="w-5 h-5"></i>
                @if(auth()->user() && auth()->user()->unreadMatchesCount() > 0)
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                @endif
            </div>
            <span>Matches</span>
        </a>
        
        <!-- Messages -->
        <a href="{{ route('messages') }}" 
           class="flex flex-col items-center justify-center space-y-1 px-2 py-2 text-xs font-medium transition-colors
                  {{ request()->routeIs('messages*') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            <div class="relative">
                <i data-lucide="message-circle" class="w-5 h-5"></i>
                @if(auth()->user() && auth()->user()->unreadMessagesCount() > 0)
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                @endif
            </div>
            <span>Messages</span>
        </a>
        
        <!-- Search -->
        <a href="{{ route('search') }}" 
           class="flex flex-col items-center justify-center space-y-1 px-2 py-2 text-xs font-medium transition-colors
                  {{ request()->routeIs('search') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            <i data-lucide="search" class="w-5 h-5"></i>
            <span>Search</span>
        </a>
        
        <!-- Profile -->
        <a href="{{ route('my-profile') }}" 
           class="flex flex-col items-center justify-center space-y-1 px-2 py-2 text-xs font-medium transition-colors
                  {{ request()->routeIs('my-profile') ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            <div class="w-5 h-5 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                @if(auth()->user() && auth()->user()->profile_photo_url)
                    <img src="{{ auth()->user()->profile_photo_url }}" 
                         alt="Profile" 
                         class="w-5 h-5 rounded-full object-cover">
                @else
                    <i data-lucide="user" class="w-3 h-3"></i>
                @endif
            </div>
            <span>Profile</span>
        </a>
    </div>
</nav>

@push('scripts')
<script>
    // Initialize Lucide icons for mobile navigation
    document.addEventListener('DOMContentLoaded', function() {
        if (window.lucide && window.lucide.createIcons) {
            window.lucide.createIcons({ icons: window.lucide.icons });
        }
    });
</script>
@endpush
