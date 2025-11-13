@extends('layouts.app')

@section('content')
<!-- Hero Section -->
@include('landing.home.hero-section')

<!-- Global Community Section -->
@include('landing.home.global-community')

<!-- Diaspora Features Section -->
@include('landing.home.diaspora-features')

<!-- How It Works Section -->
@include('landing.home.how-it-works')

<!-- Location-Based Matching Section -->
@include('landing.home.location-matching')

<!-- Enhanced Parents Section -->
@include('landing.home.enhanced-parents')

<!-- International Success Stories Section -->
@include('landing.home.international-success-stories')

<!-- Privacy & Safety Features Section -->
<section class="py-24 bg-gradient-to-br from-slate-50 via-white to-gray-50 relative overflow-hidden">
    <!-- Background decorative elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-1/4 w-64 h-64 bg-gradient-to-br from-purple-200/20 to-pink-200/20 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute bottom-20 right-1/4 w-80 h-80 bg-gradient-to-br from-pink-200/20 to-rose-200/20 rounded-full blur-3xl animate-float-reverse"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-purple-200/10 to-pink-200/10 rounded-full blur-3xl animate-float"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <div class="inline-flex items-center bg-gradient-to-r from-purple-100/80 via-pink-50/80 to-rose-100/80 backdrop-blur-sm border border-purple-200/60 rounded-full px-8 py-4 mb-8 shadow-lg shadow-purple-500/10 animate-fade-in group hover:shadow-purple-500/20 transition-all duration-300">
                <span class="text-2xl mr-3 animate-bounce">🛡️</span>
                <span class="text-sm font-semibold bg-gradient-to-r from-purple-700 to-pink-600 bg-clip-text text-transparent">Privacy & Safety First</span>
                <!-- Animated sparkle effect -->
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-purple-400 rounded-full animate-ping opacity-75"></div>
            </div>
            <h2 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-6 animate-slide-up">
                Your <span class="bg-gradient-to-r from-purple-600 via-pink-600 to-rose-600 bg-clip-text text-transparent animate-pulse">Safety</span> is Our Priority
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto animate-fade-in" style="animation-delay: 0.3s;">
                We understand the importance of safety in international relationships. Our comprehensive security measures ensure you can focus on finding love while we protect your privacy and well-being.
            </p>
        </div>

        <!-- Safety Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            <!-- Feature 1 - Identity Verification -->
            <div class="bg-gradient-to-br from-white/95 to-purple-50/80 backdrop-blur-sm rounded-2xl p-8 border border-purple-200/40 shadow-lg hover:shadow-purple-500/20 hover:scale-105 transition-all duration-500 animate-fade-in group" style="animation-delay: 0.1s;">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl flex items-center justify-center mb-6 shadow-lg group-hover:shadow-purple-500/30 group-hover:scale-110 transition-all duration-300">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-purple-900 transition-colors duration-300">International Identity Verification</h3>
                <p class="text-gray-600 leading-relaxed mb-4 group-hover:text-gray-700 transition-colors duration-300">
                    Multi-level verification including government ID, video verification, and cross-border reference checks to ensure authenticity worldwide.
                </p>
                <div class="flex items-center text-purple-600 font-medium group-hover:text-purple-700 transition-colors duration-300">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Global Verification System
                </div>
            </div>

            <!-- Feature 2 - Safe Meeting Guidelines -->
            <div class="bg-gradient-to-br from-white/95 to-pink-50/80 backdrop-blur-sm rounded-2xl p-8 border border-pink-200/40 shadow-lg hover:shadow-pink-500/20 hover:scale-105 transition-all duration-500 animate-fade-in group" style="animation-delay: 0.2s;">
                <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-pink-700 rounded-2xl flex items-center justify-center mb-6 shadow-lg group-hover:shadow-pink-500/30 group-hover:scale-110 transition-all duration-300">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-pink-900 transition-colors duration-300">Cross-Border Safety Guidelines</h3>
                <p class="text-gray-600 leading-relaxed mb-4 group-hover:text-gray-700 transition-colors duration-300">
                    Built-in safety guides for first meetings, public venue suggestions, and emergency contact features for international relationships.
                </p>
                <div class="flex items-center text-pink-600 font-medium group-hover:text-pink-700 transition-colors duration-300">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Safety First Always
                </div>
            </div>

            <!-- Feature 3 - International Family Features -->
            <div class="bg-gradient-to-br from-white/95 to-amber-50/80 backdrop-blur-sm rounded-2xl p-8 border border-amber-200/40 shadow-lg hover:shadow-amber-500/20 hover:scale-105 transition-all duration-500 animate-fade-in group" style="animation-delay: 0.3s;">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-amber-700 rounded-2xl flex items-center justify-center mb-6 shadow-lg group-hover:shadow-amber-500/30 group-hover:scale-110 transition-all duration-300">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-amber-900 transition-colors duration-300">Families Unite Across Borders</h3>
                <p class="text-gray-600 leading-relaxed mb-4 group-hover:text-gray-700 transition-colors duration-300">
                    Video meetings for overseas families, international reference checks, cross-border family introductions, and multi-country wedding planning support.
                </p>
                <div class="space-y-2">
                    <div class="flex items-center text-amber-600 font-medium group-hover:text-amber-700 transition-colors duration-300">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        International Family Video Calls
                    </div>
                    <div class="flex items-center text-amber-600 font-medium group-hover:text-amber-700 transition-colors duration-300">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Cross-Border Reference Checks
                    </div>
                    <div class="flex items-center text-amber-600 font-medium group-hover:text-amber-700 transition-colors duration-300">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Translation Services
                    </div>
                </div>
            </div>

            <!-- Feature 4 - Report & Block -->
            <div class="bg-gradient-to-br from-white/95 to-red-50/80 backdrop-blur-sm rounded-2xl p-8 border border-red-200/40 shadow-lg hover:shadow-red-500/20 hover:scale-105 transition-all duration-500 animate-fade-in group" style="animation-delay: 0.4s;">
                <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-700 rounded-2xl flex items-center justify-center mb-6 shadow-lg group-hover:shadow-red-500/30 group-hover:scale-110 transition-all duration-300">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-red-900 transition-colors duration-300">Report & Block</h3>
                <p class="text-gray-600 leading-relaxed mb-4 group-hover:text-gray-700 transition-colors duration-300">
                    Easy reporting system with immediate response. Block unwanted users and report suspicious behavior with 24/7 support.
                </p>
                <div class="flex items-center text-red-600 font-medium group-hover:text-red-700 transition-colors duration-300">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    24/7 Safety Support
                </div>
            </div>

            <!-- Feature 5 - Data Protection -->
            <div class="bg-gradient-to-br from-white/95 to-green-50/80 backdrop-blur-sm rounded-2xl p-8 border border-green-200/40 shadow-lg hover:shadow-green-500/20 hover:scale-105 transition-all duration-500 animate-fade-in group" style="animation-delay: 0.5s;">
                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-700 rounded-2xl flex items-center justify-center mb-6 shadow-lg group-hover:shadow-green-500/30 group-hover:scale-110 transition-all duration-300">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-green-900 transition-colors duration-300">Data Protection</h3>
                <p class="text-gray-600 leading-relaxed mb-4 group-hover:text-gray-700 transition-colors duration-300">
                    End-to-end encryption, secure data storage, and GDPR compliance. Your personal information is protected with military-grade security.
                </p>
                <div class="flex items-center text-green-600 font-medium group-hover:text-green-700 transition-colors duration-300">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Military-Grade Security
                </div>
            </div>

            <!-- Feature 6 - Emergency Support -->
            <div class="bg-gradient-to-br from-white/95 to-blue-50/80 backdrop-blur-sm rounded-2xl p-8 border border-blue-200/40 shadow-lg hover:shadow-blue-500/20 hover:scale-105 transition-all duration-500 animate-fade-in group" style="animation-delay: 0.6s;">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl flex items-center justify-center mb-6 shadow-lg group-hover:shadow-blue-500/30 group-hover:scale-110 transition-all duration-300">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-900 transition-colors duration-300">Emergency Support</h3>
                <p class="text-gray-600 leading-relaxed mb-4 group-hover:text-gray-700 transition-colors duration-300">
                    24/7 emergency support with local contacts in major cities worldwide. Get help when you need it, wherever you are.
                </p>
                <div class="flex items-center text-blue-600 font-medium group-hover:text-blue-700 transition-colors duration-300">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    24/7 Global Support
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA Section -->
<section class="py-24 bg-gradient-to-br from-teal-50 via-white to-blue-50 relative overflow-hidden">
    <!-- Background decorative elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-1/4 w-64 h-64 bg-gradient-to-br from-teal-200/20 to-blue-200/20 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute bottom-20 right-1/4 w-80 h-80 bg-gradient-to-br from-blue-200/20 to-indigo-200/20 rounded-full blur-3xl animate-float-reverse"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-teal-200/10 to-blue-200/10 rounded-full blur-3xl animate-float"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center">
            <h2 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-6 animate-slide-up">
                Ready to Find Your Perfect <span class="bg-gradient-to-r from-teal-600 via-blue-600 to-indigo-600 bg-clip-text text-transparent animate-pulse">Match Worldwide?</span>
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-8 animate-fade-in" style="animation-delay: 0.3s;">
                Join 50,000+ Uzbeks worldwide who've found love across borders. Whether you're in New York or Namangan, your perfect match is just one swipe away!
            </p>
            
            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                <a href="#download" class="relative inline-flex items-center justify-center bg-gradient-to-r from-teal-600 via-blue-600 to-indigo-600 hover:shadow-teal text-white px-10 py-5 rounded-2xl font-semibold transition-all duration-500 shadow-2xl shadow-teal/30 text-lg transform hover:scale-110 hover:-translate-y-1 animate-bounce-in overflow-hidden group">
                    <span class="relative z-10 flex items-center">
                        <svg class="w-5 h-5 mr-3 transform transition-transform duration-300 group-hover:rotate-12" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('messages.download_yoryor') }}
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-teal-500 via-blue-500 to-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300 animate-pulse"></div>
                    <div class="absolute top-0 -left-1 w-1 h-full bg-gradient-to-b from-transparent via-white/30 to-transparent transform -skew-x-12 group-hover:left-full transition-all duration-700"></div>
                </a>
                <a href="#how-it-works" class="relative inline-flex items-center justify-center glass-card text-gray-700 px-10 py-5 rounded-2xl font-semibold transition-all duration-500 border-0 text-lg transform hover:scale-110 hover:-translate-y-1 animate-bounce-in overflow-hidden group hover:shadow-2xl hover:shadow-teal/20">
                    <span class="relative z-10 flex items-center">
                        {{ __('messages.learn_how_it_works') }}
                        <svg class="w-4 h-4 ml-2 transform transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-teal-50/50 via-blue-50/50 to-indigo-50/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                </a>
            </div>

            <!-- Final Message -->
            <div class="bg-gradient-to-r from-teal-600 to-blue-600 rounded-2xl p-8 text-white">
                <h3 class="text-2xl font-bold mb-4">🌍 Join the Global Uzbek Community!</h3>
                <p class="text-lg text-teal-100">
                    Be the first to experience international Uzbek dating. Connect with Uzbeks worldwide while preserving our values and traditions.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
