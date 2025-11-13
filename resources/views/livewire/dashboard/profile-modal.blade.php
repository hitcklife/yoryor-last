<div>
@if($showProfile && $profile)
    <!-- Full Screen Profile View -->
    <div class="fixed inset-0 z-50 bg-white overflow-y-auto">
        <!-- Header with Navigation -->
        <div class="sticky top-0 z-10 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between p-4">
                <!-- Back Button -->
                <button wire:click="hideProfileView" class="flex items-center text-gray-600 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <!-- Profile Name & Age -->
                <div class="flex-1 text-center">
                    <h1 class="text-lg font-semibold text-gray-900">
                        {{ $profile['full_name'] }}
                        @if($profile['age'])
                            <span class="text-gray-600">, {{ $profile['age'] }}</span>
                        @endif
                    </h1>
                </div>

                <!-- More Options -->
                <button class="text-gray-600 hover:text-gray-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="pb-20">
            <!-- Main Profile Photo -->
            @if(count($profile['photos']) > 0)
                <div class="relative aspect-[3/4] bg-gray-200">
                    <img src="{{ $profile['photos'][0]['url'] }}" 
                         alt="{{ $profile['name'] }}" 
                         class="w-full h-full object-cover">
                    
                    <!-- Photo Counter -->
                    @if(count($profile['photos']) > 1)
                        <div class="absolute top-4 right-4 bg-black bg-opacity-50 text-white px-2 py-1 rounded-full text-sm">
                            1 of {{ count($profile['photos']) }}
                        </div>
                    @endif

                    <!-- Online Status -->
                    @if($profile['is_online'])
                        <div class="absolute bottom-4 left-4 flex items-center space-x-2 bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-sm">
                            <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                            <span>Online</span>
                        </div>
                    @endif

                    <!-- Verification Badge -->
                    @if($profile['verified'])
                        <div class="absolute bottom-4 right-4 bg-blue-500 rounded-full p-2">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Basic Information Section -->
            <div class="p-6 space-y-4">
                <!-- Location & Looking For -->
                <div class="flex items-center space-x-4 text-gray-600">
                    @if($profile['location'])
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            <span>{{ $profile['location'] }}</span>
                        </div>
                    @endif
                    @if($profile['looking_for'])
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <span>{{ ucfirst(str_replace('_', ' ', $profile['looking_for'])) }}</span>
                        </div>
                    @endif
                </div>

                <!-- Bio -->
                @if($profile['bio'])
                    <div>
                        <p class="text-gray-800 leading-relaxed">{{ $profile['bio'] }}</p>
                    </div>
                @endif
            </div>

            <!-- Second Photo if available -->
            @if(count($profile['photos']) > 1)
                <div class="aspect-[3/4] bg-gray-200">
                    <img src="{{ $profile['photos'][1]['url'] }}" 
                         alt="{{ $profile['name'] }}" 
                         class="w-full h-full object-cover">
                </div>
            @endif

            <!-- Basic Details -->
            <div class="p-6 bg-gray-50">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Basic Details</h2>
                <div class="grid grid-cols-2 gap-4">
                    @if($profile['occupation'])
                        <div>
                            <p class="text-sm font-medium text-gray-500">Occupation</p>
                            <p class="text-gray-900">{{ $profile['occupation'] }}</p>
                        </div>
                    @endif
                    @if($profile['education'])
                        <div>
                            <p class="text-sm font-medium text-gray-500">Education</p>
                            <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $profile['education'])) }}</p>
                        </div>
                    @endif
                    @if($profile['height'])
                        <div>
                            <p class="text-sm font-medium text-gray-500">Height</p>
                            <p class="text-gray-900">{{ $profile['height'] }}cm</p>
                        </div>
                    @endif
                    @if($profile['religion'])
                        <div>
                            <p class="text-sm font-medium text-gray-500">Religion</p>
                            <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $profile['religion'])) }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Third Photo if available -->
            @if(count($profile['photos']) > 2)
                <div class="aspect-[3/4] bg-gray-200">
                    <img src="{{ $profile['photos'][2]['url'] }}" 
                         alt="{{ $profile['name'] }}" 
                         class="w-full h-full object-cover">
                </div>
            @endif

            <!-- Cultural Background -->
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Cultural Background</h2>
                <div class="space-y-4">
                    @if($profile['ethnicity'])
                        <div>
                            <p class="text-sm font-medium text-gray-500">Ethnicity</p>
                            <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $profile['ethnicity'])) }}</p>
                        </div>
                    @endif
                    @if(!empty($profile['languages']))
                        <div>
                            <p class="text-sm font-medium text-gray-500 mb-2">Languages</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($profile['languages'] as $language)
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">{{ trim($language) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if($profile['lifestyle_type'])
                        <div>
                            <p class="text-sm font-medium text-gray-500">Lifestyle</p>
                            <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $profile['lifestyle_type'])) }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Fourth Photo if available -->
            @if(count($profile['photos']) > 3)
                <div class="aspect-[3/4] bg-gray-200">
                    <img src="{{ $profile['photos'][3]['url'] }}" 
                         alt="{{ $profile['name'] }}" 
                         class="w-full h-full object-cover">
                </div>
            @endif

            <!-- Family & Marriage -->
            <div class="p-6 bg-gray-50">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Family & Marriage</h2>
                <div class="space-y-4">
                    @if($profile['marriage_intention'])
                        <div>
                            <p class="text-sm font-medium text-gray-500">Marriage Intention</p>
                            <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $profile['marriage_intention'])) }}</p>
                        </div>
                    @endif
                    @if($profile['children_preference'])
                        <div>
                            <p class="text-sm font-medium text-gray-500">Children</p>
                            <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $profile['children_preference'])) }}</p>
                        </div>
                    @endif
                    @if($profile['family_importance'])
                        <div>
                            <p class="text-sm font-medium text-gray-500">Family Importance</p>
                            <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $profile['family_importance'])) }}</p>
                        </div>
                    @endif
                    @if($profile['marriage_timeline'])
                        <div>
                            <p class="text-sm font-medium text-gray-500">Marriage Timeline</p>
                            <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $profile['marriage_timeline'])) }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Fifth Photo if available -->
            @if(count($profile['photos']) > 4)
                <div class="aspect-[3/4] bg-gray-200">
                    <img src="{{ $profile['photos'][4]['url'] }}" 
                         alt="{{ $profile['name'] }}" 
                         class="w-full h-full object-cover">
                </div>
            @endif

            <!-- Lifestyle & Interests -->
            @if(!empty($profile['interests']) || !empty($profile['hobbies_interests']) || $profile['smoking_habit'] || $profile['drinking_habit'])
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Lifestyle & Interests</h2>
                    <div class="space-y-4">
                        @if(!empty($profile['interests']) || !empty($profile['hobbies_interests']))
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-2">Interests</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(array_merge($profile['interests'] ?? [], $profile['hobbies_interests'] ?? []) as $interest)
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">{{ $interest }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="grid grid-cols-2 gap-4">
                            @if($profile['smoking_habit'] || $profile['smoking_status'])
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Smoking</p>
                                    <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $profile['smoking_habit'] ?? $profile['smoking_status'])) }}</p>
                                </div>
                            @endif
                            @if($profile['drinking_habit'] || $profile['drinking_status'])
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Drinking</p>
                                    <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $profile['drinking_habit'] ?? $profile['drinking_status'])) }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Remaining Photos -->
            @if(count($profile['photos']) > 5)
                @for($i = 5; $i < count($profile['photos']); $i++)
                    <div class="aspect-[3/4] bg-gray-200">
                        <img src="{{ $profile['photos'][$i]['url'] }}" 
                             alt="{{ $profile['name'] }}" 
                             class="w-full h-full object-cover">
                    </div>
                @endfor
            @endif

            <!-- Additional Information (Career, Location, etc.) -->
            <div class="p-6 bg-gray-50">
                <h2 class="text-xl font-bold text-gray-900 mb-4">More About Me</h2>
                <div class="space-y-4">
                    @if($profile['university_name'])
                        <div>
                            <p class="text-sm font-medium text-gray-500">University</p>
                            <p class="text-gray-900">{{ $profile['university_name'] }}</p>
                        </div>
                    @endif
                    @if($profile['immigration_status'])
                        <div>
                            <p class="text-sm font-medium text-gray-500">Immigration Status</p>
                            <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $profile['immigration_status'])) }}</p>
                        </div>
                    @endif
                    @if($profile['profile_views'])
                        <div>
                            <p class="text-sm font-medium text-gray-500">Profile Views</p>
                            <p class="text-gray-900">{{ number_format($profile['profile_views']) }}</p>
                        </div>
                    @endif
                    @if($profile['last_active_at'])
                        <div>
                            <p class="text-sm font-medium text-gray-500">Last Active</p>
                            <p class="text-gray-900">{{ $profile['last_active_at']->diffForHumans() }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Fixed Bottom Action Buttons -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4">
            <div class="flex items-center justify-center space-x-4 max-w-sm mx-auto">
                <!-- Pass Button -->
                <button wire:click="passProfile" 
                        class="w-16 h-16 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center transition-colors shadow-lg">
                    <svg class="w-7 h-7 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                
                <!-- Super Like Button -->
                <button wire:click="superLikeProfile" 
                        class="w-16 h-16 bg-blue-500 hover:bg-blue-600 rounded-full flex items-center justify-center transition-colors shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                    </svg>
                </button>
                
                <!-- Like Button -->
                <button wire:click="likeProfile" 
                        class="w-16 h-16 bg-gradient-to-r from-pink-500 to-red-500 hover:from-pink-600 hover:to-red-600 rounded-full flex items-center justify-center transition-all transform hover:scale-105 shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endif
</div>