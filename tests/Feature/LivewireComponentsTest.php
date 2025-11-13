<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Pages\NotificationsPage;
use App\Livewire\Pages\BlockedUsersPage;
use App\Livewire\Pages\SubscriptionPage;
use App\Livewire\Pages\VerificationPage;
use App\Livewire\Pages\SearchPage;
use App\Livewire\Pages\VideoCallPage;
use App\Livewire\Components\PanicButton;
use App\Livewire\Pages\InsightsPage;

class LivewireComponentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_page_loads()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(NotificationsPage::class)
            ->assertSee('Notifications')
            ->assertSee('All')
            ->assertSee('Unread');
    }

    public function test_notifications_page_can_mark_as_read()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(NotificationsPage::class)
            ->call('markAsRead', 1)
            ->assertDispatched('notification-read');
    }

    public function test_notifications_page_can_mark_all_as_read()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(NotificationsPage::class)
            ->call('markAllAsRead')
            ->assertDispatched('notification-read');
    }

    public function test_notifications_page_can_delete_notification()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(NotificationsPage::class)
            ->call('deleteNotification', 1)
            ->assertDispatched('notification-deleted');
    }

    public function test_blocked_users_page_loads()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(BlockedUsersPage::class)
            ->assertSee('Blocked Users')
            ->assertSee('Search blocked users');
    }

    public function test_blocked_users_page_can_unblock_user()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(BlockedUsersPage::class)
            ->call('unblockUser', 1)
            ->assertSee('User unblocked successfully');
    }

    public function test_subscription_page_loads()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(SubscriptionPage::class)
            ->assertSee('Subscription & Billing')
            ->assertSee('Overview')
            ->assertSee('Plans')
            ->assertSee('Billing History');
    }

    public function test_subscription_page_can_switch_tabs()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(SubscriptionPage::class)
            ->set('activeTab', 'plans')
            ->assertSee('Choose Your Plan')
            ->set('activeTab', 'billing')
            ->assertSee('Billing History');
    }

    public function test_subscription_page_can_upgrade_plan()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(SubscriptionPage::class)
            ->call('upgradePlan', 'premium')
            ->assertDispatched('redirect-to-payment', planId: 'premium');
    }

    public function test_verification_page_loads()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(VerificationPage::class)
            ->assertSee('Account Verification')
            ->assertSee('Photo Verification')
            ->assertSee('ID Verification')
            ->assertSee('Phone Verification')
            ->assertSee('Email Verification');
    }

    public function test_verification_page_can_switch_tabs()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(VerificationPage::class)
            ->set('activeTab', 'id')
            ->assertSee('ID Verification')
            ->set('activeTab', 'phone')
            ->assertSee('Phone Verification');
    }

    public function test_verification_page_can_send_phone_verification()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(VerificationPage::class)
            ->set('phoneNumber', '+1234567890')
            ->call('sendPhoneVerification')
            ->assertSee('Verification code sent');
    }

    public function test_search_page_loads()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(SearchPage::class)
            ->assertSee('Search & Discover')
            ->assertSee('Search by name, interests, profession');
    }

    public function test_search_page_can_search()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(SearchPage::class)
            ->set('searchTerm', 'test')
            ->assertSee('test');
    }

    public function test_search_page_can_clear_filters()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(SearchPage::class)
            ->call('clearFilters')
            ->assertSet('filters.age_min', 18)
            ->assertSet('filters.age_max', 65);
    }

    public function test_video_call_page_loads()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(VideoCallPage::class)
            ->assertSee('Video Calls')
            ->assertSee('Call')
            ->assertSee('History')
            ->assertSee('Scheduled');
    }

    public function test_video_call_page_can_initiate_call()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(VideoCallPage::class)
            ->call('initiateCall', 1, 'video')
            ->assertSet('callStatus', 'ringing')
            ->assertDispatched('initiate-call');
    }

    public function test_video_call_page_can_toggle_mute()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(VideoCallPage::class)
            ->call('toggleMute')
            ->assertSet('isMuted', true)
            ->assertDispatched('toggle-mute');
    }

    public function test_panic_button_loads()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(PanicButton::class)
            ->assertSee('Safety & Emergency')
            ->assertSee('Panic Button')
            ->assertSee('Emergency Contacts');
    }

    public function test_panic_button_can_activate()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(PanicButton::class)
            ->call('activatePanicButton')
            ->assertSet('isPanicActive', true)
            ->assertSet('panicCountdown', 10)
            ->assertDispatched('start-panic-countdown');
    }

    public function test_panic_button_can_send_safety_check()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(PanicButton::class)
            ->call('sendSafetyCheck')
            ->assertSee('Safety check sent');
    }

    public function test_insights_page_loads()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(InsightsPage::class)
            ->assertSee('Insights & Analytics')
            ->assertSee('Overview')
            ->assertSee('Profile Analytics')
            ->assertSee('Match Insights');
    }

    public function test_insights_page_can_switch_tabs()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(InsightsPage::class)
            ->set('activeTab', 'profile')
            ->assertSee('Profile Performance')
            ->set('activeTab', 'matches')
            ->assertSee('Match Insights');
    }

    public function test_insights_page_can_export_data()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(InsightsPage::class)
            ->call('exportData')
            ->assertSee('Data exported successfully');
    }

    public function test_insights_page_can_generate_report()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(InsightsPage::class)
            ->call('generateReport')
            ->assertSee('Report generated successfully');
    }

    public function test_all_components_require_authentication()
    {
        $components = [
            NotificationsPage::class,
            BlockedUsersPage::class,
            SubscriptionPage::class,
            VerificationPage::class,
            SearchPage::class,
            VideoCallPage::class,
            PanicButton::class,
            InsightsPage::class
        ];

        foreach ($components as $component) {
            Livewire::test($component)
                ->assertRedirect('/login');
        }
    }

    public function test_components_handle_empty_data_gracefully()
    {
        $user = User::factory()->create();
        
        // Test with no data
        Livewire::actingAs($user)
            ->test(NotificationsPage::class)
            ->assertSee('No notifications found');
            
        Livewire::actingAs($user)
            ->test(BlockedUsersPage::class)
            ->assertSee('No users are currently blocked');
    }

    public function test_components_validate_user_input()
    {
        $user = User::factory()->create();
        
        // Test search validation
        Livewire::actingAs($user)
            ->test(SearchPage::class)
            ->set('searchTerm', '')
            ->assertSet('searchTerm', '');
            
        // Test phone number validation
        Livewire::actingAs($user)
            ->test(VerificationPage::class)
            ->set('phoneNumber', 'invalid')
            ->call('sendPhoneVerification')
            ->assertHasErrors('phoneNumber');
    }
}
