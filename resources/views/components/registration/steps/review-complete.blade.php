@props([
    'title' => 'Review Your Profile',
    'subtitle' => 'Almost there! Review your information before completing'
])

<div x-data="reviewCompleteStep()" class="space-y-8">
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

    <!-- Profile Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Basic Info & Photos -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Profile Photos -->
            <x-ui.glass-card padding="lg">
                <div class="text-center">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Photos</h3>
                    
                    <div class="grid grid-cols-2 gap-3" x-show="$store.registration.formData.photos && $store.registration.formData.photos.length > 0">
                        <template x-for="(photo, index) in $store.registration.formData.photos.slice(0, 4)" :key="index">
                            <div class="relative aspect-square rounded-xl overflow-hidden bg-gray-100">
                                <img :src="photo.url" :alt="`Photo ${index + 1}`" class="w-full h-full object-cover">
                                <div x-show="photo.isMain" class="absolute top-2 left-2 bg-pink-500 text-white text-xs px-2 py-1 rounded-full">
                                    Main
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <div x-show="!$store.registration.formData.photos || $store.registration.formData.photos.length === 0" 
                         class="text-gray-500 text-sm">
                        No photos uploaded
                    </div>
                    
                    <button type="button" 
                            @click="editSection('photos')"
                            class="mt-4 text-pink-600 hover:text-pink-700 text-sm font-medium">
                        Edit Photos
                    </button>
                </div>
            </x-ui.glass-card>

            <!-- Basic Information -->
            <x-ui.glass-card padding="lg">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                        <button type="button" 
                                @click="editSection('basic')"
                                class="text-pink-600 hover:text-pink-700 text-sm font-medium">
                            Edit
                        </button>
                    </div>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Name:</span>
                            <span class="font-medium" x-text="`${$store.registration.formData.firstName} ${$store.registration.formData.lastName}`"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Gender:</span>
                            <span class="font-medium capitalize" x-text="$store.registration.formData.gender"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date of Birth:</span>
                            <span class="font-medium" x-text="$store.registration.formData.dateOfBirth"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phone:</span>
                            <span class="font-medium" x-text="$store.registration.formData.phone"></span>
                        </div>
                    </div>
                </div>
            </x-ui.glass-card>

            <!-- Location -->
            <x-ui.glass-card padding="lg">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Location</h3>
                        <button type="button" 
                                @click="editSection('location')"
                                class="text-pink-600 hover:text-pink-700 text-sm font-medium">
                            Edit
                        </button>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div x-text="$store.registration.formData.city + ', ' + $store.registration.formData.state"></div>
                        <div class="text-gray-600" x-text="$store.registration.formData.country"></div>
                    </div>
                </div>
            </x-ui.glass-card>
        </div>

        <!-- Right Column - Detailed Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- About -->
            <x-ui.glass-card padding="lg">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">About Me</h3>
                        <button type="button" 
                                @click="editSection('profile')"
                                class="text-pink-600 hover:text-pink-700 text-sm font-medium">
                            Edit
                        </button>
                    </div>
                    
                    <p class="text-gray-700 text-sm leading-relaxed" 
                       x-text="$store.registration.formData.bio || 'No bio provided'"></p>
                    
                    <!-- Professional Info -->
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Profession</div>
                            <div class="text-sm font-medium" x-text="$store.registration.formData.profession || 'Not specified'"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Company</div>
                            <div class="text-sm font-medium" x-text="$store.registration.formData.company || 'Not specified'"></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Education</div>
                            <div class="text-sm font-medium" x-text="$store.registration.formData.educationLevel || 'Not specified'"></div>
                        </div>
                    </div>
                </div>
            </x-ui.glass-card>

            <!-- Interests -->
            <x-ui.glass-card padding="lg" x-show="$store.registration.formData.interests && $store.registration.formData.interests.length > 0">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Interests</h3>
                        <button type="button" 
                                @click="editSection('profile')"
                                class="text-pink-600 hover:text-pink-700 text-sm font-medium">
                            Edit
                        </button>
                    </div>
                    
                    <div class="flex flex-wrap gap-2">
                        <template x-for="interest in $store.registration.formData.interests" :key="interest">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-pink-100 text-pink-800" x-text="interest"></span>
                        </template>
                    </div>
                </div>
            </x-ui.glass-card>

            <!-- Lifestyle -->
            <x-ui.glass-card padding="lg">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Lifestyle</h3>
                        <button type="button" 
                                @click="editSection('profile')"
                                class="text-pink-600 hover:text-pink-700 text-sm font-medium">
                            Edit
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div x-show="$store.registration.formData.smoking">
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Smoking</div>
                            <div class="font-medium capitalize" x-text="$store.registration.formData.smoking"></div>
                        </div>
                        <div x-show="$store.registration.formData.drinking">
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Drinking</div>
                            <div class="font-medium capitalize" x-text="$store.registration.formData.drinking"></div>
                        </div>
                        <div x-show="$store.registration.formData.exercise">
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Exercise</div>
                            <div class="font-medium capitalize" x-text="$store.registration.formData.exercise"></div>
                        </div>
                        <div x-show="$store.registration.formData.diet">
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Diet</div>
                            <div class="font-medium capitalize" x-text="$store.registration.formData.diet"></div>
                        </div>
                    </div>
                </div>
            </x-ui.glass-card>

            <!-- Cultural & Family Values -->
            <x-ui.glass-card padding="lg">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Cultural & Family Values</h3>
                        <button type="button" 
                                @click="editSection('cultural')"
                                class="text-pink-600 hover:text-pink-700 text-sm font-medium">
                            Edit
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div x-show="$store.registration.formData.religion">
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Religion</div>
                            <div class="font-medium" x-text="$store.registration.formData.religion"></div>
                        </div>
                        <div x-show="$store.registration.formData.ethnicity">
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Ethnicity</div>
                            <div class="font-medium" x-text="$store.registration.formData.ethnicity"></div>
                        </div>
                        <div x-show="$store.registration.formData.wantChildren">
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Want Children</div>
                            <div class="font-medium capitalize" x-text="$store.registration.formData.wantChildren"></div>
                        </div>
                        <div x-show="$store.registration.formData.marriageTimeline">
                            <div class="text-xs text-gray-500 uppercase tracking-wide">Marriage Timeline</div>
                            <div class="font-medium capitalize" x-text="$store.registration.formData.marriageTimeline"></div>
                        </div>
                    </div>
                    
                    <!-- Languages -->
                    <div x-show="$store.registration.formData.languages && $store.registration.formData.languages.length > 0" class="pt-4 border-t border-gray-100">
                        <div class="text-xs text-gray-500 uppercase tracking-wide mb-2">Languages Spoken</div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="language in $store.registration.formData.languages" :key="language">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800" x-text="language"></span>
                            </template>
                        </div>
                    </div>
                </div>
            </x-ui.glass-card>
        </div>
    </div>

    <!-- Privacy & Terms -->
    <x-ui.glass-card padding="lg" class="bg-blue-50/30">
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Privacy & Terms</h3>
            
            <div class="space-y-3">
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" 
                           x-model="agreedToTerms"
                           class="mt-1 rounded border-gray-300 text-pink-600 focus:ring-pink-500 focus:border-pink-500">
                    <span class="text-sm text-gray-700">
                        I agree to the <a href="/terms" target="_blank" class="text-pink-600 hover:text-pink-700 underline">Terms of Service</a> 
                        and <a href="/privacy" target="_blank" class="text-pink-600 hover:text-pink-700 underline">Privacy Policy</a>
                    </span>
                </label>
                
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" 
                           x-model="agreedToAge"
                           class="mt-1 rounded border-gray-300 text-pink-600 focus:ring-pink-500 focus:border-pink-500">
                    <span class="text-sm text-gray-700">
                        I confirm that I am at least 18 years old
                    </span>
                </label>
                
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" 
                           x-model="agreedToDataUsage"
                           class="mt-1 rounded border-gray-300 text-pink-600 focus:ring-pink-500 focus:border-pink-500">
                    <span class="text-sm text-gray-700">
                        I understand that my profile information will be used to find compatible matches and may be visible to other users
                    </span>
                </label>
            </div>
        </div>
    </x-ui.glass-card>

    <!-- Completion Progress -->
    <div class="text-center">
        <div class="inline-flex items-center space-x-3 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl px-6 py-4">
            <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-full">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-left">
                <div class="text-sm font-semibold text-green-900">Profile Completion</div>
                <div class="text-xs text-green-700">Ready to find your perfect match!</div>
            </div>
        </div>
    </div>

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
            type="button"
            @click="completeRegistration"
            :loading="$store.registration.isLoading"
            :disabled="!canComplete"
            class="flex-1"
            :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z&quot;/></svg>'"
            icon-position="right"
        >
            Complete Registration
        </x-ui.gradient-button>
    </div>

    <!-- Success Animation -->
    <div x-show="registrationCompleted" 
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
         style="display: none;">
        <div class="bg-white rounded-2xl p-8 mx-4 max-w-md w-full text-center">
            <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome to YorYor!</h2>
            <p class="text-gray-600 mb-6">Your profile has been created successfully. Let's start finding your perfect match!</p>
            <x-ui.gradient-button
                variant="primary"
                size="lg"
                @click="redirectToHome"
                class="w-full"
            >
                Start Matching
            </x-ui.gradient-button>
        </div>
    </div>
