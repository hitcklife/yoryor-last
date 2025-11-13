<div>
    <!-- Header -->
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Search & Discover</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Find your perfect match with advanced search
                </p>
            </div>
            
            <div class="flex items-center space-x-4">
                <button wire:click="$set('showFilters', !$showFilters)" 
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200 text-sm font-medium">
                    {{ $showFilters ? 'Hide' : 'Show' }} Filters
                </button>
                
                <button wire:click="saveSearch" 
                        class="px-4 py-2 border border-gray-300 dark:border-zinc-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors duration-200 text-sm font-medium">
                    Save Search
                </button>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700 px-6 py-4">
        <div class="max-w-2xl mx-auto">
            <div class="relative">
                <input type="text" 
                       wire:model.live.debounce.300ms="searchTerm"
                       placeholder="Search by name, interests, profession..."
                       class="w-full pl-12 pr-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                @if($searchTerm)
                    <button wire:click="clearSearch" 
                            class="absolute inset-y-0 right-0 pr-4 flex items-center">
                        <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            </div>

            <!-- Search Suggestions -->
            @if(count($suggestions) > 0 && $searchTerm)
                <div class="absolute z-10 w-full mt-1 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-600 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    @foreach($suggestions as $suggestion)
                        <button wire:click="selectSuggestion('{{ $suggestion }}')" 
                                class="w-full px-4 py-2 text-left text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors duration-200">
                            {{ $suggestion }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Filters Panel -->
    @if($showFilters)
        <div class="bg-gray-50 dark:bg-zinc-700 border-b border-gray-200 dark:border-zinc-600 px-6 py-6">
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Age Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Age Range</label>
                        <div class="flex items-center space-x-2">
                            <input type="number" 
                                   wire:model.live="filters.age_min" 
                                   placeholder="Min"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm">
                            <span class="text-gray-500">to</span>
                            <input type="number" 
                                   wire:model.live="filters.age_max" 
                                   placeholder="Max"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm">
                        </div>
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gender</label>
                        <select wire:model.live="filters.gender" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm">
                            <option value="">Any Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="non-binary">Non-binary</option>
                        </select>
                    </div>

                    <!-- Distance -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Distance (km)</label>
                        <input type="range" 
                               wire:model.live="filters.distance" 
                               min="1" max="100" 
                               class="w-full">
                        <div class="text-center text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $filters['distance'] }} km
                        </div>
                    </div>

                    <!-- Education -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Education</label>
                        <select wire:model.live="filters.education" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm">
                            <option value="">Any Education</option>
                            @foreach($this->getEducationLevels() as $level)
                                <option value="{{ $level }}">{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Interests -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Interests</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($this->getAvailableInterests() as $interest)
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           wire:model.live="filters.interests" 
                                           value="{{ $interest }}"
                                           class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $interest }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Additional Filters -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Additional Filters</label>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       wire:model.live="filters.has_photos" 
                                       class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Has Photos</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       wire:model.live="filters.verified_only" 
                                       class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Verified Only</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       wire:model.live="filters.online_only" 
                                       class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Online Now</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-6">
                    <button wire:click="clearFilters" 
                            class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors duration-200 text-sm">
                        Clear All Filters
                    </button>
                    
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Sort by:</span>
                        <select wire:model.live="sortBy" 
                                class="px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm">
                            <option value="relevance">Relevance</option>
                            <option value="newest">Newest</option>
                            <option value="oldest">Oldest</option>
                            <option value="distance">Distance</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Results -->
    <div class="bg-white dark:bg-zinc-800 p-6">
        <div class="max-w-6xl mx-auto">
            <!-- Results Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $totalResults }} {{ $totalResults === 1 ? 'result' : 'results' }} found
                    </h2>
                    @if($searchTerm)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            for "{{ $searchTerm }}"
                        </p>
                    @endif
                </div>
            </div>

            @if($this->results->count() > 0)
                <!-- Results Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($this->results as $user)
                        <div class="bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 overflow-hidden hover:shadow-lg transition-shadow duration-200">
                            <!-- User Photo -->
                            <div class="relative h-48 bg-gradient-to-br from-purple-400 to-pink-500">
                                @if($user->photos->count() > 0)
                                    <img src="{{ $user->photos->first()->medium_url }}" 
                                         alt="{{ $user->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <span class="text-white text-4xl font-bold">
                                            {{ substr($user->name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                                
                                <!-- Online Status -->
                                @if($user->last_seen_at && $user->last_seen_at->gt(now()->subMinutes(5)))
                                    <div class="absolute top-2 right-2 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                                @endif
                            </div>

                            <!-- User Info -->
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        {{ $user->profile?->first_name ?? $user->name }}
                                        @if($user->profile?->age)
                                            <span class="text-gray-600 dark:text-gray-400 font-normal">, {{ $user->profile->age }}</span>
                                        @endif
                                    </h3>
                                    
                                    @if($user->email_verified_at)
                                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </div>

                                @if($user->profile?->city)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        📍 {{ $user->profile->city }}
                                    </p>
                                @endif

                                @if($user->profile?->bio)
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-3 line-clamp-2">
                                        {{ Str::limit($user->profile->bio, 80) }}
                                    </p>
                                @endif

                                <!-- Interests -->
                                @if($user->profile?->interests && count($user->profile->interests) > 0)
                                    <div class="flex flex-wrap gap-1 mb-3">
                                        @foreach(array_slice($user->profile->interests, 0, 3) as $interest)
                                            <span class="px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 text-xs rounded-full">
                                                {{ $interest }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Actions -->
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('user.profile.show', $user) }}" 
                                       class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200 text-sm font-medium text-center">
                                        View Profile
                                    </a>
                                    
                                    <button class="px-3 py-2 border border-gray-300 dark:border-zinc-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-600 transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $this->results->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-zinc-700 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No results found</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Try adjusting your search terms or filters to find more matches.
                    </p>
                    <button wire:click="clearFilters" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200 text-sm font-medium">
                        Clear Filters
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif
</div>
