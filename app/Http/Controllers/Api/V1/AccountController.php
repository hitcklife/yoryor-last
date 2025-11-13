<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DataExportRequest;
use App\Models\User;
use App\Services\CacheService;
use App\Services\ErrorHandlingService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PasswordChangedNotification;

class AccountController extends Controller
{
    private CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    /**
     * Change user password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Enhanced validation using ValidationService
            $validated = ValidationService::validateRequest($request, [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed|different:current_password',
            ], [
                'current_password.required' => 'Current password is required',
                'new_password.required' => 'New password is required',
                'new_password.min' => 'New password must be at least 8 characters',
                'new_password.confirmed' => 'New password confirmation does not match',
                'new_password.different' => 'New password must be different from current password'
            ]);

            // Check if current password is correct
            if (!Hash::check($validated['current_password'], $user->password)) {
                return ErrorHandlingService::errorResponse(
                    'Current password is incorrect',
                    ErrorHandlingService::ERROR_CODES['INVALID_CREDENTIALS'],
                    null,
                    401
                );
            }

            // Update password with transaction for safety
            \DB::transaction(function() use ($user, $validated) {
                $user->update([
                    'password' => Hash::make($validated['new_password']),
                    'password_changed_at' => now()
                ]);

                // Revoke all other sessions for security
                $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
            });

            // Send notification
            $user->notify(new PasswordChangedNotification());

            // Clear user caches
            $this->cacheService->invalidateUserCaches($user->id);

            return ErrorHandlingService::successResponse(
                ['password_changed_at' => $user->password_changed_at],
                'Password changed successfully'
            );
        } catch (\Exception $e) {
            return ErrorHandlingService::handleException($e, 'change_password');
        }
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
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if password is correct
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
                'error_code' => 'INVALID_CREDENTIALS'
            ], 401);
        }

        // Update email
        $user->email = $request->new_email;
        $user->save();

        return response()->json([
            'status' => 'success',
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
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if password is correct
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
                'error_code' => 'INVALID_CREDENTIALS'
            ], 401);
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
            'status' => 'success',
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
                'status' => 'error',
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
            'status' => 'success',
            'message' => 'Data export request submitted successfully',
            'data' => [
                'request_id' => $exportRequest->id,
                'status' => $exportRequest->status,
                'created_at' => $exportRequest->created_at,
            ]
        ]);
    }
}
