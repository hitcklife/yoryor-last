<?php

namespace App\Services;

use App\Models\User;
use App\Models\DeviceToken;
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
     *
     * @param User $user
     * @param string $title
     * @param string $message
     * @param array $data
     * @return array
     */
    public function sendNotification(User $user, string $title, string $message, array $data = []): array
    {
        $deviceTokens = $user->deviceTokens()->get();

        if ($deviceTokens->isEmpty()) {
            Log::info('No device tokens found for user', ['user_id' => $user->id]);
            return [
                'success' => false,
                'message' => 'No device tokens found for user',
                'data' => []
            ];
        }

        return $this->sendNotificationToTokens($deviceTokens, $title, $message, $data);
    }

    /**
     * Send a push notification to multiple users
     *
     * @param array $userIds
     * @param string $title
     * @param string $message
     * @param array $data
     * @return array
     */
    public function sendNotificationToUsers(array $userIds, string $title, string $message, array $data = []): array
    {
        $deviceTokens = DeviceToken::whereIn('user_id', $userIds)->get();

        if ($deviceTokens->isEmpty()) {
            Log::info('No device tokens found for users', ['user_ids' => $userIds]);
            return [
                'success' => false,
                'message' => 'No device tokens found for users',
                'data' => []
            ];
        }

        return $this->sendNotificationToTokens($deviceTokens, $title, $message, $data);
    }

    /**
     * Send a push notification to specific device tokens
     *
     * @param \Illuminate\Database\Eloquent\Collection $deviceTokens
     * @param string $title
     * @param string $message
     * @param array $data
     * @return array
     */
    public function sendNotificationToTokens($deviceTokens, string $title, string $message, array $data = []): array
    {
        $messages = [];
        $validTokens = [];

        foreach ($deviceTokens as $deviceToken) {
            // Skip non-Expo tokens or invalid tokens
            if (!$this->isValidExpoToken($deviceToken->token)) {
                continue;
            }

            $validTokens[] = $deviceToken->token;

            $messages[] = [
                'to' => $deviceToken->token,
                'title' => $title,
                'body' => $message,
                'data' => $data,
                'sound' => 'default',
                'badge' => 1,
                'channelId' => 'default',
                '_displayInForeground' => true,
            ];
        }

        if (empty($messages)) {
            Log::info('No valid Expo tokens found', ['tokens_count' => $deviceTokens->count()]);
            return [
                'success' => false,
                'message' => 'No valid Expo tokens found',
                'data' => []
            ];
        }

        try {
            $response = Http::post(self::EXPO_API_URL, $messages);

            Log::info('Expo push notification sent', [
                'tokens_count' => count($validTokens),
                'response' => $response->json()
            ]);

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'Notifications sent successfully' : 'Failed to send notifications',
                'data' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send Expo push notification', [
                'error' => $e->getMessage(),
                'tokens_count' => count($validTokens)
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Check if a token is a valid Expo push token
     *
     * @param string $token
     * @return bool
     */
    private function isValidExpoToken(string $token): bool
    {
        // Expo push tokens start with ExponentPushToken[ or ExpoPushToken[
        return (str_starts_with($token, 'ExponentPushToken[') ||
                str_starts_with($token, 'ExpoPushToken[')) &&
               str_ends_with($token, ']');
    }
}
