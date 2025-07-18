<?php

namespace App\Listeners;

use App\Events\NewMessageEvent;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use YieldStudio\LaravelExpoNotifier\Dto\ExpoMessage;
use YieldStudio\LaravelExpoNotifier\ExpoNotificationsChannel;

class SendMessageNotification implements ShouldQueue
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
    public function handle(NewMessageEvent $event): void
    {
        try {
            $message = $event->message;
            $sender = $message->sender;
            $chat = $message->chat;

            // Get all users in the chat except the sender
            $recipients = $chat->users()->where('user_id', '!=', $sender->id)->get();

            foreach ($recipients as $recipient) {
                $this->sendNotificationToUser($recipient, $sender, $message);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send message notification', [
                'error' => $e->getMessage(),
                'message_id' => $event->message->id,
                'sender_id' => $event->message->sender_id,
                'chat_id' => $event->message->chat_id
            ]);
        }
    }

    /**
     * Send notification to a specific user
     */
    private function sendNotificationToUser($user, $sender, $message): void
    {
        try {
            // Check if user has notification settings enabled
            $settings = $user->settings;
            if ($settings && !$settings->notify_messages) {
                Log::info('Message notification disabled for user', ['user_id' => $user->id]);
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

            // Prepare message content for notification
            $notificationBody = $this->getMessagePreview($message);

            // Send notification using existing NotificationService
            $this->notificationService->sendNotification(
                $user,
                'new_message',
                "New message from {$sender->full_name}",
                $notificationBody,
                [
                    'sender_id' => $sender->id,
                    'sender_name' => $sender->full_name,
                    'sender_photo' => $sender->getProfilePhotoUrl('thumbnail'),
                    'chat_id' => $message->chat_id,
                    'message_id' => $message->id,
                    'message_type' => $message->message_type,
                    'action' => 'view_chat'
                ],
                true // Enable push notifications
            );

            // Also send using laravel-expo-notifier package directly
            $this->sendExpoNotification($user, $sender, $message, $notificationBody);

        } catch (\Exception $e) {
            Log::error('Failed to send message notification to user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'sender_id' => $sender->id,
                'message_id' => $message->id
            ]);
        }
    }

    /**
     * Send notification using laravel-expo-notifier package
     */
    private function sendExpoNotification($user, $sender, $message, $notificationBody): void
    {
        try {
            // Get device tokens for the user
            $deviceTokens = $user->deviceTokens;
            
            if ($deviceTokens->isEmpty()) {
                Log::info('No device tokens found for user', ['user_id' => $user->id]);
                return;
            }

            // Get sender's profile photo URL
            $senderPhotoUrl = $sender->getProfilePhotoUrl('thumbnail');
            
            // Create expo message with user profile image
            $expoMessage = (new ExpoMessage())
                ->to($deviceTokens->pluck('token')->toArray())
                ->title("New message from {$sender->full_name}")
                ->body($notificationBody)
                ->channelId('messages')
                ->badge(1)
                ->sound('default')
                ->data([
                    'type' => 'new_message',
                    'sender_id' => $sender->id,
                    'sender_name' => $sender->full_name,
                    'sender_photo' => $senderPhotoUrl,
                    'chat_id' => $message->chat_id,
                    'message_id' => $message->id,
                    'message_type' => $message->message_type,
                    'action' => 'view_chat'
                ])
                ->priority('normal');

            // Add profile image to notification if available
            if ($senderPhotoUrl) {
                $expoMessage->image($senderPhotoUrl);
            }

            // Send the notification
            $channel = new ExpoNotificationsChannel();
            $channel->send($user, $expoMessage);

            Log::info('Expo message notification sent', [
                'user_id' => $user->id,
                'sender_id' => $sender->id,
                'message_id' => $message->id,
                'tokens_count' => $deviceTokens->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send expo message notification', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'sender_id' => $sender->id,
                'message_id' => $message->id
            ]);
        }
    }

    /**
     * Get a preview of the message content for notification
     */
    private function getMessagePreview($message): string
    {
        switch ($message->message_type) {
            case 'text':
                return $message->content ? (strlen($message->content) > 100 ? substr($message->content, 0, 100) . '...' : $message->content) : 'Sent a message';
            case 'image':
                return '📷 Sent a photo';
            case 'video':
                return '🎥 Sent a video';
            case 'voice':
                return '🎤 Sent a voice message';
            case 'audio':
                return '🎵 Sent an audio file';
            case 'file':
                return '📎 Sent a file';
            case 'location':
                return '📍 Sent a location';
            case 'call':
                $mediaData = $message->media_data ?? [];
                $callType = $mediaData['call_type'] ?? 'call';
                $callStatus = $mediaData['call_status'] ?? 'completed';
                
                if ($callStatus === 'missed') {
                    return "📞 Missed {$callType} call";
                } elseif ($callStatus === 'completed') {
                    return "📞 {$callType} call ended";
                } else {
                    return "📞 {$callType} call";
                }
            default:
                return 'Sent a message';
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