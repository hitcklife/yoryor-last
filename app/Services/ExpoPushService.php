<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushService
{
    /**
     * Expo push notifications API URL
     */
    private const EXPO_API_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Send a push notification to a user's Expo devices
     */
    public function sendNotification(User $user, string $title, string $message, array $data = []): array
    {
        $deviceTokens = $user->deviceTokens()->get();

        if ($deviceTokens->isEmpty()) {
            Log::info('No device tokens found for user', ['user_id' => $user->id]);

            return [
                'success' => false,
                'message' => 'No device tokens found for user',
                'data' => [],
            ];
        }

        // Log device tokens to debug duplicates
        Log::info('Sending notification to user device tokens', [
            'user_id' => $user->id,
            'tokens_count' => $deviceTokens->count(),
            'tokens' => $deviceTokens->pluck('token')->toArray(),
            'title' => $title,
        ]);

        return $this->sendNotificationToTokens($deviceTokens, $title, $message, $data);
    }

    /**
     * Send a push notification to multiple users
     */
    public function sendNotificationToUsers(array $userIds, string $title, string $message, array $data = []): array
    {
        $deviceTokens = DeviceToken::whereIn('user_id', $userIds)->get();

        if ($deviceTokens->isEmpty()) {
            Log::info('No device tokens found for users', ['user_ids' => $userIds]);

            return [
                'success' => false,
                'message' => 'No device tokens found for users',
                'data' => [],
            ];
        }

        return $this->sendNotificationToTokens($deviceTokens, $title, $message, $data);
    }

    /**
     * Send a push notification to specific device tokens
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $deviceTokens
     */
    public function sendNotificationToTokens($deviceTokens, string $title, string $message, array $data = []): array
    {
        $messages = [];
        $validTokens = [];

        foreach ($deviceTokens as $deviceToken) {
            // Skip non-Expo tokens or invalid tokens
            if (! $this->isValidExpoToken($deviceToken->token)) {
                continue;
            }

            $validTokens[] = $deviceToken->token;

            $messagePayload = [
                'to' => $deviceToken->token,
                'title' => $title,
                'body' => $message,
                'data' => $data,
                'sound' => 'default',
                'badge' => 1,
                'channelId' => 'default',
                '_displayInForeground' => true,
            ];

            // Add image to notification if imageUrl is provided in data
            if (isset($data['imageUrl']) && ! empty($data['imageUrl'])) {
                $messagePayload['image'] = $data['imageUrl'];
            }

            // Add richContent for avatar image in notifications (primarily for message notifications)
            if (isset($data['sender_photo']) && ! empty($data['sender_photo'])) {
                $messagePayload['richContent'] = [
                    'image' => $data['sender_photo'],
                ];
            }

            $messages[] = $messagePayload;
        }

        if (empty($messages)) {
            Log::info('No valid Expo tokens found', ['tokens_count' => $deviceTokens->count()]);

            return [
                'success' => false,
                'message' => 'No valid Expo tokens found',
                'data' => [],
            ];
        }

        try {
            $response = Http::post(self::EXPO_API_URL, $messages);

            Log::info('Expo push notification sent', [
                'tokens_count' => count($validTokens),
                'response' => $response->json(),
            ]);

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Notifications sent successfully' : 'Failed to send notifications',
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send Expo push notification', [
                'error' => $e->getMessage(),
                'tokens_count' => count($validTokens),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Check if a token is a valid Expo push token
     */
    private function isValidExpoToken(string $token): bool
    {
        // Expo push tokens start with ExponentPushToken[ or ExpoPushToken[
        return (str_starts_with($token, 'ExponentPushToken[') ||
                str_starts_with($token, 'ExpoPushToken[')) &&
               str_ends_with($token, ']');
    }
}
