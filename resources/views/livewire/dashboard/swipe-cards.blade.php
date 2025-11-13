<!-- Swipe Cards Section -->
<div x-data="swipeCards()" class="relative">
    
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
                        <div class="bg-white rounded-3xl shadow-lg h-[600px] opacity-{{ 100 - ($i * 20) }}">
                            <img src="{{ $potentialMatches[$currentCardIndex + $i]['profile_photo']['url'] ?? ($potentialMatches[$currentCardIndex + $i]['photos'][0]['url'] ?? '') }}" 
                                 alt="" class="w-full h-full object-cover rounded-3xl">
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
                
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden transform-gpu transition-transform duration-300"
                     :style="`transform: translateX(${dragX}px) rotate(${rotation}deg)`">
                    
                    <!-- Image Container with Photo Navigation -->
                    <div class="relative h-[600px] group" x-data="{ currentPhotoIndex: 0, photos: @js($currentUser['photos'] ?? []) }">
                        
                        <!-- Main Photo -->
                        <img :src="photos[currentPhotoIndex]?.url || '{{ $currentUser['profile_photo']['url'] ?? '' }}'" 
                             alt="{{ $currentUser['name'] }}"
                             class="w-full h-full object-cover">
                        
                        <!-- Photo Navigation Dots -->
                        @if(count($currentUser['photos'] ?? []) > 1)
                            <div class="absolute top-4 left-0 right-0 flex justify-center space-x-1 z-10">
                                @foreach($currentUser['photos'] as $index => $photo)
                                    <button @click="currentPhotoIndex = {{ $index }}"
                                            class="w-20 h-1 rounded-full transition-all duration-300"
                                            :class="currentPhotoIndex === {{ $index }} ? 'bg-white' : 'bg-white bg-opacity-40'">
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
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                        
                        <!-- Online Status -->
                        @if($currentUser['is_online'])
                            <div class="absolute top-6 right-6 flex items-center space-x-2 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                                <span>{{ __('dashboard.online_now') }}</span>
                            </div>
                        @endif
                        
                        <!-- User Info Overlay -->
                        <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                            <div class="flex items-end justify-between">
                                <div class="flex-1">
                                    <h2 class="text-3xl font-bold mb-2 leading-tight">
                                        {{ $currentUser['name'] }}@if($currentUser['age']), {{ $currentUser['age'] }}@endif
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
                                
                                <!-- Quick Info Button -->
                                <button class="w-10 h-10 bg-white bg-opacity-20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-opacity-30 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Drag Indicators -->
                        <div class="absolute inset-0 pointer-events-none">
                            <!-- Like Indicator -->
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 transition-opacity duration-200"
                                 :class="dragX > 50 ? 'opacity-100' : 'opacity-0'"
                                 :style="`transform: translate(-50%, -50%) scale(${Math.min(1.5, 1 + (dragX / 200))})`">
                                <div class="w-32 h-32 bg-green-500 rounded-full flex items-center justify-center text-white shadow-2xl">
                                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Pass Indicator -->
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 transition-opacity duration-200"
                                 :class="dragX < -50 ? 'opacity-100' : 'opacity-0'"
                                 :style="`transform: translate(-50%, -50%) scale(${Math.min(1.5, 1 + (Math.abs(dragX) / 200))})`">
                                <div class="w-32 h-32 bg-red-500 rounded-full flex items-center justify-center text-white shadow-2xl">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex justify-center items-center space-x-4 mt-8">
            <!-- Rewind Button -->
            <button wire:click="rewind"
                    class="w-12 h-12 bg-yellow-100 hover:bg-yellow-200 rounded-full flex items-center justify-center transition-all duration-300 transform hover:scale-110 shadow-lg group">
                <svg class="w-6 h-6 text-yellow-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"/>
                </svg>
            </button>
            
            <!-- Pass Button -->
            <button wire:click="passUser" 
                    @click="animateSwipe('left')"
                    class="w-16 h-16 bg-red-100 hover:bg-red-200 rounded-full flex items-center justify-center transition-all duration-300 transform hover:scale-110 shadow-xl group">
                <svg class="w-8 h-8 text-red-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            
            <!-- Super Like Button -->
            <button wire:click="superLikeUser"
                    @click="animateSwipe('up')"
                    class="w-12 h-12 bg-blue-100 hover:bg-blue-200 rounded-full flex items-center justify-center transition-all duration-300 transform hover:scale-110 shadow-lg group">
                <svg class="w-6 h-6 text-blue-600 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
            </button>
            
            <!-- Like Button -->
            <button wire:click="likeUser" 
                    @click="animateSwipe('right')"
                    class="w-16 h-16 bg-green-100 hover:bg-green-200 rounded-full flex items-center justify-center transition-all duration-300 transform hover:scale-110 shadow-xl group">
                <svg class="w-8 h-8 text-green-600 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                </svg>
            </button>
            
            <!-- Boost Button -->
            <button class="w-12 h-12 bg-purple-100 hover:bg-purple-200 rounded-full flex items-center justify-center transition-all duration-300 transform hover:scale-110 shadow-lg group">
                <svg class="w-6 h-6 text-purple-600 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    @endif
    
    <!-- Match Modal -->
    @if($matchFound && $matchedUser)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
             x-show="@entangle('matchFound')"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 overflow-hidden"
                 x-transition:enter="transition ease-out duration-300 delay-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <!-- Match Header -->
                <div class="relative bg-gradient-to-br from-pink-500 via-purple-600 to-indigo-600 text-white p-8 text-center">
                    <div class="absolute inset-0 bg-black bg-opacity-20"></div>
                    <div class="relative">
                        <div class="w-20 h-20 mx-auto mb-4 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold mb-2">{{ __('dashboard.its_a_match') }}</h2>
                        <p class="text-white text-opacity-90">{{ __('dashboard.you_liked_each_other') }}</p>
                    </div>
                </div>
                
                <!-- Matched User Info -->
                <div class="p-6">
                    <div class="flex items-center space-x-4 mb-6">
                        <img src="{{ $matchedUser['profile_photo']['thumbnail'] ?? '' }}" 
                             alt="{{ $matchedUser['name'] }}"
                             class="w-16 h-16 rounded-full object-cover border-4 border-pink-200">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $matchedUser['name'] }}</h3>
                            <p class="text-gray-600">{{ $matchedUser['occupation'] ?? $matchedUser['location'] }}</p>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <button wire:click="sendMessage"
                                class="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white py-3 px-6 rounded-xl font-medium transition-all duration-300 transform hover:scale-105">
                            {{ __('dashboard.send_message') }}
                        </button>
                        <button wire:click="closeMatchModal"
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-6 rounded-xl font-medium transition-colors">
                            {{ __('dashboard.keep_swiping') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function swipeCards() {
    return {
        dragX: 0,
        dragY: 0,
        rotation: 0,
        isDragging: false,
        startX: 0,
        startY: 0,
        
        startDrag(e) {
            this.isDragging = true;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            this.startX = clientX;
            this.startY = clientY;
            
            document.addEventListener('mousemove', this.drag.bind(this));
            document.addEventListener('mouseup', this.endDrag.bind(this));
            document.addEventListener('touchmove', this.drag.bind(this));
            document.addEventListener('touchend', this.endDrag.bind(this));
        },
        
        drag(e) {
            if (!this.isDragging) return;
            
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            this.dragX = clientX - this.startX;
            this.dragY = clientY - this.startY;
            this.rotation = this.dragX * 0.1;
        },
        
        endDrag() {
            this.isDragging = false;
            
            // Auto-swipe thresholds
            if (Math.abs(this.dragX) > 100) {
                if (this.dragX > 0) {
                    @this.likeUser();
                } else {
                    @this.passUser();
                }
                this.animateSwipe(this.dragX > 0 ? 'right' : 'left');
            } else if (this.dragY < -150) {
                @this.superLikeUser();
                this.animateSwipe('up');
            } else {
                // Snap back
                this.dragX = 0;
                this.dragY = 0;
                this.rotation = 0;
            }
            
            document.removeEventListener('mousemove', this.drag);
            document.removeEventListener('mouseup', this.endDrag);
            document.removeEventListener('touchmove', this.drag);
            document.removeEventListener('touchend', this.endDrag);
        },
        
        animateSwipe(direction) {
            const card = this.$refs.activeCard;
            if (!card) return;
            
            let translateX = 0;
            let translateY = 0;
            let rotate = 0;
            
            switch(direction) {
                case 'right':
                    translateX = window.innerWidth;
                    rotate = 30;
                    break;
                case 'left':
                    translateX = -window.innerWidth;
                    rotate = -30;
                    break;
                case 'up':
                    translateY = -window.innerHeight;
                    break;
            }
            
            card.style.transform = `translateX(${translateX}px) translateY(${translateY}px) rotate(${rotate}deg)`;
            card.style.opacity = '0';
            
            setTimeout(() => {
                this.dragX = 0;
                this.dragY = 0;
                this.rotation = 0;
                if (card) {
                    card.style.transform = '';
                    card.style.opacity = '';
                }
            }, 300);
        }
    }
}
</script>
