<div class="relative max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-screen flex flex-col justify-center">
        <!-- Mobile Progress Only -->
        <div class="lg:hidden mb-8">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-semibold text-gray-700">Step 1 of 9</span>
                <span class="text-sm font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded-full">11%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-3 shadow-inner">
                <div class="bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 h-3 rounded-full transition-all duration-700 shadow-sm" style="width: 11%"></div>
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
                <!-- Gender Selection with Icons -->
                <div class="animate-fade-in" style="animation-delay: 100ms">
                    <label class="block text-sm font-semibold text-gray-700 mb-4">Gender</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio" 
                                   name="gender" 
                                   value="male" 
                                   wire:model="gender"
                                   class="sr-only peer">
                            <div class="p-6 text-center border-2 rounded-2xl transition-all duration-300 peer-checked:border-blue-500 peer-checked:bg-gradient-to-br peer-checked:from-blue-50 peer-checked:to-blue-100 border-gray-200 hover:border-blue-300 bg-white/60 backdrop-blur-sm">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-3 transition-all duration-300 peer-checked:bg-gradient-to-br peer-checked:from-blue-500 peer-checked:to-blue-600 peer-checked:text-white bg-blue-100 text-blue-600">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <span class="font-bold text-lg">Male</span>
                                </div>
                            </div>
                        </label>

                        <label class="relative cursor-pointer">
                            <input type="radio" 
                                   name="gender" 
                                   value="female" 
                                   wire:model="gender"
                                   class="sr-only peer">
                            <div class="p-6 text-center border-2 rounded-2xl transition-all duration-300 peer-checked:border-pink-500 peer-checked:bg-gradient-to-br peer-checked:from-pink-50 peer-checked:to-pink-100 border-gray-200 hover:border-pink-300 bg-white/60 backdrop-blur-sm">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-3 transition-all duration-300 peer-checked:bg-gradient-to-br peer-checked:from-pink-500 peer-checked:to-pink-600 peer-checked:text-white bg-pink-100 text-pink-600">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <span class="font-bold text-lg">Female</span>
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('gender')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Name Fields -->
                <div class="animate-fade-in" style="animation-delay: 200ms">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="firstName" class="block text-sm font-semibold text-gray-700 mb-2">
                                First Name
                            </label>
                            <input type="text"
                                   id="firstName"
                                   wire:model.live="firstName"
                                   placeholder="Enter your first name"
                                   class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white/70 hover:bg-white hover:border-purple-300 @error('firstName') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror transition-all duration-300">
                            @error('firstName')
                                <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="lastName" class="block text-sm font-semibold text-gray-700 mb-2">
                                Last Name
                            </label>
                            <input type="text"
                                   id="lastName"
                                   wire:model.live="lastName"
                                   placeholder="Enter your last name"
                                   class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white/70 hover:bg-white hover:border-purple-300 @error('lastName') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror transition-all duration-300">
                            @error('lastName')
                                <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Date of Birth -->
                <div class="animate-fade-in" style="animation-delay: 300ms">
                    <label for="dateOfBirth" class="block text-sm font-semibold text-gray-700 mb-2">
                        Date of Birth
                    </label>
                    <div class="relative">
                        <!-- Custom Date Picker -->
                        <div x-data="datePicker()" class="relative z-10">
                            <input type="text"
                                   x-model="displayValue"
                                   placeholder="Select your birthday"
                                   readonly
                                   @click="openCalendar()"
                                   class="w-full px-4 py-4 text-lg border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white/70 hover:bg-white hover:border-purple-300 @error('dateOfBirth') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror transition-all duration-300 cursor-pointer">

                            <!-- Calendar Icon -->
                            <div class="absolute right-4 top-1/2 transform -translate-y-1/2">
                                <button type="button"
                                        @click="openCalendar()"
                                        class="p-2 text-purple-600 hover:text-purple-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Calendar Dropdown -->
                            <div x-show="isOpen" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 @click.away="closeCalendar()"
                                 class="absolute top-full left-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-gray-200 z-50 overflow-hidden">
                                
                                <!-- Calendar Header -->
                                <div class="bg-gradient-to-r from-purple-500 to-pink-500 px-6 py-4 text-white">
                                    <div class="flex items-center justify-between">
                                        <button type="button" @click="previousMonth()" 
                                                class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                            </svg>
                                        </button>
                                        <div class="text-center">
                                            <div class="text-lg font-semibold cursor-pointer hover:bg-white/20 px-2 py-1 rounded" @click="showYearPicker = !showYearPicker" x-text="monthName + ' ' + year"></div>
                                        </div>
                                        <button type="button" @click="nextMonth()" 
                                                class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <!-- Year Picker -->
                                    <div x-show="showYearPicker" x-transition class="mt-4 max-h-32 overflow-y-auto">
                                        <div class="grid grid-cols-4 gap-2">
                                            <template x-for="yearOption in availableYears" :key="yearOption">
                                                <button type="button" @click="selectYear(yearOption)" 
                                                        :class="yearOption === currentYear ? 'bg-white text-purple-600' : 'bg-white/20 text-white hover:bg-white/30'"
                                                        class="px-2 py-1 rounded text-sm transition-colors" 
                                                        x-text="yearOption"></button>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Calendar Days -->
                                <div class="p-4">
                                    <!-- Calendar Grid -->
                                    <div class="grid grid-cols-7 gap-1">
                                        <template x-for="(dayObj, index) in days" :key="index">
                                            <div x-show="dayObj !== null"
                                                 @click="dayObj && !dayObj.isDisabled && selectDate(dayObj)"
                                                 :class="{
                                                     'bg-purple-500 text-white': dayObj && dayObj.isSelected,
                                                     'bg-purple-100 text-purple-700': dayObj && dayObj.isToday && !dayObj.isSelected,
                                                     'text-gray-400 cursor-not-allowed': dayObj && dayObj.isDisabled,
                                                     'hover:bg-gray-100 cursor-pointer': dayObj && !dayObj.isDisabled && !dayObj.isSelected
                                                 }"
                                                 class="w-10 h-10 flex items-center justify-center text-sm font-medium rounded-lg transition-colors">
                                                <span x-text="dayObj ? dayObj.day : ''"></span>
                                            </div>
                                            <div x-show="dayObj === null" class="w-10 h-10"></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @error('dateOfBirth')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        <p class="mt-2 text-xs text-gray-500">You must be at least 18 years old</p>
                    </div>
                </div>

                <!-- Continue Button -->
                <div class="pt-6 animate-fade-in" style="animation-delay: 400ms">
                    <button type="submit"
                            class="w-full bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 text-white py-4 px-8 rounded-2xl font-bold text-xl disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 hover:-translate-y-1 disabled:hover:translate-y-0"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                            @if(!$this->canContinue) disabled @endif>
                        <span wire:loading.remove wire:target="submit" class="flex items-center justify-center">
                            Continue to Contact Info
                            <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </span>
                        <span wire:loading wire:target="submit" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating your profile...
                        </span>
                    </button>
                </div>
            </form>
</div>
