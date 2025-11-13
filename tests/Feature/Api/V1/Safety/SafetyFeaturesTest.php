<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\UserEmergencyContact;
use App\Models\PanicActivation;
use App\Models\UserBlock;
use App\Models\UserReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EmergencyContactNotification;
use App\Notifications\PanicAlertNotification;

uses(RefreshDatabase::class);

describe('Safety Features', function () {
    beforeEach(function () {
        Notification::fake();
        $this->user = $this->createUserWithCompleteProfile();
        Sanctum::actingAs($this->user);
    });

    describe('Panic Button', function () {
        describe('POST /api/v1/safety/panic/activate', function () {
            it('activates panic button successfully', function () {
                // Create emergency contacts
                $contacts = collect();
                for ($i = 0; $i < 3; $i++) {
                    $contact = UserEmergencyContact::create([
                        'user_id' => $this->user->id,
                        'name' => "Contact $i",
                        'relationship' => 'friend',
                        'phone' => "+99890123456$i",
                        'email' => "contact$i@example.com",
                        'is_verified' => true,
                        'is_primary' => $i === 0,
                        'receives_panic_alerts' => true
                    ]);
                    $contacts->push($contact);
                }

                $response = $this->postJson('/api/v1/safety/panic/activate', [
                    'trigger_type' => 'emergency_contact',
                    'location' => [
                        'latitude' => 41.2995,
                        'longitude' => 69.2401,
                        'accuracy' => 10.5
                    ],
                    'location_address' => 'Amir Temur Street, Tashkent',
                    'user_message' => 'Need help immediately',
                    'device_info' => [
                        'model' => 'iPhone 14',
                        'os' => 'iOS 16.5'
                    ],
                    'context_data' => [
                        'date_id' => 'date_123',
                        'emergency_level' => 'high'
                    ]
                ]);

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'message',
                        'data' => [
                            'panic_id',
                            'emergency_contacts_notified'
                        ]
                    ])
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Emergency services have been notified',
                        'data' => [
                            'emergency_contacts_notified' => 3
                        ]
                    ]);

                $this->assertDatabaseHas('panic_activations', [
                    'user_id' => $this->user->id,
                    'trigger_type' => 'emergency_contact',
                    'status' => 'active',
                    'location_latitude' => 41.2995,
                    'location_longitude' => 69.2401,
                    'location_address' => 'Amir Temur Street, Tashkent',
                    'user_message' => 'Need help immediately'
                ]);

                // Check notifications were sent
                Notification::assertSentTo(
                    $contacts,
                    PanicAlertNotification::class
                );
            });

            it('prevents multiple active panics', function () {
                // Create active panic
                PanicActivation::create([
                    'user_id' => $this->user->id,
                    'trigger_type' => 'emergency_contact',
                    'status' => 'active',
                    'triggered_at' => now()
                ]);

                $response = $this->postJson('/api/v1/safety/panic/activate', [
                    'trigger_type' => 'emergency_contact',
                    'location' => [
                        'latitude' => 41.2995,
                        'longitude' => 69.2401
                    ]
                ]);

                $this->assertApiError($response, 'DUPLICATE_ENTRY', 409);
            });

            it('validates required fields', function () {
                $response = $this->postJson('/api/v1/safety/panic/activate', []);

                $this->assertValidationError($response, ['trigger_type', 'location']);
            });

            it('requires verified emergency contacts', function () {
                // No emergency contacts
                $response = $this->postJson('/api/v1/safety/panic/activate', [
                    'trigger_type' => 'emergency_contact',
                    'location' => [
                        'latitude' => 41.2995,
                        'longitude' => 69.2401
                    ]
                ]);

                $this->assertApiError($response, 'FORBIDDEN', 403);
            });

            it('enforces rate limiting', function () {
                // Create emergency contact
                UserEmergencyContact::create([
                    'user_id' => $this->user->id,
                    'name' => 'Contact',
                    'phone' => '+998901234567',
                    'relationship' => 'friend',
                    'is_verified' => true,
                    'receives_panic_alerts' => true
                ]);

                // Make 2 requests (the limit)
                for ($i = 0; $i < 2; $i++) {
                    $this->postJson('/api/v1/safety/panic/activate', [
                        'trigger_type' => 'emergency_contact',
                        'location' => [
                            'latitude' => 41.2995,
                            'longitude' => 69.2401
                        ]
                    ]);
                    
                    // Cancel it to allow next activation
                    if ($i < 1) {
                        $panic = PanicActivation::where('user_id', $this->user->id)
                            ->where('status', 'active')
                            ->first();
                        $panic->update(['status' => 'cancelled']);
                    }
                }

                // The 3rd request should be rate limited
                $response = $this->postJson('/api/v1/safety/panic/activate', [
                    'trigger_type' => 'emergency_contact',
                    'location' => [
                        'latitude' => 41.2995,
                        'longitude' => 69.2401
                    ]
                ]);

                $response->assertStatus(429);
            });
        });

        describe('POST /api/v1/safety/panic/cancel', function () {
            beforeEach(function () {
                $this->activePanic = PanicActivation::create([
                    'user_id' => $this->user->id,
                    'trigger_type' => 'emergency_contact',
                    'status' => 'active',
                    'triggered_at' => now(),
                    'location_latitude' => 41.2995,
                    'location_longitude' => 69.2401
                ]);

                // Create emergency contacts
                for ($i = 0; $i < 2; $i++) {
                    UserEmergencyContact::create([
                        'user_id' => $this->user->id,
                        'name' => "Contact $i",
                        'phone' => "+99890123456$i",
                        'email' => "contact$i@example.com",
                        'relationship' => 'friend',
                        'is_verified' => true,
                        'receives_panic_alerts' => true
                    ]);
                }
            });

            it('cancels active panic', function () {
                $response = $this->postJson('/api/v1/safety/panic/cancel', [
                    'reason' => 'False alarm - I\'m safe now'
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Emergency cancelled. Contacts have been notified.'
                    ]);

                $this->assertDatabaseHas('panic_activations', [
                    'id' => $this->activePanic->id,
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => 'False alarm - I\'m safe now'
                ]);
            });

            it('requires active panic to cancel', function () {
                $this->activePanic->update(['status' => 'resolved']);

                $response = $this->postJson('/api/v1/safety/panic/cancel', [
                    'reason' => 'Trying to cancel'
                ]);

                $this->assertApiError($response, 'NOT_FOUND', 404);
            });

            it('validates cancellation reason', function () {
                $response = $this->postJson('/api/v1/safety/panic/cancel', []);

                $this->assertValidationError($response, ['reason']);
            });
        });

        describe('GET /api/v1/safety/panic/status', function () {
            it('returns panic status when no active panic', function () {
                $response = $this->getJson('/api/v1/safety/panic/status');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            'panic_active',
                            'active_panic',
                            'safety_settings' => [
                                'panic_button_enabled',
                                'emergency_contacts_enabled',
                                'check_in_interval_minutes'
                            ],
                            'emergency_contacts_count',
                            'verified_contacts_count',
                            'setup_complete'
                        ]
                    ])
                    ->assertJson([
                        'status' => 'success',
                        'data' => [
                            'panic_active' => false,
                            'active_panic' => null
                        ]
                    ]);
            });

            it('returns active panic details', function () {
                $activePanic = PanicActivation::create([
                    'user_id' => $this->user->id,
                    'trigger_type' => 'emergency_contact',
                    'status' => 'active',
                    'triggered_at' => now(),
                    'location_latitude' => 41.2995,
                    'location_longitude' => 69.2401,
                    'location_address' => 'Test Address'
                ]);

                $response = $this->getJson('/api/v1/safety/panic/status');

                $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'panic_active' => true,
                            'active_panic' => [
                                'id' => $activePanic->id,
                                'trigger_type' => 'emergency_contact',
                                'status' => 'active'
                            ]
                        ]
                    ]);
            });

            it('includes emergency contacts count', function () {
                // Create emergency contacts
                UserEmergencyContact::create([
                    'user_id' => $this->user->id,
                    'name' => 'Verified Contact',
                    'phone' => '+998901234567',
                    'relationship' => 'friend',
                    'is_verified' => true
                ]);

                UserEmergencyContact::create([
                    'user_id' => $this->user->id,
                    'name' => 'Unverified Contact',
                    'phone' => '+998901234568',
                    'relationship' => 'friend',
                    'is_verified' => false
                ]);

                $response = $this->getJson('/api/v1/safety/panic/status');

                $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'emergency_contacts_count' => 2,
                            'verified_contacts_count' => 1
                        ]
                    ]);
            });
        });
    });

    describe('Emergency Contacts', function () {
        describe('POST /api/v1/safety/emergency-contacts', function () {
            it('adds emergency contact successfully', function () {
                $response = $this->postJson('/api/v1/safety/emergency-contacts', [
                    'name' => 'John Smith',
                    'relationship' => 'parent',
                    'phone' => '+998901234567',
                    'email' => 'john@example.com',
                    'is_primary' => true,
                    'receives_panic_alerts' => true,
                    'receives_location_updates' => false,
                    'receives_date_check_ins' => true,
                    'priority_order' => 1
                ]);

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'message',
                        'data' => [
                            'contact' => [
                                'id',
                                'name',
                                'relationship',
                                'phone',
                                'is_verified',
                                'is_primary'
                            ]
                        ]
                    ])
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Emergency contact added. Verification code sent.',
                        'data' => [
                            'contact' => [
                                'name' => 'John Smith',
                                'relationship' => 'parent',
                                'phone' => '+998901234567',
                                'is_verified' => false,
                                'is_primary' => true
                            ]
                        ]
                    ]);

                $this->assertDatabaseHas('user_emergency_contacts', [
                    'user_id' => $this->user->id,
                    'name' => 'John Smith',
                    'relationship' => 'parent',
                    'phone' => '+998901234567',
                    'is_primary' => true
                ]);
            });

            it('validates required fields', function () {
                $response = $this->postJson('/api/v1/safety/emergency-contacts', []);

                $this->assertValidationError($response, ['name', 'phone']);
            });

            it('validates phone format', function () {
                $response = $this->postJson('/api/v1/safety/emergency-contacts', [
                    'name' => 'Test',
                    'phone' => 'invalid-phone'
                ]);

                $this->assertValidationError($response, ['phone']);
            });

            it('validates email format if provided', function () {
                $response = $this->postJson('/api/v1/safety/emergency-contacts', [
                    'name' => 'Test',
                    'phone' => '+998901234567',
                    'email' => 'invalid-email'
                ]);

                $this->assertValidationError($response, ['email']);
            });

            it('enforces maximum contacts limit', function () {
                // Create 5 emergency contacts (assuming 5 is the limit)
                for ($i = 0; $i < 5; $i++) {
                    UserEmergencyContact::create([
                        'user_id' => $this->user->id,
                        'name' => "Contact $i",
                        'phone' => "+99890123456$i",
                        'relationship' => 'friend'
                    ]);
                }

                $response = $this->postJson('/api/v1/safety/emergency-contacts', [
                    'name' => 'One Too Many',
                    'phone' => '+998901234566'
                ]);

                $this->assertApiError($response, 'FORBIDDEN', 403);
            });

            it('prevents duplicate phone numbers', function () {
                UserEmergencyContact::create([
                    'user_id' => $this->user->id,
                    'name' => 'Existing Contact',
                    'phone' => '+998901234567',
                    'relationship' => 'friend'
                ]);

                $response = $this->postJson('/api/v1/safety/emergency-contacts', [
                    'name' => 'Duplicate Contact',
                    'phone' => '+998901234567'
                ]);

                $this->assertApiError($response, 'DUPLICATE_ENTRY', 409);
            });

            it('enforces rate limiting', function () {
                // Make 5 requests (the limit)
                for ($i = 0; $i < 5; $i++) {
                    $this->postJson('/api/v1/safety/emergency-contacts', [
                        'name' => "Contact $i",
                        'phone' => "+99890123456$i"
                    ]);
                }

                // The 6th request should be rate limited
                $response = $this->postJson('/api/v1/safety/emergency-contacts', [
                    'name' => 'Rate Limited',
                    'phone' => '+998901234566'
                ]);

                $response->assertStatus(429);
            });
        });

        describe('GET /api/v1/safety/emergency-contacts', function () {
            beforeEach(function () {
                // Create emergency contacts
                $this->contacts = collect();
                for ($i = 0; $i < 3; $i++) {
                    $contact = UserEmergencyContact::create([
                        'user_id' => $this->user->id,
                        'name' => "Contact $i",
                        'relationship' => $i === 0 ? 'parent' : 'friend',
                        'phone' => "+99890123456$i",
                        'email' => "contact$i@example.com",
                        'is_verified' => $i < 2,
                        'is_primary' => $i === 0,
                        'priority_order' => $i + 1
                    ]);
                    $this->contacts->push($contact);
                }
            });

            it('returns emergency contacts', function () {
                $response = $this->getJson('/api/v1/safety/emergency-contacts');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'relationship',
                                'phone',
                                'email',
                                'is_verified',
                                'is_primary',
                                'receives_panic_alerts',
                                'receives_location_updates',
                                'receives_date_check_ins',
                                'priority_order',
                                'created_at'
                            ]
                        ]
                    ]);

                expect(count($response->json('data')))->toBe(3);
            });

            it('orders contacts by priority', function () {
                $response = $this->getJson('/api/v1/safety/emergency-contacts');

                $priorities = collect($response->json('data'))->pluck('priority_order');
                expect($priorities->toArray())->toBe([1, 2, 3]);
            });
        });

        describe('PUT /api/v1/safety/emergency-contacts/{contact}', function () {
            beforeEach(function () {
                $this->contact = UserEmergencyContact::create([
                    'user_id' => $this->user->id,
                    'name' => 'Original Name',
                    'phone' => '+998901234567',
                    'relationship' => 'friend',
                    'is_primary' => false,
                    'priority_order' => 2
                ]);
            });

            it('updates emergency contact', function () {
                $response = $this->putJson("/api/v1/safety/emergency-contacts/{$this->contact->id}", [
                    'name' => 'Updated Name',
                    'relationship' => 'parent',
                    'is_primary' => true,
                    'priority_order' => 1
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Emergency contact updated successfully'
                    ]);

                $this->assertDatabaseHas('user_emergency_contacts', [
                    'id' => $this->contact->id,
                    'name' => 'Updated Name',
                    'relationship' => 'parent',
                    'is_primary' => true,
                    'priority_order' => 1
                ]);
            });

            it('prevents updating other user contacts', function () {
                $otherUserContact = UserEmergencyContact::create([
                    'user_id' => $this->createUserWithCompleteProfile()->id,
                    'name' => 'Other User Contact',
                    'phone' => '+998901234568',
                    'relationship' => 'friend'
                ]);

                $response = $this->putJson("/api/v1/safety/emergency-contacts/{$otherUserContact->id}", [
                    'name' => 'Hacked Name'
                ]);

                $this->assertApiError($response, 'FORBIDDEN', 403);
            });

            it('handles primary contact switching', function () {
                // Create current primary contact
                $primaryContact = UserEmergencyContact::create([
                    'user_id' => $this->user->id,
                    'name' => 'Current Primary',
                    'phone' => '+998901234568',
                    'relationship' => 'friend',
                    'is_primary' => true
                ]);

                $response = $this->putJson("/api/v1/safety/emergency-contacts/{$this->contact->id}", [
                    'is_primary' => true
                ]);

                $response->assertStatus(200);

                // Check old primary is no longer primary
                $this->assertDatabaseHas('user_emergency_contacts', [
                    'id' => $primaryContact->id,
                    'is_primary' => false
                ]);

                // Check new primary is set
                $this->assertDatabaseHas('user_emergency_contacts', [
                    'id' => $this->contact->id,
                    'is_primary' => true
                ]);
            });
        });

        describe('DELETE /api/v1/safety/emergency-contacts/{contact}', function () {
            beforeEach(function () {
                $this->contact = UserEmergencyContact::create([
                    'user_id' => $this->user->id,
                    'name' => 'Contact to Delete',
                    'phone' => '+998901234567',
                    'relationship' => 'friend'
                ]);
            });

            it('deletes emergency contact', function () {
                $response = $this->deleteJson("/api/v1/safety/emergency-contacts/{$this->contact->id}");

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Emergency contact deleted successfully'
                    ]);

                $this->assertDatabaseMissing('user_emergency_contacts', [
                    'id' => $this->contact->id
                ]);
            });

            it('prevents deleting other user contacts', function () {
                $otherUserContact = UserEmergencyContact::create([
                    'user_id' => $this->createUserWithCompleteProfile()->id,
                    'name' => 'Other User Contact',
                    'phone' => '+998901234568',
                    'relationship' => 'friend'
                ]);

                $response = $this->deleteJson("/api/v1/safety/emergency-contacts/{$otherUserContact->id}");

                $this->assertApiError($response, 'FORBIDDEN', 403);
            });

            it('prevents deleting last verified contact during active panic', function () {
                // Create active panic
                PanicActivation::create([
                    'user_id' => $this->user->id,
                    'trigger_type' => 'emergency_contact',
                    'status' => 'active',
                    'triggered_at' => now()
                ]);

                // Make this the only verified contact
                $this->contact->update(['is_verified' => true]);

                $response = $this->deleteJson("/api/v1/safety/emergency-contacts/{$this->contact->id}");

                $this->assertApiError($response, 'FORBIDDEN', 403);
            });
        });
    });

    describe('Safety Tips', function () {
        describe('GET /api/v1/safety/tips', function () {
            it('returns safety tips', function () {
                $response = $this->getJson('/api/v1/safety/tips');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'content',
                                'category',
                                'icon'
                            ]
                        ]
                    ]);
            });

            it('filters tips by category', function () {
                $response = $this->getJson('/api/v1/safety/tips?category=first_date');

                $response->assertStatus(200);
                
                $tips = collect($response->json('data'));
                $tips->each(function ($tip) {
                    expect($tip['category'])->toBe('first_date');
                });
            });
        });
    });

    describe('User Blocking', function () {
        beforeEach(function () {
            $this->userToBlock = $this->createUserWithCompleteProfile();
        });

        describe('POST /api/v1/blocked-users', function () {
            it('blocks user successfully', function () {
                $response = $this->postJson('/api/v1/blocked-users', [
                    'user_id' => $this->userToBlock->id,
                    'reason' => 'Inappropriate behavior'
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'User blocked successfully'
                    ]);

                $this->assertDatabaseHas('blocked_users', [
                    'blocker_id' => $this->user->id,
                    'blocked_id' => $this->userToBlock->id,
                    'reason' => 'Inappropriate behavior'
                ]);
            });

            it('prevents blocking already blocked user', function () {
                UserBlock::create([
                    'blocker_id' => $this->user->id,
                    'blocked_id' => $this->userToBlock->id
                ]);

                $response = $this->postJson('/api/v1/blocked-users', [
                    'user_id' => $this->userToBlock->id
                ]);

                $this->assertApiError($response, 'DUPLICATE_ENTRY', 409);
            });

            it('prevents blocking self', function () {
                $response = $this->postJson('/api/v1/blocked-users', [
                    'user_id' => $this->user->id
                ]);

                $this->assertApiError($response, 'FORBIDDEN', 403);
            });

            it('enforces rate limiting', function () {
                // Create users to block
                $users = User::factory()->count(11)->create();

                // Make 10 requests (the limit)
                foreach ($users->take(10) as $user) {
                    $this->postJson('/api/v1/blocked-users', [
                        'user_id' => $user->id
                    ]);
                }

                // The 11th request should be rate limited
                $response = $this->postJson('/api/v1/blocked-users', [
                    'user_id' => $users->last()->id
                ]);

                $response->assertStatus(429);
            });
        });

        describe('GET /api/v1/blocked-users', function () {
            beforeEach(function () {
                // Create blocked users
                $this->blockedUsers = collect();
                for ($i = 0; $i < 3; $i++) {
                    $blockedUser = $this->createUserWithCompleteProfile();
                    UserBlock::create([
                        'user_id' => $this->user->id,
                        'blocked_user_id' => $blockedUser->id,
                        'reason' => "Reason $i",
                        'created_at' => now()->subDays($i)
                    ]);
                    $this->blockedUsers->push($blockedUser);
                }
            });

            it('returns blocked users', function () {
                $response = $this->getJson('/api/v1/blocked-users');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            '*' => [
                                'id',
                                'blocked_user' => [
                                    'id',
                                    'first_name',
                                    'profile_photo_url'
                                ],
                                'reason',
                                'blocked_at'
                            ]
                        ]
                    ]);

                expect(count($response->json('data')))->toBe(3);
            });

            it('anonymizes blocked user information', function () {
                $response = $this->getJson('/api/v1/blocked-users');

                $blockedUsers = collect($response->json('data'));
                $blockedUsers->each(function ($blockedUser) {
                    expect($blockedUser['blocked_user']['first_name'])->toBe('Anonymous');
                    expect($blockedUser['blocked_user']['profile_photo_url'])->toBeNull();
                });
            });
        });

        describe('DELETE /api/v1/blocked-users/{userId}', function () {
            beforeEach(function () {
                UserBlock::create([
                    'blocker_id' => $this->user->id,
                    'blocked_id' => $this->userToBlock->id
                ]);
            });

            it('unblocks user successfully', function () {
                $response = $this->deleteJson("/api/v1/blocked-users/{$this->userToBlock->id}");

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'User unblocked successfully'
                    ]);

                $this->assertDatabaseMissing('blocked_users', [
                    'blocker_id' => $this->user->id,
                    'blocked_id' => $this->userToBlock->id
                ]);
            });

            it('returns 404 for non-blocked user', function () {
                $nonBlockedUser = $this->createUserWithCompleteProfile();

                $response = $this->deleteJson("/api/v1/blocked-users/{$nonBlockedUser->id}");

                $this->assertApiError($response, 'NOT_FOUND', 404);
            });
        });
    });

    describe('User Reporting', function () {
        beforeEach(function () {
            $this->reportedUser = $this->createUserWithCompleteProfile();
        });

        describe('POST /api/v1/support/report', function () {
            it('reports user successfully', function () {
                $response = $this->postJson('/api/v1/support/report', [
                    'reported_id' => $this->reportedUser->id,
                    'reason' => 'inappropriate_photos',
                    'description' => 'User has inappropriate profile photos'
                ]);

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'message',
                        'data' => [
                            'report_id',
                            'submitted_at'
                        ]
                    ])
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Report submitted successfully'
                    ]);

                $this->assertDatabaseHas('user_reports', [
                    'reporter_id' => $this->user->id,
                    'reported_id' => $this->reportedUser->id,
                    'reason' => 'inappropriate_photos',
                    'description' => 'User has inappropriate profile photos',
                    'status' => 'pending'
                ]);
            });

            it('validates required fields', function () {
                $response = $this->postJson('/api/v1/support/report', []);

                $this->assertValidationError($response, ['reported_id', 'reason']);
            });

            it('validates reason from allowed values', function () {
                $response = $this->postJson('/api/v1/support/report', [
                    'reported_id' => $this->reportedUser->id,
                    'reason' => 'invalid_reason'
                ]);

                $this->assertValidationError($response, ['reason']);
            });

            it('prevents reporting self', function () {
                $response = $this->postJson('/api/v1/support/report', [
                    'reported_id' => $this->user->id,
                    'reason' => 'inappropriate_photos'
                ]);

                $this->assertApiError($response, 'FORBIDDEN', 403);
            });

            it('prevents duplicate reports', function () {
                UserReport::create([
                    'reporter_id' => $this->user->id,
                    'reported_id' => $this->reportedUser->id,
                    'reason' => 'inappropriate_photos',
                    'status' => 'pending'
                ]);

                $response = $this->postJson('/api/v1/support/report', [
                    'reported_id' => $this->reportedUser->id,
                    'reason' => 'inappropriate_photos'
                ]);

                $this->assertApiError($response, 'DUPLICATE_ENTRY', 409);
            });
        });
    });
});