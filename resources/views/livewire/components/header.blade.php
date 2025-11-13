<nav class="fixed top-0 w-full bg-white/90 backdrop-blur-xl border-b border-gray-200/30 z-50 shadow-lg transition-all duration-500">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <div class="flex items-center space-x-3 group cursor-pointer">
                <div class="relative w-12 h-12 bg-gradient-to-br from-pink-500 via-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center transform transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 group-hover:shadow-2xl shadow-pink overflow-hidden">
                    <!-- Animated background gradient -->
                    <div class="absolute inset-0 bg-gradient-to-br from-pink-400 via-purple-400 to-indigo-500 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 animate-pulse"></div>
                    <!-- Heart icon with glow effect -->
                    <i data-lucide="heart" class="w-7 h-7 text-white transition-all duration-500 group-hover:scale-110 relative z-10 drop-shadow-lg"></i>
                    <!-- Subtle sparkle effect -->
                    <div class="absolute top-1 right-1 w-2 h-2 bg-white/80 rounded-full animate-ping"></div>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-pink-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent transition-all duration-500 group-hover:scale-105 group-hover:from-pink-500 group-hover:via-purple-500 group-hover:to-indigo-500">YorYor</h1>
                    <span class="text-xs text-gray-500 font-medium opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-1 group-hover:translate-y-0">Find Your Perfect Match</span>
                </div>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden lg:flex items-center">
                <!-- Navigation Links -->
                <div class="flex items-center space-x-2 mr-8">
                    <a href="{{ route('home') }}" class="relative text-gray-500 hover:text-pink-600 font-medium transition-all duration-300 px-4 py-2.5 rounded-xl hover:bg-gradient-to-r hover:from-pink-50 hover:to-purple-50 hover:shadow-lg hover:shadow-pink/20 transform hover:scale-105 {{ request()->routeIs('home') ? 'text-pink-600 bg-gradient-to-r from-pink-50 to-purple-50 shadow-lg shadow-pink/20' : '' }} group">
                        <span class="relative z-10">{{ __('messages.home') }}</span>
                        @if(request()->routeIs('home'))
                            <div class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-1 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full animate-pulse"></div>
                        @else
                            <div class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full group-hover:w-2 transition-all duration-300"></div>
                        @endif
                    </a>
                    <a href="{{ route('features') }}" class="relative text-gray-500 hover:text-purple-600 font-medium transition-all duration-300 px-4 py-2.5 rounded-xl hover:bg-gradient-to-r hover:from-purple-50 hover:to-indigo-50 hover:shadow-lg hover:shadow-purple/20 transform hover:scale-105 {{ request()->routeIs('features') ? 'text-purple-600 bg-gradient-to-r from-purple-50 to-indigo-50 shadow-lg shadow-purple/20' : '' }} group">
                        <span class="relative z-10">{{ __('messages.features') }}</span>
                        @if(request()->routeIs('features'))
                            <div class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-1 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full animate-pulse"></div>
                        @else
                            <div class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full group-hover:w-2 transition-all duration-300"></div>
                        @endif
                    </a>
                    <a href="{{ route('about') }}" class="relative text-gray-500 hover:text-indigo-600 font-medium transition-all duration-300 px-4 py-2.5 rounded-xl hover:bg-gradient-to-r hover:from-indigo-50 hover:to-blue-50 hover:shadow-lg hover:shadow-indigo/20 transform hover:scale-105 {{ request()->routeIs('about') ? 'text-indigo-600 bg-gradient-to-r from-indigo-50 to-blue-50 shadow-lg shadow-indigo/20' : '' }} group">
                        <span class="relative z-10">{{ __('messages.about') }}</span>
                        @if(request()->routeIs('about'))
                            <div class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-1 bg-gradient-to-r from-indigo-500 to-blue-600 rounded-full animate-pulse"></div>
                        @else
                            <div class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-600 rounded-full group-hover:w-2 transition-all duration-300"></div>
                        @endif
                    </a>
                    <a href="{{ route('faq') }}" class="relative text-gray-500 hover:text-blue-600 font-medium transition-all duration-300 px-4 py-2.5 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 hover:shadow-lg hover:shadow-blue/20 transform hover:scale-105 {{ request()->routeIs('faq') ? 'text-blue-600 bg-gradient-to-r from-blue-50 to-cyan-50 shadow-lg shadow-blue/20' : '' }} group">
                        <span class="relative z-10">{{ __('messages.faq') }}</span>
                        @if(request()->routeIs('faq'))
                            <div class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-1 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-full animate-pulse"></div>
                        @else
                            <div class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-full group-hover:w-2 transition-all duration-300"></div>
                        @endif
                    </a>
                    <a href="{{ route('contact') }}" class="relative text-gray-500 hover:text-cyan-600 font-medium transition-all duration-300 px-4 py-2.5 rounded-xl hover:bg-gradient-to-r hover:from-cyan-50 hover:to-teal-50 hover:shadow-lg hover:shadow-cyan/20 transform hover:scale-105 {{ request()->routeIs('contact') ? 'text-cyan-600 bg-gradient-to-r from-cyan-50 to-teal-50 shadow-lg shadow-cyan/20' : '' }} group">
                        <span class="relative z-10">{{ __('messages.contact') }}</span>
                        @if(request()->routeIs('contact'))
                            <div class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-1 bg-gradient-to-r from-cyan-500 to-teal-600 rounded-full animate-pulse"></div>
                        @else
                            <div class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-cyan-500 to-teal-600 rounded-full group-hover:w-2 transition-all duration-300"></div>
                        @endif
                    </a>
                </div>

                <!-- Theme Switcher, Language Switcher & CTA Buttons -->
                <div class="flex items-center space-x-4">
                    <!-- Theme Switcher -->
                    <div class="relative">
                        @livewire('theme-switcher')
                    </div>

                    <!-- Language Switcher -->
                    <div class="relative" x-data="{ open: @entangle('languageDropdownOpen') }" @click.away="open = false">
                        <button
                            @click="open = !open"
                            class="flex items-center text-gray-500 hover:text-purple-600 font-medium transition-all duration-300 px-4 py-2.5 rounded-xl hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 hover:shadow-lg hover:shadow-purple/20 transform hover:scale-105 group">
                            <span class="flex items-center space-x-2">
                                <span class="text-lg">{{ $currentLanguage['flag'] }}</span>
                                <span class="text-sm font-medium">{{ $currentLanguage['code'] }}</span>
                            </span>
                            <i data-lucide="chevron-down" class="w-4 h-4 ml-2 transform transition-transform duration-300 group-hover:scale-110" :class="{ 'rotate-180': open }"></i>
                        </button>

                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
                            x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="transform opacity-0 scale-95 translate-y-2"
                            class="absolute right-0 mt-3 w-52 bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/30 py-2 z-[60] overflow-hidden"
                            x-cloak>
                            @foreach($languages as $locale => $language)
                                @if($locale !== $currentLocale)
                                    <button
                                        wire:click="switchLanguage('{{ $locale }}')"
                                        @click="open = false"
                                        class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 hover:text-purple-700 transition-all duration-200 group">
                                        <span class="text-lg mr-3">{{ $language['flag'] }}</span>
                                        <span class="flex-1 text-left font-medium">{{ $language['name'] }}</span>
                                        <span class="text-xs text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">{{ $language['code'] }}</span>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    @auth
                        @if(auth()->user()->hasRole('admin'))
                            <a href="{{ route('login') }}" class="text-gray-500 hover:text-blue-600 font-medium px-4 py-2 rounded-lg transition-all duration-200 hover:bg-blue-50">Admin</a>
                        @endif
                    @endauth
                    <a href="{{ route('start') }}" class="relative bg-gradient-to-r from-pink-500 via-purple-600 to-indigo-600 hover:from-pink-600 hover:via-purple-700 hover:to-indigo-700 text-white px-8 py-3 rounded-2xl font-semibold transition-all duration-300 transform hover:scale-110 hover:shadow-2xl hover:shadow-purple/30 overflow-hidden group">
                        <span class="relative z-10 flex items-center">
                            {{ __('messages.get_started') }}
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-2 transform transition-transform duration-300 group-hover:translate-x-1"></i>
                        </span>
                        <!-- Animated background effect -->
                        <div class="absolute inset-0 bg-gradient-to-r from-pink-400 via-purple-500 to-indigo-500 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 animate-pulse"></div>
                        <!-- Shine effect -->
                        <div class="absolute top-0 -left-1 w-1 h-full bg-gradient-to-b from-transparent via-white/20 to-transparent transform -skew-x-12 group-hover:left-full transition-all duration-700"></div>
                    </a>
                </div>
            </div>

            <!-- Mobile Menu Button -->
            <button wire:click="toggleMobileMenu" class="lg:hidden relative p-3 text-gray-500 hover:text-purple-600 transition-all duration-300 rounded-xl hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 hover:shadow-lg hover:shadow-purple/20 transform hover:scale-105">
                <div class="relative w-6 h-6">
                    <i data-lucide="menu" class="w-6 h-6 absolute inset-0 transition-all duration-300 transform {{ $mobileMenuOpen ? 'rotate-180 opacity-0' : 'rotate-0 opacity-100' }}"></i>
                    <i data-lucide="x" class="w-6 h-6 absolute inset-0 transition-all duration-300 transform {{ $mobileMenuOpen ? 'rotate-0 opacity-100' : '-rotate-180 opacity-0' }}"></i>
                </div>
                <!-- Subtle glow effect -->
                <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-purple-400/20 to-pink-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 animate-pulse"></div>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    @if($mobileMenuOpen)
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 transform -translate-y-6 scale-95"
            x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 transform -translate-y-6 scale-95"
            class="lg:hidden bg-gradient-to-b from-white/95 via-white/90 to-white/95 backdrop-blur-xl border-t border-gray-200/30 shadow-2xl">

            <div class="px-4 py-6 space-y-4">
                <!-- Mobile Navigation Links -->
                <div class="space-y-2">
                    <a href="{{ route('home') }}" class="flex items-center text-gray-700 hover:text-pink-600 font-medium py-4 px-5 rounded-2xl hover:bg-gradient-to-r hover:from-pink-50 hover:to-purple-50 hover:shadow-lg hover:shadow-pink/20 transition-all duration-300 transform hover:scale-105 {{ request()->routeIs('home') ? 'text-pink-600 bg-gradient-to-r from-pink-50 to-purple-50 shadow-lg shadow-pink/20' : '' }} group">
                        <span class="flex-1">{{ __('messages.home') }}</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 transform transition-transform duration-300 group-hover:translate-x-1 opacity-0 group-hover:opacity-100"></i>
                    </a>
                    <a href="{{ route('features') }}" class="flex items-center text-gray-700 hover:text-purple-600 font-medium py-4 px-5 rounded-2xl hover:bg-gradient-to-r hover:from-purple-50 hover:to-indigo-50 hover:shadow-lg hover:shadow-purple/20 transition-all duration-300 transform hover:scale-105 {{ request()->routeIs('features') ? 'text-purple-600 bg-gradient-to-r from-purple-50 to-indigo-50 shadow-lg shadow-purple/20' : '' }} group">
                        <span class="flex-1">{{ __('messages.features') }}</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 transform transition-transform duration-300 group-hover:translate-x-1 opacity-0 group-hover:opacity-100"></i>
                    </a>
                    <a href="{{ route('about') }}" class="flex items-center text-gray-700 hover:text-indigo-600 font-medium py-4 px-5 rounded-2xl hover:bg-gradient-to-r hover:from-indigo-50 hover:to-blue-50 hover:shadow-lg hover:shadow-indigo/20 transition-all duration-300 transform hover:scale-105 {{ request()->routeIs('about') ? 'text-indigo-600 bg-gradient-to-r from-indigo-50 to-blue-50 shadow-lg shadow-indigo/20' : '' }} group">
                        <span class="flex-1">{{ __('messages.about') }}</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 transform transition-transform duration-300 group-hover:translate-x-1 opacity-0 group-hover:opacity-100"></i>
                    </a>
                    <a href="{{ route('faq') }}" class="flex items-center text-gray-700 hover:text-blue-600 font-medium py-4 px-5 rounded-2xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 hover:shadow-lg hover:shadow-blue/20 transition-all duration-300 transform hover:scale-105 {{ request()->routeIs('faq') ? 'text-blue-600 bg-gradient-to-r from-blue-50 to-cyan-50 shadow-lg shadow-blue/20' : '' }} group">
                        <span class="flex-1">{{ __('messages.faq') }}</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 transform transition-transform duration-300 group-hover:translate-x-1 opacity-0 group-hover:opacity-100"></i>
                    </a>
                    <a href="{{ route('contact') }}" class="flex items-center text-gray-700 hover:text-cyan-600 font-medium py-4 px-5 rounded-2xl hover:bg-gradient-to-r hover:from-cyan-50 hover:to-teal-50 hover:shadow-lg hover:shadow-cyan/20 transition-all duration-300 transform hover:scale-105 {{ request()->routeIs('contact') ? 'text-cyan-600 bg-gradient-to-r from-cyan-50 to-teal-50 shadow-lg shadow-cyan/20' : '' }} group">
                        <span class="flex-1">{{ __('messages.contact') }}</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 transform transition-transform duration-300 group-hover:translate-x-1 opacity-0 group-hover:opacity-100"></i>
                    </a>
                </div>

                <!-- Mobile Language Switcher -->
                <div class="pt-2 border-t border-gray-200/50">
                    <div class="flex items-center justify-center space-x-2 py-2">
                        <span class="text-sm text-gray-500">Language:</span>
                        <div class="flex space-x-1">
                            @foreach($languages as $locale => $language)
                                <button
                                    wire:click="switchLanguage('{{ $locale }}')"
                                    class="px-3 py-1 text-sm font-medium rounded-lg transition-colors {{ $currentLocale === $locale ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }}">
                                    {{ $language['flag'] }} {{ $language['code'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Mobile CTA Buttons -->
                <div class="pt-6 space-y-4 border-t border-gray-200/30">
                    <a href="{{ route('start') }}" class="relative block w-full bg-gradient-to-r from-pink-500 via-purple-600 to-indigo-600 hover:from-pink-600 hover:via-purple-700 hover:to-indigo-700 text-white px-8 py-4 rounded-2xl font-semibold text-center transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-purple/30 overflow-hidden group">
                        <span class="relative z-10 flex items-center justify-center">
                            {{ __('messages.get_started') }}
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-2 transform transition-transform duration-300 group-hover:translate-x-1"></i>
                        </span>
                        <!-- Animated background effect -->
                        <div class="absolute inset-0 bg-gradient-to-r from-pink-400 via-purple-500 to-indigo-500 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 animate-pulse"></div>
                        <!-- Shine effect -->
                        <div class="absolute top-0 -left-1 w-1 h-full bg-gradient-to-b from-transparent via-white/20 to-transparent transform -skew-x-12 group-hover:left-full transition-all duration-700"></div>
                    </a>
                    @auth
                        @if(auth()->user()->hasRole('admin'))
                            <a href="{{ route('login') }}" class="block w-full text-center text-gray-600 hover:text-purple-600 font-medium py-4 px-6 rounded-2xl bg-gradient-to-r from-gray-50 to-gray-100 hover:from-purple-50 hover:to-pink-50 transition-all duration-300 transform hover:scale-105 hover:shadow-lg hover:shadow-purple/20">Admin</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    @endif
</nav>
