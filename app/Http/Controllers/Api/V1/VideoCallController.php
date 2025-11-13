<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\CallInitiatedEvent;
use App\Events\CallStatusChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\User;
use App\Services\CallMessageService;
use App\Services\VideoSDKService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class VideoCallController extends Controller
{
    protected $videoSDKService;

    protected $callMessageService;

    public function __construct(VideoSDKService $videoSDKService, CallMessageService $callMessageService)
    {
        $this->videoSDKService = $videoSDKService;
        $this->callMessageService = $callMessageService;
    }

    /**
     * Generate a token for Video SDK
     *
     * @OA\Post(
     *     path="/v1/video-call/token",
     *     summary="Generate Video SDK token",
     *     description="Generates an authentication token for Video SDK",
     *     operationId="getVideoCallToken",
     *     tags={"Video Calls"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Token generated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Failed to generate token",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function getToken(): JsonResponse
    {
        $user = Auth::user();

        try {
            $token = $this->videoSDKService->generateToken();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'token' => $token,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to generate Video SDK token: '.$e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            // Check if it's a configuration issue
            if (str_contains($e->getMessage(), 'not configured')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'VideoSDK is not configured. Please set VIDEOSDK_API_KEY and VIDEOSDK_SECRET_KEY in your .env file.',
                    'error' => 'Configuration missing',
                ], 500);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate token: '.$e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a meeting
     *
     * @OA\Post(
     *     path="/v1/video-call/create-meeting",
     *     summary="Create a video meeting",
     *     description="Creates a new video meeting room",
     *     operationId="createVideoMeeting",
     *     tags={"Video Calls"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="customRoomId", type="string", example="my-custom-room", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Meeting created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="meetingId", type="string", example="abc-defg-hijk"),
     *                 @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create meeting",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
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
                'status' => 'success',
                'data' => [
                    'meetingId' => $meetingData['meetingId'],
                    'token' => $meetingData['token'],
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to create meeting: '.$e->getMessage(), [
                'user_id' => $user->id,
                'customRoomId' => $request->input('customRoomId'),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create meeting',
                'error' => 'Failed to create meeting',
            ], 500);
        }
    }

    /**
     * Validate a meeting
     */
    public function validateMeeting(string $meetingId): JsonResponse
    {
        $user = Auth::user();

        try {
            $validationData = $this->videoSDKService->validateMeeting($meetingId);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'valid' => $validationData['valid'],
                    'meetingId' => $validationData['meetingId'],
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to validate meeting: '.$e->getMessage(), [
                'user_id' => $user->id,
                'meetingId' => $meetingId,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to validate meeting',
                'error' => 'Failed to validate meeting',
            ], 500);
        }
    }

    /**
     * Initiate a call to another user using Video SDK
     *
     * @OA\Post(
     *     path="/v1/video-call/initiate",
     *     summary="Initiate a video call",
     *     description="Initiates a video or voice call to another user",
     *     operationId="initiateVideoCall",
     *     tags={"Video Calls"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"recipient_id", "call_type"},
     *
     *             @OA\Property(property="recipient_id", type="integer", example=2, description="ID of the user to call"),
     *             @OA\Property(property="call_type", type="string", enum={"video", "voice"}, example="video", description="Type of call")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Call initiated successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="call_id", type="integer", example=1),
     *                 @OA\Property(property="meeting_id", type="string", example="abc-defg-hijk"),
     *                 @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
     *                 @OA\Property(property="type", type="string", example="video"),
     *                 @OA\Property(property="message_id", type="integer", example=123, nullable=true),
     *                 @OA\Property(property="caller", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe")
     *                 ),
     *                 @OA\Property(property="receiver", type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Jane Smith")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/Error")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/Error"))
     * )
     */
    public function initiateCall(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'call_type' => ['required', Rule::in(['video', 'voice'])],
        ]);

        $caller = Auth::user();
        $caller->load('profile');
        $receiverId = $request->recipient_id;

        // Prevent calling yourself
        if ($caller->id === $receiverId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot call yourself',
            ], 400);
        }

        // Check if there's already an active call between these users
        $existingCall = Call::with(['caller.profile', 'receiver.profile'])
            ->where(function ($query) use ($caller, $receiverId) {
                $query->where('caller_id', $caller->id)->where('receiver_id', $receiverId);
            })->whereIn('status', ['initiated', 'ongoing'])
            ->orWhere(function ($query) use ($caller, $receiverId) {
                $query->where('caller_id', $receiverId)->where('receiver_id', $caller->id);
            })->whereIn('status', ['initiated', 'ongoing'])
            ->first();

        if ($existingCall) {
            // Instead of returning an error, return the existing call details
            // so the caller can join the existing call
            try {
                // Generate a token for the existing call
                $token = $this->videoSDKService->generateToken($existingCall->channel_name);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Joining existing call',
                    'data' => [
                        'call_id' => $existingCall->id,
                        'meeting_id' => $existingCall->channel_name,
                        'token' => $token,
                        'type' => $existingCall->type,
                        'caller' => [
                            'id' => $existingCall->caller->id,
                            'name' => $existingCall->caller->full_name,
                        ],
                        'receiver' => [
                            'id' => $existingCall->receiver->id,
                            'name' => $existingCall->receiver->full_name,
                        ],
                    ],
                ]);
            } catch (Exception $e) {
                Log::error('Failed to join existing call: '.$e->getMessage(), [
                    'call_id' => $existingCall->id,
                    'user_id' => $caller->id,
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to join existing call',
                ], 500);
            }
        }

        $receiver = User::with('profile')->findOrFail($receiverId);

        try {
            $callData = $this->videoSDKService->createCall($caller, $receiver, $request->call_type);
            $call = $callData['call'];
            $token = $callData['token'];

            // Create call message automatically
            $callMessage = $this->callMessageService->createOrUpdateCallMessage($call, 'initiated');

            // Broadcast call event to the receiver
            event(new CallInitiatedEvent($call));

            return response()->json([
                'status' => 'success',
                'data' => [
                    'call_id' => $call->id,
                    'meeting_id' => $call->channel_name,
                    'token' => $token,
                    'type' => $call->type,
                    'message_id' => $callMessage?->id,
                    'caller' => [
                        'id' => $caller->id,
                        'name' => $caller->full_name,
                    ],
                    'receiver' => [
                        'id' => $receiver->id,
                        'name' => $receiver->full_name,
                    ],
                ],
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to initiate call: '.$e->getMessage(), [
                'caller_id' => $caller->id,
                'receiver_id' => $receiver->id,
                'type' => $request->call_type,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate call',
            ], 500);
        }
    }

    /**
     * Join an existing call
     */
    public function joinCall(int $callId): JsonResponse
    {
        $call = Call::with(['caller.profile', 'receiver.profile'])->findOrFail($callId);
        $user = Auth::user();

        // Check if the user is the receiver of the call
        if ($call->receiver_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to join this call',
            ], 403);
        }

        // Check if call is in initiated status
        if ($call->status !== 'initiated') {
            return response()->json([
                'status' => 'error',
                'message' => 'Call is not available to join. Current status: '.$call->status,
            ], 400);
        }

        try {
            // Update call status to ongoing
            $call = $this->videoSDKService->updateCallStatus($call, 'ongoing');

            // Update call message
            $callMessage = $this->callMessageService->createOrUpdateCallMessage($call, 'joined');

            // Generate token for the receiver
            $token = $this->videoSDKService->generateToken();

            // Broadcast call status changed event
            event(new CallStatusChangedEvent($call, $user->id));

            return response()->json([
                'status' => 'success',
                'data' => [
                    'call_id' => $call->id,
                    'meeting_id' => $call->channel_name,
                    'token' => $token,
                    'type' => $call->type,
                    'message_id' => $callMessage?->id,
                    'caller' => [
                        'id' => $call->caller->id,
                        'name' => $call->caller->full_name,
                    ],
                    'receiver' => [
                        'id' => $call->receiver->id,
                        'name' => $call->receiver->full_name,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to join call: '.$e->getMessage(), [
                'call_id' => $callId,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to join call',
            ], 500);
        }
    }

    /**
     * End an ongoing call
     */
    public function endCall(int $callId): JsonResponse
    {
        $call = Call::with(['caller.profile', 'receiver.profile'])->findOrFail($callId);
        $user = Auth::user();

        // Check if the user is part of the call
        if ($call->caller_id !== $user->id && $call->receiver_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to end this call',
            ], 403);
        }

        // Check if call can be ended
        if (! in_array($call->status, ['initiated', 'ongoing'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Call cannot be ended. Current status: '.$call->status,
            ], 400);
        }

        try {
            // Update call status to completed
            $call = $this->videoSDKService->updateCallStatus($call, 'completed');

            // Update call message
            $callMessage = $this->callMessageService->createOrUpdateCallMessage($call, 'ended');

            // Broadcast call status changed event
            event(new CallStatusChangedEvent($call, $user->id));

            $duration = $call->getDurationInSeconds();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'message' => 'Call ended successfully',
                    'call_id' => $call->id,
                    'duration' => $duration,
                    'formatted_duration' => $call->getFormattedDuration(),
                    'message_id' => $callMessage?->id,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to end call: '.$e->getMessage(), [
                'call_id' => $callId,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to end call',
            ], 500);
        }
    }

    /**
     * Reject an incoming call
     */
    public function rejectCall(int $callId): JsonResponse
    {
        $call = Call::with(['caller.profile', 'receiver.profile'])->findOrFail($callId);
        $user = Auth::user();

        // Check if the user is the receiver of the call
        if ($call->receiver_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to reject this call',
            ], 403);
        }

        // Check if call can be rejected
        if ($call->status !== 'initiated') {
            return response()->json([
                'status' => 'error',
                'message' => 'Call cannot be rejected. Current status: '.$call->status,
            ], 400);
        }

        try {
            // Update call status to rejected
            $call = $this->videoSDKService->updateCallStatus($call, 'rejected');

            // Update call message
            $callMessage = $this->callMessageService->createOrUpdateCallMessage($call, 'rejected');

            // Broadcast call status changed event
            event(new CallStatusChangedEvent($call, $user->id));

            return response()->json([
                'status' => 'success',
                'data' => [
                    'message' => 'Call rejected successfully',
                    'call_id' => $call->id,
                    'message_id' => $callMessage?->id,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to reject call: '.$e->getMessage(), [
                'call_id' => $callId,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject call',
            ], 500);
        }
    }

    /**
     * Handle missed call (called by system when call times out)
     */
    public function handleMissedCall(int $callId): JsonResponse
    {
        $call = Call::with(['caller.profile', 'receiver.profile'])->findOrFail($callId);

        // Check if call can be marked as missed
        if ($call->status !== 'initiated') {
            return response()->json([
                'status' => 'error',
                'message' => 'Call cannot be marked as missed. Current status: '.$call->status,
            ], 400);
        }

        try {
            // Handle missed call
            $this->callMessageService->handleMissedCall($call);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'message' => 'Call marked as missed',
                    'call_id' => $call->id,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to handle missed call: '.$e->getMessage(), [
                'call_id' => $callId,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to handle missed call',
            ], 500);
        }
    }

    /**
     * Get call history with integrated messages
     */
    public function getCallHistory(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:50',
            'call_status' => 'sometimes|string|in:completed,rejected,missed,ongoing',
            'call_type' => 'sometimes|string|in:video,voice',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
        ]);

        try {
            $filters = [
                'call_status' => $request->call_status,
                'call_type' => $request->call_type,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'per_page' => $request->get('per_page', 20),
            ];

            $callHistory = $this->callMessageService->getCallHistory($user, $filters);

            return response()->json([
                'status' => 'success',
                'data' => $callHistory,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get call history: '.$e->getMessage(), [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get call history',
            ], 500);
        }
    }

    /**
     * Get call analytics
     */
    public function getCallAnalytics(): JsonResponse
    {
        $user = Auth::user();

        try {
            $analytics = $this->callMessageService->getCallAnalytics($user);

            return response()->json([
                'status' => 'success',
                'data' => $analytics,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get call analytics: '.$e->getMessage(), [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get call analytics',
            ], 500);
        }
    }
}
