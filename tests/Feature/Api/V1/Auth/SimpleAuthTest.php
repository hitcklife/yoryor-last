<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('Authentication API Smoke Tests', function () {
    it('can send OTP for authentication', function () {
        $response = $this->postJson('/api/v1/auth/authenticate', [
            'phone' => '+998901234567',
        ]);

        expect($response->status())->toBe(200);
        expect($response->json('status'))->toBe('success');
    });

    it('can check email availability', function () {
        $response = $this->postJson('/api/v1/auth/check-email', [
            'email' => 'test@example.com'
        ]);

        expect($response->status())->toBe(200);
        expect($response->json('status'))->toBe('success');
    });

    it('requires authentication for logout', function () {
        $response = $this->postJson('/api/v1/auth/logout');
        expect($response->status())->toBe(401);
    });

    it('can logout authenticated user', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/logout');
        expect($response->status())->toBeIn([200, 204]);
    });

    it('requires authentication for home stats', function () {
        $response = $this->getJson('/api/v1/auth/home-stats');
        expect($response->status())->toBe(401);
    });

    it('can get home stats when authenticated', function () {
        $user = $this->createUserWithCompleteProfile();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/auth/home-stats');
        expect($response->status())->toBe(200);
        expect($response->json('status'))->toBe('success');
    });
});