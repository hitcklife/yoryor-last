<!-- International Pricing Section -->
<section class="py-24 bg-gradient-to-br from-slate-50 via-white to-gray-50 relative overflow-hidden">
    <!-- Background decorative elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-1/4 w-64 h-64 bg-gradient-to-br from-green-200/20 to-emerald-200/20 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute bottom-20 right-1/4 w-80 h-80 bg-gradient-to-br from-blue-200/20 to-indigo-200/20 rounded-full blur-3xl animate-float-reverse"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <div class="inline-flex items-center bg-gradient-to-r from-green-100/80 via-emerald-50/80 to-teal-100/80 backdrop-blur-sm border border-green-200/60 rounded-full px-8 py-4 mb-8 shadow-lg shadow-green-500/10 animate-fade-in group hover:shadow-green-500/20 transition-all duration-300">
                <span class="text-2xl mr-3 animate-bounce">💰</span>
                <span class="text-sm font-semibold bg-gradient-to-r from-green-700 to-emerald-600 bg-clip-text text-transparent">International Pricing Plans</span>
                <!-- Animated sparkle effect -->
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full animate-ping opacity-75"></div>
            </div>
            <h2 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-6 animate-slide-up">
                Global Plans for <span class="bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 bg-clip-text text-transparent animate-pulse">Every Budget</span>
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto animate-fade-in" style="animation-delay: 0.3s;">
                Choose the perfect plan for your international journey. Multi-currency support with automatic conversion to your local currency (USD, EUR, GBP, UZS).
            </p>
        </div>

        <!-- Pricing Plans -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16" x-data="{
            selectedCurrency: 'USD',
            currencies: {
                'USD': { symbol: '$', rate: 1.0 },
                'EUR': { symbol: '€', rate: 0.85 },
                'GBP': { symbol: '£', rate: 0.73 },
                'UZS': { symbol: 'so\'m', rate: 12000 }
            },
            formatPrice(price) {
                const currency = this.currencies[this.selectedCurrency];
                const convertedPrice = price * currency.rate;
                
                if (this.selectedCurrency === 'UZS') {
                    return new Intl.NumberFormat('uz-UZ').format(Math.round(convertedPrice)) + ' ' + currency.symbol;
                }
                
                return currency.symbol + convertedPrice.toFixed(2);
            }
        }" @currency-changed.window="selectedCurrency = $event.detail">
            <!-- Global Basic (Free) -->
            <div class="bg-gradient-to-br from-white/95 to-gray-50/80 backdrop-blur-sm rounded-2xl p-8 border border-gray-200/40 shadow-lg hover:shadow-gray-500/20 hover:scale-105 transition-all duration-500 animate-fade-in group" style="animation-delay: 0.1s;">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Global Basic</h3>
                    <p class="text-gray-600 mb-6">Perfect for getting started</p>
                    <div class="text-4xl font-bold text-gray-900 mb-2">
                        <span class="text-2xl">FREE</span>
                    </div>
                    <p class="text-sm text-gray-500">Forever free plan</p>
                </div>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Basic profile creation</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Browse profiles globally</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Limited daily matches</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Basic search filters</span>
                    </li>
                </ul>
                <button class="w-full bg-gradient-to-r from-gray-600 to-gray-700 text-white py-3 px-6 rounded-xl font-semibold hover:from-gray-700 hover:to-gray-800 transition-all duration-300 transform hover:scale-105">
                    Get Started Free
                </button>
            </div>

            <!-- Global Premium -->
            <div class="bg-gradient-to-br from-white/95 to-blue-50/80 backdrop-blur-sm rounded-2xl p-8 border border-blue-200/40 shadow-lg hover:shadow-blue-500/20 hover:scale-105 transition-all duration-500 animate-fade-in group relative" style="animation-delay: 0.2s;">
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-4 py-1 rounded-full text-sm font-semibold">Most Popular</span>
                </div>
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Global Premium</h3>
                    <p class="text-gray-600 mb-6">For serious international connections</p>
                    <div class="text-4xl font-bold text-gray-900 mb-2">
                        <span x-text="formatPrice(29)"></span>
                        <span class="text-lg text-gray-500">/month</span>
                    </div>
                    <p class="text-sm text-gray-500">Billed monthly</p>
                </div>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Everything in Basic</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Unlimited daily matches</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Advanced search filters</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Priority customer support</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">See who viewed your profile</span>
                    </li>
                </ul>
                <button class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 transform hover:scale-105">
                    Start Premium
                </button>
            </div>

            <!-- Global Elite -->
            <div class="bg-gradient-to-br from-white/95 to-purple-50/80 backdrop-blur-sm rounded-2xl p-8 border border-purple-200/40 shadow-lg hover:shadow-purple-500/20 hover:scale-105 transition-all duration-500 animate-fade-in group" style="animation-delay: 0.3s;">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Global Elite</h3>
                    <p class="text-gray-600 mb-6">For families seeking the best</p>
                    <div class="text-4xl font-bold text-gray-900 mb-2">
                        <span x-text="formatPrice(59)"></span>
                        <span class="text-lg text-gray-500">/month</span>
                    </div>
                    <p class="text-sm text-gray-500">Billed monthly</p>
                </div>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-purple-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Everything in Premium</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-purple-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Family verification badge</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-purple-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Priority profile placement</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-purple-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Dedicated family consultant</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-purple-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Advanced compatibility matching</span>
                    </li>
                </ul>
                <button class="w-full bg-gradient-to-r from-purple-500 to-indigo-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-purple-600 hover:to-indigo-700 transition-all duration-300 transform hover:scale-105">
                    Go Elite
                </button>
            </div>

            <!-- Global Family -->
            <div class="bg-gradient-to-br from-white/95 to-emerald-50/80 backdrop-blur-sm rounded-2xl p-8 border border-emerald-200/40 shadow-lg hover:shadow-emerald-500/20 hover:scale-105 transition-all duration-500 animate-fade-in group" style="animation-delay: 0.4s;">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Global Family</h3>
                    <p class="text-gray-600 mb-6">For entire families worldwide</p>
                    <div class="text-4xl font-bold text-gray-900 mb-2">
                        <span x-text="formatPrice(99)"></span>
                        <span class="text-lg text-gray-500">/month</span>
                    </div>
                    <p class="text-sm text-gray-500">Billed monthly</p>
                </div>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Everything in Elite</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Up to 5 family profiles</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Family group management</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">International family events</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">24/7 family support</span>
                    </li>
                </ul>
                <button class="w-full bg-gradient-to-r from-emerald-500 to-teal-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 transform hover:scale-105">
                    Choose Family
                </button>
            </div>
        </div>

        <!-- Features Comparison -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-8 border border-gray-200/40 shadow-lg">
            <h3 class="text-2xl font-bold text-gray-900 text-center mb-8">All Plans Include</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Multi-currency support
                </div>
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Global profile verification
                </div>
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    International safety features
                </div>
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Cultural compatibility matching
                </div>
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Language preference options
                </div>
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Cross-border communication tools
                </div>
            </div>
        </div>

        <!-- Money Back Guarantee -->
        <div class="text-center mt-12">
            <div class="inline-flex items-center bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-full px-6 py-3">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-semibold text-green-700">30-day money-back guarantee on all paid plans</span>
            </div>
        </div>
    </div>
</section>
