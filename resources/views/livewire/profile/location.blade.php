<div class="relative max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-screen flex flex-col justify-center">
    <!-- Mobile Progress Only -->
    <div class="lg:hidden mb-8">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-semibold text-gray-700">Step 7 of 9</span>
            <span class="text-sm font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded-full">78%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 shadow-inner">
            <div class="bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 h-3 rounded-full transition-all duration-700 shadow-sm" style="width: 78%"></div>
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
        <!-- Country Selection with Search -->
        <div class="animate-fade-in" style="animation-delay: 100ms" 
             x-data="{
                open: false,
                search: '',
                selectedCountry: @js($countryId ? optional($allCountries->firstWhere('id', $countryId))->flag . ' ' . optional($allCountries->firstWhere('id', $countryId))->name : ''),
                selectCountry(id, display) {
                    this.selectedCountry = display;
                    @this.set('countryId', id);
                    this.open = false;
                    this.search = '';
                }
             }">
            <label for="country-search" class="block text-sm font-semibold text-gray-700 mb-2">
                Country
            </label>
            
            <!-- Custom Searchable Select -->
            <div class="relative">
                <button type="button"
                        @click="open = !open"
                        class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white/70 hover:bg-white hover:border-purple-300 transition-all duration-300 text-left flex items-center justify-between @error('countryId') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                    <span x-text="selectedCountry ? selectedCountry : 'Select your country'" 
                          :class="{'text-gray-400': !selectedCountry}"></span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" 
                         :class="{'rotate-180': open}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown -->
                <div x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute z-50 w-full mt-2 bg-white rounded-2xl shadow-xl border border-gray-200 max-h-96 overflow-hidden"
                     style="display: none;">
                    
                    <!-- Search Input -->
                    <div class="sticky top-0 p-3 bg-white border-b border-gray-100">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" 
                                   x-model="search"
                                   @click.stop
                                   placeholder="Search countries..."
                                   class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                        </div>
                    </div>

                    <!-- Countries List -->
                    <div class="overflow-y-auto max-h-64">
                        @foreach($allCountries as $countryOption)
                            <div x-show="!search || @js(strtolower($countryOption->name)).includes(search.toLowerCase())"
                                 @click="selectCountry({{ $countryOption->id }}, @js($countryOption->flag . ' ' . $countryOption->name))"
                                 class="px-4 py-3 hover:bg-purple-50 cursor-pointer transition-colors flex items-center space-x-2 {{ $countryId == $countryOption->id ? 'bg-purple-100 text-purple-900 font-medium' : '' }}">
                                <span class="text-lg">{{ $countryOption->flag }}</span>
                                <span>{{ $countryOption->name }}</span>
                                @if($countryId == $countryOption->id)
                                    <svg class="w-4 h-4 text-purple-600 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            @error('countryId')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            
            @if($countryId)
                <div class="mt-2 flex items-center text-xs text-gray-500">
                    <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Country selected
                </div>
            @endif
        </div>

        <!-- State/Region Input (shown after country selection) -->
        @if($countryId)
        <div class="animate-fade-in" style="animation-delay: 200ms">
            <label for="state" class="block text-sm font-semibold text-gray-700 mb-2">
                State / Region
            </label>
            @if(count($this->states) > 0)
                <div class="relative">
                    <select id="state"
                            wire:model.live="state" 
                            class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white/70 hover:bg-white hover:border-purple-300 transition-all duration-300 appearance-none @error('state') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                        <option value="">Select your state</option>
                        @foreach($this->states as $stateOption)
                            <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            @else
                <input type="text" 
                       id="state"
                       wire:model.live="state" 
                       placeholder="Enter your state or region"
                       class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white/70 hover:bg-white hover:border-purple-300 transition-all duration-300 @error('state') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
            @endif
            @error('state')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        @endif

        <!-- City Input (shown after state selection) -->
        @if($state)
        <div class="animate-fade-in" style="animation-delay: 300ms">
            <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">
                City
            </label>
            <input type="text" 
                   id="city"
                   wire:model.live="city" 
                   placeholder="Enter your city"
                   class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white/70 hover:bg-white hover:border-purple-300 transition-all duration-300 @error('city') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
            @error('city')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        @endif

        <!-- Location Preview -->
        @if($countryId && $state && $city)
        <div class="animate-fade-in" style="animation-delay: 400ms">
            <div class="p-4 bg-purple-50 rounded-2xl border border-purple-200">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Your Location</h3>
                        <p class="text-xs text-gray-600">
                            @php
                                $selectedCountry = $allCountries->firstWhere('id', $countryId);
                            @endphp
                            {{ $city }}, {{ $state }}@if($selectedCountry), {{ $selectedCountry->flag }} {{ $selectedCountry->name }}@endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Location Detection Info -->
        @if(!empty($detectedLocation))
        <div class="animate-fade-in" style="animation-delay: 100ms">
            <div class="p-3 bg-green-50 border border-green-200 rounded-xl">
                <p class="text-sm text-green-800 font-medium flex items-start">
                    <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    We've detected your approximate location. Please verify it's correct.
                </p>
            </div>
        </div>
        @endif

        <!-- Continue Button -->
        <div class="pt-6 animate-fade-in" style="animation-delay: 500ms">
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 text-white py-4 px-8 rounded-2xl font-bold text-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 hover:-translate-y-1 disabled:hover:translate-y-0"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    @if(!$this->canContinue) disabled @endif>
                <span wire:loading.remove wire:target="submit" class="flex items-center justify-center">
                    Continue to Preview
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
                    @if(!$countryId)
                        Please select your country
                    @elseif(!$state)
                        Please enter your state or region
                    @elseif(!$city)
                        Please enter your city
                    @endif
                </p>
            @endif
        </div>
    </form>

    <!-- Back Link -->
    <div class="mt-6 text-center">
        <a href="{{ route('onboard.photos') }}" 
           class="text-sm text-gray-600 hover:text-gray-700 font-medium">
            ← Back to Photos
        </a>
    </div>
</div>