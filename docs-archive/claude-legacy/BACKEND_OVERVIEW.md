# YorYor Backend Overview

## Project Information
- **Framework**: Laravel 12.x
- **PHP Version**: 8.2+
- **Database**: SQLite (local), MySQL/PostgreSQL (production)
- **Real-time**: Pusher + Laravel Broadcasting
- **Authentication**: Laravel Sanctum
- **Mobile Platform**: React Native (Expo)

## Architecture Overview

### Technology Stack

#### Core Dependencies
- **Laravel Framework 12.x**: Latest version with performance improvements
- **Laravel Sanctum**: API token authentication
- **Laravel Horizon**: Queue monitoring
- **Laravel Telescope**: Development debugging
- **Laravel Pulse**: Application monitoring

#### Real-time & Communication
- **Pusher PHP Server**: WebSocket broadcasting
- **Laravel Broadcasting**: Real-time event system
- **Expo Push Notifications**: Mobile push notifications

#### Media Processing
- **Intervention/Image**: Image manipulation
- **PHP-FFmpeg**: Video/audio processing
- **Laravel Thumbnail**: Thumbnail generation
- **AWS S3**: Cloud storage

#### Security & Authentication
- **JWT Auth**: JSON Web Token support
- **Google 2FA**: Two-factor authentication
- **Strong password validation**
- **Rate limiting middleware**

#### API Documentation
- **L5-Swagger**: OpenAPI/Swagger documentation

## Core Features

### 1. Authentication System
- Token-based authentication using Sanctum
- Phone number verification with OTP
- Email verification support
- Two-factor authentication (2FA)
- Social login capabilities

### 2. User Management
- Comprehensive profile system
- Multi-photo management
- User preferences and settings
- Block/report functionality
- Activity tracking

### 3. Matching System
- Like/dislike functionality
- Mutual match detection
- Potential match suggestions
- Match filtering based on preferences

### 4. Chat System
- Real-time messaging
- Media sharing (images, videos, voice)
- Message editing/deletion
- Read receipts
- Typing indicators
- Group chat support

### 5. Video Calling
- Video SDK integration
- Token-based authentication
- Call history tracking
- Missed call handling
- Call analytics

### 6. Push Notifications
- Expo push notification service
- Rich notifications with images
- Notification preferences
- Device token management

### 7. Media Management
- Image processing (multiple sizes)
- Video transcoding
- Voice message support
- S3 cloud storage
- Thumbnail generation

## Database Architecture

### Core Tables
- **users**: User accounts and authentication
- **profiles**: User profile information
- **user_photos**: Photo management
- **chats**: Chat rooms
- **messages**: Chat messages
- **likes/dislikes**: User interactions
- **matches**: Mutual matches
- **calls**: Video call history
- **device_tokens**: Push notification tokens

### Supporting Tables
- **user_preferences**: Matching preferences
- **user_settings**: App settings
- **user_blocks**: Blocked users
- **user_reports**: User reports
- **user_activities**: Activity tracking
- **message_reads**: Read receipts

## API Structure

### Base URL
```
/api/v1
```

### Main Endpoints
- `/auth/*` - Authentication
- `/profile/*` - User profiles
- `/matches/*` - Matching system
- `/chats/*` - Chat functionality
- `/video-call/*` - Video calling
- `/presence/*` - Online status
- `/settings/*` - User settings
- `/photos/*` - Photo management

## Security Features

### Authentication
- Sanctum token authentication
- JWT support for video calling
- Session management
- Device token tracking

### Rate Limiting
- Authentication attempts: Custom limits
- Message sending: 60/minute
- Chat creation: 10/minute
- API calls: Laravel default

### Data Protection
- HTTPS enforcement
- Secure headers middleware
- Input validation
- SQL injection prevention
- XSS protection

## Performance Optimizations

### Database
- Optimized indexes on frequently queried columns
- Efficient query scopes
- Lazy loading prevention
- Cache integration

### Caching
- Redis/Cache support
- Presence data caching
- Configuration caching
- Route caching

### Queue System
- Laravel Horizon for monitoring
- Background job processing
- Failed job handling
- Job batching support

## Monitoring & Debugging

### Development Tools
- Laravel Telescope for debugging
- Laravel Pulse for monitoring
- Comprehensive logging
- Error tracking

### Production Monitoring
- Queue monitoring via Horizon
- Performance metrics via Pulse
- Error logging
- API response tracking

## Integration Points

### Mobile App (React Native/Expo)
- RESTful API
- WebSocket connections via Pusher
- Push notifications via Expo
- Media upload/download

### Third-party Services
- Pusher for WebSockets
- AWS S3 for storage
- Video SDK for video calls
- Expo for push notifications

## Development Workflow

### Local Development
```bash
# Install dependencies
composer install
npm install

# Run development servers
composer run dev
```

### Testing
- PHPUnit/Pest for testing
- API endpoint testing
- Feature testing
- Unit testing

### Deployment
- Environment-based configuration
- Database migrations
- Queue worker setup
- WebSocket server configuration