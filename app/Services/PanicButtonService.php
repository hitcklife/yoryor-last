<?php

namespace App\Services;

use App\Models\User;
use App\Models\PanicActivation;
use App\Models\UserEmergencyContact;
use App\Models\UserSafetySettings;
use App\Models\PanicNotification;
use App\Jobs\SendEmergencyNotificationJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PanicButtonService
{
    private NotificationService $notificationService;
    private ExpoPushService $expoPushService;

    public function __construct(
        NotificationService $notificationService,
        ExpoPushService $expoPushService
    ) {
        $this->notificationService = $notificationService;
        $this->expoPushService = $expoPushService;
    }

    /**
     * Activate panic button
     */
    public function activatePanic(
        User $user,
        string $triggerType,
        ?array $location = null,
        ?string $locationAddress = null,
        ?array $deviceInfo = null,
        ?array $contextData = null,
        ?string $userMessage = null
    ): array {
        try {
            // Check if user already has an active panic
            $existingPanic = PanicActivation::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($existingPanic) {
                return [
                    'success' => false,
                    'error' => 'Panic button is already active',
                    'existing_panic_id' => $existingPanic->id,
                ];
            }

            $panic = DB::transaction(function () use (
                $user,
                $triggerType,
                $location,
                $locationAddress,
                $deviceInfo,
                $contextData,
                $userMessage
            ) {
                // Create panic activation
                $locationPoint = null;
                if ($location && isset($location['latitude'], $location['longitude'])) {
                    $locationPoint = DB::raw("ST_Point({$location['longitude']}, {$location['latitude']})");
                }

                $panic = PanicActivation::create([
                    'user_id' => $user->id,
                    'trigger_type' => $triggerType,
                    'location' => $locationPoint,
                    'location_address' => $locationAddress,
                    'location_accuracy' => $location['accuracy'] ?? null,
                    'device_info' => $deviceInfo,
                    'context_data' => $contextData,
                    'user_message' => $userMessage,
                    'triggered_at' => now(),
                ]);

                // Update user panic status
                $user->update([
                    'panic_button_active' => true,
                    'last_panic_activation' => now(),
                    'panic_activation_count' => $user->panic_activation_count + 1,
                ]);

                return $panic;
            });

            // Send emergency notifications
            $this->sendEmergencyNotifications($panic);

            // Notify admins immediately for high-severity panics
            if ($panic->severity_level === 'critical') {
                $this->notifyAdminsImmediate($panic);
            }

            return [
                'success' => true,
                'panic_id' => $panic->id,
                'message' => 'Emergency services have been notified',
                'emergency_contacts_notified' => $this->getNotifiedContactsCount($panic),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to activate panic button', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'trigger_type' => $triggerType,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to activate emergency system',
            ];
        }
    }

    /**
     * Cancel panic activation
     */
    public function cancelPanic(User $user, ?string $reason = null): array
    {
        try {
            $panic = PanicActivation::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$panic) {
                return [
                    'success' => false,
                    'error' => 'No active panic to cancel',
                ];
            }

            DB::transaction(function () use ($panic, $user, $reason) {
                // Mark panic as false alarm
                $panic->update([
                    'status' => 'false_alarm',
                    'resolved_at' => now(),
                    'resolved_by' => $user->id,
                    'resolution_notes' => $reason ?? 'Cancelled by user',
                ]);

                // Update user status
                $user->update([
                    'panic_button_active' => false,
                ]);
            });

            // Notify emergency contacts of cancellation
            $this->sendCancellationNotifications($panic, $reason);

            return [
                'success' => true,
                'message' => 'Emergency cancelled. Contacts have been notified.',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to cancel panic', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to cancel emergency',
            ];
        }
    }

    /**
     * Get panic status for user
     */
    public function getPanicStatus(User $user): array
    {
        $activePanic = PanicActivation::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        $safetySettings = $this->getUserSafetySettings($user);
        $emergencyContacts = $user->emergencyContacts()
            ->panicAlertRecipients()
            ->orderedByPriority()
            ->get();

        return [
            'panic_active' => $activePanic !== null,
            'active_panic' => $activePanic?->load('notifications'),
            'safety_settings' => $safetySettings,
            'emergency_contacts_count' => $emergencyContacts->count(),
            'verified_contacts_count' => $emergencyContacts->where('is_verified', true)->count(),
            'setup_complete' => $this->isSafetySetupComplete($user),
            'recent_activations' => $this->getRecentActivations($user),
        ];
    }

    /**
     * Setup safety features for user
     */
    public function setupSafetyFeatures(User $user, array $settings): array
    {
        try {
            $safetySettings = UserSafetySettings::updateOrCreate(
                ['user_id' => $user->id],
                $settings
            );

            $user->update([
                'safety_features_enabled' => true,
            ]);

            return [
                'success' => true,
                'settings' => $safetySettings,
                'message' => 'Safety features configured successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to setup safety features', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to configure safety features',
            ];
        }
    }

    /**
     * Add emergency contact
     */
    public function addEmergencyContact(User $user, array $contactData): array
    {
        try {
            // Check if phone already exists for this user
            $existingContact = UserEmergencyContact::where('user_id', $user->id)
                ->where('phone', $contactData['phone'])
                ->first();

            if ($existingContact) {
                return [
                    'success' => false,
                    'error' => 'Contact with this phone number already exists',
                ];
            }

            $contact = DB::transaction(function () use ($user, $contactData) {
                // If this is marked as primary, unset other primary contacts
                if ($contactData['is_primary'] ?? false) {
                    UserEmergencyContact::where('user_id', $user->id)
                        ->update(['is_primary' => false]);
                }

                $contact = UserEmergencyContact::create([
                    'user_id' => $user->id,
                    ...$contactData,
                ]);

                // Generate and send verification code
                $verificationCode = $contact->generateVerificationCode();
                $this->sendVerificationCode($contact, $verificationCode);

                return $contact;
            });

            return [
                'success' => true,
                'contact' => $contact,
                'message' => 'Emergency contact added. Verification code sent.',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to add emergency contact', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to add emergency contact',
            ];
        }
    }

    /**
     * Verify emergency contact
     */
    public function verifyEmergencyContact(UserEmergencyContact $contact, string $code): array
    {
        if ($contact->isVerificationCodeExpired()) {
            return [
                'success' => false,
                'error' => 'Verification code has expired',
            ];
        }

        if ($contact->verifyWithCode($code)) {
            return [
                'success' => true,
                'message' => 'Emergency contact verified successfully',
            ];
        }

        return [
            'success' => false,
            'error' => 'Invalid verification code',
        ];
    }

    /**
     * Test emergency system
     */
    public function testEmergencySystem(User $user): array
    {
        try {
            $emergencyContacts = $user->emergencyContacts()
                ->panicAlertRecipients()
                ->get();

            if ($emergencyContacts->isEmpty()) {
                return [
                    'success' => false,
                    'error' => 'No verified emergency contacts found',
                ];
            }

            $results = [];
            foreach ($emergencyContacts as $contact) {
                $testResult = $this->testContactReachability($contact);
                $results[] = [
                    'contact' => $contact->name,
                    'phone' => $contact->formatted_phone,
                    'reachability' => $testResult,
                ];
            }

            // Send test notifications
            $this->sendTestNotifications($user, $emergencyContacts);

            return [
                'success' => true,
                'message' => 'Test notifications sent to emergency contacts',
                'results' => $results,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to test emergency system', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to test emergency system',
            ];
        }
    }

    /**
     * Send emergency notifications
     */
    private function sendEmergencyNotifications(PanicActivation $panic): void
    {
        $user = $panic->user;
        $emergencyContacts = $user->emergencyContacts()
            ->panicAlertRecipients()
            ->orderedByPriority()
            ->get()
            ->toArray();

        // Dispatch emergency notification job immediately (high priority queue)
        SendEmergencyNotificationJob::dispatch($panic, $emergencyContacts)
            ->onQueue('emergency')
            ->delay(now()); // Send immediately

        // Also notify app admins for monitoring (synchronously for admin alerts)
        $this->notifyAdminsOfPanic($panic);
        
        Log::info('Emergency notification job dispatched', [
            'panic_id' => $panic->id,
            'user_id' => $user->id,
            'contacts_count' => count($emergencyContacts)
        ]);
    }

    /**
     * Send emergency notification to specific contact
     */
    private function sendEmergencyNotificationToContact(
        PanicActivation $panic,
        UserEmergencyContact $contact,
        array $context
    ): void {
        $preferredMethod = $contact->getPreferredNotificationMethod();
        $message = $this->buildEmergencyMessage($panic, $contact, $context);

        // Create notification record
        $notification = PanicNotification::create([
            'panic_activation_id' => $panic->id,
            'recipient_type' => 'emergency_contact',
            'recipient_identifier' => $contact->phone,
            'notification_method' => $preferredMethod,
            'message_content' => $message,
        ]);

        try {
            switch ($preferredMethod) {
                case 'sms':
                    $this->sendEmergencySMS($contact->phone, $message);
                    break;
                case 'call':
                    $this->makeEmergencyCall($contact->phone, $message);
                    break;
                case 'email':
                    $this->sendEmergencyEmail($contact->email, $message, $context);
                    break;
                case 'whatsapp':
                    $this->sendEmergencyWhatsApp($contact->phone, $message);
                    break;
            }

            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send emergency notification', [
                'error' => $e->getMessage(),
                'panic_id' => $panic->id,
                'contact_id' => $contact->id,
                'method' => $preferredMethod,
            ]);

            $notification->update([
                'status' => 'failed',
                'response_data' => ['error' => $e->getMessage()],
            ]);
        }
    }

    /**
     * Build emergency message
     */
    private function buildEmergencyMessage(
        PanicActivation $panic,
        UserEmergencyContact $contact,
        array $context
    ): string {
        $userName = $context['user_info']['name'];
        $triggerType = $panic->trigger_type_display_name;
        $location = $panic->formatted_location;
        $time = $panic->triggered_at->format('M j, Y \a\t g:i A');

        $message = "🚨 EMERGENCY ALERT 🚨\n\n";
        $message .= "{$userName} has activated their panic button.\n\n";
        $message .= "Type: {$triggerType}\n";
        $message .= "Time: {$time}\n";
        $message .= "Location: {$location}\n";

        if ($panic->user_message) {
            $message .= "Message: {$panic->user_message}\n";
        }

        $message .= "\nPlease check on them immediately and contact authorities if needed.";
        $message .= "\n\nThis is an automated emergency alert from YorYor Dating App.";

        return $message;
    }

    /**
     * Get user safety settings
     */
    private function getUserSafetySettings(User $user): UserSafetySettings
    {
        return UserSafetySettings::firstOrCreate(
            ['user_id' => $user->id],
            [
                'panic_button_enabled' => true,
                'emergency_contacts_enabled' => true,
                'check_in_interval_minutes' => 60,
            ]
        );
    }

    /**
     * Check if safety setup is complete
     */
    private function isSafetySetupComplete(User $user): bool
    {
        $emergencyContacts = $user->emergencyContacts()
            ->verified()
            ->count();

        return $emergencyContacts >= 1 && $user->safety_features_enabled;
    }

    /**
     * Get recent panic activations
     */
    private function getRecentActivations(User $user, int $limit = 5): array
    {
        return PanicActivation::where('user_id', $user->id)
            ->latest('triggered_at')
            ->limit($limit)
            ->get(['id', 'trigger_type', 'status', 'triggered_at', 'resolved_at'])
            ->toArray();
    }

    /**
     * Get count of notified contacts
     */
    private function getNotifiedContactsCount(PanicActivation $panic): int
    {
        return $panic->notifications()
            ->where('recipient_type', 'emergency_contact')
            ->where('status', 'sent')
            ->count();
    }

    /**
     * Send various types of emergency notifications
     */
    private function sendEmergencySMS(string $phone, string $message): void
    {
        // Implementation depends on your SMS provider (Twilio, etc.)
        // This is a placeholder
    }

    private function makeEmergencyCall(string $phone, string $message): void
    {
        // Implementation for voice calls
        // This is a placeholder
    }

    private function sendEmergencyEmail(string $email, string $message, array $context): void
    {
        // Implementation for email alerts
        // This is a placeholder
    }

    private function sendEmergencyWhatsApp(string $phone, string $message): void
    {
        // Implementation for WhatsApp alerts
        // This is a placeholder
    }

    private function sendVerificationCode(UserEmergencyContact $contact, string $code): void
    {
        $message = "Your YorYor emergency contact verification code is: {$code}";
        $this->sendEmergencySMS($contact->phone, $message);
    }

    private function testContactReachability(UserEmergencyContact $contact): array
    {
        return $contact->testReachability();
    }

    private function sendTestNotifications(User $user, $contacts): void
    {
        foreach ($contacts as $contact) {
            $message = "This is a test of your emergency contact system for {$user->profile->first_name}. The panic button is working correctly.";
            $this->sendEmergencySMS($contact->phone, $message);
        }
    }

    private function sendCancellationNotifications(PanicActivation $panic, ?string $reason): void
    {
        $user = $panic->user;
        $contacts = $user->emergencyContacts()->panicAlertRecipients()->get();

        foreach ($contacts as $contact) {
            $message = "✅ EMERGENCY CANCELLED\n\n";
            $message .= "{$user->profile->first_name} has cancelled their emergency alert.\n";
            if ($reason) {
                $message .= "Reason: {$reason}\n";
            }
            $message .= "\nThey are safe. No further action needed.";

            $this->sendEmergencySMS($contact->phone, $message);
        }
    }

    private function notifyAdminsOfPanic(PanicActivation $panic): void
    {
        // Notify app administrators of panic activation
        $this->notificationService->notifyAdminsOfPanicActivation($panic);
    }

    private function notifyAdminsImmediate(PanicActivation $panic): void
    {
        // Send immediate high-priority notifications to admins
        $this->notificationService->notifyAdminsImmediate($panic);
    }
}