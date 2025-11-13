<?php

namespace Database\Seeders;

use App\Models\Call;
use App\Models\DataExportRequest;
use App\Models\PanicActivation;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\UserBlock;
use App\Models\UserFeedback;
use App\Models\UserMonthlyUsage;
use App\Models\UserReport;
use App\Models\UserSafetyScore;
use App\Models\UserSetting;
use App\Models\UserSubscription;
use App\Models\UserUsageLimits;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Notifications\DatabaseNotification;

class AdditionalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating additional data for users...');

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run UserSeeder first.');

            return;
        }

        // Create user subscriptions
        $this->createUserSubscriptions($users);

        // Create notifications
        $this->createNotifications($users);

        // Create user blocks and reports
        $this->createUserBlocksAndReports($users);

        // Create user activities
        $this->createUserActivities($users);

        // Create user settings
        $this->createUserSettings($users);

        // Create usage limits and monthly usage
        $this->createUsageData($users);

        // Create payment transactions
        $this->createPaymentTransactions($users);

        // Create calls
        $this->createCalls($users);

        // Create safety data
        $this->createSafetyData($users);

        // Create feedback and export requests
        $this->createFeedbackAndExports($users);

        $this->command->info('✅ Additional data created successfully!');
    }

    private function createUserSubscriptions($users): void
    {
        $this->command->info('Creating user subscriptions...');

        $subscriptionPlans = \App\Models\SubscriptionPlan::all();
        $progressBar = $this->command->getOutput()->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            // 70% of users have subscriptions
            if (rand(1, 10) <= 7) {
                $plan = $subscriptionPlans->random();
                $startDate = Carbon::now()->subDays(rand(0, 365));
                $endDate = $startDate->copy()->addMonths(rand(1, 12));

                UserSubscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'payment_provider' => fake()->randomElement(['stripe', 'payme', 'click', 'manual']),
                    'provider_subscription_id' => fake()->uuid(),
                    'status' => fake()->randomElement(['active', 'canceled', 'expired', 'past_due', 'trialing']),
                    'current_period_start' => $startDate,
                    'current_period_end' => $endDate,
                    'canceled_at' => fake()->optional(0.2)->dateTimeBetween($startDate, 'now'),
                    'trial_ends_at' => fake()->optional(0.1)->dateTimeBetween($startDate, $endDate),
                    'metadata' => json_encode([
                        'auto_renew' => rand(0, 1),
                        'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'apple_pay', 'google_pay']),
                        'billing_cycle' => fake()->randomElement(['monthly', 'yearly']),
                    ]),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }

    private function createNotifications($users): void
    {
        $this->command->info('Creating notifications...');

        $notificationTypes = ['match', 'message', 'like', 'view', 'system', 'verification', 'subscription'];
        $progressBar = $this->command->getOutput()->createProgressBar($users->count() * 5);
        $progressBar->start();

        foreach ($users as $user) {
            // Create 2-5 notifications per user
            $notificationCount = rand(2, 5);

            for ($i = 0; $i < $notificationCount; $i++) {
                $type = fake()->randomElement($notificationTypes);
                $isRead = rand(0, 1);

                DatabaseNotification::create([
                    'id' => fake()->uuid(),
                    'type' => $type,
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => [
                        'title' => $this->getNotificationTitle($type),
                        'message' => $this->getNotificationMessage($type),
                        'additional_data' => $this->getNotificationData($type),
                    ],
                    'read_at' => $isRead ? Carbon::now()->subDays(rand(0, 30)) : null,
                    'created_at' => Carbon::now()->subDays(rand(0, 30)),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }

    private function createUserBlocksAndReports($users): void
    {
        $this->command->info('Creating user blocks and reports...');

        $progressBar = $this->command->getOutput()->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            // 20% of users have blocked someone
            if (rand(1, 10) <= 2) {
                $blockedUser = $users->where('id', '!=', $user->id)->random();

                // Check if block already exists
                if (! UserBlock::where('blocker_id', $user->id)->where('blocked_id', $blockedUser->id)->exists()) {
                    UserBlock::create([
                        'blocker_id' => $user->id,
                        'blocked_id' => $blockedUser->id,
                        'reason' => fake()->randomElement(['inappropriate_behavior', 'spam', 'harassment', 'fake_profile', 'other']),
                    ]);
                }
            }

            // 10% of users have reported someone
            if (rand(1, 10) === 1) {
                $reportedUser = $users->where('id', '!=', $user->id)->random();

                UserReport::create([
                    'reporter_id' => $user->id,
                    'reported_user_id' => $reportedUser->id,
                    'category' => fake()->randomElement(['inappropriate_behavior', 'harassment', 'fake_profile', 'spam', 'inappropriate_photos', 'scam_attempt', 'hate_speech', 'violence_threat', 'underage', 'stolen_photos', 'catfishing', 'inappropriate_messages', 'offline_behavior', 'other']),
                    'description' => fake()->paragraph(),
                    'severity' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
                    'status' => fake()->randomElement(['pending', 'under_review', 'resolved', 'dismissed']),
                    'priority_score' => rand(1, 10),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }

    private function createUserActivities($users): void
    {
        $this->command->info('Creating user activities...');

        $activityTypes = ['login', 'logout', 'swipe_right', 'swipe_left', 'message_sent', 'profile_view', 'photo_upload', 'match_made', 'profile_updated'];
        $progressBar = $this->command->getOutput()->createProgressBar($users->count() * 10);
        $progressBar->start();

        foreach ($users as $user) {
            // Create 3-8 activities per user
            $activityCount = rand(3, 8);

            for ($i = 0; $i < $activityCount; $i++) {
                UserActivity::create([
                    'user_id' => $user->id,
                    'activity_type' => fake()->randomElement($activityTypes),
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'metadata' => $this->getActivityMetadata(fake()->randomElement($activityTypes)),
                    'created_at' => Carbon::now()->subDays(rand(0, 30)),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }

    private function createUserSettings($users): void
    {
        $this->command->info('Creating user settings...');

        $progressBar = $this->command->getOutput()->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            // Check if settings already exist
            if (! UserSetting::where('user_id', $user->id)->exists()) {
                UserSetting::create([
                    'user_id' => $user->id,
                    'email_notifications_enabled' => rand(0, 1),
                    'push_notifications_enabled' => rand(0, 1),
                    'profile_visible' => rand(0, 1),
                    'profile_visibility_level' => fake()->randomElement(['everyone', 'friends', 'private']),
                    'show_online_status' => rand(0, 1),
                    'allow_messages_from_matches' => rand(0, 1),
                    'allow_messages_from_all' => rand(0, 1),
                    'show_me_on_discovery' => rand(0, 1),
                    'global_mode' => rand(0, 1),
                    'min_age' => rand(18, 25),
                    'max_age' => rand(25, 35),
                    'max_distance' => rand(10, 50),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }

    private function createUsageData($users): void
    {
        $this->command->info('Creating usage data...');

        $progressBar = $this->command->getOutput()->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            // Create usage limits for the last 7 days
            for ($i = 0; $i < 7; $i++) {
                $date = Carbon::now()->subDays($i);
                // Check if usage limits already exist for this user and date
                if (! UserUsageLimits::where('user_id', $user->id)->where('date', $date)->exists()) {
                    UserUsageLimits::create([
                        'user_id' => $user->id,
                        'date' => $date,
                        'swipes_used' => rand(0, 50),
                        'likes_used' => rand(0, 20),
                        'video_calls_used' => rand(0, 5),
                        'voice_calls_used' => rand(0, 10),
                        'video_minutes_used' => rand(0, 120),
                        'voice_minutes_used' => rand(0, 180),
                    ]);
                }
            }

            // Create monthly usage
            if (! UserMonthlyUsage::where('user_id', $user->id)->where('year', Carbon::now()->year)->where('month', Carbon::now()->month)->exists()) {
                UserMonthlyUsage::create([
                    'user_id' => $user->id,
                    'year' => Carbon::now()->year,
                    'month' => Carbon::now()->month,
                    'video_calls_count' => rand(0, 20),
                    'voice_calls_count' => rand(0, 50),
                    'video_minutes_total' => rand(0, 1200),
                    'voice_minutes_total' => rand(0, 1800),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }

    private function createPaymentTransactions($users): void
    {
        $this->command->info('Creating payment transactions...');

        $progressBar = $this->command->getOutput()->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            // 60% of users have payment transactions
            if (rand(1, 10) <= 6) {
                $transactionCount = rand(1, 5);

                for ($i = 0; $i < $transactionCount; $i++) {
                    PaymentTransaction::create([
                        'user_id' => $user->id,
                        'subscription_id' => null, // One-time payment
                        'provider' => fake()->randomElement(['stripe', 'payme', 'click']),
                        'provider_transaction_id' => 'txn_'.fake()->uuid(),
                        'type' => fake()->randomElement(['subscription', 'one_time', 'refund']),
                        'amount' => fake()->randomFloat(2, 5, 100),
                        'currency' => fake()->randomElement(['USD', 'UZS', 'EUR', 'RUB']),
                        'status' => fake()->randomElement(['pending', 'succeeded', 'failed', 'refunded']),
                        'provider_data' => json_encode([
                            'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'apple_pay', 'google_pay']),
                            'description' => fake()->randomElement(['Monthly Subscription', 'Annual Subscription', 'Boost Purchase', 'Super Like Pack']),
                        ]),
                        'failure_reason' => fake()->optional(0.1)->randomElement(['insufficient_funds', 'card_declined', 'network_error']),
                        'created_at' => Carbon::now()->subDays(rand(0, 90)),
                    ]);
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }

    private function createCalls($users): void
    {
        $this->command->info('Creating calls...');

        $progressBar = $this->command->getOutput()->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            // 40% of users have made calls
            if (rand(1, 10) <= 4) {
                $callCount = rand(1, 10);

                for ($i = 0; $i < $callCount; $i++) {
                    $otherUser = $users->where('id', '!=', $user->id)->random();
                    $callType = fake()->randomElement(['video', 'voice']);
                    $duration = rand(30, 3600); // 30 seconds to 1 hour

                    Call::create([
                        'channel_name' => 'call_'.fake()->uuid(),
                        'caller_id' => $user->id,
                        'receiver_id' => $otherUser->id,
                        'type' => $callType,
                        'status' => fake()->randomElement(['initiated', 'ongoing', 'completed', 'missed', 'rejected']),
                        'started_at' => Carbon::now()->subDays(rand(0, 30)),
                        'ended_at' => Carbon::now()->subDays(rand(0, 30))->addSeconds($duration),
                    ]);
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }

    private function createSafetyData($users): void
    {
        $this->command->info('Creating safety data...');

        $progressBar = $this->command->getOutput()->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            // Create safety score
            if (! UserSafetyScore::where('user_id', $user->id)->exists()) {
                UserSafetyScore::create([
                    'user_id' => $user->id,
                    'overall_score' => rand(60, 100),
                    'behavior_score' => rand(60, 100),
                    'verification_score' => rand(0, 100),
                    'report_count' => rand(0, 5),
                    'positive_interactions' => rand(0, 10),
                    'last_calculated_at' => Carbon::now()->subDays(rand(0, 7)),
                ]);
            }

            // 5% of users have panic activations
            if (rand(1, 20) === 1) {
                PanicActivation::create([
                    'user_id' => $user->id,
                    'trigger_type' => fake()->randomElement(['emergency_contact', 'location_sharing', 'fake_call', 'silent_alarm', 'safe_word', 'date_check_in']),
                    'latitude' => fake()->latitude(),
                    'longitude' => fake()->longitude(),
                    'location_address' => fake()->address(),
                    'location_accuracy' => rand(1, 100),
                    'device_info' => json_encode([
                        'device_type' => fake()->randomElement(['mobile', 'desktop']),
                        'os' => fake()->randomElement(['iOS', 'Android', 'Windows', 'macOS']),
                        'app_version' => fake()->randomElement(['1.0.0', '1.1.0', '1.2.0']),
                    ]),
                    'context_data' => json_encode([
                        'date_info' => fake()->sentence(),
                        'match_details' => fake()->sentence(),
                    ]),
                    'user_message' => fake()->optional(0.5)->sentence(),
                    'status' => fake()->randomElement(['active', 'resolved', 'false_alarm', 'escalated']),
                    'triggered_at' => Carbon::now()->subDays(rand(0, 30)),
                    'resolved_at' => fake()->optional(0.7)->dateTimeBetween(Carbon::now()->subDays(30), 'now'),
                    'authorities_contacted' => rand(0, 1),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }

    private function createFeedbackAndExports($users): void
    {
        $this->command->info('Creating feedback and export requests...');

        $progressBar = $this->command->getOutput()->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            // 30% of users have feedback
            if (rand(1, 10) <= 3) {
                UserFeedback::create([
                    'user_id' => $user->id,
                    'type' => fake()->randomElement(['bug', 'feature', 'complaint', 'suggestion', 'other']),
                    'subject' => fake()->sentence(3),
                    'message' => fake()->paragraph(),
                    'status' => fake()->randomElement(['pending', 'acknowledged', 'in_progress', 'resolved', 'closed']),
                    'created_at' => Carbon::now()->subDays(rand(0, 30)),
                ]);
            }

            // 10% of users have data export requests
            if (rand(1, 10) === 1) {
                DataExportRequest::create([
                    'user_id' => $user->id,
                    'status' => fake()->randomElement(['pending', 'processing', 'completed', 'failed']),
                    'export_url' => rand(0, 1) ? 'https://example.com/exports/'.fake()->uuid().'.zip' : null,
                    'expires_at' => Carbon::now()->addDays(7),
                    'created_at' => Carbon::now()->subDays(rand(0, 30)),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }

    private function getNotificationTitle($type): string
    {
        return match ($type) {
            'match' => 'New Match!',
            'message' => 'New Message',
            'like' => 'Someone Liked You',
            'view' => 'Profile View',
            'system' => 'System Notification',
            'verification' => 'Verification Update',
            'subscription' => 'Subscription Update',
            default => 'Notification'
        };
    }

    private function getNotificationMessage($type): string
    {
        return match ($type) {
            'match' => 'You have a new match!',
            'message' => 'You received a new message',
            'like' => 'Someone liked your profile',
            'view' => 'Your profile was viewed',
            'system' => 'System update available',
            'verification' => 'Your verification status has been updated',
            'subscription' => 'Your subscription has been renewed',
            default => 'You have a new notification'
        };
    }

    private function getNotificationData($type): array
    {
        return match ($type) {
            'match' => ['match_id' => rand(1, 1000)],
            'message' => ['message_id' => rand(1, 1000)],
            'like' => ['liker_id' => rand(1, 1000)],
            'view' => ['viewer_id' => rand(1, 1000)],
            'system' => ['update_type' => 'maintenance'],
            'verification' => ['verification_type' => 'photo'],
            'subscription' => ['plan_id' => rand(1, 4)],
            default => []
        };
    }

    private function getActivityDescription($type): string
    {
        return match ($type) {
            'login' => 'User logged in',
            'profile_view' => 'Viewed a profile',
            'message_sent' => 'Sent a message',
            'like_sent' => 'Liked a profile',
            'match_created' => 'Created a match',
            'photo_uploaded' => 'Uploaded a photo',
            'story_created' => 'Created a story',
            default => 'User activity'
        };
    }

    private function getActivityMetadata($type): array
    {
        return match ($type) {
            'login' => ['ip_address' => fake()->ipv4()],
            'profile_view' => ['viewed_user_id' => rand(1, 1000)],
            'message_sent' => ['recipient_id' => rand(1, 1000)],
            'like_sent' => ['liked_user_id' => rand(1, 1000)],
            'match_created' => ['matched_user_id' => rand(1, 1000)],
            'photo_uploaded' => ['photo_id' => rand(1, 1000)],
            'story_created' => ['story_id' => rand(1, 1000)],
            default => []
        };
    }
}
