<!-- Professional Family & Marriage Page -->
<div>
    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex transition-colors duration-300">

        <!-- Left Navigation Sidebar - Full Mode -->
        <x-navigation-sidebar mode="full" />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 p-6 overflow-y-auto">
                <div class="max-w-6xl mx-auto">
                    <!-- Professional Header Section -->
                    <div class="relative bg-gradient-to-r from-pink-600 via-purple-600 to-indigo-600 rounded-3xl p-8 text-white mb-8 overflow-hidden shadow-2xl">
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
                                    <h1 class="text-4xl font-bold mb-2">💑 Family & Marriage</h1>
                                    <p class="text-white/90 text-lg">Share your family goals and marriage intentions</p>
                                    <p class="text-pink-200 text-sm mt-1">Help us find someone with similar family goals</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="max-w-4xl mx-auto">
        <form wire:submit.prevent="save" class="space-y-8">
            
            <!-- Marriage Intentions -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="text-2xl mr-3">💑</span>
                    Marriage Intentions
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="marriage_intention" class="block text-sm font-medium text-gray-900 mb-2">Marriage Intention</label>
                        <select wire:model="marriage_intention" id="marriage_intention" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose intention</option>
                            <option value="seeking_marriage">💍 Seeking Marriage</option>
                            <option value="open_to_marriage">💕 Open to Marriage</option>
                            <option value="not_ready_yet">⏰ Not Ready Yet</option>
                            <option value="undecided">🤔 Undecided</option>
                        </select>
                    </div>
                    
                    @if($marriage_intention === 'seeking_marriage')
                    <div>
                        <label for="marriage_timeline" class="block text-sm font-medium text-gray-900 mb-2">Marriage Timeline</label>
                        <select wire:model="marriage_timeline" id="marriage_timeline" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose timeline</option>
                            <option value="within_6_months">⚡ Within 6 months</option>
                            <option value="within_1_year">📅 Within 1 year</option>
                            <option value="within_2_years">🗓️ Within 2 years</option>
                            <option value="within_5_years">⏳ Within 5 years</option>
                            <option value="no_specific_timeline">♾️ No specific timeline</option>
                        </select>
                    </div>
                    @endif
                </div>
            </div>

                        <!-- Children & Family -->
                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-8 transition-colors duration-300">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mr-4">
                                    <span class="text-2xl">👶</span>
                                </div>
                                Children & Family
                            </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="children_preference" class="block text-sm font-medium text-gray-900 mb-2">Children Preference</label>
                        <select wire:model="children_preference" id="children_preference" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose preference</option>
                            <option value="want_children">👶 Want Children</option>
                            <option value="have_and_want_more">👨‍👩‍👧‍👦 Have & Want More</option>
                            <option value="have_dont_want_more">✅ Have & Don't Want More</option>
                            <option value="dont_want_children">❌ Don't Want Children</option>
                            <option value="undecided">🤔 Undecided</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Children</label>
                        <input type="number" wire:model="current_children" min="0" max="20" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100" placeholder="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Previous Marriages</label>
                        <input type="number" wire:model="previous_marriages" min="0" max="10" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100" placeholder="0">
                    </div>
                </div>
            </div>

                        <!-- Family Values -->
                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-8 transition-colors duration-300">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center mr-4">
                                    <span class="text-2xl">⭐</span>
                                </div>
                                Family Values
                            </h3>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-6">
                    @php
                        $valueOptions = [
                            ['code' => 'close_knit_family', 'name' => 'Close-Knit Family', 'icon' => '👨‍👩‍👧‍👦'],
                            ['code' => 'traditional_values', 'name' => 'Traditional Values', 'icon' => '🏛️'],
                            ['code' => 'family_first', 'name' => 'Family First', 'icon' => '❤️'],
                            ['code' => 'independent', 'name' => 'Independent', 'icon' => '🦋'],
                            ['code' => 'supportive', 'name' => 'Supportive', 'icon' => '🤝'],
                            ['code' => 'respect_for_elders', 'name' => 'Respect for Elders', 'icon' => '🙏'],
                        ];
                    @endphp
                    
                    @foreach($valueOptions as $value)
                        <button type="button" 
                                wire:click="toggleFamilyValue('{{ $value['code'] }}')"
                                class="group flex items-center justify-center p-4 rounded-2xl border-2 transition-all duration-300 transform hover:scale-105 {{ in_array($value['code'], $family_values) ? 'border-pink-500 bg-gradient-to-br from-pink-50 to-rose-50 text-pink-700 shadow-lg' : 'border-gray-300 bg-white text-gray-600 hover:border-pink-400 hover:shadow-md' }}">
                            <span class="text-xl mr-2 group-hover:scale-110 transition-transform">{{ $value['icon'] }}</span>
                            <span class="text-sm font-semibold">{{ $value['name'] }}</span>
                            @if(in_array($value['code'], $family_values))
                                <svg class="w-4 h-4 ml-2 text-pink-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="family_importance" class="block text-sm font-medium text-gray-900 mb-2">Family Importance</label>
                        <select wire:model="family_importance" id="family_importance" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose importance</option>
                            <option value="extremely_important">🎆 Extremely Important</option>
                            <option value="very_important">⭐ Very Important</option>
                            <option value="moderately_important">👌 Moderately Important</option>
                            <option value="somewhat_important">🤷 Somewhat Important</option>
                            <option value="not_important">😐 Not Important</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="living_situation" class="block text-sm font-medium text-gray-900 mb-2">Living Situation</label>
                        <select wire:model="living_situation" id="living_situation" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose situation</option>
                            <option value="living_alone">🏠 Living Alone</option>
                            <option value="with_family">👨‍👩‍👧‍👦 With Family</option>
                            <option value="with_roommates">🏡 With Roommates</option>
                            <option value="with_partner">💖 With Partner</option>
                            <option value="other">🎭 Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Family Involvement & Preferences -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="text-2xl mr-3">👨‍👩‍👧‍👦</span>
                        Family Involvement
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Family Involvement Level</label>
                            <textarea wire:model="family_involvement" 
                                      placeholder="e.g., Very involved, moderate, independent..."
                                      class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100"
                                      rows="4"></textarea>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                            <span class="text-sm font-medium flex items-center">
                                <span class="mr-2">✅</span>
                                Family Approval Important
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="family_approval_important" class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="text-2xl mr-3">💍</span>
                        Work & Home Arrangement
                    </h3>
                    
                    <div>
                        <label for="homemaker_preference" class="block text-sm font-medium text-gray-900 mb-2">Homemaker Preference</label>
                        <select wire:model="homemaker_preference" id="homemaker_preference" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-pink-500 focus:border-pink-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose preference</option>
                            <option value="prefer_traditional_roles">🏠 Prefer Traditional Roles</option>
                            <option value="both_work_equally">⚖️ Both Work Equally</option>
                            <option value="flexible_arrangement">🤸 Flexible Arrangement</option>
                            <option value="career_focused">💼 Career Focused</option>
                            <option value="no_preference">🤷 No Preference</option>
                        </select>
                    </div>
                </div>
            </div>

                        <!-- Professional Save Button -->
                        <div class="flex justify-center pt-8">
                            <button type="submit"
                                    class="bg-gradient-to-r from-pink-600 to-purple-600 hover:from-pink-700 hover:to-purple-700 text-white font-bold py-4 px-12 rounded-3xl transition-all duration-300 transform hover:scale-105 shadow-2xl flex items-center space-x-3">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-lg">Save Family Preferences</span>
                            </button>
                        </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
