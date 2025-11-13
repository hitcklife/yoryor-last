<!-- User Profile Page -->
<div>
    <div class="h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-900 flex overflow-hidden transition-colors duration-300">
        
        <!-- Left Navigation Sidebar - Compact Mode for focus on profile -->
        <x-navigation-sidebar mode="compact" />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Header -->
            <div class="bg-white/80 dark:bg-zinc-800/80 backdrop-blur-lg border-b border-white/20 dark:border-zinc-700/50 transition-colors duration-300">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <button onclick="window.history.back()" 
                                    class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-full transition-colors">
                                <svg class="w-5 h-5 text-gray-600 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white transition-colors duration-300">
                                        {{ $user->profile?->first_name ?? 'User' }}
                                        @if($this->getAge())
                                            <span class="text-xl text-gray-600 dark:text-zinc-400">, {{ $this->getAge() }}</span>
                                        @endif
                                    </h1>
                                    @if($isPrivateUser)
                                        <div class="flex items-center space-x-1 px-2 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-400 text-xs font-medium rounded-full">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            <span>Private</span>
                                        </div>
                                    @endif
                                </div>
                                @if($user->profile?->city)
                                    <p class="text-sm text-gray-600 dark:text-zinc-400 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $user->profile->city }}@if($user->profile->country_name), {{ $user->profile->country_name }}@endif
                                    </p>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-3">
                            @if(!$isBlocked && !$hasBlocked)
                                @if($isMatch)
                                    <span class="px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 text-sm font-medium rounded-full">
                                        ✨ Match
                                    </span>
                                @endif
                                
                                <!-- Message Button (only if matched) -->
                                @if($isMatch)
                                    <button wire:click="messageUser"
                                            class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        Message
                                    </button>
                                @endif
                            @endif
                            
                            <!-- Options Menu -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" 
                                        class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-full transition-colors">
                                    <svg class="w-5 h-5 text-gray-600 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                    </svg>
                                </button>
                                
                                <div x-show="open" @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-zinc-800 rounded-xl shadow-lg border border-gray-200 dark:border-zinc-700 py-1 z-50">
                                    
                                    @if(!$isBlocked && !$hasBlocked)
                                        <button wire:click="openReportModal" @click="open = false"
                                                class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-700">
                                            <svg class="w-4 h-4 mr-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                                            </svg>
                                            Report User
                                        </button>
                                        
                                        <button wire:click="blockUser" @click="open = false"
                                                class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                                            </svg>
                                            Block User
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Privacy Notice Banner -->
            @if($isPrivateUser)
                <div class="bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800/30">
                    <div class="max-w-4xl mx-auto px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                    Private Profile
                                </h3>
                                <p class="text-sm text-amber-700 dark:text-amber-300">
                                    This user has enabled privacy settings. Some content may be blurred or hidden for privacy protection.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto overflow-x-hidden">
                <div class="max-w-4xl mx-auto p-6">
                    
                    <!-- Stories Section -->
                    @if($user->stories && $user->stories->count() > 0)
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Stories</h3>
                            <div class="flex space-x-3 overflow-x-auto pb-2">
                                @foreach($user->stories as $story)
                                    <button wire:click="viewStory({{ $story->id }})"
                                            class="relative flex-shrink-0 w-20 h-20 rounded-full bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 p-1 {{ $isPrivateUser ? 'opacity-50' : '' }}">
                                        <div class="w-full h-full rounded-full overflow-hidden bg-white dark:bg-zinc-800">
                                            @if($story->media_url)
                                                <img src="{{ $story->media_url }}" alt="Story" class="w-full h-full object-cover {{ $isPrivateUser ? 'privacy-blur' : '' }}">
                                            @else
                                                <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-zinc-700 dark:to-zinc-600 flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-gray-500 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        @if($isPrivateUser)
                                            <!-- Privacy overlay for stories -->
                                            <div class="absolute inset-0 bg-black/30 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <span class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 text-xs text-gray-600 dark:text-zinc-400 bg-white dark:bg-zinc-800 px-1 rounded">
                                            {{ $loop->iteration }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Mobile-First Card Layout -->
                    <div class="space-y-6 max-w-2xl mx-auto lg:max-w-4xl">
                        
                        <!-- First Photo with Overlay Info (Dashboard Style) -->
                        @if($user->photos && $user->photos->count() > 0)
                            <div class="relative bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 overflow-hidden transition-colors duration-300">
                                <div class="aspect-[4/5] relative overflow-hidden bg-gray-100 dark:bg-zinc-700">
                                    <img src="{{ $user->photos[0]->medium_url ?? $user->photos[0]->original_url }}" 
                                         alt="{{ $user->profile?->first_name }}'s photo"
                                         onload="this.classList.remove('opacity-0')"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                         class="w-full h-full object-cover transition-opacity duration-300 opacity-0 {{ $isPrivateUser ? 'privacy-blur' : '' }}">
                                    
                                    @if($isPrivateUser)
                                        <!-- Privacy Overlay -->
                                        <div class="absolute inset-0 bg-black/20 flex items-center justify-center z-10">
                                            <div class="text-center text-white">
                                                <div class="w-16 h-16 mx-auto mb-4 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                </div>
                                                <p class="text-sm font-medium">Private Profile</p>
                                                <p class="text-xs opacity-80">Images are blurred for privacy</p>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Dark Overlay with User Info (Dashboard Style) -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                                    
                                    <!-- User Info Overlay -->
                                    <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <h2 class="text-2xl font-bold mb-1">
                                                    {{ $user->profile?->first_name ?? 'User' }}
                                                    @if($this->getAge())
                                                        <span class="text-xl font-normal opacity-90">, {{ $this->getAge() }}</span>
                                                    @endif
                                                </h2>
                                                @if($user->profile?->city)
                                                    <div class="flex items-center text-white/80 text-sm">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        </svg>
                                                        {{ $user->profile->city }}@if($user->profile->country_name), {{ $user->profile->country_name }}@endif
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            @if(!$isBlocked && !$hasBlocked)
                                                <div class="flex space-x-3">
                                                    <!-- Dislike Button -->
                                                    <button wire:click="dislikeUser"
                                                            class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center hover:bg-red-500/30 transition-all duration-300">
                                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                    
                                                    <!-- Like Button -->
                                                    <button wire:click="{{ $isLiked ? 'unlikeUser' : 'likeUser' }}"
                                                            class="w-14 h-14 rounded-full {{ $isLiked ? 'bg-gradient-to-r from-pink-500 to-red-500' : 'bg-white/20 backdrop-blur-sm border border-white/30' }} flex items-center justify-center hover:bg-pink-500/30 transition-all duration-300">
                                                        <svg class="w-6 h-6 text-white" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                                        </svg>
                                                    </button>
                                                    
                                                    <!-- Super Like Button -->
                                                    <button class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center hover:bg-blue-500/30 transition-all duration-300">
                                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Quick Info Tags -->
                                        <div class="flex flex-wrap gap-2">
                                            @if($user->profile?->looking_for_relationship)
                                                <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-xs font-medium">
                                                    {{ ucfirst(str_replace('_', ' ', $user->profile->looking_for_relationship)) }}
                                                </span>
                                            @endif
                                            @if($user->physicalProfile?->height)
                                                <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-xs font-medium">
                                                    {{ $user->physicalProfile->height }}cm
                                                </span>
                                            @endif
                                            @if($user->culturalProfile?->religion)
                                                <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-xs font-medium">
                                                    {{ ucfirst($user->culturalProfile->religion) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Photo Counter -->
                                    @if($user->photos->count() > 1)
                                        <div class="absolute top-4 right-4 bg-black/50 backdrop-blur-sm rounded-full px-3 py-1 text-white text-sm font-medium">
                                            1 / {{ $user->photos->count() }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Additional Photos with Info Cards -->
                        @if($user->photos && $user->photos->count() > 1)
                            @foreach($user->photos->slice(1) as $index => $photo)
                                <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 overflow-hidden transition-colors duration-300">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
                                        <!-- Photo -->
                                        <div class="aspect-[4/5] relative overflow-hidden bg-gray-100 dark:bg-zinc-700">
                                            <img src="{{ $photo->medium_url ?? $photo->original_url }}" 
                                                 alt="{{ $user->profile?->first_name }}'s photo {{ $index + 2 }}"
                                                 onload="this.classList.remove('opacity-0')"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                 class="w-full h-full object-cover transition-opacity duration-300 opacity-0 {{ $isPrivateUser ? 'privacy-blur' : '' }}">
                                            
                                            @if($isPrivateUser)
                                                <!-- Privacy Overlay for additional photos -->
                                                <div class="absolute inset-0 bg-black/20 flex items-center justify-center z-10">
                                                    <div class="text-center text-white">
                                                        <div class="w-12 h-12 mx-auto mb-2 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                            </svg>
                                                        </div>
                                                        <p class="text-xs font-medium">Private</p>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <!-- Photo Counter -->
                                            <div class="absolute top-4 right-4 bg-black/50 backdrop-blur-sm rounded-full px-3 py-1 text-white text-sm font-medium">
                                                {{ $index + 2 }} / {{ $user->photos->count() }}
                                            </div>
                                        </div>
                                        
                                        <!-- Info Card -->
                                        <div class="p-6 flex flex-col justify-center {{ $isPrivateUser ? 'relative' : '' }}">
                                            @if($isPrivateUser)
                                                <!-- Privacy Overlay for Info Cards -->
                                                <div class="absolute inset-0 bg-white/80 dark:bg-zinc-800/80 backdrop-blur-sm flex items-center justify-center z-10 rounded-r-3xl">
                                                    <div class="text-center">
                                                        <div class="w-12 h-12 mx-auto mb-3 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
                                                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                            </svg>
                                                        </div>
                                                        <p class="text-sm font-medium text-gray-700 dark:text-zinc-300">Private Information</p>
                                                        <p class="text-xs text-gray-500 dark:text-zinc-400">Details are hidden for privacy</p>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            @if($index === 1)
                                                <!-- Career & Education Info -->
                                                @if($user->careerProfile)
                                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"/>
                                                        </svg>
                                                        Career & Education
                                                    </h3>
                                                    <div class="space-y-3">
                                                        @if($user->careerProfile->occupation)
                                                            <div class="flex items-center text-sm text-gray-600 dark:text-zinc-400">
                                                                <svg class="w-4 h-4 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"/>
                                                                </svg>
                                                                {{ $user->careerProfile->occupation }}
                                                            </div>
                                                        @endif
                                                        @if($user->careerProfile->education_level)
                                                            <div class="flex items-center text-sm text-gray-600 dark:text-zinc-400">
                                                                <svg class="w-4 h-4 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                                                </svg>
                                                                {{ ucfirst(str_replace('_', ' ', $user->careerProfile->education_level)) }}
                                                            </div>
                                                        @endif
                                                        @if($user->careerProfile->work_status)
                                                            <div class="flex items-center text-sm text-gray-600 dark:text-zinc-400">
                                                                <svg class="w-4 h-4 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                                                </svg>
                                                                {{ ucfirst(str_replace('_', ' ', $user->careerProfile->work_status)) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @elseif($index === 2)
                                                <!-- Lifestyle & Health Info -->
                                                @if($user->physicalProfile)
                                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                        </svg>
                                                        Lifestyle & Health
                                                    </h3>
                                                    <div class="space-y-3">
                                                        @if($user->physicalProfile->fitness_level)
                                                            <div class="flex items-center text-sm text-gray-600 dark:text-zinc-400">
                                                                <span class="font-medium w-20">Fitness:</span>
                                                                <span class="ml-2">{{ ucfirst(str_replace('_', ' ', $user->physicalProfile->fitness_level)) }}</span>
                                                            </div>
                                                        @endif
                                                        @if($user->physicalProfile->smoking_habit)
                                                            <div class="flex items-center text-sm text-gray-600 dark:text-zinc-400">
                                                                <span class="font-medium w-20">Smoking:</span>
                                                                <span class="ml-2">{{ ucfirst(str_replace('_', ' ', $user->physicalProfile->smoking_habit)) }}</span>
                                                            </div>
                                                        @endif
                                                        @if($user->physicalProfile->drinking_habit)
                                                            <div class="flex items-center text-sm text-gray-600 dark:text-zinc-400">
                                                                <span class="font-medium w-20">Drinking:</span>
                                                                <span class="ml-2">{{ ucfirst(str_replace('_', ' ', $user->physicalProfile->drinking_habit)) }}</span>
                                                            </div>
                                                        @endif
                                                        @if($user->physicalProfile->diet_preference)
                                                            <div class="flex items-center text-sm text-gray-600 dark:text-zinc-400">
                                                                <span class="font-medium w-20">Diet:</span>
                                                                <span class="ml-2">{{ ucfirst($user->physicalProfile->diet_preference) }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @else
                                                <!-- Cultural Background Info -->
                                                @if($user->culturalProfile)
                                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        Cultural Background
                                                    </h3>
                                                    <div class="space-y-3">
                                                        @if($user->culturalProfile->ethnicity)
                                                            <div class="flex items-center text-sm text-gray-600 dark:text-zinc-400">
                                                                <span class="font-medium w-20">Ethnicity:</span>
                                                                <span class="ml-2">{{ ucfirst($user->culturalProfile->ethnicity) }}</span>
                                                            </div>
                                                        @endif
                                                        @if($user->culturalProfile->religion)
                                                            <div class="flex items-center text-sm text-gray-600 dark:text-zinc-400">
                                                                <span class="font-medium w-20">Religion:</span>
                                                                <span class="ml-2">{{ ucfirst($user->culturalProfile->religion) }}</span>
                                                            </div>
                                                        @endif
                                                        @if($user->culturalProfile->uzbek_region)
                                                            <div class="flex items-center text-sm text-gray-600 dark:text-zinc-400">
                                                                <span class="font-medium w-20">Region:</span>
                                                                <span class="ml-2">{{ $user->culturalProfile->uzbek_region }}</span>
                                                            </div>
                                                        @endif
                                                        @if($user->culturalProfile->lifestyle_type)
                                                            <div class="flex items-center text-sm text-gray-600 dark:text-zinc-400">
                                                                <span class="font-medium w-20">Lifestyle:</span>
                                                                <span class="ml-2">{{ ucfirst(str_replace('_', ' ', $user->culturalProfile->lifestyle_type)) }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- Additional Info Cards (for users with fewer photos or extra info) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <!-- Bio Card -->
                            @if($user->profile?->bio)
                                <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-6 transition-colors duration-300 {{ $isPrivateUser ? 'relative' : '' }}">
                                    @if($isPrivateUser)
                                        <!-- Privacy Overlay for Bio -->
                                        <div class="absolute inset-0 bg-white/80 dark:bg-zinc-800/80 backdrop-blur-sm flex items-center justify-center z-10 rounded-3xl">
                                            <div class="text-center">
                                                <div class="w-12 h-12 mx-auto mb-3 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                </div>
                                                <p class="text-sm font-medium text-gray-700 dark:text-zinc-300">Private Bio</p>
                                                <p class="text-xs text-gray-500 dark:text-zinc-400">Personal information is hidden</p>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                        </svg>
                                        About Me
                                    </h3>
                                    <p class="text-gray-700 dark:text-zinc-300 leading-relaxed">{{ $user->profile->bio }}</p>
                                </div>
                            @endif

                            <!-- Family & Marriage -->
                            @if($user->familyPreference)
                                <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-6 transition-colors duration-300 {{ $isPrivateUser ? 'relative' : '' }}">
                                    @if($isPrivateUser)
                                        <!-- Privacy Overlay for Family Info -->
                                        <div class="absolute inset-0 bg-white/80 dark:bg-zinc-800/80 backdrop-blur-sm flex items-center justify-center z-10 rounded-3xl">
                                            <div class="text-center">
                                                <div class="w-12 h-12 mx-auto mb-3 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                </div>
                                                <p class="text-sm font-medium text-gray-700 dark:text-zinc-300">Private Details</p>
                                                <p class="text-xs text-gray-500 dark:text-zinc-400">Family information is hidden</p>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        Family & Marriage
                                    </h3>
                                    <div class="space-y-3">
                                        @if($user->familyPreference->marriage_intention)
                                            <div class="flex items-center text-sm text-gray-600 dark:text-zinc-400">
                                                <span class="font-medium w-28">Marriage:</span>
                                                <span class="ml-2">{{ ucfirst(str_replace('_', ' ', $user->familyPreference->marriage_intention)) }}</span>
                                            </div>
                                        @endif
                                        @if($user->familyPreference->children_preference)
                                            <div class="flex items-center text-sm text-gray-600 dark:text-zinc-400">
                                                <span class="font-medium w-28">Children:</span>
                                                <span class="ml-2">{{ ucfirst(str_replace('_', ' ', $user->familyPreference->children_preference)) }}</span>
                                            </div>
                                        @endif
                                        @if($user->familyPreference->family_importance)
                                            <div class="flex items-center text-sm text-gray-600 dark:text-zinc-400">
                                                <span class="font-medium w-28">Family:</span>
                                                <span class="ml-2">{{ ucfirst(str_replace('_', ' ', $user->familyPreference->family_importance)) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Interests -->
                            @if($user->profile?->interests)
                                <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-6 transition-colors duration-300 {{ $isPrivateUser ? 'relative' : '' }}">
                                    @if($isPrivateUser)
                                        <!-- Privacy Overlay for Interests -->
                                        <div class="absolute inset-0 bg-white/80 dark:bg-zinc-800/80 backdrop-blur-sm flex items-center justify-center z-10 rounded-3xl">
                                            <div class="text-center">
                                                <div class="w-12 h-12 mx-auto mb-3 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                </div>
                                                <p class="text-sm font-medium text-gray-700 dark:text-zinc-300">Private Interests</p>
                                                <p class="text-xs text-gray-500 dark:text-zinc-400">Personal interests are hidden</p>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        Interests
                                    </h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(is_array($user->profile->interests) ? $user->profile->interests : explode(',', $user->profile->interests) as $interest)
                                            <span class="px-3 py-1.5 bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400 text-sm rounded-full">
                                                {{ trim($interest) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Verification Badges -->
                            @if($user->verifiedBadges && $user->verifiedBadges->count() > 0)
                                <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-6 transition-colors duration-300">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Verified
                                    </h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($user->verifiedBadges as $badge)
                                            <span class="px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 text-sm rounded-full flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ ucfirst(str_replace('_', ' ', $badge->type)) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    @if($showReportModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
             x-data="{ show: @entangle('showReportModal') }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            <div class="bg-white dark:bg-zinc-800 rounded-2xl max-w-md w-full mx-4 p-6"
                 @click.stop
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Report User</h3>
                
                <form wire:submit.prevent="reportUser" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Reason for reporting</label>
                        <select wire:model="reportReason" class="w-full px-4 py-3 bg-gray-50 dark:bg-zinc-700 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Select a reason</option>
                            <option value="inappropriate_content">Inappropriate Content</option>
                            <option value="harassment">Harassment</option>
                            <option value="fake_profile">Fake Profile</option>
                            <option value="spam">Spam</option>
                            <option value="underage">Underage User</option>
                            <option value="other">Other</option>
                        </select>
                        @error('reportReason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Additional details (optional)</label>
                        <textarea wire:model="reportDescription" 
                                  rows="3"
                                  placeholder="Provide any additional context..."
                                  class="w-full px-4 py-3 bg-gray-50 dark:bg-zinc-700 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"></textarea>
                        @error('reportDescription') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="button" wire:click="closeReportModal"
                                class="flex-1 px-4 py-2 bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-zinc-300 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-zinc-600 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium transition-colors">
                            Report User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" 
             class="fixed bottom-4 right-4 bg-gray-900 dark:bg-zinc-100 text-white dark:text-zinc-900 px-6 py-3 rounded-xl shadow-lg z-50"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('match'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" 
             class="fixed bottom-4 right-4 bg-gradient-to-r from-pink-500 to-purple-600 text-white px-6 py-3 rounded-xl shadow-lg z-50"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100">
            {{ session('match') }}
        </div>
    @endif

    <!-- Custom Styles -->
    <style>
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
        
        /* Privacy blur effects */
        .privacy-blur {
            filter: blur(8px);
            -webkit-filter: blur(8px);
            transform: scale(1.1);
        }
        
        /* Privacy overlay animations */
        .privacy-overlay {
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        
        /* Privacy lock icon animation */
        @keyframes privacy-pulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.05);
            }
        }
        
        .privacy-lock {
            animation: privacy-pulse 2s ease-in-out infinite;
        }
        
        /* Privacy card hover effects */
        .privacy-card:hover .privacy-overlay {
            background: rgba(255, 255, 255, 0.9);
        }
        
        .dark .privacy-card:hover .privacy-overlay {
            background: rgba(39, 39, 42, 0.9);
        }
    </style>
</div>