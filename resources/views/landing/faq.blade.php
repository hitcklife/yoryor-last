<x-layout.landing>
    <x-slot:title>Frequently Asked Questions - YorYor</x-slot:title>
    <x-slot:description>Find answers to common questions about YorYor dating app, including safety, matching, subscriptions, and more.</x-slot:description>

    <!-- Hero Section -->
    <section class="hero-gradient py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl lg:text-7xl font-bold text-gray-900 mb-6">
                Frequently Asked <span class="gradient-text">Questions</span>
            </h1>
            <p class="text-xl text-gray-600 leading-relaxed">
                Get answers to the most common questions about YorYor and how our dating platform works.
            </p>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ openFaq: null }">
            <!-- FAQ Categories -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                <div class="bg-gradient-to-br from-pink-50 to-purple-50 rounded-2xl p-6 text-center border border-pink-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Getting Started</h3>
                    <p class="text-gray-600 text-sm">Account setup, profile creation, and first steps</p>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-6 text-center border border-blue-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Matching & Dating</h3>
                    <p class="text-gray-600 text-sm">How our algorithm works and dating tips</p>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-2xl p-6 text-center border border-green-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Safety & Privacy</h3>
                    <p class="text-gray-600 text-sm">Security features and privacy protection</p>
                </div>
            </div>

            <!-- FAQ Items -->
            <div class="space-y-6" x-data="{
                openItem: null,
                toggleItem(index) {
                    this.openItem = this.openItem === index ? null : index;
                }
            }">
                <!-- Getting Started FAQs -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-pink-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                        Getting Started
                    </h2>

                    <!-- FAQ Item 1 -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200/40 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden mb-4">
                        <button @click="toggleItem(0)" class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                How does YorYor Global work for international matches?
                            </h3>
                            <svg class="w-6 h-6 text-gray-500 transform transition-transform duration-300 group-hover:text-blue-600" 
                                 :class="{ 'rotate-180': openItem === 0 }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openItem === 0" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-4"
                             class="px-8 pb-6">
                            <div class="text-gray-700 leading-relaxed">
                                <p class="mb-4">YorYor Global connects Uzbeks worldwide through our advanced matching algorithm that considers:</p>
                                <ul class="list-disc list-inside space-y-2 ml-4">
                                    <li><strong>Cultural Compatibility:</strong> Shared values, traditions, and cultural background</li>
                                    <li><strong>Location Preferences:</strong> Whether you want to stay local or are open to international matches</li>
                                    <li><strong>Language Preferences:</strong> Uzbek, English, Russian, or other languages you speak</li>
                                    <li><strong>Family Values:</strong> Traditional family involvement and approval processes</li>
                                    <li><strong>Lifestyle Compatibility:</strong> Career goals, interests, and life aspirations</li>
                                </ul>
                                <p class="mt-4">Our platform supports cross-border relationships with features like video calls, family group chats, and cultural compatibility scoring.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200/40 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden mb-4">
                        <button @click="toggleItem(1)" class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                Is my family involved in the matching process?
                            </h3>
                            <svg class="w-6 h-6 text-gray-500 transform transition-transform duration-300 group-hover:text-blue-600" 
                                 :class="{ 'rotate-180': openItem === 1 }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openItem === 1" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-4"
                             class="px-8 pb-6">
                            <div class="text-gray-700 leading-relaxed">
                                <p class="mb-4">Yes! Family involvement is a core feature of YorYor Global. We understand the importance of family approval in Uzbek culture:</p>
                                <ul class="list-disc list-inside space-y-2 ml-4">
                                    <li><strong>Family Profiles:</strong> Create profiles for family members who can review potential matches</li>
                                    <li><strong>Group Chats:</strong> Families can communicate with each other before meetings</li>
                                    <li><strong>Approval System:</strong> Family members can approve or provide feedback on matches</li>
                                    <li><strong>Cultural Matching:</strong> We consider family values and cultural compatibility</li>
                                    <li><strong>Privacy Controls:</strong> You control how much family involvement you want</li>
                                </ul>
                                <p class="mt-4">You can choose to involve your family as much or as little as you're comfortable with, respecting both traditional values and modern preferences.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200/40 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden mb-4">
                        <button @click="toggleItem(2)" class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                How do you ensure safety and verification?
                            </h3>
                            <svg class="w-6 h-6 text-gray-500 transform transition-transform duration-300 group-hover:text-blue-600" 
                                 :class="{ 'rotate-180': openItem === 2 }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openItem === 2" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-4"
                             class="px-8 pb-6">
                            <div class="text-gray-700 leading-relaxed">
                                <p class="mb-4">Safety is our top priority. We have multiple layers of verification and safety measures:</p>
                                <ul class="list-disc list-inside space-y-2 ml-4">
                                    <li><strong>Photo Verification:</strong> All profile photos are verified to ensure authenticity</li>
                                    <li><strong>Identity Verification:</strong> Government ID verification for premium members</li>
                                    <li><strong>Background Checks:</strong> Optional background check services for serious relationships</li>
                                    <li><strong>Report & Block:</strong> Easy reporting system for inappropriate behavior</li>
                                    <li><strong>Safety Tips:</strong> Educational content about safe online dating</li>
                                    <li><strong>Emergency Features:</strong> Panic button and emergency contact system</li>
                                    <li><strong>24/7 Support:</strong> Round-the-clock customer support for safety concerns</li>
                                </ul>
                                <p class="mt-4">We also use AI-powered detection to identify and remove fake profiles, ensuring a safe environment for all users.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200/40 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden mb-4">
                        <button @click="toggleItem(3)" class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                What languages are supported on the platform?
                            </h3>
                            <svg class="w-6 h-6 text-gray-500 transform transition-transform duration-300 group-hover:text-blue-600" 
                                 :class="{ 'rotate-180': openItem === 3 }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openItem === 3" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-4"
                             class="px-8 pb-6">
                            <div class="text-gray-700 leading-relaxed">
                                <p class="mb-4">YorYor Global supports multiple languages to serve our diverse global community:</p>
                                <ul class="list-disc list-inside space-y-2 ml-4">
                                    <li><strong>Uzbek:</strong> Full interface and content in Uzbek language</li>
                                    <li><strong>English:</strong> Complete English translation for international users</li>
                                    <li><strong>Russian:</strong> Russian language support for CIS region users</li>
                                    <li><strong>Turkish:</strong> Turkish language for Turkish-speaking Uzbeks</li>
                                    <li><strong>Arabic:</strong> Arabic language support for Middle Eastern users</li>
                                </ul>
                                <p class="mt-4">You can switch between languages anytime, and the platform will automatically translate messages between different languages. We're constantly adding more language support based on our community needs.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 5 -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200/40 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden mb-4">
                        <button @click="toggleItem(4)" class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                How much does YorYor Global cost?
                            </h3>
                            <svg class="w-6 h-6 text-gray-500 transform transition-transform duration-300 group-hover:text-blue-600" 
                                 :class="{ 'rotate-180': openItem === 4 }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openItem === 4" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-4"
                             class="px-8 pb-6">
                            <div class="text-gray-700 leading-relaxed">
                                <p class="mb-4">We offer flexible pricing options to suit different needs and budgets:</p>
                                <ul class="list-disc list-inside space-y-2 ml-4">
                                    <li><strong>Free Plan:</strong> Basic features including profile creation, limited matches, and basic search</li>
                                    <li><strong>Premium Plan:</strong> $29/month - Unlimited matches, advanced filters, priority support</li>
                                    <li><strong>Elite Plan:</strong> $59/month - Family verification, dedicated consultant, priority placement</li>
                                    <li><strong>Family Plan:</strong> $99/month - Up to 5 family profiles, group management, 24/7 support</li>
                                </ul>
                                <p class="mt-4">All plans support multiple currencies (USD, EUR, GBP, UZS) with automatic conversion. We offer a 30-day money-back guarantee on all paid plans. Currently, we're offering free access to all features for new users!</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 6 -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200/40 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden mb-4">
                        <button @click="toggleItem(5)" class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                Can I use YorYor if I'm not Uzbek but interested in Uzbek culture?
                            </h3>
                            <svg class="w-6 h-6 text-gray-500 transform transition-transform duration-300 group-hover:text-blue-600" 
                                 :class="{ 'rotate-180': openItem === 5 }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openItem === 5" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-4"
                             class="px-8 pb-6">
                            <div class="text-gray-700 leading-relaxed">
                                <p class="mb-4">Absolutely! YorYor Global welcomes people from all backgrounds who are interested in Uzbek culture and values:</p>
                                <ul class="list-disc list-inside space-y-2 ml-4">
                                    <li><strong>Cultural Interest:</strong> If you're genuinely interested in Uzbek culture and traditions</li>
                                    <li><strong>Mixed Heritage:</strong> Perfect for people with mixed Uzbek heritage or connections</li>
                                    <li><strong>Cultural Learning:</strong> Great for those wanting to learn about Uzbek traditions</li>
                                    <li><strong>Respectful Approach:</strong> We expect genuine respect for Uzbek culture and values</li>
                                    <li><strong>Community Guidelines:</strong> All users must follow our community guidelines and cultural sensitivity policies</li>
                                </ul>
                                <p class="mt-4">We believe love transcends cultural boundaries, but we also respect and celebrate Uzbek culture. Non-Uzbek users are encouraged to learn about and appreciate the traditions and values that make our community special.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional FAQ Items -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                        Additional Questions
                    </h2>

                    <!-- FAQ Item 7 -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200/40 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden mb-4">
                        <button @click="toggleItem(6)" class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                How do I create an account on YorYor?
                            </h3>
                            <svg class="w-6 h-6 text-gray-500 transform transition-transform duration-300 group-hover:text-blue-600" 
                                 :class="{ 'rotate-180': openItem === 6 }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openItem === 6" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-4"
                             class="px-8 pb-6">
                            <div class="text-gray-700 leading-relaxed">
                                <p>Creating an account is simple! Download the YorYor app from the App Store or Google Play, then sign up using your phone number or email address. You'll need to verify your account and complete your profile with photos, interests, and preferences to start matching.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 8 -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200/40 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden mb-4">
                        <button @click="toggleItem(7)" class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                What information do I need to provide?
                            </h3>
                            <svg class="w-6 h-6 text-gray-500 transform transition-transform duration-300 group-hover:text-blue-600" 
                                 :class="{ 'rotate-180': openItem === 7 }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openItem === 7" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-4"
                             class="px-8 pb-6">
                            <div class="text-gray-700 leading-relaxed">
                                <p>To create a complete profile, you'll provide basic information like name, age, location, photos, and interests. You can also add details about your cultural background, religious beliefs, education, profession, and what you're looking for in a partner. The more complete your profile, the better our matching algorithm works.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 9 -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200/40 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden mb-4">
                        <button @click="toggleItem(8)" class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                What makes YorYor different from other dating apps?
                            </h3>
                            <svg class="w-6 h-6 text-gray-500 transform transition-transform duration-300 group-hover:text-blue-600" 
                                 :class="{ 'rotate-180': openItem === 8 }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openItem === 8" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-4"
                             class="px-8 pb-6">
                            <div class="text-gray-700 leading-relaxed">
                                <p>YorYor focuses on cultural compatibility and family values, making it ideal for those seeking serious relationships. Our unique features include family approval options, professional matchmaker consultations, voice message profiles, and comprehensive cultural background matching that respects traditional values while embracing modern dating.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 10 -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200/40 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden mb-4">
                        <button @click="toggleItem(9)" class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                How do I start conversations with matches?
                            </h3>
                            <svg class="w-6 h-6 text-gray-500 transform transition-transform duration-300 group-hover:text-blue-600" 
                                 :class="{ 'rotate-180': openItem === 9 }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openItem === 9" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-4"
                             class="px-8 pb-6">
                            <div class="text-gray-700 leading-relaxed">
                                <p>Once you match with someone (both users like each other), you can start chatting immediately. YorYor offers text messages, voice messages, and photo sharing. We recommend starting with genuine questions about their interests or shared experiences mentioned in their profile.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 11 -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200/40 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden mb-4">
                        <button @click="toggleItem(10)" class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                What information does YorYor collect and how is it used?
                            </h3>
                            <svg class="w-6 h-6 text-gray-500 transform transition-transform duration-300 group-hover:text-blue-600" 
                                 :class="{ 'rotate-180': openItem === 10 }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openItem === 10" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-4"
                             class="px-8 pb-6">
                            <div class="text-gray-700 leading-relaxed">
                                <p>YorYor collects profile information, photos, location data, and usage patterns to provide matching services and improve user experience. We never sell personal data to third parties and use industry-standard encryption to protect your information. See our Privacy Policy for detailed information.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 12 -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200/40 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden mb-4">
                        <button @click="toggleItem(11)" class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                Can I report inappropriate behavior?
                            </h3>
                            <svg class="w-6 h-6 text-gray-500 transform transition-transform duration-300 group-hover:text-blue-600" 
                                 :class="{ 'rotate-180': openItem === 11 }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openItem === 11" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-4"
                             class="px-8 pb-6">
                            <div class="text-gray-700 leading-relaxed">
                                <p>Absolutely! YorYor has zero tolerance for inappropriate behavior. You can report users directly from their profile or chat, and our moderation team reviews all reports within 24 hours. We also provide blocking features and encourage users to report any concerning behavior immediately.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 13 -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200/40 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden mb-4">
                        <button @click="toggleItem(12)" class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors duration-200">
                                How does the family approval feature work?
                            </h3>
                            <svg class="w-6 h-6 text-gray-500 transform transition-transform duration-300 group-hover:text-blue-600" 
                                 :class="{ 'rotate-180': openItem === 12 }" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="openItem === 12" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-4"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-4"
                             class="px-8 pb-6">
                            <div class="text-gray-700 leading-relaxed">
                                <p>The family approval feature is optional and allows you to involve trusted family members in your dating journey. You can designate family members who can view your matches and provide input. This feature respects traditional family values while maintaining your autonomy in dating decisions.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Still Have Questions Section -->
    <section class="py-24 bg-gradient-to-br from-pink-50 to-purple-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                Still Have <span class="gradient-text">Questions?</span>
            </h2>
            <p class="text-xl text-gray-600 mb-12 leading-relaxed">
                Our support team is here to help. Reach out to us anytime for personalized assistance.
            </p>
            
            <!-- Contact Options -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Email Support</h3>
                    <p class="text-gray-600 mb-4">Get detailed help via email</p>
                    <a href="mailto:support@yoryor.com" class="text-pink-600 hover:text-pink-700 font-semibold">support@yoryor.com</a>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Live Chat</h3>
                    <p class="text-gray-600 mb-4">Instant help when you need it</p>
                    <button class="text-blue-600 hover:text-blue-700 font-semibold">Start Chat</button>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-lg card-hover">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Phone Support</h3>
                    <p class="text-gray-600 mb-4">Speak directly with our team</p>
                    <a href="tel:+1-555-YORYOR" class="text-green-600 hover:text-green-700 font-semibold">+1-555-YORYOR</a>
                </div>
            </div>

            <a href="{{ route('contact') }}" class="inline-flex items-center justify-center bg-gradient-to-r from-pink-500 to-purple-600 text-white px-8 py-4 rounded-2xl font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                Contact Us
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </section>

</x-layout.landing>