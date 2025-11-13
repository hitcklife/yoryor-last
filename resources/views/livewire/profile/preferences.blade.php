<div class="relative max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-screen flex flex-col justify-center">
    <!-- Mobile Progress Only -->
    <div class="lg:hidden mb-8">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-semibold text-gray-700">Step 4 of 9</span>
            <span class="text-sm font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded-full">44%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 shadow-inner">
            <div class="bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 h-3 rounded-full transition-all duration-700 shadow-sm" style="width: 44%"></div>
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
        <!-- Looking For -->
        <div class="animate-fade-in" style="animation-delay: 100ms">
            <label class="block text-sm font-semibold text-gray-700 mb-4">What are you looking for?</label>
            <div class="grid grid-cols-2 gap-3">
                @foreach([
                    'casual' => ['label' => 'Casual Dating', 'icon' => 'smile'],
                    'serious' => ['label' => 'Serious Relationship', 'icon' => 'heart'],
                    'friendship' => ['label' => 'Friendship', 'icon' => 'users'],
                    'open' => ['label' => 'Open to All', 'icon' => 'question']
                ] as $value => $option)
                <label class="relative cursor-pointer">
                    <input type="radio" 
                           name="lookingFor"
                           wire:model.live="lookingFor" 
                           value="{{ $value }}" 
                           class="sr-only peer">
                    <div class="p-4 text-center border-2 rounded-2xl transition-all duration-300 peer-checked:border-purple-500 peer-checked:bg-gradient-to-br peer-checked:from-purple-50 peer-checked:to-pink-50 border-gray-200 hover:border-purple-300 bg-white/60">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center mb-2 transition-colors
                                {{ $lookingFor === $value ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                @if($option['icon'] === 'smile')
                                    @if($lookingFor === $value)
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5c0 .83-.67 1.5-1.5 1.5S7 17.33 7 16.5 7.67 15 8.5 15s1.5.67 1.5 1.5zm6.5 1.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm1-5H14c-.55 0-1-.45-1-1s.45-1 1-1h3.5c.55 0 1 .45 1 1s-.45 1-1 1z"/>
                                            <circle cx="8.5" cy="9.5" r="1.5"/>
                                            <circle cx="15.5" cy="9.5" r="1.5"/>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                @elseif($option['icon'] === 'heart')
                                    @if($lookingFor === $value)
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    @endif
                                @elseif($option['icon'] === 'users')
                                    @if($lookingFor === $value)
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    @endif
                                @else
                                    @if($lookingFor === $value)
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                            <span class="text-sm font-medium">{{ $option['label'] }}</span>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
            @error('lookingFor')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Bio Section -->
        <div class="animate-fade-in" style="animation-delay: 200ms">
            <label for="bio" class="block text-sm font-semibold text-gray-700 mb-2">
                About You (Optional)
            </label>
            <textarea id="bio"
                      wire:model.live="bio" 
                      placeholder="Share your interests, what makes you unique..."
                      rows="4"
                      maxlength="500"
                      class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white/70 hover:bg-white hover:border-purple-300 transition-all duration-300 resize-none @error('bio') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"></textarea>
            <div class="flex justify-between mt-2">
                @error('bio')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @else
                    <p class="text-xs text-gray-500">
                        @if(empty($bio))
                            Minimum 20 characters if you add a bio
                        @elseif(strlen($bio) < 20)
                            Need {{ 20 - strlen($bio) }} more characters
                        @else
                            Looking good!
                        @endif
                    </p>
                @enderror
                <p class="text-xs text-gray-400">{{ strlen($bio) }}/500</p>
            </div>
        </div>

        <!-- Continue Button -->
        <div class="pt-6 animate-fade-in" style="animation-delay: 300ms">
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 text-white py-4 px-8 rounded-2xl font-bold text-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 hover:-translate-y-1 disabled:hover:translate-y-0"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    @if(!$this->canContinue) disabled @endif>
                <span wire:loading.remove wire:target="submit" class="flex items-center justify-center">
                    Continue to Interests
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

            @if(!$this->canContinue)
                <p class="mt-3 text-center text-xs text-gray-500">
                    @if(empty($lookingFor))
                        Please select what you're looking for
                    @elseif(!empty($bio) && strlen($bio) < 20)
                        Bio needs at least 20 characters or leave it empty
                    @endif
                </p>
            @endif
        </div>
    </form>

    <!-- Back Link -->
    <div class="mt-6 text-center">
        <a href="{{ route('onboard.about-you') }}" 
           class="text-sm text-gray-600 hover:text-gray-700 font-medium">
            ← Back to About You
        </a>
    </div>
</div>