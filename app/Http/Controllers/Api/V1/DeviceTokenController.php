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
            $token = $validated['token'];

            // Check if the token already exists globally (not just for this user)
            $existingToken = DeviceToken::where('token', $token)->first();

            if ($existingToken) {
                // If token exists but belongs to different user, update it to current user
                if ($existingToken->user_id !== $user->id) {
                    $existingToken->update([
                        'user_id' => $user->id,
                        'device_name' => $validated['deviceName'] ?? $existingToken->device_name,
                        'brand' => $validated['brand'] ?? $existingToken->brand,
                        'model_name' => $validated['modelName'] ?? $existingToken->model_name,
                        'os_name' => $validated['osName'] ?? $existingToken->os_name,
                        'os_version' => $validated['osVersion'] ?? $existingToken->os_version,
                        'device_type' => $validated['deviceType'] ?? $existingToken->device_type,
                        'is_device' => $validated['isDevice'] ?? $existingToken->is_device,
                        'manufacturer' => $validated['manufacturer'] ?? $existingToken->manufacturer,
                    ]);
                } else {
                    // Token exists and belongs to current user, just update the device info
                    $existingToken->update([
                        'device_name' => $validated['deviceName'] ?? $existingToken->device_name,
                        'brand' => $validated['brand'] ?? $existingToken->brand,
                        'model_name' => $validated['modelName'] ?? $existingToken->model_name,
                        'os_name' => $validated['osName'] ?? $existingToken->os_name,
                        'os_version' => $validated['osVersion'] ?? $existingToken->os_version,
                        'device_type' => $validated['deviceType'] ?? $existingToken->device_type,
                        'is_device' => $validated['isDevice'] ?? $existingToken->is_device,
                        'manufacturer' => $validated['manufacturer'] ?? $existingToken->manufacturer,
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Device token updated successfully',
                    'data' => $existingToken
                ]);
            }

            // Token doesn't exist, create new one
            $deviceToken = $user->deviceTokens()->create([
                'token' => $token,
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
                'request' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store device token',
                'error_code' => 'DEVICE_TOKEN_STORE_FAILED'
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
                'message' => 'Failed to delete device token',
                'error_code' => 'DEVICE_TOKEN_DELETE_FAILED'
            ], 500);
        }
    }
}
