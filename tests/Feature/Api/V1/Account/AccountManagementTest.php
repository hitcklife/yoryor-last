<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\MatchModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\EmailChangedNotification;
use App\Notifications\AccountDeletedNotification;
use App\Notifications\DataExportReadyNotification;

uses(RefreshDatabase::class);

describe('Account Management', function () {
    beforeEach(function () {
        Notification::fake();
        $this->user = $this->createUserWithCompleteProfile([
            'password' => Hash::make('currentpassword123')
        ]);
        Sanctum::actingAs($this->user);
    });

    describe('PUT /api/v1/account/password', function () {
        it('changes password successfully', function () {
            $response = $this->putJson('/api/v1/account/password', [
                'current_password' => 'currentpassword123',
                'new_password' => 'newpassword456',
                'new_password_confirmation' => 'newpassword456'
            ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'password_changed_at'
                    ]
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Password changed successfully'
                ]);

            // Verify password was changed
            $this->user->refresh();
            expect(Hash::check('newpassword456', $this->user->password))->toBeTrue();

            // Verify notification was sent
            Notification::assertSentTo($this->user, PasswordChangedNotification::class);
        });

        it('fails with incorrect current password', function () {
            $response = $this->putJson('/api/v1/account/password', [
                'current_password' => 'wrongpassword',
                'new_password' => 'newpassword456',
                'new_password_confirmation' => 'newpassword456'
            ]);

            $this->assertApiError($response, 'INVALID_CREDENTIALS', 401);
        });

        it('validates password confirmation', function () {
            $response = $this->putJson('/api/v1/account/password', [
                'current_password' => 'currentpassword123',
                'new_password' => 'newpassword456',
                'new_password_confirmation' => 'differentpassword'
            ]);

            $this->assertValidationError($response, ['new_password']);
        });

        it('validates password strength', function () {
            $response = $this->putJson('/api/v1/account/password', [
                'current_password' => 'currentpassword123',
                'new_password' => '123', // Too weak
                'new_password_confirmation' => '123'
            ]);

            $this->assertValidationError($response, ['new_password']);
        });

        it('prevents using same password', function () {
            $response = $this->putJson('/api/v1/account/password', [
                'current_password' => 'currentpassword123',
                'new_password' => 'currentpassword123',
                'new_password_confirmation' => 'currentpassword123'
            ]);

            $this->assertValidationError($response, ['new_password']);
        });

        it('enforces rate limiting', function () {
            // Make 3 requests (the limit)
            for ($i = 0; $i < 3; $i++) {
                $this->putJson('/api/v1/account/password', [
                    'current_password' => 'wrongpassword',
                    'new_password' => 'newpassword456',
                    'new_password_confirmation' => 'newpassword456'
                ]);
            }

            // The 4th request should be rate limited
            $response = $this->putJson('/api/v1/account/password', [
                'current_password' => 'currentpassword123',
                'new_password' => 'newpassword456',
                'new_password_confirmation' => 'newpassword456'
            ]);

            $response->assertStatus(429);
        });
    });

    describe('PUT /api/v1/account/email', function () {
        it('changes email successfully', function () {
            $response = $this->putJson('/api/v1/account/email', [
                'password' => 'currentpassword123',
                'new_email' => 'newemail@example.com'
            ]);

            $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Email change initiated. Please verify your new email address.'
                ]);

            // Should create email change request
            $this->assertDatabaseHas('email_change_requests', [
                'user_id' => $this->user->id,
                'new_email' => 'newemail@example.com',
                'status' => 'pending'
            ]);

            // Notification should be sent to new email
            Notification::assertSentTo($this->user, EmailChangedNotification::class);
        });

        it('fails with incorrect password', function () {
            $response = $this->putJson('/api/v1/account/email', [
                'password' => 'wrongpassword',
                'new_email' => 'newemail@example.com'
            ]);

            $this->assertApiError($response, 'INVALID_CREDENTIALS', 401);
        });

        it('validates email format', function () {
            $response = $this->putJson('/api/v1/account/email', [
                'password' => 'currentpassword123',
                'new_email' => 'invalid-email'
            ]);

            $this->assertValidationError($response, ['new_email']);
        });

        it('prevents using existing email', function () {
            User::factory()->create(['email' => 'taken@example.com']);

            $response = $this->putJson('/api/v1/account/email', [
                'password' => 'currentpassword123',
                'new_email' => 'taken@example.com'
            ]);

            $this->assertValidationError($response, ['new_email']);
        });

        it('prevents using current email', function () {
            $response = $this->putJson('/api/v1/account/email', [
                'password' => 'currentpassword123',
                'new_email' => $this->user->email
            ]);

            $this->assertValidationError($response, ['new_email']);
        });

        it('enforces rate limiting', function () {
            // Make 2 requests (the limit)
            for ($i = 0; $i < 2; $i++) {
                $this->putJson('/api/v1/account/email', [
                    'password' => 'currentpassword123',
                    'new_email' => "newemail$i@example.com"
                ]);
            }

            // The 3rd request should be rate limited
            $response = $this->putJson('/api/v1/account/email', [
                'password' => 'currentpassword123',
                'new_email' => 'anotheremail@example.com'
            ]);

            $response->assertStatus(429);
        });
    });

    describe('DELETE /api/v1/account', function () {
        it('deletes account successfully', function () {
            $response = $this->deleteJson('/api/v1/account', [
                'password' => 'currentpassword123',
                'reason' => 'No longer using the app'
            ]);

            $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Account deletion scheduled. Your account will be permanently deleted in 30 days.'
                ]);

            // Account should be marked for deletion
            $this->assertDatabaseHas('users', [
                'id' => $this->user->id,
                'deleted_at' => now()
            ]);

            // Notification should be sent
            Notification::assertSentTo($this->user, AccountDeletedNotification::class);
        });

        it('fails with incorrect password', function () {
            $response = $this->deleteJson('/api/v1/account', [
                'password' => 'wrongpassword',
                'reason' => 'No longer using the app'
            ]);

            $this->assertApiError($response, 'INVALID_CREDENTIALS', 401);
        });

        it('validates required fields', function () {
            $response = $this->deleteJson('/api/v1/account', []);

            $this->assertValidationError($response, ['password', 'reason']);
        });

        it('anonymizes user data on deletion', function () {
            // Create some related data
            $match = MatchModel::create([
                'user_id' => $this->user->id,
                'matched_user_id' => $this->createUserWithCompleteProfile()->id,
                'matched_at' => now()
            ]);

            $response = $this->deleteJson('/api/v1/account', [
                'password' => 'currentpassword123',
                'reason' => 'Privacy concerns'
            ]);

            $response->assertStatus(200);

            // Check profile is anonymized
            $this->assertDatabaseHas('profiles', [
                'user_id' => $this->user->id,
                'first_name' => 'Deleted',
                'last_name' => 'User',
                'bio' => null
            ]);

            // Check matches are soft deleted
            $this->assertSoftDeleted('matches', [
                'id' => $match->id
            ]);
        });

        it('enforces rate limiting', function () {
            // Only 1 request allowed per 24 hours
            $response = $this->deleteJson('/api/v1/account', [
                'password' => 'currentpassword123',
                'reason' => 'Testing'
            ]);

            $response->assertStatus(200);

            // Second request should be rate limited
            $response = $this->deleteJson('/api/v1/account', [
                'password' => 'currentpassword123',
                'reason' => 'Testing again'
            ]);

            $response->assertStatus(429);
        });
    });

    describe('POST /api/v1/account/export-data', function () {
        it('requests data export successfully', function () {
            $response = $this->postJson('/api/v1/account/export-data', [
                'email' => 'user@example.com',
                'include_messages' => true,
                'include_photos' => false
            ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'request_id',
                        'estimated_completion'
                    ]
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Data export request submitted'
                ]);

            // Export request should be created
            $this->assertDatabaseHas('data_export_requests', [
                'user_id' => $this->user->id,
                'email' => 'user@example.com',
                'include_messages' => true,
                'include_photos' => false,
                'status' => 'pending'
            ]);
        });

        it('validates email format', function () {
            $response = $this->postJson('/api/v1/account/export-data', [
                'email' => 'invalid-email',
                'include_messages' => true
            ]);

            $this->assertValidationError($response, ['email']);
        });

        it('uses user email if not provided', function () {
            $response = $this->postJson('/api/v1/account/export-data', [
                'include_messages' => true,
                'include_photos' => true
            ]);

            $response->assertStatus(200);

            $this->assertDatabaseHas('data_export_requests', [
                'user_id' => $this->user->id,
                'email' => $this->user->email
            ]);
        });

        it('prevents multiple pending requests', function () {
            // Create pending request
            \App\Models\DataExportRequest::create([
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'status' => 'pending'
            ]);

            $response = $this->postJson('/api/v1/account/export-data', [
                'email' => 'user@example.com'
            ]);

            $this->assertApiError($response, 'DUPLICATE_ENTRY', 409);
        });

        it('enforces rate limiting', function () {
            // Make 2 requests (the limit)
            for ($i = 0; $i < 2; $i++) {
                $this->postJson('/api/v1/account/export-data', [
                    'email' => "user$i@example.com"
                ]);
                
                // Mark as completed to allow next request
                \App\Models\DataExportRequest::where('user_id', $this->user->id)
                    ->update(['status' => 'completed']);
            }

            // The 3rd request should be rate limited
            $response = $this->postJson('/api/v1/account/export-data', [
                'email' => 'another@example.com'
            ]);

            $response->assertStatus(429);
        });
    });

    describe('Device Token Management', function () {
        describe('POST /api/v1/device-tokens', function () {
            it('stores device token successfully', function () {
                $response = $this->postJson('/api/v1/device-tokens', [
                    'token' => 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]',
                    'device_type' => 'PHONE'
                ]);

                $response->assertStatus(201)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Device token saved successfully'
                    ]);

                $this->assertDatabaseHas('device_tokens', [
                    'user_id' => $this->user->id,
                    'token' => 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]',
                    'device_type' => 'PHONE'
                ]);
            });

            it('updates existing device token', function () {
                // Create existing token
                \App\Models\DeviceToken::create([
                    'user_id' => $this->user->id,
                    'token' => 'old-token',
                    'device_type' => 'PHONE'
                ]);

                $response = $this->postJson('/api/v1/device-tokens', [
                    'token' => 'new-token',
                    'device_type' => 'PHONE'
                ]);

                $response->assertStatus(200);

                // Old token should be replaced
                $this->assertDatabaseMissing('device_tokens', [
                    'user_id' => $this->user->id,
                    'token' => 'old-token'
                ]);

                $this->assertDatabaseHas('device_tokens', [
                    'user_id' => $this->user->id,
                    'token' => 'new-token'
                ]);
            });

            it('validates required fields', function () {
                $response = $this->postJson('/api/v1/device-tokens', []);

                $this->assertValidationError($response, ['token', 'device_type']);
            });

            it('validates device type', function () {
                $response = $this->postJson('/api/v1/device-tokens', [
                    'token' => 'test-token',
                    'device_type' => 'invalid'
                ]);

                $this->assertValidationError($response, ['device_type']);
            });
        });

        describe('DELETE /api/v1/device-tokens', function () {
            beforeEach(function () {
                $this->deviceToken = \App\Models\DeviceToken::create([
                    'user_id' => $this->user->id,
                    'token' => 'test-token',
                    'device_type' => 'PHONE'
                ]);
            });

            it('deletes device token successfully', function () {
                $response = $this->deleteJson('/api/v1/device-tokens', [
                    'token' => 'test-token'
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Device token removed successfully'
                    ]);

                $this->assertDatabaseMissing('device_tokens', [
                    'id' => $this->deviceToken->id
                ]);
            });

            it('deletes by token', function () {
                $response = $this->deleteJson('/api/v1/device-tokens', [
                    'token' => 'test-token'
                ]);

                $response->assertStatus(200);

                $this->assertDatabaseMissing('device_tokens', [
                    'id' => $this->deviceToken->id
                ]);
            });

            it('requires either token or device_id', function () {
                $response = $this->deleteJson('/api/v1/device-tokens', []);

                $this->assertValidationError($response, ['token']);
            });

            it('returns success even if token not found', function () {
                $response = $this->deleteJson('/api/v1/device-tokens', [
                    'token' => 'non-existent-token'
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Device token removed successfully'
                    ]);
            });
        });
    });
});