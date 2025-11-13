# YorYor Dating App - Database Migration Documentation

## Overview

This documentation provides a comprehensive guide to the database schema for the YorYor Dating Application. All migrations have been consolidated from 72+ individual migration files into organized, logical groups that can be run cleanly on a fresh database.

## Table of Contents

1. **[Core Tables](01-core-tables.md)** - Essential system tables
   - Users, Countries, Profiles, OTP Codes, Sessions, Password Resets, Personal Access Tokens

2. **[Profile Extensions](02-profile-extensions.md)** - Detailed user information
   - Cultural Profiles, Family Preferences, Career Profiles, Physical Profiles, Location Preferences, User Preferences

3. **[Chat & Messaging](03-chat-messaging.md)** - Communication system
   - Chats, Chat Users, Messages, Message Reads, Calls, Media, Notifications

4. **[Matching System](04-matching-system.md)** - User interactions
   - Matches, Likes, Dislikes, User Photos, User Stories, User Activities, Device Tokens

5. **[User Settings](05-user-settings.md)** - Comprehensive settings
   - Single unified settings table with all preferences

6. **[Subscription & Payment](06-subscription-payment.md)** - Monetization
   - Subscription Plans, Plan Pricing, User Subscriptions, Payment Transactions, Usage Limits

7. **[Safety & Moderation](07-safety-moderation.md)** - User protection
   - User Blocks, Reports, Emergency Contacts, Panic System, Safety Scores, Feedback

8. **[Matchmaker System](08-matchmaker-system.md)** - Professional services
   - Matchmakers, Services, Clients, Introductions, Reviews, Availability, Consultations

9. **[Additional Features](09-additional-features.md)** - Advanced features
   - AI Compatibility, Verification System, Family Approval, Prayer Times, Data Export, Laravel System Tables

10. **[Authentication & Roles](10-auth-roles.md)** - Access control
    - Roles, Permissions, Role-User relationships, Telescope, Pulse monitoring

## Migration Strategy

### Fresh Installation

For a fresh installation, run migrations in this order:

```bash
# 1. Core tables first
php artisan migrate --path=database/migrations/core

# 2. Profile extensions
php artisan migrate --path=database/migrations/profiles

# 3. Chat and messaging
php artisan migrate --path=database/migrations/chat

# 4. Matching system
php artisan migrate --path=database/migrations/matching

# 5. User settings
php artisan migrate --path=database/migrations/settings

# 6. Subscription and payment
php artisan migrate --path=database/migrations/subscription

# 7. Safety and moderation
php artisan migrate --path=database/migrations/safety

# 8. Matchmaker system
php artisan migrate --path=database/migrations/matchmaker

# 9. Additional features
php artisan migrate --path=database/migrations/features

# 10. Authentication and roles
php artisan migrate --path=database/migrations/auth
```

Or simply run all at once:

```bash
php artisan migrate:fresh --seed
```

### Migration Organization

Create the following directory structure:

```
database/
└── migrations/
    ├── core/           # Core system tables
    ├── profiles/       # User profile extensions
    ├── chat/          # Chat and messaging
    ├── matching/      # Matching and interactions
    ├── settings/      # User settings
    ├── subscription/  # Payment system
    ├── safety/        # Safety features
    ├── matchmaker/    # Professional matchmaking
    ├── features/      # Additional features
    └── auth/          # Roles and permissions
```

## Key Design Decisions

### 1. Consolidated Migrations
- All table modifications are merged into single CREATE statements
- Eliminates migration conflicts and ordering issues
- Provides clear final schema state

### 2. Extensive Indexing
- Performance-optimized indexes on all frequently queried columns
- Composite indexes for complex queries
- Unique constraints where appropriate

### 3. JSON/JSONB Columns
- Used for flexible, evolving data structures
- Preferences, features, metadata storage
- Reduces need for additional tables

### 4. Soft Deletes
- Implemented on critical tables (users, chats, messages)
- Allows data recovery and audit trails
- Maintains referential integrity

### 5. Foreign Key Constraints
- Cascade deletes where appropriate
- Set null for optional relationships
- Ensures data consistency

