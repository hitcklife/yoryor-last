<?php

namespace App\Listeners;

use App\Events\NewLikeEvent;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use YieldStudio\LaravelExpoNotifier\Dto\ExpoMessage;
use YieldStudio\LaravelExpoNotifier\ExpoNotificationsChannel;

class SendLikeNotification implements ShouldQueue
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
    public function handle(NewLikeEvent $event): void
    {
        try {
            $likedUser = $event->likedUser;
            $liker = $event->liker;

            // Check if user has notification settings enabled
            $settings = $likedUser->settings;
            if ($settings && !$settings->notify_likes) {
                Log::info('Like notification disabled for user', ['user_id' => $likedUser->id]);
                return;
            }

            // Check if push notifications are enabled
            if ($settings && !$settings->push_notifications_enabled) {
                Log::info('Push notifications disabled for user', ['user_id' => $likedUser->id]);
                return;
            }

            // Check quiet hours
            if ($this->isQuietHours($likedUser)) {
                Log::info('In quiet hours, skipping notification', ['user_id' => $likedUser->id]);
                return;
            }

            // Send notification using existing NotificationService
            $this->notificationService->sendNotification(
                $likedUser,
                'new_like',
                'You got a new like! 💕',
                "{$liker->full_name} liked your profile!",
                [
                    'liker_id' => $liker->id,
                    'liker_name' => $liker->full_name,
                    'liker_photo' => $liker->getProfilePhotoUrl('thumbnail'),
                    'action' => 'view_likes'
                ],
                true // Enable push notifications
            );

            // Also send using laravel-expo-notifier package directly
            $this->sendExpoNotification($likedUser, $liker);

        } catch (\Exception $e) {
            Log::error('Failed to send like notification', [
                'error' => $e->getMessage(),
                'liked_user_id' => $event->likedUser->id,
                'liker_id' => $event->liker->id
            ]);
        }
    }

    /**
     * Send notification using laravel-expo-notifier package
     */
    private function sendExpoNotification($likedUser, $liker): void
    {
        try {
            // Get device tokens for the user
            $deviceTokens = $likedUser->deviceTokens;
            
            if ($deviceTokens->isEmpty()) {
                Log::info('No device tokens found for user', ['user_id' => $likedUser->id]);
                return;
            }

            // Get liker's profile photo URL
            $likerPhotoUrl = $liker->getProfilePhotoUrl('thumbnail');
            
            // Create expo message with user profile image
            $expoMessage = (new ExpoMessage())
                ->to($deviceTokens->pluck('token')->toArray())
                ->title('You got a new like! 💕')
                ->body("{$liker->full_name} liked your profile!")
                ->channelId('likes')
                ->badge(1)
                ->sound('default')
                ->data([
                    'type' => 'new_like',
                    'liker_id' => $liker->id,
                    'liker_name' => $liker->full_name,
                    'liker_photo' => $likerPhotoUrl,
                    'action' => 'view_likes'
                ])
                ->priority('high');

            // Add profile image to notification if available
            if ($likerPhotoUrl) {
                $expoMessage->image($likerPhotoUrl);
            }

            // Send the notification
            $channel = new ExpoNotificationsChannel();
            $channel->send($likedUser, $expoMessage);

            Log::info('Expo like notification sent', [
                'user_id' => $likedUser->id,
                'liker_id' => $liker->id,
                'tokens_count' => $deviceTokens->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send expo like notification', [
                'error' => $e->getMessage(),
                'user_id' => $likedUser->id,
                'liker_id' => $liker->id
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