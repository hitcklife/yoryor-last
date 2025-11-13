<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create basic roles
        $adminRole = Role::firstOrCreate([
            'name' => 'admin'
        ], [
            'description' => 'Administrator with full system access'
        ]);

        $userRole = Role::firstOrCreate([
            'name' => 'user'
        ], [
            'description' => 'Regular user'
        ]);

        $matchmakerRole = Role::firstOrCreate([
            'name' => 'matchmaker'
        ], [
            'description' => 'Matchmaker with special privileges'
        ]);

        // Create basic permissions
        $permissions = [
            // Admin permissions
            ['name' => 'manage_users', 'description' => 'Manage user accounts'],
            ['name' => 'manage_matches', 'description' => 'Manage user matches'],
            ['name' => 'manage_chats', 'description' => 'Manage user chats'],
            ['name' => 'manage_reports', 'description' => 'Manage user reports'],
            ['name' => 'manage_verification', 'description' => 'Manage user verifications'],
            ['name' => 'view_analytics', 'description' => 'View system analytics'],
            ['name' => 'manage_settings', 'description' => 'Manage system settings'],
            
            // User permissions (basic functionality)
            ['name' => 'create_profile', 'description' => 'Create and edit profile'],
            ['name' => 'send_messages', 'description' => 'Send messages'],
            ['name' => 'like_users', 'description' => 'Like other users'],
            ['name' => 'create_stories', 'description' => 'Create stories'],
            ['name' => 'make_calls', 'description' => 'Make video/voice calls'],
            
            // Matchmaker permissions
            ['name' => 'manage_clients', 'description' => 'Manage matchmaker clients'],
            ['name' => 'create_introductions', 'description' => 'Create introductions'],
            ['name' => 'view_matchmaker_analytics', 'description' => 'View matchmaker analytics'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate([
                'name' => $permissionData['name']
            ], [
                'description' => $permissionData['description']
            ]);
        }

        // Assign permissions to roles
        
        // Admin gets all permissions
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        // User gets basic permissions
        $userPermissions = Permission::whereIn('name', [
            'create_profile',
            'send_messages', 
            'like_users',
            'create_stories',
            'make_calls'
        ])->get();
        $userRole->permissions()->sync($userPermissions->pluck('id'));

        // Matchmaker gets user permissions plus matchmaker-specific ones
        $matchmakerPermissions = Permission::whereIn('name', [
            'create_profile',
            'send_messages',
            'like_users',
            'create_stories',
            'make_calls',
            'manage_clients',
            'create_introductions',
            'view_matchmaker_analytics'
        ])->get();
        $matchmakerRole->permissions()->sync($matchmakerPermissions->pluck('id'));

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
