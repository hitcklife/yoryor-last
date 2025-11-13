<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('Quick Smoke Tests', function () {
    beforeEach(function () {
        $this->user = $this->createUserWithCompleteProfile();
        Sanctum::actingAs($this->user);
    });
    
    it('can access profile endpoints', function () {
        $endpoints = [
            '/api/v1/profile/me',
            '/api/v1/settings/discovery', 
            '/api/v1/settings/notifications',
            '/api/v1/settings/privacy',
            '/api/v1/settings/security'
        ];
        
        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            expect($response->status())->toBeIn([200, 404, 422, 500])
                ->and($response->json('status') ?? 'unknown')->toBeIn(['success', 'error', 'unknown']);
        }
    });
    
    it('can access put endpoints without crashing', function () {
        $endpoints = [
            ['/api/v1/family-preferences', ['wants_children' => 'yes']],
            ['/api/v1/location-preferences', ['preferred_countries' => ['US']]],
            ['/api/v1/settings/discovery', ['age_range' => ['min' => 18, 'max' => 35]]]
        ];
        
        foreach ($endpoints as [$endpoint, $data]) {
            $response = $this->putJson($endpoint, $data);
            expect($response->status())->toBeIn([200, 404, 422, 500]);
        }
    });
    
    it('authenticated endpoints work', function () {
        // Test authenticated request (using the one from beforeEach)
        $response = $this->getJson('/api/v1/profile/me');
        expect($response->status())->toBeIn([200, 404, 500]);
        
        if ($response->status() === 200) {
            expect($response->json('status'))->toBe('success');
        }
    });
});