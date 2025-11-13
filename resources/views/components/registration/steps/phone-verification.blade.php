@props([
    'title' => 'Verify Your Phone Number',
    'subtitle' => 'We\'ll send you a verification code to get started'
])

<div x-data="phoneVerificationStep()" class="space-y-8">
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

    <!-- Phone Input Step -->
    <div x-show="!$store.registration.formData.otpSent" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0">
        
        <x-ui.glass-card padding="lg" class="max-w-md mx-auto">
            <form @submit.prevent="sendOTP()" class="space-y-6">
                <!-- Country Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Country</label>
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" 
                                @click="open = !open"
                                @click.away="open = false"
                                class="w-full flex items-center justify-between p-4 bg-white/80 backdrop-blur-sm border-2 border-gray-200 rounded-xl hover:border-pink-300 focus:border-pink-500 focus:outline-none focus:ring-4 focus:ring-pink-500/10 transition-all">
                            <div class="flex items-center space-x-3">
                                <span class="text-2xl" x-text="selectedCountry.flag"></span>
                                <div class="text-left">
                                    <div class="font-medium text-gray-900" x-text="selectedCountry.name"></div>
                                    <div class="text-sm text-gray-500" x-text="selectedCountry.dialCode"></div>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 transition-transform" 
                                 :class="{ 'rotate-180': open }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <!-- Country Dropdown -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-xl max-h-64 overflow-y-auto">
                            
                            <!-- Search -->
                            <div class="p-3 border-b border-gray-100">
                                <input type="text" 
                                       x-model="searchQuery"
                                       @input="filterCountries()"
                                       placeholder="Search countries..."
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-500/10">
                            </div>
                            
                            <!-- Country List -->
                            <div class="py-2">
                                <template x-for="country in filteredCountries" :key="country.code">
                                    <button type="button"
                                            @click="selectCountry(country); open = false"
                                            class="w-full flex items-center space-x-3 px-4 py-3 hover:bg-pink-50 transition-colors text-left">
                                        <span class="text-xl" x-text="country.flag"></span>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-gray-900 truncate" x-text="country.name"></div>
                                            <div class="text-sm text-gray-500" x-text="country.dialCode"></div>
                                        </div>
                                        <div x-show="selectedCountry.code === country.code" class="text-pink-500">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </button>
                                </template>
                                
                                <div x-show="filteredCountries.length === 0" class="px-4 py-6 text-center text-gray-500">
                                    No countries found
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Phone Number Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Phone Number</label>
                    <div class="flex space-x-2">
                        <!-- Dial Code Display -->
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-20 h-14 bg-gray-100 border-2 border-gray-200 rounded-xl text-gray-700 font-medium">
                                <span x-text="selectedCountry.dialCode"></span>
                            </div>
                        </div>
                        
                        <!-- Phone Input -->
                        <div class="flex-1">
                            <input type="tel" 
                                   x-model="phoneNumber"
                                   @input="formatPhone()"
                                   :placeholder="selectedCountry.format?.replace(/#/g, '0') || 'Enter phone number'"
                                   class="w-full px-4 py-4 bg-white/80 backdrop-blur-sm border-2 border-gray-200 rounded-xl focus:border-pink-500 focus:outline-none focus:ring-4 focus:ring-pink-500/10 transition-all text-lg"
                                   required>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        We'll never share your phone number with others
                    </p>
                </div>

                <!-- Send OTP Button -->
                <x-ui.gradient-button
                    variant="primary"
                    size="lg"
                    type="submit"
                    :loading="$store.registration.isLoading"
                    :disabled="!canSendOTP"
                    class="w-full"
                    :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z&quot;/></svg>'"
                    icon-position="left"
                >
                    Send Verification Code
                </x-ui.gradient-button>
            </form>
        </x-ui.glass-card>
    </div>

    <!-- OTP Input Step -->
    <div x-show="$store.registration.formData.otpSent" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0">
        
        <x-ui.glass-card padding="lg" class="max-w-md mx-auto">
            <form @submit.prevent="verifyOTP()" class="space-y-6 text-center">
                <!-- OTP Info -->
                <div>
                    <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Check Your Phone</h3>
                    <p class="text-gray-600 mb-1">We sent a 4-digit code to</p>
                    <p class="font-medium text-pink-600">
                        <span x-text="selectedCountry.dialCode"></span>
                        <span x-text="formattedPhone"></span>
                    </p>
                </div>

                <!-- OTP Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Verification Code</label>
                    <input type="text" 
                           x-model="otpCode"
                           @input="otpCode = otpCode.replace(/\D/g, '').slice(0, 4)"
                           @paste="handleOTPPaste($event)"
                           placeholder="Enter 4-digit code"
                           maxlength="4"
                           class="w-full px-6 py-4 text-2xl font-mono text-center bg-white/80 backdrop-blur-sm border-2 border-gray-200 rounded-xl focus:border-pink-500 focus:outline-none focus:ring-4 focus:ring-pink-500/10 transition-all tracking-widest"
                           required>
                </div>

                <!-- Resend Timer -->
                <div x-show="resendTimer > 0" class="text-sm text-gray-500">
                    Resend code in <span x-text="resendTimer"></span> seconds
                </div>

                <!-- Resend Button -->
                <button type="button"
                        x-show="resendTimer === 0"
                        @click="resendOTP()"
                        :disabled="$store.registration.isLoading"
                        class="text-pink-600 hover:text-pink-700 font-medium text-sm disabled:opacity-50 transition-colors">
                    Resend verification code
                </button>

                <!-- Verify Button -->
                <x-ui.gradient-button
                    variant="primary"
                    size="lg"
                    type="submit"
                    :loading="$store.registration.isLoading"
                    :disabled="!canVerifyOTP"
                    class="w-full"
                    :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z&quot;/></svg>'"
                    icon-position="left"
                >
                    Verify & Continue
                </x-ui.gradient-button>

                <!-- Change Number -->
                <button type="button"
                        @click="changePhoneNumber()"
                        class="text-gray-500 hover:text-gray-700 text-sm transition-colors">
                    ← Use a different phone number
                </button>
            </form>
        </x-ui.glass-card>
    </div>
