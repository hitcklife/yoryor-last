<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Dashboard\MainDashboard;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads_for_authenticated_user()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_dashboard_redirects_guest_to_login()
    {
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
    }

    public function test_dashboard_component_renders()
    {
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(MainDashboard::class)
            ->assertSee('Dashboard')
            ->assertSee($user->name);
    }

    public function test_dashboard_shows_user_data()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        Livewire::actingAs($user)
            ->test(MainDashboard::class)
            ->assertSee('John Doe')
            ->assertSee('john@example.com');
    }
}
