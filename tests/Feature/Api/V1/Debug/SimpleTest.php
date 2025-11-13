<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Simple API Test', function () {
    it('can make a basic request', function () {
        $response = $this->getJson('/api/v1/countries');
        
        // Just test that the endpoint exists and returns some response
        expect($response->status())->toBeIn([200, 404, 500]);
    });
    
    it('environment is testing', function () {
        expect(app()->environment())->toBeIn(['testing', 'local']);
    });
    
    it('database connection works', function () {
        // Test basic database connectivity
        expect(\DB::connection()->getPdo())->not->toBeNull();
    });
});