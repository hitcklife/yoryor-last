# Chat and Messaging System - Consolidated Migrations

This document contains consolidated migration files for the chat and messaging system.

## 1. Chats Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();

            // Chat properties
            $table->string('type')->default('private');
            $table->string('name')->nullable();
            $table->string('description')->nullable();

            // Activity tracking
            $table->timestamp('last_activity_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('message_count')->default(0);
            $table->string('last_message_type')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Performance indexes
            $table->index(['type', 'is_active']);
            $table->index('last_activity_at');
            $table->index(['is_active', 'last_activity_at']);
            $table->index(['type', 'is_active', 'last_activity_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
```

## 2. Chat Users Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // User settings
            $table->boolean('is_muted')->default(false);
            $table->timestamp('last_read_at')->nullable();

            // Participation tracking
            $table->timestamp('joined_at')->default(now());
            $table->timestamp('left_at')->nullable();
            $table->string('role')->default('member');

            $table->timestamps();

            // Unique constraint
            $table->unique(['chat_id', 'user_id']);

            // Performance indexes
            $table->index(['user_id', 'is_muted']);
            $table->index(['chat_id', 'left_at']);
            $table->index(['user_id', 'left_at', 'last_read_at']);
            $table->index(['chat_id', 'left_at', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_users');
    }
};
```

## 3. Messages Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');

            // Reply functionality
            $table->foreignId('reply_to_message_id')
                  ->nullable()
                  ->constrained('messages')
                  ->onDelete('set null');

            // Call integration
            $table->foreignId('call_id')
                  ->nullable()
                  ->constrained('calls')
                  ->onDelete('set null');

            // Message content
            $table->text('content')->nullable();
            $table->string('message_type')->default('text');

            // Media support
            $table->json('media_data')->nullable();
            $table->string('media_url')->nullable();
            $table->string('thumbnail_url')->nullable();

            // Status tracking
            $table->enum('status', ['sent', 'delivered', 'failed'])->default('sent');
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('sent_at')->default(now());

            $table->softDeletes();
            $table->timestamps();

            // Performance indexes
            $table->index(['chat_id', 'sent_at']);
            $table->index(['sender_id', 'sent_at']);
            $table->index(['chat_id', 'message_type']);
            $table->index(['chat_id', 'deleted_at', 'sent_at']);
            $table->index(['call_id', 'sent_at']);
            $table->index(['sender_id', 'created_at']);
            $table->index(['message_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
```

## 4. Message Reads Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('read_at')->default(now());

            // Unique constraint
            $table->unique(['message_id', 'user_id']);

            // Performance indexes
            $table->index(['user_id', 'read_at']);
            $table->index(['message_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_reads');
    }
};
```

## 5. Calls Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->string('channel_name')->unique();

            // Call participants
            $table->foreignId('caller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');

            // Call details
            $table->enum('type', ['video', 'voice'])->default('video');
            $table->enum('status', [
                'initiated',
                'ongoing',
                'completed',
                'missed',
                'rejected'
            ])->default('initiated');

            // Timing
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['caller_id', 'created_at']);
            $table->index(['receiver_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
```

## 6. Media Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');

            // Media details
            $table->string('media_url');
            $table->enum('media_type', ['photo', 'video', 'voice', 'other']);

            $table->timestamp('uploaded_at')->default(now());
            $table->timestamps();

            // Indexes
            $table->index(['message_id', 'media_type']);
            $table->index('uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
```

## 7. Notifications Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index('read_at');
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
```

## Performance Considerations

These consolidated migrations include extensive indexing for optimal performance:

1. **Chats Table**: Indexed for active chat queries and last activity sorting
2. **Chat Users**: Composite indexes for user-chat relationships and participation tracking
3. **Messages**: Multiple indexes for chat history, sender queries, and type filtering
4. **Message Reads**: Indexes for read receipt tracking
5. **Calls**: Indexes for call history and status filtering
6. **Media**: Indexes for media content retrieval

## Migration Order

These tables should be created in this order due to foreign key dependencies:

1. Chats
2. Calls (referenced by Messages)
3. Messages
4. Chat Users
5. Message Reads
6. Media
7. Notifications