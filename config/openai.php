<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key
    |--------------------------------------------------------------------------
    |
    | Your OpenAI API key for authentication
    |
    */
    'api_key' => env('OPENAI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Organization ID
    |--------------------------------------------------------------------------
    |
    | Your OpenAI organization ID (optional)
    |
    */
    'organization' => env('OPENAI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | The default model to use for API requests
    | Options: gpt-4o-mini, gpt-4o, gpt-3.5-turbo
    |
    */
    'default_model' => env('OPENAI_MODEL', 'gpt-4o-mini'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout for API requests in seconds
    |
    */
    'timeout' => env('OPENAI_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Max Tokens
    |--------------------------------------------------------------------------
    |
    | Maximum tokens for responses
    |
    */
    'max_tokens' => [
        'compatibility_analysis' => 500,
        'conversation_starter' => 150,
        'bio_analysis' => 300,
        'cultural_matching' => 400,
    ],

    /*
    |--------------------------------------------------------------------------
    | Temperature Settings
    |--------------------------------------------------------------------------
    |
    | Controls randomness in responses (0-2)
    | Lower = more focused, Higher = more creative
    |
    */
    'temperature' => [
        'compatibility_analysis' => 0.3,
        'conversation_starter' => 0.8,
        'bio_analysis' => 0.5,
        'cultural_matching' => 0.4,
    ],

    /*
    |--------------------------------------------------------------------------
    | System Prompts
    |--------------------------------------------------------------------------
    |
    | System prompts for different AI features
    |
    */
    'prompts' => [
        'compatibility_analyzer' => 'You are an expert matchmaker specializing in cultural compatibility for a dating app focused on Uzbek users worldwide. Analyze user profiles and provide compatibility insights based on cultural values, lifestyle, interests, and personality traits. Be respectful of cultural sensitivities and focus on meaningful connections.',
        
        'conversation_starter' => 'You are a friendly conversation coach helping users start meaningful conversations on a dating app. Create culturally appropriate, engaging conversation starters based on shared interests and profile information. Keep suggestions light, respectful, and encouraging.',
        
        'bio_analyzer' => 'You are a profile expert helping users understand personality traits from dating profiles. Extract key personality indicators, interests, values, and relationship goals from user bios. Focus on positive traits and compatibility factors.',
        
        'cultural_matcher' => 'You are a cultural compatibility expert for Uzbek dating. Evaluate compatibility based on cultural values, family orientation, religious practices, lifestyle choices, and traditions. Provide insights that respect both modern and traditional perspectives.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | API rate limits per user
    |
    */
    'rate_limits' => [
        'compatibility_analysis' => [
            'requests_per_day' => 50,
            'requests_per_hour' => 10,
        ],
        'conversation_starter' => [
            'requests_per_day' => 20,
            'requests_per_hour' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Cache TTL for AI responses in seconds
    |
    */
    'cache_ttl' => [
        'compatibility_score' => 86400, // 24 hours
        'bio_analysis' => 604800, // 7 days
        'conversation_starter' => 3600, // 1 hour
    ],
];