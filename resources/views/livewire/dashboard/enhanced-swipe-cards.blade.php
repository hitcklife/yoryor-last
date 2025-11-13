<!-- Enhanced Swipe Cards Section -->
<div x-data="enhancedSwipeCards()" class="relative">
    
    <!-- Loading State -->
    @if($loading)
        <div class="flex justify-center items-center h-96">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
        </div>
    @endif
    
    <!-- No More Profiles -->
    @if($noMoreProfiles && !$loading)
        <div class="text-center py-16">
            <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('dashboard.no_more_profiles') }}</h3>
            <p class="text-gray-600 mb-6">{{ __('dashboard.find_more_matches') }}</p>
            <button class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-6 py-3 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                {{ __('dashboard.change_preferences') }}
            </button>
        </div>
    @endif
    
    <!-- Card Stack -->
    @if(!$loading && !$noMoreProfiles && $currentUser)
        <div class="relative flex justify-center">
            <!-- Background Cards (for stack effect) -->
            @for($i = 1; $i <= 2; $i++)
                @if(isset($potentialMatches[$currentCardIndex + $i]))
                    <div class="absolute w-full max-w-sm" 
                         style="transform: translateY({{ $i * 4 }}px) scale({{ 1 - ($i * 0.02) }}); z-index: {{ 10 - $i }};">
                        <div class="bg-white/90 backdrop-blur-lg rounded-3xl shadow-lg h-[700px] opacity-{{ 100 - ($i * 20) }} border border-white/20">
                            <img src="{{ $potentialMatches[$currentCardIndex + $i]['profile_photo']['url'] ?? ($potentialMatches[$currentCardIndex + $i]['photos'][0]['url'] ?? '') }}" 
                                 alt="" class="w-full h-2/3 object-cover rounded-t-3xl">
                        </div>
                    </div>
                @endif
            @endfor
            
            <!-- Active Card -->
            <div class="relative w-full max-w-sm z-20" 
                 id="active-card"
                 x-ref="activeCard"
                 @mousedown="startDrag($event)"
                 @touchstart="startDrag($event)">
                
                <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden transform-gpu transition-transform duration-300 border border-white/30"
                     :style="`transform: translateX(${dragX}px) rotate(${rotation}deg)`">
                    
                    <!-- Profile Completion Badge -->
                    <div class="absolute top-4 left-4 z-20">
                        <div class="bg-gradient-to-r from-purple-500/90 to-pink-500/90 backdrop-blur-sm text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                            <div class="flex items-center space-x-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ $currentUser['profile_completion'] }}% Complete</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image Container with Photo Navigation -->
                    <div class="relative h-[450px] group" x-data="{ currentPhotoIndex: 0, photos: @js($currentUser['photos'] ?? []) }">
                        
                        <!-- Main Photo -->
                        <img :src="photos[currentPhotoIndex]?.url || '{{ $currentUser['profile_photo']['url'] ?? '' }}'" 
                             alt="{{ $currentUser['name'] }}"
                             class="w-full h-full object-cover">
                        
                        <!-- Photo Navigation Dots -->
                        @if(count($currentUser['photos'] ?? []) > 1)
                            <div class="absolute top-4 left-1/2 transform -translate-x-1/2 flex space-x-1 z-10">
                                @foreach($currentUser['photos'] as $index => $photo)
                                    <button @click="currentPhotoIndex = {{ $index }}"
                                            class="w-16 h-1 rounded-full transition-all duration-300 backdrop-blur-sm"
                                            :class="currentPhotoIndex === {{ $index }} ? 'bg-white shadow-lg' : 'bg-white/40'">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                        
                        <!-- Photo Navigation Areas -->
                        @if(count($currentUser['photos'] ?? []) > 1)
                            <div class="absolute inset-0 flex">
                                <!-- Previous Photo Area -->
                                <div class="flex-1 cursor-pointer" 
                                     @click="currentPhotoIndex = Math.max(0, currentPhotoIndex - 1)"></div>
                                <!-- Next Photo Area -->  
                                <div class="flex-1 cursor-pointer" 
                                     @click="currentPhotoIndex = Math.min(photos.length - 1, currentPhotoIndex + 1)"></div>
                            </div>
                        @endif
                        
                        <!-- Gradient Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/20"></div>
                        
                        <!-- Online Status -->
                        @if($currentUser['is_online'])
                            <div class="absolute top-4 right-4 flex items-center space-x-2 bg-green-500/90 backdrop-blur-sm text-white px-3 py-1 rounded-full text-xs font-medium shadow-lg">
                                <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                                <span>{{ __('dashboard.online_now') }}</span>
                            </div>
                        @endif
                        
                        <!-- Basic Info Overlay -->
                        <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                            <h2 class="text-2xl font-bold mb-2 leading-tight flex items-center">
                                {{ $currentUser['name'] }}@if($currentUser['age']), {{ $currentUser['age'] }}@endif
                                @if($currentUser['cultural']['religion'] ?? false)
                                    <span class="ml-2 px-2 py-1 text-xs bg-white/20 backdrop-blur-sm rounded-full">
                                        @if($currentUser['cultural']['religion'] === 'Islam')
                                            ☪️
                                        @elseif($currentUser['cultural']['religion'] === 'Christianity')  
                                            ✝️
                                        @else
                                            🙏
                                        @endif
                                    </span>
                                @endif
                            </h2>
                            
                            @if($currentUser['occupation'])
                                <div class="flex items-center space-x-1 mb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2h8zM8 14v.01M12 14v.01M16 14v.01"/>
                                    </svg>
                                    <span class="text-sm opacity-90">{{ $currentUser['occupation'] }}</span>
                                </div>
                            @endif
                            
                            <div class="flex items-center space-x-4 text-sm opacity-90">
                                <div class="flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>{{ $currentUser['distance'] }} {{ __('dashboard.km_away') }}</span>
                                </div>
                                @if($currentUser['location'])
                                    <span>• {{ $currentUser['location'] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Enhanced Profile Information -->
                    <div class="p-6 space-y-4 bg-white/95 backdrop-blur-sm">
                        
                        <!-- Bio -->
                        @if($currentUser['bio'])
                            <div class="text-sm text-gray-700 leading-relaxed">
                                <p>"{{ Str::limit($currentUser['bio'], 120) }}"</p>
                            </div>
                        @endif
                        
                        <!-- Cultural & Religious Info -->
                        @if($currentUser['cultural'])
                            <div class="flex flex-wrap gap-2">
                                @if($currentUser['cultural']['religion'])
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        🕌 {{ ucfirst($currentUser['cultural']['religion']) }}
                                        @if($currentUser['cultural']['religious_practice'])
                                            • {{ ucfirst($currentUser['cultural']['religious_practice']) }}
                                        @endif
                                    </span>
                                @endif
                                
                                @if($currentUser['cultural']['dietary_preferences'])
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        🍽️ {{ ucfirst($currentUser['cultural']['dietary_preferences']) }}
                                    </span>
                                @endif
                            </div>
                        @endif
                        
                        <!-- Languages -->
                        @if($currentUser['cultural']['languages'] ?? false)
                            <div class="flex flex-wrap gap-1">
                                @foreach($currentUser['cultural']['languages'] as $language)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        @if($language === 'uzbek')
                                            🇺🇿 O'zbek
                                        @elseif($language === 'russian')
                                            🇷🇺 Русский  
                                        @elseif($language === 'english')
                                            🇺🇸 English
                                        @elseif($language === 'turkish')
                                            🇹🇷 Türkçe
                                        @else
                                            🌍 {{ ucfirst($language) }}
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        
                        <!-- Family & Career Info Row -->
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Marriage Intent -->
                            @if($currentUser['family']['marriage_timeline'] ?? false)
                                <div class="bg-gradient-to-br from-pink-50 to-rose-50 rounded-lg p-3 border border-pink-100">
                                    <div class="flex items-center space-x-2 text-pink-700">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs font-medium">Marriage: {{ ucfirst($currentUser['family']['marriage_timeline']) }}</span>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Education -->
                            @if($currentUser['career']['education'] ?? false)
                                <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-lg p-3 border border-indigo-100">
                                    <div class="flex items-center space-x-2 text-indigo-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                        </svg>
                                        <span class="text-xs font-medium">{{ ucfirst($currentUser['career']['education']) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Lifestyle Tags -->
                        @if($currentUser['lifestyle'])
                            <div class="flex flex-wrap gap-2">
                                @if($currentUser['lifestyle']['smoking'] === 'Never')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        🚫 Non-smoker
                                    </span>
                                @endif
                                
                                @if($currentUser['lifestyle']['drinking'])
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                        {{ $currentUser['lifestyle']['drinking'] === 'Never' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                        @if($currentUser['lifestyle']['drinking'] === 'Never')
                                            🚫 No alcohol
                                        @elseif($currentUser['lifestyle']['drinking'] === 'Socially')
                                            🍷 Social drinker
                                        @else
                                            🍷 {{ ucfirst($currentUser['lifestyle']['drinking']) }}
                                        @endif
                                    </span>
                                @endif
                                
                                @if($currentUser['lifestyle']['exercise'] && $currentUser['lifestyle']['exercise'] !== 'Never')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        🏃 Active
                                    </span>
                                @endif
                                
                                @if($currentUser['lifestyle']['height'])
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        📏 {{ $currentUser['lifestyle']['height'] }}cm
                                    </span>
                                @endif
                            </div>
                        @endif
                        
                        <!-- Children Preference -->
                        @if($currentUser['family']['children_preference'] ?? false)
                            <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-3 border border-purple-100">
                                <div class="flex items-center space-x-2 text-purple-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    <span class="text-sm font-medium">👶 {{ ucfirst($currentUser['family']['children_preference']) }}</span>
                                    @if($currentUser['family']['current_children'] > 0)
                                        <span class="text-xs bg-purple-200 px-2 py-1 rounded-full">Has {{ $currentUser['family']['current_children'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Drag Indicators -->
                    <div class="absolute inset-0 pointer-events-none">
                        <!-- Like Indicator -->
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 transition-opacity duration-200"
                             :class="dragX > 50 ? 'opacity-100' : 'opacity-0'"
                             :style="`transform: translate(-50%, -50%) scale(${Math.min(1.5, 1 + (dragX / 200))})`">
                            <div class="w-32 h-32 bg-green-500/90 backdrop-blur-sm rounded-full flex items-center justify-center text-white shadow-2xl border-4 border-white">
                                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Pass Indicator -->
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 transition-opacity duration-200"
                             :class="dragX < -50 ? 'opacity-100' : 'opacity-0'"
                             :style="`transform: translate(-50%, -50%) scale(${Math.min(1.5, 1 + (Math.abs(dragX) / 200))})`">
                            <div class="w-32 h-32 bg-red-500/90 backdrop-blur-sm rounded-full flex items-center justify-center text-white shadow-2xl border-4 border-white">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Super Like Indicator -->
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 transition-opacity duration-200"
                             :class="dragY < -80 ? 'opacity-100' : 'opacity-0'"
                             :style="`transform: translate(-50%, -50%) scale(${Math.min(1.5, 1 + (Math.abs(dragY) / 150))})`">
                            <div class="w-32 h-32 bg-blue-500/90 backdrop-blur-sm rounded-full flex items-center justify-center text-white shadow-2xl border-4 border-white">
                                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Enhanced Action Buttons -->
        <div class="flex justify-center items-center space-x-4 mt-8">
            <!-- Rewind Button -->
            <button wire:click="rewind"
                    class="w-12 h-12 bg-gradient-to-br from-yellow-100 to-amber-100 hover:from-yellow-200 hover:to-amber-200 rounded-full flex items-center justify-center transition-all duration-300 transform hover:scale-110 shadow-xl group backdrop-blur-sm border border-yellow-200">
                <svg class="w-6 h-6 text-yellow-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"/>
                </svg>
            </button>
            
            <!-- Pass Button -->
            <button wire:click="passUser" 
                    @click="animateSwipe('left')"
                    class="w-16 h-16 bg-gradient-to-br from-red-100 to-rose-100 hover:from-red-200 hover:to-rose-200 rounded-full flex items-center justify-center transition-all duration-300 transform hover:scale-110 shadow-2xl group backdrop-blur-sm border border-red-200">
                <svg class="w-8 h-8 text-red-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            
            <!-- Super Like Button -->
            <button wire:click="superLikeUser"
                    @click="animateSwipe('up')"
                    class="w-12 h-12 bg-gradient-to-br from-blue-100 to-indigo-100 hover:from-blue-200 hover:to-indigo-200 rounded-full flex items-center justify-center transition-all duration-300 transform hover:scale-110 shadow-xl group backdrop-blur-sm border border-blue-200">
                <svg class="w-6 h-6 text-blue-600 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
            </button>
            
            <!-- Like Button -->
            <button wire:click="likeUser" 
                    @click="animateSwipe('right')"
                    class="w-16 h-16 bg-gradient-to-br from-green-100 to-emerald-100 hover:from-green-200 hover:to-emerald-200 rounded-full flex items-center justify-center transition-all duration-300 transform hover:scale-110 shadow-2xl group backdrop-blur-sm border border-green-200">
                <svg class="w-8 h-8 text-green-600 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                </svg>
            </button>
            
            <!-- Boost Button -->
            <button class="w-12 h-12 bg-gradient-to-br from-purple-100 to-pink-100 hover:from-purple-200 hover:to-pink-200 rounded-full flex items-center justify-center transition-all duration-300 transform hover:scale-110 shadow-xl group backdrop-blur-sm border border-purple-200">
                <svg class="w-6 h-6 text-purple-600 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    @endif
    
    <!-- Enhanced Match Modal -->
    @if($matchFound && $matchedUser)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-md"
             x-show="@entangle('matchFound')"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            
            <!-- Particles Background -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute w-2 h-2 bg-pink-400 rounded-full animate-ping" style="top: 20%; left: 10%; animation-delay: 0s;"></div>
                <div class="absolute w-1 h-1 bg-purple-400 rounded-full animate-ping" style="top: 40%; left: 80%; animation-delay: 0.5s;"></div>
                <div class="absolute w-3 h-3 bg-yellow-400 rounded-full animate-ping" style="top: 60%; left: 20%; animation-delay: 1s;"></div>
                <div class="absolute w-1 h-1 bg-blue-400 rounded-full animate-ping" style="top: 30%; left: 70%; animation-delay: 1.5s;"></div>
            </div>
            
            <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl max-w-md w-full mx-4 overflow-hidden border border-white/30"
                 x-transition:enter="transition ease-out duration-500 delay-200"
                 x-transition:enter-start="opacity-0 scale-90 rotate-3"
                 x-transition:enter-end="opacity-100 scale-100 rotate-0">
                
                <!-- Match Header -->
                <div class="relative bg-gradient-to-br from-pink-500 via-purple-600 to-indigo-600 text-white p-8 text-center overflow-hidden">
                    <div class="absolute inset-0 bg-black/20"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent animate-pulse"></div>
                    
                    <div class="relative">
                        <div class="w-20 h-20 mx-auto mb-4 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center animate-bounce">
                            <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold mb-2">{{ __('dashboard.its_a_match') }}</h2>
                        <p class="text-white/90">{{ __('dashboard.you_liked_each_other') }}</p>
                    </div>
                </div>
                
                <!-- Matched User Info -->
                <div class="p-6 bg-white/95 backdrop-blur-sm">
                    <div class="flex items-center space-x-4 mb-6">
                        <img src="{{ $matchedUser['profile_photo']['thumbnail'] ?? '' }}" 
                             alt="{{ $matchedUser['name'] }}"
                             class="w-16 h-16 rounded-full object-cover border-4 border-pink-200 shadow-lg">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $matchedUser['name'] }}</h3>
                            <p class="text-gray-600">{{ $matchedUser['occupation'] ?? $matchedUser['location'] }}</p>
                            @if($matchedUser['profile_completion'] ?? 0)
                                <p class="text-xs text-purple-600 font-medium">{{ $matchedUser['profile_completion'] }}% Profile Complete</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <button wire:click="sendMessage"
                                class="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white py-3 px-6 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                            {{ __('dashboard.send_message') }}
                        </button>
                        <button wire:click="closeMatchModal"
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-6 rounded-xl font-medium transition-colors backdrop-blur-sm">
                            {{ __('dashboard.keep_swiping') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function enhancedSwipeCards() {
    return {
        dragX: 0,
        dragY: 0,
        rotation: 0,
        
        animateSwipe(direction) {
            console.log('Animating swipe:', direction);
            // Simple animation without complex DOM manipulation
            this.dragX = 0;
            this.dragY = 0;
            this.rotation = 0;
        }
    }
}
</script>
