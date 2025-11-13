<!-- MapBox Global Reach Section -->
<section class="py-24 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 relative overflow-hidden">
    <!-- Background Stars -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-10 left-10 w-2 h-2 bg-white rounded-full animate-pulse"></div>
        <div class="absolute top-32 right-20 w-1 h-1 bg-blue-300 rounded-full animate-ping"></div>
        <div class="absolute bottom-40 left-1/4 w-1 h-1 bg-green-300 rounded-full animate-pulse"></div>
        <div class="absolute top-20 right-1/3 w-2 h-2 bg-purple-300 rounded-full animate-ping"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <div class="inline-flex items-center bg-gradient-to-r from-blue-500/20 via-purple-500/20 to-pink-500/20 backdrop-blur-sm border border-blue-500/30 rounded-full px-8 py-4 mb-8 shadow-lg shadow-blue-500/20 animate-fade-in">
                <span class="text-2xl mr-3">🌍</span>
                <span class="text-sm font-semibold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">Global Uzbek Community</span>
            </div>
            <h2 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-6">
                Our <span class="bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">Global</span> Reach
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Connecting Uzbeks across 45+ countries worldwide. From New York to Namangan, love knows no borders.
            </p>
        </div>

        <!-- MapBox Container -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 border border-gray-200/50 shadow-2xl mb-12">
            <div id="global-map" class="w-full h-[600px] rounded-2xl overflow-hidden shadow-2xl"></div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-16">
            <div class="text-center">
                <div class="text-4xl lg:text-5xl font-bold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent mb-2" 
                     x-data="{ count: 0 }" 
                     x-init="setInterval(() => { if (count < 50000) count += Math.floor(Math.random() * 1000) + 500 }, 50)"
                     x-text="count.toLocaleString() + '+'">
                    50,000+
                </div>
                <div class="text-gray-600 font-medium">Active Users</div>
            </div>
            <div class="text-center">
                <div class="text-4xl lg:text-5xl font-bold bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent mb-2">
                    45+
                </div>
                <div class="text-gray-600 font-medium">Countries</div>
            </div>
            <div class="text-center">
                <div class="text-4xl lg:text-5xl font-bold bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent mb-2" 
                     x-data="{ count: 0 }" 
                     x-init="setInterval(() => { if (count < 5200) count += Math.floor(Math.random() * 50) + 10 }, 100)"
                     x-text="count.toLocaleString() + '+'">
                    5,200+
                </div>
                <div class="text-gray-600 font-medium">Successful Matches</div>
            </div>
            <div class="text-center">
                <div class="text-4xl lg:text-5xl font-bold bg-gradient-to-r from-yellow-400 to-orange-400 bg-clip-text text-transparent mb-2">
                    24/7
                </div>
                <div class="text-gray-600 font-medium">Global Support</div>
            </div>
        </div>

        <!-- Major Cities Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach(config('mapbox.diaspora_locations') as $index => $location)
                @if($index < 9) <!-- Show only top 9 locations -->
                <div class="bg-white/90 backdrop-blur-sm rounded-xl p-6 border border-gray-200/50 hover:border-blue-500/50 transition-all duration-300 transform hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ $location['city'] }}</h3>
                            <p class="text-gray-600 text-sm">{{ $location['country'] }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                                {{ number_format($location['users']) }}+
                            </div>
                            <p class="text-gray-600 text-xs">Users</p>
                        </div>
                    </div>
                    
                    <!-- Activity Indicator -->
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-green-400 text-sm">Active Community</span>
                    </div>
                </div>
                @endif
            @endforeach
        </div>

        <!-- Call to Action -->
        <div class="text-center mt-16">
            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-6">
                Join Our Global Community Today
            </h3>
            <a href="{{ route('start') }}" class="inline-flex items-center justify-center bg-gradient-to-r from-blue-500 via-purple-600 to-pink-600 text-white px-12 py-4 rounded-2xl font-semibold text-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                <span>{{ __('messages.get_started') }}</span>
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- MapBox GL JS -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.js"></script>

    <script>
        // Initialize MapBox
        mapboxgl.accessToken = '{{ config("mapbox.access_token") }}';
        
        const map = new mapboxgl.Map({
            container: 'global-map',
            style: '{{ config("mapbox.default_style") }}',
            center: [{{ config('mapbox.default_center.0') }}, {{ config('mapbox.default_center.1') }}],
            zoom: {{ config('mapbox.default_zoom') }},
            projection: 'globe', // Use globe projection for 3D effect
            atmosphere: {
                'sky-color': '#199EF3',
                'sky-horizon-blend': 0.5,
                'horizon-color': '#ffffff',
                'horizon-fog-blend': 0.5,
                'fog-color': '#0000ff',
                'fog-ground-blend': 0.5,
                'atmosphere-blend': ['interpolate', ['linear'], ['zoom'], 0, 1, 12, 0]
            }
        });

        // Add navigation control
        map.addControl(new mapboxgl.NavigationControl(), 'top-right');

        // Wait for map to load
        map.on('load', () => {
            // Add locations data
            const locations = @json(config('mapbox.diaspora_locations'));
            
            // Create GeoJSON feature collection
            const geojsonData = {
                type: 'FeatureCollection',
                features: locations.map(location => ({
                    type: 'Feature',
                    geometry: {
                        type: 'Point',
                        coordinates: location.coordinates
                    },
                    properties: {
                        city: location.city,
                        country: location.country,
                        users: location.users,
                        type: location.type
                    }
                }))
            };

            // Add data source
            map.addSource('uzbek-diaspora', {
                type: 'geojson',
                data: geojsonData
            });

            // Add circles for locations
            map.addLayer({
                id: 'diaspora-circles',
                type: 'circle',
                source: 'uzbek-diaspora',
                paint: {
                    'circle-radius': [
                        'case',
                        ['==', ['get', 'type'], 'primary'], 15,
                        ['==', ['get', 'type'], 'major'], 12,
                        8
                    ],
                    'circle-color': [
                        'case',
                        ['==', ['get', 'type'], 'primary'], '#3B82F6', // Blue for Uzbekistan
                        ['==', ['get', 'type'], 'major'], '#8B5CF6',   // Purple for major cities
                        '#10B981'  // Green for secondary cities
                    ],
                    'circle-opacity': 0.8,
                    'circle-stroke-width': 2,
                    'circle-stroke-color': '#ffffff',
                    'circle-stroke-opacity': 0.9
                }
            });

            // Add pulsing effect for primary locations
            map.addLayer({
                id: 'diaspora-pulse',
                type: 'circle',
                source: 'uzbek-diaspora',
                filter: ['==', ['get', 'type'], 'primary'],
                paint: {
                    'circle-radius': {
                        'base': 1.75,
                        'stops': [
                            [12, 20],
                            [22, 40]
                        ]
                    },
                    'circle-color': '#3B82F6',
                    'circle-opacity': {
                        'stops': [
                            [0, 0.6],
                            [1, 0]
                        ]
                    }
                }
            });

            // Animate pulse effect
            let pulseRadius = 20;
            function animatePulse() {
                pulseRadius = pulseRadius === 20 ? 35 : 20;
                map.setPaintProperty('diaspora-pulse', 'circle-radius', pulseRadius);
                map.setPaintProperty('diaspora-pulse', 'circle-opacity', pulseRadius === 20 ? 0.6 : 0);
            }
            setInterval(animatePulse, 2000);

            // Add popups on click
            map.on('click', 'diaspora-circles', (e) => {
                const coordinates = e.features[0].geometry.coordinates.slice();
                const properties = e.features[0].properties;
                
                const popup = new mapboxgl.Popup()
                    .setLngLat(coordinates)
                    .setHTML(`
                        <div class="p-4">
                            <h3 class="font-bold text-lg text-gray-900 mb-2">${properties.city}</h3>
                            <p class="text-gray-600 mb-2">${properties.country}</p>
                            <p class="text-blue-600 font-semibold">${properties.users.toLocaleString()}+ Active Users</p>
                        </div>
                    `)
                    .addTo(map);
            });

            // Change cursor on hover
            map.on('mouseenter', 'diaspora-circles', () => {
                map.getCanvas().style.cursor = 'pointer';
            });

            map.on('mouseleave', 'diaspora-circles', () => {
                map.getCanvas().style.cursor = '';
            });

            // Add smooth rotation animation
            function rotateCameraAroundCenter() {
                map.rotateTo((map.getBearing() + 0.2) % 360, {
                    duration: 100
                });
                requestAnimationFrame(rotateCameraAroundCenter);
            }
            
            // Start rotation after 3 seconds delay
            setTimeout(() => {
                // rotateCameraAroundCenter(); // Uncomment for continuous rotation
            }, 3000);
        });

        // Add resize handler
        map.on('resize', () => {
            map.resize();
        });
    </script>
</section>