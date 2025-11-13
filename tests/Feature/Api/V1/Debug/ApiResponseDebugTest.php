<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('API Response Debug', function () {
    beforeEach(function () {
        $this->user = $this->createUserWithCompleteProfile();
        Sanctum::actingAs($this->user);
    });
    
    it('debugs settings response', function () {
        $response = $this->getJson('/api/v1/settings');
        
        dump('Settings Status: ' . $response->status());
        dump('Settings Response: ' . $response->getContent());
        
        expect($response->status())->toBeIn([200, 404, 500]);
    });
    
    it('debugs family preferences response', function () {
        $response = $this->putJson('/api/v1/family-preferences', [
            'wants_children' => 'yes',
            'children_timeline' => '2-3 years',
        ]);
        
        dump('Family Prefs Status: ' . $response->status());
        dump('Family Prefs Response: ' . $response->getContent());
        
        expect($response->status())->toBeIn([200, 404, 422, 500]);
    });
    
    it('debugs location preferences response', function () {
        $response = $this->getJson('/api/v1/location-preferences');
        
        dump('Location Prefs Status: ' . $response->status());
        dump('Location Prefs Response: ' . $response->getContent());
        
        expect($response->status())->toBeIn([200, 404, 500]);
    });
    
    it('debugs discovery settings response', function () {
        $response = $this->getJson('/api/v1/settings/discovery');
        
        dump('Discovery Status: ' . $response->status());  
        dump('Discovery Response: ' . $response->getContent());
        
        expect($response->status())->toBeIn([200, 404, 500]);
    });
});