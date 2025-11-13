<!-- Instagram Style Sidebar with Text -->
<div>
    <!-- Desktop & Tablet Sidebar (left side) -->
    <div class="hidden md:block fixed left-0 top-0 h-full bg-white border-r border-gray-200 transition-all duration-300 z-40 w-64">
        
        <!-- Logo / Brand -->
        <div class="p-6">
            <a href="/" class="block">
                <h1 class="text-2xl font-bold bg-gradient-to-r from-pink-500 to-purple-600 bg-clip-text text-transparent">
                    YorYor
                </h1>
            </a>
        </div>
        
        <!-- Navigation Items -->
        <nav class="flex-1 flex flex-col space-y-2 p-4">
            <!-- Home -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center p-2 lg:p-3 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-purple-50' : 'hover:bg-gray-100' }} transition-all group">
                <svg class="w-6 h-6 lg:w-7 lg:h-7 {{ request()->routeIs('dashboard') ? 'text-purple-600' : 'text-gray-700' }} group-hover:text-purple-600 transition-colors" 
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="ml-3 font-medium {{ request()->routeIs('dashboard') ? 'text-purple-600' : 'text-gray-700' }}">
                    Home
                </span>
            </a>
            
            <!-- Search -->
            <a href="#" 
               class="flex items-center p-2 lg:p-3 rounded-lg hover:bg-gray-100 transition-all group">
                <svg class="w-6 h-6 lg:w-7 lg:h-7 text-gray-700 group-hover:text-purple-600 transition-colors" 
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span class="ml-3 font-medium text-gray-700">Search</span>
            </a>
            
            <!-- Messages -->
            <a href="{{ route('messages') }}" 
               class="flex items-center p-2 lg:p-3 rounded-lg {{ request()->routeIs('messages*') ? 'bg-purple-50' : 'hover:bg-gray-100' }} transition-all group relative">
                <svg class="w-6 h-6 lg:w-7 lg:h-7 {{ request()->routeIs('messages*') ? 'text-purple-600' : 'text-gray-700' }} group-hover:text-purple-600 transition-colors" 
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z"/>
                </svg>
                <span class="ml-3 font-medium {{ request()->routeIs('messages*') ? 'text-purple-600' : 'text-gray-700' }}">Messages</span>
                @if($unreadMessages ?? 0 > 0)
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
                        {{ $unreadMessages }}
                    </span>
                @endif
            </a>
            
            <!-- Likes/Notifications -->
            <a href="#" 
               class="flex items-center p-2 lg:p-3 rounded-lg hover:bg-gray-100 transition-all group relative">
                <svg class="w-6 h-6 lg:w-7 lg:h-7 text-gray-700 group-hover:text-purple-600 transition-colors" 
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.5c0-2.485-2.239-4.5-5-4.5-1.719 0-3.237.871-4.136 2.197C11.719 4.871 9.719 4 8 4 5.239 4 3 6.015 3 8.5c0 .737.185 1.432.512 2.043C4.488 12.506 6.439 15.5 12 20c5.561-4.5 7.512-7.494 8.488-9.457A4.467 4.467 0 0021 8.5z"/>
                </svg>
                <span class="ml-3 font-medium text-gray-700">Notifications</span>
                @if($totalLikes > 0)
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-pink-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
                        {{ $totalLikes > 99 ? '99+' : $totalLikes }}
                    </span>
                @endif
            </a>
            
            <!-- Profile -->
            <a href="{{ route('user.profile.show', auth()->id()) }}" 
               class="flex items-center p-2 lg:p-3 rounded-lg hover:bg-gray-100 transition-all group">
                @if(auth()->user()->profilePhoto?->thumbnail_url)
                    <img src="{{ auth()->user()->profilePhoto->thumbnail_url }}" 
                         alt="Profile" 
                         class="w-6 h-6 lg:w-7 lg:h-7 rounded-full object-cover ring-2 ring-gray-200">
                @else
                    <div class="w-6 h-6 lg:w-7 lg:h-7 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-xs">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                @endif
                <span class="ml-3 font-medium text-gray-700">Profile</span>
            </a>
        </nav>
        
        <!-- More Options -->
        <div class="mt-auto p-4">
            <button class="flex items-center p-2 lg:p-3 rounded-lg hover:bg-gray-100 transition-all group w-full">
                <svg class="w-6 h-6 lg:w-7 lg:h-7 text-gray-700 group-hover:text-purple-600 transition-colors" 
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
                <span class="ml-3 font-medium text-gray-700">More</span>
            </button>
        </div>
        
        <!-- Stats Section (Desktop only) -->
        <div class="hidden mt-8 px-4">
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-4 border border-purple-100">
                <h4 class="font-semibold text-gray-900 mb-3 text-sm">Your Stats</h4>
                
                <!-- Mini Stats -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Profile Views</span>
                        <span class="text-sm font-bold text-purple-600">{{ $profileViews }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Likes</span>
                        <span class="text-sm font-bold text-pink-600">{{ $totalLikes }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Matches</span>
                        <span class="text-sm font-bold text-green-600">{{ count($mutualLikes) }}</span>
                    </div>
                </div>
                
                <!-- Profile Completion -->
                <div class="mt-4">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs text-gray-600">Profile</span>
                        <span class="text-xs font-bold text-purple-600">{{ $profileCompletion }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-1.5 rounded-full transition-all duration-500" 
                             style="width: {{ $profileCompletion }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Premium Badge (Desktop only) -->
        <div class="lg:block hidden mt-4 px-4">
            <a href="#" class="block">
                <div class="bg-gradient-to-r from-amber-400 to-orange-500 rounded-2xl p-4 text-white relative overflow-hidden group hover:scale-105 transition-transform">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-white bg-opacity-20 rounded-full -translate-y-10 translate-x-10"></div>
                    <div class="relative">
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="font-bold text-sm">Go Premium</span>
                        </div>
                        <p class="text-xs opacity-90">Get unlimited likes & more</p>
                    </div>
                </div>
            </a>
        </div>
        
        <!-- More Options -->
        <div class="absolute bottom-6 left-0 right-0 lg:px-8 md:px-2">
            <button class="flex items-center lg:px-4 md:px-0 md:justify-center lg:justify-start py-3 w-full rounded-xl hover:bg-gray-50 transition-all group">
                <svg class="w-7 h-7 text-gray-700 group-hover:scale-110 transition-transform" 
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span class="lg:block hidden ml-3 font-medium text-gray-700">More</span>
            </button>
        </div>
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
                @if($unreadMessages ?? 0 > 0)
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
            <a href="{{ route('user.profile.show', auth()->id()) }}" class="p-3 rounded-lg hover:bg-gray-50 transition-all">
                @if(auth()->user()->photos?->first())
                    <img src="{{ auth()->user()->photos->first()->url }}" 
                         alt="Profile" 
                         class="w-6 h-6 rounded-full object-cover ring-2 ring-gray-200">
                @else
                    <div class="w-6 h-6 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-xs">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                @endif
            </a>
        </div>
    </div>
</div>

<style>
    /* Custom font for Instagram-like feel */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    nav {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }
</style>