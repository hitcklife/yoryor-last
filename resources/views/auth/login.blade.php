<x-layouts.landing>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-3 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">YorYor</h1>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome back</h2>
                <p class="text-gray-600">Sign in to your account to continue</p>
            </div>

            <div x-data="simpleAuth()" class="mt-8">
                <!-- Phone Step -->
                <div x-show="currentStep === 'phone'">
                    <form @submit.prevent="sendOTP()">
                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input
                                type="tel"
                                id="phone"
                                x-model="phoneNumber"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="+1 (555) 123-4567"
                                required>
                        </div>

                        <div x-show="error" x-text="error" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm"></div>

                        <button
                            type="submit"
                            :disabled="loading || !phoneNumber"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white py-3 px-4 rounded-lg font-medium transition-all duration-200 disabled:opacity-50">
                            <span x-show="!loading">Sign In with Phone</span>
                            <span x-show="loading">Sending OTP...</span>
                        </button>
                    </form>
                </div>

                <!-- OTP Step -->
                <div x-show="currentStep === 'otp'">
                    <form @submit.prevent="verifyOTP()">
                        <div class="mb-4">
                            <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">
                                Verification Code
                                <span class="text-sm text-gray-500">sent to <span x-text="phoneNumber"></span></span>
                            </label>
                            <input
                                type="text"
                                id="otp"
                                x-model="otpCode"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-2xl font-mono tracking-widest"
                                placeholder="----"
                                maxlength="4"
                                required>
                        </div>

                        <div class="text-center mb-4">
                            <button
                                type="button"
                                @click="sendOTP()"
                                :disabled="loading || resendTimer > 0"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium disabled:opacity-50">
                                <span x-show="resendTimer === 0">Resend Code</span>
                                <span x-show="resendTimer > 0" x-text="'Resend in ' + resendTimer + 's'"></span>
                            </button>
                        </div>

                        <div x-show="error" x-text="error" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm"></div>

                        <button
                            type="submit"
                            :disabled="loading || !otpCode || otpCode.length !== 4"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white py-3 px-4 rounded-lg font-medium transition-all duration-200 disabled:opacity-50 mb-3">
                            <span x-show="!loading">Verify & Sign In</span>
                            <span x-show="loading">Verifying...</span>
                        </button>

                        <button
                            type="button"
                            @click="currentStep = 'phone'; error = ''; otpCode = ''"
                            class="w-full text-gray-600 hover:text-gray-800 py-2 text-sm">
                            ← Change Phone Number
                        </button>
                    </form>
                </div>

                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-gray-50 text-gray-500 font-medium">Or sign in with</span>
                    </div>
                </div>

                <!-- Social Login Options -->
                <div class="space-y-3">
                    <!-- Google -->
                    <a href="{{ route('auth.socialite', 'google') }}" class="w-full bg-white border border-gray-300 text-gray-700 py-3 px-4 rounded-lg font-medium flex items-center justify-center space-x-3 hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285f4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34a853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#fbbc05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#ea4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span>Continue with Google</span>
                    </a>

                    <!-- Facebook -->
                    <a href="{{ route('auth.socialite', 'facebook') }}" class="w-full bg-[#1877f2] text-white py-3 px-4 rounded-lg font-medium hover:bg-[#166fe5] transition-colors flex items-center justify-center space-x-3">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span>Continue with Facebook</span>
                    </a>
                </div>

                <!-- Sign Up Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Don't have an account?
                        <a href="{{ route('start') }}" class="text-blue-600 hover:text-blue-700 font-semibold hover:underline transition-colors">Sign up</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function simpleAuth() {
            return {
                currentStep: 'phone',
                phoneNumber: '',
                otpCode: '',
                loading: false,
                error: '',
                resendTimer: 0,
                resendInterval: null,

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
</x-layouts.landing>
