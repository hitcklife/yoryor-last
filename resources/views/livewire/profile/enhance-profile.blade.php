<!-- Professional Profile Enhancement -->
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
                                <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h1 class="text-4xl font-bold mb-2">Enhance Your Profile</h1>
                                    <p class="text-white/90 text-lg">Complete all sections for 3x more matches</p>
                                    <p class="text-pink-200 text-sm mt-1">
                                        {{ array_sum(array_column($completionData, 'completed')) }} of {{ array_sum(array_column($completionData, 'total')) }} sections completed • {{ $overallCompletion }}% complete
                                    </p>
                                </div>
                            </div>
                            <div class="hidden lg:block">
                                <div class="text-center">
                                    <div class="relative w-24 h-24">
                                        <!-- Progress Ring -->
                                        <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                                            <path
                                                d="m18,2.0845 a 15.9155,15.9155 0 0,1 0,31.831 a 15.9155,15.9155 0 0,1 0,-31.831"
                                                fill="none"
                                                stroke="rgba(255,255,255,0.2)"
                                                stroke-width="3"
                                            />
                                            <path
                                                d="m18,2.0845 a 15.9155,15.9155 0 0,1 0,31.831 a 15.9155,15.9155 0 0,1 0,-31.831"
                                                fill="none"
                                                stroke="white"
                                                stroke-width="3"
                                                stroke-dasharray="{{ $overallCompletion }}, 100"
                                                stroke-linecap="round"
                                            />
                                        </svg>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-2xl font-bold text-white">{{ $overallCompletion }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <!-- Professional Profile Strength Card -->
                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-8 overflow-hidden relative transition-colors duration-300">
                            <!-- Decorative elements -->
                            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-100/50 to-pink-100/50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-full -translate-y-16 translate-x-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-br from-indigo-100/50 to-purple-100/50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-full translate-y-12 -translate-x-12"></div>

                            <div class="relative">
                                <div class="flex items-center justify-between mb-8">
                                    <div>
                                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mr-4">
                                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            Profile Strength
                                        </h2>
                                        <p class="text-xl text-gray-600 dark:text-gray-400 mt-2">{{ array_sum(array_column($completionData, 'completed')) }} of {{ array_sum(array_column($completionData, 'total')) }} sections completed</p>
                                    </div>
                                </div>

                                @if($overallCompletion >= 100)
                                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-2 border-green-200 dark:border-green-800/30 rounded-2xl p-6 mb-8 shadow-lg">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-green-500 rounded-2xl flex items-center justify-center shadow-lg">
                                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-xl font-bold text-green-800 dark:text-green-300">🎉 Excellent! Your profile is fully enhanced</h3>
                                                <p class="text-green-700 dark:text-green-400 text-lg">You've unlocked all premium features and maximized your match potential!</p>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border border-purple-200 dark:border-purple-800/30 rounded-2xl p-6 mb-8 shadow-lg">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
                                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xl text-purple-700 dark:text-purple-300 font-bold">
                                                    💡 Complete all sections to unlock premium features and get 3x more matches!
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Professional Sections List -->
                        <div class="space-y-6">
                            @foreach($completionData as $key => $section)
                                <div class="group">
                                    <a href="{{ route('profile.enhance.' . $key) }}"
                                       class="block bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 hover:shadow-2xl transition-all duration-300 transform hover:scale-[1.02] overflow-hidden">

                                        <div class="p-8">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-6">
                                                    <!-- Professional Icon Container -->
                                                    <div class="relative">
                                                        <div class="w-20 h-20 bg-gradient-to-br from-{{ $section['color'] }}-500 to-{{ $section['color'] }}-600 rounded-3xl flex items-center justify-center group-hover:scale-110 transition-all duration-300 shadow-xl">
                                                            <span class="text-3xl">{{ $section['icon'] }}</span>
                                                        </div>
                                                        @if($section['percentage'] >= 100)
                                                            <div class="absolute -top-2 -right-2 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center border-3 border-white shadow-lg">
                                                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="flex-1">
                                                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $section['title'] }}</h3>
                                                        <p class="text-lg text-gray-600 dark:text-gray-400 mb-4">{{ $section['description'] }}</p>

                                                        <!-- Progress Bar -->
                                                        <div class="flex items-center space-x-4">
                                                            <div class="flex-1 h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                                <div class="h-3 bg-gradient-to-r from-{{ $section['color'] }}-400 to-{{ $section['color'] }}-600 rounded-full transition-all duration-700" style="width: {{ $section['percentage'] }}%"></div>
                                                            </div>
                                                            <span class="text-lg font-bold text-{{ $section['color'] }}-600 dark:text-{{ $section['color'] }}-400 min-w-[3rem]">
                                                                {{ $section['completed'] }}/{{ $section['total'] }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center space-x-4 ml-4">
                                                    @if($section['percentage'] >= 100)
                                                        <div class="px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-2xl font-bold text-sm">
                                                            Complete
                                                        </div>
                                                    @else
                                                        <div class="px-4 py-2 bg-{{ $section['color'] }}-100 dark:bg-{{ $section['color'] }}-900/30 text-{{ $section['color'] }}-800 dark:text-{{ $section['color'] }}-300 rounded-2xl font-bold text-sm">
                                                            {{ round($section['percentage']) }}%
                                                        </div>
                                                    @endif

                                                    <!-- Professional Chevron Arrow -->
                                                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center group-hover:bg-{{ $section['color'] }}-100 dark:group-hover:bg-{{ $section['color'] }}-900/30 transition-colors duration-300">
                                                        <svg class="w-6 h-6 text-gray-400 group-hover:text-{{ $section['color'] }}-600 dark:group-hover:text-{{ $section['color'] }}-400 group-hover:translate-x-1 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <!-- Professional Benefits Card -->
                        <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-8 relative overflow-hidden transition-colors duration-300">
                            <!-- Decorative elements -->
                            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-100/50 to-pink-100/50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-full -translate-y-16 translate-x-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-br from-indigo-100/50 to-purple-100/50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-full translate-y-12 -translate-x-12"></div>

                            <div class="relative">
                                <div class="flex items-start space-x-6 mb-8">
                                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-3xl flex items-center justify-center flex-shrink-0 shadow-xl">
                                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">✨ Premium Profile Benefits</h3>
                                        <p class="text-lg text-gray-600 dark:text-gray-400">Unlock these amazing features when you complete your profile:</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="flex items-center space-x-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl">
                                        <div class="w-12 h-12 bg-green-500 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <span class="text-lg font-medium text-gray-700 dark:text-gray-300">Get 3x more matches with complete profiles</span>
                                    </div>

                                    <div class="flex items-center space-x-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl">
                                        <div class="w-12 h-12 bg-blue-500 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <span class="text-lg font-medium text-gray-700 dark:text-gray-300">Priority placement in search results</span>
                                    </div>

                                    <div class="flex items-center space-x-4 p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-2xl">
                                        <div class="w-12 h-12 bg-purple-500 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <span class="text-lg font-medium text-gray-700 dark:text-gray-300">Better compatibility matching algorithm</span>
                                    </div>

                                    <div class="flex items-center space-x-4 p-4 bg-gradient-to-r from-pink-50 to-rose-50 dark:from-pink-900/20 dark:to-rose-900/20 rounded-2xl">
                                        <div class="w-12 h-12 bg-pink-500 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <span class="text-lg font-medium text-gray-700 dark:text-gray-300">Access to premium Uzbek cultural matching features</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
