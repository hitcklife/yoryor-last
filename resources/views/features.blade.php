<x-layouts.landing title="Features - YorYor | Platform Features for Uzbek Dating" description="Discover YorYor's unique features designed for the Uzbek community: Sovchilik matchmaking, Telegram integration, family approval system, and more.">

    <!-- Hero Section -->
    <section class="hero-gradient py-24 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-5xl lg:text-7xl font-bold text-gray-900 mb-6">
                    Platform <span class="gradient-text">Features</span>
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Combining modern technology with traditional Uzbek values to help you find your perfect match.
                </p>
            </div>
        </div>
    </section>

    <!-- Include Telegram Integration Section -->
    @include('landing.home.telegram-integration')

    <!-- Include Sovchilik Section -->
    @include('landing.home.sovchilik-section')

    <!-- Core Features Grid -->
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                    Core <span class="gradient-text">Features</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Everything you need for a safe, respectful, and successful matchmaking experience
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Family Approval System -->
                <div class="bg-gradient-to-br from-white to-purple-50 rounded-2xl p-8 border border-purple-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center mb-6">
                        <span class="text-2xl">👨‍👩‍👧‍👦</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ __('messages.family_approval') }}</h3>
                    <p class="text-gray-600 mb-4">
                        Parents and family members can participate in the matchmaking process with dedicated family accounts.
                    </p>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-start">
                            <span class="text-purple-500 mr-2">✓</span>
                            <span>Family group chats</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-purple-500 mr-2">✓</span>
                            <span>Parent approval workflow</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-purple-500 mr-2">✓</span>
                            <span>Elder consultation features</span>
                        </li>
                    </ul>
                </div>

                <!-- Video Verification -->
                <div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl p-8 border border-blue-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center mb-6">
                        <span class="text-2xl">🎥</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Video Verification</h3>
                    <p class="text-gray-600 mb-4">
                        Multi-level verification system including video calls to ensure authentic profiles.
                    </p>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">✓</span>
                            <span>Live video verification</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">✓</span>
                            <span>Government ID check</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">✓</span>
                            <span>Verified badge system</span>
                        </li>
                    </ul>
                </div>

                <!-- Smart Matching -->
                <div class="bg-gradient-to-br from-white to-green-50 rounded-2xl p-8 border border-green-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mb-6">
                        <span class="text-2xl">💝</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Smart Matching</h3>
                    <p class="text-gray-600 mb-4">
                        AI-powered matching based on cultural values, family background, and life goals.
                    </p>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span>Cultural compatibility</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span>Religious alignment</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span>Family values matching</span>
                        </li>
                    </ul>
                </div>

                <!-- Privacy Controls -->
                <div class="bg-gradient-to-br from-white to-pink-50 rounded-2xl p-8 border border-pink-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center mb-6">
                        <span class="text-2xl">🔒</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Privacy Controls</h3>
                    <p class="text-gray-600 mb-4">
                        Complete control over who sees your profile and personal information.
                    </p>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-start">
                            <span class="text-pink-500 mr-2">✓</span>
                            <span>Photo privacy options</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-pink-500 mr-2">✓</span>
                            <span>Contact info protection</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-pink-500 mr-2">✓</span>
                            <span>Anonymous browsing mode</span>
                        </li>
                    </ul>
                </div>

                <!-- Global Reach -->
                <div class="bg-gradient-to-br from-white to-indigo-50 rounded-2xl p-8 border border-indigo-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mb-6">
                        <span class="text-2xl">🌍</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Global Uzbek Network</h3>
                    <p class="text-gray-600 mb-4">
                        Connect with Uzbeks worldwide across 45+ countries.
                    </p>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-start">
                            <span class="text-indigo-500 mr-2">✓</span>
                            <span>Location-based search</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-indigo-500 mr-2">✓</span>
                            <span>Diaspora communities</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-indigo-500 mr-2">✓</span>
                            <span>Multi-language support</span>
                        </li>
                    </ul>
                </div>

                <!-- 24/7 Support -->
                <div class="bg-gradient-to-br from-white to-amber-50 rounded-2xl p-8 border border-amber-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center mb-6">
                        <span class="text-2xl">🆘</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">24/7 Support</h3>
                    <p class="text-gray-600 mb-4">
                        Round-the-clock support in Uzbek, Russian, and English.
                    </p>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-start">
                            <span class="text-amber-500 mr-2">✓</span>
                            <span>Live chat support</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-amber-500 mr-2">✓</span>
                            <span>Video call assistance</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-amber-500 mr-2">✓</span>
                            <span>Emergency help line</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 bg-gradient-to-br from-purple-50 to-pink-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                Ready to Experience All Features?
            </h2>
            <p class="text-xl text-gray-600 mb-8">
                Join YorYor today and start your journey to finding your perfect match. 100% FREE!
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('start') }}" class="inline-flex items-center justify-center bg-gradient-to-r from-purple-600 to-pink-600 text-white px-8 py-4 rounded-2xl font-semibold text-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <span>{{ __('messages.get_started') }}</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center bg-white text-purple-600 px-8 py-4 rounded-2xl font-semibold text-lg border-2 border-purple-200 hover:bg-purple-50 transition-all duration-300">
                    {{ __('messages.contact') }} Us
                </a>
            </div>
        </div>
    </section>

</x-layouts.landing>