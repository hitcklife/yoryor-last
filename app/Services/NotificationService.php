<?php

namespace App\Services;

use App\Events\GeneralNotificationEvent;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class NotificationService
{
    /**
     * Send a general notification to a user.
     *
     * @param User $user
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $data
     * @param bool $sendPushNotification
     * @return void
     */
    public function sendNotification(User $user, string $type, string $title, string $message, array $data = [], bool $sendPushNotification = true): void
    {
        try {
            // Log the notification
            Log::info('Sending notification to user', [
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data
            ]);

            // Broadcast the notification via WebSockets
            broadcast(new GeneralNotificationEvent($user, $type, $title, $message, $data))->toOthers();

            // Send push notification if enabled
            if ($sendPushNotification) {
                $this->sendPushNotification($user, $title, $message, array_merge(['type' => $type], $data));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send a push notification to a user's mobile devices.
     *
     * @param User $user
     * @param string $title
     * @param string $message
     * @param array $data
     * @return void
     */
    protected function sendPushNotification(User $user, string $title, string $message, array $data = []): void
    {
        try {
            // Get the ExpoPushService from the container
            $pushService = App::make(ExpoPushService::class);

            // Send the push notification
            $result = $pushService->sendNotification($user, $title, $message, $data);

            // Log the result
            if ($result['success']) {
                Log::info('Push notification sent successfully', [
                    'user_id' => $user->id,
                    'title' => $title,
                    'result' => $result
                ]);
            } else {
                Log::warning('Failed to send push notification', [
                    'user_id' => $user->id,
                    'title' => $title,
                    'result' => $result
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception while sending push notification', [
                'user_id' => $user->id,
                'title' => $title,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send a welcome notification to a new user.
     *
     * @param User $user
     * @return void
     */
    public function sendWelcomeNotification(User $user): void
    {
        $this->sendNotification(
            $user,
            'welcome',
            'Welcome to YorYor!',
            'Welcome to YorYor! Complete your profile to start meeting new people.',
            ['action' => 'complete_profile']
        );
    }

    /**
     * Send a profile completion reminder notification.
     *
     * @param User $user
     * @return void
     */
    public function sendProfileCompletionReminder(User $user): void
    {
        $this->sendNotification(
            $user,
            'profile_incomplete',
            'Complete Your Profile',
            'Complete your profile to increase your chances of getting matches!',
            ['action' => 'complete_profile']
        );
    }

    /**
     * Send a match recommendation notification.
     *
     * @param User $user
     * @param int $count
     * @return void
     */
    public function sendMatchRecommendation(User $user, int $count): void
    {
        $this->sendNotification(
            $user,
            'match_recommendation',
            'New People to Meet!',
            "We found {$count} new people who might be perfect for you!",
            ['action' => 'view_matches', 'count' => $count]
        );
    }

    /**
     * Send a verification reminder notification.
     *
     * @param User $user
     * @return void
     */
    public function sendVerificationReminder(User $user): void
    {
        $this->sendNotification(
            $user,
            'verification_reminder',
            'Verify Your Account',
            'Verify your account to unlock all features and increase your visibility.',
            ['action' => 'verify_account']
        );
    }

    /**
     * Send a daily activity summary notification.
     *
     * @param User $user
     * @param array $stats
     * @return void
     */
    public function sendDailySummary(User $user, array $stats): void
    {
        $likesCount = $stats['likes_received'] ?? 0;
        $viewsCount = $stats['profile_views'] ?? 0;

        $message = "Today you received {$likesCount} likes and {$viewsCount} profile views!";

        $this->sendNotification(
            $user,
            'daily_summary',
            'Your Daily Summary',
            $message,
            ['action' => 'view_activity', 'stats' => $stats]
        );
    }

    /**
     * Send a system maintenance notification.
     *
     * @param User $user
     * @param string $message
     * @param \DateTime $scheduledTime
     * @return void
     */
    public function sendMaintenanceNotification(User $user, string $message, \DateTime $scheduledTime): void
    {
        $this->sendNotification(
            $user,
            'maintenance',
            'Scheduled Maintenance',
            $message,
            ['scheduled_time' => $scheduledTime->toIso8601String()]
        );
    }

    /**
     * Send a bulk notification to multiple users.
     *
     * @param array $userIds
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $data
     * @param bool $sendPushNotification
     * @return void
     */
    public function sendBulkNotification(array $userIds, string $type, string $title, string $message, array $data = [], bool $sendPushNotification = true): void
    {
        $users = User::whereIn('id', $userIds)->get();

        foreach ($users as $user) {
            $this->sendNotification($user, $type, $title, $message, $data, $sendPushNotification);
        }

        // If push notifications are enabled, also send them in bulk for efficiency
        if ($sendPushNotification && count($userIds) > 10) {
            try {
                $pushService = App::make(ExpoPushService::class);
                $result = $pushService->sendNotificationToUsers($userIds, $title, $message, array_merge(['type' => $type], $data));

                Log::info('Bulk push notification result', [
                    'user_count' => count($userIds),
                    'result' => $result
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send bulk push notifications', [
                    'user_count' => count($userIds),
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send a notification to all active users.
     *
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $data
     * @param bool $sendPushNotification
     * @return void
     */
    public function sendNotificationToAllUsers(string $type, string $title, string $message, array $data = [], bool $sendPushNotification = true): void
    {
        User::active()->chunk(100, function ($users) use ($type, $title, $message, $data, $sendPushNotification) {
            $userIds = $users->pluck('id')->toArray();
            $this->sendBulkNotification($userIds, $type, $title, $message, $data, $sendPushNotification);
        });
    }
}
