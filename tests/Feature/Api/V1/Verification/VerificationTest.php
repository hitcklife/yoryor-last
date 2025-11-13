<?php

namespace Tests\Feature\Api\V1\Verification;

use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

describe('Verification Status', function () {
    describe('GET /api/v1/verification/status', function () {
        it('returns verification status for authenticated user', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->getJson('/api/v1/verification/status');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data'
                ]);
        });

        it('includes verification types in status', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            // Create some verification requests
            VerificationRequest::factory()->create([
                'user_id' => $user->id,
                'verification_type' => 'identity',
                'status' => 'approved'
            ]);

            $response = $this->getJson('/api/v1/verification/status');

            $response->assertSuccessful();
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/v1/verification/status');

            $response->assertStatus(401);
        });
    });
});

describe('Verification Requirements', function () {
    describe('GET /api/v1/verification/requirements/{type}', function () {
        it('returns requirements for identity verification', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->getJson('/api/v1/verification/requirements/identity');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'status',
                    'data'
                ]);
        });

        it('returns requirements for photo verification', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->getJson('/api/v1/verification/requirements/photo');

            $response->assertSuccessful();
        });

        it('returns requirements for employment verification', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->getJson('/api/v1/verification/requirements/employment');

            $response->assertSuccessful();
        });

        it('handles invalid verification type', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->getJson('/api/v1/verification/requirements/invalid-type');

            expect($response->status())->toBeIn([400, 404, 422]);
        });
    });
});

describe('Verification Submission', function () {
    describe('POST /api/v1/verification/submit', function () {
        it('submits identity verification with valid data', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $file = UploadedFile::fake()->image('id-card.jpg');

            $response = $this->postJson('/api/v1/verification/submit', [
                'verification_type' => 'identity',
                'submitted_data' => [
                    'id_number' => 'ID123456',
                    'id_type' => 'passport',
                    'full_name' => 'John Doe',
                    'date_of_birth' => '1990-01-01'
                ],
                'documents' => [$file],
                'user_notes' => 'This is my passport'
            ]);

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data'
                ]);

            $this->assertDatabaseHas('verification_requests', [
                'user_id' => $user->id,
                'verification_type' => 'identity'
            ]);
        });

        it('submits photo verification', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $file = UploadedFile::fake()->image('selfie.jpg');

            $response = $this->postJson('/api/v1/verification/submit', [
                'verification_type' => 'photo',
                'submitted_data' => [
                    'pose_type' => 'standard_selfie'
                ],
                'documents' => [$file]
            ]);

            $response->assertSuccessful();

            $this->assertDatabaseHas('verification_requests', [
                'user_id' => $user->id,
                'verification_type' => 'photo'
            ]);
        });

        it('submits employment verification', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $file = UploadedFile::fake()->create('employment-letter.pdf', 500);

            $response = $this->postJson('/api/v1/verification/submit', [
                'verification_type' => 'employment',
                'submitted_data' => [
                    'company_name' => 'Acme Corp',
                    'job_title' => 'Software Engineer',
                    'employer_email' => 'hr@acme.com'
                ],
                'documents' => [$file]
            ]);

            $response->assertSuccessful();
        });

        it('validates required fields', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->postJson('/api/v1/verification/submit', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['verification_type', 'submitted_data', 'documents']);
        });

        it('validates verification type enum', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $file = UploadedFile::fake()->image('doc.jpg');

            $response = $this->postJson('/api/v1/verification/submit', [
                'verification_type' => 'invalid-type',
                'submitted_data' => ['test' => 'data'],
                'documents' => [$file]
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['verification_type']);
        });

        it('requires at least one document', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->postJson('/api/v1/verification/submit', [
                'verification_type' => 'identity',
                'submitted_data' => ['test' => 'data'],
                'documents' => []
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['documents']);
        });

        it('validates document file size', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            // Create a file larger than 10MB
            $file = UploadedFile::fake()->create('large-file.pdf', 11000);

            $response = $this->postJson('/api/v1/verification/submit', [
                'verification_type' => 'identity',
                'submitted_data' => ['test' => 'data'],
                'documents' => [$file]
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['documents.0']);
        });

        it('prevents duplicate pending verification', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            // Create existing pending verification
            VerificationRequest::factory()->create([
                'user_id' => $user->id,
                'verification_type' => 'identity',
                'status' => 'pending'
            ]);

            $file = UploadedFile::fake()->image('id.jpg');

            $response = $this->postJson('/api/v1/verification/submit', [
                'verification_type' => 'identity',
                'submitted_data' => ['test' => 'data'],
                'documents' => [$file]
            ]);

            $response->assertStatus(422)
                ->assertJson([
                    'status' => 'error'
                ]);
        });

        it('allows resubmission after rejection', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            // Create rejected verification
            VerificationRequest::factory()->create([
                'user_id' => $user->id,
                'verification_type' => 'identity',
                'status' => 'rejected'
            ]);

            $file = UploadedFile::fake()->image('id.jpg');

            $response = $this->postJson('/api/v1/verification/submit', [
                'verification_type' => 'identity',
                'submitted_data' => ['test' => 'data'],
                'documents' => [$file]
            ]);

            $response->assertSuccessful();
        });

        it('enforces rate limiting', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $file = UploadedFile::fake()->image('doc.jpg');

            // Make maximum allowed requests
            for ($i = 0; $i < 3; $i++) {
                $this->postJson('/api/v1/verification/submit', [
                    'verification_type' => 'photo',
                    'submitted_data' => ['test' => "data{$i}"],
                    'documents' => [$file]
                ]);

                // Delete the created request to allow next submission
                VerificationRequest::where('user_id', $user->id)->delete();
            }

            // Next request should be rate limited
            $response = $this->postJson('/api/v1/verification/submit', [
                'verification_type' => 'identity',
                'submitted_data' => ['test' => 'data'],
                'documents' => [$file]
            ]);

            $response->assertStatus(429);
        });
    });
});