## Database Statistics

### Total Tables: 80+

#### By Category:
- **Core System**: 7 tables
- **Profile Extensions**: 6 tables
- **Chat & Messaging**: 7 tables
- **Matching System**: 7 tables
- **User Settings**: 1 comprehensive table
- **Subscription**: 6 tables
- **Safety**: 9 tables
- **Matchmaker**: 7 tables
- **Additional Features**: 15+ tables
- **Auth & Monitoring**: 10+ tables

### Key Relationships
- **One-to-One**: 15+ relationships (user profiles, settings)
- **One-to-Many**: 30+ relationships (messages, activities)
- **Many-to-Many**: 10+ relationships (matches, roles)

### Performance Features
- **Indexes**: 150+ indexes for query optimization
- **Composite Indexes**: 40+ multi-column indexes
- **Unique Constraints**: 30+ unique constraints
- **Check Constraints**: Age limits, score ranges

## Development Workflow

### 1. Local Development
```bash
# Reset database with fresh migrations
php artisan migrate:fresh --seed

# Run specific migration file
php artisan migrate --path=database/migrations/core/2025_01_01_create_users_table.php
```

### 2. Testing
```bash
# Use in-memory SQLite for tests
DB_CONNECTION=sqlite
DB_DATABASE=:memory:

# Run migrations in test
php artisan migrate --env=testing
```

### 3. Production Deployment
```bash
# Run migrations with force flag
php artisan migrate --force

# Check migration status
php artisan migrate:status
```

## Maintenance Commands

### Database Optimization
```bash
# Optimize tables and indexes
php artisan db:optimize

# Analyze table statistics
php artisan db:analyze
```

### Backup Strategy
```bash
# Before major migrations
php artisan backup:run --only-db

# Export schema only
mysqldump -d yoryor_db > schema.sql
```

## Common Patterns

### 1. Status Enums
Most tables use consistent status values:
- `pending`, `active`, `completed`, `cancelled`
- `pending`, `approved`, `rejected`
- `pending`, `reviewing`, `resolved`, `dismissed`

### 2. Timestamp Patterns
- `created_at`, `updated_at` - Laravel defaults
- `deleted_at` - Soft deletes
- `*_at` - Action timestamps (verified_at, completed_at)

### 3. JSON Structure Examples

**User Preferences**:
```json
{
  "notification_types": ["matches", "messages", "likes"],
  "quiet_hours": {"start": "22:00", "end": "08:00"},
  "discovery_filters": ["verified_only", "recently_active"]
}
```

**Matchmaker Specializations**:
```json
{
  "age_groups": ["25-35", "35-45"],
  "religions": ["muslim", "christian"],
  "communities": ["uzbek", "russian"]
}
```

## Best Practices

1. **Always use transactions** for multi-table operations
2. **Index foreign keys** for JOIN performance
3. **Use appropriate column types** (JSONB for complex data, ENUMs for fixed options)
4. **Implement soft deletes** on user-facing data
5. **Add check constraints** for data validation
6. **Use composite indexes** for multi-column queries
7. **Document complex relationships** in migrations

## Troubleshooting

### Common Issues

1. **Foreign key constraint failures**
   - Ensure parent records exist
   - Check cascade rules
   - Verify data types match

2. **Duplicate key errors**
   - Check unique constraints
   - Verify composite unique indexes

3. **Migration rollback issues**
   - Some operations aren't reversible (data changes)
   - Always backup before major migrations

## Future Considerations

### Potential Enhancements
1. **Partitioning** for large tables (messages, activities)
2. **Read replicas** for reporting queries
3. **Caching layer** for frequently accessed data
4. **Archive strategy** for old data
5. **Sharding** for horizontal scaling

### Schema Evolution
- Use feature flags for gradual rollouts
- Plan for backward compatibility
- Document breaking changes
- Maintain migration versioning

## Support

For questions or issues with the database schema:
1. Check the individual migration documentation files
2. Review the consolidated migration files
3. Test in development environment first
4. Document any schema modifications

---

*Last Updated: January 2025*
*Version: 1.0.0*