<div class="relative min-h-screen overflow-hidden">
    <!-- Background Image -->
    <div class="absolute inset-0">
        <img
            src="{{asset('assets/images/538664-married-couple.jpg')}}"
            alt="Beautiful landscape"
            class="w-full h-full object-cover"
        >
        <!-- Dark overlay for better text readability -->
        <div class="absolute inset-0 bg-black/30"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 min-h-screen flex flex-col items-center justify-center px-6 py-12">
        <!-- Logo and Main Text -->
        <div class="text-center mb-16">
            <!-- Logo -->
            <div class="mb-8" x-data="{ scale: 1 }"
                 x-init="setInterval(() => { scale = scale === 1 ? 1.05 : 1 }, 2000)"
                 :style="`transform: scale(${scale}); transition: transform 0.3s ease-in-out`">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full shadow-2xl mb-6 backdrop-blur-sm border border-white/20">
                    <span class="text-2xl font-bold text-white">Y</span>
                </div>
            </div>

            <!-- Main Title -->
            <h1 class="text-6xl md:text-8xl font-bold text-white mb-6 tracking-tight drop-shadow-2xl">
                <span class="bg-gradient-to-r from-pink-400 via-purple-400 to-indigo-400 bg-clip-text text-transparent">
                    YorYor
                </span>
            </h1>

            <!-- Subtitle -->
            <p class="text-xl md:text-2xl text-white/90 font-light mb-4 drop-shadow-lg">
                Uzbek Dating Platform
            </p>

            <!-- Coming Soon Badge -->
            <div class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-md rounded-full border border-white/20 shadow-xl">
                <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                <span class="text-white/90 font-medium">Coming Soon</span>
            </div>
        </div>

        <!-- Features Grid (Optional - can be hidden on mobile) -->
        <div class="hidden md:grid grid-cols-3 gap-6 max-w-4xl mx-auto mb-16">
            <div class="group">
                <div class="bg-white/5 backdrop-blur-md rounded-2xl p-6 border border-white/10 shadow-xl hover:bg-white/10 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-500 rounded-xl flex items-center justify-center mb-4 mx-auto shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-white mb-2 text-center">Meaningful Connections</h4>
                    <p class="text-white/70 text-sm text-center">Find someone special</p>
                </div>
            </div>

            <div class="group">
                <div class="bg-white/5 backdrop-blur-md rounded-2xl p-6 border border-white/10 shadow-xl hover:bg-white/10 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-xl flex items-center justify-center mb-4 mx-auto shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-white mb-2 text-center">Cultural Respect</h4>
                    <p class="text-white/70 text-sm text-center">Honoring traditions</p>
                </div>
            </div>

            <div class="group">
                <div class="bg-white/5 backdrop-blur-md rounded-2xl p-6 border border-white/10 shadow-xl hover:bg-white/10 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mb-4 mx-auto shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-white mb-2 text-center">Safe & Secure</h4>
                    <p class="text-white/70 text-sm text-center">Privacy focused</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Subscription Form - Fixed Bottom Right -->
    <div class="fixed bottom-6 right-6 z-20"
         x-data="{ expanded: false, showForm: true }"
         x-show="showForm"
         @hide-success.window="setTimeout(() => { showForm = true; expanded = false; }, 3000)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-full"
         x-transition:enter-end="opacity-100 transform translate-x-0">

        <div class="relative">
            <!-- Compact Button -->
            <div x-show="!expanded"
                 @click="expanded = true"
                 class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/20 shadow-2xl cursor-pointer hover:bg-white/20 transition-all duration-300 transform hover:scale-105">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                    </div>
                    <div class="text-white">
                        <p class="font-medium text-sm">Get Notified</p>
                        <p class="text-xs text-white/70">When we launch</p>
                    </div>
                </div>
            </div>

            <!-- Expanded Form -->
            <div x-show="expanded"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 shadow-2xl min-w-[300px]">

                <!-- Close button -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-white font-semibold">Get Notified</h3>
                    <button @click="expanded = false"
                            class="text-white/60 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="subscribe" class="space-y-4">
                    <div class="relative">
                        <input
                            type="email"
                            wire:model="email"
                            placeholder="Enter your email"
                            required
                            class="w-full px-4 py-3 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300"
                        >
                        @error('email')
                            <p class="text-red-300 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>Notify Me 🚀</span>
                        <span wire:loading>Subscribing...</span>
                    </button>
                </form>

                <!-- Success Message -->
                @if($showSuccess)
                    <div class="mt-4 p-3 bg-green-500/20 border border-green-500/30 rounded-lg text-green-300 text-sm text-center"
                         x-data
                         x-init="setTimeout(() => { $wire.showSuccess = false; showForm = false; }, 3000)">
                        ✨ Thank you! We'll notify you when YorYor launches!
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Floating particles effect -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-white/20 rounded-full animate-ping" style="animation-delay: 0s;"></div>
        <div class="absolute top-3/4 right-1/4 w-1 h-1 bg-pink-300/30 rounded-full animate-ping" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-3/4 w-1.5 h-1.5 bg-purple-300/20 rounded-full animate-ping" style="animation-delay: 2s;"></div>
    </div>
</div>
