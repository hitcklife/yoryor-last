<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\CallInitiatedEvent;
use App\Events\CallStatusChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\User;
use App\Services\AgoraService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AgoraController extends Controller
{
    protected $agoraService;

    public function __construct(AgoraService $agoraService)
    {
        $this->agoraService = $agoraService;
    }

    /**
     * Generate a token for Agora RTC
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateToken(Request $request): JsonResponse
    {
        $request->validate([
            'channel_name' => 'required|string',
            'uid' => 'required|string',
            'role' => 'required',
        ]);

        $user = Auth::user();

        try {
            // Validate that the user has permission to access this channel
            // Channel name should be a call's channel name
//            $call = Call::where('channel_name', $request->channel_name)
//                ->where(function ($query) use ($user) {
//                    $query->where('caller_id', $user->id)
//                          ->orWhere('receiver_id', $user->id);
//                })
//                ->first();
//
//            if (!$call) {
//                return response()->json([
//                    'status' => 'error',
//                    'message' => 'Unauthorized to access this channel'
//                ], 403);
//            }
//
//            // Validate that the UID matches the user's ID
//            if ($request->uid !== (string)$user->id) {
//                return response()->json([
//                    'status' => 'error',
//                    'message' => 'UID must match authenticated user ID'
//                ], 400);
//            }

            $token = $this->agoraService->generateRtcToken(
                $request->channel_name,
                $request->uid,
                2
            );

            return response()->json([
                'status' => 'success',
                'data' => [
                    'token' => $token,
                    'channel_name' => $request->channel_name,
                    'uid' => $request->uid,
                    'expires_in' => 3600 // 1 hour
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Failed to generate Agora token: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'channel_name' => $request->channel_name,
                'uid' => $request->uid
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate token'
            ], 500);
        }
    }

    /**
     * Initiate a call to another user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function initiateCall(Request $request): JsonResponse
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'type' => ['required', Rule::in(['video', 'voice'])],
        ]);

        $caller = Auth::user();
        $receiverId = $request->receiver_id;

        // Prevent calling yourself
        if ($caller->id === $receiverId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot call yourself'
            ], 400);
        }

        // Check if there's already an active call between these users
        $existingCall = Call::where(function ($query) use ($caller, $receiverId) {
            $query->where('caller_id', $caller->id)->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($caller, $receiverId) {
            $query->where('caller_id', $receiverId)->where('receiver_id', $caller->id);
        })->whereIn('status', ['initiated', 'ongoing'])
          ->first();

        if ($existingCall) {
            return response()->json([
                'status' => 'error',
                'message' => 'There is already an active call between these users'
            ], 409);
        }

        $receiver = User::with('profile')->findOrFail($receiverId);

        try {
            $call = $this->agoraService->createCall($caller, $receiver, $request->type);

            // Generate token for the caller
            $token = $this->agoraService->generateRtcToken(
                $call->channel_name,
                (string) $caller->id
            );

            // Broadcast call event to the receiver
            event(new CallInitiatedEvent($call));

            return response()->json([
                'status' => 'success',
                'data' => [
                    'call_id' => $call->id,
                    'channel_name' => $call->channel_name,
                    'token' => $token,
                    'type' => $call->type,
                    'caller' => [
                        'id' => $caller->id,
                        'name' => $caller->name,
                    ],
                    'receiver' => [
                        'id' => $receiver->id,
                        'name' => $receiver->name,
                    ],
                ]
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to initiate call: ' . $e->getMessage(), [
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'type' => $request->type
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate call'
            ], 500);
        }
    }

    /**
     * Join an existing call
     *
     * @param Request $request
     * @param int $callId
     * @return JsonResponse
     */
    public function joinCall(Request $request, int $callId): JsonResponse
    {
        $call = Call::with(['caller.profile', 'receiver.profile'])->findOrFail($callId);
        $user = Auth::user();

        // Check if the user is the receiver of the call
        if ($call->receiver_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to join this call'
            ], 403);
        }

        // Check if call is in initiated status
        if ($call->status !== 'initiated') {
            return response()->json([
                'status' => 'error',
                'message' => 'Call is not available to join. Current status: ' . $call->status
            ], 400);
        }

        try {
            // Update call status to ongoing
            $this->agoraService->updateCallStatus($call, 'ongoing');

            // Generate token for the receiver
            $token = $this->agoraService->generateRtcToken(
                $call->channel_name,
                (string) $user->id
            );

            // Broadcast call status changed event
            event(new CallStatusChangedEvent($call, $user->id));

            return response()->json([
                'status' => 'success',
                'data' => [
                    'call_id' => $call->id,
                    'channel_name' => $call->channel_name,
                    'token' => $token,
                    'type' => $call->type,
                    'caller' => [
                        'id' => $call->caller->id,
                        'name' => $call->caller->name,
                    ],
                    'receiver' => [
                        'id' => $call->receiver->id,
                        'name' => $call->receiver->name,
                    ],
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Failed to join call: ' . $e->getMessage(), [
                'call_id' => $callId,
                'user_id' => $user->id
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to join call'
            ], 500);
        }
    }

    /**
     * End an ongoing call
     *
     * @param Request $request
     * @param int $callId
     * @return JsonResponse
     */
    public function endCall(Request $request, int $callId): JsonResponse
    {
        $call = Call::with(['caller.profile', 'receiver.profile'])->findOrFail($callId);
        $user = Auth::user();

        // Check if the user is part of the call
        if ($call->caller_id !== $user->id && $call->receiver_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to end this call'
            ], 403);
        }

        // Check if call can be ended
        if (!in_array($call->status, ['initiated', 'ongoing'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Call cannot be ended. Current status: ' . $call->status
            ], 400);
        }

        try {
            // Update call status to completed
            $this->agoraService->updateCallStatus($call, 'completed');

            // Broadcast call status changed event
            event(new CallStatusChangedEvent($call, $user->id));

            $duration = $call->ended_at && $call->started_at
                ? $call->ended_at->diffInSeconds($call->started_at)
                : 0;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'message' => 'Call ended successfully',
                    'call_id' => $call->id,
                    'duration' => $duration,
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Failed to end call: ' . $e->getMessage(), [
                'call_id' => $callId,
                'user_id' => $user->id
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to end call'
            ], 500);
        }
    }

    /**
     * Reject an incoming call
     *
     * @param Request $request
     * @param int $callId
     * @return JsonResponse
     */
    public function rejectCall(Request $request, int $callId): JsonResponse
    {
        $call = Call::with(['caller.profile', 'receiver.profile'])->findOrFail($callId);
        $user = Auth::user();

        // Check if the user is the receiver of the call
        if ($call->receiver_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to reject this call'
            ], 403);
        }

        // Check if call can be rejected
        if ($call->status !== 'initiated') {
            return response()->json([
                'status' => 'error',
                'message' => 'Call cannot be rejected. Current status: ' . $call->status
            ], 400);
        }

        try {
            // Update call status to rejected
            $this->agoraService->updateCallStatus($call, 'rejected');

            // Broadcast call status changed event
            event(new CallStatusChangedEvent($call, $user->id));

            return response()->json([
                'status' => 'success',
                'data' => [
                    'message' => 'Call rejected successfully',
                    'call_id' => $call->id,
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Failed to reject call: ' . $e->getMessage(), [
                'call_id' => $callId,
                'user_id' => $user->id
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject call'
            ], 500);
        }
    }

    /**
     * Get call history for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCallHistory(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:50',
            'status' => 'sometimes|string|in:completed,rejected,missed',
            'type' => 'sometimes|string|in:video,voice'
        ]);

        $query = Call::where('caller_id', $user->id)
            ->orWhere('receiver_id', $user->id);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $calls = $query->with([
                'caller:id,name',
                'caller.profile:id,user_id,first_name,last_name',
                'caller.profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo',
                'receiver:id,name',
                'receiver.profile:id,user_id,first_name,last_name',
                'receiver.profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $calls
        ]);
    }
}
