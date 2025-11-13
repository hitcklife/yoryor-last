@props([
    'title' => 'Where Are You Based?',
    'subtitle' => 'Help us find matches in your area'
])

<div x-data="locationPreferencesStep()" class="space-y-8">
    <!-- Header -->
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $title }}</h1>
        <p class="text-gray-600">{{ $subtitle }}</p>
    </div>

    <!-- Success/Error Messages -->
    <div x-show="$store.registration.successMessage" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center">
        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span x-text="$store.registration.successMessage"></span>
    </div>

    <div x-show="$store.registration.errorMessage" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center">
        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span x-text="$store.registration.errorMessage"></span>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-8">
        <!-- Auto-Detect Location -->
        <x-ui.glass-card padding="base" class="text-center">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Auto-Detect Location</h3>
            <p class="text-gray-600 mb-4">Let us automatically detect your location for better matches</p>
            <x-ui.gradient-button
                variant="glass"
                size="base"
                type="button"
                @click="detectLocation()"
                :loading="detectingLocation"
                class="mx-auto"
                :icon="'<svg class=&quot;w-4 h-4&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z&quot;/></svg>'"
                icon-position="left"
            >
                <span x-show="!detectingLocation">Detect My Location</span>
                <span x-show="detectingLocation">Detecting...</span>
            </x-ui.gradient-button>
        </x-ui.glass-card>

        <!-- Manual Location Entry -->
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-4 bg-white text-gray-500 font-medium">Or enter manually</span>
            </div>
        </div>

        <!-- Country Selection -->
        <div>
            <x-ui.floating-input
                label="Country"
                x-model="countrySearch"
                @input="filterCountries()"
                @focus="showCountryDropdown = true"
                @click.away="showCountryDropdown = false"
                :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z&quot;/></svg>'"
                required="true"
                size="lg"
                class="relative"
            />
            
            <!-- Country Dropdown -->
            <div x-show="showCountryDropdown && filteredCountries.length > 0" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-xl max-h-48 overflow-y-auto">
                
                <template x-for="country in filteredCountries" :key="country.name">
                    <button type="button"
                            @click="selectCountry(country)"
                            class="w-full flex items-center space-x-3 px-4 py-3 hover:bg-pink-50 transition-colors text-left">
                        <span class="text-lg" x-text="country.flag"></span>
                        <span class="font-medium text-gray-900" x-text="country.name"></span>
                    </button>
                </template>
                
                <div x-show="filteredCountries.length === 0 && countrySearch" 
                     class="px-4 py-6 text-center text-gray-500">
                    No countries found
                </div>
            </div>
        </div>

        <!-- State/Province -->
        <div x-show="$store.registration.formData.country">
            <x-ui.floating-input
                label="State / Province"
                x-model="$store.registration.formData.state"
                :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z&quot;/></svg>'"
                size="lg"
            />
        </div>

        <!-- City -->
        <div x-show="$store.registration.formData.country">
            <x-ui.floating-input
                label="City"
                x-model="$store.registration.formData.city"
                :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4&quot;/></svg>'"
                required="true"
                size="lg"
            />
        </div>

        <!-- Privacy Notice -->
        <x-ui.glass-card padding="base" class="bg-blue-50/30">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-blue-900 mb-1">Your Privacy Matters</h4>
                    <p class="text-xs text-blue-700">
                        Your location information is used only to find compatible matches nearby. 
                        You can control who sees your exact location in your privacy settings.
                    </p>
                </div>
            </div>
        </x-ui.glass-card>

        <!-- Navigation Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 pt-8">
            <x-ui.gradient-button
                variant="outline"
                size="lg"
                type="button"
                @click="$store.registration.previousStep()"
                class="flex-1"
                :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M15 19l-7-7 7-7&quot;/></svg>'"
                icon-position="left"
            >
                Previous
            </x-ui.gradient-button>
            
            <x-ui.gradient-button
                variant="primary"
                size="lg"
                type="submit"
                :loading="$store.registration.isLoading"
                :disabled="!canProceed"
                class="flex-1"
                :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M13 7l5 5m0 0l-5 5m5-5H6&quot;/></svg>'"
                icon-position="right"
            >
                Continue
            </x-ui.gradient-button>
        </div>
    </form>
