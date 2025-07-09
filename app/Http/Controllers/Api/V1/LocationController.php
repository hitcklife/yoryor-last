<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LocationController extends Controller
{
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
    public function updateLocation(Request $request): ApiResource
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'accuracy' => 'nullable|numeric|min:0',
                'altitude' => 'nullable|numeric',
                'heading' => 'nullable|numeric',
                'speed' => 'nullable|numeric',
            ], [
                'latitude.required' => 'Latitude is required',
                'latitude.numeric' => 'Latitude must be a number',
                'latitude.between' => 'Latitude must be between -90 and 90 degrees',
                'longitude.required' => 'Longitude is required',
                'longitude.numeric' => 'Longitude must be a number',
                'longitude.between' => 'Longitude must be between -180 and 180 degrees',
                'accuracy.numeric' => 'Accuracy must be a number',
                'accuracy.min' => 'Accuracy must be a positive number',
                'altitude.numeric' => 'Altitude must be a number',
                'heading.numeric' => 'Heading must be a number',
                'heading.between' => 'Heading must be between 0 and 360 degrees',
                'speed.numeric' => 'Speed must be a number',
                'speed.min' => 'Speed must be a positive number',
            ]);

            $user = $request->user();

            // Get or create user's profile
            $profile = $user->profile;

            if (!$profile) {
                return ApiResource::error(null, 'Profile not found', 404);
            }

            // Update only latitude and longitude as requested
            $profile->update([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

            // Log the location update for debugging/analytics
            Log::info('User location updated', [
                'user_id' => $user->id,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'accuracy' => $validated['accuracy'] ?? null,
                'altitude' => $validated['altitude'] ?? null,
                'heading' => $validated['heading'] ?? null,
                'speed' => $validated['speed'] ?? null,
            ]);

            return ApiResource::success([
                'latitude' => $profile->latitude,
                'longitude' => $profile->longitude,
                'updated_at' => $profile->updated_at,
            ], 'Location updated successfully');

        } catch (ValidationException $e) {
            return ApiResource::error($e->errors(), 'Validation failed', 400);
        } catch (\Exception $e) {
            Log::error('Failed to update user location', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResource::error(null, 'Failed to update location', 500);
        }
    }
}
