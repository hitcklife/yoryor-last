<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    /**
     * Authenticate a user for testing
     */
    public function actingAs($user, $driver = null)
    {
        Sanctum::actingAs($user, ['*']);
        return parent::actingAs($user, $driver);
    }

    /**
     * Create and authenticate a user for testing
     */
    protected function createAuthenticatedUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        Sanctum::actingAs($user, ['*']);
        return $user;
    }

    /**
     * Get auth headers with Bearer token
     */
    protected function getAuthHeaders($user = null): array
    {
        if (!$user) {
            $user = User::factory()->create();
        }
        
        $token = $user->createToken('test-token')->plainTextToken;
        
        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Assert API response structure
     */
    protected function assertApiResponse($response, $status = 200)
    {
        $response->assertStatus($status)
            ->assertJsonStructure([
                'status',
                'message',
            ]);
    }

    /**
     * Assert paginated API response structure
     */
    protected function assertPaginatedApiResponse($response, $status = 200)
    {
        $response->assertStatus($status)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'pagination' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                    'has_more_pages',
                    'from',
                    'to'
                ]
            ]);
    }

    /**
     * Assert API error response
     */
    protected function assertApiError($response, $errorCode, $status)
    {
        $response->assertStatus($status)
            ->assertJsonStructure([
                'status',
                'message',
                'error_code',
            ])
            ->assertJson([
                'status' => 'error',
                'error_code' => $errorCode,
            ]);
    }

    /**
     * Assert validation error response
     */
    protected function assertValidationError($response, array $fields)
    {
        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'error_code',
                'errors'
            ])
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed',
                'error_code' => 'validation_failed'
            ])
            ->assertJsonValidationErrors($fields);
    }

    /**
     * Create a user with complete profile
     */
    protected function createUserWithCompleteProfile(array $attributes = []): User
    {
        // Separate user-specific attributes from profile attributes
        $userAttributes = array_intersect_key($attributes, array_flip([
            'email', 'phone', 'registration_completed', 'is_private', 'last_active_at'
        ]));
        
        $user = User::factory()->create(array_merge([
            'registration_completed' => true,
        ], $userAttributes));

        // Create profile
        $user->profile()->create([
            'user_id' => $user->id,
            'first_name' => $attributes['first_name'] ?? fake()->firstName(),
            'last_name' => $attributes['last_name'] ?? fake()->lastName(),
            'date_of_birth' => $attributes['date_of_birth'] ?? fake()->dateTimeBetween('-25 years', '-18 years')->format('Y-m-d'),
            'gender' => $attributes['gender'] ?? fake()->randomElement(['male', 'female']),
            'bio' => $attributes['bio'] ?? fake()->paragraph(),
            'city' => $attributes['city'] ?? fake()->city(),
            'country' => $attributes['country'] ?? fake()->country(),
            'latitude' => $attributes['latitude'] ?? fake()->latitude(),
            'longitude' => $attributes['longitude'] ?? fake()->longitude(),
            'occupation' => $attributes['occupation'] ?? fake()->jobTitle(),
            'education' => $attributes['education'] ?? fake()->randomElement(['high_school', 'bachelor', 'master', 'phd']),
            'height' => $attributes['height'] ?? fake()->numberBetween(150, 200),
            'religion' => $attributes['religion'] ?? 'Islam',
            'drinking' => $attributes['drinking'] ?? 'never',
            'smoking' => $attributes['smoking'] ?? 'never',
        ]);

        return $user;
    }
}