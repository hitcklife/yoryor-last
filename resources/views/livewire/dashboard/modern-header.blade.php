<!-- Modern Dating App Header -->
<header class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            
            <!-- Logo Section -->
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                    <div class="relative">
                        <div class="w-10 h-10 bg-gradient-to-r from-pink-500 via-purple-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </div>
                        <!-- Heartbeat animation -->
                        <div class="absolute inset-0 w-10 h-10 bg-pink-500 rounded-2xl animate-ping opacity-20 group-hover:opacity-30"></div>
                    </div>
                    <span class="text-2xl font-bold bg-gradient-to-r from-pink-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
                        {{ __('dashboard.app_name') }}
                    </span>
                </a>
            </div>

            <!-- Center Navigation -->
            <nav class="hidden md:flex items-center space-x-1">
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center space-x-2 px-4 py-2 rounded-xl font-medium transition-all duration-300 
                          {{ $currentRoute === 'dashboard' ? 'bg-gradient-to-r from-pink-50 to-purple-50 text-purple-600 shadow-sm' : 'text-gray-600 hover:text-purple-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>{{ __('dashboard.discover') }}</span>
                </a>
                
                <a href="{{ route('matches') }}" 
                   class="flex items-center space-x-2 px-4 py-2 rounded-xl font-medium transition-all duration-300
                          {{ $currentRoute === 'matches' ? 'bg-gradient-to-r from-pink-50 to-purple-50 text-purple-600 shadow-sm' : 'text-gray-600 hover:text-purple-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                    <span>{{ __('dashboard.matches') }}</span>
                </a>
                
                <a href="{{ route('messages') }}" 
                   class="flex items-center space-x-2 px-4 py-2 rounded-xl font-medium transition-all duration-300 relative
                          {{ $currentRoute === 'messages' ? 'bg-gradient-to-r from-pink-50 to-purple-50 text-purple-600 shadow-sm' : 'text-gray-600 hover:text-purple-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span>{{ __('dashboard.messages') }}</span>
                    <!-- Unread Badge -->
                    @php $unreadMessagesCount = auth()->user()->unreadMessages()->count(); @endphp
                    @if($unreadMessagesCount > 0)
                        <div class="absolute -top-1 -right-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold shadow-lg animate-pulse">
                            {{ $unreadMessagesCount }}
                        </div>
                    @endif
                </a>
            </nav>

            <!-- Right Section -->
            <div class="flex items-center space-x-4">
                
                <!-- Search Button (Mobile) -->
                <button class="md:hidden p-2 rounded-xl text-gray-600 hover:text-purple-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                
                <!-- Notifications -->
                <div class="relative">
                    <button class="p-2 rounded-xl text-gray-600 hover:text-purple-600 hover:bg-gray-50 transition-all duration-300 group">
                        <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5V12H7a2 2 0 00-2 2v6a2 2 0 002 2h8a2 2 0 002-2v-6z"/>
                        </svg>
                        @if($unreadNotifications > 0)
                            <div class="absolute -top-1 -right-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold shadow-lg">
                                {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                            </div>
                        @endif
                    </button>
                </div>
                
                <!-- Language Switcher -->
                <livewire:components.language-switcher />
                
                <!-- Profile Menu -->
                <div class="relative" x-data="{ profileOpen: false }">
                    <button @click="profileOpen = !profileOpen" 
                            class="flex items-center space-x-3 p-1 rounded-2xl hover:bg-gray-50 transition-all duration-300 group">
                        <div class="relative">
                            @if(auth()->user()->profilePhoto)
                                <img src="{{ auth()->user()->profilePhoto->thumbnail_url }}" 
                                     alt="{{ auth()->user()->name }}"
                                     class="w-10 h-10 rounded-full object-cover border-2 border-transparent group-hover:border-purple-200">
                            @else
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <!-- Online indicator -->
                            <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
                        </div>
                        <div class="hidden lg:block text-left">
                            <p class="font-semibold text-gray-900 text-sm">{{ auth()->user()->profile?->first_name ?? auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ __('dashboard.online_now') }}</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 transition-colors" 
                             :class="{ 'rotate-180': profileOpen }" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Profile Dropdown -->
                    <div x-show="profileOpen" 
                         @click.away="profileOpen = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
                         x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="transform opacity-0 scale-95 translate-y-2"
                         class="absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-2xl border border-gray-100 py-2 z-50">
                        
                        <!-- Profile Header -->
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                @if(auth()->user()->profilePhoto)
                                    <img src="{{ auth()->user()->profilePhoto->thumbnail_url }}" 
                                         alt="{{ auth()->user()->name }}"
                                         class="w-12 h-12 rounded-full object-cover">
                                @else
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-gray-900">{{ auth()->user()->profile?->first_name ?? auth()->user()->name }}</p>
                                    <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Items -->
                        <div class="py-2">
                            <a href="{{ route('my-profile') }}" 
                               class="flex items-center space-x-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                My Profile
                            </a>
                            
                            <a href="{{ route('settings') }}" 
                               class="flex items-center space-x-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Settings
                            </a>
                            
                            <a href="{{ route('profile.enhance') }}" 
                               class="flex items-center space-x-3 px-4 py-3 text-sm text-purple-600 hover:bg-purple-50 transition-colors">
                                <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Enhance Profile
                            </a>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-gray-100 my-2"></div>

                        <!-- Logout -->
                        <div class="py-2">
                            <button wire:click="logout" 
                                    class="w-full flex items-center space-x-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                {{ __('dashboard.logout') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
