<!-- Dating Activity Page -->
<div>
    <div class="h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-900 flex overflow-hidden transition-colors duration-300">
        
        <!-- Left Navigation Sidebar - Full Mode -->
        <x-navigation-sidebar mode="full" />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Header -->
    <div class="bg-white/80 dark:bg-zinc-800/80 backdrop-blur-lg border-b border-white/20 dark:border-zinc-700/50 transition-colors duration-300">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dating Activity</h1>
                        <p class="text-sm text-gray-600 dark:text-zinc-400">Your likes, views & connections</p>
                    </div>
                </div>
                
                <!-- Filter & Search -->
                <div class="flex items-center space-x-3">
                    <!-- Search -->
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" 
                               wire:model.live="searchTerm"
                               placeholder="Search activity..." 
                               class="pl-10 pr-4 py-2 bg-white/50 dark:bg-zinc-700/50 backdrop-blur-sm border border-gray-200 dark:border-zinc-600 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-zinc-400">
                    </div>
                    
                    <!-- Filter Button -->
                    <button class="p-2 bg-white/50 dark:bg-zinc-700/50 backdrop-blur-sm border border-gray-200 dark:border-zinc-600 rounded-xl hover:bg-white/80 dark:hover:bg-zinc-600/80 transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="flex space-x-1 bg-gray-100/50 dark:bg-zinc-700/50 backdrop-blur-sm p-1 rounded-xl">
                <button wire:click="setActiveTab('liked_you')"
                        class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 {{ $activeTab === 'liked_you' ? 'bg-white dark:bg-zinc-600 shadow-sm text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-300' }}">
                    <div class="flex items-center justify-center space-x-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                        <span>Liked You</span>
                    </div>
                </button>
                <button wire:click="setActiveTab('you_liked')"
                        class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 {{ $activeTab === 'you_liked' ? 'bg-white dark:bg-zinc-600 shadow-sm text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-300' }}">
                    <div class="flex items-center justify-center space-x-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                        <span>You Liked</span>
                    </div>
                </button>
                <button wire:click="setActiveTab('mutual')"
                        class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 {{ $activeTab === 'mutual' ? 'bg-white dark:bg-zinc-600 shadow-sm text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-300' }}">
                    <div class="flex items-center justify-center space-x-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span>Mutual</span>
                    </div>
                </button>
                <button wire:click="setActiveTab('views')"
                        class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 {{ $activeTab === 'views' ? 'bg-white dark:bg-zinc-600 shadow-sm text-purple-600 dark:text-purple-400' : 'text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-zinc-300' }}">
                    <div class="flex items-center justify-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>Profile Views</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden">
        <div class="max-w-4xl mx-auto px-4 py-6">
        
        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <!-- Liked You Card -->
            <div class="bg-gradient-to-br from-pink-50 to-rose-50 dark:from-pink-900/20 dark:to-rose-900/20 rounded-2xl p-6 border border-pink-100 dark:border-pink-800/30">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-pink-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-pink-600 dark:text-pink-400">{{ count($newMatches) }}</div>
                                        </div>
                <div class="text-sm text-pink-600 dark:text-pink-400 font-medium">Liked You</div>
                <div class="text-xs text-gray-600 dark:text-zinc-400 mt-1">People who liked your profile</div>
                                </div>
                                
            <!-- You Liked Card -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-blue-100 dark:border-blue-800/30">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ count($messagesWithMatches) }}</div>
                </div>
                <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">You Liked</div>
                <div class="text-xs text-gray-600 dark:text-zinc-400 mt-1">People you've liked</div>
                                    </div>
            
            <!-- Mutual Matches Card -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 border border-green-100 dark:border-green-800/30">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ count($matches) }}</div>
                </div>
                <div class="text-sm text-green-600 dark:text-green-400 font-medium">Mutual</div>
                <div class="text-xs text-gray-600 dark:text-zinc-400 mt-1">You both liked each other</div>
                            </div>
            
            <!-- Profile Views Card -->
            <div class="bg-gradient-to-br from-purple-50 to-violet-50 dark:from-purple-900/20 dark:to-violet-900/20 rounded-2xl p-6 border border-purple-100 dark:border-purple-800/30">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 bg-purple-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        </div>
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ count($superLikeMatches) }}</div>
                </div>
                <div class="text-sm text-purple-600 dark:text-purple-400 font-medium">Profile Views</div>
                <div class="text-xs text-gray-600 dark:text-zinc-400 mt-1">People who viewed you</div>
            </div>
        </div>
        
        <!-- Activity List -->
        <div class="space-y-4">
            @forelse($filteredMatches as $match)
                <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-2xl shadow-sm border border-white/50 dark:border-zinc-700/50 overflow-hidden hover:shadow-lg hover:bg-white/80 dark:hover:bg-zinc-700/80 transition-all duration-300 group">
                    <div class="p-6">
                        <div class="flex items-center space-x-4">
                            <!-- Avatar -->
                            <div class="relative flex-shrink-0">
                                @if($match['avatar'])
                                    <img src="{{ $match['avatar'] }}" 
                                         alt="{{ $match['name'] }}"
                                         class="w-16 h-16 rounded-full object-cover border-2 border-white dark:border-zinc-700 shadow-md">
                                @else
                                    <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-pink-500 dark:from-purple-600 dark:to-pink-600 rounded-full flex items-center justify-center border-2 border-white dark:border-zinc-700 shadow-md">
                                        <span class="text-white font-bold text-xl">{{ substr($match['name'], 0, 1) }}</span>
                                    </div>
                                @endif
                                
                                <!-- Status Indicators -->
                                <div class="absolute -bottom-1 -right-1 flex space-x-1">
                                    @if($match['is_online'])
                                        <div class="w-5 h-5 bg-green-500 border-2 border-white rounded-full">
                                            <div class="w-1 h-1 bg-white rounded-full mx-auto mt-1"></div>
                                        </div>
                                    @endif
                                    
                                    @if($match['is_super_like'])
                                        <div class="w-5 h-5 bg-blue-500 border-2 border-white rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Main Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <!-- Name & Basic Info -->
                                        <div class="flex items-center space-x-2 mb-1">
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ $match['name'] }}</h3>
                                            @if($match['age'])
                                                <span class="text-gray-600 dark:text-zinc-400">, {{ $match['age'] }}</span>
                                            @endif
                                            @if($match['is_new'])
                                                <span class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 px-2 py-0.5 rounded-full text-xs font-medium">New</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Location & Distance -->
                                        <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-zinc-400 mb-2">
                                            <div class="flex items-center space-x-1">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>{{ $match['location'] }} • {{ $match['distance'] }}km away</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Additional Info Tags -->
                                        <div class="flex flex-wrap gap-2 mb-3">
                                            @if($match['religion'])
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400">
                                                    🕌 {{ ucfirst($match['religion']) }}
                                                </span>
                                            @endif
                                            
                                            @if($match['marriage_timeline'])
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-pink-100 dark:bg-pink-900/30 text-pink-800 dark:text-pink-400">
                                                    💍 {{ ucfirst($match['marriage_timeline']) }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <!-- Activity Info -->
                                        <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-zinc-400">
                                            @if($activeTab === 'liked_you')
                                                <svg class="w-4 h-4 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>Liked your profile {{ $match['matched_at'] }}</span>
                                            @elseif($activeTab === 'you_liked')
                                                <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>You liked {{ $match['matched_at'] }}</span>
                                            @elseif($activeTab === 'mutual')
                                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                                <span>Mutual match {{ $match['matched_at'] }}</span>
                                            @elseif($activeTab === 'views')
                                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                <span>Viewed your profile {{ $match['matched_at'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex items-center space-x-2 ml-4">
                                        <!-- Unread Messages Badge -->
                                        @if($match['unread_count'] > 0)
                                            <div class="bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">
                                                {{ $match['unread_count'] }}
                                            </div>
                                        @endif
                                        
                                        <!-- Action Menu -->
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" class="p-2 text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:hover:text-zinc-400 rounded-full hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                                </svg>
                                            </button>
                                            
                                            <div x-show="open" @click.away="open = false" 
                                                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-zinc-800 rounded-lg shadow-lg border border-gray-200 dark:border-zinc-700 z-10"
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100">
                                                <div class="py-1">
                                                    @if($activeTab === 'liked_you')
                                                        <button wire:click="viewProfile({{ $match['id'] }})" @click="open = false"
                                                                class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700 flex items-center space-x-2">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                            </svg>
                                                            <span>View Profile</span>
                                                        </button>
                                                        <button wire:click="startChat({{ $match['id'] }})" @click="open = false"
                                                                class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700 flex items-center space-x-2">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                                            </svg>
                                                            <span>Like Back</span>
                                                        </button>
                                                    @else
                                                        <button wire:click="viewProfile({{ $match['id'] }})" @click="open = false"
                                                                class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700 flex items-center space-x-2">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                            </svg>
                                                            <span>View Profile</span>
                                                        </button>
                                                        @if($activeTab === 'mutual')
                                                            <button wire:click="startChat({{ $match['id'] }})" @click="open = false"
                                                                    class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700 flex items-center space-x-2">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                                                </svg>
                                                                <span>Send Message</span>
                                                            </button>
                                                        @endif
                                                    @endif
                                                    <div class="border-t border-gray-100 dark:border-zinc-700"></div>
                                                    <button wire:click="unmatchUser({{ $match['id'] }})" @click="open = false"
                                                            wire:confirm="Are you sure you want to remove this person?"
                                                            class="w-full px-4 py-2 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center space-x-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        <span>Remove</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-zinc-700 dark:to-zinc-800 rounded-full flex items-center justify-center">
                        @if($activeTab === 'liked_you')
                            <svg class="w-12 h-12 text-pink-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                            </svg>
                        @elseif($activeTab === 'you_liked')
                            <svg class="w-12 h-12 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                            </svg>
                        @elseif($activeTab === 'mutual')
                            <svg class="w-12 h-12 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @else
                            <svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        @endif
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                        @if($activeTab === 'liked_you')
                            No one has liked you yet
                        @elseif($activeTab === 'you_liked')
                            You haven't liked anyone yet
                        @elseif($activeTab === 'mutual')
                            No mutual matches yet
                        @elseif($activeTab === 'views')
                            No profile views yet
                        @else
                            No activity found
                        @endif
                    </h3>
                    <p class="text-gray-600 dark:text-zinc-400 mb-6">
                        @if($activeTab === 'liked_you')
                            Keep swiping to get more likes!
                        @elseif($activeTab === 'you_liked')
                            Start swiping to like people you're interested in
                        @elseif($activeTab === 'mutual')
                            When someone likes you back, they'll appear here
                        @elseif($activeTab === 'views')
                            Complete your profile to get more views
                        @else
                            Try adjusting your search or preferences
                        @endif
                    </p>
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        Start Swiping
                    </a>
                </div>
            @endforelse
        </div>
    </div>
        </div>
    </div>
    </div>
    
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        /* Custom scrollbar for content area */
        .overflow-y-auto::-webkit-scrollbar {
            width: 8px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.5);
            border-radius: 4px;
            border: 1px solid transparent;
        }
        
        .dark .overflow-y-auto::-webkit-scrollbar-thumb {
            background: rgba(82, 82, 91, 0.7);
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 0.8);
        }
        
        .dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: rgba(113, 113, 122, 0.9);
        }
    </style>
</div>
