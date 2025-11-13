<?php

namespace Tests\Feature\Api\V1\VideoCalling;

use App\Models\User;
use App\Models\Call;
use App\Models\Chat;
use App\Services\VideoSDKService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock VideoSDK Service to avoid actual API calls
    $this->videoSDKService = Mockery::mock(VideoSDKService::class);
    $this->app->instance(VideoSDKService::class, $this->videoSDKService);
});

afterEach(function () {
    Mockery::close();
});

describe('Video Call Token Generation', function () {
    describe('POST /api/v1/video-call/token', function () {
        it('generates a token for authenticated user', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            // Mock the token generation
            $this->videoSDKService->shouldReceive('generateToken')
                ->once()
                ->andReturn('mock-token-12345');

            $response = $this->postJson('/api/v1/video-call/token');

            $response->assertSuccessful()
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'token' => 'mock-token-12345'
                    ]
                ]);
        });

        it('requires authentication', function () {
            $response = $this->postJson('/api/v1/video-call/token');

            $response->assertStatus(401);
        });

        it('handles VideoSDK configuration error', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $this->videoSDKService->shouldReceive('generateToken')
                ->once()
                ->andThrow(new \Exception('VideoSDK is not configured'));

            $response = $this->postJson('/api/v1/video-call/token');

            $response->assertStatus(500)
                ->assertJson([
                    'status' => 'error'
                ]);
        });
    });
});

describe('Video Meeting Creation', function () {
    describe('POST /api/v1/video-call/create-meeting', function () {
        it('creates a meeting successfully', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $this->videoSDKService->shouldReceive('createMeeting')
                ->once()
                ->with(null)
                ->andReturn([
                    'meetingId' => 'meeting-abc-123',
                    'token' => 'mock-token-12345'
                ]);

            $response = $this->postJson('/api/v1/video-call/create-meeting');

            $response->assertSuccessful()
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'meetingId' => 'meeting-abc-123',
                        'token' => 'mock-token-12345'
                    ]
                ]);
        });

        it('creates a meeting with custom room ID', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $this->videoSDKService->shouldReceive('createMeeting')
                ->once()
                ->with('custom-room-id')
                ->andReturn([
                    'meetingId' => 'custom-room-id',
                    'token' => 'mock-token-12345'
                ]);

            $response = $this->postJson('/api/v1/video-call/create-meeting', [
                'customRoomId' => 'custom-room-id'
            ]);

            $response->assertSuccessful()
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'meetingId' => 'custom-room-id'
                    ]
                ]);
        });

        it('requires authentication', function () {
            $response = $this->postJson('/api/v1/video-call/create-meeting');

            $response->assertStatus(401);
        });
    });
});

describe('Video Meeting Validation', function () {
    describe('GET /api/v1/video-call/validate-meeting/{meetingId}', function () {
        it('validates an existing meeting', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $this->videoSDKService->shouldReceive('validateMeeting')
                ->once()
                ->with('meeting-abc-123')
                ->andReturn([
                    'valid' => true,
                    'meetingId' => 'meeting-abc-123'
                ]);

            $response = $this->getJson('/api/v1/video-call/validate-meeting/meeting-abc-123');

            $response->assertSuccessful()
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'valid' => true,
                        'meetingId' => 'meeting-abc-123'
                    ]
                ]);
        });

        it('returns invalid for non-existent meeting', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $this->videoSDKService->shouldReceive('validateMeeting')
                ->once()
                ->with('invalid-meeting')
                ->andReturn([
                    'valid' => false,
                    'meetingId' => 'invalid-meeting'
                ]);

            $response = $this->getJson('/api/v1/video-call/validate-meeting/invalid-meeting');

            $response->assertSuccessful()
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'valid' => false
                    ]
                ]);
        });
    });
});

