# YorYor Development Guide

## Table of Contents
- [Getting Started](#getting-started)
- [Project Structure](#project-structure)
- [Local Development Setup](#local-development-setup)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Git Workflow](#git-workflow)
- [Common Development Tasks](#common-development-tasks)
- [Debugging](#debugging)
- [Troubleshooting](#troubleshooting)
- [Resources](#resources)

---

## Getting Started

Welcome to the YorYor development team! This guide will help you set up your local development environment and understand our development practices.

### Prerequisites

Before you begin, ensure you have:
- **Basic knowledge**: PHP, Laravel, JavaScript, MySQL
- **Git**: Version control system
- **Code editor**: VS Code, PHPStorm, or similar
- **Terminal**: Command line experience

### Team Communication
- **Slack**: Team communication
- **GitHub**: Code repository and issues
- **Notion/Confluence**: Documentation

---

## Project Structure

### Directory Overview

```
yoryor-last/
├── app/                          # Application code
│   ├── Console/                  # Artisan commands
│   │   └── Commands/             # Custom commands
│   ├── Events/                   # Event classes
│   ├── Exceptions/               # Exception handling
│   ├── Http/                     # HTTP layer
│   │   ├── Controllers/          # Controllers
│   │   │   ├── Api/              # API controllers
│   │   │   │   └── V1/           # API version 1
│   │   │   └── Web/              # Web controllers
│   │   ├── Middleware/           # Middleware
│   │   └── Resources/            # API resources
│   ├── Jobs/                     # Queue jobs
│   ├── Livewire/                 # Livewire components
│   │   ├── Auth/                 # Authentication
│   │   ├── Profile/              # Profile management
│   │   ├── Dashboard/            # Dashboard components
│   │   ├── Admin/                # Admin panel
│   │   └── Pages/                # Full-page components
│   ├── Models/                   # Eloquent models
│   ├── Notifications/            # Notification classes
│   ├── Policies/                 # Authorization policies
│   ├── Providers/                # Service providers
│   └── Services/                 # Business logic services
├── bootstrap/                    # Bootstrap files
├── config/                       # Configuration files
├── database/                     # Database files
│   ├── factories/                # Model factories
│   ├── migrations/               # Database migrations
│   └── seeders/                  # Database seeders
├── public/                       # Public assets
│   └── assets/                   # Static assets
├── resources/                    # Views and raw assets
│   ├── css/                      # Stylesheets
│   ├── js/                       # JavaScript files
│   ├── lang/                     # Language files
│   └── views/                    # Blade templates
├── routes/                       # Route definitions
│   ├── web.php                   # Web routes
│   ├── api.php                   # API routes
│   ├── channels.php              # Broadcast channels
│   └── console.php               # Console commands
├── storage/                      # Storage files
│   ├── app/                      # Application storage
│   ├── framework/                # Framework files
│   └── logs/                     # Log files
├── tests/                        # Tests
│   ├── Feature/                  # Feature tests
│   └── Unit/                     # Unit tests
├── .env.example                  # Environment template
├── composer.json                 # PHP dependencies
├── package.json                  # Node dependencies
├── phpunit.xml                   # PHPUnit configuration
└── vite.config.js                # Vite configuration
```

### Key Directories Explained

**app/Livewire/**: Full-stack reactive components
- Keep components focused and single-purpose
- Follow naming convention: `{Feature}{Action}.php`

**app/Services/**: Business logic layer
- Extract complex logic from controllers
- Make services testable and reusable

**app/Models/**: Database models
- Define relationships
- Add custom scopes and accessors
- Keep models clean

**resources/views/livewire/**: Livewire component views
- One view per component
- Use Blade components for reusability

---

## Local Development Setup

### 1. Install Prerequisites

**macOS (using Homebrew):**
```bash
# Install Homebrew if not installed
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install PHP
brew install php@8.2

# Install Composer
brew install composer

# Install Node.js
brew install node@18

# Install MySQL
brew install mysql
brew services start mysql

# Install Redis
brew install redis
brew services start redis
```

**Ubuntu/Debian:**
```bash
# Update system
sudo apt update

# Install PHP and extensions
sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-mbstring php8.2-redis

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install MySQL
sudo apt install mysql-server
sudo mysql_secure_installation

# Install Redis
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

**Windows:**
- Install [Laravel Herd](https://herd.laravel.com/) (easiest)
- Or install [XAMPP](https://www.apachefriends.org/)
- Install [Composer](https://getcomposer.org/)
- Install [Node.js](https://nodejs.org/)

### 2. Clone Repository

```bash
git clone https://github.com/yourusername/yoryor.git
cd yoryor
```

### 3. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 4. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Environment

Edit `.env` file:

```env
APP_NAME=YorYor
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# For MySQL, use:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=yoryor
# DB_USERNAME=root
# DB_PASSWORD=

BROADCAST_CONNECTION=reverb

REVERB_APP_ID=yoryor-local
REVERB_APP_KEY=yoryor-key-local
REVERB_APP_SECRET=yoryor-secret-local
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
```

### 6. Database Setup

**Using SQLite (Easiest):**
```bash
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=CountrySeeder
```

**Using MySQL:**
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE yoryor"

# Run migrations
php artisan migrate
php artisan db:seed --class=CountrySeeder
```

### 7. Create Test User (Optional)

```bash
php artisan tinker
```

```php
$user = User::factory()->create([
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
]);

$user->profile()->create([
    'date_of_birth' => '1990-01-01',
    'gender' => 'male',
    'country_id' => 1,
]);
```

### 8. Start Development Servers

**Option 1: Using Composer Script (Recommended)**
```bash
composer dev
```

This starts all services concurrently:
- Laravel development server (port 8000)
- Queue worker
- Vite dev server
- Log viewer (pail)

**Option 2: Manual Start**

Terminal 1 - Laravel Server:
```bash
php artisan serve
```

Terminal 2 - Vite Dev Server:
```bash
npm run dev
```

Terminal 3 - Queue Worker:
```bash
php artisan queue:work
```

Terminal 4 - WebSocket Server:
```bash
php artisan reverb:start
```

### 9. Access Application

- **Web**: http://localhost:8000
- **Admin**: Create admin user first (see below)

### 10. Create Admin User

```bash
php artisan make:admin
```

Or manually:
```bash
php artisan tinker
```

```php
$user = User::create([
    'name' => 'Admin',
    'email' => 'admin@yoryor.com',
    'password' => bcrypt('admin123'),
]);

$user->assignRole('admin');
```

---

## Development Workflow

### Daily Workflow

1. **Pull latest changes:**
   ```bash
   git pull origin main
   ```

2. **Update dependencies (if changed):**
   ```bash
   composer install
   npm install
   ```

3. **Run migrations (if new):**
   ```bash
   php artisan migrate
   ```

4. **Start development servers:**
   ```bash
   composer dev
   ```

5. **Create feature branch:**
   ```bash
   git checkout -b feature/your-feature-name
   ```

6. **Develop and test**

7. **Commit changes:**
   ```bash
   git add .
   git commit -m "feat: descriptive message"
   ```

8. **Push and create PR:**
   ```bash
   git push origin feature/your-feature-name
   ```

### Hot Reload

Vite provides hot module replacement (HMR):
- CSS changes reflect instantly
- Livewire components auto-refresh
- Page auto-reloads on PHP changes (with browser extension)

### Clearing Caches

During development, you may need to clear caches:

```bash
# Clear all caches
php artisan optimize:clear

# Or individually:
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## Coding Standards

### PHP Coding Standards

Follow **PSR-12** coding standard.

**Key Rules:**
- Use 4 spaces for indentation (no tabs)
- Opening braces on same line for methods/functions
- One blank line after namespace declaration
- Use type hints and return types

**Example:**
```php
<?php

namespace App\Services;

class ExampleService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function findUser(int $id): ?User
    {
        return $this->userRepository->find($id);
    }
}
```

### Naming Conventions

**Classes:**
- PascalCase: `UserController`, `AuthService`

**Methods:**
- camelCase: `getUserProfile()`, `sendMessage()`

**Variables:**
- camelCase: `$userId`, `$userName`

**Database Tables:**
- snake_case, plural: `users`, `user_profiles`

**Database Columns:**
- snake_case: `first_name`, `created_at`

**Routes:**
- kebab-case: `/user-profile`, `/send-message`

**Blade Views:**
- kebab-case: `user-profile.blade.php`

**Livewire Components:**
- PascalCase class, kebab-case view: `UserProfile.php` → `user-profile.blade.php`

### Laravel Best Practices

**1. Use Eloquent Relationships:**
```php
// Good
$user->matches;

// Avoid
$matches = Match::where('user_id', $user->id)->get();
```

**2. Use Query Scopes:**
```php
// In Model
public function scopeActive($query)
{
    return $query->where('status', 'active');
}

// Usage
User::active()->get();
```

**3. Use Form Requests for Validation:**
```php
// Good
public function store(CreateUserRequest $request)
{
    $validated = $request->validated();
}

// Avoid validation in controller
```

**4. Use Resource Classes for API:**
```php
return new UserResource($user);
```

**5. Use Services for Complex Logic:**
```php
// Good
$this->authService->authenticate($credentials);

// Avoid putting business logic in controllers
```

**6. Use Jobs for Long-Running Tasks:**
```php
ProcessVideoUpload::dispatch($video);
```

**7. Use Events and Listeners:**
```php
event(new UserRegistered($user));
```

### JavaScript/Vue/Alpine Standards

**Use ES6+ syntax:**
```javascript
// Good
const user = { name, email };
const users = data.map(user => user.name);

// Avoid
var user = { name: name, email: email };
```

**Use Alpine.js for simple interactions:**
```html
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open">Content</div>
</div>
```

### CSS/Tailwind Standards

**Use Tailwind utility classes:**
```html
<button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
    Click me
</button>
```

**Extract repeated patterns into components:**
```css
/* resources/css/components.css */
.btn-primary {
    @apply px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600;
}
```

### Code Formatting

**Use Laravel Pint for PHP:**
```bash
./vendor/bin/pint
```

**Configure VS Code:**
```json
{
    "editor.formatOnSave": true,
    "php.suggest.basic": false,
    "php.validate.enable": false,
    "intelephense.format.braces": "psr12"
}
```

---

## Testing

### Running Tests

**Run all tests:**
```bash
php artisan test
```

**Run specific test:**
```bash
php artisan test --filter UserTest
```

**Run with coverage:**
```bash
php artisan test --coverage
```

### Writing Tests

YorYor uses **Pest PHP** for testing.

**Feature Test Example:**
```php
<?php

use App\Models\User;

test('user can register', function () {
    $response = $this->post('/register', [
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'name' => 'Test User',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});
```

**Unit Test Example:**
```php
<?php

use App\Services\AuthService;
use App\Models\User;

test('auth service authenticates user', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $authService = app(AuthService::class);
    $result = $authService->authenticate([
        'email' => $user->email,
        'password' => 'password',
    ]);

    expect($result['status'])->toBe('success');
    expect($result['user']->id)->toBe($user->id);
});
```

**Livewire Test Example:**
```php
<?php

use App\Livewire\Auth\Login;

test('login component renders', function () {
    Livewire::test(Login::class)
        ->assertSee('Email')
        ->assertSee('Password');
});

test('user can login via livewire', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect('/dashboard');
});
```

### Test Database

Tests use separate database (SQLite in-memory by default).

**Configure in `phpunit.xml`:**
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

---

## Git Workflow

### Branch Strategy

**Main Branches:**
- `main`: Production-ready code
- `develop`: Development branch (if using)

**Feature Branches:**
- `feature/{feature-name}`: New features
- `fix/{bug-name}`: Bug fixes
- `hotfix/{issue}`: Urgent production fixes
- `refactor/{component}`: Code refactoring

### Commit Message Convention

Use **Conventional Commits**:

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Formatting, no code change
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance tasks

**Examples:**
```
feat(auth): add two-factor authentication

Implement TOTP-based 2FA using pragmarx/google2fa package.
Users can enable 2FA and receive backup codes.

Closes #123
```

```
fix(chat): resolve message duplication issue

Messages were being duplicated due to WebSocket event
being fired twice. Added event deduplication logic.
```

### Pull Request Process

1. **Create feature branch:**
   ```bash
   git checkout -b feature/your-feature
   ```

2. **Make changes and commit:**
   ```bash
   git add .
   git commit -m "feat: your feature description"
   ```

3. **Keep branch updated:**
   ```bash
   git fetch origin
   git rebase origin/main
   ```

4. **Push branch:**
   ```bash
   git push origin feature/your-feature
   ```

5. **Create Pull Request on GitHub**

6. **Fill PR template:**
   - Description of changes
   - Related issue numbers
   - Testing performed
   - Screenshots (if UI changes)

7. **Request review from team members**

8. **Address review comments**

9. **Merge after approval**

---

## Common Development Tasks

### Creating a New Feature

**1. Create Feature Branch:**
```bash
git checkout -b feature/user-preferences
```

**2. Create Migration:**
```bash
php artisan make:migration create_user_preferences_table
```

**3. Create Model:**
```bash
php artisan make:model UserPreference
```

**4. Create Livewire Component:**
```bash
php artisan make:livewire Profile/Preferences
```

**5. Create Service (if needed):**
```bash
touch app/Services/PreferenceService.php
```

**6. Add Routes:**
Edit `routes/web.php` or `routes/api.php`

**7. Write Tests:**
```bash
php artisan make:test UserPreferenceTest
```

**8. Run Tests:**
```bash
php artisan test
```

**9. Commit and Push:**
```bash
git add .
git commit -m "feat: add user preferences management"
git push origin feature/user-preferences
```

### Adding a New API Endpoint

**1. Create Controller:**
```bash
php artisan make:controller Api/V1/PreferenceController --api
```

**2. Create Form Request:**
```bash
php artisan make:request UpdatePreferenceRequest
```

**3. Create Resource:**
```bash
php artisan make:resource PreferenceResource
```

**4. Add Route:**
```php
// routes/api.php
Route::apiResource('preferences', PreferenceController::class);
```

**5. Write Tests:**
```php
test('user can update preferences', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/preferences', [
            'age_min' => 25,
            'age_max' => 35,
        ]);

    $response->assertOk();
});
```

### Adding a Database Migration

**Create Migration:**
```bash
php artisan make:migration add_verified_column_to_users_table
```

**Write Migration:**
```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('verified')->default(false);
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('verified');
    });
}
```

**Run Migration:**
```bash
php artisan migrate
```

### Adding a Background Job

**Create Job:**
```bash
php artisan make:job ProcessVideoUpload
```

**Write Job:**
```php
class ProcessVideoUpload implements ShouldQueue
{
    public function __construct(public Video $video) {}

    public function handle()
    {
        // Process video
    }
}
```

**Dispatch Job:**
```php
ProcessVideoUpload::dispatch($video);
```

### Adding a Service

**Create Service File:**
```bash
touch app/Services/VideoService.php
```

**Write Service:**
```php
<?php

namespace App\Services;

class VideoService
{
    public function process(Video $video): void
    {
        // Processing logic
    }
}
```

**Register in Service Provider (if singleton):**
```php
$this->app->singleton(VideoService::class);
```

**Use in Controller:**
```php
public function __construct(
    private VideoService $videoService
) {}
```

---

## Debugging

### Laravel Telescope

Access Telescope at: http://localhost:8000/telescope

**Features:**
- View requests
- Inspect queries
- Monitor jobs
- View exceptions
- Check cache operations

### Laravel Debugbar (Optional)

Install for additional debugging:
```bash
composer require barryvdh/laravel-debugbar --dev
```

### Logging

**Log debugging information:**
```php
Log::debug('User data', ['user' => $user]);
Log::info('Action performed', ['action' => 'login']);
Log::error('Error occurred', ['error' => $exception->getMessage()]);
```

**View logs:**
```bash
tail -f storage/logs/laravel.log
```

**Or use Pail:**
```bash
php artisan pail
```

### Tinker

Laravel's REPL for testing code:
```bash
php artisan tinker
```

```php
// Test queries
User::count();
User::first();

// Test services
$authService = app(AuthService::class);
$result = $authService->authenticate([...]);

// Test relationships
$user = User::first();
$user->matches;
```

### Database Debugging

**Enable query logging:**
```php
DB::enableQueryLog();
// Your code here
dd(DB::getQueryLog());
```

**Or use Telescope to view queries**

### Frontend Debugging

**Vue Devtools / Alpine Devtools:**
Install browser extension for debugging

**Console Logging:**
```javascript
console.log('User data:', user);
console.table(users);
```

**Network Tab:**
Monitor API requests and WebSocket connections

---

## Troubleshooting

### Common Issues

**Issue: "Class not found"**
```bash
composer dump-autoload
```

**Issue: "View not found"**
```bash
php artisan view:clear
php artisan config:clear
```

**Issue: "Mix manifest not found"**
```bash
npm run dev
```

**Issue: Database connection error**
- Check `.env` database credentials
- Ensure database server is running
- Test connection: `php artisan tinker` then `DB::connection()->getPdo()`

**Issue: Queue jobs not processing**
```bash
# Restart queue worker
php artisan queue:restart

# Or if using Supervisor
sudo supervisorctl restart yoryor-worker:*
```

**Issue: WebSocket not connecting**
- Ensure Reverb is running: `php artisan reverb:start`
- Check `.env` WebSocket configuration
- Verify port 8080 is not blocked

**Issue: Permission denied errors**
```bash
chmod -R 775 storage bootstrap/cache
```

**Issue: npm install errors**
```bash
rm -rf node_modules package-lock.json
npm install
```

---

## Resources

### Laravel Documentation
- [Laravel Docs](https://laravel.com/docs)
- [Livewire Docs](https://livewire.laravel.com/docs)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Laravel Reverb](https://laravel.com/docs/reverb)

### Frontend
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Alpine.js](https://alpinejs.dev/)
- [Flowbite](https://flowbite.com/)

### Testing
- [Pest PHP](https://pestphp.com/docs)
- [Laravel Testing](https://laravel.com/docs/testing)

### Tools
- [Laravel Telescope](https://laravel.com/docs/telescope)
- [Laravel Horizon](https://laravel.com/docs/horizon)
- [Laravel Pint](https://laravel.com/docs/pint)

### Video Tutorials
- [Laracasts](https://laracasts.com/)
- [Laravel Daily](https://www.youtube.com/c/LaravelDaily)

### Community
- [Laravel Discord](https://discord.gg/laravel)
- [Livewire Discord](https://discord.gg/livewire)

---

## Development Tips

### Performance Tips
- Use eager loading to avoid N+1 queries
- Cache expensive queries
- Use database indexing
- Optimize images before upload
- Use queues for slow operations

### Security Tips
- Always validate user input
- Use CSRF protection
- Never expose sensitive data in responses
- Use policies for authorization
- Hash passwords, never store plaintext
- Sanitize output to prevent XSS

### Code Quality Tips
- Write tests for new features
- Keep methods small and focused
- Use descriptive variable names
- Comment complex logic
- Follow SOLID principles
- Review your own code before PR

### Productivity Tips
- Use PHPStorm or VS Code with Laravel plugins
- Learn keyboard shortcuts
- Use Laravel Tinker for quick testing
- Use Git aliases for common commands
- Keep your local environment clean

---

## Getting Help

**Internal Resources:**
- Check existing documentation
- Search GitHub issues
- Ask in team Slack channel

**External Resources:**
- Laravel Discord community
- Stack Overflow (tag: laravel)
- Laracasts forum
- Official Laravel documentation

**Debug Process:**
1. Read error message carefully
2. Check Laravel logs
3. Search error message online
4. Check relevant documentation
5. Ask team for help
6. Create GitHub issue if bug

---

## Onboarding Checklist

For new developers:

- [ ] Development environment set up
- [ ] Repository cloned and running locally
- [ ] Test user created and can log in
- [ ] Familiarized with project structure
- [ ] Read coding standards
- [ ] Read API documentation
- [ ] Completed sample feature (e.g., add a profile field)
- [ ] First PR reviewed and merged
- [ ] Added to team communication channels
- [ ] Access to staging/production environments (if needed)

---

**Welcome to the YorYor development team! Happy coding!**

Last Updated: September 2025