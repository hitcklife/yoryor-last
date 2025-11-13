<x-layout.landing>
    <x-slot:title>Contact YorYor Support Team</x-slot:title>
    <x-slot:description>Get in touch with YorYor's support team. We're here to help with questions about the app, technical issues, partnerships, and more.</x-slot:description>

    <!-- Hero Section -->
    <section class="hero-gradient py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl lg:text-7xl font-bold text-gray-900 mb-6">
                Get in <span class="gradient-text">Touch</span>
            </h1>
            <p class="text-xl text-gray-600 leading-relaxed">
                Have a question, suggestion, or need support? We'd love to hear from you and help make your YorYor experience amazing.
            </p>
        </div>
    </section>

    <!-- Contact Methods -->
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
                <!-- Contact Information -->
                <div>
                    <h2 class="text-4xl font-bold text-gray-900 mb-8">
                        Let's <span class="gradient-text">Connect</span>
                    </h2>
                    <p class="text-lg text-gray-600 mb-12 leading-relaxed">
                        Our dedicated support team is ready to assist you with any questions or concerns. Choose the contact method that works best for you.
                    </p>

                    <!-- Contact Cards -->
                    <div class="space-y-6">
                        <!-- Email Support -->
                        <div class="bg-gradient-to-br from-pink-50 to-purple-50 rounded-2xl p-6 card-hover border border-pink-100">
                            <div class="flex items-start">
                                <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl flex items-center justify-center mr-6 flex-shrink-0">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Email Support</h3>
                                    <p class="text-gray-600 mb-4">For detailed inquiries and support requests</p>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <span class="text-sm text-gray-500 w-20">General:</span>
                                            <a href="mailto:support@yoryor.com" class="text-pink-600 hover:text-pink-700 font-semibold">support@yoryor.com</a>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="text-sm text-gray-500 w-20">Business:</span>
                                            <a href="mailto:business@yoryor.com" class="text-pink-600 hover:text-pink-700 font-semibold">business@yoryor.com</a>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="text-sm text-gray-500 w-20">Press:</span>
                                            <a href="mailto:press@yoryor.com" class="text-pink-600 hover:text-pink-700 font-semibold">press@yoryor.com</a>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-3">Response time: 24-48 hours</p>
                                </div>
                            </div>
                        </div>

                        <!-- Phone Support -->
                        <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-6 card-hover border border-blue-100">
                            <div class="flex items-start">
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mr-6 flex-shrink-0">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Phone Support</h3>
                                    <p class="text-gray-600 mb-4">Speak directly with our support team</p>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <span class="text-sm text-gray-500 w-20">US/CA:</span>
                                            <a href="tel:+1-555-YORYOR" class="text-blue-600 hover:text-blue-700 font-semibold">+1 (555) YOR-YOR</a>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="text-sm text-gray-500 w-20">Int'l:</span>
                                            <a href="tel:+1-555-967-9671" class="text-blue-600 hover:text-blue-700 font-semibold">+1 (555) 967-9671</a>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-3">Hours: Mon-Fri 9AM-6PM EST</p>
                                </div>
                            </div>
                        </div>

                        <!-- Live Chat -->
                        <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-2xl p-6 card-hover border border-green-100">
                            <div class="flex items-start">
                                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-blue-600 rounded-2xl flex items-center justify-center mr-6 flex-shrink-0">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Live Chat</h3>
                                    <p class="text-gray-600 mb-4">Get instant help through our chat system</p>
                                    <button class="bg-gradient-to-r from-green-500 to-blue-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                                        Start Chat Now
                                    </button>
                                    <p class="text-sm text-gray-500 mt-3">Available: 24/7 with AI, Live agents 9AM-9PM EST</p>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-6 card-hover border border-purple-100">
                            <div class="flex items-start">
                                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mr-6 flex-shrink-0">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Social Media</h3>
                                    <p class="text-gray-600 mb-4">Follow us for updates and community support</p>
                                    <div class="flex space-x-4">
                                        <a href="#" class="text-gray-600 hover:text-purple-600 transition-colors">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                            </svg>
                                        </a>
                                        <a href="#" class="text-gray-600 hover:text-purple-600 transition-colors">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                            </svg>
                                        </a>
                                        <a href="#" class="text-gray-600 hover:text-purple-600 transition-colors">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                                            </svg>
                                        </a>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-3">@YorYorApp on all platforms</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div>
                    <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
                        <h3 class="text-3xl font-bold text-gray-900 mb-2">Send us a Message</h3>
                        <p class="text-gray-600 mb-8">Fill out the form below and we'll get back to you as soon as possible.</p>

                        <form class="space-y-6" action="#" method="POST">
                            @csrf
                            
                            <!-- Name and Email -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                                    <input type="text" id="name" name="name" required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors"
                                           placeholder="Your full name">
                                </div>
                                
                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address *</label>
                                    <input type="email" id="email" name="email" required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors"
                                           placeholder="your@email.com">
                                </div>
                            </div>

                            <!-- Subject -->
                            <div>
                                <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">Subject *</label>
                                <select id="subject" name="subject" required 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors">
                                    <option value="">Select a topic...</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="support">Technical Support</option>
                                    <option value="account">Account Issues</option>
                                    <option value="billing">Billing Questions</option>
                                    <option value="partnership">Partnership Opportunities</option>
                                    <option value="press">Press & Media</option>
                                    <option value="feedback">Feedback & Suggestions</option>
                                    <option value="report">Report an Issue</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <!-- Phone (Optional) -->
                            <div>
                                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" id="phone" name="phone" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors"
                                       placeholder="+1 (555) 123-4567">
                            </div>

                            <!-- Message -->
                            <div>
                                <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">Message *</label>
                                <textarea id="message" name="message" rows="6" required 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors resize-none"
                                          placeholder="Please describe your inquiry in detail..."></textarea>
                            </div>

                            <!-- Priority -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Priority Level</label>
                                <div class="flex space-x-4">
                                    <label class="flex items-center">
                                        <input type="radio" name="priority" value="low" class="w-4 h-4 text-pink-600 border-gray-300 focus:ring-pink-500">
                                        <span class="ml-2 text-sm text-gray-600">Low</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="priority" value="normal" checked class="w-4 h-4 text-pink-600 border-gray-300 focus:ring-pink-500">
                                        <span class="ml-2 text-sm text-gray-600">Normal</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="priority" value="high" class="w-4 h-4 text-pink-600 border-gray-300 focus:ring-pink-500">
                                        <span class="ml-2 text-sm text-gray-600">High</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="priority" value="urgent" class="w-4 h-4 text-pink-600 border-gray-300 focus:ring-pink-500">
                                        <span class="ml-2 text-sm text-gray-600">Urgent</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Newsletter -->
                            <div class="flex items-start">
                                <input type="checkbox" id="newsletter" name="newsletter" 
                                       class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500 mt-1">
                                <label for="newsletter" class="ml-3 text-sm text-gray-600">
                                    I'd like to receive updates about YorYor features and news
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-pink-500 to-purple-600 text-white px-8 py-4 rounded-2xl font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Send Message
                            </button>
                        </form>

                        <!-- Response Time Notice -->
                        <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-200">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-semibold text-blue-900">Expected Response Times</h4>
                                    <p class="text-sm text-blue-700 mt-1">
                                        General inquiries: 24-48 hours • Technical support: 12-24 hours • Urgent issues: 2-4 hours
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Office Information -->
    <section class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                    Visit Our <span class="gradient-text">Office</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    We'd love to meet you in person! Drop by our office or schedule a meeting with our team.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <!-- Office Details -->
                <div>
                    <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">YorYor Headquarters</h3>
                        
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-purple-600 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Address</h4>
                                    <p class="text-gray-600">
                                        123 Love Street, Suite 456<br>
                                        San Francisco, CA 94102<br>
                                        United States
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Office Hours</h4>
                                    <div class="text-gray-600 space-y-1">
                                        <p>Monday - Friday: 9:00 AM - 6:00 PM PST</p>
                                        <p>Saturday: 10:00 AM - 4:00 PM PST</p>
                                        <p>Sunday: Closed</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-blue-600 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Appointments</h4>
                                    <p class="text-gray-600 mb-3">
                                        Schedule a meeting with our team for partnerships, press inquiries, or detailed consultations.
                                    </p>
                                    <button class="bg-gradient-to-r from-green-500 to-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:shadow-lg transition-all duration-300">
                                        Schedule Meeting
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Placeholder -->
                <div class="relative">
                    <div class="bg-gray-200 rounded-3xl h-96 flex items-center justify-center">
                        <div class="text-center">
                            <div class="w-24 h-24 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-700 mb-2">Interactive Map</h3>
                            <p class="text-gray-600 mb-4">Coming soon - Find us easily</p>
                            <button class="text-pink-600 hover:text-pink-700 font-semibold">
                                Get Directions
                            </button>
                        </div>
                    </div>
                    
                    <!-- Floating Elements -->
                    <div class="absolute -top-4 -right-4 w-8 h-8 bg-pink-400 rounded-full floating-heart opacity-80"></div>
                    <div class="absolute top-20 -left-6 w-6 h-6 bg-purple-400 rounded-full floating-heart opacity-60" style="animation-delay: 1s;"></div>
                    <div class="absolute -bottom-6 right-10 w-10 h-10 bg-yellow-400 rounded-full floating-heart opacity-70" style="animation-delay: 2s;"></div>
                </div>
            </div>
        </div>
    </section>

</x-layout.landing>