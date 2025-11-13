<div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50">
    <!-- Header Section -->
    <div class="relative bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 text-white shadow-2xl">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('profile.enhance') }}" class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center hover:bg-white/30 transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold">🌟 Lifestyle</h1>
                        <p class="text-white/80 text-sm">Share your lifestyle and habits</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form wire:submit.prevent="save" class="space-y-8">
            
            <!-- Personal Habits -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="text-2xl mr-3">🚭</span>
                    Personal Habits
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="smoking_habit" class="block text-sm font-medium text-gray-900 mb-2">Smoking</label>
                        <select wire:model="smoking_habit" id="smoking_habit" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose smoking habit</option>
                            <option value="never">❌ Never</option>
                            <option value="socially">🎉 Socially</option>
                            <option value="regularly">😬 Regularly</option>
                            <option value="trying_to_quit">💪 Trying to Quit</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="drinking_habit" class="block text-sm font-medium text-gray-900 mb-2">Drinking</label>
                        <select wire:model="drinking_habit" id="drinking_habit" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose drinking habit</option>
                            <option value="never">🚫 Never</option>
                            <option value="socially">🎉 Socially</option>
                            <option value="occasionally">⭐ Occasionally</option>
                            <option value="regularly">🍷 Regularly</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Fitness & Health -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="text-2xl mr-3">💪</span>
                    Fitness & Health
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="exercise_frequency" class="block text-sm font-medium text-gray-900 mb-2">Exercise Frequency</label>
                        <select wire:model="exercise_frequency" id="exercise_frequency" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose frequency</option>
                            <option value="never">😴 Never</option>
                            <option value="rarely">😔 Rarely</option>
                            <option value="1_2_times_week">🏃 1-2 times/week</option>
                            <option value="3_4_times_week">💪 3-4 times/week</option>
                            <option value="daily">🏆 Daily</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sleep Schedule</label>
                        <input type="text" wire:model="sleep_schedule" 
                               placeholder="e.g., 11 PM - 7 AM"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                    </div>
                </div>
            </div>

            <!-- Physical Measurements -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="text-2xl mr-3">📏</span>
                    Physical Measurements
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Height (cm)</label>
                        <input type="number" wire:model="height" min="140" max="220" 
                               placeholder="e.g., 175"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg) - Optional</label>
                        <input type="number" wire:model="weight" min="40" max="200" step="0.1"
                               placeholder="e.g., 70"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                    </div>
                </div>
            </div>

            <!-- Hobbies & Interests -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="text-2xl mr-3">🎨</span>
                    Hobbies & Interests
                </h3>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @php
                        $hobbyOptions = [
                            ['code' => 'reading', 'name' => 'Reading', 'icon' => '📚'],
                            ['code' => 'cooking', 'name' => 'Cooking', 'icon' => '🍳'],
                            ['code' => 'travel', 'name' => 'Travel', 'icon' => '✈️'],
                            ['code' => 'sports', 'name' => 'Sports', 'icon' => '⚽'],
                            ['code' => 'music', 'name' => 'Music', 'icon' => '🎵'],
                            ['code' => 'movies', 'name' => 'Movies', 'icon' => '🎬'],
                            ['code' => 'gaming', 'name' => 'Gaming', 'icon' => '🎮'],
                            ['code' => 'art_design', 'name' => 'Art & Design', 'icon' => '🎨'],
                            ['code' => 'photography', 'name' => 'Photography', 'icon' => '📸'],
                            ['code' => 'hiking', 'name' => 'Hiking', 'icon' => '🥾'],
                            ['code' => 'dancing', 'name' => 'Dancing', 'icon' => '💃'],
                            ['code' => 'meditation', 'name' => 'Meditation', 'icon' => '🧘'],
                        ];
                    @endphp
                    
                    @foreach($hobbyOptions as $hobby)
                        <button type="button" 
                                wire:click="toggleHobby('{{ $hobby['code'] }}')"
                                class="group flex items-center justify-center p-4 rounded-2xl border-2 transition-all duration-300 transform hover:scale-105 {{ in_array($hobby['code'], $hobbies) ? 'border-green-500 bg-gradient-to-br from-green-50 to-emerald-50 text-green-700 shadow-lg' : 'border-gray-300 bg-white text-gray-600 hover:border-green-400 hover:shadow-md' }}">
                            <span class="text-xl mr-2 group-hover:scale-110 transition-transform">{{ $hobby['icon'] }}</span>
                            <span class="text-sm font-semibold">{{ $hobby['name'] }}</span>
                            @if(in_array($hobby['code'], $hobbies))
                                <svg class="w-4 h-4 ml-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Diet & Pets -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="text-2xl mr-3">🍽️</span>
                        Diet
                    </h3>
                    
                    <div>
                        <label for="diet_preference" class="block text-sm font-medium text-gray-900 mb-2">Dietary Preference</label>
                        <select wire:model="diet_preference" id="diet_preference" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose diet</option>
                            <option value="everything">🍽️ Everything</option>
                            <option value="vegetarian">🥕 Vegetarian</option>
                            <option value="vegan">🌱 Vegan</option>
                            <option value="halal">☪️ Halal</option>
                            <option value="kosher">✡️ Kosher</option>
                            <option value="pescatarian">🐟 Pescatarian</option>
                            <option value="keto">🥩 Keto</option>
                        </select>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="text-2xl mr-3">🐱</span>
                        Pets
                    </h3>
                    
                    <div>
                        <label for="pet_preference" class="block text-sm font-medium text-gray-900 mb-2">Pet Preference</label>
                        <select wire:model="pet_preference" id="pet_preference" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose preference</option>
                            <option value="love_pets">🐶 Love Pets</option>
                            <option value="have_pets">🐱 Have Pets</option>
                            <option value="allergic_to_pets">🤧 Allergic to Pets</option>
                            <option value="dont_like_pets">🚫 Don't Like Pets</option>
                            <option value="no_preference">🤷 No Preference</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-center pt-8">
                <button type="submit" 
                        class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-4 px-12 rounded-2xl transition-all duration-300 transform hover:scale-105 shadow-2xl flex items-center space-x-3">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Save Lifestyle Preferences</span>
                </button>
            </div>

        </form>
    </div>
</div>