describe('Call Initiation', function () {
    describe('POST /api/v1/video-call/initiate', function () {
        it('initiates a video call successfully', function () {
            $caller = User::factory()->create();
            $receiver = User::factory()->create();
            Sanctum::actingAs($caller);

            // Create a chat between users first (required for calls)
            $chat = Chat::factory()->create();
            $chat->users()->attach([$caller->id, $receiver->id]);

            // Mock the VideoSDK service
            $mockCall = Call::factory()->make([
                'id' => 1,
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'type' => 'video',
                'channel_name' => 'meeting-abc-123',
                'status' => 'initiated'
            ]);

            $this->videoSDKService->shouldReceive('createCall')
                ->once()
                ->andReturn([
                    'call' => $mockCall,
                    'token' => 'mock-token-12345'
                ]);

            $response = $this->postJson('/api/v1/video-call/initiate', [
                'recipient_id' => $receiver->id,
                'call_type' => 'video'
            ]);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'call_id',
                        'meeting_id',
                        'token',
                        'type',
                        'caller',
                        'receiver'
                    ]
                ]);
        });

        it('initiates a voice call successfully', function () {
            $caller = User::factory()->create();
            $receiver = User::factory()->create();
            Sanctum::actingAs($caller);

            $chat = Chat::factory()->create();
            $chat->users()->attach([$caller->id, $receiver->id]);

            $mockCall = Call::factory()->make([
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'type' => 'voice',
                'status' => 'initiated'
            ]);

            $this->videoSDKService->shouldReceive('createCall')
                ->once()
                ->andReturn([
                    'call' => $mockCall,
                    'token' => 'mock-token-12345'
                ]);

            $response = $this->postJson('/api/v1/video-call/initiate', [
                'recipient_id' => $receiver->id,
                'call_type' => 'voice'
            ]);

            $response->assertStatus(201);
        });

        it('fails when calling yourself', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->postJson('/api/v1/video-call/initiate', [
                'recipient_id' => $user->id,
                'call_type' => 'video'
            ]);

            $response->assertStatus(400)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Cannot call yourself'
                ]);
        });

        it('validates required fields', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->postJson('/api/v1/video-call/initiate', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['recipient_id', 'call_type']);
        });

        it('validates call_type enum values', function () {
            $user = User::factory()->create();
            $receiver = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->postJson('/api/v1/video-call/initiate', [
                'recipient_id' => $receiver->id,
                'call_type' => 'invalid-type'
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['call_type']);
        });

        it('requires recipient to exist', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            $response = $this->postJson('/api/v1/video-call/initiate', [
                'recipient_id' => 99999,
                'call_type' => 'video'
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['recipient_id']);
        });
    });
});

describe('Call Join', function () {
    describe('POST /api/v1/video-call/{callId}/join', function () {
        it('allows receiver to join initiated call', function () {
            $caller = User::factory()->create();
            $receiver = User::factory()->create();
            Sanctum::actingAs($receiver);

            $call = Call::factory()->create([
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'status' => 'initiated',
                'type' => 'video'
            ]);

            $this->videoSDKService->shouldReceive('updateCallStatus')
                ->once()
                ->with(Mockery::type(Call::class), 'ongoing')
                ->andReturn($call);

            $this->videoSDKService->shouldReceive('generateToken')
                ->once()
                ->andReturn('mock-token-12345');

            $response = $this->postJson("/api/v1/video-call/{$call->id}/join");

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'call_id',
                        'meeting_id',
                        'token',
                        'type',
                        'caller',
                        'receiver'
                    ]
                ]);
        });

        it('forbids non-receiver from joining call', function () {
            $caller = User::factory()->create();
            $receiver = User::factory()->create();
            $intruder = User::factory()->create();
            Sanctum::actingAs($intruder);

            $call = Call::factory()->create([
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'status' => 'initiated'
            ]);

            $response = $this->postJson("/api/v1/video-call/{$call->id}/join");

            $response->assertStatus(403)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Unauthorized to join this call'
                ]);
        });

        it('fails when call is not in initiated status', function () {
            $caller = User::factory()->create();
            $receiver = User::factory()->create();
            Sanctum::actingAs($receiver);

            $call = Call::factory()->create([
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'status' => 'completed'
            ]);

            $response = $this->postJson("/api/v1/video-call/{$call->id}/join");

            $response->assertStatus(400);
        });
    });
});

