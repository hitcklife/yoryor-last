<div>
    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex transition-colors duration-300">
        
        <!-- Left Navigation Sidebar - Full Mode -->
        <x-navigation-sidebar mode="full" />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <!-- Content Area - Add bottom padding on mobile for nav bar -->
            <div class="flex-1 p-4 lg:p-6 pb-20 lg:pb-6">
                <!-- Stories Section -->
                <div class="mb-6">
                    <livewire:dashboard.stories-bar />
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Profile Views Widget -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                    <x-lucide-eye class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Profile Views</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ auth()->user()->profile?->profile_views ?? 0 }}
                                </p>
                                <p class="text-xs text-green-600 dark:text-green-400">
                                    +12% from last week
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Matches Widget -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-pink-100 dark:bg-pink-900 rounded-lg flex items-center justify-center">
                                    <x-lucide-heart class="w-4 h-4 text-pink-600 dark:text-pink-400" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">New Matches</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ \App\Models\Like::where('user_id', auth()->id())->whereHas('likedUser.likes', function($query) { $query->where('liked_user_id', auth()->id()); })->count() }}
                                </p>
                                <p class="text-xs text-green-600 dark:text-green-400">
                                    0 this week
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Widget -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                    <x-lucide-message-circle class="w-4 h-4 text-green-600 dark:text-green-400" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Messages</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    0
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Unread messages
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Verification Widget -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                    <x-lucide-shield-check class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Verification</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    0%
                                </p>
                                <p class="text-xs text-blue-600 dark:text-blue-400">
                                    <a href="{{ route('verification') }}" class="hover:underline">Complete profile</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                    <!-- Discovery Card - Takes 2 columns on XL screens -->
                    <div class="xl:col-span-2">
                        <livewire:dashboard.discovery-grid />
                    </div>
                    
                    <!-- Activity Feed - Takes 1 column on XL screens -->
                    <div class="xl:col-span-1 space-y-6">
                        <livewire:dashboard.activity-sidebar />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Story Viewer Modal -->
    <livewire:dashboard.story-viewer />

    <!-- Mobile Bottom Navigation - Shown only on mobile (below lg breakpoint) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-950 border-t border-gray-200 dark:border-gray-800 z-50 safe-area-pb">
        <div class="grid grid-cols-5 h-16">
            <!-- Dashboard/Home -->
            <a href="{{ route('dashboard') }}"
               class="flex flex-col items-center justify-center space-y-1 transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400' }}">
                <x-lucide-home class="w-6 h-6" :class="request()->routeIs('dashboard') ? 'fill-current' : ''" />
                <span class="text-xs font-medium">Home</span>
            </a>

            <!-- Discover -->
            <a href="{{ route('discover') }}"
               class="flex flex-col items-center justify-center space-y-1 transition-colors duration-200 {{ request()->routeIs('discover') ? 'text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400' }}">
                <x-lucide-search class="w-6 h-6" :class="request()->routeIs('discover') ? 'fill-current' : ''" />
                <span class="text-xs font-medium">Discover</span>
            </a>

            <!-- Matches - Featured center button -->
            <a href="{{ route('matches') }}"
               class="flex flex-col items-center justify-center -mt-6 transition-transform duration-200 hover:scale-110">
                <div class="w-14 h-14 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg {{ request()->routeIs('matches') ? 'ring-4 ring-pink-200 dark:ring-pink-900' : '' }}">
                    <x-lucide-heart class="w-7 h-7 text-white" :class="request()->routeIs('matches') ? 'fill-current' : ''" />
                </div>
                <span class="text-xs font-medium mt-1 {{ request()->routeIs('matches') ? 'text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400' }}">Matches</span>
            </a>

            <!-- Messages -->
            <a href="{{ route('messages') }}"
               class="flex flex-col items-center justify-center space-y-1 transition-colors duration-200 {{ request()->routeIs('messages*') ? 'text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400' }}">
                <div class="relative">
                    <x-lucide-message-circle class="w-6 h-6" :class="request()->routeIs('messages*') ? 'fill-current' : ''" />
                    @if(auth()->check() && auth()->user()->unreadMessagesCount() > 0)
                        <span class="absolute -top-1 -right-2 min-w-[18px] h-[18px] bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold px-1 border-2 border-white dark:border-gray-950">
                            {{ auth()->user()->unreadMessagesCount() > 9 ? '9+' : auth()->user()->unreadMessagesCount() }}
                        </span>
                    @endif
                </div>
                <span class="text-xs font-medium">Messages</span>
            </a>

            <!-- Profile -->
            <a href="{{ route('my-profile') }}"
               class="flex flex-col items-center justify-center space-y-1 transition-colors duration-200 {{ request()->routeIs('my-profile') ? 'text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400' }}">
                @if(auth()->check() && auth()->user()->profilePhoto)
                    <img src="{{ auth()->user()->profilePhoto->thumbnail_url }}"
                         alt="Profile"
                         class="w-7 h-7 rounded-full object-cover {{ request()->routeIs('my-profile') ? 'ring-2 ring-purple-600 dark:ring-purple-400' : 'ring-1 ring-gray-300 dark:ring-gray-600' }}">
                @else
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center {{ request()->routeIs('my-profile') ? 'ring-2 ring-purple-600 dark:ring-purple-400' : '' }}">
                        <x-lucide-user class="w-4 h-4 text-white" />
                    </div>
                @endif
                <span class="text-xs font-medium">Profile</span>
            </a>
        </div>
    </nav>
</div>

<style>
    /* Safe area for iOS devices with notch */
    @supports(padding: max(0px)) {
        .safe-area-pb {
            padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
        }
    }
</style>
