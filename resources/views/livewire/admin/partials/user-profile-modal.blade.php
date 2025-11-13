<!-- User Profile Modal Content -->
<div class="max-h-[80vh] overflow-y-auto">
    <!-- Modal Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-200 mb-6">
        <h3 class="text-2xl font-bold text-gray-900 flex items-center">
            <svg class="w-6 h-6 mr-2 text-pink-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
            </svg>
            User Profile Details
        </h3>
        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- User Basic Info -->
    <div class="bg-gradient-to-r from-pink-50 to-purple-50 rounded-xl p-6 mb-6 border border-pink-100">
        <div class="flex items-start space-x-6">
            <!-- Profile Photo -->
            <div class="flex-shrink-0">
                @if($selectedUser->getProfilePhotoUrl())
                    <img class="h-24 w-24 rounded-full object-cover border-4 border-white shadow-lg" 
                         src="{{ $selectedUser->getProfilePhotoUrl() }}" 
                         alt="{{ $selectedUser->full_name }}">
                @else
                    <div class="h-24 w-24 rounded-full bg-gradient-to-br from-pink-500 to-purple-600 flex items-center justify-center border-4 border-white shadow-lg">
                        <span class="text-white font-bold text-2xl">{{ $selectedUser->initials() }}</span>
                    </div>
                @endif
            </div>

            <!-- Basic Info -->
            <div class="flex-1">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-900">{{ $selectedUser->full_name ?: 'No Name Provided' }}</h4>
                        <p class="text-gray-600 flex items-center space-x-2">
                            <span class="font-mono text-sm">#{{ $selectedUser->id }}</span>
                            @if($selectedUser->profile?->gender)
                                <span>•</span>
                                <span>{{ ucfirst($selectedUser->profile->gender) }}</span>
                            @endif
                            @if($selectedUser->age)
                                <span>•</span>
                                <span>{{ $selectedUser->age }} years old</span>
                            @endif
                        </p>
                    </div>
                    
                    <!-- Status Badges -->
                    <div class="flex flex-col items-end space-y-2">
                        @if($selectedUser->disabled_at)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                </svg>
                                Disabled
                            </span>
                        @elseif($selectedUser->registration_completed)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Incomplete
                            </span>
                        @endif

                        @if($selectedUser->email_verified_at || $selectedUser->phone_verified_at)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Verified
                            </span>
                        @endif

                        @if($selectedUser->isOnline())
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                                Online Now
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    @if($selectedUser->email)
                        <div class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            <span>{{ $selectedUser->email }}</span>
                            @if($selectedUser->email_verified_at)
                                <svg class="w-4 h-4 ml-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </div>
                    @endif
                    @if($selectedUser->phone)
                        <div class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                            </svg>
                            <span>{{ $selectedUser->phone }}</span>
                            @if($selectedUser->phone_verified_at)
                                <svg class="w-4 h-4 ml-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Basic Profile Info -->
        @if($selectedUser->profile)
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    Basic Information
                </h5>
                <div class="space-y-3">
                    @if($selectedUser->profile->date_of_birth)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date of Birth:</span>
                            <span class="font-medium">{{ $selectedUser->profile->date_of_birth->format('M j, Y') }} ({{ $selectedUser->age }} years)</span>
                        </div>
                    @endif
                    @if($selectedUser->profile->status)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-medium capitalize">{{ $selectedUser->profile->status }}</span>
                        </div>
                    @endif
                    @if($selectedUser->profile->occupation)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Occupation:</span>
                            <span class="font-medium capitalize">{{ $selectedUser->profile->occupation }}</span>
                        </div>
                    @endif
                    @if($selectedUser->profile->city || $selectedUser->profile->country_name)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Location:</span>
                            <span class="font-medium">
                                {{ $selectedUser->profile->city }}{{ $selectedUser->profile->city && $selectedUser->profile->country_name ? ', ' : '' }}{{ $selectedUser->profile->country_name }}
                            </span>
                        </div>
                    @endif
                    @if($selectedUser->profile->bio)
                        <div>
                            <span class="text-gray-600">Bio:</span>
                            <p class="mt-1 text-gray-900">{{ $selectedUser->profile->bio }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Account Stats -->
        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                </svg>
                Account Activity
            </h5>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Member Since:</span>
                    <span class="font-medium">{{ $selectedUser->created_at->format('M j, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Last Active:</span>
                    <span class="font-medium">
                        @if($selectedUser->last_active_at)
                            {{ $selectedUser->last_active_at->diffForHumans() }}
                        @else
                            Never
                        @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Last Login:</span>
                    <span class="font-medium">
                        @if($selectedUser->last_login_at)
                            {{ $selectedUser->last_login_at->diffForHumans() }}
                        @else
                            Never
                        @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Photos:</span>
                    <span class="font-medium">{{ $selectedUser->photos->count() }}</span>
                </div>
                @if($selectedUser->roles->isNotEmpty())
                    <div class="flex justify-between">
                        <span class="text-gray-600">Roles:</span>
                        <div class="flex space-x-1">
                            @foreach($selectedUser->roles as $role)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Extended Profile Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Cultural Profile -->
        @if($selectedUser->culturalProfile)
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                    </svg>
                    Cultural Information
                </h5>
                <div class="space-y-3 text-sm">
                    @if($selectedUser->culturalProfile->religion)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Religion:</span>
                            <span class="font-medium capitalize">{{ $selectedUser->culturalProfile->religion }}</span>
                        </div>
                    @endif
                    @if($selectedUser->culturalProfile->ethnicity)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Ethnicity:</span>
                            <span class="font-medium capitalize">{{ $selectedUser->culturalProfile->ethnicity }}</span>
                        </div>
                    @endif
                    @if($selectedUser->culturalProfile->languages)
                        <div>
                            <span class="text-gray-600">Languages:</span>
                            <p class="mt-1 text-gray-900">{{ is_array($selectedUser->culturalProfile->languages) ? implode(', ', $selectedUser->culturalProfile->languages) : $selectedUser->culturalProfile->languages }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Physical Profile -->
        @if($selectedUser->physicalProfile)
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-pink-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                    </svg>
                    Physical Information
                </h5>
                <div class="space-y-3 text-sm">
                    @if($selectedUser->physicalProfile->height)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Height:</span>
                            <span class="font-medium">{{ $selectedUser->physicalProfile->height }} cm</span>
                        </div>
                    @endif
                    @if($selectedUser->physicalProfile->weight)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Weight:</span>
                            <span class="font-medium">{{ $selectedUser->physicalProfile->weight }} kg</span>
                        </div>
                    @endif
                    @if($selectedUser->physicalProfile->body_type)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Body Type:</span>
                            <span class="font-medium capitalize">{{ $selectedUser->physicalProfile->body_type }}</span>
                        </div>
                    @endif
                    @if($selectedUser->physicalProfile->eye_color)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Eye Color:</span>
                            <span class="font-medium capitalize">{{ $selectedUser->physicalProfile->eye_color }}</span>
                        </div>
                    @endif
                    @if($selectedUser->physicalProfile->hair_color)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Hair Color:</span>
                            <span class="font-medium capitalize">{{ $selectedUser->physicalProfile->hair_color }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Career Profile -->
        @if($selectedUser->careerProfile)
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                        <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                    </svg>
                    Career Information
                </h5>
                <div class="space-y-3 text-sm">
                    @if($selectedUser->careerProfile->occupation)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Occupation:</span>
                            <span class="font-medium">{{ $selectedUser->careerProfile->occupation }}</span>
                        </div>
                    @endif
                    @if($selectedUser->careerProfile->company)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Company:</span>
                            <span class="font-medium">{{ $selectedUser->careerProfile->company }}</span>
                        </div>
                    @endif
                    @if($selectedUser->careerProfile->education_level)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Education:</span>
                            <span class="font-medium capitalize">{{ $selectedUser->careerProfile->education_level }}</span>
                        </div>
                    @endif
                    @if($selectedUser->careerProfile->income_range)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Income Range:</span>
                            <span class="font-medium">{{ $selectedUser->careerProfile->income_range }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Family Preferences -->
        @if($selectedUser->familyPreference)
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                    Family Preferences
                </h5>
                <div class="space-y-3 text-sm">
                    @if($selectedUser->familyPreference->children_preference)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Children Preference:</span>
                            <span class="font-medium capitalize">{{ $selectedUser->familyPreference->children_preference }}</span>
                        </div>
                    @endif
                    @if($selectedUser->familyPreference->has_children)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Has Children:</span>
                            <span class="font-medium capitalize">{{ $selectedUser->familyPreference->has_children }}</span>
                        </div>
                    @endif
                    @if($selectedUser->familyPreference->family_plans)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Family Plans:</span>
                            <span class="font-medium capitalize">{{ $selectedUser->familyPreference->family_plans }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- User Photos -->
    @if($selectedUser->photos->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
            <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                </svg>
                Photos ({{ $selectedUser->photos->count() }})
            </h5>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($selectedUser->photos->take(8) as $photo)
                    <div class="relative group">
                        <img src="{{ $photo->medium_url ?? $photo->original_url }}" 
                             alt="User photo" 
                             class="w-full h-32 object-cover rounded-lg border-2 {{ $photo->is_profile_photo ? 'border-pink-500' : 'border-gray-200' }}">
                        @if($photo->is_profile_photo)
                            <div class="absolute top-2 right-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                    Profile
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach
                @if($selectedUser->photos->count() > 8)
                    <div class="w-full h-32 bg-gray-100 rounded-lg border-2 border-gray-200 flex items-center justify-center text-gray-500">
                        +{{ $selectedUser->photos->count() - 8 }} more
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
        <button wire:click="closeModal" 
                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            Close
        </button>
        
        <button wire:click="toggleUserStatus({{ $selectedUser->id }})" 
                class="{{ $selectedUser->disabled_at ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-orange-600 hover:bg-orange-700 text-white' }} px-6 py-2 rounded-lg transition-colors">
            {{ $selectedUser->disabled_at ? 'Enable User' : 'Disable User' }}
        </button>
        
        <button wire:click="deleteUser({{ $selectedUser->id }})" 
                wire:confirm="Are you sure you want to delete this user? This action cannot be undone."
                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors">
            Delete User
        </button>
    </div>
</div>