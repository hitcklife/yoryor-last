<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Debug API Responses', function () {
    it('checks profile/me response', function () {
        $response = $this->getJson('/api/v1/profile/me');
        
        dump('Status: ' . $response->status());
        dump('Response: ' . $response->getContent());
        
        expect($response->status())->toBeIn([200, 401, 422]);
    });
});