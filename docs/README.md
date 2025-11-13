# YorYor Documentation Hub

<div align="center">

**Complete technical documentation for the YorYor Muslim dating and matchmaking platform**

[API Docs](#-api-documentation) • [Web Docs](#-web-documentation) • [Development](#-development) • [Deployment](#-deployment) • [Features](#-features)

</div>

---

## 📚 Documentation Overview

This comprehensive documentation covers all aspects of the YorYor platform, from API integration to deployment strategies. Whether you're a frontend developer, backend engineer, mobile app developer, or DevOps professional, you'll find the resources you need here.

## 🎯 Quick Links

### For Mobile Developers
- [API Endpoints Reference](api/ENDPOINTS.md)
- [Authentication Guide](api/AUTHENTICATION.md)
- [Mobile Integration Guide](api/MOBILE_INTEGRATION.md)
- [WebSocket/Real-time Features](api/WEBSOCKETS.md)

### For Web Developers
- [Livewire Components Catalog](web/COMPONENTS.md)
- [Frontend Architecture](web/FRONTEND_ARCHITECTURE.md)
- [Theme System & Icons](web/THEMING.md)

### For Backend Developers
- [Getting Started](development/GETTING_STARTED.md)
- [System Architecture](development/ARCHITECTURE.md)
- [Service Layer Documentation](development/SERVICES.md)
- [Database Schema](development/DATABASE.md)

### For DevOps Engineers
- [Production Deployment](deployment/PRODUCTION.md)
- [Security & Infrastructure](deployment/SECURITY.md)

---

## 📱 API Documentation

Complete documentation for the YorYor RESTful API and real-time features.

### Core API Guides

| Document | Description |
|----------|-------------|
| **[ENDPOINTS.md](api/ENDPOINTS.md)** | Complete API reference with 100+ endpoints organized by feature |
| **[AUTHENTICATION.md](api/AUTHENTICATION.md)** | Auth flows: password, OTP, 2FA, social login, token management |
| **[WEBSOCKETS.md](api/WEBSOCKETS.md)** | Laravel Reverb WebSocket setup, channels, events, real-time features |
| **[MOBILE_INTEGRATION.md](api/MOBILE_INTEGRATION.md)** | React Native/Expo integration guide with code examples |

### Key Features

- ✅ **100+ REST API Endpoints**: Complete CRUD operations for all features
- ✅ **JSON:API Format**: Standardized response structure
- ✅ **Laravel Sanctum Auth**: Token-based authentication
- ✅ **Multi-Factor Authentication**: Password, OTP, 2FA support
- ✅ **Real-time Features**: Laravel Reverb WebSocket integration
- ✅ **Rate Limiting**: 15+ granular rate limit types
- ✅ **Comprehensive Examples**: cURL, JavaScript, React Native code samples

---

## 🌐 Web Documentation

Documentation for the Livewire-powered web application frontend.

### Web Application Guides

| Document | Description |
|----------|-------------|
| **[COMPONENTS.md](web/COMPONENTS.md)** | Complete catalog of 60+ Livewire components with usage examples |
| **[FRONTEND_ARCHITECTURE.md](web/FRONTEND_ARCHITECTURE.md)** | Frontend structure, JavaScript modules, CSS architecture |
| **[THEMING.md](web/THEMING.md)** | Dark/light mode system, Lucide icons, design tokens |

### Key Features

- ✅ **60+ Livewire Components**: Full-stack reactive components
- ✅ **Livewire Flux 2.1**: Premium UI component library
- ✅ **Tailwind CSS 4.0**: Utility-first CSS framework
- ✅ **Alpine.js Integration**: Lightweight client-side interactivity
- ✅ **Dark Mode Support**: System-aware theme switching
- ✅ **Lucide Icons**: 1000+ beautiful icons
- ✅ **Real-time Updates**: WebSocket integration with Laravel Echo

---

## 🔧 Development

Guides for developers contributing to the YorYor codebase.

### Development Guides

| Document | Description |
|----------|-------------|
| **[GETTING_STARTED.md](development/GETTING_STARTED.md)** | Installation, setup, essential commands, development workflow |
| **[ARCHITECTURE.md](development/ARCHITECTURE.md)** | Layered architecture, design patterns, service layer |
| **[DATABASE.md](development/DATABASE.md)** | Complete schema for 70+ tables, relationships, migrations |
| **[SERVICES.md](development/SERVICES.md)** | 25+ business service classes documentation |
| **[TESTING.md](development/TESTING.md)** | Pest PHP testing framework, writing tests, code coverage |

### Key Concepts

#### Layered Architecture
1. **Presentation Layer**: Livewire components + Blade views + API Resources
2. **Application Layer**: Controllers orchestrate requests
3. **Service Layer**: Business logic in dedicated services
4. **Domain Layer**: Eloquent models with relationships
5. **Data Access Layer**: Database, Cache, Queue

#### Critical Rules
- **Business logic in services**, not controllers
- **Transactions for multi-model operations**
- **Route organization**: api.php (API), web.php (public), user.php (auth), admin.php (admin)
- **Real-time with Laravel Reverb**, not Pusher
- **Storage with Cloudflare R2**, not AWS S3

---

## 🚢 Deployment

Production deployment and infrastructure documentation.

### Deployment Guides

| Document | Description |
|----------|-------------|
| **[PRODUCTION.md](deployment/PRODUCTION.md)** | Complete production deployment guide with all configurations |
| **[SECURITY.md](deployment/SECURITY.md)** | Security hardening, HTTPS setup, firewall rules, compliance |

### Deployment Highlights

- **System Requirements**: PHP 8.2+, MySQL 8.0+, Redis 6.0+, Nginx/Apache
- **Environment Configuration**: Complete .env setup for production
- **Laravel Reverb**: WebSocket server on port 8080
- **Cloudflare R2**: Object storage configuration
- **Queue Workers**: Supervisor configuration
- **Zero-Downtime Deployment**: Blue-green deployment scripts
- **SSL/HTTPS**: Let's Encrypt auto-renewal
- **Monitoring**: Telescope, Pulse, Horizon, Netdata
- **Backup Strategies**: Automated database and file backups
- **Security Headers**: HSTS, CSP, X-Frame-Options, etc.

---

## 🎯 Features

Detailed documentation for all platform features.

### Feature Documentation

| Document | Description |
|----------|-------------|
| **[OVERVIEW.md](features/OVERVIEW.md)** | Complete overview of 30+ features organized by category |
| **[PROFILES.md](features/PROFILES.md)** | Multi-section profiles, verification, privacy controls |
| **[MATCHING.md](features/MATCHING.md)** | AI-powered matching algorithm, discovery modes, filters |
| **[CHAT.md](features/CHAT.md)** | Real-time messaging, read receipts, typing indicators |
| **[VIDEO_CALLING.md](features/VIDEO_CALLING.md)** | VideoSDK integration, call features, call management |
| **[SAFETY.md](features/SAFETY.md)** | Panic button, reporting, verification, privacy controls |

### Platform Features

#### User Management & Profiles
- Multi-section profiles (Basic, Cultural, Career, Physical, Family, Location)
- 5-type verification system (Identity, Photo, Employment, Education, Income)
- Photo management (2-10 photos with verification)
- Profile completion tracking
- Privacy controls (public, matches-only, private)

#### Matching & Discovery
- AI-powered compatibility algorithm (6 weighted factors)
- Multiple discovery modes (swipe cards, grid view, advanced search)
- Smart filters (15+ criteria for Premium users)
- Profile boost & super likes (Premium features)
- Daily recommendations based on preferences

#### Communication
- Real-time messaging with Laravel Reverb
- Rich media support (text, images, voice notes, videos, location)
- Read receipts & typing indicators
- Video/voice calling with VideoSDK
- Message editing and deletion

#### Safety & Privacy
- Panic button with GPS location sharing
- Evidence-based reporting system
- User blocking & content moderation
- Emergency contacts (up to 5)
- Family approval workflow
- Professional matchmaker services

#### Monetization
- 3 subscription tiers (Free, Premium, Premium Plus)
- Feature-based limits (likes, messages, profile views)
- Payment processing
- Usage tracking and analytics

---

## 🔧 Maintenance

Technical debt, code quality, and improvement documentation.

### Maintenance Documentation

| Document | Description |
|----------|-------------|
| **[CODE_QUALITY_ISSUES.md](maintenance/CODE_QUALITY_ISSUES.md)** | Code quality issues, anti-patterns, refactoring opportunities |
| **[SECURITY_AUDIT.md](maintenance/SECURITY_AUDIT.md)** | Security vulnerabilities, audit findings, remediation plans |
| **[PERFORMANCE_IMPROVEMENTS.md](maintenance/PERFORMANCE_IMPROVEMENTS.md)** | Performance optimizations, caching strategies, query optimization |
| **[FILE_TREE_ANALYSIS.md](maintenance/FILE_TREE_ANALYSIS.md)** | File structure analysis and recommendations |
| **[FILE_TREE_IDEAL.md](maintenance/FILE_TREE_IDEAL.md)** | Ideal file organization structure |
| **[TODO_CLEANUP.md](maintenance/TODO_CLEANUP.md)** | Cleanup tasks and technical debt backlog |

---

## 📊 Project Statistics

- **70+ Database Tables**: Comprehensive data model
- **100+ API Endpoints**: RESTful API with JSON:API format
- **60+ Livewire Components**: Full-stack reactive components
- **25+ Service Classes**: Clean business logic layer
- **55+ Eloquent Models**: Rich domain models
- **15+ Rate Limit Types**: Granular rate limiting
- **3 Languages**: English, Uzbek, Russian
- **5 Verification Types**: Identity, Photo, Employment, Education, Income
- **3 Subscription Tiers**: Free, Premium, Premium Plus

---

## 🛠️ Technology Stack

### Backend
- Laravel 12, PHP 8.2+
- Laravel Sanctum (Authentication)
- Laravel Reverb (WebSocket)
- MySQL/PostgreSQL + Redis
- Cloudflare R2 (Storage)

### Frontend
- Livewire 3.6 (Full-stack)
- Flux 2.1 (UI Components)
- Tailwind CSS 4.0
- Alpine.js 3.14
- Vite 6.0

### Third-Party
- VideoSDK.live (Video calls)
- Expo Push (Notifications)
- Intervention Image (Processing)
- Google Authenticator (2FA)

---

## 🚀 Getting Started

### For New Developers

1. **Read** [Getting Started](development/GETTING_STARTED.md) for installation and setup
2. **Understand** [Architecture](development/ARCHITECTURE.md) for system design
3. **Explore** [Database Schema](development/DATABASE.md) for data model
4. **Learn** [Service Layer](development/SERVICES.md) for business logic patterns
5. **Review** [Testing Guide](development/TESTING.md) for quality assurance

### For API Integration

1. **Read** [API Endpoints](api/ENDPOINTS.md) for available endpoints
2. **Implement** [Authentication](api/AUTHENTICATION.md) for token management
3. **Integrate** [WebSockets](api/WEBSOCKETS.md) for real-time features
4. **Follow** [Mobile Integration](api/MOBILE_INTEGRATION.md) for mobile apps

### For Frontend Development

1. **Explore** [Components Catalog](web/COMPONENTS.md) for available components
2. **Understand** [Frontend Architecture](web/FRONTEND_ARCHITECTURE.md) for structure
3. **Implement** [Theming](web/THEMING.md) for UI consistency

### For Deployment

1. **Prepare** environment following [Production Guide](deployment/PRODUCTION.md)
2. **Secure** infrastructure with [Security Guide](deployment/SECURITY.md)
3. **Monitor** application health with provided tools

---

## 📖 Documentation Conventions

### File Naming
- Use `UPPERCASE.md` for documentation files
- Be descriptive: `AUTHENTICATION.md` not `AUTH.md`
- Group related docs in subdirectories

### Structure
- Start with overview and table of contents
- Include code examples with syntax highlighting
- Reference actual file locations (e.g., `app/Services/AuthService.php`)
- Add troubleshooting sections when applicable

### Code Examples
- Use real code from the codebase when possible
- Include request/response examples for APIs
- Show both correct and incorrect usage where helpful
- Add comments to explain complex logic

---

## 🤝 Contributing to Documentation

Found an error or want to improve the docs?

1. Check if the issue is already reported
2. Create a pull request with your changes
3. Follow the documentation conventions above
4. Include code examples when adding new features
5. Update the table of contents if adding new sections

---

## 📞 Support

For questions about the documentation:

- 📧 **Email**: support@yoryor.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/yoryor/yoryor-dating-app/issues)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/yoryor/yoryor-dating-app/discussions)

