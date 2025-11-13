<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting comprehensive database seeding...');

        // 1. Create roles and permissions first
        $this->command->info('1. Creating roles and permissions...');
        $this->call(RoleSeeder::class);

        // 2. Create countries (required for users)
        $this->command->info('2. Creating countries...');
        $this->call(CountrySeeder::class);

        // 3. Create subscription plans and pricing
        $this->command->info('3. Creating subscription plans...');
        $this->call(SubscriptionPlanSeeder::class);

        // 4. Create 500 users with complete profiles
        $this->command->info('4. Creating 500 users with complete profiles...');
        $this->call(UserSeeder::class);

        // 5. Create additional stories for users
        $this->command->info('5. Creating additional user stories...');
        $this->call(UserStorySeeder::class);

        // 6. Create additional data (notifications, subscriptions, etc.)
        $this->command->info('6. Creating additional user data...');
        $this->call(AdditionalDataSeeder::class);

        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📊 Created:');
        $this->command->info('   • Countries: ' . \App\Models\Country::count());
        $this->command->info('   • Users: ' . \App\Models\User::count());
        $this->command->info('   • Profiles: ' . \App\Models\Profile::count());
        $this->command->info('   • User Photos: ' . \App\Models\UserPhoto::count());
        $this->command->info('   • User Stories: ' . \App\Models\UserStory::count());
        $this->command->info('   • Likes: ' . \App\Models\Like::count());
        $this->command->info('   • Matches: ' . \App\Models\MatchModel::count());
        $this->command->info('   • Chats: ' . \App\Models\Chat::count());
        $this->command->info('   • Messages: ' . \App\Models\Message::count());
        $this->command->info('   • Notifications: ' . \App\Models\Notification::count());
        $this->command->info('   • User Subscriptions: ' . \App\Models\UserSubscription::count());
        $this->command->info('   • Payment Transactions: ' . \App\Models\PaymentTransaction::count());
        $this->command->info('   • Calls: ' . \App\Models\Call::count());
        $this->command->info('   • User Blocks: ' . \App\Models\UserBlock::count());
        $this->command->info('   • User Reports: ' . \App\Models\UserReport::count());
        $this->command->info('   • User Activities: ' . \App\Models\UserActivity::count());
        $this->command->info('   • Subscription Plans: ' . \App\Models\SubscriptionPlan::count());
        $this->command->info('   • Roles: ' . \App\Models\Role::count());
        $this->command->info('   • Permissions: ' . \App\Models\Permission::count());
        $this->command->info('');
        $this->command->info('✅ All seeders completed successfully!');
    }
}
