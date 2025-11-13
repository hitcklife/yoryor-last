# Authentication and Authorization - Consolidated Migrations

This document contains consolidated migration files for authentication, roles, and permissions system.

## 1. Roles Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();

            // Index for name lookups
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
```

## 2. Permissions Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();

            // Index for name lookups
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
```

## 3. Role-User Pivot Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Composite primary key
            $table->primary(['role_id', 'user_id']);

            // Indexes for reverse lookups
            $table->index('user_id');
            $table->index('role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
```

## 4. Permission-Role Pivot Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');

            // Composite primary key
            $table->primary(['permission_id', 'role_id']);

            // Indexes for reverse lookups
            $table->index('role_id');
            $table->index('permission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};
```

## 5. Permission-User Pivot Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_user', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Composite primary key
            $table->primary(['permission_id', 'user_id']);

            // Indexes for reverse lookups
            $table->index('user_id');
            $table->index('permission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
    }
};
```

## 6. Telescope Tables Migration (Development Monitoring)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main entries table
        Schema::create('telescope_entries', function (Blueprint $table) {
            $table->bigIncrements('sequence');
            $table->uuid('uuid');
            $table->uuid('batch_id');
            $table->string('family_hash')->nullable();
            $table->boolean('should_display_on_index')->default(true);
            $table->string('type', 20);
            $table->longText('content');
            $table->dateTime('created_at')->nullable();

            $table->unique('uuid');
            $table->index('batch_id');
            $table->index('family_hash');
            $table->index('created_at');
            $table->index(['type', 'should_display_on_index']);
        });

        // Tags for entries
        Schema::create('telescope_entries_tags', function (Blueprint $table) {
            $table->uuid('entry_uuid');
            $table->string('tag');

            $table->primary(['entry_uuid', 'tag']);
            $table->index('tag');

            $table->foreign('entry_uuid')
                  ->references('uuid')
                  ->on('telescope_entries')
                  ->onDelete('cascade');
        });

        // Monitoring records
        Schema::create('telescope_monitoring', function (Blueprint $table) {
            $table->string('tag')->primary();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telescope_monitoring');
        Schema::dropIfExists('telescope_entries_tags');
        Schema::dropIfExists('telescope_entries');
    }
};
```

## 7. Pulse Tables Migration (Performance Monitoring)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Values storage
        Schema::create('pulse_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('timestamp');
            $table->string('type');
            $table->mediumText('key');
            $table->mediumText('key_hash')->virtualAs('md5(`key`)')->nullable();
            $table->mediumText('value');

            $table->index('timestamp');
            $table->index('type');
            $table->index('key_hash');
            $table->unique(['type', 'key_hash']);
        });

        // Entries storage
        Schema::create('pulse_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('timestamp');
            $table->string('type');
            $table->mediumText('key');
            $table->mediumText('key_hash')->virtualAs('md5(`key`)')->nullable();
            $table->bigInteger('value')->nullable();

            $table->index(['timestamp', 'type', 'key_hash', 'value']);
            $table->index('timestamp');
            $table->index('type');
            $table->index('key_hash');
        });

        // Aggregates storage
        Schema::create('pulse_aggregates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('period');
            $table->string('type');
            $table->mediumText('key');
            $table->mediumText('key_hash')->virtualAs('md5(`key`)')->nullable();
            $table->string('aggregate');
            $table->decimal('value', 20, 2);
            $table->unsignedInteger('count')->nullable();

            $table->unique(['period', 'type', 'aggregate', 'key_hash']);
            $table->index('period');
            $table->index('type');
            $table->index(['period', 'aggregate']);
            $table->index('key_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pulse_aggregates');
        Schema::dropIfExists('pulse_entries');
        Schema::dropIfExists('pulse_values');
    }
};
```

## Default Roles and Permissions Seeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = [
            ['name' => 'super-admin', 'description' => 'Full system access'],
            ['name' => 'admin', 'description' => 'Administrative access'],
            ['name' => 'moderator', 'description' => 'Content moderation access'],
            ['name' => 'support', 'description' => 'Customer support access'],
            ['name' => 'matchmaker', 'description' => 'Professional matchmaker'],
            ['name' => 'verified-user', 'description' => 'Verified user account'],
            ['name' => 'premium-user', 'description' => 'Premium subscription user'],
            ['name' => 'user', 'description' => 'Regular user account'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert(array_merge($role, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create permissions
        $permissions = [
            // User Management
            ['name' => 'users.view', 'description' => 'View user profiles'],
            ['name' => 'users.edit', 'description' => 'Edit user profiles'],
            ['name' => 'users.delete', 'description' => 'Delete user accounts'],
            ['name' => 'users.ban', 'description' => 'Ban/unban users'],

            // Content Moderation
            ['name' => 'content.moderate', 'description' => 'Moderate user content'],
            ['name' => 'photos.approve', 'description' => 'Approve/reject photos'],
            ['name' => 'reports.handle', 'description' => 'Handle user reports'],

            // Matchmaker Features
            ['name' => 'matchmaker.dashboard', 'description' => 'Access matchmaker dashboard'],
            ['name' => 'matchmaker.clients', 'description' => 'Manage matchmaker clients'],
            ['name' => 'matchmaker.introductions', 'description' => 'Make introductions'],

            // Admin Features
            ['name' => 'admin.dashboard', 'description' => 'Access admin dashboard'],
            ['name' => 'admin.analytics', 'description' => 'View system analytics'],
            ['name' => 'admin.settings', 'description' => 'Manage system settings'],

            // Support Features
            ['name' => 'support.tickets', 'description' => 'Handle support tickets'],
            ['name' => 'support.chat', 'description' => 'Access support chat'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert(array_merge($permission, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Assign permissions to roles
        $rolePermissions = [
            'super-admin' => '*', // All permissions
            'admin' => [
                'users.view', 'users.edit', 'users.ban',
                'content.moderate', 'photos.approve', 'reports.handle',
                'admin.dashboard', 'admin.analytics', 'admin.settings'
            ],
            'moderator' => [
                'users.view', 'content.moderate', 'photos.approve', 'reports.handle'
            ],
            'support' => [
                'users.view', 'support.tickets', 'support.chat'
            ],
            'matchmaker' => [
                'matchmaker.dashboard', 'matchmaker.clients', 'matchmaker.introductions'
            ],
        ];

        // Insert role-permission relationships
        foreach ($rolePermissions as $roleName => $perms) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');

            if ($perms === '*') {
                $permissionIds = DB::table('permissions')->pluck('id');
            } else {
                $permissionIds = DB::table('permissions')
                    ->whereIn('name', $perms)
                    ->pluck('id');
            }

            foreach ($permissionIds as $permissionId) {
                DB::table('permission_role')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }
}
```

## Migration Dependencies

These tables should be created in this order:

1. roles
2. permissions
3. role_user (depends on roles and users)
4. permission_role (depends on permissions and roles)
5. permission_user (depends on permissions and users)
6. telescope tables (optional, for development)
7. pulse tables (optional, for monitoring)

## Usage Notes

1. **Role Hierarchy**: The system supports multiple roles per user
2. **Direct Permissions**: Users can have permissions assigned directly, bypassing roles
3. **Permission Checking**: Check permissions through middleware or gates
4. **Telescope**: Only enable in development/staging environments
5. **Pulse**: Use for production performance monitoring