describe('Call End', function () {
    describe('POST /api/v1/video-call/{callId}/end', function () {
        it('allows caller to end ongoing call', function () {
            $caller = User::factory()->create();
            $receiver = User::factory()->create();
            Sanctum::actingAs($caller);

            $call = Call::factory()->create([
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'status' => 'ongoing',
                'started_at' => now()->subMinutes(5)
            ]);

            $this->videoSDKService->shouldReceive('updateCallStatus')
                ->once()
                ->with(Mockery::type(Call::class), 'completed')
                ->andReturn($call);

            $response = $this->postJson("/api/v1/video-call/{$call->id}/end");

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'message',
                        'call_id',
                        'duration'
                    ]
                ]);
        });

        it('allows receiver to end ongoing call', function () {
            $caller = User::factory()->create();
            $receiver = User::factory()->create();
            Sanctum::actingAs($receiver);

            $call = Call::factory()->create([
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'status' => 'ongoing'
            ]);

            $this->videoSDKService->shouldReceive('updateCallStatus')
                ->once()
                ->andReturn($call);

            $response = $this->postJson("/api/v1/video-call/{$call->id}/end");

            $response->assertSuccessful();
        });

        it('forbids non-participants from ending call', function () {
            $caller = User::factory()->create();
            $receiver = User::factory()->create();
            $intruder = User::factory()->create();
            Sanctum::actingAs($intruder);

            $call = Call::factory()->create([
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'status' => 'ongoing'
            ]);

            $response = $this->postJson("/api/v1/video-call/{$call->id}/end");

            $response->assertStatus(403);
        });
    });
});

describe('Call Reject', function () {
    describe('POST /api/v1/video-call/{callId}/reject', function () {
        it('allows receiver to reject initiated call', function () {
            $caller = User::factory()->create();
            $receiver = User::factory()->create();
            Sanctum::actingAs($receiver);

            $call = Call::factory()->create([
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'status' => 'initiated'
            ]);

            $this->videoSDKService->shouldReceive('updateCallStatus')
                ->once()
                ->with(Mockery::type(Call::class), 'rejected')
                ->andReturn($call);

            $response = $this->postJson("/api/v1/video-call/{$call->id}/reject");

            $response->assertSuccessful()
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'message' => 'Call rejected successfully'
                    ]
                ]);
        });

        it('forbids caller from rejecting their own call', function () {
            $caller = User::factory()->create();
            $receiver = User::factory()->create();
            Sanctum::actingAs($caller);

            $call = Call::factory()->create([
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'status' => 'initiated'
            ]);

            $response = $this->postJson("/api/v1/video-call/{$call->id}/reject");

            $response->assertStatus(403);
        });

        it('fails when call is not in initiated status', function () {
            $caller = User::factory()->create();
            $receiver = User::factory()->create();
            Sanctum::actingAs($receiver);

            $call = Call::factory()->create([
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'status' => 'ongoing'
            ]);

            $response = $this->postJson("/api/v1/video-call/{$call->id}/reject");

            $response->assertStatus(400);
        });
    });
});

describe('Call History', function () {
    describe('GET /api/v1/video-call/history', function () {
        it('returns call history for authenticated user', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            // Create some calls
            Call::factory()->count(3)->create(['caller_id' => $user->id]);
            Call::factory()->count(2)->create(['receiver_id' => $user->id]);

            $response = $this->getJson('/api/v1/video-call/history');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'status',
                    'data'
                ]);
        });

        it('filters call history by status', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            Call::factory()->create(['caller_id' => $user->id, 'status' => 'completed']);
            Call::factory()->create(['caller_id' => $user->id, 'status' => 'missed']);

            $response = $this->getJson('/api/v1/video-call/history?call_status=completed');

            $response->assertSuccessful();
        });

        it('filters call history by call type', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            Call::factory()->create(['caller_id' => $user->id, 'type' => 'video']);
            Call::factory()->create(['caller_id' => $user->id, 'type' => 'voice']);

            $response = $this->getJson('/api/v1/video-call/history?call_type=video');

            $response->assertSuccessful();
        });

        it('paginates results', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            Call::factory()->count(25)->create(['caller_id' => $user->id]);

            $response = $this->getJson('/api/v1/video-call/history?per_page=10');

            $response->assertSuccessful();
        });
    });
});

describe('Call Analytics', function () {
    describe('GET /api/v1/video-call/analytics', function () {
        it('returns call analytics for authenticated user', function () {
            $user = User::factory()->create();
            Sanctum::actingAs($user);

            // Create some calls with different statuses
            Call::factory()->count(5)->create(['caller_id' => $user->id, 'status' => 'completed']);
            Call::factory()->count(3)->create(['caller_id' => $user->id, 'status' => 'missed']);
            Call::factory()->count(2)->create(['receiver_id' => $user->id, 'status' => 'rejected']);

            $response = $this->getJson('/api/v1/video-call/analytics');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'status',
                    'data'
                ]);
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/v1/video-call/analytics');

            $response->assertStatus(401);
        });
    });
});
