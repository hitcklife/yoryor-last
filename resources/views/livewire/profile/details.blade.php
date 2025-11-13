<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 relative">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%239C92AC" fill-opacity="0.05"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-30"></div>

    <div class="relative max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-screen flex flex-col justify-center">
        <!-- Mobile Progress -->
        <div class="lg:hidden mb-8">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-semibold text-gray-700">Step 7 of 9</span>
                <span class="text-sm font-medium text-rose-600 bg-rose-100 px-2 py-1 rounded-full">78%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-3 shadow-inner">
                <div class="bg-gradient-to-r from-rose-500 via-pink-500 to-purple-600 h-3 rounded-full transition-all duration-700 shadow-sm" style="width: 78%"></div>
            </div>
        </div>

        <!-- Welcome Section -->
        <div class="text-center mb-10 animate-fade-in">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-rose-400 to-pink-500 rounded-2xl mb-6 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h1 class="text-4xl lg:text-5xl font-bold mb-4">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-rose-600 via-pink-600 to-purple-600">Complete Your Profile</span>
            </h1>
            <p class="text-xl text-gray-600 max-w-md mx-auto leading-relaxed">
                Tell us more about yourself to help find your perfect matches ✨
            </p>
        </div>

        <!-- Main Content Card -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl ring-1 ring-white/20 p-8 sm:p-10 transition-all duration-300 hover:shadow-2xl border border-white/30">
            @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl shadow-sm">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-800 font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if ($errorMessage)
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl shadow-sm">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-800 font-medium">{{ $errorMessage }}</p>
                    </div>
                </div>
            @endif

            <form wire:submit.prevent="submit" class="space-y-8">
            <!-- Bio Section -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">About me</h2>
                <div>
                    <label for="bio" class="block text-sm font-semibold text-gray-700 mb-3">
                        Write a brief bio (Optional)
                    </label>
                    <textarea id="bio"
                              wire:model="bio"
                              placeholder="Tell people about yourself, your interests, what you're looking for..."
                              rows="4"
                              maxlength="500"
                              class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 bg-white/70 hover:bg-white hover:border-rose-300 resize-none @error('bio') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror transition-all duration-300"></textarea>
                    <div class="flex justify-between mt-2">
                        @error('bio')
                            <p class="text-sm text-red-600 font-medium">{{ $message }}</p>
                        @else
                            <p class="text-sm text-gray-500">Help others get to know you better</p>
                        @enderror
                        <p class="text-sm text-gray-400 font-medium">{{ strlen($bio) }}/500</p>
                    </div>
                </div>
            </div>

            <!-- Interests Section -->
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">My interests</h2>
                <p class="text-center text-gray-600 mb-6">Select up to 8 interests that represent you (Optional)</p>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($availableInterests as $interest)
                        <button type="button"
                                wire:click="toggleInterest('{{ $interest }}')"
                                class="px-4 py-3 text-sm font-semibold border-2 rounded-2xl transition-all duration-300 transform hover:scale-105
                                       {{ in_array($interest, $interests)
                                          ? 'border-rose-500 bg-gradient-to-br from-rose-50 to-pink-50 text-rose-700 shadow-lg shadow-rose-500/20'
                                          : 'border-gray-200 bg-white text-gray-700 hover:border-rose-300 hover:bg-rose-50' }}
                                       {{ count($interests) >= 8 && !in_array($interest, $interests)
                                          ? 'opacity-50 cursor-not-allowed'
                                          : 'cursor-pointer hover:shadow-lg' }}">
                            {{ $interest }}
                        </button>
                    @endforeach
                </div>

                @if(count($interests) > 0)
                    <div class="mt-4 p-3 bg-rose-50 border border-rose-200 rounded-xl">
                        <p class="text-center text-sm text-rose-800 font-medium">
                            Selected {{ count($interests) }}/8 interests ✨
                        </p>
                    </div>
                @endif

                @error('interests')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Work & Location -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Work</h2>
                    <div>
                        <label for="profession" class="block text-sm font-semibold text-gray-700 mb-3">
                            Profession (Optional)
                        </label>
                        <input type="text"
                               id="profession"
                               wire:model="profession"
                               placeholder="e.g., Software Engineer"
                               maxlength="100"
                               class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 bg-white/70 hover:bg-white hover:border-rose-300 @error('profession') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror transition-all duration-300">
                        @error('profession')
                            <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Location</h2>
                    <div>
                        <label for="city" class="block text-sm font-semibold text-gray-700 mb-3">
                            City (Optional)
                        </label>
                        <input type="text"
                               id="city"
                               wire:model="city"
                               placeholder="e.g., Tashkent"
                               maxlength="100"
                               class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 bg-white/70 hover:bg-white hover:border-rose-300 @error('city') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror transition-all duration-300">
                        @error('city')
                            <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Complete Profile Button -->
            <div class="pt-8 space-y-4">
                <button type="submit"
                        class="w-full bg-gradient-to-r from-rose-500 via-pink-500 to-purple-600 text-white py-4 px-8 rounded-2xl font-bold text-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 hover:-translate-y-1 disabled:hover:translate-y-0"
                        wire:loading.attr="disabled"
                        wire:target="submit">
                    <span wire:loading.remove wire:target="submit" class="flex items-center justify-center">
                        Complete Profile
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </span>
                    <span wire:loading wire:target="submit" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Completing...
                    </span>
                </button>

                <!-- Skip Button -->
                <button type="button"
                        wire:click="skipForNow"
                        class="w-full bg-gray-100 text-gray-700 py-4 px-8 rounded-2xl font-semibold hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition-all duration-300 hover:-translate-y-1">
                    I'll complete this later
                </button>
            </div>
        </form>
        <!-- Success Preview -->
        @if(count($interests) > 0 || $bio || $profession || $city)
            <div class="mt-6 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6">
                <h3 class="font-bold text-green-900 mb-4 flex items-center justify-center">
                    <svg class="w-6 h-6 mr-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Profile Preview ✨
                </h3>
                <div class="text-sm text-green-800 space-y-2">
                    @if($bio)
                        <p><strong>Bio:</strong> {{ Str::limit($bio, 100) }}</p>
                    @endif
                    @if(count($interests) > 0)
                        <p><strong>Interests:</strong> {{ implode(', ', array_slice($interests, 0, 3)) }}{{ count($interests) > 3 ? '...' : '' }}</p>
                    @endif
                    @if($profession)
                        <p><strong>Work:</strong> {{ $profession }}</p>
                    @endif
                    @if($city)
                        <p><strong>Location:</strong> {{ $city }}</p>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Back Button -->
    <div class="mt-6 text-center">
        <a href="{{ route('onboard.photos') }}"
           class="text-sm text-gray-600 hover:text-gray-700 font-medium">
            ← Back to Photos
        </a>
    </div>
</div>