describe('Verification Requests List', function () {
    describe('GET /api/v1/verification/requests', function () {
        it('returns all verification requests for user', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            // Create multiple verification requests
            VerificationRequest::factory()->count(3)->create([
                'user_id' => $user->id
            ]);

            $response = $this->getJson('/api/v1/verification/requests');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'status',
                    'data'
                ]);
        });

        it('only returns requests for authenticated user', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            Sanctum::actingAs($user1);

            VerificationRequest::factory()->create(['user_id' => $user1->id]);
            VerificationRequest::factory()->create(['user_id' => $user2->id]);

            $response = $this->getJson('/api/v1/verification/requests');

            $response->assertSuccessful();
        });

        it('returns empty array when user has no requests', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->getJson('/api/v1/verification/requests');

            $response->assertSuccessful();
        });
    });
});

describe('Single Verification Request', function () {
    describe('GET /api/v1/verification/requests/{verificationRequest}', function () {
        it('returns details of specific verification request', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $verificationRequest = VerificationRequest::factory()->create([
                'user_id' => $user->id,
                'verification_type' => 'identity',
                'status' => 'pending'
            ]);

            $response = $this->getJson("/api/v1/verification/requests/{$verificationRequest->id}");

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'status',
                    'data'
                ]);
        });

        it('forbids access to other users verification requests', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            Sanctum::actingAs($user1);

            $verificationRequest = VerificationRequest::factory()->create([
                'user_id' => $user2->id
            ]);

            $response = $this->getJson("/api/v1/verification/requests/{$verificationRequest->id}");

            $response->assertStatus(403);
        });

        it('returns 404 for non-existent request', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->getJson('/api/v1/verification/requests/99999');

            $response->assertStatus(404);
        });
    });
});

describe('Admin Verification Management', function () {
    describe('GET /api/v1/verification/admin/pending', function () {
        it('returns pending verification requests for admin', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            Sanctum::actingAs($admin);

            VerificationRequest::factory()->count(5)->create(['status' => 'pending']);
            VerificationRequest::factory()->count(3)->create(['status' => 'approved']);

            $response = $this->getJson('/api/v1/verification/admin/pending');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'status',
                    'data'
                ]);
        });

        it('requires admin authentication', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->getJson('/api/v1/verification/admin/pending');

            expect($response->status())->toBeIn([401, 403]);
        });
    });

    describe('POST /api/v1/verification/admin/{verificationRequest}/approve', function () {
        it('allows admin to approve verification request', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $user = User::factory()->create();
            Sanctum::actingAs($admin);

            $verificationRequest = VerificationRequest::factory()->create([
                'user_id' => $user->id,
                'status' => 'pending'
            ]);

            $response = $this->postJson("/api/v1/verification/admin/{$verificationRequest->id}/approve", [
                'admin_notes' => 'Documents verified successfully'
            ]);

            $response->assertSuccessful();

            $this->assertDatabaseHas('verification_requests', [
                'id' => $verificationRequest->id,
                'status' => 'approved'
            ]);
        });

        it('requires admin role', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $verificationRequest = VerificationRequest::factory()->create([
                'status' => 'pending'
            ]);

            $response = $this->postJson("/api/v1/verification/admin/{$verificationRequest->id}/approve");

            expect($response->status())->toBeIn([401, 403]);
        });
    });

    describe('POST /api/v1/verification/admin/{verificationRequest}/reject', function () {
        it('allows admin to reject verification request', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $user = User::factory()->create();
            Sanctum::actingAs($admin);

            $verificationRequest = VerificationRequest::factory()->create([
                'user_id' => $user->id,
                'status' => 'pending'
            ]);

            $response = $this->postJson("/api/v1/verification/admin/{$verificationRequest->id}/reject", [
                'rejection_reason' => 'Documents are not clear',
                'admin_notes' => 'Please resubmit with better quality images'
            ]);

            $response->assertSuccessful();

            $this->assertDatabaseHas('verification_requests', [
                'id' => $verificationRequest->id,
                'status' => 'rejected'
            ]);
        });

        it('validates rejection reason is provided', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            Sanctum::actingAs($admin);

            $verificationRequest = VerificationRequest::factory()->create([
                'status' => 'pending'
            ]);

            $response = $this->postJson("/api/v1/verification/admin/{$verificationRequest->id}/reject", []);

            expect($response->status())->toBeIn([400, 422]);
        });
    });
});
