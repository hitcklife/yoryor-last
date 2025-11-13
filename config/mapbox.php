<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MapBox Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MapBox integration including access tokens and
    | default map settings for the YorYor global reach visualization.
    |
    */

    'access_token' => env('MAPBOX_ACCESS_TOKEN'),
    'secret_token' => env('MAPBOX_SECRET_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Default Map Settings
    |--------------------------------------------------------------------------
    */

    'default_style' => 'mapbox://styles/mapbox/light-v11', // Light theme for better visibility
    'default_center' => [64.585262, 39.650349], // Centered on Uzbekistan
    'default_zoom' => 2,

    /*
    |--------------------------------------------------------------------------
    | Uzbek Diaspora Locations
    |--------------------------------------------------------------------------
    | Key locations where Uzbek communities are present
    */

    'diaspora_locations' => [
        // Central Asia
        [
            'city' => 'Tashkent',
            'country' => 'Uzbekistan',
            'coordinates' => [69.2401, 41.2995],
            'users' => 15000,
            'type' => 'primary'
        ],
        [
            'city' => 'Samarkand',
            'country' => 'Uzbekistan', 
            'coordinates' => [66.9597, 39.6270],
            'users' => 8000,
            'type' => 'primary'
        ],
        [
            'city' => 'Bukhara',
            'country' => 'Uzbekistan',
            'coordinates' => [64.4286, 39.7675],
            'users' => 5000,
            'type' => 'primary'
        ],

        // Russia & CIS
        [
            'city' => 'Moscow',
            'country' => 'Russia',
            'coordinates' => [37.6176, 55.7558],
            'users' => 12000,
            'type' => 'major'
        ],
        [
            'city' => 'St. Petersburg',
            'country' => 'Russia',
            'coordinates' => [30.3351, 59.9311],
            'users' => 6000,
            'type' => 'major'
        ],
        [
            'city' => 'Almaty',
            'country' => 'Kazakhstan',
            'coordinates' => [76.8512, 43.2220],
            'users' => 4500,
            'type' => 'major'
        ],

        // Europe
        [
            'city' => 'London',
            'country' => 'United Kingdom',
            'coordinates' => [-0.1276, 51.5074],
            'users' => 3200,
            'type' => 'major'
        ],
        [
            'city' => 'Berlin',
            'country' => 'Germany',
            'coordinates' => [13.4050, 52.5200],
            'users' => 2800,
            'type' => 'major'
        ],
        [
            'city' => 'Paris',
            'country' => 'France',
            'coordinates' => [2.3522, 48.8566],
            'users' => 2100,
            'type' => 'major'
        ],
        [
            'city' => 'Istanbul',
            'country' => 'Turkey',
            'coordinates' => [28.9784, 41.0082],
            'users' => 3800,
            'type' => 'major'
        ],

        // North America
        [
            'city' => 'New York',
            'country' => 'United States',
            'coordinates' => [-74.0060, 40.7128],
            'users' => 4200,
            'type' => 'major'
        ],
        [
            'city' => 'Los Angeles',
            'country' => 'United States',
            'coordinates' => [-118.2437, 34.0522],
            'users' => 2900,
            'type' => 'major'
        ],
        [
            'city' => 'Chicago',
            'country' => 'United States',
            'coordinates' => [-87.6298, 41.8781],
            'users' => 1800,
            'type' => 'secondary'
        ],
        [
            'city' => 'Toronto',
            'country' => 'Canada',
            'coordinates' => [-79.3832, 43.6532],
            'users' => 1500,
            'type' => 'secondary'
        ],

        // Asia Pacific
        [
            'city' => 'Dubai',
            'country' => 'UAE',
            'coordinates' => [55.2708, 25.2048],
            'users' => 2400,
            'type' => 'major'
        ],
        [
            'city' => 'Seoul',
            'country' => 'South Korea',
            'coordinates' => [126.9780, 37.5665],
            'users' => 1200,
            'type' => 'secondary'
        ],
        [
            'city' => 'Tokyo',
            'country' => 'Japan',
            'coordinates' => [139.6917, 35.6895],
            'users' => 900,
            'type' => 'secondary'
        ],
        [
            'city' => 'Sydney',
            'country' => 'Australia',
            'coordinates' => [151.2093, -33.8688],
            'users' => 800,
            'type' => 'secondary'
        ],

        // Additional locations
        [
            'city' => 'Prague',
            'country' => 'Czech Republic',
            'coordinates' => [14.4378, 50.0755],
            'users' => 700,
            'type' => 'secondary'
        ],
        [
            'city' => 'Warsaw',
            'country' => 'Poland',
            'coordinates' => [21.0122, 52.2298],
            'users' => 650,
            'type' => 'secondary'
        ],
        [
            'city' => 'Vienna',
            'country' => 'Austria',
            'coordinates' => [16.3738, 48.2082],
            'users' => 550,
            'type' => 'secondary'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Map Styles
    |--------------------------------------------------------------------------
    */

    'styles' => [
        'light' => 'mapbox://styles/mapbox/light-v11',
        'dark' => 'mapbox://styles/mapbox/dark-v11',
        'streets' => 'mapbox://styles/mapbox/streets-v12',
        'satellite' => 'mapbox://styles/mapbox/satellite-streets-v12',
        'custom' => 'mapbox://styles/mapbox/cjf4m44iw0uza2spb3ovr1u75' // Custom style if needed
    ]
];