---

## 📝 Documentation Index

### API Documentation (4 files)
- [ENDPOINTS.md](api/ENDPOINTS.md) - API reference
- [AUTHENTICATION.md](api/AUTHENTICATION.md) - Auth guide
- [WEBSOCKETS.md](api/WEBSOCKETS.md) - Real-time features
- [MOBILE_INTEGRATION.md](api/MOBILE_INTEGRATION.md) - Mobile guide

### Web Documentation (3 files)
- [COMPONENTS.md](web/COMPONENTS.md) - Components catalog
- [FRONTEND_ARCHITECTURE.md](web/FRONTEND_ARCHITECTURE.md) - Frontend guide
- [THEMING.md](web/THEMING.md) - Theming & icons

### Development (5 files)
- [GETTING_STARTED.md](development/GETTING_STARTED.md) - Setup guide
- [ARCHITECTURE.md](development/ARCHITECTURE.md) - System design
- [DATABASE.md](development/DATABASE.md) - Database schema
- [SERVICES.md](development/SERVICES.md) - Service layer
- [TESTING.md](development/TESTING.md) - Testing guide

### Deployment (2 files)
- [PRODUCTION.md](deployment/PRODUCTION.md) - Deployment guide
- [SECURITY.md](deployment/SECURITY.md) - Security guide

### Features (6 files)
- [OVERVIEW.md](features/OVERVIEW.md) - Features overview
- [PROFILES.md](features/PROFILES.md) - Profile system
- [MATCHING.md](features/MATCHING.md) - Matching algorithm
- [CHAT.md](features/CHAT.md) - Messaging system
- [VIDEO_CALLING.md](features/VIDEO_CALLING.md) - Video calls
- [SAFETY.md](features/SAFETY.md) - Safety features

### Maintenance (6 files)
- [CODE_QUALITY_ISSUES.md](maintenance/CODE_QUALITY_ISSUES.md)
- [SECURITY_AUDIT.md](maintenance/SECURITY_AUDIT.md)
- [PERFORMANCE_IMPROVEMENTS.md](maintenance/PERFORMANCE_IMPROVEMENTS.md)
- [FILE_TREE_ANALYSIS.md](maintenance/FILE_TREE_ANALYSIS.md)
- [FILE_TREE_IDEAL.md](maintenance/FILE_TREE_IDEAL.md)
- [TODO_CLEANUP.md](maintenance/TODO_CLEANUP.md)

---

**Last Updated**: October 2025
**Version**: 1.0.0
**Maintainers**: YorYor Development Team
