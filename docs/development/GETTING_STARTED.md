# Getting Started with YorYor Development

## Table of Contents
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [Essential Commands](#essential-commands)
- [Development Workflow](#development-workflow)
- [Testing Your Setup](#testing-your-setup)
- [Troubleshooting](#troubleshooting)
- [Next Steps](#next-steps)

---

## Prerequisites

Before you begin developing YorYor, ensure you have the following installed:

### Required Software
- **PHP 8.2 or higher** - Laravel 12 requires PHP 8.2+
- **Composer** - PHP dependency manager
- **Node.js 18+ and npm** - For frontend assets
- **Database** - MySQL 8.0+ (production) or SQLite (development)
- **Redis** (optional but recommended for production)

### Platform-Specific Installation

#### macOS (using Homebrew)

```bash
# Install Homebrew if not installed
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install PHP 8.2
brew install php@8.2
brew link php@8.2 --force

# Install Composer
brew install composer

# Install Node.js
brew install node@18

# Install MySQL
brew install mysql@8.0
brew services start mysql@8.0

# Install Redis (recommended)
brew install redis
brew services start redis
```

#### Ubuntu/Debian

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2 and extensions
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml \
    php8.2-curl php8.2-zip php8.2-gd php8.2-mbstring php8.2-redis \
    php8.2-bcmath php8.2-intl php8.2-soap

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Install Redis
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

#### Windows

**Option 1: Laravel Herd (Recommended)**
- Download and install [Laravel Herd](https://herd.laravel.com/)
- Herd includes PHP, Composer, and all required extensions
- Lightweight and optimized for Laravel development

**Option 2: Manual Installation**
- Install [XAMPP](https://www.apachefriends.org/) for PHP and MySQL
- Install [Composer](https://getcomposer.org/download/)
- Install [Node.js](https://nodejs.org/)

### Verify Installation

```bash
# Check PHP version
php -v
# Should show PHP 8.2.x or higher

# Check Composer
composer --version

# Check Node.js and npm
node -v
npm -v

# Check MySQL
mysql --version

# Check Redis (if installed)
redis-cli ping
# Should return "PONG"
```

---

## Installation

### 1. Clone the Repository

```bash
# Clone the repository
git clone https://github.com/yourusername/yoryor.git
cd yoryor

# Or if you have SSH configured
git clone git@github.com:yourusername/yoryor.git
cd yoryor
```

### 2. Install PHP Dependencies

```bash
composer install
```

This will install all Laravel packages and dependencies defined in `composer.json`.

**Note:** If you encounter memory issues, run:
```bash
php -d memory_limit=-1 /usr/local/bin/composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

This installs all frontend dependencies (Vite, Tailwind CSS, Alpine.js, etc.).

### 4. Create Environment File

```bash
# Copy example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

The `.env.example` file contains all necessary environment variables with sensible defaults.

---

## Environment Configuration

### Basic Configuration

Edit the `.env` file and configure the following essential variables:

```env
# Application
APP_NAME=YorYor
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_LOCALE=en

# Database (SQLite for development)
DB_CONNECTION=sqlite
# For MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=yoryor
# DB_USERNAME=root
# DB_PASSWORD=

# Broadcasting (Laravel Reverb)
BROADCAST_CONNECTION=reverb

# Queue
QUEUE_CONNECTION=database

# Cache
CACHE_STORE=database
```

### Laravel Reverb Configuration

Configure WebSocket server for real-time features:

```env
REVERB_APP_ID=yoryor-local
REVERB_APP_KEY=yoryor-key-local-$(openssl rand -hex 16)
REVERB_APP_SECRET=yoryor-secret-local-$(openssl rand -hex 16)
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Vite needs to know Reverb configuration
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Mail Configuration (Development)

For local development, use the `log` driver to write emails to log files:

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@yoryor.local
MAIL_FROM_NAME="${APP_NAME}"
```

For testing with real emails, use [Mailtrap](https://mailtrap.io/):

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
```

### Optional: Advanced Configuration

These can be configured later as needed:

```env
# Video Calling (VideoSDK.live)
VIDEOSDK_API_KEY=
VIDEOSDK_SECRET_KEY=
VIDEOSDK_API_ENDPOINT=https://api.videosdk.live/v2

# Media Storage (Cloudflare R2)
CLOUDFLARE_R2_ACCESS_KEY_ID=
CLOUDFLARE_R2_SECRET_ACCESS_KEY=
CLOUDFLARE_R2_DEFAULT_REGION=auto
CLOUDFLARE_R2_BUCKET=
CLOUDFLARE_R2_URL=
CLOUDFLARE_R2_ENDPOINT=
CLOUDFLARE_R2_USE_PATH_STYLE_ENDPOINT=true

# Expo Push Notifications
EXPO_ACCESS_TOKEN=

# Social Authentication
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

---

## Database Setup

### Option 1: SQLite (Recommended for Development)

SQLite is the easiest option for local development:

```bash
# Create SQLite database file
touch database/database.sqlite

# Ensure .env has SQLite configuration
# DB_CONNECTION=sqlite

# Run migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed --class=CountrySeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=SubscriptionPlanSeeder
```

### Option 2: MySQL

If you prefer MySQL:

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE yoryor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Update .env
# DB_CONNECTION=mysql
# DB_DATABASE=yoryor
# DB_USERNAME=root
# DB_PASSWORD=your-password

# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed --class=CountrySeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=SubscriptionPlanSeeder
```

### Create Test User

```bash
php artisan tinker
```

Then run the following in Tinker:

```php
$user = User::factory()->create([
    'email' => 'test@example.com',
    'password' => bcrypt('password123'),
    'registration_completed' => true,
]);

$user->profile()->create([
    'first_name' => 'Test',
    'last_name' => 'User',
    'date_of_birth' => '1990-01-01',
    'gender' => 'male',
    'country_id' => 1,
]);

$user->setting()->create([]);
$user->preference()->create([
    'gender_preference' => 'female',
    'min_age' => 25,
    'max_age' => 35,
]);

echo "Test user created: test@example.com / password123\n";
exit;
```

### Create Admin User

```bash
php artisan tinker
```

```php
$admin = User::factory()->create([
    'email' => 'admin@yoryor.com',
    'password' => bcrypt('admin123'),
    'registration_completed' => true,
]);

$admin->profile()->create([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'date_of_birth' => '1985-01-01',
    'gender' => 'male',
    'country_id' => 1,
]);

// Assign admin role (ensure RoleSeeder has run)
$adminRole = \App\Models\Role::where('slug', 'admin')->first();
if ($adminRole) {
    $admin->roles()->attach($adminRole->id);
}

echo "Admin user created: admin@yoryor.com / admin123\n";
exit;
```

---

## Running the Application

### Option 1: Using Composer Script (Recommended)

The easiest way to start all services:

```bash
composer dev
```

This single command starts:
- Laravel development server (port 8000)
- Laravel Reverb WebSocket server (port 8080)
- Queue worker
- Real-time logs (Laravel Pail)
- Vite dev server (hot reload)

Access the application at: **http://localhost:8000**

### Option 2: Manual Start

If you prefer to run services separately:

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Vite Dev Server:**
```bash
npm run dev
```

**Terminal 3 - Laravel Reverb (WebSocket):**
```bash
php artisan reverb:start
```

**Terminal 4 - Queue Worker:**
```bash
php artisan queue:listen --tries=1
```

**Terminal 5 - Real-time Logs (Optional):**
```bash
php artisan pail --timeout=0
```

### Stopping the Application

- If using `composer dev`: Press `Ctrl+C` to stop all services
- If running manually: Press `Ctrl+C` in each terminal

---

## Essential Commands

### Development Server

```bash
# Start all services (Laravel + Reverb + Queue + Vite + Logs)
composer dev

# Start Laravel server only
php artisan serve

# Start on custom port
php artisan serve --port=8080

# Start Vite dev server
npm run dev

# Build assets for production
npm run build
```

### Database

```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Fresh database (drops all tables and re-runs migrations)
php artisan migrate:fresh

# Fresh database with seeders
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_table_name

# Create model with migration
php artisan make:model ModelName -m
```

### Queue & Jobs

```bash
# Start queue worker
php artisan queue:work

# Listen to queue (auto-restart on code changes)
php artisan queue:listen --tries=1

# View failed jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all

# Retry specific job
php artisan queue:retry {job-id}

# Clear failed jobs
php artisan queue:flush
```

### Broadcasting (Reverb)

```bash
# Start Reverb WebSocket server
php artisan reverb:start

# Start with debug output
php artisan reverb:start --debug

# Restart Reverb
php artisan reverb:restart
```

### Cache Management

```bash
# Clear all caches
php artisan optimize:clear

# Clear specific caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Cache configurations (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthTest.php

# Run with coverage
php artisan test --coverage

# Run with coverage and minimum threshold
php artisan test --coverage --min=80

# Filter tests by name
php artisan test --filter=test_user_can_login
```

### Code Quality

```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Check code without fixing
./vendor/bin/pint --test
```

### Livewire

```bash
# Create Livewire component
php artisan make:livewire ComponentName

# Create in subdirectory
php artisan make:livewire Profile/BasicInfo

# Create page component
php artisan make:livewire Pages/DiscoverPage
```

### Maintenance

```bash
# Enter maintenance mode
php artisan down

# Enter maintenance with secret bypass
php artisan down --secret="maintenance-bypass-token"

# Exit maintenance mode
php artisan up

# View logs in real-time
php artisan pail

# Open Tinker (Laravel REPL)
php artisan tinker
```

---

## Development Workflow

### Daily Workflow

1. **Pull Latest Changes:**
   ```bash
   git pull origin main
   ```

2. **Update Dependencies (if changed):**
   ```bash
   composer install
   npm install
   ```

3. **Run Migrations (if new):**
   ```bash
   php artisan migrate
   ```

4. **Clear Caches:**
   ```bash
   php artisan optimize:clear
   ```

5. **Start Development Servers:**
   ```bash
   composer dev
   ```

6. **Create Feature Branch:**
   ```bash
   git checkout -b feature/your-feature-name
   ```

7. **Develop and Test**

8. **Commit Changes:**
   ```bash
   git add .
   git commit -m "feat: your feature description"
   ```

9. **Push and Create PR:**
   ```bash
   git push origin feature/your-feature-name
   ```

### Hot Reload

Vite provides Hot Module Replacement (HMR):
- CSS changes reflect instantly without page reload
- JavaScript changes trigger automatic page refresh
- Livewire components auto-refresh on interaction

### Debugging

**Laravel Telescope** (available in development):
- Access: http://localhost:8000/telescope
- View requests, queries, exceptions, logs, events, jobs

**Laravel Tinker:**
```bash
php artisan tinker
```

```php
// Test queries
User::count();
User::with('profile')->first();

// Test services
$authService = app(\App\Services\AuthService::class);

// Fire events
event(new \App\Events\NewMessageEvent($message));
```

**Real-time Logs:**
```bash
# Watch logs in real-time
php artisan pail

# Filter by level
php artisan pail --filter=error
```

---

## Testing Your Setup

### 1. Access Landing Page

Visit http://localhost:8000 - You should see the YorYor landing page.

### 2. Login with Test User

- Navigate to http://localhost:8000/login
- Email: `test@example.com`
- Password: `password123`

### 3. Test WebSocket Connection

Open browser console and check for WebSocket connection:
```javascript
// Should see successful connection to ws://localhost:8080
```

### 4. Run Test Suite

```bash
php artisan test
```

All tests should pass. If tests fail, check:
- Database connection
- Environment configuration
- Dependencies installed

### 5. Check Services

**Laravel Server:**
```bash
curl http://localhost:8000/api/health
# Should return: {"status":"ok"}
```

**Reverb WebSocket:**
Check if port 8080 is listening:
```bash
lsof -i :8080
# Should show php process
```

**Queue Worker:**
```bash
# Check jobs table in database
php artisan tinker
\DB::table('jobs')->count();
```

---

## Troubleshooting

### Common Issues

#### Issue: "Class not found"

```bash
# Clear and regenerate autoload files
composer dump-autoload
php artisan optimize:clear
```

#### Issue: "View not found"

```bash
# Clear view cache
php artisan view:clear
php artisan config:clear
```

#### Issue: "SQLSTATE connection refused"

**For SQLite:**
```bash
# Ensure database file exists
touch database/database.sqlite

# Verify .env
# DB_CONNECTION=sqlite
```

**For MySQL:**
```bash
# Test MySQL connection
mysql -u root -p -e "SHOW DATABASES;"

# Verify .env credentials
# DB_HOST=127.0.0.1
# DB_DATABASE=yoryor
# DB_USERNAME=root
# DB_PASSWORD=
```

#### Issue: "Mix manifest not found" or "Vite manifest not found"

```bash
# Rebuild assets
npm run dev
```

#### Issue: "Port 8000 already in use"

```bash
# Find and kill process using port 8000
lsof -ti:8000 | xargs kill -9

# Or use different port
php artisan serve --port=8080
```

#### Issue: "WebSocket connection failed"

```bash
# Ensure Reverb is running
php artisan reverb:start

# Check .env configuration
# REVERB_HOST=localhost
# REVERB_PORT=8080
# VITE_REVERB_APP_KEY should match REVERB_APP_KEY

# Check firewall isn't blocking port 8080
```

#### Issue: "Queue jobs not processing"

```bash
# Restart queue worker
php artisan queue:restart

# Check failed jobs
php artisan queue:failed

# Check jobs table
php artisan tinker
\DB::table('jobs')->get();
```

#### Issue: "Permission denied" on storage/cache directories

```bash
# macOS/Linux
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache

# Verify permissions
ls -la storage
```

#### Issue: "npm install" fails

```bash
# Clear npm cache
npm cache clean --force

# Remove node_modules and package-lock.json
rm -rf node_modules package-lock.json

# Reinstall
npm install
```

#### Issue: "Composer install" fails with memory limit

```bash
# Increase memory limit
php -d memory_limit=-1 /usr/local/bin/composer install
```

### Getting Help

**Internal Resources:**
- Check `/documentation/` directory for detailed docs
- Review this guide and other development docs in `/docs/development/`
- Search GitHub issues

**External Resources:**
- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- Laravel Discord: https://discord.gg/laravel
- Stack Overflow (tag: laravel)

---

## Next Steps

### 1. Familiarize with Codebase

- Read `/docs/development/ARCHITECTURE.md` - Understand system architecture
- Read `/docs/development/DATABASE.md` - Learn database schema
- Review `/docs/development/SERVICES.md` - Understand service layer

### 2. Explore Core Features

- **Authentication**: `app/Services/AuthService.php`
- **Matching System**: `app/Models/Like.php`, `app/Models/MatchModel.php`
- **Messaging**: `app/Http/Controllers/Api/V1/ChatController.php`
- **Real-time**: `app/Events/`, `resources/js/echo.js`

### 3. Review Code Standards

- Read coding standards in `/documentation/DEVELOPMENT_GUIDE.md`
- Review Laravel best practices
- Check Livewire component patterns in `app/Livewire/`

### 4. Set Up Development Tools

**Recommended VS Code Extensions:**
- Laravel Extension Pack
- Livewire Goto
- Tailwind CSS IntelliSense
- PHP Intelephense
- GitLens

**Recommended PHPStorm Plugins:**
- Laravel Idea
- Tailwind CSS
- .env files support

### 5. Complete Onboarding Checklist

- [ ] Development environment set up
- [ ] Repository cloned and running locally
- [ ] Test user created and can log in
- [ ] Familiarized with project structure
- [ ] Read coding standards
- [ ] Read API documentation
- [ ] Completed sample feature (e.g., add a profile field)
- [ ] First PR reviewed and merged
- [ ] Added to team communication channels

---

## Quick Reference

### Useful URLs (Local Development)

- **Application**: http://localhost:8000
- **Telescope**: http://localhost:8000/telescope
- **Pulse**: http://localhost:8000/pulse
- **Horizon**: http://localhost:8000/horizon (requires Redis)

### Important File Locations

- **Configuration**: `config/`
- **Routes**: `routes/api.php`, `routes/web.php`, `routes/user.php`
- **Controllers**: `app/Http/Controllers/Api/V1/`
- **Services**: `app/Services/`
- **Models**: `app/Models/`
- **Livewire**: `app/Livewire/`
- **Migrations**: `database/migrations/`
- **Views**: `resources/views/`
- **JavaScript**: `resources/js/`
- **CSS**: `resources/css/`

### Environment Files

- **Example**: `.env.example` - Template with all variables
- **Local**: `.env` - Your local configuration (not committed)
- **Testing**: `.env.testing` - Test environment config

---

**Welcome to YorYor development! Happy coding! 🚀**

*Last Updated: October 2025*
