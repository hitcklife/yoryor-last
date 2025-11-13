@props(['mode' => 'full']) {{-- 'full' for icon+text, 'compact' for icon-only --}}

<!-- Desktop Sidebar - Hidden on mobile (below lg breakpoint) -->
<div class="hidden lg:flex {{ $mode === 'compact' ? 'w-16' : 'w-64' }} bg-white dark:bg-gray-950 border-r border-gray-200 dark:border-gray-800 h-screen flex-shrink-0 transition-colors duration-300 flex-col">
    <div class="{{ $mode === 'compact' ? 'p-3' : 'p-6' }} flex-1 flex flex-col">
        <!-- Logo/Brand and Theme Switcher -->
        <div class="flex items-center justify-between {{ $mode === 'compact' ? 'mb-6' : 'mb-8' }}">
            <div class="flex items-center {{ $mode === 'compact' ? 'justify-center' : 'space-x-3' }}">
                <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center">
                    <x-lucide-users class="w-5 h-5 text-white" />
                </div>
                @if($mode === 'full')
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white transition-colors duration-300">YorYor</h1>
                        <p class="text-xs text-gray-600 dark:text-gray-400 transition-colors duration-300">Dating App</p>
                    </div>
                @endif
            </div>
            
            <!-- Theme Switcher - Moved to top -->
            <div class="{{ $mode === 'compact' ? 'ml-2' : '' }}">
                <livewire:theme-switcher />
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="space-y-2 flex-1">
            <a href="{{ route('dashboard') }}" 
               class="flex items-center {{ $mode === 'compact' ? 'justify-center px-2 py-3' : 'space-x-3 px-4 py-3' }} rounded-xl text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-purple-500/10 hover:text-purple-700 dark:hover:text-purple-400 transition-colors duration-300 {{ request()->routeIs('dashboard') ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400' : '' }}">
                <x-lucide-home class="w-5 h-5" />
                @if($mode === 'full')
                    <span class="font-medium transition-colors duration-300">Dashboard</span>
                @endif
            </a>

            <a href="{{ route('discover') }}" 
               class="flex items-center {{ $mode === 'compact' ? 'justify-center px-2 py-3' : 'space-x-3 px-4 py-3' }} rounded-xl text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-purple-500/10 hover:text-purple-700 dark:hover:text-purple-400 transition-colors duration-300 {{ request()->routeIs('discover') ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400' : '' }}">
                <x-lucide-search class="w-5 h-5" />
                @if($mode === 'full')
                    <span class="font-medium transition-colors duration-300">Discover</span>
                @endif
            </a>

            <a href="{{ route('matches') }}" 
               class="flex items-center {{ $mode === 'compact' ? 'justify-center px-2 py-3' : 'space-x-3 px-4 py-3' }} rounded-xl text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-purple-500/10 hover:text-purple-700 dark:hover:text-purple-400 transition-colors duration-300 {{ request()->routeIs('matches') ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400' : '' }}">
                <x-lucide-heart class="w-5 h-5" />
                @if($mode === 'full')
                    <span class="font-medium transition-colors duration-300">Matches</span>
                @endif
            </a>

            <a href="{{ route('messages') }}"
               class="flex items-center {{ $mode === 'compact' ? 'justify-center px-2 py-3' : 'space-x-3 px-4 py-3' }} rounded-xl text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-purple-500/10 hover:text-purple-700 dark:hover:text-purple-400 transition-colors duration-300 {{ request()->routeIs('messages*') ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400' : '' }}">
                <div class="relative">
                    <x-lucide-message-circle class="w-5 h-5" />
                    @if(auth()->check() && auth()->user()->unreadMessagesCount() > 0)
                        <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-green-500 text-white text-xs rounded-full flex items-center justify-center font-bold px-1 border-2 border-white dark:border-gray-950">
                            {{ auth()->user()->unreadMessagesCount() > 9 ? '9' : auth()->user()->unreadMessagesCount() }}
                        </span>
                    @endif
                </div>
                @if($mode === 'full')
                    <span class="font-medium transition-colors duration-300">Messages</span>
                @endif
            </a>

            <a href="{{ route('notifications') }}" 
               class="flex items-center {{ $mode === 'compact' ? 'justify-center px-2 py-3' : 'space-x-3 px-4 py-3' }} rounded-xl text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-purple-500/10 hover:text-purple-700 dark:hover:text-purple-400 transition-colors duration-300 {{ request()->routeIs('notifications*') ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400' : '' }}">
                <div class="relative">
                    <x-lucide-bell class="w-5 h-5" />
                    @if(auth()->check() && auth()->user()->unreadNotifications()->count() > 0)
                        <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-green-500 text-white text-xs rounded-full flex items-center justify-center font-bold px-1 border-2 border-white dark:border-gray-950">
                            {{ auth()->user()->unreadNotifications()->count() > 9 ? '9' : auth()->user()->unreadNotifications()->count() }}
                        </span>
                    @endif
                </div>
                @if($mode === 'full')
                    <span class="font-medium transition-colors duration-300">Notifications</span>
                @endif
            </a>


            <a href="{{ route('settings') }}" 
               class="flex items-center {{ $mode === 'compact' ? 'justify-center px-2 py-3' : 'space-x-3 px-4 py-3' }} rounded-xl text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-purple-500/10 hover:text-purple-700 dark:hover:text-purple-400 transition-colors duration-300 {{ request()->routeIs('settings*') ? 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400' : '' }}">
                <x-lucide-settings class="w-5 h-5" />
                @if($mode === 'full')
                    <span class="font-medium transition-colors duration-300">Settings</span>
                @endif
            </a>
        </nav>

        <!-- Bottom Section - Profile -->
        <div class="mt-auto">
            <!-- Clickable Profile Card -->
            <a href="{{ route('my-profile') }}" 
               class="{{ $mode === 'compact' ? 'mt-6 p-2' : 'mt-8 p-4' }} bg-gradient-to-br from-purple-50 to-pink-50 dark:bg-gray-800 rounded-xl border border-purple-100 dark:border-gray-700 transition-all duration-300 hover:from-purple-100 hover:to-pink-100 dark:hover:bg-gray-750 hover:shadow-md hover:scale-[1.02] cursor-pointer block {{ request()->routeIs('my-profile') ? 'ring-2 ring-purple-500 dark:ring-purple-400' : '' }}">
                <div class="flex items-center {{ $mode === 'compact' ? 'justify-center' : 'space-x-3' }}">
                    @if(auth()->check() && auth()->user()->profilePhoto)
                        <img src="{{ auth()->user()->profilePhoto->medium_url }}" 
                             alt="{{ auth()->user()->name }}"
                             class="{{ $mode === 'compact' ? 'w-10 h-10' : 'w-12 h-12' }} rounded-full object-cover border-2 border-white shadow-sm">
                    @else
                        <div class="{{ $mode === 'compact' ? 'w-10 h-10' : 'w-12 h-12' }} bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                            <span class="text-white font-bold {{ $mode === 'compact' ? 'text-sm' : 'text-lg' }}">{{ substr(auth()->user()?->name ?? 'U', 0, 1) }}</span>
                        </div>
                    @endif
                    @if($mode === 'full')
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate transition-colors duration-300">{{ auth()->user()?->profile?->first_name ?? auth()->user()?->name }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-300 transition-colors duration-300">View Profile</p>
                        </div>
                        <div class="flex-shrink-0">
                            <x-lucide-chevron-right class="w-4 h-4 text-gray-400 dark:text-gray-500 transition-colors duration-300" />
                        </div>
                    @endif
                </div>
            </a>
        </div>
    </div>
</div>
