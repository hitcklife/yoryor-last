<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use App\Models\Profile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'create:admin 
                            {--email= : Admin email address}
                            {--password= : Admin password}
                            {--name= : Admin full name}
                            {--phone= : Admin phone number}';

    protected $description = 'Create an admin user for the dating app';

    public function handle()
    {
        $this->info('Creating admin user...');

        // Get input data
        $email = $this->option('email') ?: $this->ask('Email address');
        $password = $this->option('password') ?: $this->secret('Password (min 8 characters)');
        $name = $this->option('name') ?: $this->ask('Full name', 'Admin User');
        $phone = $this->option('phone') ?: $this->ask('Phone number (optional)');

        // Validate input
        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
            'name' => $name,
            'phone' => $phone,
        ], [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20|unique:users,phone',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('- ' . $error);
            }
            return 1;
        }

        try {
            // Create admin role if it doesn't exist
            $adminRole = Role::firstOrCreate([
                'name' => 'admin'
            ], [
                'description' => 'Administrator with full system access'
            ]);

            // Create user role if it doesn't exist
            Role::firstOrCreate([
                'name' => 'user'
            ], [
                'description' => 'Regular user'
            ]);

            // Create matchmaker role if it doesn't exist
            Role::firstOrCreate([
                'name' => 'matchmaker'
            ], [
                'description' => 'Matchmaker with special privileges'
            ]);

            // Split name into first and last name
            $nameParts = explode(' ', trim($name), 2);
            $firstName = $nameParts[0];
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

            // Create user
            $user = User::create([
                'email' => $email,
                'phone' => $phone ?: null,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'phone_verified_at' => $phone ? now() : null,
                'registration_completed' => true,
                'is_private' => false,
            ]);

            // Create user profile
            Profile::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => now()->subYears(30), // Default age 30
                'gender' => 'other',
                'bio' => 'System Administrator',
                'city' => 'Admin City',
                'country' => 'Admin Country',
                'status' => 'single',
                'occupation' => 'administrator',
            ]);

            // Assign admin role
            $user->roles()->attach($adminRole);

            $this->info('✅ Admin user created successfully!');
            $this->table(['Field', 'Value'], [
                ['ID', $user->id],
                ['Email', $user->email],
                ['Phone', $user->phone ?: 'Not provided'],
                ['Name', $name],
                ['Role', 'admin'],
                ['Created', $user->created_at->format('Y-m-d H:i:s')],
            ]);

            $this->info('🔐 You can now login at: ' . url('/login'));

        } catch (\Exception $e) {
            $this->error('Failed to create admin user: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
