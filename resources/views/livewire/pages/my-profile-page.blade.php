<!-- My Profile Page -->
<div>
    <div class="h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-900 flex overflow-hidden transition-colors duration-300">
        
        <!-- Left Navigation Sidebar - Full Mode -->
        <x-navigation-sidebar mode="full" />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Header -->
            <div class="bg-white/80 dark:bg-zinc-800/80 backdrop-blur-lg border-b border-white/20 dark:border-zinc-700/50 transition-colors duration-300">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Profile</h1>
                            <p class="text-sm text-gray-600 dark:text-zinc-400">Manage your dating profile</p>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="flex items-center space-x-3">
                            <button wire:click="viewAsOthers" 
                                    class="px-4 py-2 bg-gray-100 dark:bg-zinc-700 hover:bg-gray-200 dark:hover:bg-zinc-600 text-gray-700 dark:text-zinc-300 rounded-xl font-medium transition-colors backdrop-blur-sm border border-gray-200 dark:border-zinc-600">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Preview
                            </button>
                            <button wire:click="editProfile"
                                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto overflow-x-hidden p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Profile Overview Card -->
                <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 overflow-hidden transition-colors duration-300">
                    <div class="relative">
                        <!-- Background Pattern -->
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-pink-500/10 dark:from-purple-500/20 dark:to-pink-500/20"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 dark:via-zinc-800/20 to-transparent"></div>
                        
                        <div class="relative p-8">
                            <div class="flex items-start space-x-6">
                                <!-- Profile Photo -->
                                <div class="relative flex-shrink-0">
                                    @if($user->profilePhoto)
                                        <img src="{{ $user->profilePhoto->medium_url }}" 
                                             alt="{{ $user->name }}"
                                             class="w-24 h-24 rounded-2xl object-cover border-4 border-white shadow-xl">
                                    @else
                                        <div class="w-24 h-24 bg-gradient-to-br from-purple-400 to-pink-500 dark:from-purple-600 dark:to-pink-600 rounded-2xl flex items-center justify-center border-4 border-white dark:border-zinc-700 shadow-xl">
                                            <span class="text-white font-bold text-2xl">{{ substr($user->name ?? 'U', 0, 1) }}</span>
                                        </div>
                                    @endif
                                    
                                    <!-- Photo Count Badge -->
                                    <div class="absolute -bottom-2 -right-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs font-bold rounded-full w-8 h-8 flex items-center justify-center shadow-lg">
                                        {{ $user->photos->count() }}
                                    </div>
                                </div>
                                
                                <!-- Profile Info -->
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                                {{ $user->profile?->first_name ?? $user->name }}
                                                @if($user->profile?->date_of_birth)
                                                    , {{ \Carbon\Carbon::parse($user->profile->date_of_birth)->age }}
                                                @endif
                                            </h2>
                                            @if($user->profile?->occupation)
                                                <p class="text-gray-600 dark:text-zinc-400 mb-2">{{ $user->profile->occupation }}</p>
                                            @endif
                                            @if($user->profile?->city)
                                                <div class="flex items-center text-gray-500 dark:text-zinc-500 text-sm">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    {{ $user->profile->city }}
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Profile Completion Circle -->
                                        <div class="relative w-16 h-16">
                                            <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 100 100">
                                                <circle cx="50" cy="50" r="45" stroke="#E5E7EB" stroke-width="8" fill="none"/>
                                                <circle cx="50" cy="50" r="45" 
                                                        stroke="url(#gradient)" stroke-width="8" fill="none"
                                                        stroke-dasharray="282.7" 
                                                        stroke-dashoffset="{{ 282.7 - (282.7 * $profileCompletion / 100) }}"
                                                        stroke-linecap="round"
                                                        class="transition-all duration-500"/>
                                                <defs>
                                                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                                        <stop offset="0%" style="stop-color:#8B5CF6"/>
                                                        <stop offset="100%" style="stop-color:#EC4899"/>
                                                    </linearGradient>
                                                </defs>
                                            </svg>
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $profileCompletion }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Bio -->
                                    @if($user->profile?->bio)
                                        <p class="text-gray-700 dark:text-zinc-300 text-sm leading-relaxed mb-4">
                                            "{{ Str::limit($user->profile->bio, 120) }}"
                                        </p>
                                    @endif
                                    
                                    <!-- Quick Stats -->
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-3 border border-blue-100 dark:border-blue-800/30">
                                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $profileStats['profile_views']['total'] ?? 0 }}</div>
                                            <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">Profile Views</div>
                                        </div>
                                        <div class="bg-gradient-to-br from-pink-50 to-rose-50 dark:from-pink-900/20 dark:to-rose-900/20 rounded-xl p-3 border border-pink-100 dark:border-pink-800/30">
                                            <div class="text-2xl font-bold text-pink-600 dark:text-pink-400">{{ $profileStats['likes_received']['total'] ?? 0 }}</div>
                                            <div class="text-xs text-pink-600 dark:text-pink-400 font-medium">Likes Received</div>
                                        </div>
                                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-3 border border-green-100 dark:border-green-800/30">
                                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $profileStats['matches']['total'] ?? 0 }}</div>
                                            <div class="text-xs text-green-600 dark:text-green-400 font-medium">Total Matches</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Sections Completion -->
                <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-8 transition-colors duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Profile Sections
                        </h3>
                        <div class="text-sm text-gray-600 dark:text-zinc-400">
                            {{ collect($completionSections)->where('completed', true)->count() }} of {{ count($completionSections) }} completed
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        @foreach($completionSections as $key => $section)
                            <div class="border border-gray-200 dark:border-zinc-700 rounded-2xl p-4 {{ $section['completed'] ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800/30' : 'bg-gray-50 dark:bg-zinc-700' }}">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-3">
                                        @if($section['completed'])
                                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <h4 class="font-semibold text-gray-900 dark:text-white">{{ $section['name'] }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-zinc-400">{{ $section['weight'] }}% of profile strength</p>
                                        </div>
                                    </div>
                                    
                                    @if(!$section['completed'])
                                        <button wire:click="editProfile"
                                                class="px-4 py-2 bg-purple-100 dark:bg-purple-900/30 hover:bg-purple-200 dark:hover:bg-purple-900/50 text-purple-700 dark:text-purple-400 rounded-lg font-medium transition-colors text-sm">
                                            Complete
                                        </button>
                                    @endif
                                </div>
                                
                                <!-- Section Items -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 ml-11">
                                    @foreach($section['items'] as $itemName => $completed)
                                        <div class="flex items-center space-x-2 text-sm">
                                            @if($completed)
                                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-gray-700 dark:text-zinc-300">{{ $itemName }}</span>
                                            @else
                                                <svg class="w-4 h-4 text-gray-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                <span class="text-gray-500 dark:text-zinc-500">{{ $itemName }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Analytics Dashboard -->
                <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-8 transition-colors duration-300">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Profile Performance
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Profile Views -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-blue-100 dark:border-blue-800/30">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </div>
                                @if($profileStats['profile_views']['trend'] === 'up')
                                    <div class="text-green-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L10 4.414 4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-1">{{ $profileStats['profile_views']['total'] }}</div>
                            <div class="text-sm text-blue-600 dark:text-blue-400 font-medium mb-1">Profile Views</div>
                            <div class="text-xs text-gray-600 dark:text-zinc-400">+{{ $profileStats['profile_views']['recent'] }} this month</div>
                        </div>
                        
                        <!-- Likes -->
                        <div class="bg-gradient-to-br from-pink-50 to-rose-50 dark:from-pink-900/20 dark:to-rose-900/20 rounded-2xl p-6 border border-pink-100 dark:border-pink-800/30">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-10 h-10 bg-pink-500 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                @if($profileStats['likes_received']['trend'] === 'up')
                                    <div class="text-green-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L10 4.414 4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="text-2xl font-bold text-pink-600 dark:text-pink-400 mb-1">{{ $profileStats['likes_received']['total'] }}</div>
                            <div class="text-sm text-pink-600 dark:text-pink-400 font-medium mb-1">Likes Received</div>
                            <div class="text-xs text-gray-600 dark:text-zinc-400">+{{ $profileStats['likes_received']['recent'] }} this month</div>
                        </div>
                        
                        <!-- Match Rate -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 border border-green-100 dark:border-green-800/30">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                                @if($likeStats['match_rate'] > 15)
                                    <div class="text-green-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L10 4.414 4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400 mb-1">{{ $likeStats['match_rate'] }}%</div>
                            <div class="text-sm text-green-600 dark:text-green-400 font-medium mb-1">Match Rate</div>
                            <div class="text-xs text-gray-600 dark:text-zinc-400">{{ $likeStats['mutual'] }} mutual matches</div>
                        </div>
                        
                        <!-- Response Rate -->
                        <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-2xl p-6 border border-amber-100 dark:border-amber-800/30">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                </div>
                                @if($profileStats['response_rate'] > 70)
                                    <div class="text-green-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L10 4.414 4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mb-1">{{ $profileStats['response_rate'] }}%</div>
                            <div class="text-sm text-amber-600 dark:text-amber-400 font-medium mb-1">Response Rate</div>
                            <div class="text-xs text-gray-600 dark:text-zinc-400">Avg: {{ $profileStats['avg_response_time'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="space-y-6">
                
                <!-- Profile Boost Card -->
                <div class="bg-gradient-to-br from-purple-500 via-pink-500 to-rose-500 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden">
                    <!-- Background decoration -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-16 translate-x-16"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full translate-y-12 -translate-x-12"></div>
                    
                    <div class="relative">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold">Boost Your Profile</h3>
                        </div>
                        
                        <div class="space-y-2 mb-6 text-sm">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Be one of the top profiles for 30 minutes</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Get up to 10x more views</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Increase your chances of matching</span>
                            </div>
                        </div>
                        
                        <button wire:click="boostProfile"
                                class="w-full bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white py-3 px-6 rounded-xl font-bold transition-colors border border-white/30">
                            Boost Now - 1 Credit
                        </button>
                    </div>
                </div>
                
                <!-- Who Viewed You -->
                <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-6 transition-colors duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Who Viewed You
                        </h3>
                        @if(count($profileViews) > 0)
                            <span class="text-xs text-purple-600 dark:text-purple-400 font-medium">{{ count($profileViews) }} recent</span>
                        @endif
                    </div>
                    
                    @forelse(array_slice($profileViews, 0, 5) as $viewer)
                        <div class="flex items-center space-x-3 py-3 hover:bg-gray-50 dark:hover:bg-zinc-700 rounded-lg px-2 cursor-pointer transition-colors">
                            @if($viewer['avatar'])
                                <img src="{{ $viewer['avatar'] }}" alt="{{ $viewer['name'] }}" 
                                     class="w-10 h-10 rounded-full object-cover border-2 {{ $viewer['is_recent'] ? 'border-green-200 dark:border-green-800/50' : 'border-gray-200 dark:border-zinc-600' }}">
                            @else
                                <div class="w-10 h-10 bg-gradient-to-br from-gray-400 to-gray-500 dark:from-zinc-600 dark:to-zinc-700 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">{{ substr($viewer['name'], 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $viewer['name'] }}</p>
                                <div class="flex items-center space-x-2">
                                    <p class="text-xs text-gray-500 dark:text-zinc-400">{{ $viewer['viewed_at'] }}</p>
                                    @if($viewer['is_recent'])
                                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 dark:text-zinc-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <p class="text-sm text-gray-500 dark:text-zinc-400">No recent profile views</p>
                        </div>
                    @endforelse
                </div>
                
                <!-- Recent Activity -->
                <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-6 transition-colors duration-300">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Recent Activity
                    </h3>
                    
                    @forelse(array_slice($recentActivity, 0, 6) as $activity)
                        <div class="flex items-center space-x-3 py-2">
                            @if($activity['user_avatar'])
                                <img src="{{ $activity['user_avatar'] }}" alt="{{ $activity['user_name'] }}" 
                                     class="w-8 h-8 rounded-full object-cover border border-gray-200">
                            @else
                                <div class="w-8 h-8 bg-gray-100 dark:bg-zinc-700 rounded-full flex items-center justify-center">
                                    @if($activity['icon'] === 'heart')
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endif
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <span class="font-medium">{{ $activity['user_name'] }}</span>
                                    <span class="text-gray-600 dark:text-zinc-400">{{ $activity['text'] }}</span>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-zinc-400">{{ $activity['time'] }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 dark:text-zinc-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <p class="text-sm text-gray-500 dark:text-zinc-400">No recent activity</p>
                        </div>
                    @endforelse
                </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Success Message -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" 
             class="fixed bottom-4 right-4 bg-green-500 dark:bg-green-600 text-white px-6 py-3 rounded-xl shadow-lg z-50"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100">
            {{ session('message') }}
        </div>
    @endif

    <!-- Custom Styles -->
    <style>
        /* Progress circle animation */
        .transition-all {
            transition-property: stroke-dashoffset;
            transition-timing-function: cubic-bezier(0.4, 0, 0.6, 1);
            transition-duration: 500ms;
        }
        
        /* Enhanced hover effects for cards */
        .hover\:shadow-lg:hover {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        /* Smooth scrolling */
        .space-y-6 {
            scroll-behavior: smooth;
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
