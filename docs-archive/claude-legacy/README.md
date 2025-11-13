# YorYor Backend Documentation

Welcome to the comprehensive documentation for the YorYor backend system. This documentation provides detailed information about the Laravel-based API that powers the YorYor mobile dating application.

## 📚 Documentation Structure

### 1. [Backend Overview](./BACKEND_OVERVIEW.md)
A high-level overview of the entire backend system including:
- Technology stack and dependencies
- Core features and capabilities
- Database architecture
- Security features
- Performance optimizations
- Monitoring and debugging tools

### 2. [Backend Workflow](./BACKEND_WORKFLOW.md)
Detailed explanation of how the backend processes requests:
- Request lifecycle and middleware pipeline
- Core workflows (authentication, chat, video calling, matching)
- Background jobs and scheduled tasks
- Real-time features and WebSocket implementation
- API response patterns and error handling

### 3. [API Reference](./API_REFERENCE.md)
Complete API documentation including:
- All available endpoints
- Request/response formats
- Authentication requirements
- Rate limiting information
- WebSocket events
- Error codes and handling

### 4. [Mobile Integration Guide](./MOBILE_INTEGRATION_GUIDE.md)
Guide for integrating the React Native (Expo) mobile app:
- API configuration and setup
- Authentication implementation
- Real-time features with Pusher
- Push notifications with Expo
- Media upload handling
- Video calling integration
- Offline support and caching
- Performance optimization tips

### 5. [Optimization Guide](./OPTIMIZATION_GUIDE.md)
Performance optimization recommendations:
- Database query optimization
- Caching strategies
- API response optimization
- Queue optimization
- Real-time performance improvements
- Media handling optimization
- Implementation priorities and metrics

### 6. [Deployment Guide](./DEPLOYMENT_GUIDE.md)
Complete deployment instructions:
- Server requirements
- Environment setup
- Web server configuration (Nginx/Apache)
- Queue worker setup
- Security hardening
- Backup strategies
- Health checks and monitoring

## 🚀 Quick Start

1. **Development Setup**
   ```bash
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   composer run dev
   ```

2. **Key Technologies**
   - Laravel 12.x
   - PHP 8.2+
   - MySQL/PostgreSQL
   - Redis
   - Pusher (WebSockets)
   - AWS S3 (Media Storage)
   - Video SDK (Video Calling)
   - Expo (Push Notifications)

3. **Main Features**
   - Token-based authentication (Sanctum)
   - Real-time chat with media support
   - Video/voice calling
   - Matching algorithm
   - Push notifications
   - Media processing (images, videos, audio)
   - User presence tracking

## 📊 Architecture Highlights

- **Service-Oriented Architecture**: Business logic separated into service classes
- **Event-Driven**: Broadcasting system for real-time updates
- **API-First**: RESTful API design with consistent response formats
- **Scalable**: Redis caching, queue workers, and optimized database queries
- **Secure**: Rate limiting, authentication middleware, and security headers

## 🔧 Development Tools

- **Laravel Horizon**: Queue monitoring
- **Laravel Telescope**: Debugging and profiling
- **Laravel Pulse**: Application monitoring
- **Swagger/OpenAPI**: API documentation

## 📱 Mobile App Support

The backend is specifically designed to support the React Native (Expo) mobile application with:
- Optimized API responses for mobile bandwidth
- Push notification integration
- Real-time updates via WebSockets
- Efficient media handling and CDN support
- Offline-first considerations

## 🔒 Security Features

- JWT/Sanctum token authentication
- Two-factor authentication support
- Rate limiting on all endpoints
- Input validation and sanitization
- SQL injection protection
- XSS prevention
- HTTPS enforcement

## 📈 Performance Considerations

- Database query optimization with indexes
- Redis caching for frequently accessed data
- CDN integration for media files
- Queue workers for background processing
- Response compression
- Connection pooling

## 🚦 Getting Started

1. Review the [Backend Overview](./BACKEND_OVERVIEW.md) to understand the system
2. Check the [API Reference](./API_REFERENCE.md) for endpoint details
3. Follow the [Mobile Integration Guide](./MOBILE_INTEGRATION_GUIDE.md) for app development
4. Use the [Optimization Guide](./OPTIMIZATION_GUIDE.md) to improve performance
5. Deploy using the [Deployment Guide](./DEPLOYMENT_GUIDE.md)

## 📞 Support

For questions or issues:
- Check the documentation thoroughly
- Review error logs in `storage/logs`
- Use Laravel Telescope for debugging
- Monitor queues with Laravel Horizon

---

*This documentation is maintained alongside the YorYor backend codebase. Keep it updated as the system evolves.*