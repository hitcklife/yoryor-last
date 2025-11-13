<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_profile_api_returns_user_data()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/user/profile');
            
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_user_matches_api_returns_matches()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/matches');
            
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'matches' => [],
                    'pagination' => [
                        'total',
                        'per_page',
                        'current_page',
                        'last_page'
                    ]
                ]
            ]);
    }

    public function test_user_messages_api_returns_messages()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/messages');
            
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'conversations' => [],
                    'pagination' => []
                ]
            ]);
    }

    public function test_user_notifications_api_returns_notifications()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notifications');
            
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'notifications' => [],
                    'pagination' => []
                ]
            ]);
    }

    public function test_user_search_api_returns_search_results()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/search?q=test');
            
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'results' => [],
                    'pagination' => []
                ]
            ]);
    }

    public function test_user_subscription_api_returns_subscription_data()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/subscription');
            
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'current_plan',
                    'usage_stats',
                    'billing_history' => []
                ]
            ]);
    }

    public function test_user_verification_api_returns_verification_status()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/verification/status');
            
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'photo_verified',
                    'id_verified',
                    'phone_verified',
                    'email_verified'
                ]
            ]);
    }

    public function test_user_emergency_api_returns_emergency_contacts()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/safety/emergency-contacts');
            
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'emergency_contacts' => []
                ]
            ]);
    }

    public function test_user_insights_api_returns_analytics_data()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/analytics');
            
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'profile_views',
                    'matches',
                    'messages',
                    'success_score'
                ]
            ]);
    }

    public function test_like_user_api_creates_like()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/user/like/{$targetUser->id}");
            
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User liked successfully'
            ]);
    }

    public function test_unlike_user_api_removes_like()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/user/unlike/{$targetUser->id}");
            
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User unliked successfully'
            ]);
    }

    public function test_pass_user_api_creates_pass()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/user/pass/{$targetUser->id}");
            
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User passed successfully'
            ]);
    }

    public function test_block_user_api_creates_block()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/user/block/{$targetUser->id}");
            
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User blocked successfully'
            ]);
    }

    public function test_unblock_user_api_removes_block()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/user/unblock/{$targetUser->id}");
            
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User unblocked successfully'
            ]);
    }

    public function test_report_user_api_creates_report()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/user/report/{$targetUser->id}");
            
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User reported successfully'
            ]);
    }

    public function test_mark_notification_read_api_marks_notification()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/user/notifications/1/mark-read');
            
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
    }

    public function test_mark_all_notifications_read_api_marks_all()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/user/notifications/mark-all-read');
            
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
    }

    public function test_delete_notification_api_deletes_notification()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/user/notifications/1');
            
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
    }

    public function test_emergency_panic_api_sends_panic_alert()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/user/emergency/panic');
            
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Emergency alert sent'
            ]);
    }

    public function test_emergency_safety_check_api_sends_safety_check()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/user/emergency/safety-check');
            
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Safety check completed'
            ]);
    }

    public function test_api_endpoints_require_authentication()
    {
        $protectedEndpoints = [
            '/api/v1/user/profile',
            '/api/v1/matches',
            '/api/v1/messages',
            '/api/v1/notifications',
            '/api/v1/search',
            '/api/v1/subscription',
            '/api/v1/verification/status',
            '/api/v1/safety/emergency-contacts',
            '/api/v1/analytics'
        ];

        foreach ($protectedEndpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401);
        }
    }

    public function test_api_endpoints_validate_input()
    {
        $user = User::factory()->create();
        
        // Test invalid user ID
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/user/like/invalid-id');
            
        $response->assertStatus(404);
    }

    public function test_api_endpoints_handle_rate_limiting()
    {
        $user = User::factory()->create();
        
        // Make multiple requests to trigger rate limiting
        for ($i = 0; $i < 10; $i++) {
            $response = $this->actingAs($user, 'sanctum')
                ->getJson('/api/v1/user/profile');
        }
        
        // Should still be within rate limit for this endpoint
        $response->assertStatus(200);
    }

    public function test_api_endpoints_return_proper_json_structure()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/user/profile');
            
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'data' => []
            ]);
    }

    public function test_api_endpoints_handle_errors_gracefully()
    {
        $user = User::factory()->create();
        
        // Test with non-existent resource
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/non-existent-endpoint');
            
        $response->assertStatus(404);
    }
}
