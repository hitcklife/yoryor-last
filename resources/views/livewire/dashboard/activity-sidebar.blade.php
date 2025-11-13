<!-- Activity Sidebar -->
<div class="space-y-6">
    
    <!-- Profile Stats -->
    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-700 p-6 transition-colors duration-300">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
            <x-lucide-bar-chart-3 class="w-5 h-5 mr-2 text-purple-600" />
            Your Stats
        </h3>
        
        <div class="grid grid-cols-2 gap-4">
            <!-- Profile Views -->
            <div class="text-center p-3 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-100 dark:border-blue-800/30">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-1">{{ $profileViews }}</div>
                <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">{{ __('dashboard.profile_views') }}</div>
            </div>
            
            <!-- Likes Received -->
            <div class="text-center p-3 bg-gradient-to-br from-pink-50 to-rose-50 dark:from-pink-900/20 dark:to-rose-900/20 rounded-xl border border-pink-100 dark:border-pink-800/30">
                <div class="text-2xl font-bold text-pink-600 dark:text-pink-400 mb-1">{{ $totalLikes }}</div>
                <div class="text-xs text-pink-600 dark:text-pink-400 font-medium">{{ __('dashboard.likes_received') }}</div>
            </div>
        </div>
        
        <!-- Profile Completion -->
        <div class="mt-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-zinc-300">Profile Completion</span>
                <span class="text-sm font-bold text-purple-600 dark:text-purple-400">{{ $profileCompletion }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-zinc-700 rounded-full h-2">
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 h-2 rounded-full transition-all duration-500" style="width: {{ $profileCompletion }}%"></div>
            </div>
            @if($profileCompletion < 100)
                <a href="{{ route('profile.enhance') }}" class="text-xs text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 mt-2 block">
                    Complete your profile →
                </a>
            @endif
        </div>
    </div>
    
    <!-- Who Viewed You -->
    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-700 p-6 transition-colors duration-300">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                <x-lucide-eye class="w-5 h-5 mr-2 text-green-600" />
                {{ __('dashboard.who_viewed_you') }}
            </h3>
            @if(count($whoViewedYou) > 0)
                <span class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 text-xs font-medium px-2 py-1 rounded-full">
                    {{ count($whoViewedYou) }} new
                </span>
            @endif
        </div>
        
        @forelse($whoViewedYou as $viewer)
            <div class="flex items-center space-x-3 py-3 hover:bg-gray-50 dark:hover:bg-zinc-700 rounded-lg px-2 cursor-pointer transition-colors" 
                 wire:click="viewProfile({{ $viewer['id'] }})">
                @if($viewer['avatar'])
                    <img src="{{ $viewer['avatar'] }}" alt="{{ $viewer['name'] }}" 
                         class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 dark:border-zinc-600">
                @else
                    <div class="w-10 h-10 bg-gradient-to-br from-gray-400 to-gray-500 dark:from-zinc-600 dark:to-zinc-700 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-sm">{{ substr($viewer['name'], 0, 1) }}</span>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $viewer['name'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-zinc-400">{{ $viewer['viewed_at'] }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        @empty
            <div class="text-center py-6">
                <svg class="w-12 h-12 text-gray-300 dark:text-zinc-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <p class="text-sm text-gray-500 dark:text-zinc-400">No recent profile views</p>
            </div>
        @endforelse
    </div>
    
    <!-- Mutual Likes -->
    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-700 p-6 transition-colors duration-300">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-pink-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                </svg>
                {{ __('dashboard.mutual_likes') }}
            </h3>
            @if(count($mutualLikes) > 0)
                <span class="bg-pink-100 dark:bg-pink-900/30 text-pink-800 dark:text-pink-400 text-xs font-medium px-2 py-1 rounded-full">
                    {{ count($mutualLikes) }}
                </span>
            @endif
        </div>
        
        @forelse($mutualLikes as $like)
            <div class="flex items-center space-x-3 py-3 hover:bg-gray-50 dark:hover:bg-zinc-700 rounded-lg px-2 cursor-pointer transition-colors"
                 wire:click="viewProfile({{ $like['id'] }})">
                <div class="relative">
                    @if($like['avatar'])
                        <img src="{{ $like['avatar'] }}" alt="{{ $like['name'] }}" 
                             class="w-10 h-10 rounded-full object-cover border-2 border-pink-200 dark:border-pink-800/50">
                    @else
                        <div class="w-10 h-10 bg-gradient-to-br from-pink-400 to-pink-500 dark:from-pink-600 dark:to-pink-700 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-sm">{{ substr($like['name'], 0, 1) }}</span>
                        </div>
                    @endif
                    
                    @if($like['is_super_like'])
                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $like['name'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-zinc-400">
                        @if($like['is_super_like'])
                            Super liked • {{ $like['liked_at'] }}
                        @else
                            Liked back • {{ $like['liked_at'] }}
                        @endif
                    </p>
                </div>
                <div class="w-6 h-6 bg-gradient-to-br from-pink-500 to-purple-500 rounded-full flex items-center justify-center">
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        @empty
            <div class="text-center py-6">
                <svg class="w-12 h-12 text-gray-300 dark:text-zinc-600 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-gray-500 dark:text-zinc-400">No mutual likes yet</p>
            </div>
        @endforelse
    </div>
    
    <!-- Recent Activity -->
    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-700 p-6 transition-colors duration-300">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            {{ __('dashboard.recent_activity') }}
        </h3>
        
        @forelse($recentActivity as $activity)
            <div class="flex items-center space-x-3 py-2">
                @if(isset($activity['user_avatar']) && $activity['user_avatar'])
                    <img src="{{ $activity['user_avatar'] }}" alt="{{ $activity['user_name'] }}" 
                         class="w-8 h-8 rounded-full object-cover border-2 border-gray-200 dark:border-zinc-600 flex-shrink-0">
                @else
                    <div class="w-8 h-8 bg-gradient-to-br from-gray-400 to-gray-500 dark:from-zinc-600 dark:to-zinc-700 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-bold text-xs">{{ substr($activity['user_name'], 0, 1) }}</span>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900 dark:text-white">
                        <span class="font-medium">{{ $activity['user_name'] }}</span>
                        <span class="text-gray-600 dark:text-zinc-400">{{ $activity['text'] }}</span>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-zinc-400">{{ $activity['time'] }}</p>
                </div>
                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0">
                    @if($activity['icon'] === 'heart')
                        <div class="w-6 h-6 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @elseif($activity['icon'] === 'star')
                        <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                    @else
                        <div class="w-6 h-6 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-6">
                <svg class="w-12 h-12 text-gray-300 dark:text-zinc-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <p class="text-sm text-gray-500 dark:text-zinc-400">No recent activity</p>
            </div>
        @endforelse
    </div>
    
    <!-- Premium Upgrade -->
    <div class="bg-gradient-to-br from-purple-500 via-pink-500 to-rose-500 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -translate-y-16 translate-x-16"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white bg-opacity-10 rounded-full translate-y-12 -translate-x-12"></div>
        
        <div class="relative">
            <div class="flex items-center mb-3">
                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold">{{ __('dashboard.upgrade_to_premium') }}</h3>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex items-center space-x-2 text-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ __('dashboard.see_who_likes_you') }}</span>
                </div>
                <div class="flex items-center space-x-2 text-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ __('dashboard.unlimited_likes') }}</span>
                </div>
                <div class="flex items-center space-x-2 text-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ __('dashboard.rewind_last_swipe') }}</span>
                </div>
            </div>
            
            <button class="w-full bg-white text-purple-600 py-3 px-6 rounded-xl font-bold hover:bg-gray-100 transition-colors">
                {{ __('dashboard.upgrade_now') }}
            </button>
        </div>
    </div>
</div>
