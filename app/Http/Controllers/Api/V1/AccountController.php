<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DataExportRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Change user password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 422);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Change user email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changeEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'new_email' => 'required|string|email|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if password is correct
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect'
            ], 422);
        }

        // Update email
        $user->email = $request->new_email;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Email changed successfully'
        ]);
    }

    /**
     * Delete user account
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if password is correct
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect'
            ], 422);
        }

        // Log the deletion reason if provided
        if ($request->has('reason') && !empty($request->reason)) {
            // You could store this in a separate table if needed
            // For now, we'll just log it
            \Log::info('User ' . $user->id . ' deleted account. Reason: ' . $request->reason);
        }

        // Revoke all tokens
        $user->tokens()->delete();

        // Delete the user (or soft delete if your app uses SoftDeletes)
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
    }

    /**
     * Request data export
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function requestDataExport(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if there's already a pending request
        $pendingRequest = $user->dataExportRequests()
            ->where('status', 'pending')
            ->first();

        if ($pendingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending data export request',
                'data' => [
                    'request_id' => $pendingRequest->id,
                    'created_at' => $pendingRequest->created_at,
                ]
            ], 422);
        }

        // Create a new data export request
        $exportRequest = new DataExportRequest([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        $exportRequest->save();

        // In a real application, you would queue a job to process this request
        // For now, we'll just return a success response

        return response()->json([
            'success' => true,
            'message' => 'Data export request submitted successfully',
            'data' => [
                'request_id' => $exportRequest->id,
                'status' => $exportRequest->status,
                'created_at' => $exportRequest->created_at,
            ]
        ]);
    }
}
