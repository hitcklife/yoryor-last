<div class="relative max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-screen flex flex-col justify-center">
    <!-- Mobile Progress Only -->
    <div class="lg:hidden mb-8">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-semibold text-gray-700">Step 3 of 9</span>
            <span class="text-sm font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded-full">33%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 shadow-inner">
            <div class="bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 h-3 rounded-full transition-all duration-700 shadow-sm" style="width: 33%"></div>
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
        <!-- Relationship Status -->
        <div class="animate-fade-in" style="animation-delay: 100ms">
            <label class="block text-sm font-semibold text-gray-700 mb-4">Relationship Status</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach(['single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced', 'widowed' => 'Widowed', 'separated' => 'Separated'] as $value => $label)
                <label class="relative cursor-pointer">
                    <input type="radio" 
                           name="relationshipStatus"
                           wire:model.live="relationshipStatus" 
                           value="{{ $value }}" 
                           class="sr-only peer">
                    <div class="p-4 text-center border-2 rounded-2xl transition-all duration-300 peer-checked:border-purple-500 peer-checked:bg-gradient-to-br peer-checked:from-purple-50 peer-checked:to-pink-50 border-gray-200 hover:border-purple-300 bg-white/60">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-colors
                                {{ $relationshipStatus === $value ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                @if($value === 'single')
                                    @if($relationshipStatus === $value)
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    @endif
                                @elseif($value === 'married')
                                    @if($relationshipStatus === $value)
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    @endif
                                @elseif($value === 'divorced')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                @elseif($value === 'widowed')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                @endif
                            </div>
                            <span class="text-sm font-medium">{{ $label }}</span>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
            @error('relationshipStatus')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Occupation Type -->
        <div class="animate-fade-in" style="animation-delay: 200ms">
            <label class="block text-sm font-semibold text-gray-700 mb-4">Occupation Type</label>
            <div class="grid grid-cols-2 gap-3">
                @foreach(['employee' => 'Employee', 'student' => 'Student', 'business' => 'Business', 'unemployed' => 'Unemployed'] as $value => $label)
                <label class="relative cursor-pointer">
                    <input type="radio" 
                           name="occupationType"
                           wire:model.live="occupationType" 
                           value="{{ $value }}" 
                           class="sr-only peer">
                    <div class="p-4 text-center border-2 rounded-2xl transition-all duration-300 peer-checked:border-purple-500 peer-checked:bg-gradient-to-br peer-checked:from-purple-50 peer-checked:to-pink-50 border-gray-200 hover:border-purple-300 bg-white/60">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-colors
                                {{ $occupationType === $value ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-purple-100 text-purple-600' }}">
                                @if($value === 'employee')
                                    @if($occupationType === $value)
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0h6a2 2 0 012 2v3m-2 0v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                                        </svg>
                                    @endif
                                @elseif($value === 'student')
                                    @if($occupationType === $value)
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                                        </svg>
                                    @endif
                                @elseif($value === 'business')
                                    @if($occupationType === $value)
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    @endif
                                @else
                                    @if($occupationType === $value)
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                            <span class="text-sm font-medium">{{ $label }}</span>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
            @error('occupationType')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Profession (Optional) -->
        <div class="animate-fade-in" style="animation-delay: 300ms">
            <label for="profession" class="block text-sm font-semibold text-gray-700 mb-2">
                Profession (Optional)
            </label>
            <input type="text" 
                   id="profession"
                   wire:model.live="profession" 
                   placeholder="e.g., Software Engineer, Teacher, Doctor"
                   maxlength="100"
                   class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white/70 hover:bg-white hover:border-purple-300 transition-all duration-300">
            @error('profession')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-gray-500">This helps others understand what you do</p>
        </div>

        <!-- Continue Button -->
        <div class="pt-6 animate-fade-in" style="animation-delay: 400ms">
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 text-white py-4 px-8 rounded-2xl font-bold text-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 hover:-translate-y-1 disabled:hover:translate-y-0"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    @if(!$this->canContinue) disabled @endif>
                <span wire:loading.remove wire:target="submit" class="flex items-center justify-center">
                    Continue to Preferences
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
                    Please select your relationship status and occupation type
                </p>
            @endif
        </div>
    </form>

    <!-- Back Link -->
    <div class="mt-6 text-center">
        <a href="{{ route('onboard.contact-info') }}" 
           class="text-sm text-gray-600 hover:text-gray-700 font-medium">
            ← Back to Contact Info
        </a>
    </div>
</div>