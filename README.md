# YorYor - Muslim Dating & Matchmaking Platform

<div align="center">

![YorYor Logo](public/assets/images/premium_photo-1674235766088-80d8410f9523.jpeg)

**A comprehensive Islamic dating and matchmaking platform emphasizing cultural values, family involvement, and serious relationships.**

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)](https://php.net)
[![React](https://img.shields.io/badge/React-18+-61DAFB?logo=react)](https://react.dev)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-1.0-9553E9?logo=inertia)](https://inertiajs.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

[Features](#features) • [Quick Start](#quick-start) • [Documentation](#documentation) • [Tech Stack](#tech-stack)

</div>

---

## 📖 About YorYor

YorYor modernizes traditional matchmaking while respecting Islamic cultural and religious values. The platform combines cutting-edge technology with time-honored traditions of family involvement and serious commitment to marriage.

**Core Mission:** Facilitate meaningful connections for Muslims seeking marriage through technology-enhanced traditional matchmaking.

## ✨ Key Features

### 👤 User Management & Profiles
- **Multi-Section Profiles**: Basic, Cultural, Career, Physical, Family Preferences, Location
- **5-Type Verification System**: Identity, Photo, Employment, Education, Income
- **Privacy Controls**: Public, matches-only, or private profile visibility
- **Photo Management**: 2-10 photos with verification badges

### 💕 Matching & Discovery
- **AI-Powered Compatibility**: Advanced algorithm considering 6 weighted factors
- **Multiple Discovery Modes**: Swipe cards, grid view, advanced search
- **Smart Filters**: Age, distance, cultural compatibility, prayer frequency, career
- **Profile Boost & Super Likes**: Premium features for enhanced visibility

### 💬 Communication
- **Real-Time Messaging**: Powered by Laravel Reverb WebSocket
- **Rich Media Support**: Text, images, voice notes, videos, location sharing
- **Read Receipts & Typing Indicators**: Enhanced messaging experience
- **Video/Voice Calling**: Integrated VideoSDK with HD quality

### 🛡️ Safety & Privacy
- **Panic Button**: Emergency GPS location sharing with contacts & admin
- **Evidence-Based Reporting**: Comprehensive safety reporting system
- **User Blocking & Moderation**: AI-powered + manual content review
- **Emergency Contacts**: Up to 5 emergency contacts management
- **Family Involvement**: Family approval workflow for matches

### 👨‍👩‍👧 Unique Features
- **Professional Matchmakers**: Expert matchmaking services
- **Family Approval System**: Shared profile access with controlled permissions
- **Prayer Time Integration**: Islamic prayer notifications
- **Cultural Compatibility**: Religion, ethnicity, language preferences
- **Marriage Intent Verification**: Serious relationship focus

### 💎 Subscription Tiers
- **Free**: Basic features (50 likes/month, limited messages)
- **Premium**: Enhanced features (unlimited likes, advanced filters, read receipts)
- **Premium Plus**: All features (matchmaker access, verification fast-track, profile boost)

## 🚀 Quick Start

### Prerequisites
- PHP 8.4 or higher
- Composer
- Node.js 18+ & npm
- MySQL 8.0+ or SQLite (development)
- Redis 6.0+ (recommended for production)

### Installation

```bash
# Clone the repository
git clone https://github.com/yoryor/yoryor-dating-app.git
cd yoryor-dating-app

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Build assets
npm run build

# Start development services
composer dev  # Starts Laravel server, queue worker, Reverb, and Vite
```

Visit `http://localhost:8000` to access the application.

## 📚 Documentation

Comprehensive documentation is available in the `/docs` folder:

### 📱 API Documentation
- [API Endpoints Reference](docs/api/ENDPOINTS.md) - Complete API documentation
- [Authentication Guide](docs/api/AUTHENTICATION.md) - Auth flows & security
- [WebSockets (Reverb)](docs/api/WEBSOCKETS.md) - Real-time features
- [Mobile Integration](docs/api/MOBILE_INTEGRATION.md) - React Native/Expo guide

### 🌐 Web Documentation
- [React Components](docs/web/COMPONENTS.md) - Component catalog
- [Frontend Architecture](docs/web/FRONTEND_ARCHITECTURE.md) - Structure & patterns
- [Theming & Icons](docs/web/THEMING.md) - Dark mode & design system

### 🔧 Development
- [Getting Started](docs/development/GETTING_STARTED.md) - Setup & workflow
- [Architecture](docs/development/ARCHITECTURE.md) - System design
- [Database Schema](docs/development/DATABASE.md) - 70+ tables documentation
- [Service Layer](docs/development/SERVICES.md) - 25+ business services
- [Testing Guide](docs/development/TESTING.md) - Pest PHP testing

### 🚢 Deployment
- [Production Deployment](docs/deployment/PRODUCTION.md) - Complete deployment guide
- [Security & Infrastructure](docs/deployment/SECURITY.md) - Security hardening

### 🎯 Features
- [Features Overview](docs/features/OVERVIEW.md) - All 30+ features
- [Profile System](docs/features/PROFILES.md) - Multi-section profiles
- [Matching System](docs/features/MATCHING.md) - AI-powered matching
- [Chat & Messaging](docs/features/CHAT.md) - Real-time chat
- [Video Calling](docs/features/VIDEO_CALLING.md) - VideoSDK integration
- [Safety Features](docs/features/SAFETY.md) - Comprehensive safety

### 🔧 Maintenance
- [Code Quality Issues](docs/maintenance/CODE_QUALITY_ISSUES.md)
- [Security Audit](docs/maintenance/SECURITY_AUDIT.md)
- [Performance Improvements](docs/maintenance/PERFORMANCE_IMPROVEMENTS.md)

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 12
- **Language**: PHP 8.4+
- **Authentication**: Laravel Sanctum
- **Real-time**: Laravel Reverb (WebSocket)
- **Database**: MySQL/PostgreSQL (production), SQLite (development)
- **Cache & Queue**: Redis
- **Storage**: Cloudflare R2 (S3-compatible)

### Frontend
- **Framework**: React 18+ with Inertia.js 1.0
- **UI Library**: Custom React components with Tailwind CSS
- **CSS**: Tailwind CSS 4.0
- **JavaScript**: React 18+, Axios for HTTP requests
- **Icons**: Lucide Icons
- **Build Tool**: Vite 6.0

### Third-Party Services
- **Video Calling**: VideoSDK.live & Agora RTC
- **Push Notifications**: Expo Push Service
- **Image Processing**: Intervention Image
- **2FA**: Google Authenticator (TOTP)
- **Social Auth**: Google & Facebook OAuth

### DevOps & Monitoring
- **Testing**: Pest PHP 3.8
- **Code Quality**: Laravel Pint
- **Debugging**: Laravel Telescope
- **Monitoring**: Laravel Pulse
- **Queue Management**: Laravel Horizon
- **API Docs**: L5-Swagger (OpenAPI)

## 📊 Project Stats

- **70+ Database Tables**: Comprehensive data model
- **100+ API Endpoints**: RESTful API with JSON:API format
- **React Components**: Modern, reusable component library
- **25+ Service Classes**: Clean business logic layer
- **55+ Eloquent Models**: Rich domain models
- **15+ Rate Limit Types**: Granular rate limiting
- **3 Languages**: English, Uzbek, Russian

## 🔐 Security Features

- Bcrypt password hashing (12 rounds)
- Two-factor authentication (Google Authenticator)
- Account lockout after 5 failed attempts
- Rate limiting on all endpoints
- CSRF protection
- XSS prevention
- SQL injection protection
- HTTPS enforcement
- Security headers (HSTS, CSP, X-Frame-Options)
- GDPR compliance

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthTest.php

# Run with coverage
php artisan test --coverage

# Code formatting
./vendor/bin/pint
```

## 📋 Essential Commands

### Development
```bash
composer dev          # Start all services
php artisan serve     # Laravel server (port 8000)
php artisan reverb:start  # WebSocket server (port 8080)
npm run dev          # Vite dev server
```

### Database
```bash
php artisan migrate          # Run migrations
php artisan migrate:fresh --seed  # Fresh database with seeders
```

### Monitoring
```bash
# Access monitoring tools
http://localhost:8000/telescope  # Debugging
http://localhost:8000/pulse      # Performance
http://localhost:8000/horizon    # Queue monitoring
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP framework
- [Inertia.js](https://inertiajs.com) - Modern monolith approach
- [React](https://react.dev) - UI library
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS framework
- [VideoSDK](https://videosdk.live) - Video calling infrastructure
- [Cloudflare R2](https://www.cloudflare.com/products/r2/) - Object storage

## 📞 Support

For support and questions:
- 📧 Email: support@yoryor.com
- 📚 Documentation: [/docs](docs/)
- 🐛 Issues: [GitHub Issues](https://github.com/yoryor/yoryor-dating-app/issues)

---

<div align="center">

**Built with ❤️ for the Muslim community**

[Website](https://yoryor.com) • [Documentation](docs/) • [API Reference](docs/api/ENDPOINTS.md)

</div>
