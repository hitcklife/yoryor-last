<x-layout.landing>
    <x-slot:title>About YorYor - Our Mission & Story</x-slot:title>
    <x-slot:description>Learn about YorYor's mission to connect hearts through meaningful relationships and our commitment to helping you find your perfect match.</x-slot:description>

    <!-- Hero Section -->
    <section class="hero-gradient py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-5xl lg:text-7xl font-bold text-gray-900 mb-6">
                    About <span class="gradient-text">YorYor</span>
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    We believe everyone deserves to find love. YorYor was created to bridge the gap between traditional matchmaking and modern technology.
                </p>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <!-- Left Content -->
                <div>
                    <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-8">
                        Our <span class="gradient-text">Mission</span>
                    </h2>
                    <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                        YorYor is more than just a dating app—it's a platform built on the belief that meaningful connections are formed when people share similar values, cultural backgrounds, and life goals.
                    </p>
                    <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                        We combine cutting-edge technology with traditional relationship values to create an environment where genuine connections can flourish. Our platform respects cultural diversity while celebrating the universal desire for love and companionship.
                    </p>
                    
                    <!-- Values -->
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center mr-4 mt-1">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Authentic Connections</h4>
                                <p class="text-gray-600">We prioritize genuine relationships over superficial matches.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center mr-4 mt-1">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Cultural Respect</h4>
                                <p class="text-gray-600">We honor diverse cultural backgrounds and family values.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center mr-4 mt-1">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Safety First</h4>
                                <p class="text-gray-600">Advanced security measures ensure a safe dating environment.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Visual -->
                <div class="relative">
                    <div class="bg-gradient-to-br from-pink-100 to-purple-100 rounded-3xl p-8 shadow-xl">
                        <div class="grid grid-cols-2 gap-6">
                            <!-- Stats Cards -->
                            <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                                <div class="text-3xl font-bold gradient-text mb-2">50K+</div>
                                <div class="text-gray-600 text-sm">Happy Users</div>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                                <div class="text-3xl font-bold gradient-text mb-2">10K+</div>
                                <div class="text-gray-600 text-sm">Successful Matches</div>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                                <div class="text-3xl font-bold gradient-text mb-2">2K+</div>
                                <div class="text-gray-600 text-sm">Love Stories</div>
                            </div>
                            <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                                <div class="text-3xl font-bold gradient-text mb-2">95%</div>
                                <div class="text-gray-600 text-sm">Satisfaction Rate</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Floating Hearts -->
                    <div class="absolute -top-4 -right-4 w-8 h-8 bg-pink-400 rounded-full floating-heart opacity-80"></div>
                    <div class="absolute top-20 -left-6 w-6 h-6 bg-purple-400 rounded-full floating-heart opacity-60" style="animation-delay: 1s;"></div>
                    <div class="absolute -bottom-6 right-10 w-10 h-10 bg-yellow-400 rounded-full floating-heart opacity-70" style="animation-delay: 2s;"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Story Section -->
    <section class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                    Our <span class="gradient-text">Story</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Born from personal experience and a vision to revolutionize how people find love in the digital age.
                </p>
            </div>

            <!-- Timeline -->
            <div class="max-w-4xl mx-auto">
                <div class="relative">
                    <!-- Timeline Line -->
                    <div class="absolute left-1/2 transform -translate-x-1/2 w-1 h-full bg-gradient-to-b from-pink-500 to-purple-600 rounded-full"></div>

                    <!-- Timeline Items -->
                    <div class="space-y-16">
                        <!-- Item 1 -->
                        <div class="flex items-center">
                            <div class="flex-1 text-right pr-8">
                                <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">The Vision</h4>
                                    <p class="text-gray-600">
                                        Our founder experienced firsthand the challenges of finding a compatible partner who shared similar cultural values and life goals in today's fast-paced world.
                                    </p>
                                </div>
                            </div>
                            <div class="relative z-10">
                                <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 pl-8">
                                <div class="text-lg font-semibold gradient-text">2023</div>
                            </div>
                        </div>

                        <!-- Item 2 -->
                        <div class="flex items-center">
                            <div class="flex-1 text-right pr-8">
                                <div class="text-lg font-semibold gradient-text">Early 2024</div>
                            </div>
                            <div class="relative z-10">
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 pl-8">
                                <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">Development Begins</h4>
                                    <p class="text-gray-600">
                                        We assembled a team of developers, relationship experts, and cultural consultants to build a platform that truly understands the complexities of modern relationships.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Item 3 -->
                        <div class="flex items-center">
                            <div class="flex-1 text-right pr-8">
                                <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">Beta Testing</h4>
                                    <p class="text-gray-600">
                                        Through extensive beta testing with diverse communities, we refined our matching algorithm and safety features to ensure the best possible experience.
                                    </p>
                                </div>
                            </div>
                            <div class="relative z-10">
                                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 pl-8">
                                <div class="text-lg font-semibold gradient-text">Mid 2024</div>
                            </div>
                        </div>

                        <!-- Item 4 -->
                        <div class="flex items-center">
                            <div class="flex-1 text-right pr-8">
                                <div class="text-lg font-semibold gradient-text">Late 2024</div>
                            </div>
                            <div class="relative z-10">
                                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 pl-8">
                                <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">Launching Soon</h4>
                                    <p class="text-gray-600">
                                        YorYor is ready to launch! We're excited to help thousands of people find meaningful connections and lasting love through our innovative platform.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                    Meet Our <span class="gradient-text">Team</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    A diverse group of passionate individuals committed to helping you find love.
                </p>
            </div>

            <!-- Team Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Team Member 1 -->
                <div class="text-center card-hover">
                    <div class="relative mb-6">
                        <div class="w-32 h-32 bg-gradient-to-br from-pink-400 to-purple-600 rounded-full mx-auto flex items-center justify-center shadow-lg">
                            <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-2">Sarah Johnson</h4>
                    <p class="text-pink-600 font-medium mb-3">CEO & Founder</p>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Former matchmaker with 10+ years of experience in helping people find lasting love and meaningful connections.
                    </p>
                </div>

                <!-- Team Member 2 -->
                <div class="text-center card-hover">
                    <div class="relative mb-6">
                        <div class="w-32 h-32 bg-gradient-to-br from-blue-400 to-purple-600 rounded-full mx-auto flex items-center justify-center shadow-lg">
                            <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-2">Michael Chen</h4>
                    <p class="text-purple-600 font-medium mb-3">CTO</p>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        AI and machine learning expert specializing in recommendation systems and user behavior analysis.
                    </p>
                </div>

                <!-- Team Member 3 -->
                <div class="text-center card-hover">
                    <div class="relative mb-6">
                        <div class="w-32 h-32 bg-gradient-to-br from-green-400 to-blue-600 rounded-full mx-auto flex items-center justify-center shadow-lg">
                            <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-2">Dr. Amina Patel</h4>
                    <p class="text-green-600 font-medium mb-3">Relationship Psychologist</p>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Licensed therapist and relationship expert with expertise in cross-cultural relationships and family dynamics.
                    </p>
                </div>

                <!-- Team Member 4 -->
                <div class="text-center card-hover">
                    <div class="relative mb-6">
                        <div class="w-32 h-32 bg-gradient-to-br from-yellow-400 to-orange-600 rounded-full mx-auto flex items-center justify-center shadow-lg">
                            <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-2">James Rodriguez</h4>
                    <p class="text-orange-600 font-medium mb-3">Head of Safety</p>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Cybersecurity specialist ensuring user privacy and safety with advanced verification and protection systems.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Join Us Section -->
    <section class="py-24 bg-gradient-to-br from-pink-50 to-purple-50">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-8">
                Ready to Begin Your <span class="gradient-text">Love Story</span>?
            </h2>
            <p class="text-xl text-gray-600 mb-12 leading-relaxed">
                Join thousands of singles who trust YorYor to help them find meaningful relationships. Your perfect match is waiting.
            </p>
            
            <!-- Download Buttons -->
            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                <a href="#" class="inline-flex items-center justify-center bg-gradient-to-r from-pink-500 to-purple-600 text-white px-8 py-4 rounded-2xl font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                    <svg class="w-8 h-8 mr-3" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                    </svg>
                    <div class="text-left">
                        <div class="text-xs text-pink-200">Coming Soon on</div>
                        <div class="text-lg font-bold">App Store</div>
                    </div>
                </a>
                
                <a href="#" class="inline-flex items-center justify-center bg-gradient-to-r from-pink-500 to-purple-600 text-white px-8 py-4 rounded-2xl font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                    <svg class="w-8 h-8 mr-3" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                    </svg>
                    <div class="text-left">
                        <div class="text-xs text-pink-200">Coming Soon on</div>
                        <div class="text-lg font-bold">Google Play</div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Include Islamic/Halal Compliance Section -->
    @include('landing.home.halal-compliance')

    <!-- CTA Section -->
    <section class="py-24 bg-gradient-to-br from-purple-50 to-pink-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                Join Our Growing Community
            </h2>
            <p class="text-xl text-gray-600 mb-8">
                Start your journey with YorYor today. 100% FREE forever!
            </p>
            <a href="{{ route('start') }}" class="inline-flex items-center justify-center bg-gradient-to-r from-purple-600 to-pink-600 text-white px-8 py-4 rounded-2xl font-semibold text-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <span>{{ __('messages.get_started') }}</span>
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </section>

</x-layouts.landing>