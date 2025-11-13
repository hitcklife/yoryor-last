<!-- Interactive World Map Section -->
<section class="py-24 bg-gradient-to-br from-slate-50 via-white to-gray-50 relative overflow-hidden">
    <!-- Background decorative elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-1/4 w-64 h-64 bg-gradient-to-br from-blue-200/20 to-indigo-200/20 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute bottom-20 right-1/4 w-80 h-80 bg-gradient-to-br from-indigo-200/20 to-purple-200/20 rounded-full blur-3xl animate-float-reverse"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-br from-blue-200/10 to-indigo-200/10 rounded-full blur-3xl animate-float"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <div class="inline-flex items-center bg-gradient-to-r from-blue-100/80 via-indigo-50/80 to-purple-100/80 backdrop-blur-sm border border-blue-200/60 rounded-full px-8 py-4 mb-8 shadow-lg shadow-blue-500/10 animate-fade-in group hover:shadow-blue-500/20 transition-all duration-300">
                <span class="text-2xl mr-3 animate-bounce">🌍</span>
                <span class="text-sm font-semibold bg-gradient-to-r from-blue-700 to-indigo-600 bg-clip-text text-transparent">Global Uzbek Community Map</span>
                <!-- Animated sparkle effect -->
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-blue-400 rounded-full animate-ping opacity-75"></div>
            </div>
            <h2 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-6 animate-slide-up">
                See Our <span class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent animate-pulse">Global Reach</span>
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto animate-fade-in" style="animation-delay: 0.3s;">
                Explore our worldwide Uzbek community. Click on different regions to see member counts, success stories, and local community features.
            </p>
        </div>

        <!-- Interactive Map Container -->
        <div class="relative bg-gradient-to-br from-white/95 to-blue-50/80 backdrop-blur-sm rounded-3xl p-8 border border-blue-200/40 shadow-2xl shadow-blue-500/10 mb-16">
            <!-- Map SVG -->
            <div class="relative w-full h-96 lg:h-[500px] overflow-hidden rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50">
                <svg viewBox="0 0 1000 500" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <!-- Simplified world map with interactive regions -->
                    
                    <!-- North America -->
                    <g class="region-group" data-region="north-america" data-members="12000" data-country="USA & Canada">
                        <path d="M100 150 L200 140 L220 160 L180 180 L120 170 Z" 
                              class="region-path fill-blue-200 hover:fill-blue-400 stroke-blue-300 stroke-2 cursor-pointer transition-all duration-300 hover:scale-105" 
                              data-tooltip="USA & Canada: 12,000+ members">
                        </path>
                        <circle cx="150" cy="160" r="8" class="fill-blue-600 animate-pulse">
                            <animate attributeName="r" values="8;12;8" dur="2s" repeatCount="indefinite"/>
                        </circle>
                    </g>

                    <!-- Europe -->
                    <g class="region-group" data-region="europe" data-members="8500" data-country="Europe">
                        <path d="M400 120 L500 110 L520 130 L480 150 L420 140 Z" 
                              class="region-path fill-indigo-200 hover:fill-indigo-400 stroke-indigo-300 stroke-2 cursor-pointer transition-all duration-300 hover:scale-105">
                        </path>
                        <circle cx="450" cy="130" r="6" class="fill-indigo-600 animate-pulse">
                            <animate attributeName="r" values="6;10;6" dur="2.5s" repeatCount="indefinite"/>
                        </circle>
                    </g>

                    <!-- Russia & CIS -->
                    <g class="region-group" data-region="russia-cis" data-members="15000" data-country="Russia & CIS">
                        <path d="M500 100 L700 90 L720 110 L680 130 L520 120 Z" 
                              class="region-path fill-purple-200 hover:fill-purple-400 stroke-purple-300 stroke-2 cursor-pointer transition-all duration-300 hover:scale-105">
                        </path>
                        <circle cx="600" cy="110" r="10" class="fill-purple-600 animate-pulse">
                            <animate attributeName="r" values="10;14;10" dur="1.8s" repeatCount="indefinite"/>
                        </circle>
                    </g>

                    <!-- South Korea & Japan -->
                    <g class="region-group" data-region="east-asia" data-members="4200" data-country="South Korea & Japan">
                        <path d="M750 180 L800 170 L820 190 L780 210 L760 200 Z" 
                              class="region-path fill-red-200 hover:fill-red-400 stroke-red-300 stroke-2 cursor-pointer transition-all duration-300 hover:scale-105">
                        </path>
                        <circle cx="780" cy="190" r="5" class="fill-red-600 animate-pulse">
                            <animate attributeName="r" values="5;8;5" dur="3s" repeatCount="indefinite"/>
                        </circle>
                    </g>

                    <!-- Turkey -->
                    <g class="region-group" data-region="turkey" data-members="3800" data-country="Turkey">
                        <path d="M450 200 L480 190 L500 210 L470 230 L460 220 Z" 
                              class="region-path fill-orange-200 hover:fill-orange-400 stroke-orange-300 stroke-2 cursor-pointer transition-all duration-300 hover:scale-105">
                        </path>
                        <circle cx="475" cy="210" r="5" class="fill-orange-600 animate-pulse">
                            <animate attributeName="r" values="5;8;5" dur="2.2s" repeatCount="indefinite"/>
                        </circle>
                    </g>

                    <!-- UAE & Gulf -->
                    <g class="region-group" data-region="gulf" data-members="2500" data-country="UAE & Gulf">
                        <path d="M480 280 L520 270 L540 290 L500 310 L490 300 Z" 
                              class="region-path fill-green-200 hover:fill-green-400 stroke-green-300 stroke-2 cursor-pointer transition-all duration-300 hover:scale-105">
                        </path>
                        <circle cx="510" cy="290" r="4" class="fill-green-600 animate-pulse">
                            <animate attributeName="r" values="4;7;4" dur="2.8s" repeatCount="indefinite"/>
                        </circle>
                    </g>

                    <!-- Uzbekistan (Central) -->
                    <g class="region-group" data-region="uzbekistan" data-members="25000" data-country="Uzbekistan">
                        <path d="M550 200 L600 190 L620 210 L580 230 L560 220 Z" 
                              class="region-path fill-teal-200 hover:fill-teal-400 stroke-teal-300 stroke-2 cursor-pointer transition-all duration-300 hover:scale-105">
                        </path>
                        <circle cx="580" cy="210" r="12" class="fill-teal-600 animate-pulse">
                            <animate attributeName="r" values="12;16;12" dur="1.5s" repeatCount="indefinite"/>
                        </circle>
                    </g>

                    <!-- Connection Lines -->
                    <g class="connection-lines opacity-30">
                        <line x1="150" y1="160" x2="450" y2="130" stroke="#3B82F6" stroke-width="2" stroke-dasharray="5,5">
                            <animate attributeName="stroke-dashoffset" values="0;10" dur="3s" repeatCount="indefinite"/>
                        </line>
                        <line x1="450" y1="130" x2="600" y2="110" stroke="#8B5CF6" stroke-width="2" stroke-dasharray="5,5">
                            <animate attributeName="stroke-dashoffset" values="0;10" dur="3.5s" repeatCount="indefinite"/>
                        </line>
                        <line x1="600" y1="110" x2="580" y2="210" stroke="#10B981" stroke-width="2" stroke-dasharray="5,5">
                            <animate attributeName="stroke-dashoffset" values="0;10" dur="2.8s" repeatCount="indefinite"/>
                        </line>
                        <line x1="580" y1="210" x2="780" y2="190" stroke="#EF4444" stroke-width="2" stroke-dasharray="5,5">
                            <animate attributeName="stroke-dashoffset" values="0;10" dur="4s" repeatCount="indefinite"/>
                        </line>
                    </g>
                </svg>

                <!-- Floating Statistics -->
                <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm rounded-xl p-4 shadow-lg border border-blue-200/40">
                    <div class="text-sm font-semibold text-gray-700 mb-1">Total Members</div>
                    <div class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">50,000+</div>
                </div>

                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-xl p-4 shadow-lg border border-indigo-200/40">
                    <div class="text-sm font-semibold text-gray-700 mb-1">Countries</div>
                    <div class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">45</div>
                </div>

                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-white/90 backdrop-blur-sm rounded-xl p-4 shadow-lg border border-purple-200/40">
                    <div class="text-sm font-semibold text-gray-700 mb-1">Success Stories</div>
                    <div class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">5,200+</div>
                </div>
            </div>

            <!-- Interactive Tooltip -->
            <div id="map-tooltip" class="absolute bg-white/95 backdrop-blur-sm rounded-xl p-4 shadow-2xl border border-gray-200/40 pointer-events-none opacity-0 transition-all duration-300 transform scale-95">
                <div class="text-lg font-bold text-gray-900 mb-1" id="tooltip-country">Select a Region</div>
                <div class="text-sm text-gray-600 mb-2" id="tooltip-members">Hover over a region to see details</div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                    <span class="text-xs text-gray-500">Active Community</span>
                </div>
            </div>
        </div>

        <!-- Regional Statistics Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-16">
            <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200/40 hover:shadow-lg hover:shadow-blue-500/20 transition-all duration-300 cursor-pointer" data-region="north-america">
                <div class="text-2xl mb-2">🇺🇸</div>
                <div class="text-lg font-bold text-blue-700">12K+</div>
                <div class="text-xs text-blue-600">USA & Canada</div>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl border border-indigo-200/40 hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-300 cursor-pointer" data-region="europe">
                <div class="text-2xl mb-2">🇪🇺</div>
                <div class="text-lg font-bold text-indigo-700">8.5K+</div>
                <div class="text-xs text-indigo-600">Europe</div>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200/40 hover:shadow-lg hover:shadow-purple-500/20 transition-all duration-300 cursor-pointer" data-region="russia-cis">
                <div class="text-2xl mb-2">🇷🇺</div>
                <div class="text-lg font-bold text-purple-700">15K+</div>
                <div class="text-xs text-purple-600">Russia & CIS</div>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-xl border border-red-200/40 hover:shadow-lg hover:shadow-red-500/20 transition-all duration-300 cursor-pointer" data-region="east-asia">
                <div class="text-2xl mb-2">🇰🇷</div>
                <div class="text-lg font-bold text-red-700">4.2K+</div>
                <div class="text-xs text-red-600">East Asia</div>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border border-orange-200/40 hover:shadow-lg hover:shadow-orange-500/20 transition-all duration-300 cursor-pointer" data-region="turkey">
                <div class="text-2xl mb-2">🇹🇷</div>
                <div class="text-lg font-bold text-orange-700">3.8K+</div>
                <div class="text-xs text-orange-600">Turkey</div>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200/40 hover:shadow-lg hover:shadow-green-500/20 transition-all duration-300 cursor-pointer" data-region="gulf">
                <div class="text-2xl mb-2">🇦🇪</div>
                <div class="text-lg font-bold text-green-700">2.5K+</div>
                <div class="text-xs text-green-600">UAE & Gulf</div>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-teal-50 to-teal-100 rounded-xl border border-teal-200/40 hover:shadow-lg hover:shadow-teal-500/20 transition-all duration-300 cursor-pointer" data-region="uzbekistan">
                <div class="text-2xl mb-2">🇺🇿</div>
                <div class="text-lg font-bold text-teal-700">25K+</div>
                <div class="text-xs text-teal-600">Uzbekistan</div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-8 text-white">
                <h3 class="text-2xl font-bold mb-4">Join the Global Uzbek Community</h3>
                <p class="text-lg mb-6 text-blue-100">
                    Connect with Uzbeks in your region or explore new connections worldwide. Our platform makes it easy to find your perfect match across borders.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                    <div class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Interactive global map
                    </div>
                    <div class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Regional community stats
                    </div>
                    <div class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Cross-border connections
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Interactive map functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tooltip = document.getElementById('map-tooltip');
            const regions = document.querySelectorAll('.region-group, [data-region]');
            
            regions.forEach(region => {
                region.addEventListener('mouseenter', function(e) {
                    const members = this.dataset.members || '0';
                    const country = this.dataset.country || 'Unknown';
                    
                    tooltip.querySelector('#tooltip-country').textContent = country;
                    tooltip.querySelector('#tooltip-members').textContent = `${members} members`;
                    
                    tooltip.style.opacity = '1';
                    tooltip.style.transform = 'scale(1)';
                    
                    // Position tooltip
                    const rect = this.getBoundingClientRect();
                    const mapRect = this.closest('svg').getBoundingClientRect();
                    tooltip.style.left = (rect.left - mapRect.left + rect.width / 2) + 'px';
                    tooltip.style.top = (rect.top - mapRect.top - 10) + 'px';
                });
                
                region.addEventListener('mouseleave', function() {
                    tooltip.style.opacity = '0';
                    tooltip.style.transform = 'scale(0.95)';
                });
                
                region.addEventListener('click', function() {
                    // Add click functionality here
                    console.log('Clicked region:', this.dataset.region);
                });
            });
        });
    </script>
</section>