</div>

<script>
function phoneVerificationStep() {
    return {
        // Country selection
        countries: [],
        selectedCountry: null,
        filteredCountries: [],
        searchQuery: '',
        
        // Phone input
        phoneNumber: '',
        formattedPhone: '',
        
        // OTP
        otpCode: '',
        resendTimer: 0,
        resendInterval: null,
        
        // Computed properties
        get canSendOTP() {
            return this.phoneNumber.trim().length >= 7 && this.selectedCountry && !this.$store.registration.isLoading;
        },
        
        get canVerifyOTP() {
            return this.otpCode.length === 4 && !this.$store.registration.isLoading;
        },
        
        async init() {
            // Import country data
            const { countries, detectCountryByIP, formatPhoneNumber } = await import('/resources/js/country-data.js');
            this.countries = countries;
            this.filteredCountries = countries;
            
            // Store formatting function
            this.formatPhoneNumber = formatPhoneNumber;
            
            // Auto-detect country
            try {
                this.selectedCountry = await detectCountryByIP();
            } catch (error) {
                // Fallback to US
                this.selectedCountry = countries.find(c => c.code === 'US');
            }
            
            // Load persisted data
            if (this.$store.registration.formData.phone) {
                this.phoneNumber = this.$store.registration.formData.phone.replace(this.selectedCountry?.dialCode || '', '').trim();
                this.formatPhone();
            }
            
            // Start resend timer if OTP was already sent
            if (this.$store.registration.formData.otpSent && this.$store.registration.formData.otpExpires) {
                const timeLeft = Math.max(0, this.$store.registration.formData.otpExpires - Date.now());
                if (timeLeft > 0) {
                    this.resendTimer = Math.ceil(timeLeft / 1000);
                    this.startResendTimer();
                }
            }
        },
        
        selectCountry(country) {
            this.selectedCountry = country;
            this.$store.registration.formData.countryCode = country.dialCode;
            this.formatPhone();
        },
        
        filterCountries() {
            if (!this.searchQuery) {
                this.filteredCountries = this.countries;
                return;
            }
            
            const query = this.searchQuery.toLowerCase();
            this.filteredCountries = this.countries.filter(country =>
                country.name.toLowerCase().includes(query) ||
                country.code.toLowerCase().includes(query) ||
                country.dialCode.includes(this.searchQuery)
            );
        },
        
        formatPhone() {
            if (this.phoneNumber && this.selectedCountry) {
                this.formattedPhone = this.formatPhoneNumber(this.phoneNumber, this.selectedCountry);
            } else {
                this.formattedPhone = this.phoneNumber;
            }
        },
        
        async sendOTP() {
            if (!this.canSendOTP) return;
            
            const fullPhoneNumber = this.selectedCountry.dialCode + this.phoneNumber.replace(/\D/g, '');
            const success = await this.$store.registration.sendOTP(fullPhoneNumber);
            
            if (success) {
                this.startResendTimer();
            }
        },
        
        async verifyOTP() {
            if (!this.canVerifyOTP) return;
            
            const success = await this.$store.registration.verifyOTP(this.otpCode);
            
            if (success) {
                // Move to next step
                await this.$store.registration.nextStep();
            }
        },
        
        async resendOTP() {
            const fullPhoneNumber = this.selectedCountry.dialCode + this.phoneNumber.replace(/\D/g, '');
            const success = await this.$store.registration.sendOTP(fullPhoneNumber);
            
            if (success) {
                this.startResendTimer();
            }
        },
        
        changePhoneNumber() {
            this.$store.registration.formData.otpSent = false;
            this.$store.registration.formData.otp = '';
            this.otpCode = '';
            this.stopResendTimer();
            this.$store.registration.clearMessages();
        },
        
        startResendTimer() {
            this.resendTimer = 60;
            this.resendInterval = setInterval(() => {
                this.resendTimer--;
                if (this.resendTimer <= 0) {
                    this.stopResendTimer();
                }
            }, 1000);
        },
        
        stopResendTimer() {
            if (this.resendInterval) {
                clearInterval(this.resendInterval);
                this.resendInterval = null;
            }
            this.resendTimer = 0;
        },
        
        handleOTPPaste(event) {
            const paste = (event.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/\D/g, '').slice(0, 4);
            this.otpCode = digits;
            event.preventDefault();
        }
    }
}
</script>