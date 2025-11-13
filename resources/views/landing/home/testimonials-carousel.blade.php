<!-- Interactive Testimonials Carousel -->
<section class="py-24 bg-gradient-to-br from-indigo-50 via-white to-purple-50 relative overflow-hidden">
    <!-- Background decorative elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 right-1/4 w-72 h-72 bg-gradient-to-br from-indigo-200/20 to-purple-200/20 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute bottom-20 left-1/4 w-80 h-80 bg-gradient-to-br from-purple-200/20 to-pink-200/20 rounded-full blur-3xl animate-float-reverse"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <div class="inline-flex items-center bg-gradient-to-r from-indigo-100/80 via-purple-50/80 to-pink-100/80 backdrop-blur-sm border border-indigo-200/60 rounded-full px-8 py-4 mb-8 shadow-lg shadow-indigo-500/10 animate-fade-in group hover:shadow-indigo-500/20 transition-all duration-300">
                <span class="text-2xl mr-3 animate-bounce">💬</span>
                <span class="text-sm font-semibold bg-gradient-to-r from-indigo-700 to-purple-600 bg-clip-text text-transparent">Real Success Stories</span>
                <!-- Animated sparkle effect -->
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-indigo-400 rounded-full animate-ping opacity-75"></div>
            </div>
            <h2 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-6 animate-slide-up">
                Love Stories from <span class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent animate-pulse">Around the World</span>
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto animate-fade-in" style="animation-delay: 0.3s;">
                Hear from real couples who found their perfect match through our global platform. Their stories inspire hope and prove that love knows no borders.
            </p>
        </div>

        <!-- Testimonials Carousel -->
        <div class="relative" x-data="{
            currentSlide: 0,
            testimonials: [
                {
                    id: 1,
                    name: 'Amina & David',
                    location: 'Tashkent, Uzbekistan → London, UK',
                    image: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&h=150&fit=crop&crop=face',
                    rating: 5,
                    text: 'We met through YorYor when I was studying in London and David was working there. Despite being from different countries, our shared Uzbek heritage brought us together. We got married last year and now live in Tashkent!',
                    verified: true,
                    date: 'Married 2023'
                },
                {
                    id: 2,
                    name: 'Malika & Ahmed',
                    location: 'New York, USA → Dubai, UAE',
                    image: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face',
                    rating: 5,
                    text: 'The cultural compatibility matching was incredible. We both wanted someone who understood our traditions while being open to new experiences. YorYor helped us find each other across continents.',
                    verified: true,
                    date: 'Engaged 2024'
                },
                {
                    id: 3,
                    name: 'Zarina & Michael',
                    location: 'Toronto, Canada → Berlin, Germany',
                    image: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face',
                    rating: 5,
                    text: 'As a mixed-heritage couple, we appreciated how YorYor celebrated both our cultures. The platform helped us connect on a deeper level, understanding each other\'s backgrounds and values.',
                    verified: true,
                    date: 'Married 2023'
                },
                {
                    id: 4,
                    name: 'Dilnoza & James',
                    location: 'Seoul, South Korea → Melbourne, Australia',
                    image: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face',
                    rating: 5,
                    text: 'The family verification feature gave us confidence in each other. Our families were able to connect through the platform before we even met in person. It made the whole process so much more trustworthy.',
                    verified: true,
                    date: 'Married 2024'
                },
                {
                    id: 5,
                    name: 'Nigora & Alex',
                    location: 'Moscow, Russia → Los Angeles, USA',
                    image: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&h=150&fit=crop&crop=face',
                    rating: 5,
                    text: 'The international features were perfect for us. We could communicate in both English and Uzbek, and the platform helped us navigate the cultural differences while celebrating our similarities.',
                    verified: true,
                    date: 'Engaged 2024'
                },
                {
                    id: 6,
                    name: 'Shahnoza & Daniel',
                    location: 'Istanbul, Turkey → Paris, France',
                    image: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&h=150&fit=crop&crop=face',
                    rating: 5,
                    text: 'YorYor made it easy to find someone who shared my values and cultural background, even living in different countries. The matching algorithm really understood what we were looking for.',
                    verified: true,
                    date: 'Married 2023'
                }
            ],
            nextSlide() {
                this.currentSlide = (this.currentSlide + 1) % this.testimonials.length;
            },
            prevSlide() {
                this.currentSlide = this.currentSlide === 0 ? this.testimonials.length - 1 : this.currentSlide - 1;
            },
            goToSlide(index) {
                this.currentSlide = index;
            }
        }">
            <!-- Main Carousel Container -->
            <div class="relative overflow-hidden rounded-3xl">
                <!-- Testimonial Cards -->
                <div class="flex transition-transform duration-500 ease-in-out" 
                     :style="`transform: translateX(-${currentSlide * 100}%)`">
                    <template x-for="(testimonial, index) in testimonials" :key="testimonial.id">
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-white/95 backdrop-blur-xl rounded-2xl p-8 shadow-2xl border border-gray-200/40 hover:shadow-3xl transition-all duration-500 transform hover:scale-105">
                                <!-- Testimonial Header -->
                                <div class="flex items-center mb-6">
                                    <div class="relative">
                                        <img :src="testimonial.image" 
                                             :alt="testimonial.name"
                                             class="w-16 h-16 rounded-full object-cover border-4 border-white shadow-lg">
                                        <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center">
                                            <h3 class="text-xl font-bold text-gray-900" x-text="testimonial.name"></h3>
                                            <span class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-semibold" x-show="testimonial.verified">Verified</span>
                                        </div>
                                        <p class="text-sm text-gray-600" x-text="testimonial.location"></p>
                                        <div class="flex items-center mt-1">
                                            <div class="flex text-yellow-400">
                                                <template x-for="i in testimonial.rating" :key="i">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                </template>
                                            </div>
                                            <span class="ml-2 text-sm text-gray-500" x-text="testimonial.date"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Testimonial Content -->
                                <blockquote class="text-lg text-gray-700 leading-relaxed mb-6 italic" x-text="testimonial.text"></blockquote>

                                <!-- Quote Icon -->
                                <div class="flex justify-end">
                                    <svg class="w-8 h-8 text-indigo-200" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Navigation Arrows -->
                <button @click="prevSlide()" 
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/90 backdrop-blur-sm hover:bg-white text-gray-600 hover:text-indigo-600 w-12 h-12 rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-110 z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button @click="nextSlide()" 
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/90 backdrop-blur-sm hover:bg-white text-gray-600 hover:text-indigo-600 w-12 h-12 rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-110 z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            <!-- Dots Navigation -->
            <div class="flex justify-center mt-8 space-x-2">
                <template x-for="(testimonial, index) in testimonials" :key="index">
                    <button @click="goToSlide(index)"
                            class="w-3 h-3 rounded-full transition-all duration-300"
                            :class="currentSlide === index ? 'bg-indigo-600 scale-125' : 'bg-gray-300 hover:bg-gray-400'">
                    </button>
                </template>
            </div>

            <!-- Auto-play indicator -->
            <div class="text-center mt-6">
                <div class="inline-flex items-center text-sm text-gray-500">
                    <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse mr-2"></div>
                    Auto-playing testimonials
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center mt-16">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl p-8 text-white">
                <h3 class="text-2xl font-bold mb-4">Ready to Write Your Own Success Story?</h3>
                <p class="text-indigo-100 mb-6">Join thousands of Uzbeks worldwide who have found their perfect match</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button class="bg-white text-indigo-600 px-8 py-3 rounded-xl font-semibold hover:bg-indigo-50 transition-all duration-300 transform hover:scale-105">
                        Start Your Journey
                    </button>
                    <button class="border-2 border-white text-white px-8 py-3 rounded-xl font-semibold hover:bg-white hover:text-indigo-600 transition-all duration-300 transform hover:scale-105">
                        Read More Stories
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-play script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const carousel = document.querySelector('[x-data*="currentSlide"]');
            if (carousel) {
                setInterval(() => {
                    const nextButton = carousel.querySelector('button[\\@click="nextSlide()"]');
                    if (nextButton) {
                        nextButton.click();
                    }
                }, 5000); // Auto-advance every 5 seconds
            }
        });
    </script>
</section>
