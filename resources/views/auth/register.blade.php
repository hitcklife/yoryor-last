<x-layouts.onboarding title="Join YorYor - Find Your Perfect Match">
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('authForm', () => ({
                // OTP flow state
                currentStep: 'phone',
                phoneNumber: '',
                otpCode: '',
                loading: false,
                error: '',
                resendTimer: 0,
                resendInterval: null,

                // Country selection state
                countries: [],
                selectedCountry: null,
                filteredCountries: [],
                isOpen: false,
                searchTerm: '',

                // Computed properties
                get phonePlaceholder() {
                    return this.selectedCountry ? this.selectedCountry.mask.replace(/#/g, '0') : 'Enter phone number';
                },

                get cleanPhone() {
                    return this.phoneNumber.replace(/\D/g, '');
                },

                get isPhoneValid() {
                    return this.cleanPhone && this.cleanPhone.length >= 7;
                },

                get isOtpValid() {
                    return this.otpCode && this.otpCode.length === 6;
                },

                // Initialize component
                async init() {
                    await this.loadCountries();
                    this.selectedCountry = this.countries.find(c => c.code === 'US') || this.countries[0];
                    this.filteredCountries = [...this.countries];
                    await this.detectCountryByIP();
                },

                // Load countries from API
                async loadCountries() {
                    try {
                        const response = await fetch('/api/v1/countries', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            const result = await response.json();
                            const countries = result.data || result;

                            this.countries = countries.map(country => ({
                                id: country.id,
                                name: country.name,
                                code: country.code,
                                flag: country.flag,
                                dialCode: country.phone_code,
                                mask: country.phone_template || "### ### ####"
                            }));

                            console.log(`🌍 Loaded ${this.countries.length} countries`);
                        } else {
                            console.warn('Countries API failed, using fallback');
                            this.loadFallbackCountries();
                        }
                    } catch (error) {
                        console.error('Failed to load countries:', error);
                        this.loadFallbackCountries();
                    }
                },

                // Fallback countries if API fails
                loadFallbackCountries() {
                    this.countries = [
                        { id: 1, name: "United States", code: "US", flag: "🇺🇸", dialCode: "+1", mask: "(###) ###-####" },
                        { id: 2, name: "Canada", code: "CA", flag: "🇨🇦", dialCode: "+1", mask: "(###) ###-####" },
                        { id: 3, name: "United Kingdom", code: "GB", flag: "🇬🇧", dialCode: "+44", mask: "#### ### ####" },
                        { id: 4, name: "Uzbekistan", code: "UZ", flag: "🇺🇿", dialCode: "+998", mask: "## ### ####" },
                        { id: 5, name: "Russia", code: "RU", flag: "🇷🇺", dialCode: "+7", mask: "### ### ####" },
                        { id: 6, name: "India", code: "IN", flag: "🇮🇳", dialCode: "+91", mask: "##### #####" }
                    ];
                },

                // Auto-detect country by IP
                async detectCountryByIP() {
                    try {
                        const response = await fetch('https://ipapi.co/json/', {
                            method: 'GET',
                            headers: { 'Accept': 'application/json' }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            const countryByIP = this.countries.find(c => c.code === data.country_code);
                            if (countryByIP) {
                                this.selectedCountry = countryByIP;
                                console.log('🗺️ Detected country:', countryByIP.name);
                            }
                        }
                    } catch (error) {
                        console.log('IP detection failed, using default country');
                    }
                },

                // Country dropdown methods
                toggleDropdown() {
                    this.isOpen = !this.isOpen;
                    if (this.isOpen) {
                        this.searchTerm = '';
                        this.filteredCountries = [...this.countries];
                        this.$nextTick(() => {
                            this.$refs.searchInput?.focus();
                        });
                    }
                },

                selectCountry(country) {
                    this.selectedCountry = country;
                    this.isOpen = false;
                    this.phoneNumber = '';
                    this.error = '';
                },

                filterCountries() {
                    if (!this.searchTerm.trim()) {
                        this.filteredCountries = [...this.countries];
                        return;
                    }

                    const term = this.searchTerm.toLowerCase();
                    this.filteredCountries = this.countries.filter(country =>
                        country.name.toLowerCase().includes(term) ||
                        country.dialCode.includes(term) ||
                        country.code.toLowerCase().includes(term)
                    );
                },

                // Phone formatting
                formatPhone() {
                    if (!this.selectedCountry) return;

                    const cleanValue = this.phoneNumber.replace(/\D/g, '');
                    this.phoneNumber = this.applyMask(cleanValue, this.selectedCountry.mask);
                },

                applyMask(value, mask) {
                    let maskedValue = '';
                    let valueIndex = 0;

                    for (let i = 0; i < mask.length && valueIndex < value.length; i++) {
                        if (mask[i] === '#') {
                            maskedValue += value[valueIndex];
                            valueIndex++;
                        } else {
                            maskedValue += mask[i];
                        }
                    }

                    return maskedValue;
                },

                // OTP methods
                async sendOTP() {
                    if (!this.isPhoneValid) {
                        this.error = 'Please enter a valid phone number';
                        return;
                    }

                    this.loading = true;
                    this.error = '';

                    try {
                        const fullPhone = this.selectedCountry.dialCode + this.cleanPhone;
                        const response = await fetch('/auth/authenticate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ phone: fullPhone })
                        });

                        const data = await response.json();

                        if (data.status === 'success' && data.data.otp_sent) {
                            this.currentStep = 'otp';
                            this.startResendTimer();
                            this.$nextTick(() => {
                                this.$refs.otpInput?.focus();
                            });
                        } else {
                            this.error = data.message || 'Failed to send verification code';
                        }
                    } catch (error) {
                        console.error('Error sending OTP:', error);
                        this.error = 'Failed to send verification code. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                },

                async verifyOTP() {
                    if (!this.isOtpValid) {
                        this.error = 'Please enter the complete 6-digit code';
                        return;
                    }

                    this.loading = true;
                    this.error = '';

                    try {
                        const fullPhone = this.selectedCountry.dialCode + this.cleanPhone;
                        const response = await fetch('/auth/authenticate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                phone: fullPhone,
                                otp: this.otpCode
                            })
                        });

                        const data = await response.json();

                        if (data.status === 'success' && data.data.authenticated) {
                            // Success! Redirect to dashboard or onboarding
                            window.location.href = data.data.redirect_url || '/dashboard';
                        } else {
                            this.error = data.message || 'Invalid verification code';
                            this.otpCode = '';
                            this.$refs.otpInput?.focus();
                        }
                    } catch (error) {
                        console.error('Error verifying OTP:', error);
                        this.error = 'Failed to verify code. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                },

                // Timer methods
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

                // Step navigation
                goToPhoneStep() {
                    this.currentStep = 'phone';
                    this.error = '';
                    this.otpCode = '';
                    this.stopResendTimer();
                },

                // Cleanup on destroy
                destroy() {
                    this.stopResendTimer();
                }
            }));
        });
    </script>

    <div x-data="authForm" x-init="init()" class="w-full max-w-md mx-auto">
        <!-- Theme Toggle Button -->
        <div class="absolute top-4 right-4">
            <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                    class="p-2 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm hover:bg-white dark:hover:bg-gray-700 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 transition-all duration-300 hover:shadow-xl">
                <!-- Sun Icon for Light Mode -->
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300 transition-all duration-300" :class="{ 'hidden': darkMode }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <!-- Moon Icon for Dark Mode -->
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300 transition-all duration-300" :class="{ 'hidden': !darkMode }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>
        </div>

        <!-- Header Section -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl mb-4 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2 transition-colors">Create your account</h1>
            <p class="text-gray-600 dark:text-gray-300 transition-colors">Join thousands finding love worldwide</p>
        </div>


        <!-- Phone Number Step -->
        <div x-show="currentStep === 'phone'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <form @submit.prevent="sendOTP()" class="space-y-6">
                <!-- Phone Input Group -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 transition-colors">Phone Number</label>
                    <div class="relative">
                        <!-- Phone Input Container -->
                        <div class="flex rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 transition-all shadow-sm">
                            <!-- Country Selector -->
                            <button type="button" @click="toggleDropdown()"
                                    class="flex items-center px-4 py-3 bg-gray-50 dark:bg-gray-700 border-r border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 focus:outline-none transition-colors text-gray-700 dark:text-gray-100">
                                <span x-text="selectedCountry?.flag || '🇺🇸'" class="text-xl mr-2"></span>
                                <span x-text="selectedCountry?.dialCode || '+1'" class="text-sm font-medium mr-2 min-w-[3rem] text-left"></span>
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-300 transition-transform"
                                     :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Phone Input -->
                            <input type="tel" x-model="phoneNumber" @input="formatPhone()"
                                   :placeholder="phonePlaceholder"
                                   class="flex-1 px-4 py-3 bg-white dark:bg-gray-800 border-0 focus:outline-none text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400 transition-colors">
                        </div>

                        <!-- Country Dropdown -->
                        <div x-show="isOpen" @click.away="isOpen = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute top-full left-0 mt-2 w-full bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-600 z-50 overflow-hidden">

                            <!-- Search Box -->
                            <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input type="text" x-model="searchTerm" @input="filterCountries()"
                                           x-ref="searchInput"
                                           placeholder="Search countries..."
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-600 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-300 transition-colors">
                                </div>
                            </div>

                            <!-- Countries List -->
                            <div class="max-h-60 overflow-y-auto bg-white dark:bg-gray-800">
                                <template x-for="country in filteredCountries" :key="country.id">
                                    <button type="button" @click="selectCountry(country)"
                                            class="w-full flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-600 border-b border-gray-50 dark:border-gray-600 last:border-b-0 transition-colors text-left">
                                        <span x-text="country.flag" class="text-xl mr-3"></span>
                                        <div class="flex-1 min-w-0">
                                            <div x-text="country.name" class="text-sm font-medium text-gray-900 dark:text-white truncate"></div>
                                        </div>
                                        <span x-text="country.dialCode" class="text-xs text-gray-500 dark:text-gray-200 bg-gray-100 dark:bg-gray-600 px-2 py-1 rounded font-mono border border-gray-200 dark:border-gray-500"></span>
                                    </button>
                                </template>

                                <!-- No Results -->
                                <div x-show="filteredCountries.length === 0" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto w-8 h-8 mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <p class="text-sm">No countries found</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div x-show="error" x-transition class="p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 dark:border-red-500 rounded-r-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400 dark:text-red-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p x-text="error" class="text-sm text-red-700 dark:text-red-400"></p>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" :disabled="loading || !isPhoneValid"
                        class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 px-6 rounded-xl font-semibold text-lg
                               hover:from-blue-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                               disabled:opacity-50 disabled:cursor-not-allowed transform transition-all duration-200 hover:scale-[1.02] hover:shadow-lg">
                    <span x-show="!loading" class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Continue with Phone
                    </span>
                    <span x-show="loading" class="flex items-center justify-center">
                        <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sending Code...
                    </span>
                </button>
            </form>
        </div>

        <!-- OTP Verification Step -->
        <div x-show="currentStep === 'otp'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">

            <form @submit.prevent="verifyOTP()" class="space-y-6">
                <!-- OTP Header -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full mb-4">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2 transition-colors">Check your phone</h3>
                    <p class="text-gray-600 dark:text-gray-300 transition-colors">
                        We've sent a 6-digit code to
                        <span class="font-medium" x-text="selectedCountry?.dialCode + ' ' + phoneNumber"></span>
                    </p>
                </div>

                <!-- OTP Input -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 transition-colors">Verification Code</label>
                    <input type="text" x-model="otpCode" x-ref="otpInput" maxlength="6"
                           class="w-full px-4 py-4 text-center text-2xl font-mono tracking-widest border border-gray-300 dark:border-gray-600 rounded-xl
                                  focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-400"
                           placeholder="000000">
                </div>

                <!-- Resend Timer -->
                <div class="text-center">
                    <button type="button" @click="sendOTP()" :disabled="loading || resendTimer > 0"
                            class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span x-show="resendTimer === 0">Resend Code</span>
                        <span x-show="resendTimer > 0">
                            Resend in <span x-text="resendTimer"></span>s
                        </span>
                    </button>
                </div>

                <!-- Error Message -->
                <div x-show="error" x-transition class="p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 dark:border-red-500 rounded-r-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400 dark:text-red-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p x-text="error" class="text-sm text-red-700 dark:text-red-400"></p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <button type="submit" :disabled="loading || !isOtpValid"
                            class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 px-6 rounded-xl font-semibold text-lg
                                   hover:from-blue-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                   disabled:opacity-50 disabled:cursor-not-allowed transform transition-all duration-200 hover:scale-[1.02] hover:shadow-lg">
                        <span x-show="!loading" class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Verify & Continue
                        </span>
                        <span x-show="loading" class="flex items-center justify-center">
                            <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Verifying...
                        </span>
                    </button>

                    <button type="button" @click="goToPhoneStep()"
                            class="w-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 py-3 px-6 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Change Phone Number
                    </button>
                </div>
            </form>
        </div>

        <!-- Divider -->
        <div class="relative my-8">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-4 bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400 font-medium transition-colors">Or continue with</span>
            </div>
        </div>

        <!-- Social Login Options -->
        <div class="space-y-3">
            <!-- Google -->
            <a href="{{ route('auth.socialite', 'google') }}"
               class="w-full flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 font-medium bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all hover:shadow-md">
                <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                    <path fill="#4285f4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34a853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#fbbc05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#ea4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Continue with Google
            </a>

            <!-- Facebook -->
            <a href="{{ route('auth.socialite', 'facebook') }}"
               class="w-full flex items-center justify-center px-6 py-3 bg-[#1877f2] text-white rounded-xl font-medium hover:bg-[#166fe5] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all hover:shadow-md">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                Continue with Facebook
            </a>
        </div>

        <!-- Footer -->
        <div class="mt-8 space-y-4 text-center">
            <!-- Terms -->
            <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors">
                By continuing, you agree to our
                <a href="{{ route('terms') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 underline font-medium transition-colors">Terms of Service</a>
                and
                <a href="{{ route('privacy') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 underline font-medium transition-colors">Privacy Policy</a>
            </p>

            <!-- Sign In Link -->
            <p class="text-sm text-gray-600 dark:text-gray-300 transition-colors">
                Already have an account?
                <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold underline transition-colors">Sign in</a>
            </p>
        </div>
    </div>
</x-layouts.onboarding>