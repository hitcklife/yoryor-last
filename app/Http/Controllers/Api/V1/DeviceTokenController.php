<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DeviceTokenController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    /**
     * Store a device token for push notifications.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string|max:255',
                'deviceName' => 'nullable|string|max:100',
                'brand' => 'nullable|string|max:50',
                'modelName' => 'nullable|string|max:100',
                'osName' => 'nullable|string|max:50',
                'osVersion' => 'nullable|string|max:50',
                'deviceType' => 'nullable|string|in:PHONE,TABLET,DESKTOP,OTHER',
                'isDevice' => 'nullable|boolean',
                'manufacturer' => 'nullable|string|max:50',
            ]);

            $user = Auth::user();
            $token = $validated['token'];

            return DB::transaction(function () use ($user, $token, $validated) {
                // Use upsert for better performance and atomic operations
                $deviceToken = DeviceToken::updateOrCreate(
                    ['token' => $token],
                    [
                        'user_id' => $user->id,
                        'device_name' => $validated['deviceName'] ?? null,
                        'brand' => $validated['brand'] ?? null,
                        'model_name' => $validated['modelName'] ?? null,
                        'os_name' => $validated['osName'] ?? null,
                        'os_version' => $validated['osVersion'] ?? null,
                        'device_type' => $validated['deviceType'] ?? 'PHONE',
                        'is_device' => $validated['isDevice'] ?? true,
                        'manufacturer' => $validated['manufacturer'] ?? null,
                        'last_used_at' => now(),
                    ]
                );

                // Clear any cached user device tokens
                $this->cacheService->forget("user_device_tokens:{$user->id}");

                return response()->json([
                    'status' => 'success',
                    'message' => $deviceToken->wasRecentlyCreated ? 'Device token stored successfully' : 'Device token updated successfully',
                    'data' => $deviceToken
                ], $deviceToken->wasRecentlyCreated ? 201 : 200);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to store device token', [
                'user_id' => Auth::id(),
                'token_length' => strlen($request->input('token', '')),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
            $validated = $request->validate([
                'token' => 'required|string|max:255',
            ]);

            $user = Auth::user();
            $token = $validated['token'];

            return DB::transaction(function () use ($user, $token) {
                // Find and delete the token with better performance
                $deleted = DeviceToken::where('user_id', $user->id)
                    ->where('token', $token)
                    ->delete();

                if ($deleted) {
                    // Clear any cached user device tokens
                    $this->cacheService->forget("user_device_tokens:{$user->id}");

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Device token deleted successfully'
                    ]);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Device token not found or does not belong to you',
                    'error_code' => 'DEVICE_TOKEN_NOT_FOUND'
                ], 404);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to delete device token', [
                'user_id' => Auth::id(),
                'token_length' => strlen($request->input('token', '')),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete device token',
                'error_code' => 'DEVICE_TOKEN_DELETE_FAILED'
            ], 500);
        }
    }

    /**
     * Get all device tokens for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $deviceTokens = $this->cacheService->remember(
                "user_device_tokens:{$user->id}",
                3600, // 1 hour cache
                function () use ($user) {
                    return $user->deviceTokens()
                        ->select('id', 'device_name', 'brand', 'model_name', 'os_name', 'os_version', 'device_type', 'is_device', 'manufacturer', 'last_used_at', 'created_at')
                        ->orderBy('last_used_at', 'desc')
                        ->get();
                }
            );

            return response()->json([
                'status' => 'success',
                'data' => [
                    'device_tokens' => $deviceTokens,
                    'total' => $deviceTokens->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch device tokens', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch device tokens',
                'error_code' => 'DEVICE_TOKENS_FETCH_FAILED'
            ], 500);
        }
    }
}
