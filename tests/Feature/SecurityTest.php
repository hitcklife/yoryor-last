<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present()
    {
        $response = $this->get('/');
        
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_content_security_policy_is_set()
    {
        $response = $this->get('/');
        
        $response->assertHeader('Content-Security-Policy');
        
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringContainsString("style-src 'self'", $csp);
    }

    public function test_rate_limiting_works_for_api_endpoints()
    {
        $user = User::factory()->create();
        
        // Make multiple requests to trigger rate limiting
        for ($i = 0; $i < 10; $i++) {
            $response = $this->actingAs($user)->get('/api/user/profile');
        }
        
        // Should still be within rate limit
        $response->assertStatus(200);
    }

    public function test_rate_limiting_blocks_excessive_requests()
    {
        $user = User::factory()->create();
        
        // Clear any existing rate limits
        RateLimiter::clear('api:'.$user->id.':127.0.0.1');
        
        // Make excessive requests
        for ($i = 0; $i < 1001; $i++) {
            $response = $this->actingAs($user)->get('/api/user/profile');
        }
        
        // Should be rate limited
        $response->assertStatus(429);
        $response->assertJson([
            'error' => 'Too Many Requests'
        ]);
    }

    public function test_authentication_required_for_protected_routes()
    {
        $protectedRoutes = [
            '/dashboard',
            '/matches',
            '/messages',
            '/profile',
            '/settings',
            '/notifications',
            '/search',
            '/subscription',
            '/verification',
            '/emergency',
            '/insights'
        ];
        
        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    public function test_csrf_protection_is_enabled()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/dashboard', [
            '_token' => 'invalid-token'
        ]);
        
        $response->assertStatus(419); // CSRF token mismatch
    }

    public function test_sql_injection_protection()
    {
        $user = User::factory()->create();
        
        $maliciousInput = "'; DROP TABLE users; --";
        
        $response = $this->actingAs($user)->get('/search?q=' . urlencode($maliciousInput));
        
        // Should not cause database error
        $response->assertStatus(200);
        
        // Verify users table still exists
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_xss_protection()
    {
        $user = User::factory()->create();
        
        $xssPayload = '<script>alert("XSS")</script>';
        
        $response = $this->actingAs($user)->get('/search?q=' . urlencode($xssPayload));
        
        // Should not contain the script tag
        $response->assertDontSee('<script>alert("XSS")</script>');
    }

    public function test_file_upload_security()
    {
        $user = User::factory()->create();
        
        // Test with malicious file
        $maliciousFile = [
            'name' => 'malicious.php',
            'type' => 'application/php',
            'content' => '<?php system($_GET["cmd"]); ?>'
        ];
        
        $response = $this->actingAs($user)->post('/api/user/upload/photo', [
            'file' => $maliciousFile
        ]);
        
        // Should reject the file
        $response->assertStatus(422);
    }

    public function test_session_security()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        // Check session cookie security
        $cookies = $response->headers->getCookies();
        $sessionCookie = collect($cookies)->firstWhere('getName', 'laravel_session');
        
        if ($sessionCookie) {
            $this->assertTrue($sessionCookie->isSecure());
            $this->assertTrue($sessionCookie->isHttpOnly());
        }
    }

    public function test_password_requirements()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123', // Too weak
            'password_confirmation' => '123'
        ];
        
        $response = $this->post('/register', $userData);
        
        $response->assertSessionHasErrors('password');
    }

    public function test_brute_force_protection()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        
        // Attempt multiple failed logins
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword'
            ]);
        }
        
        // Should be blocked
        $response->assertStatus(429);
    }
}
