<?php

namespace App\Services;

use App\Models\Call;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;

class VideoSDKService
{
    /**
     * Generate a JWT token for Video SDK
     */
    public function generateToken(int $expireTimeInSeconds = 86400): string
    {
        $apiKey = config('services.videosdk.api_key');
        $secretKey = config('services.videosdk.secret_key');

        if (empty($apiKey) || empty($secretKey)) {
            throw new Exception('Video SDK credentials not configured');
        }

        $currentTimestamp = time();
        $expireTime = $currentTimestamp + $expireTimeInSeconds;

        $payload = [
            'apikey' => $apiKey,
            'permissions' => ['allow_join', 'allow_mod', 'ask_join'],
            'version' => 2,
            'exp' => $expireTime
        ];

        return JWT::encode($payload, $secretKey, 'HS256');
    }

    /**
     * Create a new meeting
     */
    public function createMeeting(?string $customRoomId = null): array
    {
        $token = $this->generateToken();
        $apiEndpoint = config('services.videosdk.api_endpoint');

        if (empty($apiEndpoint)) {
            throw new Exception('Video SDK API endpoint not configured');
        }

        $payload = [];
        if ($customRoomId) {
            $payload['customRoomId'] = $customRoomId;
            $payload['disabled'] = false;
        }

        $response = Http::withHeaders([
            'Authorization' => $token,
            'Content-Type' => 'application/json',
        ])->post($apiEndpoint . '/rooms', $payload);

        if ($response->failed()) {
            throw new Exception('Failed to create meeting: ' . $response->body());
        }

        $data = $response->json();

        return [
            'meetingId' => $data['roomId'],
            'token' => $token
        ];
    }

    /**
     * Validate a meeting
     */
    public function validateMeeting(string $meetingId): array
    {
        $token = $this->generateToken();
        $apiEndpoint = config('services.videosdk.api_endpoint');

        if (empty($apiEndpoint)) {
            throw new Exception('Video SDK API endpoint not configured');
        }

        $response = Http::withHeaders([
            'Authorization' => $token,
            'Content-Type' => 'application/json',
        ])->get($apiEndpoint . '/rooms/validate/' . $meetingId);

        if ($response->failed()) {
            throw new Exception('Failed to validate meeting: ' . $response->body());
        }

        $data = $response->json();

        return [
            'valid' => $data['roomId'] === $meetingId,
            'meetingId' => $data['roomId']
        ];
    }

    /**
     * Create a new call with Video SDK
     */
    public function createCall(User $caller, User $receiver, string $type = 'video'): array
    {
        $meetingData = $this->createMeeting();
        $meetingId = $meetingData['meetingId'];
        $token = $meetingData['token'];

        $call = Call::create([
            'channel_name' => $meetingId,
            'caller_id' => $caller->id,
            'receiver_id' => $receiver->id,
            'type' => $type,
            'status' => 'initiated',
        ]);

        return [
            'call' => $call,
            'token' => $token
        ];
    }

    /**
     * Update call status
     */
    public function updateCallStatus(Call $call, string $status): Call
    {
        if ($status === 'ongoing' && is_null($call->started_at)) {
            $call->started_at = now();
        }

        if (in_array($status, ['completed', 'missed', 'rejected']) && is_null($call->ended_at)) {
            $call->ended_at = now();
        }

        $call->status = $status;
        $call->save();

        return $call;
    }
}
