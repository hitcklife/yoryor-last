// Authentication Alpine.js Component
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