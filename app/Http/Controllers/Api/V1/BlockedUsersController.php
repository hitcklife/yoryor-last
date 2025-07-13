<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserBlock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BlockedUsersController extends Controller
{
    /**
     * Get list of blocked users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBlockedUsers(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get blocked users with pagination
        $blockedUsers = UserBlock::where('blocker_id', $user->id)
            ->with('blocked:id,name,email') // Only include necessary fields
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $blockedUsers
        ]);
    }

    /**
     * Block a user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function blockUser(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if trying to block self
        if ($user->id == $request->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot block yourself'
            ], 422);
        }

        // Check if already blocked
        $existingBlock = UserBlock::where('blocker_id', $user->id)
            ->where('blocked_id', $request->user_id)
            ->first();

        if ($existingBlock) {
            return response()->json([
                'success' => false,
                'message' => 'User is already blocked'
            ], 422);
        }

        // Create the block
        $blockedUser = new UserBlock([
            'blocker_id' => $user->id,
            'blocked_id' => $request->user_id,
            'reason' => $request->reason,
        ]);

        $blockedUser->save();

        return response()->json([
            'success' => true,
            'message' => 'User blocked successfully',
            'data' => $blockedUser
        ]);
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
        $user = $request->user();

        // Find the block
        $blockedUser = UserBlock::where('blocker_id', $user->id)
            ->where('blocked_id', $userId)
            ->first();

        if (!$blockedUser) {
            return response()->json([
                'success' => false,
                'message' => 'User is not blocked'
            ], 404);
        }

        // Delete the block
        $blockedUser->delete();

        return response()->json([
            'success' => true,
            'message' => 'User unblocked successfully'
        ]);
    }
}
