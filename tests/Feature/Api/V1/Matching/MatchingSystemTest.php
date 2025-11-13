<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Like;
use App\Models\MatchModel;
use App\Models\Dislike;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('Matching System', function () {
    beforeEach(function () {
        $this->user = $this->createUserWithCompleteProfile();
        Sanctum::actingAs($this->user);
    });

    describe('GET /api/v1/matches/potential', function () {
        beforeEach(function () {
            // Create potential matches with different attributes
            $this->potentialMatches = collect();
            
            // User within age and distance preferences
            $this->potentialMatches->push($this->createUserWithCompleteProfile([
                'date_of_birth' => now()->subYears(25)->format('Y-m-d'),
                'gender' => $this->user->profile->gender === 'male' ? 'female' : 'male',
                'latitude' => $this->user->profile->latitude + 0.01,
                'longitude' => $this->user->profile->longitude + 0.01,
            ]));

            // User outside age preference
            $this->potentialMatches->push($this->createUserWithCompleteProfile([
                'date_of_birth' => now()->subYears(50)->format('Y-m-d'),
                'gender' => $this->user->profile->gender === 'male' ? 'female' : 'male',
            ]));

            // User outside distance preference
            $this->potentialMatches->push($this->createUserWithCompleteProfile([
                'date_of_birth' => now()->subYears(25)->format('Y-m-d'),
                'gender' => $this->user->profile->gender === 'male' ? 'female' : 'male',
                'latitude' => $this->user->profile->latitude + 10,
                'longitude' => $this->user->profile->longitude + 10,
            ]));

            // Already liked user
            $likedUser = $this->createUserWithCompleteProfile([
                'date_of_birth' => now()->subYears(25)->format('Y-m-d'),
                'gender' => $this->user->profile->gender === 'male' ? 'female' : 'male',
            ]);
            Like::create([
                'user_id' => $this->user->id,
                'liked_user_id' => $likedUser->id
            ]);

            // Already disliked user
            $dislikedUser = $this->createUserWithCompleteProfile([
                'date_of_birth' => now()->subYears(25)->format('Y-m-d'),
                'gender' => $this->user->profile->gender === 'male' ? 'female' : 'male',
            ]);
            Dislike::create([
                'user_id' => $this->user->id,
                'disliked_user_id' => $dislikedUser->id
            ]);
        });

        it('returns potential matches', function () {
            $response = $this->getJson('/api/v1/matches/potential');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => [
                            'id',
                            'first_name',
                            'age',
                            'distance_km',
                            'bio',
                            'photos',
                            'compatibility_score',
                            'common_interests',
                            'mutual_friends',
                            'last_active',
                            'verification_badges'
                        ]
                    ]
                ]);
        });

        it('filters by age preferences', function () {
            // Set user preferences
            $this->user->preference()->create([
                'min_age' => 22,
                'max_age' => 30
            ]);

            $response = $this->getJson('/api/v1/matches/potential');

            $response->assertStatus(200);
            $matches = collect($response->json('data'));
            
            $matches->each(function ($match) {
                expect($match['age'])->toBeGreaterThanOrEqual(22);
                expect($match['age'])->toBeLessThanOrEqual(30);
            });
        });

        it('filters by distance preferences', function () {
            // Set user preferences
            $this->user->preference()->create([
                'max_distance' => 50
            ]);

            $response = $this->getJson('/api/v1/matches/potential?distance_km=50');

            $response->assertStatus(200);
            $matches = collect($response->json('data'));
            
            $matches->each(function ($match) {
                expect($match['distance_km'])->toBeLessThanOrEqual(50);
            });
        });

        it('excludes already liked users', function () {
            $response = $this->getJson('/api/v1/matches/potential');

            $response->assertStatus(200);
            $matchIds = collect($response->json('data'))->pluck('id');
            
            $likedUserIds = Like::where('user_id', $this->user->id)->pluck('liked_user_id');
            
            $likedUserIds->each(function ($userId) use ($matchIds) {
                expect($matchIds->contains($userId))->toBeFalse();
            });
        });

        it('excludes already disliked users', function () {
            $response = $this->getJson('/api/v1/matches/potential');

            $response->assertStatus(200);
            $matchIds = collect($response->json('data'))->pluck('id');
            
            $dislikedUserIds = Dislike::where('user_id', $this->user->id)->pluck('disliked_user_id');
            
            $dislikedUserIds->each(function ($userId) use ($matchIds) {
                expect($matchIds->contains($userId))->toBeFalse();
            });
        });

        it('excludes blocked users', function () {
            $blockedUser = $this->createUserWithCompleteProfile();
            $this->user->blockedUsers()->create(['blocked_user_id' => $blockedUser->id]);

            $response = $this->getJson('/api/v1/matches/potential');

            $matchIds = collect($response->json('data'))->pluck('id');
            expect($matchIds->contains($blockedUser->id))->toBeFalse();
        });

        it('respects query parameters', function () {
            $response = $this->getJson('/api/v1/matches/potential?age_min=20&age_max=30&distance_km=10&limit=5');

            $response->assertStatus(200);
            $matches = $response->json('data');
            
            expect(count($matches))->toBeLessThanOrEqual(5);
        });

        it('calculates compatibility score', function () {
            // Create user with common interests
            $compatibleUser = $this->createUserWithCompleteProfile();
            $compatibleUser->profile->update([
                'interests' => ['travel', 'photography', 'cooking']
            ]);
            $this->user->profile->update([
                'interests' => ['travel', 'photography', 'reading']
            ]);

            $response = $this->getJson('/api/v1/matches/potential');

            $response->assertStatus(200);
            $matches = collect($response->json('data'));
            
            $compatibleMatch = $matches->firstWhere('id', $compatibleUser->id);
            if ($compatibleMatch) {
                expect($compatibleMatch['compatibility_score'])->toBeGreaterThan(0);
                expect($compatibleMatch['common_interests'])->toContain('travel', 'photography');
            }
        });

        it('enforces rate limiting', function () {
            // Make 50 requests (the limit)
            for ($i = 0; $i < 50; $i++) {
                $this->getJson('/api/v1/matches/potential');
            }

            // The 51st request should be rate limited
            $response = $this->getJson('/api/v1/matches/potential');

            $response->assertStatus(429);
        });
    });

    describe('GET /api/v1/matches', function () {
        beforeEach(function () {
            // Create some matches
            $this->matches = collect();
            
            for ($i = 0; $i < 3; $i++) {
                $matchedUser = $this->createUserWithCompleteProfile();
                $match = MatchModel::create([
                    'user1_id' => $this->user->id,
                    'user2_id' => $matchedUser->id,
                    'matched_at' => now()->subDays($i)
                ]);
                $this->matches->push(['match' => $match, 'user' => $matchedUser]);
            }
        });

        it('returns user matches', function () {
            $response = $this->getJson('/api/v1/matches');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => [
                            'id',
                            'user' => [
                                'id',
                                'first_name',
                                'age',
                                'photos'
                            ],
                            'matched_at',
                            'last_message',
                            'unread_count'
                        ]
                    ]
                ]);
        });

        it('includes last message in match', function () {
            $match = $this->matches->first();
            $chat = $this->user->chats()->create([
                'participant_id' => $match['user']->id
            ]);
            $message = $chat->messages()->create([
                'sender_id' => $match['user']->id,
                'content' => 'Hello!',
                'sent_at' => now()
            ]);

            $response = $this->getJson('/api/v1/matches');

            $response->assertStatus(200);
            $matchData = collect($response->json('data'))->firstWhere('user.id', $match['user']->id);
            
            expect($matchData['last_message'])->toMatchArray([
                'content' => 'Hello!',
                'sender_id' => $match['user']->id
            ]);
        });

        it('includes unread message count', function () {
            $match = $this->matches->first();
            $chat = $this->user->chats()->create([
                'participant_id' => $match['user']->id
            ]);
            
            // Create unread messages
            for ($i = 0; $i < 3; $i++) {
                $chat->messages()->create([
                    'sender_id' => $match['user']->id,
                    'content' => "Message $i",
                    'sent_at' => now(),
                    'read_at' => null
                ]);
            }

            $response = $this->getJson('/api/v1/matches');

            $matchData = collect($response->json('data'))->firstWhere('user.id', $match['user']->id);
            expect($matchData['unread_count'])->toBe(3);
        });

        it('orders matches by last activity', function () {
            $response = $this->getJson('/api/v1/matches');

            $response->assertStatus(200);
            $matches = collect($response->json('data'));
            
            // Should be ordered by most recent activity first
            $matchedDates = $matches->pluck('matched_at');
            expect($matchedDates)->toBe($matchedDates->sort()->reverse()->values()->toArray());
        });
    });

    describe('POST /api/v1/matches', function () {
        beforeEach(function () {
            $this->targetUser = $this->createUserWithCompleteProfile();
        });

        it('creates match when mutual like exists', function () {
            // Target user already liked current user
            Like::create([
                'user_id' => $this->targetUser->id,
                'liked_user_id' => $this->user->id
            ]);

            $response = $this->postJson('/api/v1/matches', [
                'user_id' => $this->targetUser->id
            ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'match_id',
                        'matched_user' => [
                            'id',
                            'first_name',
                            'age'
                        ],
                        'matched_at'
                    ]
                ])
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Match created successfully'
                ]);

            $this->assertDatabaseHas('matches', [
                'user1_id' => min($this->user->id, $this->targetUser->id),
                'user2_id' => max($this->user->id, $this->targetUser->id)
            ]);
        });

        it('creates like without match when no mutual like', function () {
            $response = $this->postJson('/api/v1/matches', [
                'user_id' => $this->targetUser->id
            ]);

            $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Like sent successfully'
                ]);

            $this->assertDatabaseHas('likes', [
                'user_id' => $this->user->id,
                'liked_user_id' => $this->targetUser->id
            ]);

            $this->assertDatabaseMissing('matches', [
                'user1_id' => min($this->user->id, $this->targetUser->id),
                'user2_id' => max($this->user->id, $this->targetUser->id)
            ]);
        });

        it('prevents liking already liked user', function () {
            Like::create([
                'user_id' => $this->user->id,
                'liked_user_id' => $this->targetUser->id
            ]);

            $response = $this->postJson('/api/v1/matches', [
                'user_id' => $this->targetUser->id
            ]);

            $this->assertApiError($response, 'DUPLICATE_ENTRY', 409);
        });

        it('prevents liking blocked user', function () {
            $this->user->blockedUsers()->create(['blocked_user_id' => $this->targetUser->id]);

            $response = $this->postJson('/api/v1/matches', [
                'user_id' => $this->targetUser->id
            ]);

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });

        it('prevents liking user who blocked you', function () {
            $this->targetUser->blockedUsers()->create(['blocked_user_id' => $this->user->id]);

            $response = $this->postJson('/api/v1/matches', [
                'user_id' => $this->targetUser->id
            ]);

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });

        it('validates user exists', function () {
            $response = $this->postJson('/api/v1/matches', [
                'user_id' => 99999
            ]);

            $this->assertApiError($response, 'NOT_FOUND', 404);
        });

        it('enforces rate limiting', function () {
            // Create multiple users to like
            $users = User::factory()->count(101)->create();

            // Make 100 requests (the limit)
            foreach ($users->take(100) as $user) {
                $this->postJson('/api/v1/matches', [
                    'user_id' => $user->id
                ]);
            }

            // The 101st request should be rate limited
            $response = $this->postJson('/api/v1/matches', [
                'user_id' => $users->last()->id
            ]);

            $response->assertStatus(429);
        });
    });

    describe('DELETE /api/v1/matches/{id}', function () {
        beforeEach(function () {
            $this->matchedUser = $this->createUserWithCompleteProfile();
            $this->match = MatchModel::create([
                'user1_id' => min($this->user->id, $this->matchedUser->id),
                'user2_id' => max($this->user->id, $this->matchedUser->id),
                'matched_at' => now()
            ]);
        });

        it('deletes match successfully', function () {
            $response = $this->deleteJson("/api/v1/matches/{$this->match->id}");

            $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Match deleted successfully'
                ]);

            $this->assertDatabaseMissing('matches', [
                'id' => $this->match->id
            ]);
        });

        it('prevents deleting match not belonging to user', function () {
            $otherUser = $this->createUserWithCompleteProfile();
            $otherMatch = MatchModel::create([
                'user1_id' => $otherUser->id,
                'user2_id' => $this->matchedUser->id,
                'matched_at' => now()
            ]);

            $response = $this->deleteJson("/api/v1/matches/{$otherMatch->id}");

            $this->assertApiError($response, 'FORBIDDEN', 403);
        });

        it('returns 404 for non-existent match', function () {
            $response = $this->deleteJson('/api/v1/matches/99999');

            $this->assertApiError($response, 'NOT_FOUND', 404);
        });
    });

    describe('Like System', function () {
        beforeEach(function () {
            $this->targetUser = $this->createUserWithCompleteProfile();
        });

        describe('POST /api/v1/likes', function () {
            it('creates like successfully', function () {
                $response = $this->postJson('/api/v1/likes', [
                    'user_id' => $this->targetUser->id
                ]);

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'message',
                        'data' => [
                            'like_id',
                            'is_match',
                            'match_id'
                        ]
                    ]);

                $this->assertDatabaseHas('likes', [
                    'user_id' => $this->user->id,
                    'liked_user_id' => $this->targetUser->id
                ]);
            });

            it('creates match on mutual like', function () {
                // Target user already liked current user
                Like::create([
                    'user_id' => $this->targetUser->id,
                    'liked_user_id' => $this->user->id
                ]);

                $response = $this->postJson('/api/v1/likes', [
                    'user_id' => $this->targetUser->id
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'is_match' => true
                        ]
                    ]);

                expect($response->json('data.match_id'))->not->toBeNull();
            });
        });

        describe('POST /api/v1/dislikes', function () {
            it('creates dislike successfully', function () {
                $response = $this->postJson('/api/v1/dislikes', [
                    'user_id' => $this->targetUser->id
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'User disliked successfully'
                    ]);

                $this->assertDatabaseHas('dislikes', [
                    'user_id' => $this->user->id,
                    'disliked_user_id' => $this->targetUser->id
                ]);
            });

            it('prevents disliking already disliked user', function () {
                Dislike::create([
                    'user_id' => $this->user->id,
                    'disliked_user_id' => $this->targetUser->id
                ]);

                $response = $this->postJson('/api/v1/dislikes', [
                    'user_id' => $this->targetUser->id
                ]);

                $this->assertApiError($response, 'DUPLICATE_ENTRY', 409);
            });
        });

        describe('GET /api/v1/likes/received', function () {
            beforeEach(function () {
                // Create users who liked current user
                $this->likingUsers = collect();
                for ($i = 0; $i < 3; $i++) {
                    $user = $this->createUserWithCompleteProfile();
                    Like::create([
                        'user_id' => $user->id,
                        'liked_user_id' => $this->user->id,
                        'created_at' => now()->subDays($i)
                    ]);
                    $this->likingUsers->push($user);
                }
            });

            it('returns received likes', function () {
                $response = $this->getJson('/api/v1/likes/received');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            '*' => [
                                'id',
                                'user' => [
                                    'id',
                                    'first_name',
                                    'age',
                                    'photos',
                                    'distance_km'
                                ],
                                'liked_at'
                            ]
                        ]
                    ]);

                expect(count($response->json('data')))->toBe(3);
            });

            it('orders by most recent first', function () {
                $response = $this->getJson('/api/v1/likes/received');

                $likedDates = collect($response->json('data'))->pluck('liked_at');
                expect($likedDates)->toBe($likedDates->sort()->reverse()->values()->toArray());
            });

            it('excludes likes from blocked users', function () {
                $blockedUser = $this->likingUsers->first();
                $this->user->blockedUsers()->create(['blocked_user_id' => $blockedUser->id]);

                $response = $this->getJson('/api/v1/likes/received');

                $userIds = collect($response->json('data'))->pluck('user.id');
                expect($userIds->contains($blockedUser->id))->toBeFalse();
            });
        });

        describe('GET /api/v1/likes/sent', function () {
            beforeEach(function () {
                // Create users that current user liked
                $this->likedUsers = collect();
                for ($i = 0; $i < 3; $i++) {
                    $user = $this->createUserWithCompleteProfile();
                    Like::create([
                        'user_id' => $this->user->id,
                        'liked_user_id' => $user->id,
                        'created_at' => now()->subDays($i)
                    ]);
                    $this->likedUsers->push($user);
                }
            });

            it('returns sent likes', function () {
                $response = $this->getJson('/api/v1/likes/sent');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            '*' => [
                                'id',
                                'user' => [
                                    'id',
                                    'first_name',
                                    'age'
                                ],
                                'liked_at',
                                'is_match'
                            ]
                        ]
                    ]);

                expect(count($response->json('data')))->toBe(3);
            });

            it('indicates if like resulted in match', function () {
                // Create mutual like
                $mutualUser = $this->likedUsers->first();
                Like::create([
                    'user_id' => $mutualUser->id,
                    'liked_user_id' => $this->user->id
                ]);
                MatchModel::create([
                    'user1_id' => min($this->user->id, $mutualUser->id),
                    'user2_id' => max($this->user->id, $mutualUser->id),
                    'matched_at' => now()
                ]);

                $response = $this->getJson('/api/v1/likes/sent');

                $mutualLike = collect($response->json('data'))->firstWhere('user.id', $mutualUser->id);
                expect($mutualLike['is_match'])->toBeTrue();
            });
        });
    });
});