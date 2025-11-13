<?php

namespace App\Jobs;

use App\Models\PanicActivation;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ExpoPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmergencyNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $panicActivation;
    protected $emergencyContacts;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(PanicActivation $panicActivation, array $emergencyContacts)
    {
        $this->panicActivation = $panicActivation;
        $this->emergencyContacts = $emergencyContacts;
        
        // Set high priority for emergency notifications
        $this->onQueue('emergency');
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService, ExpoPushService $expoPushService): void
    {
        try {
            $user = $this->panicActivation->user;
            
            Log::info('Processing emergency notification', [
                'panic_id' => $this->panicActivation->id,
                'user_id' => $user->id,
                'trigger_type' => $this->panicActivation->trigger_type,
                'contacts_count' => count($this->emergencyContacts)
            ]);

            foreach ($this->emergencyContacts as $contact) {
                $this->sendEmergencyNotification($contact, $user, $notificationService, $expoPushService);
            }

            // Update panic activation to mark notifications as sent
            $this->panicActivation->update([
                'notifications_sent_at' => now(),
                'status' => 'notifications_sent'
            ]);

            Log::info('Emergency notifications sent successfully', [
                'panic_id' => $this->panicActivation->id,
                'contacts_notified' => count($this->emergencyContacts)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send emergency notifications', [
                'panic_id' => $this->panicActivation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Mark panic as failed
            $this->panicActivation->update([
                'status' => 'notification_failed',
                'error_message' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Send notification to individual emergency contact
     */
    private function sendEmergencyNotification(
        $contact, 
        User $user, 
        NotificationService $notificationService,
        ExpoPushService $expoPushService
    ): void {
        try {
            $message = $this->buildEmergencyMessage($user, $contact);
            
            // Send SMS if phone number is available
            if ($contact->phone && $contact->receives_sms) {
                $notificationService->sendSMS($contact->phone, $message['sms']);
            }

            // Send email if email is available
            if ($contact->email && $contact->receives_email) {
                $notificationService->sendEmail(
                    $contact->email, 
                    'Emergency Alert - ' . $user->full_name,
                    $message['email']
                );
            }

            // Send push notification if they have the app
            if ($contact->user_id) {
                $contactUser = User::find($contact->user_id);
                if ($contactUser) {
                    $expoPushService->sendPushNotification($contactUser, [
                        'title' => 'Emergency Alert',
                        'body' => $message['push'],
                        'data' => [
                            'type' => 'emergency_alert',
                            'panic_id' => $this->panicActivation->id,
                            'user_id' => $user->id,
                            'trigger_type' => $this->panicActivation->trigger_type,
                            'location' => $this->panicActivation->location_data
                        ]
                    ]);
                }
            }

            Log::info('Emergency notification sent to contact', [
                'contact_id' => $contact->id,
                'contact_name' => $contact->name,
                'methods' => [
                    'sms' => $contact->phone && $contact->receives_sms,
                    'email' => $contact->email && $contact->receives_email,
                    'push' => (bool)$contact->user_id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send notification to emergency contact', [
                'contact_id' => $contact->id,
                'contact_name' => $contact->name,
                'error' => $e->getMessage()
            ]);
            
            // Don't throw here to continue with other contacts
        }
    }

    /**
     * Build emergency message content
     */
    private function buildEmergencyMessage(User $user, $contact): array
    {
        $urgencyLevel = $this->getUrgencyLevel();
        $locationText = $this->getLocationText();
        
        $messages = [
            'sms' => "🚨 EMERGENCY ALERT: {$user->full_name} has activated their panic button. {$urgencyLevel} {$locationText} Please check on them immediately or contact local authorities. Time: " . now()->format('Y-m-d H:i:s'),
            
            'email' => "Emergency Alert - Immediate Action Required\n\n" .
                      "Dear {$contact->name},\n\n" .
                      "{$user->full_name} has activated their emergency panic button.\n\n" .
                      "Details:\n" .
                      "- Trigger Type: {$this->panicActivation->trigger_type}\n" .
                      "- Time: " . $this->panicActivation->triggered_at->format('Y-m-d H:i:s') . "\n" .
                      "- Urgency: {$urgencyLevel}\n" .
                      "{$locationText}\n\n" .
                      "Please take immediate action:\n" .
                      "1. Try to contact {$user->full_name} immediately\n" .
                      "2. If you cannot reach them, consider contacting local authorities\n" .
                      "3. Go to their location if safe to do so\n\n" .
                      "This is an automated emergency alert from YorYor Dating App.",
            
            'push' => "🚨 {$user->full_name} needs help! Emergency activated. {$locationText}"
        ];

        return $messages;
    }

    /**
     * Get urgency level based on trigger type
     */
    private function getUrgencyLevel(): string
    {
        return match($this->panicActivation->trigger_type) {
            'silent_alarm' => '🔴 CRITICAL - Silent alarm activated',
            'safe_word' => '🔴 CRITICAL - Safe word used', 
            'emergency_contact' => '🟡 HIGH - Emergency contact requested',
            'date_check_in' => '🟡 MEDIUM - Failed to check in from date',
            'location_sharing' => '🟡 MEDIUM - Location sharing activated',
            'fake_call' => '🟢 LOW - Fake call assistance requested',
            default => '🟡 ALERT'
        };
    }

    /**
     * Get location information
     */
    private function getLocationText(): string
    {
        if (!$this->panicActivation->location_data) {
            return "Location: Not available";
        }

        $location = $this->panicActivation->location_data;
        $text = "";
        
        if (isset($location['latitude']) && isset($location['longitude'])) {
            $text .= "Location: {$location['latitude']}, {$location['longitude']}";
            
            if (isset($location['address'])) {
                $text .= " ({$location['address']})";
            }
            
            $text .= " - Google Maps: https://maps.google.com/?q={$location['latitude']},{$location['longitude']}";
        }

        return $text;
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Emergency notification job failed permanently', [
            'panic_id' => $this->panicActivation->id,
            'attempts' => $this->attempts,
            'error' => $exception->getMessage()
        ]);

        // Update panic activation to mark as failed
        $this->panicActivation->update([
            'status' => 'notification_failed',
            'error_message' => $exception->getMessage(),
            'failed_at' => now()
        ]);

        // TODO: Send admin alert about failed emergency notification
        // This is critical - emergency services may need to be contacted manually
    }
}