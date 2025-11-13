<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('Profile Management', function () {
    beforeEach(function () {
        $this->user = $this->createUserWithCompleteProfile();
        Sanctum::actingAs($this->user);
    });

    describe('GET /api/v1/profile/me', function () {
        it('returns authenticated user profile', function () {
            $response = $this->getJson('/api/v1/profile/me');


            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'first_name',
                        'last_name',
                        'age',
                        'date_of_birth',
                        'gender',
                        'bio',
                        'city',
                        'country',
                        'latitude',
                        'longitude',
                        'occupation',
                        'education',
                        'height',
                        'religion',
                        'drinking',
                        'smoking',
                        'languages',
                        'interests',
                        'photos',
                        'completion_percentage',
                        'verification_status' => [
                            'identity_verified',
                            'photo_verified',
                            'employment_verified'
                        ]
                    ]
                ]);
        });

        it('includes calculated age', function () {
            $profile = $this->user->profile;
            $profile->update(['date_of_birth' => now()->subYears(25)->format('Y-m-d')]);

            $response = $this->getJson('/api/v1/profile/me');

            $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'age' => 25
                    ]
                ]);
        });

    });

    describe('GET /api/v1/users/{userId}/profile', function () {
        beforeEach(function () {
            $this->otherUser = $this->createUserWithCompleteProfile();
        });

        it('returns other user profile', function () {
            $response = $this->getJson("/api/v1/users/{$this->otherUser->id}/profile");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'first_name',
                        'last_name',
                        'age',
                        'distance_km',
                        'bio',
                        'photos',
                        'common_interests',
                        'compatibility_score',
                        'mutual_friends',
                        'last_active'
                    ]
                ]);
        });

        it('calculates distance between users', function () {
            // Set user locations
            $this->user->profile->update([
                'latitude' => 40.7128,
                'longitude' => -74.0060 // New York
            ]);

            $this->otherUser->profile->update([
                'latitude' => 40.7580,
                'longitude' => -73.9855 // Times Square (about 3.5km away)
            ]);

            $response = $this->getJson("/api/v1/users/{$this->otherUser->id}/profile");

            $response->assertStatus(200);
            $distance = $response->json('data.distance_km');
            expect($distance)->toBeGreaterThan(3)->toBeLessThan(4);
        });

        it('hides profile of blocked users', function () {
            $this->user->blockedUsers()->create(['blocked_id' => $this->otherUser->id]);

            $response = $this->getJson("/api/v1/users/{$this->otherUser->id}/profile");

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });

        it('hides profile from blocked users', function () {
            $this->otherUser->blockedUsers()->create(['blocked_id' => $this->user->id]);

            $response = $this->getJson("/api/v1/users/{$this->otherUser->id}/profile");

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });

        it('returns 404 for non-existent user', function () {
            $response = $this->getJson('/api/v1/users/99999/profile');

            $this->assertApiError($response, 'NOT_FOUND', 404);
        });

        it('respects privacy settings', function () {
            $this->otherUser->update(['is_private' => true]);

            $response = $this->getJson("/api/v1/users/{$this->otherUser->id}/profile");

            $response->assertStatus(200);
            // Private profiles should show limited information
            expect($response->json('data'))->not->toHaveKey('bio');
            expect($response->json('data'))->not->toHaveKey('occupation');
        });
    });

    describe('PUT /api/v1/profile/{profile}', function () {
        it('updates profile successfully', function () {
            $updateData = [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'bio' => 'Updated bio text',
                'city' => 'New York',
                'occupation' => 'Senior Developer',
                'interests' => ['coding', 'travel', 'photography']
            ];

            $response = $this->putJson("/api/v1/profile/{$this->user->profile->id}", $updateData);

            $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Profile updated successfully'
                ]);

            $this->assertDatabaseHas('profiles', [
                'user_id' => $this->user->id,
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'bio' => 'Updated bio text',
                'city' => 'New York',
                'occupation' => 'Senior Developer'
            ]);
        });

        it('validates profile data', function () {
            $response = $this->putJson("/api/v1/profile/{$this->user->profile->id}", [
                'first_name' => '',
                'age' => 150,
                'height' => 300,
                'gender' => 'invalid'
            ]);

            $this->assertValidationError($response, ['first_name', 'height', 'gender']);
        });

        it('prevents updating other user profiles', function () {
            $otherUser = $this->createUserWithCompleteProfile();

            $response = $this->putJson("/api/v1/profile/{$otherUser->profile->id}", [
                'first_name' => 'Hacked'
            ]);

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });

        it('validates interests array', function () {
            $response = $this->putJson("/api/v1/profile/{$this->user->profile->id}", [
                'interests' => 'not-an-array'
            ]);

            $this->assertValidationError($response, ['interests']);
        });

        it('validates languages array', function () {
            $response = $this->putJson("/api/v1/profile/{$this->user->profile->id}", [
                'languages' => ['valid', '', 'also valid']
            ]);

            $this->assertValidationError($response, ['languages.1']);
        });

        it('enforces rate limiting', function () {
            // Make 20 requests (the limit)
            for ($i = 0; $i < 20; $i++) {
                $this->putJson("/api/v1/profile/{$this->user->profile->id}", [
                    'bio' => "Update $i"
                ]);
            }

            // The 21st request should be rate limited
            $response = $this->putJson("/api/v1/profile/{$this->user->profile->id}", [
                'bio' => 'Rate limited update'
            ]);

            $response->assertStatus(429);
        });
    });

    describe('GET /api/v1/profile/completion-status', function () {
        it('returns profile completion status', function () {
            $response = $this->getJson('/api/v1/profile/completion-status');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'completion_percentage',
                        'missing_fields',
                        'completed_sections' => [
                            'basic_info',
                            'photos',
                            'bio',
                            'interests',
                            'preferences'
                        ]
                    ]
                ]);
        });

        it('identifies missing fields correctly', function () {
            $this->user->profile->update([
                'height' => null,
                'education' => null,
                'bio' => null
            ]);

            $response = $this->getJson('/api/v1/profile/completion-status');

            $response->assertStatus(200);
            $missingFields = $response->json('data.missing_fields');
            expect($missingFields)->toContain('height', 'education', 'bio');
        });

        it('calculates completion percentage', function () {
            // Complete profile
            $completeProfile = $this->user->profile;
            $completeProfile->update([
                'bio' => 'Complete bio',
                'height' => 175,
                'education' => 'Bachelor',
                'occupation' => 'Developer'
            ]);

            $response = $this->getJson('/api/v1/profile/completion-status');

            expect($response->json('data.completion_percentage'))->toBeGreaterThan(80);

            // Incomplete profile
            $this->user->profile->update([
                'bio' => null,
                'height' => null,
                'education' => null,
                'occupation' => null
            ]);

            $response = $this->getJson('/api/v1/profile/completion-status');

            expect($response->json('data.completion_percentage'))->toBeLessThan(50);
        });
    });

    describe('Cultural Profile', function () {
        describe('GET /api/v1/cultural-profile', function () {
            it('returns cultural profile', function () {
                $response = $this->getJson('/api/v1/cultural-profile');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            'religion',
                            'religiosity_level',
                            'prayer_frequency',
                            'dietary_restrictions',
                            'languages_spoken',
                            'cultural_values',
                            'family_values'
                        ]
                    ]);
            });
        });

        describe('PUT /api/v1/cultural-profile', function () {
            it('updates cultural profile', function () {
                $response = $this->putJson('/api/v1/cultural-profile', [
                    'religion' => 'Islam',
                    'religiosity_level' => 'moderate',
                    'prayer_frequency' => 'daily',
                    'dietary_restrictions' => ['halal'],
                    'languages_spoken' => ['Uzbek', 'English', 'Russian'],
                    'cultural_values' => ['family_oriented', 'traditional'],
                    'family_values' => 'very_important'
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Cultural profile updated successfully'
                    ]);
            });

            it('validates cultural values', function () {
                $response = $this->putJson('/api/v1/cultural-profile', [
                    'religiosity_level' => 'invalid',
                    'prayer_frequency' => 'invalid',
                    'family_values' => 'invalid'
                ]);

                $this->assertValidationError($response, [
                    'religiosity_level',
                    'prayer_frequency',
                    'family_values'
                ]);
            });
        });
    });

    describe('Career Profile', function () {
        describe('GET /api/v1/career-profile', function () {
            it('returns career profile', function () {
                $response = $this->getJson('/api/v1/career-profile');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            'occupation',
                            'company',
                            'education_level',
                            'university',
                            'field_of_study',
                            'income_level',
                            'career_goals',
                            'work_schedule'
                        ]
                    ]);
            });
        });

        describe('PUT /api/v1/career-profile', function () {
            it('updates career profile', function () {
                $response = $this->putJson('/api/v1/career-profile', [
                    'occupation' => 'Software Engineer',
                    'company' => 'Tech Corp',
                    'education_level' => 'master',
                    'university' => 'MIT',
                    'field_of_study' => 'Computer Science',
                    'income_level' => 'high',
                    'career_goals' => 'Become a CTO',
                    'work_schedule' => 'flexible'
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Career profile updated successfully'
                    ]);
            });
        });
    });

    describe('Physical Profile', function () {
        describe('GET /api/v1/physical-profile', function () {
            it('returns physical profile', function () {
                $response = $this->getJson('/api/v1/physical-profile');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            'height',
                            'body_type',
                            'ethnicity',
                            'eye_color',
                            'hair_color',
                            'has_children',
                            'wants_children',
                            'smoking',
                            'drinking',
                            'exercise_frequency'
                        ]
                    ]);
            });
        });

        describe('PUT /api/v1/physical-profile', function () {
            it('updates physical profile', function () {
                $response = $this->putJson('/api/v1/physical-profile', [
                    'height' => 175,
                    'body_type' => 'athletic',
                    'ethnicity' => 'asian',
                    'eye_color' => 'brown',
                    'hair_color' => 'black',
                    'has_children' => false,
                    'wants_children' => true,
                    'smoking' => 'never',
                    'drinking' => 'socially',
                    'exercise_frequency' => 'regularly'
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Physical profile updated successfully'
                    ]);
            });

            it('validates height range', function () {
                $response = $this->putJson('/api/v1/physical-profile', [
                    'height' => 300 // Too tall
                ]);

                $this->assertValidationError($response, ['height']);
            });
        });
    });

    describe('Comprehensive Profile', function () {
        describe('GET /api/v1/comprehensive-profile', function () {
            it('returns all profile data in one endpoint', function () {
                $response = $this->getJson('/api/v1/comprehensive-profile');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            'basic' => [
                                'id',
                                'first_name',
                                'last_name',
                                'age',
                                'gender'
                            ],
                            'location' => [
                                'city',
                                'country',
                                'latitude',
                                'longitude'
                            ],
                            'cultural' => [
                                'religion',
                                'languages_spoken'
                            ],
                            'career' => [
                                'occupation',
                                'education_level'
                            ],
                            'physical' => [
                                'height',
                                'body_type'
                            ],
                            'preferences' => [
                                'age_range',
                                'distance_km'
                            ],
                            'photos',
                            'verification_status',
                            'completion_percentage'
                        ]
                    ]);
            });
        });

        describe('PUT /api/v1/comprehensive-profile', function () {
            it('updates multiple profile sections at once', function () {
                $response = $this->putJson('/api/v1/comprehensive-profile', [
                    'basic' => [
                        'first_name' => 'John',
                        'bio' => 'Updated comprehensive bio'
                    ],
                    'cultural' => [
                        'religion' => 'Islam',
                        'prayer_frequency' => 'daily'
                    ],
                    'career' => [
                        'occupation' => 'Senior Developer'
                    ],
                    'physical' => [
                        'height' => 180
                    ]
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Profile updated successfully'
                    ]);

                $this->assertDatabaseHas('profiles', [
                    'user_id' => $this->user->id,
                    'first_name' => 'John',
                    'bio' => 'Updated comprehensive bio',
                    'occupation' => 'Senior Developer',
                    'height' => 180
                ]);
            });
        });
    });

    describe('Report Reasons', function () {
        it('returns available report reasons', function () {
            $response = $this->getJson('/api/v1/report-reasons');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => [
                            'id',
                            'reason',
                            'description',
                            'category'
                        ]
                    ]
                ]);
        });
    });
});

// Test without authentication (outside the main describe block)
describe('Profile Authentication', function () {
    it('requires authentication for profile/me', function () {
        $response = $this->getJson('/api/v1/profile/me');
        $response->assertStatus(401);
    });
});