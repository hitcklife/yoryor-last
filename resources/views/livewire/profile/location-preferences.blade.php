<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    <!-- Header Section -->
    <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white shadow-2xl">
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
                        <h1 class="text-2xl font-bold">🗺️ Location</h1>
                        <p class="text-white/80 text-sm">Share your location preferences and plans</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form wire:submit.prevent="save" class="space-y-8">
            
            <!-- Immigration Status -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="text-2xl mr-3">🏛️</span>
                    Immigration Status
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="immigration_status" class="block text-sm font-medium text-gray-900 mb-2">Immigration Status</label>
                        <select wire:model="immigration_status" id="immigration_status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose status</option>
                            <option value="citizen">🇨 Citizen</option>
                            <option value="permanent_resident">🌄 Permanent Resident</option>
                            <option value="work_visa">💼 Work Visa</option>
                            <option value="student_visa">🎓 Student Visa</option>
                            <option value="tourist_visa">✈️ Tourist Visa</option>
                            <option value="asylum_refugee">🎆 Asylum/Refugee</option>
                            <option value="other">🌍 Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Years in Current Country</label>
                        <input type="number" wire:model="years_in_current_country" min="0" max="100" 
                               placeholder="e.g., 5"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                    </div>
                </div>
                
                <div class="mt-6">
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl border border-indigo-200">
                        <span class="text-sm font-semibold flex items-center text-gray-900">
                            <span class="text-lg mr-3">🏠</span>
                            Live with Family
                        </span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="live_with_family" class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Uzbekistan Connection -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="text-2xl mr-3">🇺🇿</span>
                    Uzbekistan Connection
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="plans_to_return_uzbekistan" class="block text-sm font-medium text-gray-900 mb-2">Plans to Return</label>
                        <select wire:model="plans_to_return_uzbekistan" id="plans_to_return_uzbekistan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose plans</option>
                            <option value="definitely_yes">✅ Definitely Yes</option>
                            <option value="probably_yes">👍 Probably Yes</option>
                            <option value="maybe">🤷 Maybe</option>
                            <option value="probably_no">👎 Probably No</option>
                            <option value="definitely_no">❌ Definitely No</option>
                            <option value="undecided">🤔 Undecided</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="uzbekistan_visit_frequency" class="block text-sm font-medium text-gray-900 mb-2">Visit Frequency</label>
                        <select wire:model="uzbekistan_visit_frequency" id="uzbekistan_visit_frequency" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                            <option value="">Choose frequency</option>
                            <option value="never">🙅 Never</option>
                            <option value="rarely">⏰ Rarely (Every few years)</option>
                            <option value="once_a_year">📅 Once a year</option>
                            <option value="twice_a_year">🔄 Twice a year</option>
                            <option value="every_few_months">🗓️ Every few months</option>
                            <option value="monthly">📆 Monthly</option>
                            <option value="very_frequently">✈️ Very frequently</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Relocation Preferences -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="text-2xl mr-3">✈️</span>
                    Relocation Preferences
                </h3>
                
                <div class="mb-6">
                    <label for="willing_to_relocate" class="block text-sm font-medium text-gray-900 mb-2">Willing to Relocate</label>
                    <select wire:model="willing_to_relocate" id="willing_to_relocate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100">
                        <option value="">Choose willingness</option>
                        <option value="not_open">❌ Not Open to Relocation</option>
                        <option value="within_city">🏢 Within Same City</option>
                        <option value="within_state_region">🗺️ Within Same State/Region</option>
                        <option value="within_country">🇨 Within Same Country</option>
                        <option value="open_international">🌍 Open to International</option>
                        <option value="for_right_person">💖 For the Right Person</option>
                    </select>
                </div>

                @if($willing_to_relocate === 'open_international' || $willing_to_relocate === 'for_right_person')
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-700 mb-3">Relocation Countries (Select multiple)</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @php
                            $countryOptions = [
                                ['code' => 'uzbekistan', 'name' => 'Uzbekistan', 'flag' => '🇺🇿'],
                                ['code' => 'usa', 'name' => 'United States', 'flag' => '🇺🇸'],
                                ['code' => 'canada', 'name' => 'Canada', 'flag' => '🇨🇦'],
                                ['code' => 'uk', 'name' => 'United Kingdom', 'flag' => '🇬🇧'],
                                ['code' => 'germany', 'name' => 'Germany', 'flag' => '🇩🇪'],
                                ['code' => 'australia', 'name' => 'Australia', 'flag' => '🇦🇺'],
                                ['code' => 'turkey', 'name' => 'Turkey', 'flag' => '🇹🇷'],
                                ['code' => 'russia', 'name' => 'Russia', 'flag' => '🇷🇺'],
                                ['code' => 'kazakhstan', 'name' => 'Kazakhstan', 'flag' => '🇰🇿'],
                            ];
                        @endphp
                        
                        @foreach($countryOptions as $country)
                            <button type="button" 
                                    wire:click="toggleRelocationCountry('{{ $country['code'] }}')"
                                    class="group flex items-center justify-center p-4 rounded-2xl border-2 transition-all duration-300 transform hover:scale-105 {{ in_array($country['code'], $relocation_countries) ? 'border-indigo-500 bg-gradient-to-br from-indigo-50 to-purple-50 text-indigo-700 shadow-lg' : 'border-gray-300 bg-white text-gray-600 hover:border-indigo-400 hover:shadow-md' }}">
                                <span class="text-xl mr-2 group-hover:scale-110 transition-transform">{{ $country['flag'] }}</span>
                                <span class="text-sm font-semibold">{{ $country['name'] }}</span>
                                @if(in_array($country['code'], $relocation_countries))
                                    <svg class="w-4 h-4 ml-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Living Preferences -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-700 mb-3">Living Preferences (Select multiple)</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @php
                            $locationOptions = [
                                ['code' => 'city_center', 'name' => 'City Center', 'icon' => '🏙️'],
                                ['code' => 'suburbs', 'name' => 'Suburbs', 'icon' => '🏘️'],
                                ['code' => 'countryside', 'name' => 'Countryside', 'icon' => '🌾'],
                                ['code' => 'near_family', 'name' => 'Near Family', 'icon' => '👨‍👩‍👧‍👦'],
                                ['code' => 'near_work', 'name' => 'Near Work', 'icon' => '💼'],
                                ['code' => 'quiet_area', 'name' => 'Quiet Area', 'icon' => '🌿'],
                            ];
                        @endphp
                        
                        @foreach($locationOptions as $location)
                            <button type="button" 
                                    wire:click="togglePreferredLocation('{{ $location['code'] }}')"
                                    class="group flex items-center justify-center p-4 rounded-2xl border-2 transition-all duration-300 transform hover:scale-105 {{ in_array($location['code'], $preferred_locations) ? 'border-purple-500 bg-gradient-to-br from-purple-50 to-pink-50 text-purple-700 shadow-lg' : 'border-gray-300 bg-white text-gray-600 hover:border-purple-400 hover:shadow-md' }}">
                                <span class="text-xl mr-2 group-hover:scale-110 transition-transform">{{ $location['icon'] }}</span>
                                <span class="text-sm font-semibold">{{ $location['name'] }}</span>
                                @if(in_array($location['code'], $preferred_locations))
                                    <svg class="w-4 h-4 ml-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Future Location Plans -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border-0">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="text-2xl mr-3">🧭</span>
                    Future Location Plans
                </h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Describe your future location plans</label>
                    <textarea wire:model="future_location_plans" 
                              placeholder="e.g., Stay in Tashkent, move abroad, return to Uzbekistan..."
                              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-4 transition-all duration-200 hover:bg-gray-100"
                              rows="4"></textarea>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-center pt-8">
                <button type="submit" 
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-4 px-12 rounded-2xl transition-all duration-300 transform hover:scale-105 shadow-2xl flex items-center space-x-3">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Save Location Preferences</span>
                </button>
            </div>

        </form>
    </div>
</div>
