# Database Seeders Documentation

This document describes the comprehensive database seeding system for the Yoryor Dating App.

## Overview

The seeding system creates a complete, realistic dataset for development and testing purposes, including:

- **500 Users** with complete profiles, photos, preferences, and cultural data
- **Countries** with phone codes, timezones, and regional settings
- **Subscription Plans** with multi-currency pricing
- **User Interactions** including likes, matches, chats, and messages
- **Additional Data** like notifications, subscriptions, calls, and safety features

## Seeder Files

### 1. DatabaseSeeder.php
**Main orchestrator** that runs all seeders in the correct order.

**Order of execution:**
1. RoleSeeder - Creates roles and permissions
2. CountrySeeder - Creates countries with regional data
3. SubscriptionPlanSeeder - Creates subscription plans and pricing
4. UserSeeder - Creates 500 users with complete profiles
5. UserStorySeeder - Creates additional user stories
6. AdditionalDataSeeder - Creates notifications, subscriptions, etc.

### 2. RoleSeeder.php
Creates user roles and permissions system.

**Creates:**
- **Roles:** admin, user, matchmaker
- **Permissions:** 13 different permissions for various actions
- **Role-Permission assignments**

### 3. CountrySeeder.php
Creates comprehensive country data with regional settings.

**Creates:**
- **195+ Countries** with complete data
- **Phone codes** and templates
- **Timezones** and time formats
- **Unit measurements** (metric/imperial)
- **Currency information**

### 4. SubscriptionPlanSeeder.php
Creates subscription plans with multi-currency pricing.

**Creates:**
- **4 Subscription Plans:** Free, Basic, Gold, Platinum
- **10 Features** with icons and descriptions
- **Multi-currency pricing** for 9 regions
- **Plan features** and limitations

### 5. UserSeeder.php
Creates 500 users with complete, realistic profiles.

**Creates per user:**
- **Basic Profile** with demographics and preferences
- **Cultural Profile** with religion, ethnicity, lifestyle
- **Family Preferences** with marriage intentions and children
- **Location Preferences** with immigration status and relocation plans
- **Career Profile** with education and work information
- **Physical Profile** with body type, fitness, and habits
- **User Photos** (1-5 photos per user)
- **User Stories** (0-3 Instagram-like stories)
- **Device Tokens** for push notifications
- **Emergency Contacts** (0-3 contacts per user)
- **Matchmaker Data** (5% of users become matchmakers)
- **Verification Data** (30% of users have verification)
- **User Interactions** (likes, matches, chats, messages)

### 6. UserStorySeeder.php
Creates additional user stories for Instagram-like functionality.

**Creates:**
- **1-3 Stories per user** with random content
- **Active and expired stories** with realistic timing
- **Image URLs** and captions
- **Expiration times** (1-24 hours for active stories)

### 7. AdditionalDataSeeder.php
Creates comprehensive additional data for realistic app usage.

**Creates:**
- **User Subscriptions** (70% of users have subscriptions)
- **Notifications** (3-8 per user with various types)
- **User Blocks & Reports** (20% block, 10% report)
- **User Activities** (5-15 activities per user)
- **User Settings** (privacy, notifications, preferences)
- **Usage Data** (limits and monthly usage tracking)
- **Payment Transactions** (60% of users have transactions)
- **Calls** (40% of users have made calls)
- **Safety Data** (safety scores, panic activations)
- **Feedback & Exports** (user feedback and data export requests)

## Data Statistics

After running all seeders, you'll have:

- **500 Users** with complete profiles
- **195+ Countries** with regional data
- **4 Subscription Plans** with multi-currency pricing
- **1,000+ Likes** between users
- **500+ Matches** from mutual likes
- **500+ Chats** between matched users
- **5,000+ Messages** with realistic conversations
- **1,500+ User Stories** (Instagram-like)
- **2,500+ Notifications** of various types
- **350+ User Subscriptions** with different plans
- **1,500+ Payment Transactions** across currencies
- **2,000+ Calls** (video and voice)
- **100+ User Blocks** and reports
- **5,000+ User Activities** for analytics
- **500 User Settings** for preferences
- **500 Usage Limits** and monthly usage data
- **100+ Safety Scores** and panic activations
- **150+ Feedback** and export requests

## Usage

### Run All Seeders
```bash
php artisan db:seed
```

### Run Individual Seeders
```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=CountrySeeder
php artisan db:seed --class=SubscriptionPlanSeeder
```

### Fresh Database with Seeders
```bash
php artisan migrate:fresh --seed
```

## Features

### Realistic Data
- **Realistic names** and demographics
- **Authentic conversations** with dating app context
- **Proper relationships** between users and data
- **Realistic timing** for activities and interactions

### Cultural Sensitivity
- **Multi-language support** (English, Russian, Uzbek, Arabic, Hebrew)
- **Religious diversity** with appropriate cultural data
- **Regional preferences** for different countries
- **Cultural profiles** with traditional and modern lifestyles

### Complete User Journey
- **Profile creation** with all required fields
- **Photo uploads** with verification status
- **Matching process** with likes and mutual matches
- **Messaging system** with realistic conversations
- **Subscription management** with different plans
- **Safety features** with panic buttons and emergency contacts

### Analytics Ready
- **User activities** for behavior analysis
- **Usage statistics** for subscription management
- **Payment data** for revenue tracking
- **Safety scores** for user verification
- **Feedback system** for improvement

## Customization

### Adding More Users
Edit `UserSeeder.php` and change the loop from `500` to your desired number.

### Adding More Countries
Edit `CountrySeeder.php` and add more countries to the `$countries` array.

### Adding More Subscription Plans
Edit `SubscriptionPlanSeeder.php` and add more plans to the `$plans` array.

### Customizing User Data
Edit the data arrays in `UserSeeder.php` to customize:
- Genders, interests, professions
- Cultural data, religions, ethnicities
- Family preferences, career data
- Physical profiles, lifestyle choices

## Performance

The seeding process is optimized for performance:
- **Batch operations** where possible
- **Progress bars** for long-running operations
- **Memory management** for large datasets
- **Error handling** for robust execution

## Troubleshooting

### Common Issues
1. **Memory limits** - Increase PHP memory limit for large datasets
2. **Timeout issues** - Increase execution time for long-running seeders
3. **Foreign key constraints** - Ensure seeders run in correct order
4. **Duplicate data** - Use `firstOrCreate` instead of `create` where appropriate

### Debug Mode
Add `DB::enableQueryLog()` to see SQL queries being executed.

## Maintenance

### Updating Data
- **User data** can be updated by re-running UserSeeder
- **Countries** are static and rarely change
- **Subscription plans** should be updated carefully in production

### Adding New Features
- **New seeders** should be added to DatabaseSeeder in correct order
- **New models** should be included in statistics reporting
- **New relationships** should be handled in existing seeders

This comprehensive seeding system provides a realistic, production-ready dataset for development, testing, and demonstration purposes.
