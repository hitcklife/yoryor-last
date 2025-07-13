# Profile API Optimization and Enhancement

## Overview
This document outlines the comprehensive optimization and enhancement of the profile API system, including new blocking and reporting functionality.

## New Features Implemented

### 1. User Blocking System
- **Models**: `UserBlock` model with proper relationships
- **Database**: `user_blocks` table with foreign key constraints
- **API Endpoint**: `POST /api/v1/users/{userId}/block`
- **Features**:
  - Block users with optional reason
  - Prevent blocked users from viewing each other's profiles
  - Automatically remove matches when blocking occurs
  - Duplicate block prevention

### 2. User Reporting System
- **Models**: `UserReport` model with proper relationships
- **Database**: `user_reports` table with status tracking
- **API Endpoint**: `POST /api/v1/users/{userId}/report`
- **Features**:
  - Report users with predefined reasons
  - Optional description and metadata
  - Status tracking (pending, reviewing, resolved, dismissed)
  - Duplicate report prevention

### 3. Profile Photo System Optimization
- **Enhanced**: Profile photo handling with centralized logic
- **Added**: Helper methods in User model for profile photo URLs
- **Optimized**: Fallback system for legacy `profile_photo_path` field
- **Features**:
  - Multiple photo sizes (thumbnail, medium, original)
  - Smart fallback to legacy field when needed
  - Consistent photo URL generation

### 4. Enhanced Profile Viewing
- **New Endpoint**: `GET /api/v1/users/{userId}/profile`
- **Features**:
  - View other users' profiles (matched users only)
  - Profile view tracking
  - Block/report status information
  - Privacy-aware photo display

## API Endpoints

### Get User Profile
```
GET /api/v1/users/{userId}/profile
```
**Authorization**: Bearer token required  
**Access**: Only matched users can view each other's profiles  
**Response**: Profile data with photos, match status, and interaction history

### Block User
```
POST /api/v1/users/{userId}/block
```
**Authorization**: Bearer token required  
**Body**:
```json
{
  "reason": "harassment" // Optional
}
```

### Report User
```
POST /api/v1/users/{userId}/report
```
**Authorization**: Bearer token required  
**Body**:
```json
{
  "reason": "inappropriate_content", // Required
  "description": "Additional details", // Optional
  "metadata": {} // Optional
}
```

### Get Report Reasons
```
GET /api/v1/report-reasons
```
**Authorization**: Bearer token required  
**Response**: List of available report reasons

## Database Schema

### user_blocks
```sql
- id (bigint, primary key)
- blocker_id (bigint, foreign key to users)
- blocked_id (bigint, foreign key to users)
- reason (varchar, nullable)
- created_at (timestamp)
- updated_at (timestamp)
- unique index on (blocker_id, blocked_id)
```

### user_reports
```sql
- id (bigint, primary key)
- reporter_id (bigint, foreign key to users)
- reported_id (bigint, foreign key to users)
- reason (varchar, required)
- description (text, nullable)
- status (enum: pending, reviewing, resolved, dismissed)
- metadata (json, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

## Model Relationships

### User Model Additions
```php
// Blocking relationships
public function blockedUsers(): HasMany
public function blockedBy(): HasMany

// Reporting relationships
public function reportsMade(): HasMany
public function reportsReceived(): HasMany

// Helper methods
public function hasBlocked(User $user): bool
public function isBlockedBy(User $user): bool
public function hasReported(User $user): bool
public function canViewProfile(User $user): bool

// Profile photo helpers
public function getProfilePhotoUrl(string $size = 'medium'): ?string
```

## Security Features

### Privacy Protection
- Users cannot view profiles of blocked users
- Blocked users cannot view blocker's profile
- Profile photos respect privacy settings
- Only matched users can view detailed profiles

### Validation
- Prevent self-blocking and self-reporting
- Duplicate action prevention
- Input validation and sanitization
- Rate limiting considerations

## Report Reasons
- `inappropriate_content`: Inappropriate Content
- `harassment`: Harassment or Bullying
- `spam`: Spam or Fake Profile
- `inappropriate_photos`: Inappropriate Photos
- `scam`: Scam or Fraud
- `underage`: Underage User
- `violence`: Violence or Threats
- `hate_speech`: Hate Speech
- `other`: Other

## Error Handling

### Common Error Codes
- `profile_blocked`: User cannot view blocked profile
- `not_matched`: Users must be matched to view profiles
- `already_blocked`: User is already blocked
- `already_reported`: User has already been reported
- `cannot_block_self`: Cannot block yourself
- `cannot_report_self`: Cannot report yourself

## Performance Optimizations

### Database Indexing
- Composite indexes on user relationships
- Performance indexes on created_at for sorting
- Foreign key constraints for data integrity

### Query Optimization
- Eager loading of relationships
- Selective field loading
- Efficient existence checks

## Migration Commands
```bash
# Run the new migrations
php artisan migrate --path=database/migrations/2025_07_11_165350_create_user_blocks_table.php
php artisan migrate --path=database/migrations/2025_07_11_165357_create_user_reports_table.php
```

## Testing Considerations

### Test Cases to Implement
1. Block user functionality
2. Report user functionality
3. Profile viewing permissions
4. Privacy enforcement
5. Duplicate action prevention
6. Match removal on blocking
7. Profile photo fallback system

## Future Enhancements

### Potential Additions
- Unblock functionality
- Report management dashboard
- Automated moderation
- Block/report statistics
- Appeal system for reports
- Bulk moderation tools

## Backward Compatibility

The optimization maintains backward compatibility with:
- Existing profile photo URLs
- Legacy `profile_photo_path` field
- Current profile API responses
- Existing mobile app integration

## Conclusion

This optimization provides a comprehensive profile management system with enhanced security, privacy, and user safety features while maintaining performance and backward compatibility. 