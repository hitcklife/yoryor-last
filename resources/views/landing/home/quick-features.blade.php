<!-- Quick Features Navigation -->
<section class="py-16 bg-gradient-to-br from-gray-50 via-white to-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                Discover YorYor <span class="gradient-text">Features</span>
            </h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Explore our unique features designed specifically for the Uzbek community
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Sovchilik Card -->
            <a href="{{ route('features') }}#sovchilik" class="group">
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-amber-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <span class="text-3xl">👰</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ __('messages.traditional_matchmaking') }}</h3>
                    <p class="text-gray-600 mb-4">
                        Traditional Sovchilik with modern technology. Professional matchmakers and family involvement.
                    </p>
                    <div class="flex items-center text-amber-600 font-medium group-hover:translate-x-2 transition-transform">
                        <span>Learn More</span>
                        <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                    </div>
                </div>
            </a>

            <!-- Telegram Integration Card -->
            <a href="{{ route('features') }}#telegram" class="group">
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-blue-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <span class="text-3xl">✈️</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ __('messages.telegram_easy_access') }}</h3>
                    <p class="text-gray-600 mb-4">
                        Connect through Telegram - used by 18 million Uzbeks. Quick registration and family group chats.
                    </p>
                    <div class="flex items-center text-blue-600 font-medium group-hover:translate-x-2 transition-transform">
                        <span>Learn More</span>
                        <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                    </div>
                </div>
            </a>

            <!-- Halal Compliance Card -->
            <a href="{{ route('about') }}#halal" class="group">
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-emerald-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <span class="text-3xl">☪️</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ __('messages.hundred_percent_halal') }}</h3>
                    <p class="text-gray-600 mb-4">
                        100% Halal and Sharia compliant. Islamic guidance, nikah support, and prayer reminders.
                    </p>
                    <div class="flex items-center text-emerald-600 font-medium group-hover:translate-x-2 transition-transform">
                        <span>Learn More</span>
                        <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- View All Features Button -->
        <div class="text-center mt-12">
            <a href="{{ route('features') }}" class="inline-flex items-center justify-center bg-gradient-to-r from-purple-600 to-pink-600 text-white px-8 py-3 rounded-xl font-semibold hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                View All Features
                <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
            </a>
        </div>
    </div>
</section>