<!-- Newsletter Signup Section -->
<section class="py-24 bg-gradient-to-br from-emerald-50 via-white to-teal-50 relative overflow-hidden">
    <!-- Background decorative elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 right-1/4 w-72 h-72 bg-gradient-to-br from-emerald-200/20 to-teal-200/20 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute bottom-20 left-1/4 w-80 h-80 bg-gradient-to-br from-teal-200/20 to-cyan-200/20 rounded-full blur-3xl animate-float-reverse"></div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Newsletter Signup Card -->
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-8 md:p-12 shadow-2xl border border-gray-200/40 relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-5">
                <div class="absolute top-0 left-0 w-full h-full" style="background-image: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23000000" fill-opacity="0.1"><circle cx="30" cy="30" r="4"/></g></svg>')"></div>
            </div>

            <div class="relative z-10" x-data="{
                email: '',
                isSubscribed: false,
                isLoading: false,
                subscribe() {
                    if (!this.email || !this.email.includes('@')) return;
                    
                    this.isLoading = true;
                    
                    // Simulate API call
                    setTimeout(() => {
                        this.isLoading = false;
                        this.isSubscribed = true;
                        this.email = '';
                        
                        // Reset after 3 seconds
                        setTimeout(() => {
                            this.isSubscribed = false;
                        }, 3000);
                    }, 1500);
                }
            }">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center bg-gradient-to-r from-emerald-100/80 via-teal-50/80 to-cyan-100/80 backdrop-blur-sm border border-emerald-200/60 rounded-full px-6 py-3 mb-6 shadow-lg shadow-emerald-500/10 animate-fade-in group hover:shadow-emerald-500/20 transition-all duration-300">
                        <span class="text-xl mr-2 animate-bounce">📧</span>
                        <span class="text-sm font-semibold bg-gradient-to-r from-emerald-700 to-teal-600 bg-clip-text text-transparent">Stay Connected</span>
                    </div>
                    <h2 class="text-3xl md:text-5xl font-bold text-gray-900 mb-4 animate-slide-up">
                        Get <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent animate-pulse">Updates & Tips</span>
                    </h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto animate-fade-in" style="animation-delay: 0.2s;">
                        Join our newsletter for success stories, dating tips, cultural insights, and exclusive updates from the YorYor Global community.
                    </p>
                </div>

                <!-- Newsletter Form -->
                <div x-show="!isSubscribed" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                    <form @submit.prevent="subscribe()" class="max-w-md mx-auto">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <input type="email" 
                                       x-model="email"
                                       placeholder="Enter your email address"
                                       class="w-full px-6 py-4 rounded-xl border border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all duration-300 text-gray-900 placeholder-gray-500"
                                       required>
                            </div>
                            <button type="submit" 
                                    :disabled="isLoading || !email"
                                    class="px-8 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center">
                                <span x-show="!isLoading">Subscribe</span>
                                <span x-show="isLoading" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Subscribing...
                                </span>
                            </button>
                        </div>
                    </form>

                    <!-- Benefits List -->
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 mb-2">Success Stories</h3>
                            <p class="text-sm text-gray-600">Real love stories from our global community</p>
                        </div>
                        <div class="text-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-teal-500 to-cyan-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 mb-2">Dating Tips</h3>
                            <p class="text-sm text-gray-600">Expert advice for meaningful connections</p>
                        </div>
                        <div class="text-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <h3 class="font-semibold text-gray-900 mb-2">Cultural Insights</h3>
                            <p class="text-sm text-gray-600">Learn about Uzbek traditions and values</p>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <div x-show="isSubscribed" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="opacity-0 transform scale-95" 
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Welcome to YorYor Global!</h3>
                    <p class="text-lg text-gray-600 mb-6">Thank you for subscribing! You'll receive our first newsletter within 24 hours.</p>
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                        <p class="text-sm text-emerald-800">
                            <strong>What's next?</strong> Check your email for a confirmation link and get ready to receive exclusive content from our global community.
                        </p>
                    </div>
                </div>

                <!-- Trust Indicators -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between text-sm text-gray-500">
                        <div class="flex items-center mb-4 sm:mb-0">
                            <svg class="w-4 h-4 text-emerald-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Your email is safe with us</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-emerald-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <span>Unsubscribe anytime</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