</div>

<script>
function reviewCompleteStep() {
    return {
        // Data
        agreedToTerms: false,
        agreedToAge: false,
        agreedToDataUsage: false,
        registrationCompleted: false,
        
        // Computed properties
        get canComplete() {
            return this.agreedToTerms && this.agreedToAge && this.agreedToDataUsage;
        },
        
        init() {
            // Auto-scroll to top when this step loads
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        
        editSection(section) {
            const stepMap = {
                'basic': 2,
                'location': 3,
                'photos': 4,
                'profile': 5,
                'cultural': 6
            };
            
            if (stepMap[section]) {
                this.$store.registration.currentStep = stepMap[section];
            }
        },
        
        async completeRegistration() {
            if (!this.canComplete) {
                this.$store.registration.setError('Please accept all terms and conditions');
                return;
            }
            
            try {
                // Call the registration store's complete method
                await this.$store.registration.completeRegistration();
                
                // Show success animation
                this.registrationCompleted = true;
                
                // Redirect after animation
                setTimeout(() => {
                    this.redirectToHome();
                }, 2000);
                
            } catch (error) {
                console.error('Registration completion failed:', error);
                this.$store.registration.setError('Failed to complete registration. Please try again.');
            }
        },
        
        redirectToHome() {
            window.location.href = '/dashboard';
        }
    }
}
</script>