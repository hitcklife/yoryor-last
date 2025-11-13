<!-- Country Selector Component -->
<div class="fixed top-16 right-4 z-50" x-data="{ 
    isOpen: false, 
    selectedCountry: 'Global',
    searchQuery: '',
    countries: [
        { code: 'global', name: 'Global', flag: '🌍', members: '50,000+' },
        { code: 'us', name: 'USA & Canada', flag: '🇺🇸', members: '12,000+' },
        { code: 'eu', name: 'Europe', flag: '🇪🇺', members: '8,500+' },
        { code: 'ru', name: 'Russia & CIS', flag: '🇷🇺', members: '15,000+' },
        { code: 'kr', name: 'South Korea & Japan', flag: '🇰🇷', members: '4,200+' },
        { code: 'tr', name: 'Turkey', flag: '🇹🇷', members: '3,800+' },
        { code: 'ae', name: 'UAE & Gulf', flag: '🇦🇪', members: '2,500+' },
        { code: 'uz', name: 'Uzbekistan', flag: '🇺🇿', members: '25,000+' }
    ],
    get filteredCountries() {
        if (!this.searchQuery) return this.countries;
        return this.countries.filter(country => 
            country.name.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
    },
    selectCountry(country) {
        this.selectedCountry = country.name;
        this.isOpen = false;
        this.searchQuery = '';
        // Trigger custom event for other components
        window.dispatchEvent(new CustomEvent('country-selected', { detail: country }));
    }
}" @click.away="isOpen = false">
    <!-- Selector Button -->
    <button @click="isOpen = !isOpen" 
            class="flex items-center space-x-3 bg-white/95 backdrop-blur-xl border border-gray-200/40 rounded-2xl px-4 py-3 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 group">
        <span class="text-2xl" x-text="countries.find(c => c.name === selectedCountry)?.flag || '🌍'"></span>
        <div class="text-left">
            <div class="text-sm font-semibold text-gray-900" x-text="selectedCountry"></div>
            <div class="text-xs text-gray-500" x-text="countries.find(c => c.name === selectedCountry)?.members || '50,000+'"></div>
        </div>
        <svg class="w-4 h-4 text-gray-400 transform transition-transform duration-300" 
             :class="{ 'rotate-180': isOpen }" 
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="transform opacity-0 scale-95 translate-y-2"
         x-cloak
         class="absolute right-0 mt-2 w-64 bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/40 py-3 z-50">
        
        <!-- Search Input -->
        <div class="px-4 pb-3 border-b border-gray-200/30">
            <div class="relative">
                <input type="text" 
                       placeholder="Search countries..." 
                       class="w-full px-4 py-2 text-sm bg-gray-50/80 border border-gray-200/40 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-300 transition-all duration-300"
                       x-model="searchQuery">
                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Country List -->
        <div class="max-h-64 overflow-y-auto">
            <template x-for="country in filteredCountries" :key="country.code">
                <button @click="selectCountry(country)"
                        class="w-full flex items-center space-x-3 px-4 py-3 text-left hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group"
                        :class="{ 'bg-gradient-to-r from-blue-50 to-indigo-50': selectedCountry === country.name }">
                    <span class="text-2xl" x-text="country.flag"></span>
                    <div class="flex-1">
                        <div class="text-sm font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200" 
                             x-text="country.name"></div>
                        <div class="text-xs text-gray-500 group-hover:text-blue-600 transition-colors duration-200" 
                             x-text="country.members + ' members'"></div>
                    </div>
                    <div class="w-2 h-2 rounded-full bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                         :class="{ 'opacity-100': selectedCountry === country.name }"></div>
                </button>
            </template>
        </div>

        <!-- Footer -->
        <div class="px-4 pt-3 border-t border-gray-200/30">
            <div class="text-xs text-gray-500 text-center">
                Choose your region to see relevant content
            </div>
        </div>
    </div>
</div>

<!-- Country-specific content overlay -->
<div x-data="{ 
    showContent: false, 
    currentCountry: null,
    init() {
        window.addEventListener('country-selected', (e) => {
            this.currentCountry = e.detail;
            this.showContent = true;
            // Auto-hide after 3 seconds
            setTimeout(() => this.showContent = false, 3000);
        });
    }
}" 
     x-show="showContent"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="transform opacity-0 translate-y-4"
     x-transition:enter-end="transform opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="transform opacity-100 translate-y-0"
     x-transition:leave-end="transform opacity-0 translate-y-4"
     x-cloak
     class="fixed top-28 right-4 z-40">
    <div class="bg-white/95 backdrop-blur-xl border border-gray-200/40 rounded-2xl p-4 shadow-2xl max-w-sm">
        <div class="flex items-center space-x-3 mb-2">
            <span class="text-2xl" x-text="currentCountry?.flag"></span>
            <div>
                <div class="text-sm font-semibold text-gray-900" x-text="'Now viewing: ' + currentCountry?.name"></div>
                <div class="text-xs text-gray-500" x-text="currentCountry?.members + ' members in this region'"></div>
            </div>
        </div>
        <div class="text-xs text-gray-600">
            Content and features will be customized for your selected region.
        </div>
    </div>
</div>

