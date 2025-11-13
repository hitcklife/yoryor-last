<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MatchModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('Chat System', function () {
    beforeEach(function () {
        Storage::fake('public');
        $this->user = $this->createUserWithCompleteProfile();
        $this->otherUser = $this->createUserWithCompleteProfile();
        
        // Create a match between users
        $this->match = MatchModel::create([
            'user_id' => $this->user->id,
            'matched_user_id' => $this->otherUser->id
        ]);
        
        Sanctum::actingAs($this->user);
    });

    describe('GET /api/v1/chats', function () {
        beforeEach(function () {
            // Create chats with different users
            $this->chats = collect();
            
            for ($i = 0; $i < 3; $i++) {
                $participant = $this->createUserWithCompleteProfile();
                // Create match first
                MatchModel::create([
                    'user_id' => $this->user->id,
                    'matched_user_id' => $participant->id
                ]);
                
                $chat = Chat::create([
                    'user1_id' => min($this->user->id, $participant->id),
                    'user2_id' => max($this->user->id, $participant->id)
                ]);
                
                // Add messages
                $message = Message::create([
                    'chat_id' => $chat->id,
                    'sender_id' => $participant->id,
                    'content' => "Hello from user $i",
                    'sent_at' => now()->subMinutes($i)
                ]);
                
                $this->chats->push([
                    'chat' => $chat,
                    'participant' => $participant,
                    'last_message' => $message
                ]);
            }
        });

        it('returns user chats', function () {
            $response = $this->getJson('/api/v1/chats');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'chats' => [
                            '*' => [
                            'id',
                            'participants' => [
                                '*' => [
                                    'id',
                                    'first_name',
                                    'profile_photo_url',
                                    'is_online',
                                    'last_active_at'
                                ]
                            ],
                            'last_message' => [
                                'id',
                                'content',
                                'sent_at',
                                'sender_id',
                                'message_type',
                                'read_at'
                            ],
                            'unread_count',
                            'updated_at'
                            ]
                        ],
                        'pagination' => [
                            'total',
                            'per_page',
                            'current_page',
                            'last_page'
                        ]
                    ]
                ]);
        });

        it('orders chats by last message time', function () {
            $response = $this->getJson('/api/v1/chats');

            $response->assertStatus(200);
            $chats = collect($response->json('data.chats'));
            
            $updatedDates = $chats->pluck('updated_at');
            expect($updatedDates)->toBe($updatedDates->sort()->reverse()->values()->toArray());
        });

        it('includes unread message count', function () {
            $chat = $this->chats->first()['chat'];
            
            // Add unread messages
            for ($i = 0; $i < 3; $i++) {
                Message::create([
                    'chat_id' => $chat->id,
                    'sender_id' => $this->chats->first()['participant']->id,
                    'content' => "Unread message $i",
                    'sent_at' => now(),
                    'read_at' => null
                ]);
            }

            $response = $this->getJson('/api/v1/chats');

            $chatData = collect($response->json('data'))->firstWhere('id', $chat->id);
            expect($chatData['unread_count'])->toBe(3);
        });

        it('excludes chats with blocked users', function () {
            $blockedParticipant = $this->chats->first()['participant'];
            $this->user->blockedUsers()->create(['blocked_id' => $blockedParticipant->id]);

            $response = $this->getJson('/api/v1/chats');

            $participantIds = collect($response->json('data'))
                ->pluck('participants')
                ->flatten(1)
                ->pluck('id');
                
            expect($participantIds->contains($blockedParticipant->id))->toBeFalse();
        });
    });

    describe('GET /api/v1/chats/{id}', function () {
        beforeEach(function () {
            $this->chat = Chat::create([
                'user1_id' => min($this->user->id, $this->otherUser->id),
                'user2_id' => max($this->user->id, $this->otherUser->id)
            ]);
            
            // Create messages
            for ($i = 0; $i < 55; $i++) {
                Message::create([
                    'chat_id' => $this->chat->id,
                    'sender_id' => $i % 2 === 0 ? $this->user->id : $this->otherUser->id,
                    'content' => "Message $i",
                    'sent_at' => now()->subMinutes(60 - $i)
                ]);
            }
        });

        it('returns chat with messages', function () {
            $response = $this->getJson("/api/v1/chats/{$this->chat->id}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'chat' => [
                            'id',
                            'participants'
                        ],
                        'messages' => [
                            '*' => [
                                'id',
                                'content',
                                'message_type',
                                'sender_id',
                                'sent_at',
                                'read_at',
                                'edited_at',
                                'media'
                            ]
                        ]
                    ],
                    'pagination' => [
                        'total',
                        'per_page',
                        'current_page',
                        'last_page',
                        'has_more_pages'
                    ]
                ]);
        });

        it('paginates messages', function () {
            $response = $this->getJson("/api/v1/chats/{$this->chat->id}?page=1&per_page=20");

            $response->assertStatus(200);
            $messages = $response->json('data.messages');
            $pagination = $response->json('pagination');
            
            expect(count($messages))->toBe(20);
            expect($pagination['total'])->toBe(55);
            expect($pagination['per_page'])->toBe(20);
            expect($pagination['has_more_pages'])->toBeTrue();
        });

        it('prevents access to chat user is not part of', function () {
            $otherChat = Chat::create([
                'user1_id' => $this->otherUser->id,
                'user2_id' => $this->createUserWithCompleteProfile()->id
            ]);

            $response = $this->getJson("/api/v1/chats/{$otherChat->id}");

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });

        it('returns 404 for non-existent chat', function () {
            $response = $this->getJson('/api/v1/chats/99999');

            $this->assertApiError($response, 'NOT_FOUND', 404);
        });
    });

    describe('POST /api/v1/chats/create', function () {
        it('creates or gets existing chat with matched user', function () {
            $response = $this->postJson('/api/v1/chats/create', [
                'user_id' => $this->otherUser->id
            ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'chat' => [
                            'id',
                            'participants'
                        ],
                        'created'
                    ]
                ]);

            $this->assertDatabaseHas('chats', [
                'user1_id' => min($this->user->id, $this->otherUser->id),
                'user2_id' => max($this->user->id, $this->otherUser->id)
            ]);
        });

        it('returns existing chat if already exists', function () {
            $existingChat = Chat::create([
                'user1_id' => min($this->user->id, $this->otherUser->id),
                'user2_id' => max($this->user->id, $this->otherUser->id)
            ]);

            $response = $this->postJson('/api/v1/chats/create', [
                'user_id' => $this->otherUser->id
            ]);

            $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'chat' => [
                            'id' => $existingChat->id
                        ],
                        'created' => false
                    ]
                ]);
        });

        it('prevents creating chat with unmatched user', function () {
            $unmatchedUser = $this->createUserWithCompleteProfile();

            $response = $this->postJson('/api/v1/chats/create', [
                'user_id' => $unmatchedUser->id
            ]);

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });

        it('prevents creating chat with blocked user', function () {
            $this->user->blockedUsers()->create(['blocked_id' => $this->otherUser->id]);

            $response = $this->postJson('/api/v1/chats/create', [
                'user_id' => $this->otherUser->id
            ]);

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });

        it('enforces rate limiting', function () {
            // Create multiple matched users
            $matchedUsers = collect();
            for ($i = 0; $i < 11; $i++) {
                $user = $this->createUserWithCompleteProfile();
                MatchModel::create([
                    'user1_id' => min($this->user->id, $user->id),
                    'user2_id' => max($this->user->id, $user->id),
                    'matched_at' => now()
                ]);
                $matchedUsers->push($user);
            }

            // Make 10 requests (the limit)
            foreach ($matchedUsers->take(10) as $user) {
                $this->postJson('/api/v1/chats/create', [
                    'user_id' => $user->id
                ]);
            }

            // The 11th request should be rate limited
            $response = $this->postJson('/api/v1/chats/create', [
                'user_id' => $matchedUsers->last()->id
            ]);

            $response->assertStatus(429);
        });
    });

    describe('POST /api/v1/chats/{id}/messages', function () {
        beforeEach(function () {
            $this->chat = Chat::create([
                'user1_id' => min($this->user->id, $this->otherUser->id),
                'user2_id' => max($this->user->id, $this->otherUser->id)
            ]);
        });

        it('sends text message successfully', function () {
            $response = $this->postJson("/api/v1/chats/{$this->chat->id}/messages", [
                'content' => 'Hello! How are you today?',
                'message_type' => 'text'
            ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'content',
                        'message_type',
                        'sender_id',
                        'sent_at',
                        'read_at'
                    ]
                ])
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'content' => 'Hello! How are you today?',
                        'message_type' => 'text',
                        'sender_id' => $this->user->id
                    ]
                ]);

            $this->assertDatabaseHas('messages', [
                'chat_id' => $this->chat->id,
                'sender_id' => $this->user->id,
                'content' => 'Hello! How are you today?'
            ]);
        });

        it('sends image message successfully', function () {
            $response = $this->postJson("/api/v1/chats/{$this->chat->id}/messages", [
                'content' => 'Check out this photo!',
                'message_type' => 'image',
                'media' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAAAAAAAD/2wBD'
            ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'content',
                        'message_type',
                        'media' => [
                            'url',
                            'thumbnail_url',
                            'file_type',
                            'file_size'
                        ]
                    ]
                ])
                ->assertJson([
                    'data' => [
                        'message_type' => 'image'
                    ]
                ]);
        });

        it('validates message content', function () {
            $response = $this->postJson("/api/v1/chats/{$this->chat->id}/messages", [
                'content' => '',
                'message_type' => 'text'
            ]);

            $this->assertValidationError($response, ['content']);
        });

        it('validates message type', function () {
            $response = $this->postJson("/api/v1/chats/{$this->chat->id}/messages", [
                'content' => 'Test',
                'message_type' => 'invalid'
            ]);

            $this->assertValidationError($response, ['message_type']);
        });

        it('prevents sending message to chat user is not part of', function () {
            $otherChat = Chat::create([
                'user1_id' => $this->otherUser->id,
                'user2_id' => $this->createUserWithCompleteProfile()->id
            ]);

            $response = $this->postJson("/api/v1/chats/{$otherChat->id}/messages", [
                'content' => 'Unauthorized message',
                'message_type' => 'text'
            ]);

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });

        it('enforces rate limiting', function () {
            // Make 30 requests (the limit)
            for ($i = 0; $i < 30; $i++) {
                $this->postJson("/api/v1/chats/{$this->chat->id}/messages", [
                    'content' => "Message $i",
                    'message_type' => 'text'
                ]);
            }

            // The 31st request should be rate limited
            $response = $this->postJson("/api/v1/chats/{$this->chat->id}/messages", [
                'content' => 'Rate limited message',
                'message_type' => 'text'
            ]);

            $response->assertStatus(429);
        });
    });

    describe('POST /api/v1/chats/{id}/read', function () {
        beforeEach(function () {
            $this->chat = Chat::create([
                'user1_id' => min($this->user->id, $this->otherUser->id),
                'user2_id' => max($this->user->id, $this->otherUser->id)
            ]);
            
            // Create unread messages from other user
            $this->unreadMessages = collect();
            for ($i = 0; $i < 3; $i++) {
                $message = Message::create([
                    'chat_id' => $this->chat->id,
                    'sender_id' => $this->otherUser->id,
                    'content' => "Unread message $i",
                    'sent_at' => now()->subMinutes($i),
                    'read_at' => null
                ]);
                $this->unreadMessages->push($message);
            }
        });

        it('marks messages as read', function () {
            $messageIds = $this->unreadMessages->pluck('id')->toArray();

            $response = $this->postJson("/api/v1/chats/{$this->chat->id}/read", [
                'message_ids' => $messageIds
            ]);

            $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Messages marked as read',
                    'data' => [
                        'marked_count' => 3
                    ]
                ]);

            foreach ($messageIds as $messageId) {
                $this->assertDatabaseHas('messages', [
                    'id' => $messageId,
                    'read_at' => now()
                ]);
            }
        });

        it('marks all messages as read when no IDs provided', function () {
            $response = $this->postJson("/api/v1/chats/{$this->chat->id}/read", []);

            $response->assertStatus(200);
            
            $unreadCount = Message::where('chat_id', $this->chat->id)
                ->where('sender_id', '!=', $this->user->id)
                ->whereNull('read_at')
                ->count();
                
            expect($unreadCount)->toBe(0);
        });

        it('only marks messages from other user as read', function () {
            // Add message from current user
            $ownMessage = Message::create([
                'chat_id' => $this->chat->id,
                'sender_id' => $this->user->id,
                'content' => 'My own message',
                'sent_at' => now(),
                'read_at' => null
            ]);

            $response = $this->postJson("/api/v1/chats/{$this->chat->id}/read", []);

            $response->assertStatus(200);
            
            // Own message should still be unread
            $this->assertDatabaseHas('messages', [
                'id' => $ownMessage->id,
                'read_at' => null
            ]);
        });
    });

    describe('PUT /api/v1/chats/{chat_id}/messages/{message_id}', function () {
        beforeEach(function () {
            $this->chat = Chat::create([
                'user1_id' => min($this->user->id, $this->otherUser->id),
                'user2_id' => max($this->user->id, $this->otherUser->id)
            ]);
            
            $this->message = Message::create([
                'chat_id' => $this->chat->id,
                'sender_id' => $this->user->id,
                'content' => 'Original message',
                'sent_at' => now()->subMinutes(5)
            ]);
        });

        it('edits message successfully', function () {
            $response = $this->putJson("/api/v1/chats/{$this->chat->id}/messages/{$this->message->id}", [
                'content' => 'Updated message content'
            ]);

            $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Message updated successfully'
                ]);

            $this->assertDatabaseHas('messages', [
                'id' => $this->message->id,
                'content' => 'Updated message content',
                'edited_at' => now()
            ]);
        });

        it('prevents editing message from other user', function () {
            $otherMessage = Message::create([
                'chat_id' => $this->chat->id,
                'sender_id' => $this->otherUser->id,
                'content' => 'Other user message',
                'sent_at' => now()
            ]);

            $response = $this->putJson("/api/v1/chats/{$this->chat->id}/messages/{$otherMessage->id}", [
                'content' => 'Trying to edit'
            ]);

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });

        it('prevents editing message older than 15 minutes', function () {
            $oldMessage = Message::create([
                'chat_id' => $this->chat->id,
                'sender_id' => $this->user->id,
                'content' => 'Old message',
                'sent_at' => now()->subMinutes(20)
            ]);

            $response = $this->putJson("/api/v1/chats/{$this->chat->id}/messages/{$oldMessage->id}", [
                'content' => 'Trying to edit old message'
            ]);

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });

        it('validates new content', function () {
            $response = $this->putJson("/api/v1/chats/{$this->chat->id}/messages/{$this->message->id}", [
                'content' => ''
            ]);

            $this->assertValidationError($response, ['content']);
        });
    });

    describe('DELETE /api/v1/chats/{chat_id}/messages/{message_id}', function () {
        beforeEach(function () {
            $this->chat = Chat::create([
                'user1_id' => min($this->user->id, $this->otherUser->id),
                'user2_id' => max($this->user->id, $this->otherUser->id)
            ]);
            
            $this->message = Message::create([
                'chat_id' => $this->chat->id,
                'sender_id' => $this->user->id,
                'content' => 'Message to delete',
                'sent_at' => now()->subMinutes(5)
            ]);
        });

        it('deletes message successfully', function () {
            $response = $this->deleteJson("/api/v1/chats/{$this->chat->id}/messages/{$this->message->id}");

            $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Message deleted successfully'
                ]);

            $this->assertSoftDeleted('messages', [
                'id' => $this->message->id
            ]);
        });

        it('prevents deleting message from other user', function () {
            $otherMessage = Message::create([
                'chat_id' => $this->chat->id,
                'sender_id' => $this->otherUser->id,
                'content' => 'Other user message',
                'sent_at' => now()
            ]);

            $response = $this->deleteJson("/api/v1/chats/{$this->chat->id}/messages/{$otherMessage->id}");

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });

        it('prevents deleting message older than 15 minutes', function () {
            $oldMessage = Message::create([
                'chat_id' => $this->chat->id,
                'sender_id' => $this->user->id,
                'content' => 'Old message',
                'sent_at' => now()->subMinutes(20)
            ]);

            $response = $this->deleteJson("/api/v1/chats/{$this->chat->id}/messages/{$oldMessage->id}");

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });
    });

    describe('GET /api/v1/chats/unread-count', function () {
        beforeEach(function () {
            // Create multiple chats with unread messages
            for ($i = 0; $i < 3; $i++) {
                $participant = $this->createUserWithCompleteProfile();
                MatchModel::create([
                    'user_id' => $this->user->id,
                    'matched_user_id' => $participant->id
                ]);
                
                $chat = Chat::create([
                    'user1_id' => min($this->user->id, $participant->id),
                    'user2_id' => max($this->user->id, $participant->id)
                ]);
                
                // Add unread messages
                for ($j = 0; $j < $i + 1; $j++) {
                    Message::create([
                        'chat_id' => $chat->id,
                        'sender_id' => $participant->id,
                        'content' => "Unread message $j in chat $i",
                        'sent_at' => now(),
                        'read_at' => null
                    ]);
                }
            }
        });

        it('returns correct unread count', function () {
            $response = $this->getJson('/api/v1/chats/unread-count');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'total_unread',
                        'chats_with_unread'
                    ]
                ])
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'total_unread' => 6, // 1 + 2 + 3
                        'chats_with_unread' => 3
                    ]
                ]);
        });

        it('excludes messages from blocked users', function () {
            // Block one of the participants
            $blockedChat = Chat::where('user1_id', $this->user->id)
                ->orWhere('user2_id', $this->user->id)
                ->first();
            $blockedUserId = $blockedChat->user1_id === $this->user->id 
                ? $blockedChat->user2_id 
                : $blockedChat->user1_id;
                
            $this->user->blockedUsers()->create(['blocked_id' => $blockedUserId]);

            $response = $this->getJson('/api/v1/chats/unread-count');

            $response->assertStatus(200);
            // Should have fewer unread messages and chats
            expect($response->json('data.total_unread'))->toBeLessThan(6);
            expect($response->json('data.chats_with_unread'))->toBeLessThan(3);
        });
    });

    describe('DELETE /api/v1/chats/{id}', function () {
        beforeEach(function () {
            $this->chat = Chat::create([
                'user1_id' => min($this->user->id, $this->otherUser->id),
                'user2_id' => max($this->user->id, $this->otherUser->id)
            ]);
            
            // Add messages
            for ($i = 0; $i < 5; $i++) {
                Message::create([
                    'chat_id' => $this->chat->id,
                    'sender_id' => $i % 2 === 0 ? $this->user->id : $this->otherUser->id,
                    'content' => "Message $i",
                    'sent_at' => now()->subMinutes($i)
                ]);
            }
        });

        it('deletes chat successfully', function () {
            $response = $this->deleteJson("/api/v1/chats/{$this->chat->id}");

            $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Chat deleted successfully'
                ]);

            $this->assertSoftDeleted('chats', [
                'id' => $this->chat->id
            ]);
        });

        it('prevents deleting chat user is not part of', function () {
            $otherChat = Chat::create([
                'user1_id' => $this->otherUser->id,
                'user2_id' => $this->createUserWithCompleteProfile()->id
            ]);

            $response = $this->deleteJson("/api/v1/chats/{$otherChat->id}");

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });

        it('soft deletes associated messages', function () {
            $messageIds = Message::where('chat_id', $this->chat->id)->pluck('id');

            $response = $this->deleteJson("/api/v1/chats/{$this->chat->id}");

            $response->assertStatus(200);
            
            foreach ($messageIds as $messageId) {
                $this->assertSoftDeleted('messages', [
                    'id' => $messageId
                ]);
            }
        });
    });
});