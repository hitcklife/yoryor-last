<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFlowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_user_registration_flow()
    {
        // Step 1: User visits registration page
        $response = $this->get('/register');
        $response->assertStatus(200);

        // Step 2: User fills registration form
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => true,
        ];

        $response = $this->post('/register', $userData);
        $response->assertRedirect('/onboard/basic-info');

        // Step 3: User completes onboarding
        $user = User::where('email', 'john@example.com')->first();
        $this->actingAs($user);

        // Basic info step
        $response = $this->get('/onboard/basic-info');
        $response->assertStatus(200);

        $basicInfoData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '1995-01-01',
            'gender' => 'male',
            'interested_in' => 'female',
        ];

        $response = $this->post('/onboard/basic-info', $basicInfoData);
        $response->assertRedirect('/onboard/contact-info');

        // Contact info step
        $response = $this->get('/onboard/contact-info');
        $response->assertStatus(200);

        $contactData = [
            'phone' => '+1234567890',
            'location' => 'New York, NY',
        ];

        $response = $this->post('/onboard/contact-info', $contactData);
        $response->assertRedirect('/onboard/about-you');

        // About you step
        $response = $this->get('/onboard/about-you');
        $response->assertStatus(200);

        $aboutData = [
            'bio' => 'Love to travel and meet new people',
            'height' => '6 feet',
            'education' => 'Bachelor\'s Degree',
            'profession' => 'Software Engineer',
        ];

        $response = $this->post('/onboard/about-you', $aboutData);
        $response->assertRedirect('/onboard/preferences');

        // Preferences step
        $response = $this->get('/onboard/preferences');
        $response->assertStatus(200);

        $preferencesData = [
            'age_min' => 25,
            'age_max' => 35,
            'distance' => 50,
            'gender_preference' => 'female',
        ];

        $response = $this->post('/onboard/preferences', $preferencesData);
        $response->assertRedirect('/onboard/interests');

        // Interests step
        $response = $this->get('/onboard/interests');
        $response->assertStatus(200);

        $interestsData = [
            'interests' => ['Travel', 'Music', 'Sports', 'Art'],
        ];

        $response = $this->post('/onboard/interests', $interestsData);
        $response->assertRedirect('/onboard/photos');

        // Photos step
        $response = $this->get('/onboard/photos');
        $response->assertStatus(200);

        // Location step
        $response = $this->get('/onboard/location');
        $response->assertStatus(200);

        $locationData = [
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'US',
        ];

        $response = $this->post('/onboard/location', $locationData);
        $response->assertRedirect('/onboard/preview');

        // Preview step
        $response = $this->get('/onboard/preview');
        $response->assertStatus(200);

        // Complete step
        $response = $this->get('/onboard/complete');
        $response->assertRedirect('/dashboard');

        // Verify user profile is complete
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    public function test_matching_flow()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        \App\Models\Profile::factory()->create(['user_id' => $user1->id]);
        \App\Models\Profile::factory()->create(['user_id' => $user2->id]);

        // User1 likes User2
        $this->actingAs($user1);
        $response = $this->postJson("/api/user/like/{$user2->id}");
        $response->assertStatus(200);

        // User2 likes User1 back (creates a match)
        $this->actingAs($user2);
        $response = $this->postJson("/api/user/like/{$user1->id}");
        $response->assertStatus(200);

        // Verify match was created
        $this->assertDatabaseHas('matches', [
            'user1_id' => $user1->id,
            'user2_id' => $user2->id,
            'status' => 'matched',
        ]);

        // Verify both users received match notifications
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user1->id,
            'type' => 'match',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user2->id,
            'type' => 'match',
        ]);
    }

    public function test_messaging_flow()
    {
        // Create matched users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        \App\Models\Profile::factory()->create(['user_id' => $user1->id]);
        \App\Models\Profile::factory()->create(['user_id' => $user2->id]);

        // Create a match
        \App\Models\UserMatch::create([
            'user_id' => $user1->id,
            'matched_user_id' => $user2->id,
            'matched_at' => now(),
        ]);

        // User1 sends first message
        $this->actingAs($user1);
        $response = $this->postJson('/api/v1/messages', [
            'recipient_id' => $user2->id,
            'content' => 'Hello! How are you?',
            'type' => 'text',
        ]);
        $response->assertStatus(201);

        // Verify conversation was created
        $conversation = \App\Models\Conversation::where('user1_id', $user1->id)
            ->where('user2_id', $user2->id)
            ->first();

        $this->assertNotNull($conversation);

        // Verify message was created
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $user1->id,
            'content' => 'Hello! How are you?',
        ]);

        // User2 responds
        $this->actingAs($user2);
        $response = $this->postJson("/api/v1/messages/{$conversation->id}", [
            'content' => 'Hi! I\'m doing great, thanks!',
            'type' => 'text',
        ]);
        $response->assertStatus(201);

        // Verify response message
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $user2->id,
            'content' => 'Hi! I\'m doing great, thanks!',
        ]);

        // User1 marks messages as read
        $this->actingAs($user1);
        $response = $this->postJson("/api/v1/messages/{$conversation->id}/mark-read");
        $response->assertStatus(200);
    }

    public function test_video_call_flow()
    {
        // Create matched users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        \App\Models\Profile::factory()->create(['user_id' => $user1->id]);
        \App\Models\Profile::factory()->create(['user_id' => $user2->id]);

        // Create a match
        \App\Models\UserMatch::create([
            'user_id' => $user1->id,
            'matched_user_id' => $user2->id,
            'matched_at' => now(),
        ]);

        // User1 initiates video call
        $this->actingAs($user1);
        $response = $this->postJson('/api/v1/video-calls', [
            'recipient_id' => $user2->id,
            'type' => 'video',
        ]);
        $response->assertStatus(201);

        // User2 accepts the call
        $this->actingAs($user2);
        $response = $this->postJson('/api/v1/video-calls/1/accept');
        $response->assertStatus(200);

        // User1 ends the call
        $this->actingAs($user1);
        $response = $this->postJson('/api/v1/video-calls/1/end');
        $response->assertStatus(200);

        // Verify call was recorded
        $this->assertDatabaseHas('video_calls', [
            'caller_id' => $user1->id,
            'recipient_id' => $user2->id,
            'status' => 'completed',
        ]);
    }

    public function test_verification_flow()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // User starts photo verification
        $response = $this->postJson('/api/v1/verification/photo', [
            'photo' => 'base64_encoded_image_data',
        ]);
        $response->assertStatus(200);

        // User starts ID verification
        $response = $this->postJson('/api/v1/verification/id', [
            'id_type' => 'drivers_license',
            'id_number' => '123456789',
            'front_image' => 'base64_encoded_image_data',
            'back_image' => 'base64_encoded_image_data',
        ]);
        $response->assertStatus(200);

        // User verifies phone number
        $response = $this->postJson('/api/v1/verification/phone', [
            'phone_number' => '+1234567890',
        ]);
        $response->assertStatus(200);

        // User verifies email
        $response = $this->postJson('/api/v1/verification/email');
        $response->assertStatus(200);

        // Check verification status
        $response = $this->getJson('/api/v1/verification/status');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'photo_verified',
                    'id_verified',
                    'phone_verified',
                    'email_verified',
                    'overall_score',
                ],
            ]);
    }

    public function test_subscription_flow()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // User views subscription plans
        $response = $this->getJson('/api/v1/subscription');
        $response->assertStatus(200);

        // User upgrades to premium
        $response = $this->postJson('/api/v1/subscription/upgrade', [
            'plan_id' => 'premium',
            'payment_method' => 'stripe',
            'stripe_token' => 'tok_visa',
        ]);
        $response->assertStatus(200);

        // Verify subscription was created
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_id' => 'premium',
            'status' => 'active',
        ]);

        // User views billing history
        $response = $this->getJson('/api/v1/subscription/billing-history');
        $response->assertStatus(200);

        // User cancels subscription
        $response = $this->postJson('/api/v1/subscription/cancel');
        $response->assertStatus(200);

        // Verify subscription was cancelled
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_safety_features_flow()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // User adds emergency contact
        $response = $this->postJson('/api/v1/safety/emergency-contacts', [
            'name' => 'Emergency Contact',
            'phone' => '+1234567890',
            'relationship' => 'friend',
        ]);
        $response->assertStatus(201);

        // User activates panic button
        $response = $this->postJson('/api/v1/safety/panic');
        $response->assertStatus(200);

        // User sends safety check
        $response = $this->postJson('/api/v1/safety/safety-check');
        $response->assertStatus(200);

        // Verify panic activation was recorded
        $this->assertDatabaseHas('panic_activations', [
            'user_id' => $user->id,
            'status' => 'active',
        ]);
    }

    public function test_search_and_discovery_flow()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // User searches for matches
        $response = $this->getJson('/api/v1/search?q=travel&age_min=25&age_max=35');
        $response->assertStatus(200);

        // User applies filters
        $response = $this->getJson('/api/v1/search', [
            'age_min' => 25,
            'age_max' => 35,
            'distance' => 50,
            'interests' => 'Travel,Music',
        ]);
        $response->assertStatus(200);

        // User likes someone
        $targetUser = User::factory()->create();
        $response = $this->postJson("/api/user/like/{$targetUser->id}");
        $response->assertStatus(200);

        // User passes on someone
        $anotherUser = User::factory()->create();
        $response = $this->postJson("/api/user/pass/{$anotherUser->id}");
        $response->assertStatus(200);

        // User blocks someone
        $blockedUser = User::factory()->create();
        $response = $this->postJson("/api/user/block/{$blockedUser->id}");
        $response->assertStatus(200);

        // User reports someone
        $reportedUser = User::factory()->create();
        $response = $this->postJson("/api/user/report/{$reportedUser->id}", [
            'reason' => 'inappropriate_behavior',
            'description' => 'User was being inappropriate',
        ]);
        $response->assertStatus(200);
    }

    public function test_notification_flow()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // User receives notification
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => 'match',
            'title' => 'New Match!',
            'message' => 'You have a new match with Jane Doe',
            'data' => json_encode(['user_id' => 2, 'user_name' => 'Jane Doe']),
        ]);

        // User views notifications
        $response = $this->getJson('/api/v1/notifications');
        $response->assertStatus(200);

        // User marks notification as read
        $notification = \App\Models\Notification::where('user_id', $user->id)->first();
        $response = $this->postJson("/api/v1/notifications/{$notification->id}/mark-read");
        $response->assertStatus(200);

        // User marks all notifications as read
        $response = $this->postJson('/api/v1/notifications/mark-all-read');
        $response->assertStatus(200);

        // User deletes notification
        $response = $this->deleteJson("/api/v1/notifications/{$notification->id}");
        $response->assertStatus(200);
    }

    public function test_analytics_flow()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // User views analytics
        $response = $this->getJson('/api/v1/analytics');
        $response->assertStatus(200);

        // User exports data
        $response = $this->postJson('/api/v1/analytics/export');
        $response->assertStatus(200);

        // User generates report
        $response = $this->postJson('/api/v1/analytics/report', [
            'date_range' => '30',
            'metrics' => ['profile_views', 'matches', 'messages'],
        ]);
        $response->assertStatus(200);
    }

    public function test_mobile_responsiveness()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test mobile viewport
        $response = $this->get('/dashboard', [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15',
        ]);
        $response->assertStatus(200);

        // Test tablet viewport
        $response = $this->get('/dashboard', [
            'User-Agent' => 'Mozilla/5.0 (iPad; CPU OS 14_0 like Mac OS X) AppleWebKit/605.1.15',
        ]);
        $response->assertStatus(200);
    }

    public function test_offline_functionality()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test service worker registration
        $response = $this->get('/sw.js');
        $response->assertStatus(200);

        // Test manifest file
        $response = $this->get('/manifest.json');
        $response->assertStatus(200);

        // Test offline page
        $response = $this->get('/offline');
        $response->assertStatus(200);
    }

    public function test_security_flow()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test rate limiting
        for ($i = 0; $i < 10; $i++) {
            $response = $this->getJson('/api/v1/user/profile');
        }
        $response->assertStatus(200); // Should still be within limit

        // Test CSRF protection
        $response = $this->postJson('/api/v1/messages', [
            'recipient_id' => 1,
            'content' => 'Test message',
        ]);
        $response->assertStatus(200); // Should be protected by Sanctum

        // Test XSS protection
        $response = $this->postJson('/api/v1/messages', [
            'recipient_id' => 1,
            'content' => '<script>alert("xss")</script>',
        ]);
        $response->assertStatus(201);
        // Content should be sanitized
    }
}
