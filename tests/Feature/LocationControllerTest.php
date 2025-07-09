<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class LocationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_update_location()
    {
        // Create a user with profile
        $user = User::factory()->create();
        $profile = Profile::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'city' => 'Test City',
        ]);

        $locationData = [
            'latitude' => 37.7749,
            'longitude' => -122.4194,
            'accuracy' => 10,
            'altitude' => 100,
            'heading' => 90,
            'speed' => 0
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/v1/location/update', $locationData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Location updated successfully',
                'data' => [
                    'latitude' => 37.7749,
                    'longitude' => -122.4194,
                ]
            ]);

        // Verify the profile was updated in database
        $profile->refresh();
        $this->assertEquals(37.7749, $profile->latitude);
        $this->assertEquals(-122.4194, $profile->longitude);
    }

    public function test_location_update_requires_authentication()
    {
        $locationData = [
            'latitude' => 37.7749,
            'longitude' => -122.4194,
        ];

        $response = $this->postJson('/api/v1/location/update', $locationData);

        $response->assertStatus(401);
    }

    public function test_location_update_validates_required_fields()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/location/update', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    public function test_location_update_validates_latitude_range()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/location/update', [
                'latitude' => 100, // Invalid: > 90
                'longitude' => -122.4194,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude']);
    }

    public function test_location_update_validates_longitude_range()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/location/update', [
                'latitude' => 37.7749,
                'longitude' => 200, // Invalid: > 180
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['longitude']);
    }

    public function test_location_update_returns_error_when_profile_not_found()
    {
        $user = User::factory()->create();
        // Don't create a profile for this user

        $locationData = [
            'latitude' => 37.7749,
            'longitude' => -122.4194,
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/v1/location/update', $locationData);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Profile not found',
            ]);
    }

    public function test_location_update_accepts_optional_fields()
    {
        $user = User::factory()->create();
        $profile = Profile::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'city' => 'Test City',
        ]);

        $locationData = [
            'latitude' => 37.7749,
            'longitude' => -122.4194,
            'accuracy' => 15,
            'altitude' => 150,
            'heading' => 180,
            'speed' => 5
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/v1/location/update', $locationData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Location updated successfully',
            ]);

        // Verify only latitude and longitude were updated
        $profile->refresh();
        $this->assertEquals(37.7749, $profile->latitude);
        $this->assertEquals(-122.4194, $profile->longitude);
    }
} 