</div>

<script>
function locationPreferencesStep() {
    return {
        // Countries data
        countries: [],
        filteredCountries: [],
        countrySearch: '',
        showCountryDropdown: false,
        
        // Location detection
        detectingLocation: false,
        
        // Computed properties
        get canProceed() {
            return !!this.$store.registration.formData.country?.trim();
        },
        
        async init() {
            // Import country data
            const { countries } = await import('/resources/js/country-data.js');
            this.countries = countries;
            this.filteredCountries = countries;
            
            // Load existing data
            if (this.$store.registration.formData.country) {
                this.countrySearch = this.$store.registration.formData.country;
            }
        },
        
        filterCountries() {
            if (!this.countrySearch) {
                this.filteredCountries = this.countries;
                this.showCountryDropdown = false;
                return;
            }
            
            const query = this.countrySearch.toLowerCase();
            this.filteredCountries = this.countries.filter(country =>
                country.name.toLowerCase().includes(query)
            );
            this.showCountryDropdown = true;
            
            // If exact match, don't show dropdown
            if (this.filteredCountries.length === 1 && 
                this.filteredCountries[0].name.toLowerCase() === query) {
                this.showCountryDropdown = false;
            }
        },
        
        selectCountry(country) {
            this.countrySearch = country.name;
            this.$store.registration.formData.country = country.name;
            this.$store.registration.formData.countryCode = country.code;
            this.showCountryDropdown = false;
        },
        
        async detectLocation() {
            this.detectingLocation = true;
            this.$store.registration.clearMessages();
            
            try {
                // Check if geolocation is supported
                if (!navigator.geolocation) {
                    throw new Error('Geolocation is not supported by this browser');
                }
                
                // Get current position
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 600000 // 10 minutes
                    });
                });
                
                const { latitude, longitude } = position.coords;
                
                // Reverse geocoding using a free service
                const response = await fetch(
                    `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${latitude}&longitude=${longitude}&localityLanguage=en`
                );
                
                if (!response.ok) {
                    throw new Error('Failed to get location information');
                }
                
                const locationData = await response.json();
                
                // Fill in the detected location
                if (locationData.countryName) {
                    this.countrySearch = locationData.countryName;
                    this.$store.registration.formData.country = locationData.countryName;
                    this.$store.registration.formData.countryCode = locationData.countryCode;
                }
                
                if (locationData.principalSubdivision) {
                    this.$store.registration.formData.state = locationData.principalSubdivision;
                }
                
                if (locationData.city || locationData.locality) {
                    this.$store.registration.formData.city = locationData.city || locationData.locality;
                }
                
                this.$store.registration.setSuccess('Location detected successfully!');
                
            } catch (error) {
                console.error('Location detection failed:', error);
                
                let errorMessage = 'Unable to detect your location. ';
                if (error.code === 1) {
                    errorMessage += 'Please allow location access and try again.';
                } else if (error.code === 2) {
                    errorMessage += 'Location information is unavailable.';
                } else if (error.code === 3) {
                    errorMessage += 'Request timed out. Please try again.';
                } else {
                    errorMessage += 'Please enter your location manually.';
                }
                
                this.$store.registration.setError(errorMessage);
                
                // Fallback to IP-based detection
                try {
                    const { detectCountryByIP } = await import('/resources/js/country-data.js');
                    const country = await detectCountryByIP();
                    if (country) {
                        this.countrySearch = country.name;
                        this.$store.registration.formData.country = country.name;
                        this.$store.registration.formData.countryCode = country.code;
                        this.$store.registration.setSuccess('Country detected from your IP address');
                    }
                } catch (ipError) {
                    console.error('IP detection also failed:', ipError);
                }
            } finally {
                this.detectingLocation = false;
            }
        },
        
        async handleSubmit() {
            if (!this.canProceed) {
                this.$store.registration.setError('Please select your country');
                return;
            }
            
            // Save and proceed to next step
            await this.$store.registration.nextStep();
        }
    }
}
</script>