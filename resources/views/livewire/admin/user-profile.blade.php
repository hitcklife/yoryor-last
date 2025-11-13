<div>
    @if(!$user)
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="text-lg font-medium text-red-800">User Not Found</h3>
                    <p class="text-red-700">The requested user could not be found.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Header with Back Button -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.users') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Users
                </a>
                <div class="text-sm text-gray-500">
                    <span>User Profile</span>
                </div>
            </div>
            
            <!-- Action Buttons - Moved to header -->
            <div class="flex items-center space-x-3">
                <button wire:click="toggleUserStatus" 
                        class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm text-sm font-medium transition-all duration-200 {{ $user->disabled_at ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-orange-600 hover:bg-orange-700 text-white' }}">
                    @if($user->disabled_at)
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enable User
                    @else
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                        </svg>
                        Disable User
                    @endif
                </button>
                
                <button wire:click="deleteUser" 
                        wire:confirm="Are you sure you want to delete this user? This action cannot be undone."
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg shadow-sm text-sm font-medium transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete User
                </button>
            </div>
        </div>

        <!-- User Basic Info Header -->
        <div class="bg-gradient-to-r from-pink-50 to-purple-50 rounded-xl p-8 mb-8 border border-pink-100 shadow-sm">
            <div class="flex items-start space-x-8">
                <!-- Profile Photo -->
                <div class="flex-shrink-0">
                    @if(method_exists($user, 'getProfilePhotoUrl') && $user->getProfilePhotoUrl())
                        <img class="h-32 w-32 rounded-full object-cover border-4 border-white shadow-lg" 
                             src="{{ $user->getProfilePhotoUrl() }}" 
                             alt="{{ $user->profile?->first_name ?? 'User' }}">
                    @else
                        <div class="h-32 w-32 rounded-full bg-gradient-to-br from-pink-500 to-purple-600 flex items-center justify-center border-4 border-white shadow-lg">
                            <span class="text-white font-bold text-3xl">
                                {{ substr($user->profile?->first_name ?? 'U', 0, 1) }}{{ substr($user->profile?->last_name ?? 'U', 0, 1) }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Basic Info -->
                <div class="flex-1">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ $user->profile?->first_name . ' ' . $user->profile?->last_name ?: 'No Name Provided' }}</h1>
                        <div class="flex flex-wrap items-center gap-4 text-lg text-gray-600">
                            <span class="inline-flex items-center px-3 py-1 bg-gray-100 rounded-full text-sm font-mono">#{{ $user->id }}</span>
                            @if($user->profile?->gender)
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ ucfirst($user->profile->gender) }}
                                </span>
                            @endif
                            @if($user->profile?->date_of_birth)
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($user->profile->date_of_birth)->age }} years old
                                </span>
                            @endif
                            @if(method_exists($user, 'isOnline') && $user->isOnline())
                                <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                                    Online Now
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Status Badges -->
                    <div class="flex flex-wrap items-center gap-3 mb-6">
                        @if($user->disabled_at)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                </svg>
                                Disabled
                            </span>
                        @elseif($user->registration_completed)
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

                        @if($user->email_verified_at || $user->phone_verified_at)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Verified
                            </span>
                        @endif

                        @if($user->roles->isNotEmpty())
                            @foreach($user->roles as $role)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ ucfirst($role->name) }}
                                </span>
                            @endforeach
                        @endif
                    </div>

                    <!-- Contact Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($user->email)
                            <div class="flex items-center p-3 bg-white bg-opacity-60 rounded-lg">
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $user->email }}</p>
                                    <div class="flex items-center mt-1">
                                        @if($user->email_verified_at)
                                            <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-xs text-green-600">Verified</span>
                                        @else
                                            <span class="text-xs text-gray-500">Unverified</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if($user->phone)
                            <div class="flex items-center p-3 bg-white bg-opacity-60 rounded-lg">
                                <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $user->phone }}</p>
                                    <div class="flex items-center mt-1">
                                        @if($user->phone_verified_at)
                                            <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-xs text-green-600">Verified</span>
                                        @else
                                            <span class="text-xs text-gray-500">Unverified</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($user->profile?->bio)
                        <div class="mt-6 p-4 bg-white bg-opacity-60 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                                </svg>
                                About
                            </h4>
                            <p class="text-gray-700 leading-relaxed">{{ $user->profile->bio }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Profile Details Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
            <!-- Basic Profile Info -->
            @if($user->profile)
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        Basic Information
                    </h5>
                    <div class="space-y-3">
                        @if($user->profile->date_of_birth)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date of Birth:</span>
                                <span class="font-medium">{{ $user->profile->date_of_birth->format('M j, Y') }} ({{ \Carbon\Carbon::parse($user->profile->date_of_birth)->age }} years)</span>
                            </div>
                        @endif
                        @if($user->profile->status)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-medium capitalize">{{ $user->profile->status }}</span>
                            </div>
                        @endif
                        @if($user->profile->occupation)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Occupation:</span>
                                <span class="font-medium capitalize">{{ $user->profile->occupation }}</span>
                            </div>
                        @endif
                        @if($user->profile->city || $user->profile->country_name)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Location:</span>
                                <span class="font-medium">
                                    {{ $user->profile->city }}{{ $user->profile->city && $user->profile->country_name ? ', ' : '' }}{{ $user->profile->country_name }}
                                </span>
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
                        <span class="font-medium">{{ $user->created_at->format('M j, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Active:</span>
                        <span class="font-medium">
                            @if($user->last_active_at)
                                {{ $user->last_active_at->diffForHumans() }}
                            @else
                                Never
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Login:</span>
                        <span class="font-medium">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->diffForHumans() }}
                            @else
                                Never
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Photos:</span>
                        <span class="font-medium">{{ $user->photos->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Profile Complete:</span>
                        <span class="font-medium">{{ $user->registration_completed ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                    </svg>
                    Quick Actions
                </h5>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Registration Complete:</span>
                        <span class="{{ $user->registration_completed ? 'text-green-600' : 'text-red-600' }} font-medium">
                            {{ $user->registration_completed ? 'Complete' : 'Incomplete' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Email Verified:</span>
                        <span class="{{ $user->email_verified_at ? 'text-green-600' : 'text-red-600' }} font-medium">
                            {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Phone Verified:</span>
                        <span class="{{ $user->phone_verified_at ? 'text-green-600' : 'text-red-600' }} font-medium">
                            {{ $user->phone_verified_at ? 'Verified' : 'Unverified' }}
                        </span>
                    </div>
                    @if(method_exists($user, 'isOnline'))
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Online Status:</span>
                            <span class="{{ $user->isOnline() ? 'text-green-600' : 'text-gray-600' }} font-medium">
                                {{ $user->isOnline() ? 'Online' : 'Offline' }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Extended Profile Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Cultural Profile -->
            @if($user->culturalProfile)
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                        Cultural Information
                    </h5>
                    <div class="space-y-3 text-sm">
                        @if($user->culturalProfile->religion)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Religion:</span>
                                <span class="font-medium capitalize">{{ $user->culturalProfile->religion }}</span>
                            </div>
                        @endif
                        @if($user->culturalProfile->ethnicity)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Ethnicity:</span>
                                <span class="font-medium capitalize">{{ $user->culturalProfile->ethnicity }}</span>
                            </div>
                        @endif
                        @if($user->culturalProfile->languages)
                            <div>
                                <span class="text-gray-600">Languages:</span>
                                <p class="mt-1 text-gray-900">{{ is_array($user->culturalProfile->languages) ? implode(', ', $user->culturalProfile->languages) : $user->culturalProfile->languages }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Physical Profile -->
            @if($user->physicalProfile)
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-pink-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                        </svg>
                        Physical Information
                    </h5>
                    <div class="space-y-3 text-sm">
                        @if($user->physicalProfile->height)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Height:</span>
                                <span class="font-medium">{{ $user->physicalProfile->height }} cm</span>
                            </div>
                        @endif
                        @if($user->physicalProfile->weight)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Weight:</span>
                                <span class="font-medium">{{ $user->physicalProfile->weight }} kg</span>
                            </div>
                        @endif
                        @if($user->physicalProfile->body_type)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Body Type:</span>
                                <span class="font-medium capitalize">{{ $user->physicalProfile->body_type }}</span>
                            </div>
                        @endif
                        @if($user->physicalProfile->eye_color)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Eye Color:</span>
                                <span class="font-medium capitalize">{{ $user->physicalProfile->eye_color }}</span>
                            </div>
                        @endif
                        @if($user->physicalProfile->hair_color)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Hair Color:</span>
                                <span class="font-medium capitalize">{{ $user->physicalProfile->hair_color }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Career Profile -->
            @if($user->careerProfile)
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                        </svg>
                        Career Information
                    </h5>
                    <div class="space-y-3 text-sm">
                        @if($user->careerProfile->occupation)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Occupation:</span>
                                <span class="font-medium">{{ $user->careerProfile->occupation }}</span>
                            </div>
                        @endif
                        @if($user->careerProfile->company)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Company:</span>
                                <span class="font-medium">{{ $user->careerProfile->company }}</span>
                            </div>
                        @endif
                        @if($user->careerProfile->education_level)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Education:</span>
                                <span class="font-medium capitalize">{{ $user->careerProfile->education_level }}</span>
                            </div>
                        @endif
                        @if($user->careerProfile->income_range)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Income Range:</span>
                                <span class="font-medium">{{ $user->careerProfile->income_range }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Family Preferences -->
            @if($user->familyPreference)
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                        Family Preferences
                    </h5>
                    <div class="space-y-3 text-sm">
                        @if($user->familyPreference->children_preference)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Children Preference:</span>
                                <span class="font-medium capitalize">{{ $user->familyPreference->children_preference }}</span>
                            </div>
                        @endif
                        @if($user->familyPreference->has_children)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Has Children:</span>
                                <span class="font-medium capitalize">{{ $user->familyPreference->has_children }}</span>
                            </div>
                        @endif
                        @if($user->familyPreference->family_plans)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Family Plans:</span>
                                <span class="font-medium capitalize">{{ $user->familyPreference->family_plans }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- User Photos -->
        @if($user->photos->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
                <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                    </svg>
                    Photos ({{ $user->photos->count() }})
                </h5>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    @foreach($user->photos as $photo)
                        <div class="relative group bg-gray-100 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                            @php
                                $imageUrl = $photo->medium_url ?: $photo->original_url ?: $photo->thumbnail_url;
                            @endphp
                            
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" 
                                     alt="User photo" 
                                     class="w-full h-40 object-cover cursor-pointer hover:scale-105 transition-transform duration-200"
                                     onclick="window.open('{{ $photo->original_url ?: $imageUrl }}', '_blank')"
                                     onerror="this.parentElement.querySelector('.photo-fallback').classList.remove('hidden'); this.style.display='none';">
                            @endif
                            
                            <!-- Fallback for missing/broken images -->
                            <div class="photo-fallback w-full h-40 bg-gray-200 flex items-center justify-center {{ $imageUrl ? 'hidden' : '' }}">
                                <div class="text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-xs">No image available</p>
                                    @if($photo->status === 'pending')
                                        <p class="text-xs text-orange-500 mt-1">Pending approval</p>
                                    @elseif($photo->status === 'rejected')
                                        <p class="text-xs text-red-500 mt-1">Rejected</p>
                                    @endif
                                </div>
                            </div>
                            
                            @if($photo->is_profile_photo)
                                <div class="absolute top-2 right-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-pink-500 text-white shadow-lg">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                        </svg>
                                        Profile
                                    </span>
                                </div>
                            @endif
                            
                            @if($photo->status && $photo->status !== 'approved')
                                <div class="absolute top-2 left-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $photo->status === 'pending' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($photo->status) }}
                                    </span>
                                </div>
                            @endif
                            
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center">
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    @if($imageUrl)
                                        <svg class="w-8 h-8 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Photo info tooltip -->
                            @if($imageUrl)
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <div class="text-white text-xs">
                                        @if($photo->uploaded_at)
                                            <p>{{ $photo->uploaded_at->diffForHumans() }}</p>
                                        @elseif($photo->created_at)
                                            <p>{{ $photo->created_at->diffForHumans() }}</p>
                                        @endif
                                        <p class="opacity-75">Click to view full size</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Toast Notifications -->
        <script>
            window.addEventListener('user-updated', event => {
                alert(event.detail[0].message);
            });
        </script>
    @endif
</div>