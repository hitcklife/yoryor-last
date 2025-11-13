<!-- Auth Modal Component -->
<div x-data="authModal()" x-cloak class="relative">
    <!-- Authentication Modal -->
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm"
         @click.self="showModal = false">
        
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-md mx-auto">
            
            <!-- Modal Content -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold" x-text="currentStep === 'phone' ? 'Welcome to YorYor' : 'Verify Your Phone'"></h2>
                        <button @click="closeModal()" class="p-1 hover:bg-white hover:bg-opacity-20 rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-blue-100 text-sm mt-1" x-text="currentStep === 'phone' ? 'Enter your phone number to get started' : 'Enter the 4-digit code sent to ' + phoneNumber"></p>
                </div>

                <!-- Phone Number Step -->
                <div x-show="currentStep === 'phone'" class="p-6">
                    <form @submit.prevent="sendOTP()">
                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input 
                                type="tel" 
                                id="phone"
                                x-model="phoneNumber"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                placeholder="+1 (555) 123-4567"
                                required>
                        </div>
                        
                        <!-- Error Display -->
                        <div x-show="error" x-text="error" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm"></div>
                        
                        <button 
                            type="submit"
                            :disabled="loading || !phoneNumber"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed text-white py-3 px-4 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 hover:shadow-lg">
                            <span x-show="!loading">Continue</span>
                            <span x-show="loading" class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Sending...
                            </span>
                        </button>
                    </form>
                </div>

                <!-- OTP Verification Step -->
                <div x-show="currentStep === 'otp'" class="p-6">
                    <form @submit.prevent="verifyOTP()">
                        <div class="mb-4">
                            <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">Verification Code</label>
                            <input 
                                type="text" 
                                id="otp"
                                x-model="otpCode"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-center text-2xl font-mono tracking-widest"
                                placeholder="----"
                                maxlength="4"
                                required>
                        </div>

                        <!-- Resend OTP -->
                        <div class="text-center mb-4">
                            <button 
                                type="button"
                                @click="sendOTP()"
                                :disabled="loading || resendTimer > 0"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="resendTimer === 0">Resend Code</span>
                                <span x-show="resendTimer > 0" x-text="'Resend in ' + resendTimer + 's'"></span>
                            </button>
                        </div>
                        
                        <!-- Error Display -->
                        <div x-show="error" x-text="error" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm"></div>
                        
                        <button 
                            type="submit"
                            :disabled="loading || !otpCode || otpCode.length !== 4"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed text-white py-3 px-4 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 hover:shadow-lg">
                            <span x-show="!loading">Verify & Continue</span>
                            <span x-show="loading" class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Verifying...
                            </span>
                        </button>
                        
                        <button 
                            type="button"
                            @click="currentStep = 'phone'; error = ''; otpCode = ''"
                            class="w-full mt-3 text-gray-600 hover:text-gray-800 py-2 px-4 text-sm transition-colors">
                            ← Change Phone Number
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function authModal() {
    return {
        showModal: false,
        currentStep: 'phone', // 'phone' or 'otp'
        phoneNumber: '',
        otpCode: '',
        loading: false,
        error: '',
        resendTimer: 0,
        resendInterval: null,

        openModal() {
            this.showModal = true;
            this.resetForm();
        },

        closeModal() {
            this.showModal = false;
            this.resetForm();
        },

        resetForm() {
            this.currentStep = 'phone';
            this.phoneNumber = '';
            this.otpCode = '';
            this.loading = false;
            this.error = '';
            this.stopResendTimer();
        },

        async sendOTP() {
            if (!this.phoneNumber) {
                this.error = 'Please enter your phone number';
                return;
            }

            this.loading = true;
            this.error = '';

            try {
                const response = await fetch('/auth/authenticate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        phone: this.phoneNumber
                    })
                });

                const data = await response.json();

                if (data.status === 'success' && data.data.otp_sent) {
                    this.currentStep = 'otp';
                    this.startResendTimer();
                    // For development, show OTP in console
                    console.log('OTP sent successfully');
                } else {
                    this.error = data.message || 'Failed to send OTP';
                }
            } catch (error) {
                console.error('Error sending OTP:', error);
                this.error = 'Failed to send OTP. Please try again.';
            } finally {
                this.loading = false;
            }
        },

        async verifyOTP() {
            if (!this.otpCode || this.otpCode.length !== 4) {
                this.error = 'Please enter the 4-digit code';
                return;
            }

            this.loading = true;
            this.error = '';

            try {
                const response = await fetch('/auth/authenticate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        phone: this.phoneNumber,
                        otp: this.otpCode
                    })
                });

                const data = await response.json();

                if (data.status === 'success' && data.data.authenticated) {
                    // Store token for potential API calls
                    localStorage.setItem('auth_token', data.data.token);
                    
                    // Use the redirect URL provided by the server
                    window.location.href = data.data.redirect_url || '/dashboard';
                } else {
                    this.error = data.message || 'Invalid verification code';
                }
            } catch (error) {
                console.error('Error verifying OTP:', error);
                this.error = 'Failed to verify code. Please try again.';
            } finally {
                this.loading = false;
            }
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
        }
    }
}
</script>