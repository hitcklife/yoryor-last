<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\CallInitiatedEvent;
use App\Events\CallStatusChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\User;
use App\Services\VideoSDKService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class VideoCallController extends Controller
{
    protected $videoSDKService;

    public function __construct(VideoSDKService $videoSDKService)
    {
        $this->videoSDKService = $videoSDKService;
    }

    /**
     * Generate a token for Video SDK
     *
     * @return JsonResponse
     */
    public function getToken(): JsonResponse
    {
        $user = Auth::user();

        try {
            $token = $this->videoSDKService->generateToken();

            return response()->json([
                'token' => $token,
                'success' => true
            ]);
        } catch (Exception $e) {
            Log::error('Failed to generate Video SDK token: ' . $e->getMessage(), [
                'user_id' => $user->id
            ]);
            return response()->json([
                'error' => 'Failed to generate token',
                'success' => false
            ], 500);
        }
    }

    /**
     * Create a meeting
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createMeeting(Request $request): JsonResponse
    {
        $request->validate([
            'customRoomId' => 'sometimes|string',
        ]);

        $user = Auth::user();

        try {
            $meetingData = $this->videoSDKService->createMeeting($request->input('customRoomId'));

            return response()->json([
                'meetingId' => $meetingData['meetingId'],
                'token' => $meetingData['token'],
                'success' => true
            ]);
        } catch (Exception $e) {
            Log::error('Failed to create meeting: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'customRoomId' => $request->input('customRoomId')
            ]);
            return response()->json([
                'error' => 'Failed to create meeting',
                'success' => false
            ], 500);
        }
    }

    /**
     * Validate a meeting
     *
     * @param string $meetingId
     * @return JsonResponse
     */
    public function validateMeeting(string $meetingId): JsonResponse
    {
        $user = Auth::user();

        try {
            $validationData = $this->videoSDKService->validateMeeting($meetingId);

            return response()->json([
                'valid' => $validationData['valid'],
                'meetingId' => $validationData['meetingId'],
                'success' => true
            ]);
        } catch (Exception $e) {
            Log::error('Failed to validate meeting: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'meetingId' => $meetingId
            ]);
            return response()->json([
                'error' => 'Failed to validate meeting',
                'success' => false
            ], 500);
        }
    }

    /**
     * Initiate a call to another user using Video SDK
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

        $receiver = User::findOrFail($receiverId);

        try {
            $callData = $this->videoSDKService->createCall($caller, $receiver, $request->type);
            $call = $callData['call'];
            $token = $callData['token'];

            // Broadcast call event to the receiver
            event(new CallInitiatedEvent($call));

            return response()->json([
                'status' => 'success',
                'data' => [
                    'call_id' => $call->id,
                    'meeting_id' => $call->channel_name, // Using channel_name to store meeting ID
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
     * @param int $callId
     * @return JsonResponse
     */
    public function joinCall(int $callId): JsonResponse
    {
        $call = Call::findOrFail($callId);
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
            $call = $this->videoSDKService->updateCallStatus($call, 'ongoing');

            // Generate token for the receiver
            $token = $this->videoSDKService->generateToken();

            // Broadcast call status changed event
            event(new CallStatusChangedEvent($call, $user->id));

            return response()->json([
                'status' => 'success',
                'data' => [
                    'call_id' => $call->id,
                    'meeting_id' => $call->channel_name, // Using channel_name to store meeting ID
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
     * @param int $callId
     * @return JsonResponse
     */
    public function endCall(int $callId): JsonResponse
    {
        $call = Call::findOrFail($callId);
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
            $call = $this->videoSDKService->updateCallStatus($call, 'completed');

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
     * @param int $callId
     * @return JsonResponse
     */
    public function rejectCall(int $callId): JsonResponse
    {
        $call = Call::findOrFail($callId);
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
            $call = $this->videoSDKService->updateCallStatus($call, 'rejected');

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

        $calls = $query->with(['caller:id,name,profile_photo_path', 'receiver:id,name,profile_photo_path'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $calls
        ]);
    }
}
