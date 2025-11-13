<?php

namespace Tests\Feature\Api\V1;

use App\Models\Story;
use App\Models\User;
use App\Models\UserPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('Media and Photo Management', function () {
    beforeEach(function () {
        Storage::fake('public');
        $this->user = $this->createUserWithCompleteProfile();
        Sanctum::actingAs($this->user);
    });

    describe('Photos', function () {
        describe('GET /api/v1/photos', function () {
            beforeEach(function () {
                // Create photos for the user
                $this->photos = collect();
                for ($i = 0; $i < 5; $i++) {
                    $photo = UserPhoto::create([
                        'user_id' => $this->user->id,
                        'original_url' => "https://cdn.yoryor.app/photos/{$this->user->id}/{$i}.jpg",
                        'thumbnail_url' => "https://cdn.yoryor.app/photos/{$this->user->id}/{$i}_thumb.jpg",
                        'is_profile_photo' => $i === 0,
                        'order' => $i + 1,
                        'status' => $i < 3 ? 'approved' : 'pending',
                    ]);
                    $this->photos->push($photo);
                }
            });

            it('returns user photos', function () {
                $response = $this->getJson('/api/v1/photos');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            '*' => [
                                'id',
                                'original_url',
                                'thumbnail_url',
                                'image_url',
                                'is_profile_photo',
                                'order',
                                'status',
                                'uploaded_at',
                            ],
                        ],
                    ]);

                expect(count($response->json('data')))->toBe(5);
            });

            it('orders photos by order field', function () {
                $response = $this->getJson('/api/v1/photos');

                $response->assertStatus(200);
                $orders = collect($response->json('data'))->pluck('order');
                expect($orders->toArray())->toBe([1, 2, 3, 4, 5]);
            });

            it('indicates profile photo correctly', function () {
                $response = $this->getJson('/api/v1/photos');

                $profilePhotos = collect($response->json('data'))->where('is_profile_photo', true);
                expect($profilePhotos->count())->toBe(1);
                expect($profilePhotos->first()['order'])->toBe(1);
            });
        });

        describe('POST /api/v1/photos/upload', function () {
            it('uploads photo successfully', function () {
                $photo = \Illuminate\Http\UploadedFile::fake()->image('photo.jpg');

                $response = $this->postJson('/api/v1/photos/upload', [
                    'photo' => $photo,
                    'is_profile_photo' => false,
                ]);

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'message',
                        'data' => [
                            'id',
                            'url',
                            'thumbnail_url',
                            'is_profile_photo',
                            'order',
                            'status',
                        ],
                    ])
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Photo uploaded successfully',
                        'data' => [
                            'is_profile_photo' => false,
                            'status' => 'pending',
                        ],
                    ]);

                $this->assertDatabaseHas('user_photos', [
                    'user_id' => $this->user->id,
                    'is_profile_photo' => false,
                    'status' => 'pending',
                ]);
            });

            it('sets new photo as profile photo when requested', function () {
                // Create existing profile photo
                UserPhoto::create([
                    'user_id' => $this->user->id,
                    'original_url' => 'https://cdn.yoryor.app/photos/old.jpg',
                    'is_profile_photo' => true,
                    'order' => 1,
                ]);

                $photo = \Illuminate\Http\UploadedFile::fake()->image('photo.jpg');

                $response = $this->postJson('/api/v1/photos/upload', [
                    'photo' => $photo,
                    'is_profile_photo' => true,
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'is_profile_photo' => true,
                            'order' => 1,
                        ],
                    ]);

                // Check old profile photo is no longer profile photo
                $this->assertDatabaseHas('user_photos', [
                    'user_id' => $this->user->id,
                    'original_url' => 'https://cdn.yoryor.app/photos/old.jpg',
                    'is_profile_photo' => false,
                ]);
            });

            it('validates photo format', function () {
                $response = $this->postJson('/api/v1/photos/upload', [
                    'photo' => 'not-a-valid-base64-image',
                    'is_profile_photo' => false,
                ]);

                $this->assertValidationError($response, ['photo']);
            });

            it('enforces maximum photo limit', function () {
                // Create 9 photos (assuming 9 is the limit)
                for ($i = 0; $i < 9; $i++) {
                    UserPhoto::create([
                        'user_id' => $this->user->id,
                        'original_url' => "https://cdn.yoryor.app/photos/{$i}.jpg",
                        'order' => $i + 1,
                    ]);
                }

                $response = $this->postJson('/api/v1/photos/upload', [
                    'photo' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAAAAAAAD/2wBD',
                    'is_profile_photo' => false,
                ]);

                $this->assertApiError($response, 'FORBIDDEN', 403);
            });

            it('enforces rate limiting', function () {
                // Make 10 requests (the limit)
                for ($i = 0; $i < 10; $i++) {
                    $this->postJson('/api/v1/photos/upload', [
                        'photo' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAAAAAAAD/2wBD',
                        'is_profile_photo' => false,
                    ]);
                }

                // The 11th request should be rate limited
                $response = $this->postJson('/api/v1/photos/upload', [
                    'photo' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAAAAAAAD/2wBD',
                    'is_profile_photo' => false,
                ]);

                $response->assertStatus(429);
            });
        });

        describe('PUT /api/v1/photos/{id}', function () {
            beforeEach(function () {
                $this->photo = UserPhoto::create([
                    'user_id' => $this->user->id,
                    'original_url' => 'https://cdn.yoryor.app/photos/test.jpg',
                    'is_profile_photo' => false,
                    'order' => 2,
                ]);
            });

            it('updates photo successfully', function () {
                $response = $this->putJson("/api/v1/photos/{$this->photo->id}", [
                    'is_profile_photo' => true,
                    'order' => 1,
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Photo updated successfully',
                    ]);

                $this->assertDatabaseHas('user_photos', [
                    'id' => $this->photo->id,
                    'is_profile_photo' => true,
                    'order' => 1,
                ]);
            });

            it('prevents updating other user photos', function () {
                $otherUserPhoto = UserPhoto::create([
                    'user_id' => $this->createUserWithCompleteProfile()->id,
                    'original_url' => 'https://cdn.yoryor.app/photos/other.jpg',
                    'order' => 1,
                ]);

                $response = $this->putJson("/api/v1/photos/{$otherUserPhoto->id}", [
                    'order' => 5,
                ]);

                $this->assertApiError($response, 'FORBIDDEN', 403);
            });

            it('reorders other photos when setting new profile photo', function () {
                // Create current profile photo
                $currentProfilePhoto = UserPhoto::create([
                    'user_id' => $this->user->id,
                    'original_url' => 'https://cdn.yoryor.app/photos/current.jpg',
                    'is_profile_photo' => true,
                    'order' => 1,
                ]);

                $response = $this->putJson("/api/v1/photos/{$this->photo->id}", [
                    'is_profile_photo' => true,
                ]);

                $response->assertStatus(200);

                // Check old profile photo is no longer profile photo
                $this->assertDatabaseHas('user_photos', [
                    'id' => $currentProfilePhoto->id,
                    'is_profile_photo' => false,
                ]);

                // Check new profile photo has order 1
                $this->assertDatabaseHas('user_photos', [
                    'id' => $this->photo->id,
                    'is_profile_photo' => true,
                    'order' => 1,
                ]);
            });
        });

        describe('DELETE /api/v1/photos/{id}', function () {
            beforeEach(function () {
                $this->photo = UserPhoto::create([
                    'user_id' => $this->user->id,
                    'original_url' => 'https://cdn.yoryor.app/photos/test.jpg',
                    'is_profile_photo' => false,
                    'order' => 2,
                ]);
            });

            it('deletes photo successfully', function () {
                $response = $this->deleteJson("/api/v1/photos/{$this->photo->id}");

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Photo deleted successfully',
                    ]);

                $this->assertDatabaseMissing('user_photos', [
                    'id' => $this->photo->id,
                ]);
            });

            it('prevents deleting profile photo', function () {
                $profilePhoto = UserPhoto::create([
                    'user_id' => $this->user->id,
                    'original_url' => 'https://cdn.yoryor.app/photos/profile.jpg',
                    'is_profile_photo' => true,
                    'order' => 1,
                ]);

                $response = $this->deleteJson("/api/v1/photos/{$profilePhoto->id}");

                $this->assertApiError($response, 'FORBIDDEN', 403);
            });

            it('prevents deleting last photo', function () {
                // Delete all photos except this one
                UserPhoto::where('user_id', $this->user->id)
                    ->where('id', '!=', $this->photo->id)
                    ->delete();

                $response = $this->deleteJson("/api/v1/photos/{$this->photo->id}");

                $this->assertApiError($response, 'FORBIDDEN', 403);
            });

            it('prevents deleting other user photos', function () {
                $otherUserPhoto = UserPhoto::create([
                    'user_id' => $this->createUserWithCompleteProfile()->id,
                    'original_url' => 'https://cdn.yoryor.app/photos/other.jpg',
                    'order' => 1,
                ]);

                $response = $this->deleteJson("/api/v1/photos/{$otherUserPhoto->id}");

                $this->assertApiError($response, 'FORBIDDEN', 403);
            });

            it('reorders remaining photos after deletion', function () {
                // Create photos with order 1, 2, 3, 4
                $photos = collect();
                for ($i = 1; $i <= 4; $i++) {
                    if ($i !== 2) { // Skip order 2 as we already have it
                        $photos->push(UserPhoto::create([
                            'user_id' => $this->user->id,
                            'original_url' => "https://cdn.yoryor.app/photos/test{$i}.jpg",
                            'order' => $i,
                        ]));
                    }
                }

                // Delete photo with order 2
                $response = $this->deleteJson("/api/v1/photos/{$this->photo->id}");

                $response->assertStatus(200);

                // Check remaining photos have been reordered
                $remainingPhotos = UserPhoto::where('user_id', $this->user->id)
                    ->orderBy('order')
                    ->pluck('order')
                    ->toArray();

                expect($remainingPhotos)->toBe([1, 2, 3]);
            });
        });
    });

    describe('Stories', function () {
        describe('GET /api/v1/stories', function () {
            beforeEach(function () {
                // Create stories for the user
                $this->stories = collect();
                for ($i = 0; $i < 3; $i++) {
                    $story = Story::create([
                        'user_id' => $this->user->id,
                        'media_url' => "https://cdn.yoryor.app/stories/{$this->user->id}/{$i}.jpg",
                        'media_type' => 'image',
                        'caption' => "Story caption $i",
                        'created_at' => now()->subHours($i),
                        'expires_at' => now()->addHours(24 - $i),
                        'view_count' => $i * 10,
                    ]);
                    $this->stories->push($story);
                }
            });

            it('returns user stories', function () {
                $response = $this->getJson('/api/v1/stories');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            '*' => [
                                'id',
                                'media_url',
                                'media_type',
                                'caption',
                                'created_at',
                                'expires_at',
                                'view_count',
                            ],
                        ],
                    ]);

                expect(count($response->json('data')))->toBe(3);
            });

            it('excludes expired stories', function () {
                // Create expired story
                Story::create([
                    'user_id' => $this->user->id,
                    'media_url' => 'https://cdn.yoryor.app/stories/expired.jpg',
                    'media_type' => 'image',
                    'created_at' => now()->subHours(25),
                    'expires_at' => now()->subHour(),
                ]);

                $response = $this->getJson('/api/v1/stories');

                $response->assertStatus(200);
                expect(count($response->json('data')))->toBe(3); // Still 3, expired not included
            });

            it('orders stories by creation date', function () {
                $response = $this->getJson('/api/v1/stories');

                $createdDates = collect($response->json('data'))->pluck('created_at');
                expect($createdDates)->toBe($createdDates->sort()->reverse()->values()->toArray());
            });
        });

        describe('POST /api/v1/stories', function () {
            it('creates story successfully', function () {
                $response = $this->postJson('/api/v1/stories', [
                    'media' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAAAAAAAD/2wBD',
                    'media_type' => 'image',
                    'caption' => 'Beautiful sunset today!',
                ]);

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'message',
                        'data' => [
                            'id',
                            'media_url',
                            'media_type',
                            'caption',
                            'created_at',
                            'expires_at',
                        ],
                    ])
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Story created successfully',
                        'data' => [
                            'media_type' => 'image',
                            'caption' => 'Beautiful sunset today!',
                        ],
                    ]);

                $this->assertDatabaseHas('stories', [
                    'user_id' => $this->user->id,
                    'media_type' => 'image',
                    'caption' => 'Beautiful sunset today!',
                ]);
            });

            it('creates video story', function () {
                $response = $this->postJson('/api/v1/stories', [
                    'media' => 'data:video/mp4;base64,AAAAIGZ0eXBpc29tAAACAGlzb21pc28yYXZjMW',
                    'media_type' => 'video',
                    'caption' => 'Check out this video!',
                ]);

                $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'media_type' => 'video',
                            'caption' => 'Check out this video!',
                        ],
                    ]);
            });

            it('validates media type', function () {
                $response = $this->postJson('/api/v1/stories', [
                    'media' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAAAAAAAD/2wBD',
                    'media_type' => 'invalid',
                    'caption' => 'Test',
                ]);

                $this->assertValidationError($response, ['media_type']);
            });

            it('validates caption length', function () {
                $response = $this->postJson('/api/v1/stories', [
                    'media' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAAAAAAAD/2wBD',
                    'media_type' => 'image',
                    'caption' => str_repeat('a', 501), // Assuming 500 is the limit
                ]);

                $this->assertValidationError($response, ['caption']);
            });

            it('enforces maximum active stories limit', function () {
                // Create 10 active stories (assuming 10 is the limit)
                for ($i = 0; $i < 10; $i++) {
                    Story::create([
                        'user_id' => $this->user->id,
                        'media_url' => "https://cdn.yoryor.app/stories/{$i}.jpg",
                        'media_type' => 'image',
                        'created_at' => now(),
                        'expires_at' => now()->addHours(24),
                    ]);
                }

                $response = $this->postJson('/api/v1/stories', [
                    'media' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAAAAAAAD/2wBD',
                    'media_type' => 'image',
                    'caption' => 'One too many',
                ]);

                $this->assertApiError($response, 'FORBIDDEN', 403);
            });

            it('enforces rate limiting', function () {
                // Make 20 requests (the limit)
                for ($i = 0; $i < 20; $i++) {
                    $this->postJson('/api/v1/stories', [
                        'media' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAAAAAAAD/2wBD',
                        'media_type' => 'image',
                        'caption' => "Story $i",
                    ]);
                }

                // The 21st request should be rate limited
                $response = $this->postJson('/api/v1/stories', [
                    'media' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAAAAAAAD/2wBD',
                    'media_type' => 'image',
                    'caption' => 'Rate limited',
                ]);

                $response->assertStatus(429);
            });
        });

        describe('DELETE /api/v1/stories/{id}', function () {
            beforeEach(function () {
                $this->story = Story::create([
                    'user_id' => $this->user->id,
                    'media_url' => 'https://cdn.yoryor.app/stories/test.jpg',
                    'media_type' => 'image',
                    'caption' => 'Test story',
                    'created_at' => now(),
                    'expires_at' => now()->addHours(24),
                ]);
            });

            it('deletes story successfully', function () {
                $response = $this->deleteJson("/api/v1/stories/{$this->story->id}");

                $response->assertStatus(200)
                    ->assertJson([
                        'status' => 'success',
                        'message' => 'Story deleted successfully',
                    ]);

                $this->assertDatabaseMissing('stories', [
                    'id' => $this->story->id,
                ]);
            });

            it('prevents deleting other user stories', function () {
                $otherUserStory = Story::create([
                    'user_id' => $this->createUserWithCompleteProfile()->id,
                    'media_url' => 'https://cdn.yoryor.app/stories/other.jpg',
                    'media_type' => 'image',
                    'created_at' => now(),
                    'expires_at' => now()->addHours(24),
                ]);

                $response = $this->deleteJson("/api/v1/stories/{$otherUserStory->id}");

                $this->assertApiError($response, 'FORBIDDEN', 403);
            });

            it('returns 404 for non-existent story', function () {
                $response = $this->deleteJson('/api/v1/stories/99999');

                $this->assertApiError($response, 'NOT_FOUND', 404);
            });
        });

        describe('GET /api/v1/stories/matches', function () {
            beforeEach(function () {
                // Create matched users with stories
                $this->matchedUsersWithStories = collect();

                for ($i = 0; $i < 3; $i++) {
                    $matchedUser = $this->createUserWithCompleteProfile();

                    // Create match
                    \App\Models\UserMatch::create([
                        'user_id' => $this->user->id,
                        'matched_user_id' => $matchedUser->id,
                        'matched_at' => now(),
                    ]);

                    // Create stories for matched user
                    $stories = collect();
                    for ($j = 0; $j < 2; $j++) {
                        $story = Story::create([
                            'user_id' => $matchedUser->id,
                            'media_url' => "https://cdn.yoryor.app/stories/{$matchedUser->id}/{$j}.jpg",
                            'media_type' => 'image',
                            'created_at' => now()->subHours($j),
                            'expires_at' => now()->addHours(24 - $j),
                            'viewed_by' => $j === 0 ? [] : [$this->user->id], // First story unviewed
                        ]);
                        $stories->push($story);
                    }

                    $this->matchedUsersWithStories->push([
                        'user' => $matchedUser,
                        'stories' => $stories,
                    ]);
                }
            });

            it('returns matched user stories', function () {
                $response = $this->getJson('/api/v1/stories/matches');

                $response->assertStatus(200)
                    ->assertJsonStructure([
                        'status',
                        'data' => [
                            '*' => [
                                'user' => [
                                    'id',
                                    'first_name',
                                    'profile_photo_url',
                                ],
                                'stories' => [
                                    '*' => [
                                        'id',
                                        'media_url',
                                        'media_type',
                                        'created_at',
                                    ],
                                ],
                                'unviewed_count',
                            ],
                        ],
                    ]);

                expect(count($response->json('data')))->toBe(3);
            });

            it('includes unviewed count', function () {
                $response = $this->getJson('/api/v1/stories/matches');

                $userData = collect($response->json('data'))->first();
                expect($userData['unviewed_count'])->toBe(1); // One unviewed story per user
            });

            it('excludes expired stories', function () {
                // Create user with expired story
                $userWithExpiredStory = $this->createUserWithCompleteProfile();
                \App\Models\UserMatch::create([
                    'user_id' => $this->user->id,
                    'matched_user_id' => $userWithExpiredStory->id,
                    'matched_at' => now(),
                ]);

                Story::create([
                    'user_id' => $userWithExpiredStory->id,
                    'media_url' => 'https://cdn.yoryor.app/stories/expired.jpg',
                    'media_type' => 'image',
                    'created_at' => now()->subHours(25),
                    'expires_at' => now()->subHour(),
                ]);

                $response = $this->getJson('/api/v1/stories/matches');

                // User with only expired stories should not appear
                $userIds = collect($response->json('data'))->pluck('user.id');
                expect($userIds->contains($userWithExpiredStory->id))->toBeFalse();
            });

            it('orders by most recent story', function () {
                $response = $this->getJson('/api/v1/stories/matches');

                $userData = collect($response->json('data'));

                // Check that users are ordered by their most recent story
                $previousTime = now()->addDay();
                foreach ($userData as $user) {
                    $mostRecentStory = collect($user['stories'])->sortByDesc('created_at')->first();
                    expect($mostRecentStory['created_at'])->toBeLessThanOrEqual($previousTime);
                    $previousTime = $mostRecentStory['created_at'];
                }
            });
        });
    });
});
