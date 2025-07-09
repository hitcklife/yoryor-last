<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeviceTokenController extends Controller
{
    /**
     * Store a device token for push notifications.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'token' => 'required|string',
                'deviceName' => 'nullable|string',
                'brand' => 'nullable|string',
                'modelName' => 'nullable|string',
                'osName' => 'nullable|string',
                'osVersion' => 'nullable|string',
                'deviceType' => 'nullable|string|in:PHONE,TABLET,DESKTOP,OTHER',
                'isDevice' => 'nullable|boolean',
                'manufacturer' => 'nullable|string',
            ]);

            $user = Auth::user();

            // Check if the token already exists for this user
            $deviceToken = $user->deviceTokens()
                ->where('token', $validated['token'])
                ->first();

            if ($deviceToken) {
                // Update existing token
                $deviceToken->update([
                    'device_name' => $validated['deviceName'] ?? $deviceToken->device_name,
                    'brand' => $validated['brand'] ?? $deviceToken->brand,
                    'model_name' => $validated['modelName'] ?? $deviceToken->model_name,
                    'os_name' => $validated['osName'] ?? $deviceToken->os_name,
                    'os_version' => $validated['osVersion'] ?? $deviceToken->os_version,
                    'device_type' => $validated['deviceType'] ?? $deviceToken->device_type,
                    'is_device' => $validated['isDevice'] ?? $deviceToken->is_device,
                    'manufacturer' => $validated['manufacturer'] ?? $deviceToken->manufacturer,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Device token updated successfully',
                    'data' => $deviceToken
                ]);
            }

            // Create new token
            $deviceToken = $user->deviceTokens()->create([
                'token' => $validated['token'],
                'device_name' => $validated['deviceName'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'model_name' => $validated['modelName'] ?? null,
                'os_name' => $validated['osName'] ?? null,
                'os_version' => $validated['osVersion'] ?? null,
                'device_type' => $validated['deviceType'] ?? 'PHONE',
                'is_device' => $validated['isDevice'] ?? true,
                'manufacturer' => $validated['manufacturer'] ?? null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Device token stored successfully',
                'data' => $deviceToken
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to store device token: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store device token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a device token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'token' => 'required|string',
            ]);

            $user = Auth::user();
            $token = $validated['token'];

            // Find and delete the token
            $deleted = $user->deviceTokens()
                ->where('token', $token)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Device token deleted successfully'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Device token not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete device token: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete device token: ' . $e->getMessage()
            ], 500);
        }
    }
}
