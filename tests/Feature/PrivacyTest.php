<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\PrivacyService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected $privacyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->privacyService = new PrivacyService();
    }

    public function test_user_can_set_privacy_settings()
    {
        $user = User::factory()->create();
        
        $privacySettings = [
            'profile_visibility' => 'private',
            'show_online_status' => false,
            'show_location' => false,
            'data_sharing' => false,
            'marketing_emails' => false
        ];
        
        $this->privacyService->updateUserPrivacySettings($user->id, $privacySettings);
        
        $updatedSettings = $this->privacyService->getUserPrivacySettings($user->id);
        
        $this->assertEquals('private', $updatedSettings['profile_visibility']);
        $this->assertFalse($updatedSettings['show_online_status']);
        $this->assertFalse($updatedSettings['show_location']);
        $this->assertFalse($updatedSettings['data_sharing']);
        $this->assertFalse($updatedSettings['marketing_emails']);
    }

    public function test_privacy_settings_control_data_access()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Set user2's privacy to private
        $this->privacyService->updateUserPrivacySettings($user2->id, [
            'profile_visibility' => 'private'
        ]);
        
        // User1 should not be able to access user2's data
        $canAccess = $this->privacyService->canAccessUserData($user1->id, $user2->id, 'profile');
        
        $this->assertFalse($canAccess);
    }

    public function test_public_privacy_settings_allow_access()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Set user2's privacy to public
        $this->privacyService->updateUserPrivacySettings($user2->id, [
            'profile_visibility' => 'public'
        ]);
        
        // User1 should be able to access user2's data
        $canAccess = $this->privacyService->canAccessUserData($user1->id, $user2->id, 'profile');
        
        $this->assertTrue($canAccess);
    }

    public function test_user_can_export_their_data()
    {
        $user = User::factory()->create();
        
        $exportPath = $this->privacyService->exportUserData($user->id);
        
        $this->assertNotNull($exportPath);
        $this->assertStringContainsString('user_data_export', $exportPath);
    }

    public function test_user_data_can_be_anonymized()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        $this->privacyService->anonymizeUserData($user->id);
        
        $user->refresh();
        
        $this->assertEquals('Deleted User', $user->name);
        $this->assertStringContainsString('deleted_', $user->email);
        $this->assertNotNull($user->deleted_at);
    }

    public function test_privacy_compliance_report()
    {
        $report = $this->privacyService->getPrivacyComplianceReport();
        
        $this->assertArrayHasKey('data_retention_policies', $report);
        $this->assertArrayHasKey('privacy_features', $report);
        $this->assertArrayHasKey('compliance_status', $report);
        
        $this->assertEquals('compliant', $report['compliance_status']);
        $this->assertTrue($report['privacy_features']['data_export']);
        $this->assertTrue($report['privacy_features']['data_anonymization']);
    }

    public function test_photo_privacy_settings()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Set user2 to not show photos
        $this->privacyService->updateUserPrivacySettings($user2->id, [
            'show_photos' => false
        ]);
        
        $canAccess = $this->privacyService->canAccessUserData($user1->id, $user2->id, 'photos');
        
        $this->assertFalse($canAccess);
    }

    public function test_location_privacy_settings()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Set user2 to not show location
        $this->privacyService->updateUserPrivacySettings($user2->id, [
            'show_location' => false
        ]);
        
        $canAccess = $this->privacyService->canAccessUserData($user1->id, $user2->id, 'location');
        
        $this->assertFalse($canAccess);
    }

    public function test_online_status_privacy_settings()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Set user2 to not show online status
        $this->privacyService->updateUserPrivacySettings($user2->id, [
            'show_online_status' => false
        ]);
        
        $canAccess = $this->privacyService->canAccessUserData($user1->id, $user2->id, 'online_status');
        
        $this->assertFalse($canAccess);
    }

    public function test_interests_privacy_settings()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Set user2 to not show interests
        $this->privacyService->updateUserPrivacySettings($user2->id, [
            'show_interests' => false
        ]);
        
        $canAccess = $this->privacyService->canAccessUserData($user1->id, $user2->id, 'interests');
        
        $this->assertFalse($canAccess);
    }

    public function test_data_retention_policies()
    {
        $report = $this->privacyService->getPrivacyComplianceReport();
        
        $retentionPolicies = $report['data_retention_policies'];
        
        $this->assertEquals(90, $retentionPolicies['profile_views']);
        $this->assertEquals(30, $retentionPolicies['search_history']);
        $this->assertEquals(365, $retentionPolicies['message_history']);
        $this->assertEquals(730, $retentionPolicies['analytics']);
        $this->assertEquals(30, $retentionPolicies['logs']);
    }

    public function test_privacy_settings_are_persistent()
    {
        $user = User::factory()->create();
        
        $privacySettings = [
            'profile_visibility' => 'friends',
            'show_online_status' => true,
            'data_sharing' => true
        ];
        
        $this->privacyService->updateUserPrivacySettings($user->id, $privacySettings);
        
        // Retrieve settings again
        $retrievedSettings = $this->privacyService->getUserPrivacySettings($user->id);
        
        $this->assertEquals('friends', $retrievedSettings['profile_visibility']);
        $this->assertTrue($retrievedSettings['show_online_status']);
        $this->assertTrue($retrievedSettings['data_sharing']);
    }
}
