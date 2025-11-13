<div x-data="discoveryGrid()" x-init="init()">
    <div class="relative">
        <!-- Discovery Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-600 rounded-xl flex items-center justify-center mr-3">
                        <x-lucide-heart class="w-6 h-6 text-white" />
                    </div>
                    Discover Your Match
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 ml-13 mt-1">
                    <span x-text="profiles.length"></span> potential matches available
                </p>
            </div>
            <button @click="showFilterModal = true"
                    class="flex items-center space-x-2 px-4 py-2 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-600 rounded-xl hover:border-purple-500 dark:hover:border-purple-500 transition-all">
                <x-lucide-sliders-horizontal class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Filters</span>
            </button>
        </div>

        <!-- Profile Card - Modern Tinder-like Design -->
        <template x-if="currentProfile">
            <div class="relative max-w-2xl mx-auto">
                <!-- Main Card Container with Shadow -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 transform transition-all hover:scale-[1.01]">

                    <!-- Photo Gallery Section -->
                    <div class="relative">
                        <!-- Main Photo Display -->
                        <div class="relative aspect-[3/4] overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800">
                            <template x-if="currentProfile.primary_photo">
                                <img :src="currentProfile.primary_photo"
                                     :alt="currentProfile.name"
                                     :class="currentProfile.show_privacy_restrictions ? 'w-full h-full object-cover privacy-blur' : 'w-full h-full object-cover'"
                                     class="transition-transform duration-300">
                            </template>
                            <template x-if="!currentProfile.primary_photo">
                                <div class="w-full h-full flex items-center justify-center">
                                    <x-lucide-user class="w-24 h-24 text-gray-400" />
                                </div>
                            </template>
                        
                            <!-- Privacy Overlay for Images -->
                            <template x-if="currentProfile.show_privacy_restrictions">
                                <div class="absolute inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-10">
                                    <div class="text-center text-white px-6">
                                        <div class="w-20 h-20 mx-auto mb-4 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center">
                                            <x-lucide-lock class="w-10 h-10" />
                                        </div>
                                        <p class="text-lg font-semibold mb-1">Private Profile</p>
                                        <p class="text-sm opacity-90">Photos are blurred for privacy</p>
                                    </div>
                                </div>
                            </template>

                            <!-- Photo Navigation Dots -->
                            <template x-if="currentProfile.photos && currentProfile.photos.length > 1">
                                <div class="absolute top-4 left-1/2 transform -translate-x-1/2 flex space-x-1.5 z-20">
                                    <template x-for="(photo, index) in currentProfile.photos.slice(0, 6)">
                                        <button @click="changeMainPhoto(photo)"
                                                class="h-1 rounded-full transition-all"
                                                :class="photo.url === currentProfile.primary_photo ? 'w-8 bg-white' : 'w-6 bg-white/50 hover:bg-white/70'">
                                        </button>
                                    </template>
                                </div>
                            </template>

                            <!-- Online Status Badge -->
                            <template x-if="currentProfile.is_online">
                                <div class="absolute top-6 left-6 z-20">
                                    <div class="flex items-center space-x-2 bg-green-500/90 backdrop-blur-sm px-3 py-1.5 rounded-full">
                                        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                                        <span class="text-white text-xs font-medium">Online</span>
                                    </div>
                                </div>
                            </template>

                            <!-- Gradient Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/20 to-transparent pointer-events-none"></div>

                            <!-- User Info Overlay -->
                            <div class="absolute bottom-0 left-0 right-0 p-6 z-10">
                                <div class="text-white space-y-3">
                                    <!-- Name and Age -->
                                    <div class="flex items-center space-x-3">
                                        <h2 class="text-4xl font-bold" x-text="currentProfile.name"></h2>
                                        <span class="text-3xl font-light opacity-95" x-text="currentProfile.age"></span>
                                        <template x-if="currentProfile.verified">
                                            <div class="bg-blue-500 w-8 h-8 rounded-full flex items-center justify-center">
                                                <x-lucide-check class="w-5 h-5 text-white" />
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Location & Distance -->
                                    <div class="flex items-center flex-wrap gap-3 text-sm">
                                        <template x-if="currentProfile.location">
                                            <div class="flex items-center bg-white/10 backdrop-blur-sm px-3 py-1.5 rounded-lg">
                                                <x-lucide-map-pin class="w-4 h-4 mr-1.5" />
                                                <span x-text="currentProfile.location"></span>
                                            </div>
                                        </template>
                                        <template x-if="currentProfile.distance">
                                            <div class="flex items-center bg-white/10 backdrop-blur-sm px-3 py-1.5 rounded-lg">
                                                <x-lucide-navigation class="w-4 h-4 mr-1.5" />
                                                <span x-text="currentProfile.distance + ' km away'"></span>
                                            </div>
                                        </template>
                                        <template x-if="currentProfile.occupation">
                                            <div class="flex items-center bg-white/10 backdrop-blur-sm px-3 py-1.5 rounded-lg">
                                                <x-lucide-briefcase class="w-4 h-4 mr-1.5" />
                                                <span x-text="currentProfile.occupation"></span>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Compatibility Score -->
                                    <div class="inline-flex items-center bg-gradient-to-r from-pink-500 to-purple-600 px-4 py-2 rounded-full">
                                        <x-lucide-heart class="w-4 h-4 mr-2 text-white" />
                                        <span class="text-sm font-semibold" x-text="currentProfile.compatibility_score + '% Match'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                    <!-- Photo Gallery Section -->
                    <template x-if="currentProfile.photos && currentProfile.photos.length > 1">
                        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                                    <div class="w-9 h-9 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center mr-3">
                                        <x-lucide-images class="w-5 h-5 text-white" />
                                    </div>
                                    Photo Gallery
                                </h4>
                                <span class="text-sm text-gray-500 dark:text-gray-400" x-text="currentProfile.photos.length + ' photos'"></span>
                            </div>

                            <!-- Photo Grid with Hover Effects -->
                            <div class="grid grid-cols-3 gap-3">
                                <template x-for="(photo, index) in currentProfile.photos">
                                    <div class="group relative aspect-square rounded-xl overflow-hidden cursor-pointer transition-all hover:scale-105 hover:shadow-xl"
                                         :class="currentProfile.show_privacy_restrictions ? 'privacy-blur' : ''"
                                         @click="changeMainPhoto(photo)">
                                        <!-- Photo Image -->
                                        <img :src="photo.thumbnail || photo.url"
                                             :alt="currentProfile.name + ' photo ' + (index + 1)"
                                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">

                                        <!-- Hover Overlay -->
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3">
                                            <div class="flex items-center space-x-2 text-white text-xs font-medium">
                                                <x-lucide-eye class="w-4 h-4" />
                                                <span>View Full</span>
                                            </div>
                                        </div>

                                        <!-- Privacy Overlay -->
                                        <template x-if="currentProfile.show_privacy_restrictions">
                                            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center">
                                                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                                                    <x-lucide-lock class="w-5 h-5 text-white" />
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Active Photo Indicator -->
                                        <div class="absolute top-2 right-2 text-white text-xs font-semibold"
                                             :class="photo.url === currentProfile.primary_photo ? 'bg-purple-600' : 'bg-black/50'"
                                             class="px-2 py-1 rounded-full backdrop-blur-sm">
                                            <span x-text="index + 1"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    
                    <!-- Bio Section -->
                    <template x-if="currentProfile.bio || currentProfile.show_privacy_restrictions">
                        <div class="bg-white dark:bg-gray-800 relative">
                            <template x-if="currentProfile.show_privacy_restrictions">
                                <!-- Privacy Overlay for Bio -->
                                <div class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm flex items-center justify-center z-10 rounded-r-lg">
                                    <div class="text-center">
                                        <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Private Bio</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Personal information is hidden</p>
                                    </div>
                                </div>
                            </template>
                            
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                <h4 class="font-bold text-gray-900 dark:text-white text-lg flex items-center">
                                    <x-lucide-message-square class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" />
                                    About Me
                                </h4>
                            </div>
                            <div class="p-6">
                                <template x-if="currentProfile.bio && !currentProfile.show_privacy_restrictions">
                                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed italic" x-text="'&quot;' + currentProfile.bio + '&quot;'"></p>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Basic Information Section -->
                    <template x-if="currentProfile.gender || currentProfile.looking_for || currentProfile.height || currentProfile.weight || currentProfile.show_privacy_restrictions">
                        <div class="bg-white dark:bg-gray-800 relative">
                            <template x-if="currentProfile.show_privacy_restrictions">
                                <!-- Privacy Overlay for Basic Info -->
                                <div class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm flex items-center justify-center z-10">
                                    <div class="text-center">
                                        <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Private Information</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Personal details are hidden</p>
                                    </div>
                                </div>
                            </template>
                            
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                <h4 class="font-bold text-gray-900 dark:text-white text-lg flex items-center">
                                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                                        <x-lucide-user class="w-5 h-5 text-white" />
                                    </div>
                                    Basic Information
                                </h4>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <template x-if="currentProfile.gender">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Gender:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.gender.charAt(0).toUpperCase() + currentProfile.gender.slice(1)"></span>
                                        </div>
                                    </template>
                                    <template x-if="currentProfile.looking_for">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Looking For:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.looking_for.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                                        </div>
                                    </template>
                                    <template x-if="currentProfile.height && !currentProfile.show_privacy_restrictions">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Height:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.height + 'cm'"></span>
                                        </div>
                                    </template>
                                    <template x-if="currentProfile.weight && !currentProfile.show_privacy_restrictions">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Weight:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.weight + 'kg'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Cultural & Religious Section -->
                    <template x-if="currentProfile.religion || currentProfile.ethnicity || currentProfile.languages || currentProfile.show_privacy_restrictions">
                        <div class="bg-white dark:bg-gray-800 relative">
                            <template x-if="currentProfile.show_privacy_restrictions">
                                <!-- Privacy Overlay for Cultural Info -->
                                <div class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm flex items-center justify-center z-10">
                                    <div class="text-center">
                                        <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Private Cultural Info</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Cultural details are hidden</p>
                                    </div>
                                </div>
                            </template>
                            
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                <h4 class="font-bold text-gray-900 dark:text-white text-lg flex items-center">
                                    <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.083 9h1.946c.089-1.546.383-2.97.837-4.118A6.004 6.004 0 004.083 9zM10 2a8 8 0 100 16 8 8 0 000-16zm0 2c-.076 0-.232.032-.465.262-.238.234-.497.623-.737 1.182-.389.907-.673 2.142-.766 3.556h3.936c-.093-1.414-.377-2.649-.766-3.556-.24-.56-.5-.948-.737-1.182C10.232 4.032 10.076 4 10 4zm3.971 5c-.089-1.546-.383-2.97-.837-4.118A6.004 6.004 0 0115.917 9h-1.946zm-2.003 2H8.032c.093 1.414.377 2.649.766 3.556.24.56.5.948.737 1.182.233.23.389.262.465.262.076 0 .232-.032.465-.262.238-.234.498-.623.737-1.182.389-.907.673-2.142.766-3.556zm1.166 4.118c.454-1.147.748-2.572.837-4.118h1.946a6.004 6.004 0 01-2.783 4.118zm-6.268 0C6.412 13.97 6.118 12.546 6.03 11H4.083a6.004 6.004 0 002.783 4.118z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    Cultural & Religious
                                </h4>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <template x-if="currentProfile.religion && !currentProfile.show_privacy_restrictions">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Religion:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.religion.charAt(0).toUpperCase() + currentProfile.religion.slice(1)"></span>
                                        </div>
                                    </template>
                                    <template x-if="currentProfile.ethnicity && !currentProfile.show_privacy_restrictions">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Ethnicity:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.ethnicity.charAt(0).toUpperCase() + currentProfile.ethnicity.slice(1)"></span>
                                        </div>
                                    </template>
                                </div>
                                <template x-if="currentProfile.languages && currentProfile.languages.length && !currentProfile.show_privacy_restrictions">
                                    <div class="mt-3">
                                        <span class="text-gray-500 dark:text-gray-400 text-sm">Languages:</span>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            <template x-for="language in currentProfile.languages">
                                                <span class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-1 rounded-full text-xs" x-text="language"></span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Career & Education Section -->
                    <template x-if="currentProfile.education || currentProfile.work_status || currentProfile.show_privacy_restrictions">
                        <div class="bg-white dark:bg-gray-800 relative">
                            <template x-if="currentProfile.show_privacy_restrictions">
                                <!-- Privacy Overlay for Career Info -->
                                <div class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm flex items-center justify-center z-10">
                                    <div class="text-center">
                                        <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Private Career Info</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Professional details are hidden</p>
                                    </div>
                                </div>
                            </template>
                            
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                <h4 class="font-bold text-gray-900 dark:text-white text-lg flex items-center">
                                    <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                        </svg>
                                    </div>
                                    Career & Education
                                </h4>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <template x-if="currentProfile.education && !currentProfile.show_privacy_restrictions">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Education:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.education.charAt(0).toUpperCase() + currentProfile.education.slice(1)"></span>
                                        </div>
                                    </template>
                                    <template x-if="currentProfile.work_status && !currentProfile.show_privacy_restrictions">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Work Status:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.work_status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Lifestyle Section -->
                    <template x-if="currentProfile.smoking_habit || currentProfile.drinking_habit || currentProfile.show_privacy_restrictions">
                        <div class="bg-white dark:bg-gray-800 relative">
                            <template x-if="currentProfile.show_privacy_restrictions">
                                <!-- Privacy Overlay for Lifestyle Info -->
                                <div class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm flex items-center justify-center z-10 rounded-lg">
                                    <div class="text-center">
                                        <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Private Lifestyle Info</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Lifestyle details are hidden</p>
                                    </div>
                                </div>
                            </template>
                            
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                <h4 class="font-bold text-gray-900 dark:text-white text-lg flex items-center">
                                    <div class="w-8 h-8 bg-orange-600 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    Lifestyle
                                </h4>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <template x-if="currentProfile.smoking_habit && !currentProfile.show_privacy_restrictions">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Smoking:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.smoking_habit.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                                        </div>
                                    </template>
                                    <template x-if="currentProfile.drinking_habit && !currentProfile.show_privacy_restrictions">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Drinking:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.drinking_habit.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Family & Marriage Section -->
                    <template x-if="currentProfile.marriage_intention || currentProfile.children_preference || currentProfile.show_privacy_restrictions">
                        <div class="bg-white dark:bg-gray-800 relative">
                            <template x-if="currentProfile.show_privacy_restrictions">
                                <!-- Privacy Overlay for Family Info -->
                                <div class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm flex items-center justify-center z-10">
                                    <div class="text-center">
                                        <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Private Family Info</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Family details are hidden</p>
                                    </div>
                                </div>
                            </template>
                            
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                <h4 class="font-bold text-gray-900 dark:text-white text-lg flex items-center">
                                    <div class="w-8 h-8 bg-pink-600 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                                        </svg>
                                    </div>
                                    Family & Marriage
                                </h4>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <template x-if="currentProfile.marriage_intention && !currentProfile.show_privacy_restrictions">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Marriage Intention:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.marriage_intention.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                                        </div>
                                    </template>
                                    <template x-if="currentProfile.children_preference && !currentProfile.show_privacy_restrictions">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Children:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.children_preference.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Interests Section -->
                    <template x-if="(currentProfile.interests && currentProfile.interests.length) || currentProfile.show_privacy_restrictions">
                        <div class="bg-white dark:bg-gray-800 relative">
                            <template x-if="currentProfile.show_privacy_restrictions">
                                <!-- Privacy Overlay for Interests -->
                                <div class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm flex items-center justify-center z-10 rounded-lg">
                                    <div class="text-center">
                                        <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Private Interests</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Personal interests are hidden</p>
                                    </div>
                                </div>
                            </template>
                            
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                <h4 class="font-bold text-gray-900 dark:text-white text-lg flex items-center">
                                    <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </div>
                                    Interests
                                </h4>
                            </div>
                            <div class="p-6">
                                <template x-if="currentProfile.interests && currentProfile.interests.length && !currentProfile.show_privacy_restrictions">
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="interest in currentProfile.interests">
                                            <span class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-1 rounded-full text-sm font-medium" x-text="interest"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Verification Badges Section -->
                    <template x-if="currentProfile.verified_badges && currentProfile.verified_badges.length > 0">
                        <div class="bg-white dark:bg-gray-800">
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                <h4 class="font-bold text-gray-900 dark:text-white text-lg flex items-center">
                                    <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center mr-3">
                                        <x-lucide-shield-check class="w-5 h-5 text-white" />
                                    </div>
                                    Verified
                                </h4>
                            </div>
                            <div class="p-6">
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="badge in currentProfile.verified_badges">
                                        <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-full flex items-center font-medium">
                                            <x-lucide-check class="w-3 h-3 mr-1" />
                                            <span x-text="badge.charAt(0).toUpperCase() + badge.slice(1).replace(/_/g, ' ')"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Location Details Section -->
                    <template x-if="currentProfile.country || currentProfile.state || currentProfile.province">
                        <div class="bg-white dark:bg-gray-800">
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                <h4 class="font-bold text-gray-900 dark:text-white text-lg flex items-center">
                                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center mr-3">
                                        <x-lucide-map-pin class="w-5 h-5 text-white" />
                                    </div>
                                    Location Details
                                </h4>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <template x-if="currentProfile.country">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Country:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.country"></span>
                                        </div>
                                    </template>
                                    <template x-if="currentProfile.state">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">State:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.state"></span>
                                        </div>
                                    </template>
                                    <template x-if="currentProfile.province">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Province:</span>
                                            <span class="ml-1 font-medium text-gray-900 dark:text-white" x-text="currentProfile.province"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    </div>

                    <!-- Action Buttons Section - Tinder Style -->
                    <div class="p-6 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-900 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-center space-x-4">
                            <!-- Pass Button -->
                            <button @click="passProfile(currentProfile.id)"
                                    class="group relative w-16 h-16 bg-white dark:bg-gray-800 border-2 border-red-200 dark:border-red-900 rounded-full flex items-center justify-center shadow-lg hover:shadow-2xl hover:scale-110 hover:border-red-500 transition-all duration-300">
                                <x-lucide-x class="w-8 h-8 text-red-500 group-hover:scale-110 transition-transform" />
                                <span class="absolute -bottom-8 text-xs font-medium text-gray-600 dark:text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">Pass</span>
                            </button>

                            <!-- Super Like Button -->
                            <button @click="superLikeProfile(currentProfile.id)"
                                    class="group relative w-14 h-14 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-full flex items-center justify-center shadow-lg hover:shadow-2xl hover:scale-110 transition-all duration-300">
                                <x-lucide-star class="w-7 h-7 text-white group-hover:rotate-12 transition-transform" />
                                <span class="absolute -bottom-8 text-xs font-medium text-gray-600 dark:text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">Super</span>
                            </button>

                            <!-- Like Button -->
                            <button @click="likeProfile(currentProfile.id)"
                                    class="group relative w-20 h-20 bg-gradient-to-br from-pink-500 to-rose-600 rounded-full flex items-center justify-center shadow-2xl hover:shadow-3xl hover:scale-110 transition-all duration-300">
                                <x-lucide-heart class="w-10 h-10 text-white group-hover:scale-125 transition-transform" />
                                <span class="absolute -bottom-10 text-xs font-medium text-gray-600 dark:text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">Like</span>
                            </button>

                            <!-- Message Button (for matched users) -->
                            <button @click="viewProfile(currentProfile.id)"
                                    class="group relative w-14 h-14 bg-white dark:bg-gray-800 border-2 border-purple-200 dark:border-purple-900 rounded-full flex items-center justify-center shadow-lg hover:shadow-2xl hover:scale-110 hover:border-purple-500 transition-all duration-300">
                                <x-lucide-message-circle class="w-7 h-7 text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform" />
                                <span class="absolute -bottom-8 text-xs font-medium text-gray-600 dark:text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">View</span>
                            </button>
                        </div>

                        <!-- Swipe instruction text -->
                        <div class="text-center mt-6">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Click photos to view more • Tap heart to like
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        
        <!-- Loading State -->
        <template x-if="loading && profiles.length === 0">
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 animate-pulse">
                <div class="aspect-[3/4] bg-gray-200"></div>
            </div>
        </template>
        
        <!-- Empty State -->
        <template x-if="profiles.length === 0 && !loading">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-12 text-center border border-gray-200 dark:border-gray-700">
                <div class="w-32 h-32 bg-gray-100 dark:bg-gray-700 rounded-full mx-auto mb-6 flex items-center justify-center">
                    <x-lucide-users class="w-16 h-16 text-gray-500 dark:text-gray-400" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">You've seen everyone!</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-sm mx-auto">We don't have any new profiles to show you right now. Check back later or adjust your preferences.</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button @click="resetFilters()" 
                            class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-full transition-all transform hover:scale-105 shadow-lg">
                        Reset Filters
                    </button>
                    <button @click="showFilterModal = true" 
                            class="px-6 py-3 bg-white dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-full hover:border-gray-300 dark:hover:border-gray-500 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all">
                        Adjust Preferences
                    </button>
                </div>
            </div>
        </template>

        <!-- Filter Modal -->
        <div x-show="showFilterModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <!-- Background overlay -->
                <div @click="showFilterModal = false" class="fixed inset-0 bg-black bg-opacity-50"></div>

                <!-- Modal content -->
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full mx-auto p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Filters</h3>
                        <button @click="showFilterModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <x-lucide-x class="w-6 h-6" />
                        </button>
                    </div>

                    <!-- Age Range -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Age Range</label>
                        <div class="flex items-center space-x-4">
                            <input type="range" x-model="filters.ageMin" min="18" max="80" class="flex-1">
                            <span x-text="filters.ageMin" class="text-sm text-gray-600 dark:text-gray-400 min-w-[2rem]"></span>
                            <span class="text-gray-400">-</span>
                            <input type="range" x-model="filters.ageMax" min="18" max="80" class="flex-1">
                            <span x-text="filters.ageMax" class="text-sm text-gray-600 dark:text-gray-400 min-w-[2rem]"></span>
                        </div>
                    </div>

                    <!-- Distance -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Maximum Distance</label>
                        <div class="flex items-center space-x-4">
                            <input type="range" x-model="filters.maxDistance" min="1" max="100" class="flex-1">
                            <span x-text="filters.maxDistance + ' km'" class="text-sm text-gray-600 dark:text-gray-400 min-w-[3rem]"></span>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex space-x-3">
                        <button @click="resetFilters()"
                                class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Reset
                        </button>
                        <button @click="applyFilters()"
                                class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                            Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Hide scrollbar for Chrome, Safari and Opera */
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    
    /* Hide scrollbar for IE, Edge and Firefox */
    .scrollbar-hide {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
    
    /* Privacy blur effects */
    .privacy-blur {
        filter: blur(8px);
        -webkit-filter: blur(8px);
        transform: scale(1.1);
    }
    
    /* Privacy overlay animations */
    .privacy-overlay {
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }
    
    /* Privacy lock icon animation */
    @keyframes privacy-pulse {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0.8;
            transform: scale(1.05);
        }
    }
    
    .privacy-lock {
        animation: privacy-pulse 2s ease-in-out infinite;
    }
    
    /* Privacy card hover effects */
    .privacy-card:hover .privacy-overlay {
        background: rgba(255, 255, 255, 0.9);
    }
    
    .dark .privacy-card:hover .privacy-overlay {
        background: rgba(39, 39, 42, 0.9);
    }
