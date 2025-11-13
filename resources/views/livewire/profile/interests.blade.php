<div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-screen flex flex-col justify-center">
    <!-- Mobile Progress Only -->
    <div class="lg:hidden mb-8">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-semibold text-gray-700">Step 5 of 9</span>
            <span class="text-sm font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded-full">56%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 shadow-inner">
            <div class="bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 h-3 rounded-full transition-all duration-700 shadow-sm" style="width: 56%"></div>
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

    <div class="space-y-8">
        <!-- Interest Selection Counter -->
        <div class="text-center animate-fade-in" style="animation-delay: 100ms">
            <div class="inline-flex items-center px-4 py-2 bg-purple-100 border border-purple-200 rounded-full">
                <span class="text-sm font-semibold text-purple-800">
                    Selected: <span class="font-bold text-purple-900">{{ count($selectedInterests) }}</span>/8 interests
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-600">Choose what you enjoy doing</p>
        </div>

        <!-- Interests Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 animate-fade-in" style="animation-delay: 200ms">
            @foreach($availableInterests as $key => $label)
            <div wire:click="toggleInterest('{{ $key }}')"
                 class="relative cursor-pointer">
                <div class="p-4 border-2 rounded-2xl transition-all duration-300
                    @if(in_array($key, $selectedInterests))
                        border-purple-500 bg-gradient-to-br from-purple-50 to-pink-50
                    @else
                        border-gray-200 hover:border-purple-300 bg-white/60
                    @endif">

                    <div class="text-center">
                        <!-- Interest Icons -->
                        @if($key === 'gaming')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                        @elseif($key === 'dancing')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                </svg>
                            </div>
                        @elseif($key === 'music')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                        </svg>
                                    </div>
                        @elseif($key === 'movies')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                        @elseif($key === 'reading')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </div>
                        @elseif($key === 'sports')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                        @elseif($key === 'cooking')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                                        </svg>
                                    </div>
                        @elseif($key === 'travel')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                        @elseif($key === 'photography')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                        @elseif($key === 'art')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v11a3 3 0 11-6 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 8v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V8a1 1 0 011-1h2a1 1 0 011 1z"/>
                                        </svg>
                                    </div>
                        @elseif($key === 'technology')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                        @elseif($key === 'fitness')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </div>
                        @elseif($key === 'nature')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                        </svg>
                                    </div>
                        @elseif($key === 'nightlife')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                        </svg>
                                    </div>
                        @elseif($key === 'pets')
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </div>
                        @else
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full flex items-center justify-center transition-colors
                                {{ in_array($key, $selectedInterests) ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </div>
                                @endif

                        <span class="text-sm font-medium">{{ $label }}</span>
                            </div>

                    <!-- Selection Indicator -->
                    @if(in_array($key, $selectedInterests))
                        <div class="absolute -top-1 -right-1 bg-purple-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-lg">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @endif
                        </div>
                    </div>
                    @endforeach
                </div>

        @error('selectedInterests')
            <div class="text-center">
                <p class="text-sm text-red-600">{{ $message }}</p>
            </div>
        @enderror

        <!-- Continue Button -->
        <div class="pt-6 animate-fade-in" style="animation-delay: 300ms">
            <button type="button" 
                    wire:click="submit"
                    class="w-full bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 text-white py-4 px-8 rounded-2xl font-bold text-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 hover:-translate-y-1 disabled:hover:translate-y-0"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    @if(!$this->canContinue) disabled @endif>
                <span wire:loading.remove wire:target="submit" class="flex items-center justify-center">
                    Continue to Photos
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
                    Please select 1-8 interests to continue
                </p>
            @endif
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6 text-center">
        <a href="{{ route('onboard.preferences') }}" 
           class="text-sm text-gray-600 hover:text-gray-700 font-medium">
            ← Back to Preferences
        </a>
    </div>
</div>