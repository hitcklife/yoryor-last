<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Services\CacheService;
use App\Services\ErrorHandlingService;
use App\Services\ValidationService;
use App\Services\PresenceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LocationController extends Controller
{
    private CacheService $cacheService;
    private PresenceService $presenceService;

    public function __construct(CacheService $cacheService, PresenceService $presenceService)
    {
        $this->cacheService = $cacheService;
        $this->presenceService = $presenceService;
    }
    /**
     * Update user's location
     *
     * @OA\Post(
     *     path="/api/v1/location/update",
     *     summary="Update user's location",
     *     description="Update the authenticated user's latitude and longitude coordinates",
     *     tags={"Location"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"latitude","longitude"},
     *             @OA\Property(property="latitude", type="number", format="float", example=37.7749, description="Latitude coordinate"),
     *             @OA\Property(property="longitude", type="number", format="float", example=-122.4194, description="Longitude coordinate"),
     *             @OA\Property(property="accuracy", type="number", format="float", example=10, description="Location accuracy in meters"),
     *             @OA\Property(property="altitude", type="number", format="float", example=100, description="Altitude in meters"),
     *             @OA\Property(property="heading", type="number", format="float", example=90, description="Heading in degrees"),
     *             @OA\Property(property="speed", type="number", format="float", example=0, description="Speed in meters per second")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Location updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Location updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="latitude", type="number", example=37.7749),
     *                 @OA\Property(property="longitude", type="number", example=-122.4194),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Profile not found"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to update location"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function updateLocation(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Enhanced validation using ValidationService with rate limiting awareness
            $validated = ValidationService::validateRequest($request, [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'accuracy' => 'nullable|numeric',
                'altitude' => 'nullable|numeric',
                'heading' => 'nullable|numeric',
                'speed' => 'nullable|numeric',
            ], [
                'latitude.required' => 'Latitude is required',
                'latitude.between' => 'Latitude must be between -90 and 90 degrees',
                'longitude.required' => 'Longitude is required',
                'longitude.between' => 'Longitude must be between -180 and 180 degrees',
            ]);
            
            // Normalize values: treat negative values as null/0 for optional fields
            if (isset($validated['accuracy']) && $validated['accuracy'] < 0) {
                $validated['accuracy'] = null;
            }
            if (isset($validated['altitude']) && $validated['altitude'] < -1000) {
                $validated['altitude'] = null;
            }
            if (isset($validated['heading']) && ($validated['heading'] < 0 || $validated['heading'] > 360)) {
                $validated['heading'] = null;
            }
            if (isset($validated['speed']) && $validated['speed'] < 0) {
                $validated['speed'] = null;
            }

            // Get or create user's profile with optimized loading
            $profile = $user->profile;
            if (!$profile) {
                return ErrorHandlingService::notFoundError('Profile');
            }

            // Check for significant location change to avoid unnecessary updates
            $significantChange = $this->isSignificantLocationChange(
                $profile,
                $validated['latitude'],
                $validated['longitude']
            );

            if ($significantChange) {
                // Batch update location and related data
                $profile->update([
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'location_updated_at' => now(),
                ]);

                // Update presence data with new location
                $this->presenceService->markUserOnline($user);

                // Clear location-related caches
                $this->cacheService->flushByTags([
                    "user_{$user->id}_location",
                    "user_{$user->id}_matches",
                    "nearby_users"
                ]);

                // Log significant location updates
                Log::info('User location updated', [
                    'user_id' => $user->id,
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'accuracy' => $validated['accuracy'] ?? null,
                    'previous_lat' => $profile->getOriginal('latitude'),
                    'previous_lng' => $profile->getOriginal('longitude'),
                ]);
            }

            return ErrorHandlingService::successResponse([
                'latitude' => $profile->latitude,
                'longitude' => $profile->longitude,
                'updated_at' => $profile->updated_at,
                'location_updated_at' => $profile->location_updated_at,
                'accuracy' => $validated['accuracy'] ?? null,
            ], 'Location updated successfully');

        } catch (\Exception $e) {
            return ErrorHandlingService::handleException($e, 'update_location');
        }
    }

    /**
     * Check if location change is significant enough to warrant an update
     */
    private function isSignificantLocationChange($profile, float $newLat, float $newLng): bool
    {
        if (!$profile->latitude || !$profile->longitude) {
            return true; // First time setting location
        }

        // Calculate distance using Haversine formula (in meters)
        $earthRadius = 6371000; // Earth's radius in meters
        
        $deltaLat = deg2rad($newLat - $profile->latitude);
        $deltaLng = deg2rad($newLng - $profile->longitude);
        
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos(deg2rad($profile->latitude)) * cos(deg2rad($newLat)) *
             sin($deltaLng / 2) * sin($deltaLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        // Only update if user moved more than 50 meters or hasn't updated in 5 minutes
        $timeSinceLastUpdate = $profile->location_updated_at 
            ? now()->diffInMinutes($profile->location_updated_at) 
            : 999;

        return $distance > 50 || $timeSinceLastUpdate > 5;
    }
}