</style>

<script>
function discoveryGrid() {
    return {
        profiles: [],
        loading: false,
        hasMore: true,
        page: 1,
        currentIndex: 0,
        showFilterModal: false,
        showFullProfile: false,
        filters: {
            ageMin: 18,
            ageMax: 50,
            maxDistance: 50,
            religion: '',
            education: '',
            profession: ''
        },

        get currentProfile() {
            return this.profiles[this.currentIndex] || null;
        },

        get totalProfiles() {
            return this.profiles.length + (this.hasMore ? '?' : '');
        },

        init() {
            // Initialize with Livewire data
            this.fallbackToLivewire();
        },

        async loadProfiles(append = false) {
            this.loading = true;

            try {
                // Use Livewire data if available, otherwise fallback to demo data
                if (this.profiles && this.profiles.length > 0) {
                    console.log('Using Livewire profiles data...');
                    this.loading = false;
                    return;
                }
                
                console.log('Loading demo profiles...');
                await this.loadDemoProfiles(append);
            } catch (error) {
                console.error('Error loading profiles:', error);
                console.error('Error details:', error.message);
                await this.loadDemoProfiles(append);
            } finally {
                this.loading = false;
            }
        },

        async loadDemoProfiles(append = false) {
            // Demo profiles for development
            const demoProfiles = [
                {
                    id: 1,
                    name: 'Sarah',
                    age: 28,
                    bio: 'Love traveling and trying new cuisines. Looking for someone who shares my passion for adventure.',
                    primary_photo: '/assets/images/premium_photo-1674235766088-80d8410f9523.jpeg',
                    location: 'New York',
                    distance: 2,
                    compatibility_score: 95,
                    verified: true,
                    gender: 'Female',
                    looking_for: 'Long-term relationship',
                    height: '5\'6"',
                    weight: '125 lbs'
                },
                {
                    id: 2,
                    name: 'Emma',
                    age: 25,
                    bio: 'Creative soul who loves art and music. Always up for deep conversations.',
                    primary_photo: '/assets/images/pexels-asadphoto-169196.jpg',
                    location: 'Brooklyn',
                    distance: 5,
                    compatibility_score: 88,
                    verified: false,
                    gender: 'Female',
                    looking_for: 'Dating',
                    height: '5\'4"',
                    weight: '118 lbs'
                },
                {
                    id: 3,
                    name: 'Lisa',
                    age: 30,
                    bio: 'Fitness enthusiast and coffee lover. Seeking someone who values health and wellness.',
                    primary_photo: '/assets/images/538664-married-couple.jpg',
                    location: 'Manhattan',
                    distance: 3,
                    compatibility_score: 92,
                    verified: true,
                    gender: 'Female',
                    looking_for: 'Long-term relationship',
                    height: '5\'7"',
                    weight: '135 lbs'
                }
            ];

            if (append) {
                this.profiles = [...this.profiles, ...demoProfiles];
            } else {
                this.profiles = demoProfiles;
                this.currentIndex = 0;
            }

            this.hasMore = false; // No more demo profiles
        },

        async loadMoreProfiles() {
            if (this.hasMore && !this.loading) {
                this.page++;
                await this.loadProfiles(true);
            }
        },

        async applyFilters() {
            this.page = 1;
            this.showFilterModal = false;
            await this.loadProfiles(false);
        },

        async resetFilters() {
            this.filters = {
                ageMin: 18,
                ageMax: 50,
                maxDistance: 50,
                religion: '',
                education: '',
                profession: ''
            };
            await this.applyFilters();
        },

        nextProfile() {
            if (this.currentIndex < this.profiles.length - 1) {
                this.currentIndex++;
            } else if (this.hasMore) {
                // Load more profiles when reaching the end
                this.loadMoreProfiles().then(() => {
                    if (this.profiles.length > this.currentIndex + 1) {
                        this.currentIndex++;
                    }
                });
            }
        },

        previousProfile() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
            }
        },

        async likeProfile(profileId) {
            try {
                console.log(`Liking profile ${profileId}...`);

                // Demo implementation - simulate like
                // Random chance for match
                const isMatch = Math.random() > 0.7;

                if (isMatch) {
                    this.showMatchNotification();
                    console.log('It\'s a match! 🎉');
                }

                this.removeCurrentProfile();
                console.log('Profile liked successfully');
            } catch (error) {
                console.error('Error liking profile:', error);
            }
        },

        async superLikeProfile(profileId) {
            try {
                console.log(`Super liking profile ${profileId}...`);

                // Demo implementation - simulate super like
                // Higher chance for match with super like
                const isMatch = Math.random() > 0.5;

                if (isMatch) {
                    this.showMatchNotification();
                    console.log('Super Like Match! 💙');
                }

                this.removeCurrentProfile();
                console.log('Profile super liked successfully');
            } catch (error) {
                console.error('Error super liking profile:', error);
            }
        },

        async passProfile(profileId) {
            try {
                console.log(`Passing profile ${profileId}...`);

                // Demo implementation - simulate pass
                this.removeCurrentProfile();
                console.log('Profile passed successfully');
            } catch (error) {
                console.error('Error passing profile:', error);
            }
        },

        viewProfile(profileId) {
            Livewire.dispatch('show-profile-view', { profileId });
        },

        removeCurrentProfile() {
            this.profiles.splice(this.currentIndex, 1);
            
            // Adjust current index if needed
            if (this.currentIndex >= this.profiles.length && this.profiles.length > 0) {
                this.currentIndex = this.profiles.length - 1;
            }
            
            // Load more if running low
            if (this.profiles.length < 3 && this.hasMore) {
                this.loadMoreProfiles();
            }
        },

        changeMainPhoto(photo) {
            if (this.currentProfile) {
                // Update the primary photo
                this.currentProfile.primary_photo = photo.url || photo.medium_url || photo.thumbnail;
                
                // Move the selected photo to the front of the photos array
                const photoIndex = this.currentProfile.photos.findIndex(p => p.id === photo.id);
                if (photoIndex > 0) {
                    const selectedPhoto = this.currentProfile.photos.splice(photoIndex, 1)[0];
                    this.currentProfile.photos.unshift(selectedPhoto);
                }
            }
        },

        showMatchNotification() {
            console.log('It\'s a match! 🎉');
            // You can implement a proper match notification here
        },

        fallbackToLivewire() {
            this.profiles = @js($profiles ?? []);
            this.hasMore = @js($hasMore ?? true);
            this.loading = @js($loading ?? false);
            this.currentIndex = 0;
        }
    }
}
</script>
</div>