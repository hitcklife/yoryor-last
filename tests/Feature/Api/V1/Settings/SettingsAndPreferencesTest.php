<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\UserSetting;
use App\Models\Preference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('Settings and Preferences', function () {
    beforeEach(function () {
        $this->user = $this->createUserWithCompleteProfile();
        Sanctum::actingAs($this->user);
    });

    describe('Settings Management', function () {
        describe('GET /api/v1/settings', function () {
            it('returns all user settings', function () {
                $response = $this->getJson('/api/v1/settings');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            'notifications' => [
                                'push_enabled',
                                'email_enabled',
                                'match_notifications',
                                'message_notifications',
                                'like_notifications'
                            ],
                            'privacy' => [
                                'show_online_status',
                                'show_distance',
                                'show_age',
                                'discoverable'
                            ],
                            'discovery' => [
                                'age_range' => [
                                    'min',
                                    'max'
                                ],
                                'distance_km',
                                'show_me'
                            ]
                        ]
                    ]);
            });

            it('returns default settings for new user', function () {
                $response = $this->getJson('/api/v1/settings');

                $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'notifications' => [
                                'push_enabled' => true,
                                'email_enabled' => false,
                                'match_notifications' => true,
                                'message_notifications' => true,
                                'like_notifications' => true
                            ],
                            'privacy' => [
                                'show_online_status' => true,
                                'show_distance' => true,
                                'show_age' => true,
                                'discoverable' => true
                            ]
                        ]
                    ]);
            });
        });

        describe('PUT /api/v1/settings', function () {
            it('updates settings successfully', function () {
                $response = $this->putJson('/api/v1/settings', [
                    'notifications' => [
                        'push_enabled' => false,
                        'match_notifications' => false
                    ],
                    'privacy' => [
                        'show_online_status' => false,
                        'discoverable' => false
                    ]
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Settings updated successfully'
                    ]);

                // Verify settings were updated
                $response = $this->getJson('/api/v1/settings');
                $response->assertJson([
                    'data' => [
                        'notifications' => [
                            'push_enabled' => false,
                            'match_notifications' => false
                        ],
                        'privacy' => [
                            'show_online_status' => false,
                            'discoverable' => false
                        ]
                    ]
                ]);
            });

            it('updates partial settings', function () {
                $response = $this->putJson('/api/v1/settings', [
                    'privacy' => [
                        'show_distance' => false
                    ]
                ]);

                $response->assertStatus(200);

                // Verify only specified setting was updated
                $response = $this->getJson('/api/v1/settings');
                $response->assertJson([
                    'data' => [
                        'privacy' => [
                            'show_distance' => false,
                            'show_online_status' => true // Default unchanged
                        ]
                    ]
                ]);
            });

            it('validates setting values', function () {
                $response = $this->putJson('/api/v1/settings', [
                    'discovery' => [
                        'age_range' => [
                            'min' => 50,
                            'max' => 18 // Invalid: max less than min
                        ],
                        'distance_km' => -10 // Invalid: negative distance
                    ]
                ]);

                $this->assertValidationError($response, ['discovery.age_range', 'discovery.distance_km']);
            });
        });

        describe('Notification Settings', function () {
            describe('GET /api/v1/settings/notifications', function () {
                it('returns notification settings', function () {
                    $response = $this->getJson('/api/v1/settings/notifications');

                    $response->assertStatus(200)
                        ->assertJsonStructure([
                            'status',
                            'data' => [
                                'push_enabled',
                                'email_enabled',
                                'match_notifications',
                                'message_notifications',
                                'like_notifications',
                                'story_notifications',
                                'video_call_notifications',
                                'marketing_notifications'
                            ]
                        ]);
                });
            });

            describe('PUT /api/v1/settings/notifications', function () {
                it('updates notification settings', function () {
                    $response = $this->putJson('/api/v1/settings/notifications', [
                        'push_enabled' => false,
                        'email_enabled' => true,
                        'marketing_notifications' => false
                    ]);

                    $response->assertStatus(200)
                        ->assertJson([
                            'status' => 'success',
                            'message' => 'Notification settings updated successfully'
                        ]);
                });
            });
        });

        describe('Privacy Settings', function () {
            describe('GET /api/v1/settings/privacy', function () {
                it('returns privacy settings', function () {
                    $response = $this->getJson('/api/v1/settings/privacy');

                    $response->assertStatus(200)
                        ->assertJsonStructure([
                            'status',
                            'data' => [
                                'show_online_status',
                                'show_distance',
                                'show_age',
                                'discoverable',
                                'show_read_receipts',
                                'allow_screenshot'
                            ]
                        ]);
                });
            });

            describe('PUT /api/v1/settings/privacy', function () {
                it('updates privacy settings', function () {
                    $response = $this->putJson('/api/v1/settings/privacy', [
                        'show_online_status' => false,
                        'show_distance' => false,
                        'show_read_receipts' => false
                    ]);

                    $response->assertStatus(200)
                        ->assertJson([
                            'status' => 'success',
                            'message' => 'Privacy settings updated successfully'
                        ]);
                });

                it('handles private mode activation', function () {
                    $response = $this->putJson('/api/v1/settings/privacy', [
                        'discoverable' => false
                    ]);

                    $response->assertStatus(200);

                    // User should not appear in discovery
                    $this->assertDatabaseHas('users', [
                        'id' => $this->user->id,
                        'is_private' => true
                    ]);
                });
            });
        });
    });

    describe('Preferences Management', function () {
        describe('GET /api/v1/preferences', function () {
            it('returns user preferences', function () {
                $response = $this->getJson('/api/v1/preferences');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            'age_range' => ['min', 'max'],
                            'distance_km',
                            'gender_preference',
                            'religion_preference',
                            'education_preference',
                            'smoking_preference',
                            'drinking_preference',
                            'children_preference',
                            'marriage_timeline'
                        ]
                    ]);
            });

            it('returns default preferences for new user', function () {
                $response = $this->getJson('/api/v1/preferences');

                $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'age_range' => [
                                'min' => 18,
                                'max' => 35
                            ],
                            'distance_km' => 50,
                            'gender_preference' => $this->user->profile->gender === 'male' ? 'female' : 'male'
                        ]
                    ]);
            });
        });

        describe('PUT /api/v1/preferences', function () {
            it('updates preferences successfully', function () {
                $response = $this->putJson('/api/v1/preferences', [
                    'age_range' => [
                        'min' => 25,
                        'max' => 40
                    ],
                    'distance_km' => 100,
                    'religion_preference' => 'Islam',
                    'education_preference' => ['bachelor', 'master', 'phd'],
                    'smoking_preference' => 'never',
                    'children_preference' => 'wants_children'
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Preferences updated successfully'
                    ]);

                $this->assertDatabaseHas('user_preferences', [
                    'user_id' => $this->user->id,
                    'min_age' => 25,
                    'max_age' => 40,
                    'max_distance' => 100,
                    'religion_preference' => 'Islam',
                    'smoking_preference' => 'never',
                    'children_preference' => 'wants_children'
                ]);
            });

            it('validates age range', function () {
                $response = $this->putJson('/api/v1/preferences', [
                    'age_range' => [
                        'min' => 17, // Below 18
                        'max' => 100 // Too high
                    ]
                ]);

                $this->assertValidationError($response, ['age_range.min', 'age_range.max']);
            });

            it('validates preference arrays', function () {
                $response = $this->putJson('/api/v1/preferences', [
                    'education_preference' => 'not-an-array'
                ]);

                $this->assertValidationError($response, ['education_preference']);
            });

            it('enforces rate limiting', function () {
                // Make 20 requests (the limit)
                for ($i = 0; $i < 20; $i++) {
                    $this->putJson('/api/v1/preferences', [
                        'distance_km' => $i + 10
                    ]);
                }

                // The 21st request should be rate limited
                $response = $this->putJson('/api/v1/preferences', [
                    'distance_km' => 100
                ]);

                $response->assertStatus(429);
            });
        });

        describe('Family Preferences', function () {
            describe('GET /api/v1/family-preferences', function () {
                it('returns family preferences', function () {
                    $response = $this->getJson('/api/v1/family-preferences');

                    $response->assertStatus(200)
                        ->assertJsonStructure([
                            'status',
                            'data' => [
                                'wants_children',
                                'children_timeline',
                                'parenting_style',
                                'family_involvement',
                                'living_with_family',
                                'elder_care_responsibility'
                            ]
                        ]);
                });
            });

            describe('PUT /api/v1/family-preferences', function () {
                it('updates family preferences', function () {
                    $response = $this->putJson('/api/v1/family-preferences', [
                        'wants_children' => 'yes',
                        'children_timeline' => '2-3 years',
                        'parenting_style' => 'balanced',
                        'family_involvement' => 'moderate',
                        'living_with_family' => false,
                        'elder_care_responsibility' => 'shared'
                    ]);

                    $response->assertStatus(200)
                        ->assertJson([
                            'status' => 'success',
                            'message' => 'Family preferences updated successfully'
                        ]);
                });
            });
        });

        describe('Location Preferences', function () {
            describe('GET /api/v1/location-preferences', function () {
                it('returns location preferences', function () {
                    $response = $this->getJson('/api/v1/location-preferences');

                    $response->assertStatus(200)
                        ->assertJsonStructure([
                            'status',
                            'data' => [
                                'preferred_countries',
                                'preferred_cities',
                                'willing_to_relocate',
                                'relocation_timeline',
                                'visa_status'
                            ]
                        ]);
                });
            });

            describe('PUT /api/v1/location-preferences', function () {
                it('updates location preferences', function () {
                    $response = $this->putJson('/api/v1/location-preferences', [
                        'preferred_countries' => ['Uzbekistan', 'Turkey', 'UAE'],
                        'preferred_cities' => ['Tashkent', 'Istanbul', 'Dubai'],
                        'willing_to_relocate' => true,
                        'relocation_timeline' => 'within_year',
                        'visa_status' => 'citizen'
                    ]);

                    $response->assertStatus(200)
                        ->assertJson([
                            'status' => 'success',
                            'message' => 'Location preferences updated successfully'
                        ]);
                });
            });
        });

        describe('Discovery Settings', function () {
            describe('GET /api/v1/settings/discovery', function () {
                it('returns discovery settings', function () {
                    $response = $this->getJson('/api/v1/settings/discovery');

                    $response->assertStatus(200)
                        ->assertJsonStructure([
                            'status',
                            'data' => [
                                'age_range' => ['min', 'max'],
                                'distance_km',
                                'show_me',
                                'global_mode',
                                'verified_only',
                                'recently_active_only'
                            ]
                        ]);
                });
            });

            describe('PUT /api/v1/settings/discovery', function () {
                it('updates discovery settings', function () {
                    $response = $this->putJson('/api/v1/settings/discovery', [
                        'age_range' => [
                            'min' => 22,
                            'max' => 35
                        ],
                        'distance_km' => 75,
                        'show_me' => 'everyone',
                        'global_mode' => true,
                        'verified_only' => true
                    ]);

                    $response->assertStatus(200)
                        ->assertJson([
                            'status' => 'success',
                            'message' => 'Discovery settings updated successfully'
                        ]);
                });

                it('validates show_me values', function () {
                    $response = $this->putJson('/api/v1/settings/discovery', [
                        'show_me' => 'invalid'
                    ]);

                    $this->assertValidationError($response, ['show_me']);
                });

                it('handles global mode toggle', function () {
                    $response = $this->putJson('/api/v1/settings/discovery', [
                        'global_mode' => true,
                        'distance_km' => null // Should be ignored in global mode
                    ]);

                    $response->assertStatus(200);
                });
            });
        });

        describe('Security Settings', function () {
            describe('GET /api/v1/settings/security', function () {
                it('returns security settings', function () {
                    $response = $this->getJson('/api/v1/settings/security');

                    $response->assertStatus(200)
                        ->assertJsonStructure([
                            'status',
                            'data' => [
                                'two_factor_enabled',
                                'require_photo_verification',
                                'block_screenshots',
                                'hide_last_seen',
                                'login_notifications'
                            ]
                        ]);
                });
            });

            describe('PUT /api/v1/settings/security', function () {
                it('updates security settings', function () {
                    $response = $this->putJson('/api/v1/settings/security', [
                        'require_photo_verification' => true,
                        'block_screenshots' => true,
                        'hide_last_seen' => true,
                        'login_notifications' => true
                    ]);

                    $response->assertStatus(200)
                        ->assertJson([
                            'status' => 'success',
                            'message' => 'Security settings updated successfully'
                        ]);
                });
            });
        });
    });
});