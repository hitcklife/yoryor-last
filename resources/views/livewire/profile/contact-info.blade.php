<div class="relative max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-screen flex flex-col justify-center">
    <!-- Mobile Progress Only -->
    <div class="lg:hidden mb-8">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-semibold text-gray-700">Step 2 of 9</span>
            <span class="text-sm font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded-full">22%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 shadow-inner">
            <div class="bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 h-3 rounded-full transition-all duration-700 shadow-sm" style="width: 22%"></div>
        </div>
    </div>

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50/90 border border-red-200 rounded-xl">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if ($errorMessage)
        <div class="mb-6 p-4 bg-red-50/90 border border-red-200 rounded-xl">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-red-800 font-medium">{{ $errorMessage }}</p>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="submit" class="space-y-8">
        @if(!$hasEmail)
            <!-- Email Input (Only if missing) -->
            <div class="animate-fade-in" style="animation-delay: 100ms">
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                    Email Address (Optional)
                </label>
                <input type="email" 
                       id="email"
                       wire:model.live="email" 
                       placeholder="Enter your email address"
                       class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white/70 hover:bg-white hover:border-purple-300 @error('email') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror transition-all duration-300">
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">We'll use this for account recovery and important updates</p>
            </div>
        @endif

        @if(!$hasPhone)
            <!-- Phone Number (Only if missing) -->
            <div class="animate-fade-in" style="animation-delay: 200ms">
                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                    Phone Number (Optional)
                </label>
                <input type="tel" 
                       id="phone"
                       wire:model.live="phone" 
                       placeholder="Enter your phone number"
                       class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white/70 hover:bg-white hover:border-purple-300 @error('phone') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror transition-all duration-300">
                @error('phone')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">For secure account access</p>
            </div>
        @endif
        
        @if($hasPhone && $hasEmail)
            <!-- This shouldn't show as we redirect in mount, but just in case -->
            <div class="text-center py-8">
                <p class="text-lg text-gray-600">You already have both email and phone on file!</p>
            </div>
        @endif

        <!-- Privacy Note -->
        <div class="animate-fade-in" style="animation-delay: 300ms">
            <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl">
                <p class="text-sm text-purple-800 font-medium flex items-start">
                    <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Your contact information is kept private and will never be shared without your permission
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-6 space-y-4 animate-fade-in" style="animation-delay: 400ms">
            <!-- Continue Button -->
            <button type="submit"
                    class="w-full bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 text-white py-4 px-8 rounded-2xl font-bold text-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 hover:-translate-y-1 disabled:hover:translate-y-0"
                    wire:loading.attr="disabled"
                    wire:target="submit">
                <span wire:loading.remove wire:target="submit" class="flex items-center justify-center">
                    Continue to About You
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </span>
                <span wire:loading wire:target="submit" class="inline-flex items-center justify-center">
                    <svg class="animate-spin mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Saving...</span>
                </span>
            </button>

            <!-- Skip Button -->
            <button type="button"
                    wire:click="skip"
                    class="w-full bg-gray-100 text-gray-700 py-3 px-6 rounded-2xl font-semibold hover:bg-gray-200 transition-all duration-300">
                Skip for now
            </button>
        </div>
    </form>

    <!-- Back Link -->
    <div class="mt-6 text-center">
        <a href="{{ route('onboard.basic-info') }}" 
           class="text-sm text-gray-600 hover:text-gray-700 font-medium">
            ← Back to Basic Info
        </a>
    </div>
</div>