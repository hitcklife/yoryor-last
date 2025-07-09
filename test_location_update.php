<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\LocationController;
use App\Models\User;
use App\Models\Profile;

// Create a test user with profile
$user = User::factory()->create();
$profile = Profile::create([
    'user_id' => $user->id,
    'first_name' => 'Test',
    'last_name' => 'User',
    'gender' => 'male',
    'date_of_birth' => '1990-01-01',
    'city' => 'Test City',
]);

echo "Created test user with ID: " . $user->id . "\n";
echo "Created profile with ID: " . $profile->id . "\n";

// Create a mock request
$request = new Request();
$request->merge([
    'latitude' => 37.7749,
    'longitude' => -122.4194,
    'accuracy' => 10,
    'altitude' => 100,
    'heading' => 90,
    'speed' => 0
]);

// Mock the authenticated user
$request->setUserResolver(function () use ($user) {
    return $user;
});

// Test the controller
$controller = new LocationController();
$response = $controller->updateLocation($request);

echo "Response status: " . $response->getStatusCode() . "\n";
echo "Response content: " . $response->getContent() . "\n";

// Verify the profile was updated
$profile->refresh();
echo "Updated profile latitude: " . $profile->latitude . "\n";
echo "Updated profile longitude: " . $profile->longitude . "\n"; 