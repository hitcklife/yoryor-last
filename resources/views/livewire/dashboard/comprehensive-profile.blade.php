<div class="max-w-4xl mx-auto p-6 space-y-8">
    <!-- Header Section -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            My Profile
        </h1>
        <div class="flex items-center justify-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                <span>{{ $profileCompletion }}% Complete</span>
            </div>
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                </svg>
                <span>{{ $user->profile?->profile_views ?? 0 }} Profile Views</span>
            </div>
        </div>
    </div>

    <!-- Profile Content with Alternating Layout -->
    <div class="space-y-8">
        @php
            $sections = array_values($profileData);
            $photos = $photos;
            $photoIndex = 0;
            $sectionIndex = 0;
        @endphp

        @while($sectionIndex < count($sections) || $photoIndex < count($photos))
            @if($sectionIndex < count($sections))
                @php $section = $sections[$sectionIndex]; @endphp
                
                <!-- Information Section -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-pink-500 to-purple-600 p-6">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                @if($section['icon'] === 'user')
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($section['icon'] === 'globe')
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($section['icon'] === 'heart')
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($section['icon'] === 'briefcase')
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                        <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                                    </svg>
                                @elseif($section['icon'] === 'users')
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                    </svg>
                                @elseif($section['icon'] === 'star')
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endif
                            </div>
                            <h2 class="text-xl font-bold text-white">{{ $section['title'] }}</h2>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($section['data'] as $label => $value)
                                @if($value)
                                    <div class="flex flex-col space-y-1">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                            {{ $label }}
                                        </span>
                                        <span class="text-gray-900 dark:text-white font-medium">
                                            {{ $value }}
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                
                @php $sectionIndex++; @endphp
            @endif

            @if($photoIndex < count($photos))
                @php $photo = $photos[$photoIndex]; @endphp
                
                <!-- Photo Section -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                    <div class="relative">
                        <img 
                            src="{{ $photo['url'] }}" 
                            alt="Profile Photo {{ $photo['order'] + 1 }}"
                            class="w-full h-96 object-cover"
                            loading="lazy"
                        >
                        
                        <!-- Photo Badge -->
                        <div class="absolute top-4 right-4">
                            @if($photo['is_profile'])
                                <span class="bg-pink-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                    Main Photo
                                </span>
                            @else
                                <span class="bg-gray-800 bg-opacity-75 text-white px-3 py-1 rounded-full text-sm font-medium">
                                    Photo {{ $photo['order'] + 1 }}
                                </span>
                            @endif
                        </div>
                        
                        <!-- Verification Badge -->
                        @if($photo['verified'])
                            <div class="absolute top-4 left-4">
                                <div class="bg-green-500 text-white p-2 rounded-full">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                @php $photoIndex++; @endphp
            @endif
        @endwhile
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center pt-8">
        <a href="{{ route('settings.profile') }}" 
           class="bg-pink-500 hover:bg-pink-600 text-white px-8 py-3 rounded-full font-medium transition-colors duration-200 text-center">
            Edit Profile
        </a>
        <a href="{{ route('settings.photos') }}" 
           class="bg-purple-500 hover:bg-purple-600 text-white px-8 py-3 rounded-full font-medium transition-colors duration-200 text-center">
            Manage Photos
        </a>
        <a href="{{ route('user.profile.show', $user->id) }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-full font-medium transition-colors duration-200 text-center">
            View Public Profile
        </a>
    </div>

    <!-- Profile Completion Tips -->
    @if($profileCompletion < 100)
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6 mt-8">
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-3">
                Complete Your Profile
            </h3>
            <p class="text-blue-800 dark:text-blue-200 mb-4">
                A complete profile gets {{ rand(3, 5) }}x more matches! Add more information to increase your chances of finding the perfect match.
            </p>
            <div class="space-y-2">
                @if(empty($user->profile?->bio))
                    <div class="flex items-center space-x-2 text-blue-700 dark:text-blue-300">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Add a compelling bio</span>
                    </div>
                @endif
                @if(count($photos) < 3)
                    <div class="flex items-center space-x-2 text-blue-700 dark:text-blue-300">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Add more photos ({{ 3 - count($photos) }} more recommended)</span>
                    </div>
                @endif
                @if(empty($user->culturalProfile))
                    <div class="flex items-center space-x-2 text-blue-700 dark:text-blue-300">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Complete cultural & religious information</span>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
