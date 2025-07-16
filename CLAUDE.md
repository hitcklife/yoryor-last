# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**YorYor** - A Laravel-based dating/matchmaking application with real-time messaging, video calling, and comprehensive user matching features.

- **Framework**: Laravel 12.x with Livewire
- **Frontend**: Vite + TailwindCSS v4
- **Real-time**: Pusher WebSockets with Laravel Echo
- **Database**: SQLite (dev), supports MySQL/PostgreSQL
- **Queue**: Redis/Database with Laravel Horizon
- **Testing**: Pest PHP

## Essential Commands

### Development
```bash
# Start all development servers (Laravel, Queue, Logs, Vite)
composer dev

# Individual services
php artisan serve              # Laravel server
npm run dev                    # Vite dev server
php artisan queue:work         # Queue worker
php artisan pail              # Real-time log viewer
```

### Build & Production
```bash
npm run build                  # Build frontend assets
php artisan optimize           # Optimize for production
php artisan config:cache       # Cache configuration
php artisan route:cache        # Cache routes
```

### Testing
```bash
./vendor/bin/pest              # Run all tests
php artisan test               # Alternative test command
php artisan test --parallel    # Run tests in parallel
./vendor/bin/pest --watch      # Watch mode
```

### Code Quality
```bash
./vendor/bin/pint              # Format code with Laravel Pint
php artisan l5-swagger:generate # Generate API documentation
```

### Database
```bash
php artisan migrate            # Run migrations
php artisan migrate:fresh      # Fresh migration (deletes all data)
php artisan db:seed            # Seed database
```

### Queue & Jobs
```bash
php artisan queue:work         # Process queues
php artisan horizon            # Start Horizon (queue monitoring)
php artisan horizon:status     # Check Horizon status
```

### Custom Commands
```bash
php artisan calls:handle-missed    # Process missed video calls
php artisan test:audio-conversion  # Test audio conversion functionality
```

## Architecture & Code Organization

### API Structure
- All APIs are versioned under `/api/v1/`
- Controllers: `app/Http/Controllers/Api/V1/`
- Resources: `app/Http/Resources/` (for consistent API responses)
- Requests: `app/Http/Requests/` (validation)
- Exceptions: `app/Exceptions/Api/` (custom API exceptions)

### Design Patterns
- **Repository Pattern**: Data access layer in `app/Repositories/`
- **Service Layer**: Business logic in `app/Services/`
- **Resource Classes**: API transformers in `app/Http/Resources/`
- **Event-Driven**: Real-time events in `app/Events/`
- **Policy Authorization**: Resource policies in `app/Policies/`

### Key Services
- **AuthService**: Authentication logic
- **NotificationService**: Push notifications via Expo
- **MatchService**: Matching algorithm
- **ChatService**: Messaging functionality
- **CallService**: Video/voice calling (Agora + Video SDK)

### Real-time Features
- WebSocket events in `app/Events/`
- Channel authorization in `routes/channels.php`
- Echo configuration in `resources/js/echo.js`

## Core Features

### Authentication & User Management
- Phone/Email authentication with OTP
- JWT-based API authentication
- Two-factor authentication (Google 2FA)
- Profile completion tracking
- User preferences (cultural, physical, career, family)

### Matching System
- Like/Dislike functionality
- Mutual match detection
- Preference-based filtering
- Match suggestions algorithm

### Chat System
- Real-time messaging via WebSockets
- Message status (sent, delivered, read)
- Typing indicators
- Message edit/delete
- Unread counts
- Media sharing

### Video/Voice Calling
- Dual integration: Agora and Video SDK
- Call history tracking
- Missed call handling
- Call analytics

### Push Notifications
- Expo push notification service
- Device token management
- Notification preferences

### Media Handling
- Image upload with multiple sizes (Intervention/Image)
- Video processing (PHP-FFmpeg)
- Thumbnail generation
- S3 storage support

## Environment Configuration

Key environment variables to configure:

```bash
# Core
APP_URL=http://localhost
DB_CONNECTION=sqlite

# Real-time
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=

# Video Calling
AGORA_APP_ID=
AGORA_APP_CERTIFICATE=
VIDEOSDK_API_KEY=
VIDEOSDK_SECRET_KEY=

# Storage
FILESYSTEM_DISK=local  # or s3 for production
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_BUCKET=

# Queue & Cache
QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
```

## Development Tools

### Debugging & Monitoring
- **Laravel Telescope**: `/telescope` - Request/response debugging
- **Laravel Horizon**: `/horizon` - Queue monitoring
- **Laravel Pulse**: Application performance monitoring
- **Laravel Pail**: Real-time log viewer

### API Documentation
- Swagger/OpenAPI: `/api/documentation`
- Generate docs: `php artisan l5-swagger:generate`
- Swagger definitions in `app/Swagger/`

## Testing Guidelines

- Use Pest PHP syntax for new tests
- Feature tests must use `RefreshDatabase` trait
- Tests run on SQLite in-memory database
- Mock external services (mail, notifications)
- Test structure:
  - `tests/Feature/` - Integration tests
  - `tests/Unit/` - Unit tests

## Common Development Tasks

### Adding New API Endpoint
1. Create controller in `app/Http/Controllers/Api/V1/`
2. Add validation request in `app/Http/Requests/`
3. Create resource in `app/Http/Resources/`
4. Add route in `routes/api.php`
5. Update Swagger documentation
6. Write feature tests

### Working with Real-time Events
1. Create event class in `app/Events/`
2. Define channel authorization in `routes/channels.php`
3. Broadcast event using `broadcast(new EventName())`
4. Listen on frontend with Laravel Echo

### Database Changes
1. Create migration: `php artisan make:migration create_table_name`
2. Update model in `app/Models/`
3. Create/update repository in `app/Repositories/`
4. Run migration: `php artisan migrate`
5. Update factories and seeders if needed

## Performance Considerations

- Use eager loading to prevent N+1 queries
- Implement caching for frequently accessed data
- Use queues for time-consuming tasks
- Optimize images on upload
- Use database indexes on frequently queried columns
- API responses are paginated by default

## Security Best Practices

- All API routes require authentication except auth endpoints
- Rate limiting implemented on sensitive endpoints
- CORS configured for API access
- Input validation on all user inputs
- File upload restrictions enforced
- SQL injection prevention via Eloquent ORM
- XSS protection via blade templating