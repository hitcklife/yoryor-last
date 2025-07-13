<?php

namespace App\Listeners;

use App\Events\NewMatchEvent;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use YieldStudio\LaravelExpoNotifier\Dto\ExpoMessage;
use YieldStudio\LaravelExpoNotifier\ExpoNotificationsChannel;

class SendMatchNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(NewMatchEvent $event): void
    {
        try {
            $match = $event->match;
            $initiator = $event->initiator;
            $receiver = $event->receiver;

            // Send notification to both users
            $this->sendNotificationToUser($receiver, $initiator, 'It\'s a match! 🎉');
            $this->sendNotificationToUser($initiator, $receiver, 'It\'s a match! 🎉');

        } catch (\Exception $e) {
            Log::error('Failed to send match notification', [
                'error' => $e->getMessage(),
                'match_id' => $event->match->id,
                'initiator_id' => $event->initiator->id,
                'receiver_id' => $event->receiver->id
            ]);
        }
    }

    /**
     * Send notification to a specific user
     */
    private function sendNotificationToUser($user, $matchedUser, $title): void
    {
        try {
            // Check if user has notification settings enabled
            $settings = $user->settings;
            if ($settings && !$settings->notify_matches) {
                Log::info('Match notification disabled for user', ['user_id' => $user->id]);
                return;
            }

            // Check if push notifications are enabled
            if ($settings && !$settings->push_notifications_enabled) {
                Log::info('Push notifications disabled for user', ['user_id' => $user->id]);
                return;
            }

            // Check quiet hours
            if ($this->isQuietHours($user)) {
                Log::info('In quiet hours, skipping notification', ['user_id' => $user->id]);
                return;
            }

            // Send notification using existing NotificationService
            $this->notificationService->sendNotification(
                $user,
                'new_match',
                $title,
                "You and {$matchedUser->full_name} liked each other! Start chatting now.",
                [
                    'matched_user_id' => $matchedUser->id,
                    'matched_user_name' => $matchedUser->full_name,
                    'matched_user_photo' => $matchedUser->getProfilePhotoUrl('thumbnail'),
                    'action' => 'view_matches'
                ],
                true // Enable push notifications
            );

            // Also send using laravel-expo-notifier package directly
            $this->sendExpoNotification($user, $matchedUser, $title);

        } catch (\Exception $e) {
            Log::error('Failed to send match notification to user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'matched_user_id' => $matchedUser->id
            ]);
        }
    }

    /**
     * Send notification using laravel-expo-notifier package
     */
    private function sendExpoNotification($user, $matchedUser, $title): void
    {
        try {
            // Get device tokens for the user
            $deviceTokens = $user->deviceTokens;
            
            if ($deviceTokens->isEmpty()) {
                Log::info('No device tokens found for user', ['user_id' => $user->id]);
                return;
            }

            // Get matched user's profile photo URL
            $matchedUserPhotoUrl = $matchedUser->getProfilePhotoUrl('thumbnail');
            
            // Create expo message with user profile image
            $expoMessage = (new ExpoMessage())
                ->to($deviceTokens->pluck('token')->toArray())
                ->title($title)
                ->body("You and {$matchedUser->full_name} liked each other! Start chatting now.")
                ->channelId('matches')
                ->badge(1)
                ->sound('default')
                ->data([
                    'type' => 'new_match',
                    'matched_user_id' => $matchedUser->id,
                    'matched_user_name' => $matchedUser->full_name,
                    'matched_user_photo' => $matchedUserPhotoUrl,
                    'action' => 'view_matches'
                ])
                ->priority('high');

            // Add profile image to notification if available
            if ($matchedUserPhotoUrl) {
                $expoMessage->image($matchedUserPhotoUrl);
            }

            // Send the notification
            $channel = new ExpoNotificationsChannel();
            $channel->send($user, $expoMessage);

            Log::info('Expo match notification sent', [
                'user_id' => $user->id,
                'matched_user_id' => $matchedUser->id,
                'tokens_count' => $deviceTokens->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send expo match notification', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'matched_user_id' => $matchedUser->id
            ]);
        }
    }

    /**
     * Check if current time is within user's quiet hours
     */
    private function isQuietHours($user): bool
    {
        $settings = $user->settings;
        
        if (!$settings || !$settings->quiet_hours_start || !$settings->quiet_hours_end) {
            return false;
        }

        $currentTime = now()->format('H:i');
        $startTime = $settings->quiet_hours_start;
        $endTime = $settings->quiet_hours_end;

        // Handle same day quiet hours
        if ($startTime <= $endTime) {
            return $currentTime >= $startTime && $currentTime <= $endTime;
        }

        // Handle overnight quiet hours (e.g., 22:00 to 06:00)
        return $currentTime >= $startTime || $currentTime <= $endTime;
    }
} 