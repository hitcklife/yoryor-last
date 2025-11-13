<x-layouts.user title="Dashboard - YorYor">
    <div class="min-h-screen bg-gradient-to-br from-pink-50 via-purple-50 to-indigo-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Enhanced Welcome Section -->
            <div class="relative bg-gradient-to-r from-pink-500 via-purple-600 to-indigo-600 rounded-3xl p-8 text-white mb-8 overflow-hidden shadow-2xl">
                <!-- Decorative elements -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-32 translate-x-32"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full translate-y-24 -translate-x-24"></div>
                
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-4xl font-bold mb-2">Salom, {{ auth()->user()->profile?->first_name ?? auth()->user()->name }}! 🇺🇿</h1>
                            <p class="text-white/90 text-lg">Ready to find your soulmate in Uzbekistan today? 💕</p>
                            <p class="text-pink-200 text-sm mt-1">{{ now()->format('l, F j, Y') }} • {{ now()->format('g:i A') }}</p>
                        </div>
                    </div>
                    <div class="hidden lg:block">
                        <div class="text-right">
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-4">
                                <p class="text-white/80 text-sm">Your Love Journey</p>
                                <p class="text-2xl font-bold">Day {{ auth()->user()->created_at->diffInDays(now()) + 1 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Love Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="group bg-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 border-0">
                    <div class="bg-gradient-to-br from-pink-50 to-rose-100 rounded-2xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                </div>
                                <p class="text-3xl font-bold text-gray-900 mb-1">{{ auth()->user()->receivedLikes()->count() ?: '24' }}</p>
                                <p class="text-pink-600 font-medium">Hearts Received 💖</p>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-pink-500 bg-pink-50 px-2 py-1 rounded-full">
                                    +3 today
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="group bg-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 border-0">
                    <div class="bg-gradient-to-br from-purple-50 to-violet-100 rounded-2xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <p class="text-3xl font-bold text-gray-900 mb-1">{{ auth()->user()->matches()->count() ?: '12' }}</p>
                                <p class="text-purple-600 font-medium">Perfect Matches ✨</p>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-purple-500 bg-purple-50 px-2 py-1 rounded-full">
                                    2 mutual
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="group bg-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 border-0">
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-2xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                </div>
                                <p class="text-3xl font-bold text-gray-900 mb-1">{{ auth()->user()->sentMessages()->count() ?: '8' }}</p>
                                <p class="text-blue-600 font-medium">Conversations 💬</p>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-blue-500 bg-blue-50 px-2 py-1 rounded-full">
                                    3 active
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="group bg-white rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 border-0">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-2xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </div>
                                <p class="text-3xl font-bold text-gray-900 mb-1">156</p>
                                <p class="text-green-600 font-medium">Profile Views 👁️</p>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-green-500 bg-green-50 px-2 py-1 rounded-full">
                                    +12 today
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Matches -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-2xl p-8 border-0">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-900">Your Recent Matches</h2>
                            </div>
                            <a href="{{ route('matches') }}" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-6 py-2 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                                View All
                            </a>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Enhanced match cards -->
                            @php
                                $sampleMatches = [
                                    ['name' => 'Dilnoza Karimova', 'age' => 24, 'distance' => '1.2 km', 'city' => 'Tashkent', 'profession' => 'Teacher', 'gradient' => 'from-pink-400 to-rose-500'],
                                    ['name' => 'Aziza Rahimova', 'age' => 27, 'distance' => '800m', 'city' => 'Samarkand', 'profession' => 'Designer', 'gradient' => 'from-purple-400 to-indigo-500'],
                                    ['name' => 'Nodira Usmanova', 'age' => 25, 'distance' => '2.1 km', 'city' => 'Bukhara', 'profession' => 'Doctor', 'gradient' => 'from-blue-400 to-cyan-500'],
                                    ['name' => 'Malika Tosheva', 'age' => 23, 'distance' => '1.8 km', 'city' => 'Andijan', 'profession' => 'Engineer', 'gradient' => 'from-green-400 to-emerald-500']
                                ];
                            @endphp
                            @foreach($sampleMatches as $index => $match)
                            <div class="group bg-gradient-to-br from-gray-50 to-white border-2 border-gray-100 rounded-2xl p-6 hover:shadow-2xl hover:border-pink-200 transition-all duration-300 transform hover:scale-105">
                                <div class="flex items-start space-x-4">
                                    <div class="relative">
                                        <div class="w-16 h-16 bg-gradient-to-br {{ $match['gradient'] }} rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                            <span class="text-white font-bold text-lg">{{ substr($match['name'], 0, 1) }}</span>
                                        </div>
                                        <div class="absolute -top-1 -right-1 w-5 h-5 bg-green-400 border-2 border-white rounded-full"></div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-gray-900 text-lg mb-1">{{ $match['name'] }}</h3>
                                        <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
                                            <span class="bg-gray-100 px-2 py-1 rounded-lg">{{ $match['age'] }} years</span>
                                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-lg">{{ $match['profession'] }}</span>
                                        </div>
                                        <div class="flex items-center text-sm text-gray-500">
                                            <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>{{ $match['distance'] }} away • {{ $match['city'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                    <div class="flex space-x-2">
                                        <button class="w-10 h-10 bg-red-100 hover:bg-red-200 rounded-xl flex items-center justify-center transition-colors group-hover:scale-110 transform">
                                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                        <button class="w-10 h-10 bg-pink-100 hover:bg-pink-200 rounded-xl flex items-center justify-center transition-colors group-hover:scale-110 transform">
                                            <svg class="w-5 h-5 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                            </svg>
                                        </button>
                                        <button class="w-10 h-10 bg-blue-100 hover:bg-blue-200 rounded-xl flex items-center justify-center transition-colors group-hover:scale-110 transform">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="text-xs text-gray-400">{{ rand(85, 98) }}% match</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Enhanced Sidebar -->
                <div class="space-y-8">
                    <!-- Enhanced Profile Completion -->
                    <div class="bg-white rounded-2xl shadow-2xl p-6 border-0 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-purple-100 to-pink-100 rounded-full -translate-y-12 translate-x-12"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Profile Power
                                </h3>
                                @php
                                    // Calculate basic completion rate
                                    $basicCompletionRate = auth()->user()->photos->count() > 0 ? 75 : 50;
                                    
                                    // Calculate enhanced profile completion
                                    $user = auth()->user();
                                    $culturalProfile = $user->culturalProfile;
                                    $familyPreference = $user->familyPreference;
                                    $careerProfile = $user->careerProfile;
                                    $physicalProfile = $user->physicalProfile;
                                    $locationPreference = $user->locationPreference;
                                    
                                    // Simple check for enhanced sections
                                    $enhancedSections = 0;
                                    if ($culturalProfile && $culturalProfile->ethnicity) $enhancedSections++;
                                    if ($familyPreference && $familyPreference->marriage_intention) $enhancedSections++;
                                    if ($careerProfile && $careerProfile->education_level) $enhancedSections++;
                                    if ($physicalProfile && $physicalProfile->exercise_frequency) $enhancedSections++;
                                    if ($locationPreference && $locationPreference->immigration_status) $enhancedSections++;
                                    
                                    $enhancementBonus = ($enhancedSections / 5) * 25; // Up to 25% bonus
                                    $totalCompletion = min(100, $basicCompletionRate + $enhancementBonus);
                                @endphp
                            </div>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-600">Overall Profile</span>
                                    <span class="text-lg font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">{{ round($totalCompletion) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div class="bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 h-3 rounded-full transition-all duration-1000" style="width: {{ $totalCompletion }}%"></div>
                                </div>
                                
                                <!-- Enhanced Profile Section -->
                                <div class="space-y-3 mt-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-600">Enhanced Sections</span>
                                        <span class="text-sm font-bold text-purple-600">{{ $enhancedSections }}/5 Complete</span>
                                    </div>
                                    
                                    @if($enhancedSections < 5)
                                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-xl p-4">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm text-purple-700 font-medium mb-1">🌟 Unlock Your Love Potential!</p>
                                                    <p class="text-xs text-purple-600">Complete your enhanced profile to get 3x more quality matches</p>
                                                </div>
                                                <a href="{{ route('profile.enhance') }}" 
                                                   class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-4 py-2 rounded-lg text-xs font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                                                    Enhance
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-green-700 font-medium">🎉 Profile Fully Enhanced!</p>
                                                    <p class="text-xs text-green-600">You're getting maximum visibility and matches!</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Progress Mini Sections -->
                                    <div class="grid grid-cols-5 gap-1 mt-3">
                                        @php
                                            $sectionNames = ['Cultural', 'Family', 'Career', 'Lifestyle', 'Location'];
                                            $sectionColors = ['purple', 'pink', 'blue', 'green', 'indigo'];
                                            $completedSections = [
                                                $culturalProfile && $culturalProfile->ethnicity,
                                                $familyPreference && $familyPreference->marriage_intention,
                                                $careerProfile && $careerProfile->education_level,
                                                $physicalProfile && $physicalProfile->exercise_frequency,
                                                $locationPreference && $locationPreference->immigration_status
                                            ];
                                        @endphp
                                        @foreach($completedSections as $index => $completed)
                                            <div class="text-center">
                                                <div class="w-full h-2 rounded-full {{ $completed ? 'bg-'.$sectionColors[$index].'-500' : 'bg-gray-200' }} transition-colors duration-300"></div>
                                                <span class="text-xs text-gray-500 mt-1 block">{{ $sectionNames[$index] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Hub -->
                    <div class="bg-white rounded-2xl shadow-2xl p-6 border-0">
                        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                            Love Action Center
                        </h3>
                        <div class="space-y-3">
                            <a href="{{ route('profile') }}" class="group flex items-center p-4 rounded-xl bg-gradient-to-r from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 transition-all duration-300 transform hover:scale-105">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-gray-900 font-bold">Edit Profile</span>
                                    <p class="text-xs text-purple-600">Make yourself irresistible</p>
                                </div>
                            </a>

                            <a href="{{ route('discover') }}" class="group flex items-center p-4 rounded-xl bg-gradient-to-r from-pink-50 to-rose-100 hover:from-pink-100 hover:to-rose-200 transition-all duration-300 transform hover:scale-105">
                                <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-gray-900 font-bold">Discover Love</span>
                                    <p class="text-xs text-pink-600">Find your soulmate</p>
                                </div>
                            </a>

                            <a href="{{ route('messages') }}" class="group flex items-center p-4 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-100 hover:from-blue-100 hover:to-indigo-200 transition-all duration-300 transform hover:scale-105">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-gray-900 font-bold">Messages</span>
                                    <p class="text-xs text-blue-600">Continue conversations</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Uzbek Love Wisdom -->
                    <div class="relative bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-600 rounded-2xl p-6 text-white overflow-hidden shadow-2xl">
                        <!-- Decorative Pattern -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-16 translate-x-16"></div>
                        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full translate-y-12 -translate-x-12"></div>
                        
                        <div class="relative">
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                                    <span class="text-lg">🌟</span>
                                </div>
                                <h3 class="text-lg font-bold">Daily Love Wisdom</h3>
                            </div>
                            @php
                                $tips = [
                                    "In Uzbek culture, genuine connections start with respect and understanding. Show authentic interest! 💝",
                                    "A complete profile with beautiful photos attracts 3x more matches. Let your personality shine! ✨",
                                    "The best conversations start with asking about family and traditions. Show you care about values! 🏡",
                                    "Patience is a virtue in love. The right person is worth waiting for, dear heart! 💕",
                                    "Share your dreams and aspirations - Uzbek hearts love ambitious and caring partners! 🌙"
                                ];
                                $randomTip = $tips[array_rand($tips)];
                            @endphp
                            <p class="text-white/95 text-sm leading-relaxed">
                                {{ $randomTip }}
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-white/70 text-xs">YorYor Daily Wisdom</div>
                                <div class="flex items-center text-white/70 text-xs">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                    </svg>
                                    Made in 🇺🇿
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.user>