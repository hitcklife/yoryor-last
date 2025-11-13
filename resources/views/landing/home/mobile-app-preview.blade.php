<!-- Mobile App Preview Section -->
<section class="py-24 bg-gradient-to-br from-slate-50 via-white to-gray-50 relative overflow-hidden">
    <!-- Background decorative elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-1/4 w-96 h-96 bg-gradient-to-br from-blue-500/10 to-purple-500/10 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute bottom-20 right-1/4 w-80 h-80 bg-gradient-to-br from-purple-500/10 to-pink-500/10 rounded-full blur-3xl animate-float-reverse"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <div class="inline-flex items-center bg-gradient-to-r from-blue-500/20 via-purple-500/20 to-pink-500/20 backdrop-blur-sm border border-blue-500/30 rounded-full px-8 py-4 mb-8 shadow-lg shadow-blue-500/20 animate-fade-in group hover:shadow-blue-500/30 transition-all duration-300">
                <span class="text-2xl mr-3 animate-bounce">📱</span>
                <span class="text-sm font-semibold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">Mobile Experience</span>
                <!-- Animated sparkle effect -->
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-blue-400 rounded-full animate-ping opacity-75"></div>
            </div>
            <h2 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-6 animate-slide-up">
                Your Love Story in <span class="bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent animate-pulse">Your Pocket</span>
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto animate-fade-in" style="animation-delay: 0.3s;">
                Experience YorYor on mobile with our beautifully designed app. Swipe, chat, and connect with Uzbeks worldwide, all from the palm of your hand.
            </p>
        </div>

        <!-- Mobile App Preview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center" x-data="{
            activeScreen: 0,
            screens: [
                {
                    id: 'home',
                    title: 'Discover Matches',
                    description: 'Swipe through profiles of Uzbeks worldwide with our intelligent matching algorithm',
                    features: ['Smart matching', 'Location filters', 'Cultural compatibility', 'Verified profiles']
                },
                {
                    id: 'chat',
                    title: 'Real-time Chat',
                    description: 'Connect instantly with your matches through our secure messaging platform',
                    features: ['Instant messaging', 'Voice messages', 'Video calls', 'Media sharing']
                },
                {
                    id: 'profile',
                    title: 'Your Profile',
                    description: 'Showcase your personality with photos, interests, and cultural background',
                    features: ['Photo gallery', 'Cultural info', 'Family details', 'Verification badges']
                },
                {
                    id: 'family',
                    title: 'Family Features',
                    description: 'Involve your family in the process with our family-friendly features',
                    features: ['Family approval', 'Group chats', 'Cultural matching', 'Safety features']
                }
            ],
            setActiveScreen(index) {
                this.activeScreen = index;
            }
        }">
            <!-- Phone Mockups -->
            <div class="relative">
                <!-- Main Phone -->
                <div class="relative mx-auto w-80 h-[600px] bg-gray-800 rounded-[3rem] p-2 shadow-2xl">
                    <div class="w-full h-full bg-gray-900 rounded-[2.5rem] overflow-hidden relative">
                        <!-- Status Bar -->
                        <div class="flex justify-between items-center px-6 py-3 text-white text-sm">
                            <span class="font-semibold">9:41</span>
                            <div class="flex items-center space-x-1">
                                <div class="w-4 h-2 bg-white rounded-sm"></div>
                                <div class="w-4 h-2 bg-white rounded-sm"></div>
                                <div class="w-4 h-2 bg-white rounded-sm"></div>
                                <div class="w-4 h-2 bg-white rounded-sm"></div>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-6 h-3 border border-white rounded-sm"></div>
                                <div class="w-6 h-3 bg-white rounded-sm"></div>
                            </div>
                        </div>

                        <!-- Screen Content -->
                        <div class="px-6 py-4 h-full overflow-hidden">
                            <!-- Home Screen -->
                            <div x-show="activeScreen === 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                                <div class="text-center mb-6">
                                    <h3 class="text-white text-xl font-bold mb-2">Discover</h3>
                                    <p class="text-gray-400 text-sm">Find your perfect match</p>
                                </div>
                                
                                <!-- Profile Card -->
                                <div class="bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl p-6 mb-4 relative overflow-hidden">
                                    <div class="absolute top-0 right-0 w-20 h-20 bg-white/20 rounded-full -translate-y-10 translate-x-10"></div>
                                    <div class="relative z-10">
                                        <div class="flex items-center mb-4">
                                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                                <span class="text-white text-xl">👤</span>
                                            </div>
                                            <div class="ml-3">
                                                <h4 class="text-white font-semibold">Amina, 25</h4>
                                                <p class="text-white/80 text-sm">Tashkent → London</p>
                                            </div>
                                        </div>
                                        <p class="text-white/90 text-sm mb-4">Looking for someone who shares my values and cultural background...</p>
                                        <div class="flex space-x-2">
                                            <button class="bg-white/20 text-white px-4 py-2 rounded-full text-sm">❤️ Like</button>
                                            <button class="bg-white/20 text-white px-4 py-2 rounded-full text-sm">💬 Chat</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bottom Navigation -->
                                <div class="absolute bottom-0 left-0 right-0 bg-gray-800 rounded-b-2xl p-4">
                                    <div class="flex justify-around">
                                        <div class="text-center">
                                            <div class="w-8 h-8 bg-blue-500 rounded-lg mx-auto mb-1"></div>
                                            <span class="text-blue-400 text-xs">Home</span>
                                        </div>
                                        <div class="text-center">
                                            <div class="w-8 h-8 bg-gray-600 rounded-lg mx-auto mb-1"></div>
                                            <span class="text-gray-400 text-xs">Chat</span>
                                        </div>
                                        <div class="text-center">
                                            <div class="w-8 h-8 bg-gray-600 rounded-lg mx-auto mb-1"></div>
                                            <span class="text-gray-400 text-xs">Profile</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chat Screen -->
                            <div x-show="activeScreen === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                                <div class="flex items-center mb-6">
                                    <button class="text-white mr-4">←</button>
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm">A</span>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-white font-semibold">Amina</h3>
                                            <p class="text-green-400 text-xs">Online now</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4 mb-4">
                                    <div class="flex justify-start">
                                        <div class="bg-gray-700 text-white p-3 rounded-2xl rounded-bl-md max-w-xs">
                                            <p class="text-sm">Salam! How are you doing today?</p>
                                            <span class="text-xs text-gray-400 mt-1 block">2:30 PM</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <div class="bg-blue-500 text-white p-3 rounded-2xl rounded-br-md max-w-xs">
                                            <p class="text-sm">Salam! I'm doing great, thank you! How about you?</p>
                                            <span class="text-xs text-blue-200 mt-1 block">2:32 PM</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-start">
                                        <div class="bg-gray-700 text-white p-3 rounded-2xl rounded-bl-md max-w-xs">
                                            <p class="text-sm">I'm wonderful! I love your profile photos 😊</p>
                                            <span class="text-xs text-gray-400 mt-1 block">2:33 PM</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="absolute bottom-0 left-0 right-0 bg-gray-800 p-4">
                                    <div class="flex items-center space-x-2">
                                        <input type="text" placeholder="Type a message..." class="flex-1 bg-gray-700 text-white px-4 py-2 rounded-full text-sm">
                                        <button class="bg-blue-500 text-white p-2 rounded-full">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Profile Screen -->
                            <div x-show="activeScreen === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                                <div class="text-center mb-6">
                                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                                        <span class="text-white text-2xl">👤</span>
                                    </div>
                                    <h3 class="text-white text-xl font-bold">Your Profile</h3>
                                    <p class="text-gray-400 text-sm">Complete your profile to get better matches</p>
                                </div>

                                <div class="space-y-4">
                                    <div class="bg-gray-800 rounded-xl p-4">
                                        <h4 class="text-white font-semibold mb-2">Personal Info</h4>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-gray-400 text-sm">Name:</span>
                                                <span class="text-white text-sm">David</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-400 text-sm">Age:</span>
                                                <span class="text-white text-sm">28</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-400 text-sm">Location:</span>
                                                <span class="text-white text-sm">London, UK</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gray-800 rounded-xl p-4">
                                        <h4 class="text-white font-semibold mb-2">Cultural Background</h4>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="bg-blue-500/20 text-blue-400 px-3 py-1 rounded-full text-xs">Uzbek Heritage</span>
                                            <span class="bg-purple-500/20 text-purple-400 px-3 py-1 rounded-full text-xs">Muslim</span>
                                            <span class="bg-green-500/20 text-green-400 px-3 py-1 rounded-full text-xs">Traditional Values</span>
                                        </div>
                                    </div>

                                    <button class="w-full bg-blue-500 text-white py-3 rounded-xl font-semibold">
                                        Edit Profile
                                    </button>
                                </div>
                            </div>

                            <!-- Family Screen -->
                            <div x-show="activeScreen === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                                <div class="text-center mb-6">
                                    <h3 class="text-white text-xl font-bold mb-2">Family Features</h3>
                                    <p class="text-gray-400 text-sm">Involve your family in the process</p>
                                </div>

                                <div class="space-y-4">
                                    <div class="bg-gradient-to-r from-green-500/20 to-blue-500/20 rounded-xl p-4 border border-green-500/30">
                                        <div class="flex items-center mb-2">
                                            <span class="text-green-400 text-lg mr-2">👨‍👩‍👧‍👦</span>
                                            <h4 class="text-white font-semibold">Family Approval</h4>
                                        </div>
                                        <p class="text-gray-300 text-sm">Your family can review and approve potential matches</p>
                                    </div>

                                    <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-xl p-4 border border-purple-500/30">
                                        <div class="flex items-center mb-2">
                                            <span class="text-purple-400 text-lg mr-2">💬</span>
                                            <h4 class="text-white font-semibold">Group Chats</h4>
                                        </div>
                                        <p class="text-gray-300 text-sm">Connect families through group conversations</p>
                                    </div>

                                    <div class="bg-gradient-to-r from-yellow-500/20 to-orange-500/20 rounded-xl p-4 border border-yellow-500/30">
                                        <div class="flex items-center mb-2">
                                            <span class="text-yellow-400 text-lg mr-2">🛡️</span>
                                            <h4 class="text-white font-semibold">Safety Features</h4>
                                        </div>
                                        <p class="text-gray-300 text-sm">Advanced safety and verification systems</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floating Elements -->
                <div class="absolute -top-4 -right-4 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center animate-bounce">
                    <span class="text-white text-sm">✓</span>
                </div>
                <div class="absolute -bottom-4 -left-4 w-6 h-6 bg-blue-500 rounded-full animate-pulse"></div>
            </div>

            <!-- Feature Description -->
            <div class="space-y-8">
                <div class="text-center lg:text-left">
                    <h3 class="text-3xl font-bold text-gray-900 mb-4" x-text="screens[activeScreen].title"></h3>
                    <p class="text-xl text-gray-600 mb-6" x-text="screens[activeScreen].description"></p>
                </div>

                <!-- Feature List -->
                <div class="space-y-4">
                    <template x-for="feature in screens[activeScreen].features" :key="feature">
                        <div class="flex items-center">
                            <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-gray-700 text-lg" x-text="feature"></span>
                        </div>
                    </template>
                </div>

                <!-- Screen Navigation -->
                <div class="flex space-x-2">
                    <template x-for="(screen, index) in screens" :key="screen.id">
                        <button @click="setActiveScreen(index)"
                                class="w-3 h-3 rounded-full transition-all duration-300"
                                :class="activeScreen === index ? 'bg-gray-900 scale-125' : 'bg-gray-300 hover:bg-gray-400'">
                        </button>
                    </template>
                </div>

                <!-- App Store Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <button class="flex items-center justify-center bg-gray-900 border border-gray-300 text-white px-6 py-3 rounded-xl hover:bg-gray-800 transition-all duration-300 transform hover:scale-105">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 mr-3" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                            </svg>
                            <div class="text-left">
                                <div class="text-xs">Download on the</div>
                                <div class="text-sm font-semibold">App Store</div>
                            </div>
                        </div>
                    </button>
                    <button class="flex items-center justify-center bg-gray-900 border border-gray-300 text-white px-6 py-3 rounded-xl hover:bg-gray-800 transition-all duration-300 transform hover:scale-105">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 mr-3" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3.609 1.814L13.792 12 3.609 22.186a.996.996 0 01-.61-.92V2.734a1 1 0 01.609-.92zm10.89 10.893l2.302 2.302-10.937 6.333 8.635-8.635zm3.199-3.198l2.807 1.626a1 1 0 010 1.73l-2.808 1.626L12.864 12l4.834-2.491zM5.864 12l4.834 2.491L9.891 16.117 3.609 12l6.282-4.117L10.698 9.509 5.864 12z"/>
                            </svg>
                            <div class="text-left">
                                <div class="text-xs">GET IT ON</div>
                                <div class="text-sm font-semibold">Google Play</div>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
