<div>
    <!-- Desktop Sidebar -->
    <div class="hidden md:flex {{ $hideText ? 'w-16 lg:w-20' : 'w-64' }} bg-white border-r border-gray-200 flex-col fixed left-0 top-0 h-full z-40">
        
        <!-- Logo -->
        <div class="p-6 h-20 flex items-center">
            <a href="{{ route('dashboard') }}" class="block">
                @if($hideText)
                    <div class="w-10 h-10 bg-gradient-to-r from-pink-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <span class="text-white font-bold text-xl">Y</span>
                    </div>
                @else
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-pink-500 to-purple-600 bg-clip-text text-transparent">
                        YorYor
                    </h1>
                @endif
            </a>
        </div>
        
        <!-- Navigation Icons -->
        <nav class="flex-1 {{ $hideText ? 'flex flex-col items-center space-y-4 lg:space-y-6 py-4' : 'flex flex-col space-y-2 p-4' }}">
            <!-- Home -->
            <a href="{{ route('dashboard') }}" 
               class="group flex items-center p-2 lg:p-3 rounded-lg transition-all {{ $hideText ? '' : '' }} {{ request()->routeIs('dashboard') ? 'bg-purple-50' : 'hover:bg-gray-100' }}">
                <svg class="w-6 h-6 lg:w-7 lg:h-7 {{ request()->routeIs('dashboard') ? 'text-purple-600' : 'text-gray-700' }} group-hover:text-purple-600 transition-colors" 
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                @unless($hideText)
                    <span class="ml-3 font-medium {{ request()->routeIs('dashboard') ? 'text-purple-600' : 'text-gray-700' }}">
                        Home
                    </span>
                @endunless
            </a>
            
            <!-- Search -->
            <a href="#" 
               class="group flex items-center p-2 lg:p-3 rounded-lg hover:bg-gray-100 transition-all">
                <svg class="w-6 h-6 lg:w-7 lg:h-7 text-gray-700 group-hover:text-purple-600 transition-colors" 
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @unless($hideText)
                    <span class="ml-3 font-medium text-gray-700">Search</span>
                @endunless
            </a>
            
            <!-- Messages -->
            <a href="{{ route('messages') }}" 
               class="group flex items-center p-2 lg:p-3 rounded-lg {{ request()->routeIs('messages*') ? 'bg-purple-50' : 'hover:bg-gray-100' }} transition-all relative">
                <svg class="w-6 h-6 lg:w-7 lg:h-7 {{ request()->routeIs('messages*') ? 'text-purple-600' : 'text-gray-700' }} group-hover:text-purple-600 transition-colors" 
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z"/>
                </svg>
                @unless($hideText)
                    <span class="ml-3 font-medium {{ request()->routeIs('messages*') ? 'text-purple-600' : 'text-gray-700' }}">Messages</span>
                @endunless
                @if($unreadMessages > 0)
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
                        {{ $unreadMessages }}
                    </span>
                @endif
            </a>
            
            <!-- Likes/Notifications -->
            <a href="#" 
               class="group flex items-center p-2 lg:p-3 rounded-lg hover:bg-gray-100 transition-all relative">
                <svg class="w-6 h-6 lg:w-7 lg:h-7 text-gray-700 group-hover:text-purple-600 transition-colors" 
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.5c0-2.485-2.239-4.5-5-4.5-1.719 0-3.237.871-4.136 2.197C11.719 4.871 9.719 4 8 4 5.239 4 3 6.015 3 8.5c0 .737.185 1.432.512 2.043C4.488 12.506 6.439 15.5 12 20c5.561-4.5 7.512-7.494 8.488-9.457A4.467 4.467 0 0021 8.5z"/>
                </svg>
                @unless($hideText)
                    <span class="ml-3 font-medium text-gray-700">Notifications</span>
                @endunless
                @if($totalLikes > 0)
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-pink-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
                        {{ $totalLikes > 99 ? '99+' : $totalLikes }}
                    </span>
                @endif
            </a>
            
            <!-- Profile -->
            <a href="{{ route('my-profile') ?? route('user.profile.show', auth()->id()) }}" 
               class="group flex items-center p-2 lg:p-3 rounded-lg hover:bg-gray-100 transition-all">
                @if(auth()->user()->profilePhoto?->thumbnail_url)
                    <img src="{{ auth()->user()->profilePhoto->thumbnail_url }}" 
                         alt="Profile" 
                         class="w-6 h-6 lg:w-7 lg:h-7 rounded-full object-cover ring-2 ring-gray-200">
                @else
                    <div class="w-6 h-6 lg:w-7 lg:h-7 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-xs">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                    </div>
                @endif
                @unless($hideText)
                    <span class="ml-3 font-medium text-gray-700">Profile</span>
                @endunless
            </a>
        </nav>
        
        <!-- More Options -->
        @unless($hideText)
            <div class="mt-auto p-4">
                <button class="group flex items-center p-2 lg:p-3 rounded-lg hover:bg-gray-100 transition-all w-full">
                    <svg class="w-6 h-6 lg:w-7 lg:h-7 text-gray-700 group-hover:text-purple-600 transition-colors" 
                         fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                    <span class="ml-3 font-medium text-gray-700">More</span>
                </button>
            </div>
        @else
            <div class="p-4">
                <button class="p-2 lg:p-3 rounded-lg hover:bg-gray-100 transition-all group">
                    <svg class="w-6 h-6 lg:w-7 lg:h-7 text-gray-700 group-hover:text-purple-600 transition-colors" 
                         fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                </button>
            </div>
        @endunless
    </div>
    
    <!-- Mobile Bottom Navigation -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
        <div class="flex justify-around items-center py-2">
            <!-- Home -->
            <a href="{{ route('dashboard') }}" class="p-3 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-purple-50' : 'hover:bg-gray-50' }} transition-all">
                <svg class="w-6 h-6 {{ request()->routeIs('dashboard') ? 'text-purple-600' : 'text-gray-700' }}" 
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </a>
            
            <!-- Search -->
            <a href="#" class="p-3 rounded-lg hover:bg-gray-50 transition-all">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </a>
            
            <!-- Messages -->
            <a href="{{ route('messages') }}" class="p-3 rounded-lg {{ request()->routeIs('messages*') ? 'bg-purple-50' : 'hover:bg-gray-50' }} transition-all relative">
                <svg class="w-6 h-6 {{ request()->routeIs('messages*') ? 'text-purple-600' : 'text-gray-700' }}" 
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z"/>
                </svg>
                @if($unreadMessages > 0)
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
                        {{ $unreadMessages }}
                    </span>
                @endif
            </a>
            
            <!-- Likes -->
            <a href="#" class="p-3 rounded-lg hover:bg-gray-50 transition-all relative">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.5c0-2.485-2.239-4.5-5-4.5-1.719 0-3.237.871-4.136 2.197C11.719 4.871 9.719 4 8 4 5.239 4 3 6.015 3 8.5c0 .737.185 1.432.512 2.043C4.488 12.506 6.439 15.5 12 20c5.561-4.5 7.512-7.494 8.488-9.457A4.467 4.467 0 0021 8.5z"/>
                </svg>
                @if($totalLikes > 0)
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-pink-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
                        {{ $totalLikes > 99 ? '99+' : $totalLikes }}
                    </span>
                @endif
            </a>
            
            <!-- Profile -->
            <a href="{{ route('my-profile') ?? route('user.profile.show', auth()->id()) }}" class="p-3 rounded-lg hover:bg-gray-50 transition-all">
                @if(auth()->user()->profilePhoto?->thumbnail_url)
                    <img src="{{ auth()->user()->profilePhoto->thumbnail_url }}" 
                         alt="Profile" 
                         class="w-6 h-6 rounded-full object-cover ring-2 ring-gray-200">
                @else
                    <div class="w-6 h-6 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-xs">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                    </div>
                @endif
            </a>
        </div>
    </div>
</div>