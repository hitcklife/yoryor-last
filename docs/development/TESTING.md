# YorYor Testing Guide

## Table of Contents
- [Overview](#overview)
- [Testing Framework](#testing-framework)
- [Running Tests](#running-tests)
- [Test Structure](#test-structure)
- [Writing Feature Tests](#writing-feature-tests)
- [Writing Unit Tests](#writing-unit-tests)
- [Testing Livewire Components](#testing-livewire-components)
- [Testing API Endpoints](#testing-api-endpoints)
- [Database Testing](#database-testing)
- [Mocking & Faking](#mocking--faking)
- [Code Coverage](#code-coverage)
- [Code Formatting](#code-formatting)
- [Best Practices](#best-practices)

---

## Overview

YorYor uses **Pest PHP** as its testing framework. Pest provides an elegant syntax for writing tests and is built on top of PHPUnit.

**Why Pest?**
- Elegant, expressive syntax
- Better test organization
- Built-in expectations
- Laravel integration
- Active development

**Testing Philosophy:**
- Write tests for all new features
- Maintain high code coverage (aim for 80%+)
- Test business logic thoroughly
- Keep tests fast and isolated
- Use factories for test data

---

## Testing Framework

### Pest PHP 3.8

**Installation:**
Pest is already included in YorYor's dependencies.

**Configuration:**
- **Pest Config:** `tests/Pest.php`
- **PHPUnit Config:** `phpunit.xml`

```php
// tests/Pest.php
uses(TestCase::class)->in('Feature');
uses(TestCase::class)->in('Unit');
```

### Test Database

Tests use an in-memory SQLite database by default:

```xml
<!-- phpunit.xml -->
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

---

## Running Tests

### Basic Commands

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthTest.php

# Run specific test by name
php artisan test --filter test_user_can_register

# Run tests with coverage
php artisan test --coverage

# Run tests with minimum coverage threshold
php artisan test --coverage --min=80

# Run tests in parallel (faster)
php artisan test --parallel

# Stop on first failure
php artisan test --stop-on-failure

# Show detailed output
php artisan test --verbose
```

### Using Pest Directly

```bash
# Run with Pest binary
./vendor/bin/pest

# Run specific file
./vendor/bin/pest tests/Feature/AuthTest.php

# Run with coverage
./vendor/bin/pest --coverage

# Run in parallel
./vendor/bin/pest --parallel
```

### Continuous Testing

Watch for file changes and auto-run tests:

```bash
./vendor/bin/pest --watch
```

---

## Test Structure

### Directory Structure

```
tests/
├── Feature/                      # Feature/integration tests
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   ├── RegisterTest.php
│   │   └── TwoFactorTest.php
│   ├── Api/
│   │   ├── ChatTest.php
│   │   ├── MatchTest.php
│   │   └── ProfileTest.php
│   ├── Livewire/
│   │   ├── SwipeCardsTest.php
│   │   └── ProfilePageTest.php
│   └── Services/
│       ├── AuthServiceTest.php
│       └── MediaUploadServiceTest.php
├── Unit/                         # Unit tests
│   ├── Models/
│   │   ├── UserTest.php
│   │   └── ProfileTest.php
│   ├── Services/
│   │   └── OtpServiceTest.php
│   └── Helpers/
│       └── HelperTest.php
├── Pest.php                      # Pest configuration
└── TestCase.php                  # Base test case
```

### Test File Naming

- Test files must end with `Test.php`
- Name after the class/feature being tested
- Example: `UserController.php` → `UserControllerTest.php`

---

## Writing Feature Tests

Feature tests test complete workflows through HTTP requests.

### Authentication Test Example

```php
<?php
// tests/Feature/Auth/RegisterTest.php

use App\Models\User;

test('user can register with valid data', function () {
    $response = $this->post('/register', [
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'country_id' => 1,
    ]);

    $response->assertRedirect('/discover');

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not()->toBeNull();
    expect($user->profile)->not()->toBeNull();
});

test('user cannot register with invalid email', function () {
    $response = $this->post('/register', [
        'email' => 'invalid-email',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('user cannot register with existing email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->post('/register', [
        'email' => 'existing@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});
```

### API Test Example

```php
<?php
// tests/Feature/Api/MatchTest.php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('user can like another user', function () {
    $user = User::factory()->has(Profile::factory())->create();
    $targetUser = User::factory()->has(Profile::factory())->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/likes', [
        'liked_user_id' => $targetUser->id,
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('likes', [
        'user_id' => $user->id,
        'liked_user_id' => $targetUser->id,
    ]);
});

test('mutual likes create a match', function () {
    $userA = User::factory()->has(Profile::factory())->create();
    $userB = User::factory()->has(Profile::factory())->create();

    // User A likes User B
    $userA->sentLikes()->create(['liked_user_id' => $userB->id]);

    // User B likes User A (should create match)
    Sanctum::actingAs($userB);

    $response = $this->postJson('/api/v1/likes', [
        'liked_user_id' => $userA->id,
    ]);

    $response->assertStatus(201);

    // Check match was created
    $this->assertDatabaseHas('matches', [
        'user_id' => $userA->id,
        'matched_user_id' => $userB->id,
    ]);

    $this->assertDatabaseHas('matches', [
        'user_id' => $userB->id,
        'matched_user_id' => $userA->id,
    ]);
});

test('unauthenticated user cannot like', function () {
    $targetUser = User::factory()->create();

    $response = $this->postJson('/api/v1/likes', [
        'liked_user_id' => $targetUser->id,
    ]);

    $response->assertStatus(401);
});
```

---

## Writing Unit Tests

Unit tests test individual classes/methods in isolation.

### Model Test Example

```php
<?php
// tests/Unit/Models/UserTest.php

use App\Models\User;
use App\Models\Profile;

test('user has profile relationship', function () {
    $user = User::factory()->has(Profile::factory())->create();

    expect($user->profile)->toBeInstanceOf(Profile::class);
    expect($user->profile->user_id)->toBe($user->id);
});

test('user can check if online', function () {
    $user = User::factory()->create([
        'last_active_at' => now()->subMinutes(3),
    ]);

    expect($user->isOnline())->toBeTrue();

    $user->update(['last_active_at' => now()->subMinutes(10)]);

    expect($user->isOnline())->toBeFalse();
});

test('user age is calculated correctly', function () {
    $user = User::factory()->has(
        Profile::factory()->state(['date_of_birth' => '1990-01-01'])
    )->create();

    $expectedAge = now()->year - 1990;

    expect($user->profile->age)->toBe($expectedAge);
});
```

### Service Test Example

```php
<?php
// tests/Unit/Services/AuthServiceTest.php

use App\Services\AuthService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('auth service can authenticate valid user', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $authService = app(AuthService::class);

    $result = $authService->authenticate([
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    expect($result['user'])->toBeInstanceOf(User::class);
    expect($result['token'])->toBeString();
});

test('auth service throws exception for invalid credentials', function () {
    $authService = app(AuthService::class);

    $authService->authenticate([
        'email' => 'nonexistent@example.com',
        'password' => 'wrong',
    ]);
})->throws(AuthenticationException::class);

test('auth service creates user with all related data', function () {
    $authService = app(AuthService::class);

    $result = $authService->register([
        'email' => 'new@example.com',
        'password' => 'password123',
        'first_name' => 'John',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'country_id' => 1,
    ]);

    expect($result['user'])->toBeInstanceOf(User::class);
    expect($result['user']->profile)->not()->toBeNull();
    expect($result['user']->setting)->not()->toBeNull();
    expect($result['user']->preference)->not()->toBeNull();
});
```

---

## Testing Livewire Components

### Basic Livewire Test

```php
<?php
// tests/Feature/Livewire/SwipeCardsTest.php

use App\Livewire\Dashboard\SwipeCards;
use App\Models\User;
use Livewire\Livewire;

test('swipe cards component renders', function () {
    $user = User::factory()->has(Profile::factory())->create();

    $this->actingAs($user);

    Livewire::test(SwipeCards::class)
        ->assertStatus(200)
        ->assertSee('Discovery');
});

test('user can like profile from swipe cards', function () {
    $user = User::factory()->has(Profile::factory())->create();
    $targetUser = User::factory()->has(Profile::factory())->create();

    $this->actingAs($user);

    Livewire::test(SwipeCards::class)
        ->call('like', $targetUser->id)
        ->assertDispatched('profile-liked');

    $this->assertDatabaseHas('likes', [
        'user_id' => $user->id,
        'liked_user_id' => $targetUser->id,
    ]);
});

test('swipe cards updates current index after action', function () {
    $user = User::factory()->has(Profile::factory())->create();
    $targetUser = User::factory()->has(Profile::factory())->create();

    $this->actingAs($user);

    Livewire::test(SwipeCards::class)
        ->assertSet('currentIndex', 0)
        ->call('like', $targetUser->id)
        ->assertSet('currentIndex', 1);
});
```

### Testing Livewire Forms

```php
<?php
// tests/Feature/Livewire/ProfileBasicInfoTest.php

use App\Livewire\Profile\BasicInfo;
use App\Models\User;

test('user can update basic info', function () {
    $user = User::factory()->has(Profile::factory())->create();

    $this->actingAs($user);

    Livewire::test(BasicInfo::class)
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->set('bio', 'Updated bio')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('profile-updated');

    $this->assertDatabaseHas('profiles', [
        'user_id' => $user->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'bio' => 'Updated bio',
    ]);
});

test('validation errors are displayed', function () {
    $user = User::factory()->has(Profile::factory())->create();

    $this->actingAs($user);

    Livewire::test(BasicInfo::class)
        ->set('first_name', '')  // Required field
        ->call('save')
        ->assertHasErrors(['first_name' => 'required']);
});
```

---

## Testing API Endpoints

### REST API Test

```php
<?php
// tests/Feature/Api/ChatTest.php

use App\Models\User;
use App\Models\Chat;
use Laravel\Sanctum\Sanctum;

test('user can send message in chat', function () {
    $user = User::factory()->create();
    $chat = Chat::factory()->create();
    $chat->users()->attach($user);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/chats/{$chat->id}/messages", [
        'content' => 'Hello, world!',
        'type' => 'text',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'type',
                'attributes' => [
                    'content',
                    'sender_id',
                    'sent_at',
                ],
            ],
        ]);

    $this->assertDatabaseHas('messages', [
        'chat_id' => $chat->id,
        'sender_id' => $user->id,
        'content' => 'Hello, world!',
    ]);
});

test('user cannot send message to chat they are not in', function () {
    $user = User::factory()->create();
    $chat = Chat::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/chats/{$chat->id}/messages", [
        'content' => 'Hello',
    ]);

    $response->assertStatus(403);
});

test('rate limiting is applied to message sending', function () {
    $user = User::factory()->create();
    $chat = Chat::factory()->create();
    $chat->users()->attach($user);

    Sanctum::actingAs($user);

    // Send 501 messages (limit is 500/hour)
    for ($i = 0; $i < 501; $i++) {
        $response = $this->postJson("/api/v1/chats/{$chat->id}/messages", [
            'content' => "Message {$i}",
        ]);

        if ($i < 500) {
            $response->assertStatus(201);
        } else {
            $response->assertStatus(429); // Too Many Requests
        }
    }
});
```

### JSON:API Format Test

```php
test('user resource returns correct json api format', function () {
    $user = User::factory()->has(Profile::factory())->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/profile');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'type' => 'users',
                'id' => (string) $user->id,
                'attributes' => [
                    'email' => $user->email,
                ],
            ],
        ]);
});
```

---

## Database Testing

### Using Factories

```php
// Create single model
$user = User::factory()->create();

// Create with specific attributes
$user = User::factory()->create([
    'email' => 'specific@example.com',
]);

// Create multiple models
$users = User::factory()->count(10)->create();

// Create with relationships
$user = User::factory()
    ->has(Profile::factory())
    ->has(UserPhoto::factory()->count(3))
    ->create();

// Create using relationship
$profile = Profile::factory()
    ->for(User::factory())
    ->create();
```

### Database Assertions

```php
// Assert record exists
$this->assertDatabaseHas('users', [
    'email' => 'test@example.com',
]);

// Assert record doesn't exist
$this->assertDatabaseMissing('users', [
    'email' => 'deleted@example.com',
]);

// Assert count
$this->assertDatabaseCount('users', 5);

// Assert soft deleted
$this->assertSoftDeleted('users', [
    'id' => $user->id,
]);
```

### Database Transactions

Tests automatically run in database transactions and roll back after each test:

```php
test('user is created', function () {
    $user = User::factory()->create();

    expect(User::count())->toBe(1);
    // After test completes, database rolls back
});
```

---

## Mocking & Faking

### Faking External Services

```php
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

test('user photo is uploaded', function () {
    Storage::fake('r2');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('photo.jpg', 800, 800);

    $response = $this->actingAs($user)
        ->post('/api/v1/photos', [
            'photo' => $file,
        ]);

    $response->assertStatus(201);

    Storage::disk('r2')->assertExists("photos/{$user->id}/" . $file->hashName());
});

test('welcome email is sent', function () {
    Mail::fake();

    $this->post('/register', [
        'email' => 'test@example.com',
        // ... other fields
    ]);

    Mail::assertSent(WelcomeEmail::class, function ($mail) {
        return $mail->hasTo('test@example.com');
    });
});

test('external api is called', function () {
    Http::fake([
        'api.videosdk.live/*' => Http::response(['meetingId' => 'test-123'], 200),
    ]);

    $videoService = app(VideoSDKService::class);
    $meeting = $videoService->createMeeting();

    expect($meeting['meeting_id'])->toBe('test-123');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.videosdk.live/v2/meetings';
    });
});
```

### Mocking Services

```php
use Mockery;

test('panic button calls emergency service', function () {
    $mock = Mockery::mock(PanicButtonService::class);
    $mock->shouldReceive('activatePanic')
        ->once()
        ->with(Mockery::type(User::class), Mockery::type('array'))
        ->andReturn(new PanicActivation());

    $this->app->instance(PanicButtonService::class, $mock);

    // Test code that uses PanicButtonService
});
```

### Faking Events

```php
use Illuminate\Support\Facades\Event;

test('new match event is fired', function () {
    Event::fake([NewMatchEvent::class]);

    $userA = User::factory()->create();
    $userB = User::factory()->create();

    // Create match logic

    Event::assertDispatched(NewMatchEvent::class, function ($event) use ($userA, $userB) {
        return $event->match->user_id === $userA->id;
    });
});
```

---

## Code Coverage

### Running Coverage

```bash
# Basic coverage report
php artisan test --coverage

# Coverage with minimum threshold
php artisan test --coverage --min=80

# HTML coverage report
php artisan test --coverage-html coverage

# Open HTML report
open coverage/index.html
```

### Coverage Output

```
  PASS  Tests\Feature\Auth\LoginTest
  ✓ user can login with valid credentials
  ✓ user cannot login with invalid credentials

  PASS  Tests\Feature\Auth\RegisterTest
  ✓ user can register with valid data

  Tests:  3 passed
  Time:   0.45s

  Code Coverage
    App\Services\AuthService    95%
    App\Models\User            100%
```

### Coverage Configuration

```xml
<!-- phpunit.xml -->
<coverage processUncoveredFiles="true">
    <include>
        <directory suffix=".php">./app</directory>
    </include>
    <exclude>
        <directory>./app/Console</directory>
        <directory>./app/Exceptions</directory>
    </exclude>
</coverage>
```

---

## Code Formatting

### Laravel Pint

YorYor uses Laravel Pint for code formatting (PSR-12 standard).

```bash
# Format all files
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test

# Format specific directory
./vendor/bin/pint app/Services

# Format specific file
./vendor/bin/pint app/Services/AuthService.php
```

### Pint Configuration

```json
// pint.json
{
    "preset": "laravel",
    "rules": {
        "simplified_null_return": true,
        "braces": false,
        "new_with_braces": true
    }
}
```

### Pre-commit Hook

Add to `.git/hooks/pre-commit`:

```bash
#!/bin/sh
./vendor/bin/pint --test
```

---

## Best Practices

### 1. Test Organization

```php
// Group related tests with describe
describe('User Authentication', function () {
    test('user can login')->todo();
    test('user can logout')->todo();
    test('user can reset password')->todo();
});
```

### 2. Use Factories

```php
// Good - Using factories
$user = User::factory()->create();

// Bad - Manual creation
$user = User::create([
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    // ... many fields
]);
```

### 3. Test One Thing

```php
// Good - Tests one specific behavior
test('user can like another user', function () {
    // Single assertion about liking
});

// Bad - Tests multiple behaviors
test('user interactions', function () {
    // Tests liking, matching, messaging all together
});
```

### 4. Clear Test Names

```php
// Good - Clear what is being tested
test('user cannot register with existing email', function () {});

// Bad - Unclear test name
test('register fails', function () {});
```

### 5. Arrange, Act, Assert

```php
test('user can send message', function () {
    // Arrange - Set up test data
    $user = User::factory()->create();
    $chat = Chat::factory()->create();

    // Act - Perform the action
    $response = $this->actingAs($user)
        ->post("/chats/{$chat->id}/messages", [
            'content' => 'Hello',
        ]);

    // Assert - Verify the outcome
    $response->assertStatus(201);
    $this->assertDatabaseHas('messages', [
        'content' => 'Hello',
    ]);
});
```

### 6. Use Expectations

```php
// Good - Pest expectations
expect($user->isActive())->toBeTrue();
expect($user->age)->toBe(25);

// Also good - Traditional assertions
$this->assertTrue($user->isActive());
$this->assertEquals(25, $user->age);
```

### 7. Test Happy and Sad Paths

```php
// Happy path
test('user can register with valid data', function () {});

// Sad paths
test('user cannot register without email', function () {});
test('user cannot register with invalid email', function () {});
test('user cannot register with existing email', function () {});
```

### 8. Keep Tests Fast

```php
// Good - Use in-memory database
// Bad - Use actual MySQL connection

// Good - Fake external services
Mail::fake();
Storage::fake();

// Bad - Actually send emails or upload files
```

### 9. Isolate Tests

```php
// Each test should be independent
test('first test', function () {
    $user = User::factory()->create();
    // Test doesn't rely on other tests
});

test('second test', function () {
    $user = User::factory()->create();
    // Creates its own data
});
```

---

## Common Testing Patterns

### Testing Authentication

```php
// Acting as authenticated user
$this->actingAs($user);

// Using Sanctum for API
Sanctum::actingAs($user);

// Testing unauthenticated access
$response = $this->get('/dashboard');
$response->assertRedirect('/start');
```

### Testing Validation

```php
test('validates required fields', function () {
    $response = $this->post('/register', []);

    $response->assertSessionHasErrors([
        'email',
        'password',
        'first_name',
    ]);
});
```

### Testing Events

```php
test('dispatches event', function () {
    Event::fake();

    // Trigger action

    Event::assertDispatched(UserRegistered::class);
});
```

### Testing Jobs

```php
test('dispatches job', function () {
    Queue::fake();

    // Trigger action

    Queue::assertPushed(ProcessVerificationJob::class);
});
```

---

## Continuous Integration

### GitHub Actions Example

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Install dependencies
        run: composer install

      - name: Run tests
        run: php artisan test --coverage --min=80

      - name: Check code style
        run: ./vendor/bin/pint --test
```

---

**Testing is crucial for maintaining code quality. Write tests for all new features and bug fixes!**

*Last Updated: October 2025*
