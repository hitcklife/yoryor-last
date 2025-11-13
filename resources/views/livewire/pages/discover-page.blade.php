<!-- Modern Discover Page - Professional Dating Interface -->
<div x-data="discoverPage()" x-init="init()" class="h-screen overflow-hidden">
    <!-- Breadcrumb Navigation -->
    <x-navigation.breadcrumb />
    
    <div class="h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-900 transition-colors duration-300 flex">

        <!-- Left Navigation Sidebar -->
        <x-navigation-sidebar mode="full" />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <div class="bg-white/80 dark:bg-zinc-800/80 backdrop-blur-sm border-b border-gray-200 dark:border-zinc-700 px-6 py-4 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                            <div class="w-10 h-10 mr-3 bg-gradient-to-r from-pink-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg">
                                <x-lucide-heart class="w-6 h-6 text-white" />
                            </div>
                            Discover
                        </h1>
                        @if($currentUser)
                            <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                                <x-lucide-users class="w-4 h-4" />
                                <span>Finding your perfect match</span>
                            </div>
                        @endif
                    </div>

                    <!-- Filter and Sort Controls -->
                    <div class="flex items-center space-x-3">
                        <!-- Sort Dropdown -->
                        <div class="relative">
                            <select wire:model="sortBy" wire:change="applyFilters" 
                                    class="appearance-none bg-white dark:bg-zinc-700 border border-gray-200 dark:border-zinc-600 rounded-xl px-4 py-2 pr-8 text-sm font-medium text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                <option value="compatibility">Best Match</option>
                                <option value="distance">Distance</option>
                                <option value="activity">Recently Active</option>
                                <option value="age">Age</option>
                            </select>
                            <x-lucide-chevron-down class="w-4 h-4 absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none" />
                        </div>
                        
                        <!-- Filter Toggle Button -->
                        <button @click="showFilters = !showFilters" 
                                class="flex items-center space-x-2 px-4 py-2 bg-white dark:bg-zinc-700 border border-gray-200 dark:border-zinc-600 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-600 transition-all duration-200 shadow-sm">
                            <x-lucide-filter class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Filters</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex-1 flex overflow-hidden">
                <!-- Filters Sidebar -->
                <div x-show="showFilters" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="-translate-x-full opacity-0"
                     x-transition:enter-end="translate-x-0 opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="translate-x-0 opacity-100"
                     x-transition:leave-end="-translate-x-full opacity-0"
                     class="w-80 bg-white dark:bg-zinc-800 border-r border-gray-200 dark:border-zinc-700 overflow-y-auto flex-shrink-0">
                    
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filter Matches</h3>
                            <button @click="showFilters = false" class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-lg">
                                <x-lucide-x class="w-5 h-5 text-gray-500" />
                            </button>
                        </div>

                        <!-- Filter Presets -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Quick Filters</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" wire:click="applyFilterPreset('nearby')" 
                                        class="px-3 py-2 text-xs font-medium rounded-lg border border-gray-200 dark:border-zinc-600 hover:bg-pink-50 dark:hover:bg-pink-900/20 hover:border-pink-300 dark:hover:border-pink-600 transition-all">
                                    Nearby
                                </button>
                                <button type="button" wire:click="applyFilterPreset('highly_compatible')" 
                                        class="px-3 py-2 text-xs font-medium rounded-lg border border-gray-200 dark:border-zinc-600 hover:bg-pink-50 dark:hover:bg-pink-900/20 hover:border-pink-300 dark:hover:border-pink-600 transition-all">
                                    Best Match
                                </button>
                                <button type="button" wire:click="applyFilterPreset('recently_active')" 
                                        class="px-3 py-2 text-xs font-medium rounded-lg border border-gray-200 dark:border-zinc-600 hover:bg-pink-50 dark:hover:bg-pink-900/20 hover:border-pink-300 dark:hover:border-pink-600 transition-all">
                                    Active Now
                                </button>
                                <button type="button" wire:click="applyFilterPreset('marriage_ready')" 
                                        class="px-3 py-2 text-xs font-medium rounded-lg border border-gray-200 dark:border-zinc-600 hover:bg-pink-50 dark:hover:bg-pink-900/20 hover:border-pink-300 dark:hover:border-pink-600 transition-all">
                                    Marriage Ready
                                </button>
                                <button type="button" wire:click="applyFilterPreset('professionals')" 
                                        class="px-3 py-2 text-xs font-medium rounded-lg border border-gray-200 dark:border-zinc-600 hover:bg-pink-50 dark:hover:bg-pink-900/20 hover:border-pink-300 dark:hover:border-pink-600 transition-all">
                                    Professionals
                                </button>
                                <button type="button" wire:click="applyFilterPreset('health_conscious')" 
                                        class="px-3 py-2 text-xs font-medium rounded-lg border border-gray-200 dark:border-zinc-600 hover:bg-pink-50 dark:hover:bg-pink-900/20 hover:border-pink-300 dark:hover:border-pink-600 transition-all">
                                    Health Conscious
                                </button>
                            </div>
                        </div>

                        <form wire:submit.prevent="applyFilters" class="space-y-6">
                            <!-- Age Range -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Age Range</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Min Age</label>
                                        <input type="number" wire:model="ageMin" min="18" max="100" 
                                               class="w-full px-3 py-2 border border-gray-200 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Max Age</label>
                                        <input type="number" wire:model="ageMax" min="18" max="100" 
                                               class="w-full px-3 py-2 border border-gray-200 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            <!-- Religion -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Religion</label>
                                <select wire:model="religion" class="w-full px-3 py-2 border border-gray-200 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                    <option value="">Any Religion</option>
                                    <option value="muslim">Muslim</option>
                                    <option value="christian">Christian</option>
                                    <option value="secular">Secular</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <!-- Education -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Education</label>
                                <select wire:model="education" class="w-full px-3 py-2 border border-gray-200 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                    <option value="">Any Education</option>
                                    <option value="high_school">High School</option>
                                    <option value="associate">Associate Degree</option>
                                    <option value="bachelor">Bachelor's Degree</option>
                                    <option value="master">Master's Degree</option>
                                    <option value="doctorate">Doctorate</option>
                                    <option value="professional">Professional</option>
                                </select>
                            </div>

                            <!-- Marriage Intention -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Marriage Intention</label>
                                <select wire:model="marriageIntention" class="w-full px-3 py-2 border border-gray-200 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                    <option value="">Any Intention</option>
                                    <option value="seeking_marriage">Seeking Marriage</option>
                                    <option value="open_to_marriage">Open to Marriage</option>
                                    <option value="not_ready_yet">Not Ready Yet</option>
                                    <option value="undecided">Undecided</option>
                                </select>
                            </div>

                            <!-- Lifestyle Filters -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Smoking</label>
                                <select wire:model="smokingHabit" class="w-full px-3 py-2 border border-gray-200 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                    <option value="">Any</option>
                                    <option value="never">Never</option>
                                    <option value="socially">Socially</option>
                                    <option value="regularly">Regularly</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Drinking</label>
                                <select wire:model="drinkingHabit" class="w-full px-3 py-2 border border-gray-200 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                                    <option value="">Any</option>
                                    <option value="never">Never</option>
                                    <option value="socially">Socially</option>
                                    <option value="occasionally">Occasionally</option>
                                    <option value="regularly">Regularly</option>
                                </select>
                            </div>

                            <!-- Quick Filters -->
                            <div class="space-y-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quick Filters</label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="showOnlineOnly" class="rounded border-gray-300 text-pink-500 focus:ring-pink-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Online Only</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="showVerifiedOnly" class="rounded border-gray-300 text-pink-500 focus:ring-pink-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Verified Only</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="pt-4 border-t border-gray-200 dark:border-zinc-700 space-y-3">
                                <button type="submit" 
                                        class="w-full bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold py-3 px-4 rounded-xl hover:from-pink-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                    Apply Filters
                                </button>
                                <button type="button" wire:click="resetFilters"
                                        class="w-full bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 font-semibold py-3 px-4 rounded-xl hover:bg-gray-200 dark:hover:bg-zinc-600 transition-all duration-200">
                                    Reset Filters
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 flex flex-col overflow-hidden">
                    <!-- Filter Card -->
                    <div class="p-4 lg:p-8 pb-0">
                        <div class="max-w-7xl mx-auto">
                            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-lg border border-gray-100 dark:border-zinc-700 p-6">
                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                    <!-- Filter Presets -->
                                    <div class="flex flex-wrap gap-2">
                                        <button wire:click="applyFilterPreset('nearby')" 
                                                class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ $filterPreset === 'nearby' ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-blue-100 dark:hover:bg-blue-900/30' }}">
                                            <x-lucide-map-pin class="w-4 h-4 inline mr-1" />
                                            Nearby
                                        </button>
                                        <button wire:click="applyFilterPreset('highly_compatible')" 
                                                class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ $filterPreset === 'highly_compatible' ? 'bg-pink-500 text-white' : 'bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-pink-100 dark:hover:bg-pink-900/30' }}">
                                            <x-lucide-heart class="w-4 h-4 inline mr-1" />
                                            Best Match
                                        </button>
                                        <button wire:click="applyFilterPreset('active_now')" 
                                                class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ $filterPreset === 'active_now' ? 'bg-green-500 text-white' : 'bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-green-100 dark:hover:bg-green-900/30' }}">
                                            <x-lucide-circle class="w-4 h-4 inline mr-1" />
                                            Active Now
                                        </button>
                                        <button wire:click="applyFilterPreset('marriage_ready')" 
                                                class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ $filterPreset === 'marriage_ready' ? 'bg-purple-500 text-white' : 'bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-purple-100 dark:hover:bg-purple-900/30' }}">
                                            <x-lucide-heart-handshake class="w-4 h-4 inline mr-1" />
                                            Marriage Ready
                                        </button>
                                    </div>
                                    
                                    <!-- Sort Dropdown -->
                                    <div class="flex items-center gap-3">
                                        <x-lucide-sort-asc class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                        <select wire:model="sortBy" class="bg-gray-50 dark:bg-zinc-700 border border-gray-200 dark:border-zinc-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="compatibility">Best Match</option>
                                            <option value="distance">Distance</option>
                                            <option value="activity">Recently Active</option>
                                            <option value="age">Age</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Card Container -->
                    <div class="flex-1 p-4 lg:p-8 pt-4 overflow-y-auto min-h-0">
                        @if($loading)
                            <!-- Loading State -->
                            <div class="w-full max-w-6xl mx-auto">
                                <div class="bg-white dark:bg-zinc-800 rounded-3xl shadow-2xl overflow-hidden border border-gray-100 dark:border-zinc-700 animate-pulse">
                                    <div class="aspect-[3/2] bg-gray-200 dark:bg-zinc-700"></div>
                                    <div class="p-6 space-y-4">
                                        <div class="h-6 bg-gray-200 dark:bg-zinc-700 rounded"></div>
                                        <div class="h-4 bg-gray-200 dark:bg-zinc-700 rounded w-3/4"></div>
                                        <div class="h-4 bg-gray-200 dark:bg-zinc-700 rounded w-1/2"></div>
                                    </div>
                                </div>
                            </div>
                        @elseif($currentUser)
                            <!-- Profile Card - Two Column Layout -->
                            <div class="w-full max-w-7xl mx-auto">
                                <div class="bg-white dark:bg-zinc-800 rounded-3xl shadow-2xl overflow-hidden border border-gray-100 dark:border-zinc-700 transform hover:scale-[1.02] transition-all duration-300">
                                    <div class="flex flex-col lg:flex-row">
                                        <!-- Left Column - Image and Basic Info -->
                                        <div class="lg:w-1/2">
                                            <div class="relative">
                                                @if($currentUser['primary_photo'] && !empty($currentUser['primary_photo']))
                                                    <img src="{{ $currentUser['primary_photo'] }}" 
                                                         alt="{{ $currentUser['name'] }}" 
                                                         class="w-full aspect-[4/5] object-cover"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="w-full aspect-[4/5] bg-gradient-to-br from-gray-200 to-gray-300 dark:from-zinc-700 dark:to-zinc-600 flex items-center justify-center" style="display: none;">
                                                        <div class="text-center">
                                                            <x-lucide-user class="w-20 h-20 text-gray-400 dark:text-gray-500 mx-auto mb-2" />
                                                            <p class="text-sm text-gray-500 dark:text-gray-400">Image not available</p>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="w-full aspect-[4/5] bg-gradient-to-br from-gray-200 to-gray-300 dark:from-zinc-700 dark:to-zinc-600 flex items-center justify-center">
                                                        <div class="text-center">
                                                            <x-lucide-user class="w-20 h-20 text-gray-400 dark:text-gray-500 mx-auto mb-2" />
                                                            <p class="text-sm text-gray-500 dark:text-gray-400">No photo available</p>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                <!-- Gradient Overlay -->
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent"></div>
                                                
                                                <!-- Floating Action Buttons -->
                                                <div class="absolute top-4 right-4 z-20 flex space-x-2">
                                                    <button wire:click="passProfile" 
                                                            class="w-10 h-10 bg-white/90 dark:bg-zinc-800/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-all group hover:bg-red-50 dark:hover:bg-red-900/30">
                                                        <x-lucide-x class="w-5 h-5 text-gray-700 dark:text-gray-300 group-hover:text-red-500" />
                                                    </button>
                                                    
                                                    <button wire:click="superLikeProfile" 
                                                            class="w-10 h-10 bg-white/90 dark:bg-zinc-800/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-all group hover:bg-blue-50 dark:hover:bg-blue-900/30">
                                                        <x-lucide-star class="w-5 h-5 text-gray-700 dark:text-gray-300 group-hover:text-blue-500" />
                                                    </button>
                                                    
                                                    <button wire:click="likeProfile" 
                                                            class="w-10 h-10 bg-white/90 dark:bg-zinc-800/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-all group hover:bg-pink-50 dark:hover:bg-pink-900/30">
                                                        <x-lucide-heart class="w-5 h-5 text-gray-700 dark:text-gray-300 group-hover:text-pink-500" />
                                                    </button>
                                                </div>
                                                
                                                <!-- User Info Overlay -->
                                                <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                                    <div class="flex items-end justify-between">
                                                        <div class="flex-1">
                                                            <div class="flex items-center space-x-3 mb-2">
                                                                <h2 class="text-3xl font-bold">{{ $currentUser['name'] }}</h2>
                                                                <span class="text-2xl font-light opacity-90">{{ $currentUser['age'] }}</span>
                                                                @if($currentUser['verified'])
                                                                    <div class="bg-blue-500 w-6 h-6 rounded-full flex items-center justify-center">
                                                                        <x-lucide-check class="w-4 h-4 text-white" />
                                                                    </div>
                                                                @endif
                                                                @if($currentUser['is_online'])
                                                                    <div class="flex items-center space-x-1 bg-green-500/20 backdrop-blur-sm px-2 py-1 rounded-full">
                                                                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                                                        <span class="text-xs font-medium">Online</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            
                                                            <div class="flex items-center space-x-4 text-sm opacity-90">
                                                                @if($currentUser['location'])
                                                                    <div class="flex items-center">
                                                                        <x-lucide-map-pin class="w-4 h-4 mr-1" />
                                                                        <span>{{ $currentUser['location'] }}</span>
                                                                    </div>
                                                                @endif
                                                                @if($currentUser['distance'])
                                                                    <span>•</span>
                                                                    <span>{{ $currentUser['distance'] }} km away</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Compatibility Score -->
                                                        <div class="text-right">
                                                            <div class="bg-gradient-to-r from-pink-500/20 to-purple-500/20 backdrop-blur-sm rounded-xl p-3 border border-white/20">
                                                                <div class="text-2xl font-bold text-pink-300">{{ $currentUser['compatibility_score'] }}%</div>
                                                                <div class="text-xs opacity-90 font-medium">Match</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Additional Photos Thumbnails -->
                                            @if($currentUser['photos'] && count($currentUser['photos']) > 1)
                                                <div class="p-4 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-zinc-700 dark:to-zinc-800 border-t border-gray-200 dark:border-zinc-600">
                                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                                                        <x-lucide-images class="w-4 h-4 mr-2" />
                                                        More Photos
                                                    </h4>
                                                    <div class="flex space-x-3 overflow-x-auto pb-2">
                                                        @foreach($currentUser['photos'] as $photo)
                                                            <div class="flex-shrink-0 w-20 h-20 rounded-xl overflow-hidden border-2 border-transparent hover:border-pink-500 transition-all duration-200 cursor-pointer shadow-md hover:shadow-lg">
                                                                <img src="{{ $photo['thumbnail'] ?? $photo['url'] }}" 
                                                                     alt="Photo {{ $loop->iteration }}" 
                                                                     class="w-full h-full object-cover hover:scale-105 transition-transform duration-200">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Right Column - Detailed Information -->
                                        <div class="lg:w-1/2 p-6 lg:p-8 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-zinc-800 dark:to-zinc-900">

                                            <!-- Bio Section -->
                                            @if($currentUser['bio'])
                                                <div class="mb-6">
                                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center">
                                                        <x-lucide-message-square class="w-5 h-5 mr-2 text-purple-600" />
                                                        About Me
                                                    </h3>
                                                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-600 shadow-lg hover:shadow-xl transition-all duration-200">
                                                        <p class="text-gray-700 dark:text-gray-300 italic leading-relaxed text-base">"{{ $currentUser['bio'] }}"</p>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Information Cards Grid -->
                                            <div class="space-y-4">
                                                <!-- Basic Information Card -->
                                                <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-600 shadow-lg hover:shadow-xl transition-all duration-200">
                                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                                        <x-lucide-user class="w-5 h-5 mr-2 text-blue-600" />
                                                        Basic Info
                                                    </h4>
                                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                                        @if($currentUser['gender'])
                                                            <div>
                                                                <span class="text-gray-500 dark:text-gray-400">Gender</span>
                                                                <div class="font-medium text-gray-900 dark:text-white capitalize">{{ $currentUser['gender'] }}</div>
                                                            </div>
                                                        @endif
                                                        @if($currentUser['height'])
                                                            <div>
                                                                <span class="text-gray-500 dark:text-gray-400">Height</span>
                                                                <div class="font-medium text-gray-900 dark:text-white">{{ $currentUser['height'] }}cm</div>
                                                            </div>
                                                        @endif
                                                        @if($currentUser['looking_for'])
                                                            <div>
                                                                <span class="text-gray-500 dark:text-gray-400">Looking For</span>
                                                                <div class="font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $currentUser['looking_for']) }}</div>
                                                            </div>
                                                        @endif
                                                        @if($currentUser['occupation'])
                                                            <div>
                                                                <span class="text-gray-500 dark:text-gray-400">Occupation</span>
                                                                <div class="font-medium text-gray-900 dark:text-white">{{ $currentUser['occupation'] }}</div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Cultural & Religious Card -->
                                                @if($currentUser['religion'] || $currentUser['ethnicity'])
                                                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-600 shadow-lg hover:shadow-xl transition-all duration-200">
                                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                                            <x-lucide-globe class="w-5 h-5 mr-2 text-indigo-600" />
                                                            Cultural
                                                        </h4>
                                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                                            @if($currentUser['religion'])
                                                                <div>
                                                                    <span class="text-gray-500 dark:text-gray-400">Religion</span>
                                                                    <div class="font-medium text-gray-900 dark:text-white capitalize">{{ $currentUser['religion'] }}</div>
                                                                </div>
                                                            @endif
                                                            @if($currentUser['ethnicity'])
                                                                <div>
                                                                    <span class="text-gray-500 dark:text-gray-400">Ethnicity</span>
                                                                    <div class="font-medium text-gray-900 dark:text-white capitalize">{{ $currentUser['ethnicity'] }}</div>
                                                                </div>
                                                            @endif
                                                            @if($currentUser['religiousness_level'])
                                                                <div>
                                                                    <span class="text-gray-500 dark:text-gray-400">Religiousness</span>
                                                                    <div class="font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $currentUser['religiousness_level']) }}</div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Career & Education Card -->
                                                @if($currentUser['education'] || $currentUser['work_status'])
                                                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-600 shadow-lg hover:shadow-xl transition-all duration-200">
                                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                                            <x-lucide-graduation-cap class="w-5 h-5 mr-2 text-green-600" />
                                                            Career
                                                        </h4>
                                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                                            @if($currentUser['education'])
                                                                <div>
                                                                    <span class="text-gray-500 dark:text-gray-400">Education</span>
                                                                    <div class="font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $currentUser['education']) }}</div>
                                                                </div>
                                                            @endif
                                                            @if($currentUser['work_status'])
                                                                <div>
                                                                    <span class="text-gray-500 dark:text-gray-400">Work Status</span>
                                                                    <div class="font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $currentUser['work_status']) }}</div>
                                                                </div>
                                                            @endif
                                                            @if($currentUser['job_title'])
                                                                <div>
                                                                    <span class="text-gray-500 dark:text-gray-400">Job Title</span>
                                                                    <div class="font-medium text-gray-900 dark:text-white">{{ $currentUser['job_title'] }}</div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Lifestyle Card -->
                                                @if($currentUser['smoking_habit'] || $currentUser['drinking_habit'] || $currentUser['exercise_frequency'])
                                                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-600 shadow-lg hover:shadow-xl transition-all duration-200">
                                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                                            <x-lucide-heart class="w-5 h-5 mr-2 text-green-600" />
                                                            Lifestyle
                                                        </h4>
                                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                                            @if($currentUser['smoking_habit'])
                                                                <div>
                                                                    <span class="text-gray-500 dark:text-gray-400">Smoking</span>
                                                                    <div class="font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $currentUser['smoking_habit']) }}</div>
                                                                </div>
                                                            @endif
                                                            @if($currentUser['drinking_habit'])
                                                                <div>
                                                                    <span class="text-gray-500 dark:text-gray-400">Drinking</span>
                                                                    <div class="font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $currentUser['drinking_habit']) }}</div>
                                                                </div>
                                                            @endif
                                                            @if($currentUser['exercise_frequency'])
                                                                <div>
                                                                    <span class="text-gray-500 dark:text-gray-400">Exercise</span>
                                                                    <div class="font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $currentUser['exercise_frequency']) }}</div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Family & Marriage Card -->
                                                @if($currentUser['marriage_intention'] || $currentUser['children_preference'])
                                                    <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-600 shadow-lg hover:shadow-xl transition-all duration-200">
                                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                                            <x-lucide-home class="w-5 h-5 mr-2 text-pink-600" />
                                                            Family
                                                        </h4>
                                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                                            @if($currentUser['marriage_intention'])
                                                                <div>
                                                                    <span class="text-gray-500 dark:text-gray-400">Marriage</span>
                                                                    <div class="font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $currentUser['marriage_intention']) }}</div>
                                                                </div>
                                                            @endif
                                                            @if($currentUser['children_preference'])
                                                                <div>
                                                                    <span class="text-gray-500 dark:text-gray-400">Children</span>
                                                                    <div class="font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $currentUser['children_preference']) }}</div>
                                                                </div>
                                                            @endif
                                                            @if($currentUser['marriage_timeline'])
                                                                <div>
                                                                    <span class="text-gray-500 dark:text-gray-400">Timeline</span>
                                                                    <div class="font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $currentUser['marriage_timeline']) }}</div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Empty State -->
                            <div class="text-center">
                                <div class="w-32 h-32 bg-gradient-to-br from-pink-100 to-purple-100 dark:from-zinc-700 dark:to-zinc-600 rounded-full mx-auto mb-6 flex items-center justify-center">
                                    <x-lucide-users class="w-16 h-16 text-purple-500 dark:text-purple-400" />
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">No more profiles!</h3>
                                <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-sm mx-auto">You've seen everyone in your area. Try adjusting your filters or check back later.</p>
                                <button wire:click="resetFilters" 
                                        class="px-6 py-3 bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold rounded-full hover:from-pink-600 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg">
                                    Reset Filters
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Match Notification Modal -->
    <div x-show="showMatchModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-8 max-w-md mx-4 text-center shadow-2xl">
            <div class="w-20 h-20 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                <x-lucide-heart class="w-10 h-10 text-white" />
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">It's a Match!</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">You and this person liked each other. Start a conversation!</p>
            <button @click="showMatchModal = false" 
                    class="w-full bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold py-3 px-6 rounded-xl hover:from-pink-600 hover:to-purple-700 transition-all duration-200">
                Start Chatting
            </button>
        </div>
    </div>
</div>

<script>
function discoverPage() {
    return {
        showFilters: false,
        showMatchModal: false,

        init() {
            // Listen for match events
            Livewire.on('match-found', () => {
                this.showMatchModal = true;
                setTimeout(() => {
                    this.showMatchModal = false;
                }, 5000);
            });

            // Listen for super like events
            Livewire.on('super-like-sent', () => {
                // You can add a super like notification here
                console.log('Super like sent!');
            });
        }
    }
}
</script>