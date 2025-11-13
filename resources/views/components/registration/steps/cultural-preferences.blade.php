@props([
    'title' => 'Cultural & Family Values',
    'subtitle' => 'Help us find someone who shares your values'
])

<div x-data="culturalPreferencesStep()" class="space-y-8">
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
        <!-- Religious Background -->
        <x-ui.glass-card padding="lg">
            <div class="space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Religious & Spiritual Values</h3>
                        <p class="text-sm text-gray-600">Your faith and spiritual beliefs</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Religion -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Religion</label>
                        <select x-model="$store.registration.formData.religion"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors bg-white/50 backdrop-blur-sm">
                            <option value="">Select religion...</option>
                            <template x-for="religion in religions" :key="religion">
                                <option :value="religion" x-text="religion"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Religious Practice Level -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">How religious are you?</label>
                        <select x-model="$store.registration.formData.religiosity"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors bg-white/50 backdrop-blur-sm">
                            <option value="">Select level...</option>
                            <option value="not-religious">Not religious</option>
                            <option value="spiritual">Spiritual but not religious</option>
                            <option value="somewhat">Somewhat religious</option>
                            <option value="moderately">Moderately religious</option>
                            <option value="very">Very religious</option>
                            <option value="orthodox">Orthodox/Traditional</option>
                        </select>
                    </div>
                </div>

                <!-- Prayer Frequency (for religious users) -->
                <div x-show="$store.registration.formData.religiosity && $store.registration.formData.religiosity !== 'not-religious'">
                    <label class="block text-sm font-medium text-gray-900 mb-2">Prayer Frequency</label>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                        <template x-for="frequency in prayerFrequencies" :key="frequency.value">
                            <button type="button"
                                    @click="$store.registration.formData.prayerFrequency = frequency.value"
                                    :class="{
                                        'bg-gradient-to-r from-pink-500 to-purple-600 text-white border-transparent shadow-lg': $store.registration.formData.prayerFrequency === frequency.value,
                                        'bg-white/50 text-gray-700 border-gray-200 hover:bg-white/80': $store.registration.formData.prayerFrequency !== frequency.value
                                    }"
                                    class="px-3 py-3 rounded-xl border text-xs font-medium transition-all duration-200 backdrop-blur-sm text-center">
                                <div x-text="frequency.label"></div>
                                <div class="text-xs opacity-75" x-text="frequency.description"></div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </x-ui.glass-card>

        <!-- Cultural Background -->
        <x-ui.glass-card padding="lg">
            <div class="space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-green-500 to-teal-600 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Cultural Identity</h3>
                        <p class="text-sm text-gray-600">Your cultural background and traditions</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Ethnicity -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Ethnicity</label>
                        <select x-model="$store.registration.formData.ethnicity"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors bg-white/50 backdrop-blur-sm">
                            <option value="">Select ethnicity...</option>
                            <template x-for="ethnicity in ethnicities" :key="ethnicity">
                                <option :value="ethnicity" x-text="ethnicity"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Languages Spoken -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Languages Spoken</label>
                        <div class="relative">
                            <input type="text"
                                   x-model="languageInput"
                                   @keydown.enter.prevent="addLanguage"
                                   @keydown.comma.prevent="addLanguage"
                                   placeholder="Type and press Enter to add..."
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors bg-white/50 backdrop-blur-sm">
                        </div>
                        
                        <!-- Language Tags -->
                        <div class="flex flex-wrap gap-2 mt-2" x-show="selectedLanguages.length > 0">
                            <template x-for="language in selectedLanguages" :key="language">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-pink-100 text-pink-800">
                                    <span x-text="language"></span>
                                    <button type="button" 
                                            @click="removeLanguage(language)"
                                            class="ml-2 text-pink-600 hover:text-pink-800">
                                        ×
                                    </button>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Cultural Values Importance -->
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-3">How important are cultural traditions to you?</label>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                        <template x-for="level in importanceLevels" :key="level.value">
                            <button type="button"
                                    @click="$store.registration.formData.culturalImportance = level.value"
                                    :class="{
                                        'bg-gradient-to-r from-pink-500 to-purple-600 text-white border-transparent shadow-lg': $store.registration.formData.culturalImportance === level.value,
                                        'bg-white/50 text-gray-700 border-gray-200 hover:bg-white/80': $store.registration.formData.culturalImportance !== level.value
                                    }"
                                    class="px-4 py-3 rounded-xl border text-sm font-medium transition-all duration-200 backdrop-blur-sm text-center">
                                <div x-text="level.label"></div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </x-ui.glass-card>

        <!-- Family Values -->
        <x-ui.glass-card padding="lg">
            <div class="space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-rose-500 to-pink-600 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Family & Relationships</h3>
                        <p class="text-sm text-gray-600">Your thoughts on family and future</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Want Children -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Do you want children?</label>
                        <select x-model="$store.registration.formData.wantChildren"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors bg-white/50 backdrop-blur-sm">
                            <option value="">Select...</option>
                            <option value="yes">Yes, definitely</option>
                            <option value="probably">Probably yes</option>
                            <option value="maybe">Maybe</option>
                            <option value="probably-not">Probably not</option>
                            <option value="no">No, never</option>
                            <option value="have-children">I already have children</option>
                        </select>
                    </div>

                    <!-- Number of Children Desired -->
                    <div x-show="$store.registration.formData.wantChildren && !['no', 'probably-not', 'have-children'].includes($store.registration.formData.wantChildren)">
                        <label class="block text-sm font-medium text-gray-900 mb-2">How many children?</label>
                        <select x-model="$store.registration.formData.numberOfChildren"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors bg-white/50 backdrop-blur-sm">
                            <option value="">Select...</option>
                            <option value="1">1 child</option>
                            <option value="2">2 children</option>
                            <option value="3">3 children</option>
                            <option value="4">4+ children</option>
                            <option value="open">Open to discussion</option>
                        </select>
                    </div>

                    <!-- Family Closeness -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Relationship with family</label>
                        <select x-model="$store.registration.formData.familyCloseness"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors bg-white/50 backdrop-blur-sm">
                            <option value="">Select...</option>
                            <option value="very-close">Very close</option>
                            <option value="close">Close</option>
                            <option value="somewhat-close">Somewhat close</option>
                            <option value="distant">Distant</option>
                            <option value="complicated">It's complicated</option>
                        </select>
                    </div>

                    <!-- Marriage Timeline -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">When do you want to get married?</label>
                        <select x-model="$store.registration.formData.marriageTimeline"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors bg-white/50 backdrop-blur-sm">
                            <option value="">Select...</option>
                            <option value="within-year">Within a year</option>
                            <option value="1-2-years">1-2 years</option>
                            <option value="2-5-years">2-5 years</option>
                            <option value="5-plus-years">5+ years</option>
                            <option value="no-timeline">No specific timeline</option>
                            <option value="not-sure">Not sure yet</option>
                        </select>
                    </div>
                </div>

                <!-- Family Involvement in Relationship -->
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-3">How much should family be involved in your relationship decisions?</label>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <template x-for="involvement in familyInvolvement" :key="involvement.value">
                            <button type="button"
                                    @click="$store.registration.formData.familyInvolvement = involvement.value"
                                    :class="{
                                        'bg-gradient-to-r from-pink-500 to-purple-600 text-white border-transparent shadow-lg': $store.registration.formData.familyInvolvement === involvement.value,
                                        'bg-white/50 text-gray-700 border-gray-200 hover:bg-white/80': $store.registration.formData.familyInvolvement !== involvement.value
                                    }"
                                    class="px-4 py-4 rounded-xl border text-sm font-medium transition-all duration-200 backdrop-blur-sm text-center">
                                <div class="font-semibold" x-text="involvement.label"></div>
                                <div class="text-xs opacity-75 mt-1" x-text="involvement.description"></div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </x-ui.glass-card>

        <!-- Additional Preferences -->
        <x-ui.glass-card padding="lg">
            <div class="space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Additional Preferences</h3>
                        <p class="text-sm text-gray-600">Other important considerations</p>
                    </div>
                </div>

                <!-- Deal Breakers -->
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-3">Select any deal breakers (optional)</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <template x-for="dealBreaker in dealBreakers" :key="dealBreaker">
                            <button type="button"
                                    @click="toggleDealBreaker(dealBreaker)"
                                    :class="{
                                        'bg-gradient-to-r from-red-500 to-rose-600 text-white border-transparent shadow-lg': selectedDealBreakers.includes(dealBreaker),
                                        'bg-white/50 text-gray-700 border-gray-200 hover:bg-white/80': !selectedDealBreakers.includes(dealBreaker)
                                    }"
                                    class="px-4 py-3 rounded-xl border text-sm font-medium transition-all duration-200 backdrop-blur-sm text-center">
                                <span x-text="dealBreaker"></span>
                                <span x-show="selectedDealBreakers.includes(dealBreaker)" class="ml-1">✗</span>
                            </button>
                        </template>
                    </div>
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
function culturalPreferencesStep() {
    return {
        // Data
        languageInput: '',
        selectedLanguages: [],
        selectedDealBreakers: [],
        
        // Options
        religions: [
            'Islam', 'Christianity', 'Judaism', 'Hinduism', 'Buddhism', 'Sikhism',
            'Atheist', 'Agnostic', 'Spiritual', 'Other', 'Prefer not to say'
        ],
        
        ethnicities: [
            'Arab', 'Asian', 'Black/African', 'Caucasian/White', 'Hispanic/Latino',
            'Indian', 'Mixed Race', 'Native American', 'Pacific Islander', 
            'Middle Eastern', 'Other', 'Prefer not to say'
        ],
        
        prayerFrequencies: [
            { value: 'daily', label: 'Daily', description: '5x daily' },
            { value: 'weekly', label: 'Weekly', description: 'Fridays' },
            { value: 'monthly', label: 'Monthly', description: 'Special occasions' },
            { value: 'rarely', label: 'Rarely', description: 'Major holidays' },
            { value: 'never', label: 'Never', description: 'Not applicable' }
        ],
        
        importanceLevels: [
            { value: 'not-important', label: 'Not Important' },
            { value: 'slightly', label: 'Slightly Important' },
            { value: 'moderately', label: 'Moderately Important' },
            { value: 'very', label: 'Very Important' },
            { value: 'extremely', label: 'Extremely Important' }
        ],
        
        familyInvolvement: [
            { 
                value: 'minimal', 
                label: 'Minimal', 
                description: 'We decide independently' 
            },
            { 
                value: 'consultation', 
                label: 'Consultation', 
                description: 'We ask for advice' 
            },
            { 
                value: 'collaborative', 
                label: 'Collaborative', 
                description: 'Joint decision making' 
            },
            { 
                value: 'traditional', 
                label: 'Traditional', 
                description: 'Family leads decisions' 
            }
        ],
        
        dealBreakers: [
            'Smoking', 'Heavy Drinking', 'Drug Use', 'Gambling', 
            'Debt Issues', 'No Career Goals', 'Different Religion',
            "Doesn't Want Kids", 'Lives with Parents', 'No Education'
        ],
        
        // Computed properties
        get canProceed() {
            // Basic validation - at least some cultural information provided
            return !!(
                this.$store.registration.formData.religion ||
                this.$store.registration.formData.ethnicity ||
                this.$store.registration.formData.wantChildren
            );
        },
        
        init() {
            // Load existing data
            this.selectedLanguages = this.$store.registration.formData.languages || [];
            this.selectedDealBreakers = this.$store.registration.formData.dealBreakers || [];
        },
        
        addLanguage() {
            const language = this.languageInput.trim();
            if (language && !this.selectedLanguages.includes(language)) {
                this.selectedLanguages.push(language);
                this.$store.registration.formData.languages = this.selectedLanguages;
                this.languageInput = '';
            }
        },
        
        removeLanguage(language) {
            const index = this.selectedLanguages.indexOf(language);
            if (index > -1) {
                this.selectedLanguages.splice(index, 1);
                this.$store.registration.formData.languages = this.selectedLanguages;
            }
        },
        
        toggleDealBreaker(dealBreaker) {
            const index = this.selectedDealBreakers.indexOf(dealBreaker);
            
            if (index > -1) {
                this.selectedDealBreakers.splice(index, 1);
            } else {
                this.selectedDealBreakers.push(dealBreaker);
            }
            
            this.$store.registration.formData.dealBreakers = this.selectedDealBreakers;
        },
        
        async handleSubmit() {
            if (!this.canProceed) {
                this.$store.registration.setError('Please provide at least some cultural or family information');
                return;
            }
            
            // Save and proceed to next step
            await this.$store.registration.nextStep();
        }
    }
}
</script>