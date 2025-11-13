<!-- Professional Cultural Background Page -->
<div>
    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex transition-colors duration-300">

        <!-- Left Navigation Sidebar - Full Mode -->
        <x-navigation-sidebar mode="full" />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 p-6 overflow-y-auto">
                <div class="max-w-6xl mx-auto">
                    <!-- Professional Header Section -->
                    <div class="relative bg-gradient-to-r from-purple-600 via-pink-600 to-indigo-600 rounded-3xl p-8 text-white mb-8 overflow-hidden shadow-2xl">
                        <!-- Decorative elements -->
                        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-32 translate-x-32"></div>
                        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full translate-y-24 -translate-x-24"></div>

                        <div class="relative flex items-center justify-between">
                            <div class="flex items-center space-x-6">
                                <a href="{{ route('profile.enhance') }}" class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center hover:bg-white/30 transition-all duration-300 transform hover:scale-110 shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                </a>
                                <div>
                                    <h1 class="text-4xl font-bold mb-2">🏛️ Cultural Background</h1>
                                    <p class="text-white/90 text-lg">Share your heritage and cultural values</p>
                                    <p class="text-pink-200 text-sm mt-1">Help us find culturally compatible matches for you</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="max-w-4xl mx-auto">
        <form wire:submit.prevent="save" class="space-y-8">
            
                        <!-- Heritage & Beliefs Section -->
                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-8 transition-colors duration-300">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mr-4">
                                    <span class="text-2xl">🌍</span>
                                </div>
                                Heritage & Beliefs
                            </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Ethnicity -->
                    <div>
                        <label for="ethnicity" class="block text-sm font-medium text-gray-900 mb-2">Ethnicity</label>
                        <select wire:model="ethnicity" id="ethnicity" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose ethnicity</option>
                            <option value="uzbek">🇺🇿 Uzbek</option>
                            <option value="russian">🇷🇺 Russian</option>
                            <option value="tajik">🇹🇯 Tajik</option>
                            <option value="kazakh">🇰🇿 Kazakh</option>
                            <option value="tatar">🏴 Tatar</option>
                            <option value="kyrgyz">🇰🇬 Kyrgyz</option>
                            <option value="korean">🇰🇷 Korean</option>
                            <option value="other">🌍 Other</option>
                        </select>
                    </div>
                    
                    <!-- Religion -->
                    <div>
                        <label for="religion" class="block text-sm font-medium text-gray-900 mb-2">Religion</label>
                        <select wire:model="religion" id="religion" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose religion</option>
                            <option value="islam">☪️ Islam</option>
                            <option value="christianity">✝️ Christianity</option>
                            <option value="judaism">✡️ Judaism</option>
                            <option value="buddhism">☸️ Buddhism</option>
                            <option value="agnostic">🤔 Agnostic</option>
                            <option value="atheist">🔬 Atheist</option>
                            <option value="spiritual">✨ Spiritual</option>
                            <option value="other">🌟 Other</option>
                            <option value="prefer_not_to_say">🤐 Prefer not to say</option>
                        </select>
                    </div>
                    
                    <!-- Religious Practice -->
                    <div>
                        <label for="religiousness_level" class="block text-sm font-medium text-gray-900 mb-2">Religious Practice</label>
                        <select wire:model="religiousness_level" id="religiousness_level" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose practice level</option>
                            <option value="very_religious">🕌 Very Religious</option>
                            <option value="religious">📿 Religious</option>
                            <option value="somewhat_religious">⭐ Somewhat Religious</option>
                            <option value="not_religious">🚫 Not Religious</option>
                            <option value="cultural_only">🎭 Cultural Only</option>
                        </select>
                    </div>
                </div>
            </div>

                        <!-- Languages Section -->
                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-8 transition-colors duration-300">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mr-4">
                                    <span class="text-2xl">💬</span>
                                </div>
                                Languages
                            </h3>
                
                <!-- Spoken Languages -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                        <span class="mr-2">🗣️</span>
                        Spoken Languages
                    </h4>
                    <p class="text-sm text-gray-600 mb-4">Select all languages you can speak fluently</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @php
                            $languageOptions = [
                                ['code' => 'uzbek', 'name' => "O'zbekcha", 'flag' => '🇺🇿'],
                                ['code' => 'russian', 'name' => 'Русский', 'flag' => '🇷🇺'],
                                ['code' => 'english', 'name' => 'English', 'flag' => '🇬🇧'],
                                ['code' => 'turkish', 'name' => 'Türkçe', 'flag' => '🇹🇷'],
                                ['code' => 'arabic', 'name' => 'العربية', 'flag' => '🇸🇦'],
                                ['code' => 'tajik', 'name' => 'Тоҷикӣ', 'flag' => '🇹🇯'],
                                ['code' => 'kazakh', 'name' => 'Қазақша', 'flag' => '🇰🇿'],
                                ['code' => 'kyrgyz', 'name' => 'Кыргызча', 'flag' => '🇰🇬'],
                            ];
                        @endphp
                        
                        @foreach($languageOptions as $lang)
                            <button type="button" 
                                    wire:click="toggleSpokenLanguage('{{ $lang['code'] }}')"
                                    class="group flex items-center justify-center p-4 rounded-2xl border-2 transition-all duration-300 transform hover:scale-105 {{ in_array($lang['code'], $spoken_languages) ? 'border-purple-500 bg-gradient-to-br from-purple-50 to-pink-50 text-purple-700 shadow-lg' : 'border-gray-300 bg-white text-gray-600 hover:border-purple-400 hover:shadow-md' }}">
                                <span class="text-xl mr-2 group-hover:scale-110 transition-transform">{{ $lang['flag'] }}</span>
                                <span class="text-sm font-semibold">{{ $lang['name'] }}</span>
                                @if(in_array($lang['code'], $spoken_languages))
                                    <svg class="w-4 h-4 ml-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Native Languages -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                        <span class="mr-2">🏠</span>
                        Native Languages
                    </h4>
                    <p class="text-sm text-gray-600 mb-4">Select your mother tongue(s)</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($languageOptions as $lang)
                            <button type="button" 
                                    wire:click="toggleNativeLanguage('{{ $lang['code'] }}')"
                                    class="group flex items-center justify-center p-4 rounded-2xl border-2 transition-all duration-300 transform hover:scale-105 {{ in_array($lang['code'], $native_languages) ? 'border-blue-500 bg-gradient-to-br from-blue-50 to-cyan-50 text-blue-700 shadow-lg' : 'border-gray-300 bg-white text-gray-600 hover:border-blue-400 hover:shadow-md' }}">
                                <span class="text-xl mr-2 group-hover:scale-110 transition-transform">{{ $lang['flag'] }}</span>
                                <span class="text-sm font-semibold">{{ $lang['name'] }}</span>
                                @if(in_array($lang['code'], $native_languages))
                                    <svg class="w-4 h-4 ml-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Preferred Communication Language -->
                <div>
                    <label for="preferred_communication_language" class="block text-sm font-medium text-gray-900 mb-2">Preferred Communication Language</label>
                    <select wire:model="preferred_communication_language" id="preferred_communication_language" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                        <option value="">Choose preferred language</option>
                        @foreach($languageOptions as $lang)
                            <option value="{{ $lang['code'] }}">{{ $lang['flag'] }} {{ $lang['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

                        <!-- Cultural Events Participation -->
                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-8 transition-colors duration-300">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-600 rounded-2xl flex items-center justify-center mr-4">
                                    <span class="text-2xl">🎭</span>
                                </div>
                                Cultural Events Participation
                            </h3>
                
                    <div>
                        <label for="cultural_events_participation" class="block text-sm font-medium text-gray-900 mb-2">How often do you participate in cultural events?</label>
                        <select wire:model="cultural_events_participation" id="cultural_events_participation" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose frequency</option>
                            <option value="daily">🎭 Daily</option>
                            <option value="weekly">📅 Weekly</option>
                            <option value="monthly">📆 Monthly</option>
                            <option value="occasionally">⭐ Occasionally</option>
                            <option value="never">❌ Never</option>
                        </select>
                    </div>
            </div>

            <!-- Religious Practice -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                            <!-- Religious Practice Advanced -->
                            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-8 transition-colors duration-300">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mr-4">
                                        <span class="text-2xl">🕌</span>
                                    </div>
                                    Religious Practice
                                </h3>
                    
                    <div class="space-y-4">
                        <!-- Mosque Attendance -->
                        <div>
                            <label for="mosque_attendance" class="block text-sm font-medium text-gray-900 mb-2">Mosque Attendance</label>
                            <select wire:model="mosque_attendance" id="mosque_attendance" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                                <option value="">Choose frequency</option>
                                <option value="daily">🌅 Daily</option>
                                <option value="weekly">📅 Weekly</option>
                                <option value="monthly">📆 Monthly</option>
                                <option value="occasionally">⭐ Occasionally</option>
                                <option value="never">❌ Never</option>
                            </select>
                        </div>

                        <!-- Quran Reading -->
                        <div>
                            <label for="quran_reading" class="block text-sm font-medium text-gray-900 mb-2">Quran Reading</label>
                            <select wire:model="quran_reading" id="quran_reading" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                                <option value="">Choose frequency</option>
                                <option value="daily">📖 Daily</option>
                                <option value="weekly">📚 Weekly</option>
                                <option value="monthly">📝 Monthly</option>
                                <option value="occasionally">⭐ Occasionally</option>
                                <option value="never">❌ Never</option>
                            </select>
                        </div>

                        <!-- Beautiful Toggle Options -->
                        <div class="space-y-4 mt-6">
                            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl border border-purple-200">
                                <span class="text-sm font-semibold flex items-center text-gray-900">
                                    <span class="text-lg mr-3">☪️</span>
                                    Observes Ramadan
                                </span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="observes_ramadan" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                </label>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl border border-purple-200">
                                <span class="text-sm font-semibold flex items-center text-gray-900">
                                    <span class="text-lg mr-3">🕌</span>
                                    Halal Lifestyle
                                </span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="halal_lifestyle" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                </label>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl border border-purple-200">
                                <span class="text-sm font-semibold flex items-center text-gray-900">
                                    <span class="text-lg mr-3">🍽️</span>
                                    Prefers Halal Dates
                                </span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="prefers_halal_dates" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                            <!-- Lifestyle Preferences -->
                            <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-8 transition-colors duration-300">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center mr-4">
                                        <span class="text-2xl">🌟</span>
                                    </div>
                                    Lifestyle & Values
                                </h3>
                    
                    <div class="space-y-6">
                        <!-- Lifestyle Type -->
                        <div>
                            <label for="lifestyle_type" class="block text-sm font-medium text-gray-900 mb-2">Lifestyle Type</label>
                            <select wire:model="lifestyle_type" id="lifestyle_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                                <option value="">Choose lifestyle</option>
                                <option value="traditional">🏛️ Traditional</option>
                                <option value="modern">🌍 Modern</option>
                                <option value="mix">⚖️ Mix of Both</option>
                            </select>
                        </div>

                        <!-- Gender Role Views -->
                        <div>
                            <label for="gender_role_views" class="block text-sm font-medium text-gray-900 mb-2">Gender Role Views</label>
                            <select wire:model="gender_role_views" id="gender_role_views" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                                <option value="">Choose view</option>
                                <option value="egalitarian">🤝 Egalitarian</option>
                                <option value="balanced">⚖️ Balanced</option>
                                <option value="traditional">🏛️ Traditional</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

                        <!-- Cultural Depth Section -->
                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-8 transition-colors duration-300">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mr-4">
                                    <span class="text-2xl">🎭</span>
                                </div>
                                Cultural Depth
                            </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Uzbek Region -->
                    <div>
                        <label for="uzbek_region" class="block text-sm font-medium text-gray-900 mb-2">Uzbek Region</label>
                        <select wire:model="uzbek_region" id="uzbek_region" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose region</option>
                            <option value="tashkent">🏛️ Tashkent</option>
                            <option value="samarkand">🕌 Samarkand</option>
                            <option value="bukhara">🏰 Bukhara</option>
                            <option value="andijan">🌸 Andijan</option>
                            <option value="namangan">🌿 Namangan</option>
                            <option value="fergana">🌾 Fergana</option>
                            <option value="khorezm">🏜️ Khorezm</option>
                            <option value="karakalpakstan">🏞️ Karakalpakstan</option>
                            <option value="kashkadarya">⛰️ Kashkadarya</option>
                            <option value="surkhandarya">🌄 Surkhandarya</option>
                            <option value="navoiy">💎 Navoiy</option>
                            <option value="jizzakh">🌻 Jizzakh</option>
                            <option value="sirdaryo">🌊 Sirdaryo</option>
                        </select>
                    </div>
                    
                    <!-- Uzbek Cuisine Knowledge -->
                    <div>
                        <label for="uzbek_cuisine_knowledge" class="block text-sm font-medium text-gray-900 mb-2">Uzbek Cuisine Knowledge</label>
                        <select wire:model="uzbek_cuisine_knowledge" id="uzbek_cuisine_knowledge" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose level</option>
                            <option value="expert">👨‍🍳 Expert</option>
                            <option value="good">😋 Good</option>
                            <option value="basic">🍽️ Basic</option>
                            <option value="none">❓ None</option>
                        </select>
                    </div>
                    
                    <!-- Traditional Clothing Comfort -->
                    <div>
                        <label for="traditional_clothing_comfort" class="block text-sm font-medium text-gray-900 mb-2">Traditional Clothing Comfort</label>
                        <select wire:model="traditional_clothing_comfort" id="traditional_clothing_comfort" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-purple-500 focus:border-purple-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose comfort level</option>
                            <option value="very_comfortable">😊 Very Comfortable</option>
                            <option value="comfortable">👍 Comfortable</option>
                            <option value="neutral">😐 Neutral</option>
                            <option value="uncomfortable">😟 Uncomfortable</option>
                            <option value="very_uncomfortable">😰 Very Uncomfortable</option>
                        </select>
                    </div>
                </div>
            </div>

                        <!-- Professional Save Button -->
                        <div class="flex justify-center pt-8">
                            <button type="submit"
                                    class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-4 px-12 rounded-3xl transition-all duration-300 transform hover:scale-105 shadow-2xl flex items-center space-x-3">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-lg">Save Cultural Background</span>
                            </button>
                        </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
