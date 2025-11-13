<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserBlock;
use App\Models\User;
use App\Services\CacheService;
use App\Services\ErrorHandlingService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BlockedUsersController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    /**
     * Get list of blocked users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBlockedUsers(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Validate pagination parameters
            ValidationService::validatePagination($request);
            
            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);

            return $this->cacheService->remember(
                "blocked_users:{$user->id}:page:{$page}:per:{$perPage}",
                CacheService::TTL_MEDIUM,
                function() use ($user, $perPage) {
                    // Get blocked users with optimized loading
                    $blockedUsers = UserBlock::where('blocker_id', $user->id)
                        ->with([
                            'blocked:id,email',
                            'blocked.profile:id,user_id,first_name,last_name',
                            'blocked.profilePhoto:id,user_id,thumbnail_url'
                        ])
                        ->orderBy('created_at', 'desc')
                        ->paginate($perPage);

                    return ErrorHandlingService::paginatedResponse($blockedUsers, 'blocked_users');
                },
                ["user_{$user->id}_blocks"]
            );
        } catch (\Exception $e) {
            return ErrorHandlingService::handleException($e, 'get_blocked_users');
        }
    }

    /**
     * Block a user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function blockUser(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Validate the request data
            $validated = ValidationService::validateRequest($request, [
                'user_id' => 'required|integer|exists:users,id',
                'reason' => 'nullable|string|max:255',
            ], [
                'user_id.required' => 'User ID is required',
                'user_id.exists' => 'User not found',
                'reason.max' => 'Reason cannot exceed 255 characters'
            ]);

            // Business logic validation
            $error = ErrorHandlingService::validateBusinessLogic(
                $user->id !== $validated['user_id'],
                'You cannot block yourself',
                ErrorHandlingService::ERROR_CODES['INVALID_REQUEST']
            );
            if ($error) return $error;

            // Check if already blocked
            $existingBlock = UserBlock::where('blocker_id', $user->id)
                ->where('blocked_id', $validated['user_id'])
                ->first();

            $error = ErrorHandlingService::validateBusinessLogic(
                !$existingBlock,
                'User is already blocked',
                ErrorHandlingService::ERROR_CODES['DUPLICATE_ENTRY']
            );
            if ($error) return $error;

            // Create the block
            $blockedUser = UserBlock::create([
                'blocker_id' => $user->id,
                'blocked_id' => $validated['user_id'],
                'reason' => $validated['reason'],
            ]);

            // Clear cache
            $this->cacheService->invalidateUserCaches($user->id);

            return ErrorHandlingService::successResponse(
                $blockedUser,
                'User blocked successfully',
                201
            );
        } catch (\Exception $e) {
            return ErrorHandlingService::handleException($e, 'block_user');
        }
    }

    /**
     * Unblock a user
     *
     * @param Request $request
     * @param int $userId
     * @return JsonResponse
     */
    public function unblockUser(Request $request, int $userId): JsonResponse
    {
        try {
            $user = $request->user();

            // Validate user ID exists
            $targetUser = User::find($userId);
            if (!$targetUser) {
                return ErrorHandlingService::notFoundError('User');
            }

            // Find the block
            $blockedUser = UserBlock::where('blocker_id', $user->id)
                ->where('blocked_id', $userId)
                ->first();

            if (!$blockedUser) {
                return ErrorHandlingService::notFoundError('Block record');
            }

            // Delete the block
            $blockedUser->delete();

            // Clear cache
            $this->cacheService->invalidateUserCaches($user->id);

            return ErrorHandlingService::successResponse(
                null,
                'User unblocked successfully'
            );
        } catch (\Exception $e) {
            return ErrorHandlingService::handleException($e, 'unblock_user');
        }
    }
}
