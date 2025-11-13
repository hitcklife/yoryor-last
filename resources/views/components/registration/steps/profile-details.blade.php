@props([
    'title' => 'Tell Us About Yourself',
    'subtitle' => 'Help others get to know the real you'
])

<div x-data="profileDetailsStep()" class="space-y-8">
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
        <!-- Bio Section -->
        <x-ui.glass-card padding="lg">
            <div class="space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-pink-500 to-purple-600 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">About You</h3>
                        <p class="text-sm text-gray-600">Write a brief description about yourself</p>
                    </div>
                </div>

                <div class="relative">
                    <textarea
                        x-model="$store.registration.formData.bio"
                        @input="updateCharCount"
                        rows="6"
                        maxlength="500"
                        placeholder="Tell us about your interests, hobbies, what makes you unique..."
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors resize-none bg-white/50 backdrop-blur-sm"
                    ></textarea>
                    
                    <!-- Character Counter -->
                    <div class="flex justify-between items-center mt-2">
                        <p class="text-xs text-gray-500">Make it personal and authentic</p>
                        <span class="text-xs text-gray-500" 
                              :class="{ 'text-red-500': bioCharCount > 450 }"
                              x-text="`${bioCharCount}/500`"></span>
                    </div>
                </div>
            </div>
        </x-ui.glass-card>

        <!-- Profession/Education -->
        <x-ui.glass-card padding="lg">
            <div class="space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Career & Education</h3>
                        <p class="text-sm text-gray-600">Your professional background</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.floating-input
                        label="Profession"
                        x-model="$store.registration.formData.profession"
                        :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6z&quot;/></svg>'"
                        size="lg"
                    />

                    <x-ui.floating-input
                        label="Company/School"
                        x-model="$store.registration.formData.company"
                        :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4&quot;/></svg>'"
                        size="lg"
                    />
                </div>

                <!-- Education Level -->
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-3">Education Level</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <template x-for="level in educationLevels" :key="level">
                            <button type="button"
                                    @click="selectEducationLevel(level)"
                                    :class="{
                                        'bg-gradient-to-r from-pink-500 to-purple-600 text-white border-transparent shadow-lg': $store.registration.formData.educationLevel === level,
                                        'bg-white/50 text-gray-700 border-gray-200 hover:bg-white/80': $store.registration.formData.educationLevel !== level
                                    }"
                                    class="px-4 py-3 rounded-xl border text-sm font-medium transition-all duration-200 backdrop-blur-sm">
                                <span x-text="level"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </x-ui.glass-card>

        <!-- Interests Section -->
        <x-ui.glass-card padding="lg">
            <div class="space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-green-500 to-teal-600 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Interests & Hobbies</h3>
                        <p class="text-sm text-gray-600">Select what you're passionate about (max 10)</p>
                    </div>
                </div>

                <!-- Interest Categories -->
                <div class="space-y-4">
                    <template x-for="category in interestCategories" :key="category.name">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2" x-text="category.name"></h4>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="interest in category.items" :key="interest">
                                    <button type="button"
                                            @click="toggleInterest(interest)"
                                            :disabled="!canSelectMoreInterests && !selectedInterests.includes(interest)"
                                            :class="{
                                                'bg-gradient-to-r from-pink-500 to-purple-600 text-white border-transparent shadow-md': selectedInterests.includes(interest),
                                                'bg-white/50 text-gray-700 border-gray-200 hover:bg-white/80': !selectedInterests.includes(interest) && canSelectMoreInterests,
                                                'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed': !selectedInterests.includes(interest) && !canSelectMoreInterests
                                            }"
                                            class="px-3 py-2 rounded-lg border text-sm font-medium transition-all duration-200 backdrop-blur-sm">
                                        <span x-text="interest"></span>
                                        <span x-show="selectedInterests.includes(interest)" class="ml-1">✓</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Selected Count -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        <span x-text="selectedInterests.length"></span> of 10 interests selected
                    </p>
                </div>
            </div>
        </x-ui.glass-card>

        <!-- Lifestyle Preferences -->
        <x-ui.glass-card padding="lg">
            <div class="space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Lifestyle</h3>
                        <p class="text-sm text-gray-600">Your lifestyle preferences</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Smoking -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Smoking</label>
                        <select x-model="$store.registration.formData.smoking"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors bg-white/50 backdrop-blur-sm">
                            <option value="">Select...</option>
                            <option value="never">Never</option>
                            <option value="occasionally">Occasionally</option>
                            <option value="regularly">Regularly</option>
                            <option value="trying-to-quit">Trying to quit</option>
                        </select>
                    </div>

                    <!-- Drinking -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Drinking</label>
                        <select x-model="$store.registration.formData.drinking"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors bg-white/50 backdrop-blur-sm">
                            <option value="">Select...</option>
                            <option value="never">Never</option>
                            <option value="occasionally">Occasionally</option>
                            <option value="socially">Socially</option>
                            <option value="regularly">Regularly</option>
                        </select>
                    </div>

                    <!-- Exercise -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Exercise</label>
                        <select x-model="$store.registration.formData.exercise"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors bg-white/50 backdrop-blur-sm">
                            <option value="">Select...</option>
                            <option value="never">Never</option>
                            <option value="rarely">Rarely</option>
                            <option value="sometimes">Sometimes</option>
                            <option value="regularly">Regularly</option>
                            <option value="daily">Daily</option>
                        </select>
                    </div>

                    <!-- Diet -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Diet</label>
                        <select x-model="$store.registration.formData.diet"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors bg-white/50 backdrop-blur-sm">
                            <option value="">Select...</option>
                            <option value="omnivore">Omnivore</option>
                            <option value="vegetarian">Vegetarian</option>
                            <option value="vegan">Vegan</option>
                            <option value="pescatarian">Pescatarian</option>
                            <option value="keto">Keto</option>
                            <option value="paleo">Paleo</option>
                        </select>
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
function profileDetailsStep() {
    return {
        // Data
        bioCharCount: 0,
        selectedInterests: [],
        
        // Education levels
        educationLevels: [
            'High School',
            'Some College',
            'Bachelor\'s',
            'Master\'s',
            'PhD/Doctorate',
            'Trade School',
            'Other'
        ],
        
        // Interest categories
        interestCategories: [
            {
                name: 'Activities & Sports',
                items: ['Gym', 'Running', 'Swimming', 'Yoga', 'Dancing', 'Hiking', 'Cycling', 'Tennis', 'Basketball', 'Football']
            },
            {
                name: 'Creative & Arts',
                items: ['Photography', 'Painting', 'Music', 'Writing', 'Drawing', 'Cooking', 'Crafts', 'Theater', 'Film', 'Poetry']
            },
            {
                name: 'Entertainment',
                items: ['Movies', 'TV Shows', 'Gaming', 'Reading', 'Podcasts', 'Comedy', 'Concerts', 'Museums', 'Shopping', 'Fashion']
            },
            {
                name: 'Travel & Adventure',
                items: ['Travel', 'Adventure', 'Camping', 'Beach', 'Mountains', 'Road Trips', 'Backpacking', 'Culture', 'Food Tours', 'Festivals']
            },
            {
                name: 'Social & Lifestyle',
                items: ['Friends', 'Family', 'Volunteering', 'Nightlife', 'Wine Tasting', 'Coffee', 'Meditation', 'Spirituality', 'Politics', 'Environment']
            },
            {
                name: 'Professional & Learning',
                items: ['Technology', 'Science', 'Business', 'Investing', 'Languages', 'History', 'Philosophy', 'Psychology', 'Health', 'Fitness']
            }
        ],
        
        // Computed properties
        get canProceed() {
            return !!this.$store.registration.formData.bio?.trim() && 
                   this.selectedInterests.length >= 3;
        },
        
        get canSelectMoreInterests() {
            return this.selectedInterests.length < 10;
        },
        
        init() {
            // Load existing data
            this.updateCharCount();
            this.selectedInterests = this.$store.registration.formData.interests || [];
        },
        
        updateCharCount() {
            this.bioCharCount = (this.$store.registration.formData.bio || '').length;
        },
        
        selectEducationLevel(level) {
            this.$store.registration.formData.educationLevel = level;
        },
        
        toggleInterest(interest) {
            const index = this.selectedInterests.indexOf(interest);
            
            if (index > -1) {
                // Remove interest
                this.selectedInterests.splice(index, 1);
            } else if (this.canSelectMoreInterests) {
                // Add interest
                this.selectedInterests.push(interest);
            }
            
            // Update store
            this.$store.registration.formData.interests = this.selectedInterests;
        },
        
        async handleSubmit() {
            if (!this.canProceed) {
                let errorMessage = '';
                if (!this.$store.registration.formData.bio?.trim()) {
                    errorMessage = 'Please write a brief bio about yourself';
                } else if (this.selectedInterests.length < 3) {
                    errorMessage = 'Please select at least 3 interests';
                }
                this.$store.registration.setError(errorMessage);
                return;
            }
            
            // Save and proceed to next step
            await this.$store.registration.nextStep();
        }
    }
}
</script>