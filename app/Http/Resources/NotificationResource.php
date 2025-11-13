<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'notifications',
            'id' => (string) $this->id,
            'attributes' => [
                'type' => $this->type,
                'notifiable_type' => $this->notifiable_type,
                'notifiable_id' => $this->notifiable_id,
                'data' => $this->formatNotificationData($request),
                'read_at' => $this->read_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                // Computed attributes
                'is_read' => $this->read_at !== null,
                'is_unread' => $this->read_at === null,
                'time_ago' => $this->created_at->diffForHumans(),
                // Notification metadata
                'title' => $this->getNotificationTitle(),
                'message' => $this->getNotificationMessage(),
                'action_url' => $this->getActionUrl(),
                'icon' => $this->getNotificationIcon(),
                'priority' => $this->getNotificationPriority(),
            ],
            'relationships' => [
                'notifiable' => [
                    'data' => [
                        'type' => $this->getNotifiableType(),
                        'id' => (string) $this->notifiable_id,
                    ],
                ],
            ],
            'included' => $this->when(
                $this->relationLoaded('notifiable'),
                function () {
                    $included = [];

                    // Include minimal notifiable data (usually User)
                    if ($this->notifiable) {
                        $included[] = [
                            'type' => $this->getNotifiableType(),
                            'id' => (string) $this->notifiable->id,
                            'attributes' => $this->getNotifiableAttributes(),
                        ];
                    }

                    return array_filter($included);
                }
            ),
        ];
    }

    /**
     * Format notification data with privacy controls
     */
    protected function formatNotificationData(Request $request): array
    {
        $data = $this->data ?? [];
        $currentUser = $request->user();

        // Only show notification data to the owner
        if (!$currentUser || $currentUser->id !== $this->notifiable_id) {
            return [
                'message' => 'Access denied',
            ];
        }

        return $data;
    }

    /**
     * Get notification title based on type
     */
    protected function getNotificationTitle(): string
    {
        $data = $this->data ?? [];

        return match ($this->type) {
            'App\\Notifications\\NewMessageNotification' => 'New Message',
            'App\\Notifications\\NewMatchNotification' => 'New Match',
            'App\\Notifications\\MatchRequestNotification' => 'Match Request',
            'App\\Notifications\\ProfileViewedNotification' => 'Profile Viewed',
            'App\\Notifications\\LikeReceivedNotification' => 'Someone Liked You',
            'App\\Notifications\\VerificationApprovedNotification' => 'Verification Approved',
            'App\\Notifications\\VerificationRejectedNotification' => 'Verification Rejected',
            'App\\Notifications\\SubscriptionExpiringNotification' => 'Subscription Expiring',
            'App\\Notifications\\PanicButtonActivatedNotification' => 'Emergency Alert',
            default => $data['title'] ?? 'Notification'
        };
    }

    /**
     * Get notification message based on type
     */
    protected function getNotificationMessage(): string
    {
        $data = $this->data ?? [];

        return $data['message'] ?? $data['body'] ?? 'You have a new notification';
    }

    /**
     * Get action URL for notification
     */
    protected function getActionUrl(): ?string
    {
        $data = $this->data ?? [];

        return $data['action_url'] ?? $data['url'] ?? null;
    }

    /**
     * Get notification icon based on type
     */
    protected function getNotificationIcon(): string
    {
        return match ($this->type) {
            'App\\Notifications\\NewMessageNotification' => 'message',
            'App\\Notifications\\NewMatchNotification' => 'heart',
            'App\\Notifications\\MatchRequestNotification' => 'user-plus',
            'App\\Notifications\\ProfileViewedNotification' => 'eye',
            'App\\Notifications\\LikeReceivedNotification' => 'heart',
            'App\\Notifications\\VerificationApprovedNotification' => 'check-circle',
            'App\\Notifications\\VerificationRejectedNotification' => 'x-circle',
            'App\\Notifications\\SubscriptionExpiringNotification' => 'alert-triangle',
            'App\\Notifications\\PanicButtonActivatedNotification' => 'alert-octagon',
            default => 'bell'
        };
    }

    /**
     * Get notification priority
     */
    protected function getNotificationPriority(): string
    {
        $data = $this->data ?? [];

        // Check if priority is explicitly set in data
        if (isset($data['priority'])) {
            return $data['priority'];
        }

        // Determine priority based on notification type
        return match ($this->type) {
            'App\\Notifications\\PanicButtonActivatedNotification' => 'critical',
            'App\\Notifications\\VerificationRejectedNotification',
            'App\\Notifications\\SubscriptionExpiringNotification' => 'high',
            'App\\Notifications\\NewMatchNotification',
            'App\\Notifications\\LikeReceivedNotification' => 'medium',
            default => 'normal'
        };
    }

    /**
     * Get notifiable type for relationships
     */
    protected function getNotifiableType(): string
    {
        return match ($this->notifiable_type) {
            'App\\Models\\User' => 'users',
            default => strtolower(class_basename($this->notifiable_type))
        };
    }

    /**
     * Get notifiable attributes based on type
     */
    protected function getNotifiableAttributes(): array
    {
        if ($this->notifiable instanceof \App\Models\User) {
            return [
                'email' => $this->notifiable->email,
                'full_name' => $this->notifiable->relationLoaded('profile') && $this->notifiable->profile
                    ? trim($this->notifiable->profile->first_name . ' ' . $this->notifiable->profile->last_name) ?: null
                    : null,
            ];
        }

        return [];
    }
}
