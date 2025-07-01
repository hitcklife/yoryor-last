# Database Optimization Details for YorYor Application

This document provides detailed recommendations for optimizing the database structure and queries in the YorYor dating application, based on analysis of the existing migrations and controllers.

## Migration-Specific Improvements

### Users Table

Current migration (`2023_01_01_000000_create_users_table.php`):
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email', 255)->unique()->nullable();
    $table->string('phone', 20)->unique()->nullable();
    $table->string('google_id', 100)->nullable()->index();
    $table->string('facebook_id', 100)->nullable()->index();
    $table->timestamp('email_verified_at')->nullable();
    $table->timestamp('phone_verified_at')->nullable();
    $table->timestamp('disabled_at')->nullable()->index();
    $table->boolean('registration_completed')->default(false);
    $table->boolean('is_admin')->default(false);
    $table->boolean('is_private')->default(false);
    $table->string('password', 60)->nullable();
    $table->rememberToken();
    $table->string('profile_photo_path', 1024)->nullable();
    $table->timestamp('last_active_at')->nullable(); // New field for activity tracking
    $table->softDeletes();
    $table->timestamps();

    // Composite indexes for dating app queries
    $table->index(['registration_completed', 'disabled_at', 'is_private'], 'users_active_profiles_index');
    $table->index(['last_active_at', 'registration_completed'], 'users_activity_index');
    $table->index('created_at'); // For new user queries
});
```

Recommendations:
1. Add index on `last_active_at` for better performance on activity-based queries
2. Consider using `tinyInteger` instead of `boolean` for boolean fields
3. Add a separate index for `is_admin` if admin filtering is common

### Profiles Table

Current migration (`2023_01_02_000001_create_profiles_table.php`):
```php
Schema::create('profiles', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id')->unique();
    $table->string('first_name', 50)->nullable();
    $table->string('last_name', 50)->nullable();
    $table->enum('gender', ['male', 'female', 'non-binary', 'other'])->nullable();
    $table->date('date_of_birth')->nullable();
    $table->unsignedTinyInteger('age')->nullable(); // Computed age for faster queries
    $table->string('city', 85)->nullable();
    $table->string('state', 50)->nullable();
    $table->string('province', 50)->nullable();
    $table->unsignedInteger('country_id')->nullable();
    $table->decimal('latitude', 10, 7)->nullable();
    $table->decimal('longitude', 10, 7)->nullable();
    $table->text('bio')->nullable(); // Dating app bio
    $table->json('interests')->nullable(); // User interests as JSON
    $table->enum('looking_for', ['casual', 'serious', 'friendship', 'all'])->default('all');
    $table->unsignedInteger('profile_views')->default(0); // Track profile views
    $table->timestamp('profile_completed_at')->nullable(); // When profile was completed

    $table->string('status', 50)->nullable();
    $table->string('occupation', 100)->nullable();
    $table->string('profession', 100)->nullable();
    $table->string('country_code', 10)->nullable();

    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');

    // Optimized indexes for matching algorithms
    $table->index(['gender', 'age', 'city'], 'profiles_matching_index');
    $table->index(['latitude', 'longitude', 'gender'], 'profiles_location_index');
    $table->index(['looking_for', 'age'], 'profiles_intent_index');
    $table->index('profile_completed_at');
    $table->index('profile_views'); // For popularity sorting

    $table->timestamps();
});
```

Recommendations:
1. Add a full-text search index on `bio` if text searching is performed
2. Consider adding a composite index on `[country_id, city]` for location-based filtering
3. Optimize the `interests` JSON column by ensuring it stores only necessary data
4. Consider adding a computed column for full name to avoid concatenation in queries

### Messages Table

Recommendations for the messages table:
1. Add a composite index on `[chat_id, created_at]` for efficient message retrieval
2. Consider partitioning by date ranges if the table grows very large
3. Add a `read_at` timestamp to track when messages are read (if not already present)
4. Consider adding a `deleted_at` timestamp for soft deletes

### Likes and Dislikes Tables

Recommendations:
1. Ensure foreign key constraints are properly defined
2. Add composite indexes for efficient querying
3. Consider adding timestamps for when likes/dislikes were created

## Query Optimization Examples

### Potential N+1 Query Issues in MatchController

Current code in `getPotentialMatches`:
```php
$potentialMatches = $query->with([
    'profile',
    'photos',
    'profilePhoto'
])->paginate($perPage);
```

This is good as it uses eager loading, but could be optimized further:

```php
$potentialMatches = $query->with([
    'profile' => function($query) {
        $query->select('id', 'user_id', 'first_name', 'last_name', 'age', 'gender', 'city', 'bio');
    },
    'photos' => function($query) {
        $query->select('id', 'user_id', 'path', 'order')->orderBy('order');
    },
    'profilePhoto'
])->paginate($perPage);
```

### Caching Strategy Improvements

Current caching in `getMatches`:
```php
$cacheKey = "user_{$user->id}_matches_page_{$request->input('page', 1)}_per_{$perPage}_mutual_{$mutualOnly}";

return \Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user, $perPage, $mutualOnly, $request) {
    // Query logic
});
```

Improved caching with tags:
```php
$cacheKey = "user_{$user->id}_matches_page_{$request->input('page', 1)}_per_{$perPage}_mutual_{$mutualOnly}";

return \Cache::tags(['user-matches', "user-{$user->id}"])->remember($cacheKey, now()->addMinutes(5), function () use ($user, $perPage, $mutualOnly, $request) {
    // Query logic
});
```

Then when a match is created or deleted:
```php
\Cache::tags(["user-{$user->id}", "user-{$matchedUserId}"])->flush();
```

## Database-Level Optimizations

1. **Implement Database Views**

Example for a frequently used complex query:
```sql
CREATE VIEW active_users_view AS
SELECT u.id, u.email, u.phone, p.first_name, p.last_name, p.age, p.gender, p.city
FROM users u
JOIN profiles p ON u.id = p.user_id
WHERE u.registration_completed = 1
  AND u.disabled_at IS NULL
  AND u.is_private = 0;
```

2. **Consider Materialized Views for Aggregations**

For statistics or reports that are expensive to calculate:
```sql
CREATE MATERIALIZED VIEW user_stats_view AS
SELECT 
    u.id,
    COUNT(DISTINCT l1.id) as likes_sent,
    COUNT(DISTINCT l2.id) as likes_received,
    COUNT(DISTINCT m.id) as matches,
    COUNT(DISTINCT msg.id) as messages_sent
FROM users u
LEFT JOIN likes l1 ON u.id = l1.user_id
LEFT JOIN likes l2 ON u.id = l2.liked_user_id
LEFT JOIN matches m ON u.id = m.user_id
LEFT JOIN messages msg ON u.id = msg.sender_id
GROUP BY u.id;
```

3. **Implement Connection Pooling**

In your Laravel configuration:
```php
// config/database.php
'mysql' => [
    // ...
    'pool' => [
        'min' => 5,
        'max' => 20,
    ],
],
```

## Implementation Plan

1. **Phase 1: Index Optimization**
   - Add missing indexes to existing tables
   - Optimize existing indexes based on query patterns
   - Measure performance improvements

2. **Phase 2: Query Optimization**
   - Refactor N+1 queries in controllers
   - Implement selective column loading
   - Optimize JOIN operations

3. **Phase 3: Advanced Optimizations**
   - Implement database views for complex queries
   - Set up table partitioning for large tables
   - Configure connection pooling

Each phase should include performance testing to measure the impact of changes.
