<div class="min-h-screen">
    <div class="max-w-lg mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-semibold text-gray-900 mb-2">
                Preview Your Profile
            </h1>
            <p class="text-sm text-gray-600">
                This is how others will see you
            </p>
        </div>

        @if ($errorMessage)
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-800">{{ $errorMessage }}</p>
                </div>
            </div>
        @endif

        <!-- Dating Card Style Profile -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <!-- Photo Section -->
            @if(count($photos) > 0)
                <div class="relative h-[450px]">
                    @php
                        $mainPhoto = collect($photos)->firstWhere('is_profile', true) ?? $photos[0];
                    @endphp
                    <img src="{{ $mainPhoto['url'] }}" 
                         alt="Profile photo"
                         class="w-full h-full object-cover">
                    
                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                    
                    <!-- Private Photo Lock Icon -->
                    @if($mainPhoto['is_private'] ?? false)
                        <div class="absolute top-4 left-4">
                            <div class="bg-red-500 p-2 rounded-full shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Photo Dots Indicator -->
                    @if(count($photos) > 1)
                        <div class="absolute top-4 left-0 right-0 flex justify-center gap-1.5">
                            @foreach($photos as $index => $photo)
                                <div class="w-2 h-2 rounded-full {{ $index === 0 ? 'bg-white' : 'bg-white/50' }}"></div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Name and Age Overlay -->
                    <div class="absolute bottom-4 left-4 right-4">
                        <h2 class="text-2xl font-bold text-white mb-1">
                            {{ $profile->first_name ?? 'User' }}, 
                            @if($profile->date_of_birth)
                                {{ \Carbon\Carbon::parse($profile->date_of_birth)->age }}
                            @endif
                        </h2>
                        <div class="flex items-center gap-2 text-white/90 text-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            @if($country)
                                {{ $country->name }}
                                @if($profile->city)
                                    • {{ $profile->city }}
                                @endif
                            @elseif($profile->city)
                                {{ $profile->city }}
                            @else
                                Location not set
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <!-- No Photo Placeholder -->
                <div class="relative h-[450px] bg-gradient-to-b from-purple-50 to-white flex items-center justify-center">
                    <div class="text-center">
                        <div class="w-32 h-32 mx-auto mb-4 border-2 border-purple-200 rounded-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <p class="text-gray-600">No photos uploaded yet</p>
                    </div>
                </div>
            @endif

            <!-- Profile Content -->
            <div class="p-6 space-y-5">
                <!-- Key Information Pills -->
                <div class="flex flex-wrap gap-2">
                    @if($profile->gender)
                        <span class="inline-flex items-center px-3 py-1 border border-gray-200 text-gray-700 rounded-full text-sm">
                            {{ ucfirst($profile->gender) }}
                        </span>
                    @endif
                    
                    @if($profile->status && in_array($profile->status, ['single', 'married', 'divorced', 'widowed', 'separated']))
                        <span class="inline-flex items-center px-3 py-1 border border-gray-200 text-gray-700 rounded-full text-sm">
                            {{ ucfirst($profile->status) }}
                        </span>
                    @endif
                    
                    @if($physicalProfile && $physicalProfile->height)
                        <span class="inline-flex items-center px-3 py-1 border border-gray-200 text-gray-700 rounded-full text-sm">
                            {{ $physicalProfile->height }}cm
                        </span>
                    @endif
                    
                    @if($physicalProfile && $physicalProfile->body_type)
                        <span class="inline-flex items-center px-3 py-1 border border-gray-200 text-gray-700 rounded-full text-sm">
                            {{ ucfirst(str_replace('_', ' ', $physicalProfile->body_type)) }}
                        </span>
                    @endif

                    @if($profile->looking_for_relationship)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-50 text-purple-700 rounded-full text-sm font-medium">
                            @if($profile->looking_for_relationship === 'serious')
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                </svg>
                                Serious Relationship
                            @elseif($profile->looking_for_relationship === 'casual')
                                Casual Dating
                            @elseif($profile->looking_for_relationship === 'friendship')
                                Friendship
                            @elseif($profile->looking_for_relationship === 'open' || $profile->looking_for_relationship === 'all')
                                Open to All
                            @else
                                {{ ucfirst($profile->looking_for_relationship) }}
                            @endif
                        </span>
                    @endif
                    
                    @if($hasPrivatePhotos)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 rounded-full text-sm">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Private Photos
                        </span>
                    @endif
                </div>

                <!-- Bio -->
                @if($profile->bio)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-2">About Me</h3>
                        <p class="text-gray-700 leading-relaxed">{{ $profile->bio }}</p>
                    </div>
                @endif

                <!-- Work & Education -->
                @if($profile->occupation || $profile->profession || $careerProfile)
                    <div class="space-y-3">
                        @if($profile->profession || $profile->occupation)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 border border-gray-200 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                        <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Work</p>
                                    <p class="text-sm font-medium text-gray-900">
                                        @if($profile->profession)
                                            {{ $profile->profession }}
                                        @elseif($profile->occupation)
                                            {{ ucfirst($profile->occupation) }}
                                        @else
                                            Not specified
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($careerProfile && $careerProfile->education_level)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 border border-gray-200 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Education</p>
                                    <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $careerProfile->education_level)) }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($careerProfile && $careerProfile->income_range)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 border border-gray-200 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Income</p>
                                    <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $careerProfile->income_range)) }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Lifestyle Section -->
                @if($physicalProfile && ($physicalProfile->smoking_status || $physicalProfile->drinking_status || $physicalProfile->fitness_level))
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Lifestyle</h3>
                        <div class="grid grid-cols-2 gap-3">
                            @if($physicalProfile->smoking_status)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs text-gray-500">Smoking</p>
                                        <p class="text-xs font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $physicalProfile->smoking_status)) }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            @if($physicalProfile->drinking_status)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v14l3.5-2 3.5 2 3.5-2 3.5 2V4a2 2 0 00-2-2H5zm2.5 3a1.5 1.5 0 100 3 1.5 1.5 0 000-3zm6.207.293a1 1 0 00-1.414 0l-6 6a1 1 0 101.414 1.414l6-6a1 1 0 000-1.414zM12.5 10a1.5 1.5 0 100 3 1.5 1.5 0 000-3z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs text-gray-500">Drinking</p>
                                        <p class="text-xs font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $physicalProfile->drinking_status)) }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            @if($physicalProfile->fitness_level)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs text-gray-500">Fitness</p>
                                        <p class="text-xs font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $physicalProfile->fitness_level)) }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Cultural Background -->
                @if($culturalProfile && ($culturalProfile->religion || $culturalProfile->language || $culturalProfile->ethnicity))
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Cultural Background</h3>
                        <div class="space-y-2">
                            @if($culturalProfile->religion)
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-500">Religion:</span>
                                    <span class="font-medium text-gray-700">{{ ucfirst($culturalProfile->religion) }}</span>
                                </div>
                            @endif
                            @if($culturalProfile->language)
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-500">Language:</span>
                                    <span class="font-medium text-gray-700">{{ ucfirst($culturalProfile->language) }}</span>
                                </div>
                            @endif
                            @if($culturalProfile->ethnicity)
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-500">Ethnicity:</span>
                                    <span class="font-medium text-gray-700">{{ ucfirst($culturalProfile->ethnicity) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Interests -->
                @if($profile->interests)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Interests</h3>
                        <div class="flex flex-wrap gap-2">
                            @php
                                $interests = is_array($profile->interests) ? $profile->interests : json_decode($profile->interests, true);
                            @endphp
                            @if($interests)
                                @foreach($interests as $interest)
                                    <span class="px-3 py-1.5 bg-purple-50 text-purple-700 rounded-full text-xs font-medium">
                                        {{ $interest }}
                                    </span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endif

                <!-- More Photos Grid -->
                @if(count($photos) > 1)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">More Photos</h3>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(array_slice($photos, 1, 6) as $photo)
                                <div class="relative aspect-square rounded-lg overflow-hidden">
                                    <img src="{{ $photo['url'] }}" 
                                         alt="Additional photo"
                                         class="w-full h-full object-cover">
                                    @if($photo['is_private'] ?? false)
                                        <div class="absolute top-1 right-1">
                                            <div class="bg-red-500 p-1 rounded-full shadow">
                                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-4">
            <!-- Primary Action -->
            <button type="button"
                    wire:click="continueToDetails"
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white py-4 px-6 rounded-xl font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200"
                    wire:loading.attr="disabled"
                    wire:target="continueToDetails">
                <span wire:loading.remove wire:target="continueToDetails">
                    Complete Registration
                </span>
                <span wire:loading wire:target="continueToDetails" class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Completing...
                </span>
            </button>

            <!-- Edit Options -->
            <div class="flex flex-wrap gap-2 justify-center">
                <a href="{{ route('onboard.photos') }}" 
                   class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg text-sm text-gray-700 font-medium transition-colors">
                    Edit Photos
                </a>
                <a href="{{ route('onboard.basic-info') }}" 
                   class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg text-sm text-gray-700 font-medium transition-colors">
                    Edit Info
                </a>
                <a href="{{ route('onboard.interests') }}" 
                   class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg text-sm text-gray-700 font-medium transition-colors">
                    Edit Interests
                </a>
                <a href="{{ route('onboard.location') }}" 
                   class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg text-sm text-gray-700 font-medium transition-colors">
                    Edit Location
                </a>
            </div>

            <!-- Back Button -->
            <div class="text-center">
                <a href="{{ route('onboard.location') }}" 
                   class="text-sm text-gray-500 hover:text-gray-700">
                    ← Back to Previous Step
                </a>
            </div>
        </div>
    </div>
</div>