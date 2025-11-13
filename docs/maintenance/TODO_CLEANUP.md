# TODO Cleanup - YorYor Dating App

This document provides a **prioritized, actionable cleanup plan** with specific commands to transform the current project structure into the ideal structure outlined in `FILE_TREE_IDEAL.md`.

---

## 📋 Table of Contents

1. [Priority Overview](#priority-overview)
2. [Phase 1: Critical Cleanup (1 hour)](#phase-1-critical-cleanup-1-hour)
3. [Phase 2: Component Reorganization (2-3 hours)](#phase-2-component-reorganization-2-3-hours)
4. [Phase 3: Add Missing Components (1 week)](#phase-3-add-missing-components-1-week)
5. [Phase 4: Testing Infrastructure (2 weeks)](#phase-4-testing-infrastructure-2-weeks)
6. [Safety Checks](#safety-checks)
7. [Verification Commands](#verification-commands)

---

## Priority Overview

| Priority | Tasks | Time Estimate | Impact |
|----------|-------|---------------|--------|
| 🔴 **Critical** | Delete temporary files, remove unused components | 1 hour | Immediate clarity, reduced confusion |
| 🟡 **High** | Reorganize services, create missing validations | 2-3 hours | Better maintainability |
| 🟢 **Medium** | Add missing jobs/notifications, create helpers | 1 week | Long-term scalability |
| 🔵 **Low** | Complete test coverage, add documentation | 2 weeks | Code quality & confidence |

---

## Phase 1: Critical Cleanup (1 hour)

### Step 1.1: Delete Temporary Setup Files (5 minutes)

**Files to delete:**
- `populate_migrations.php`
- `populate_all_migrations.php`
- `populate_remaining_migrations.php`
- `populate_migrations.py`
- `setup-migrations.sh`

```bash
# Navigate to project root
cd /Users/khurshidjumaboev/Desktop/yoryor/yoryor-last

# Delete temporary migration setup files
rm -f populate_migrations.php \
      populate_all_migrations.php \
      populate_remaining_migrations.php \
      populate_migrations.py \
      setup-migrations.sh

# Verify deletion
ls -la | grep -E "populate|setup-migrations"
```

**Expected output:** No results (files deleted)

---

### Step 1.2: Remove Unused Shell Scripts (2 minutes)

**Files to delete:**
- `run-chat-optimizations.sh`
- `setup-chat-v2-optimizations.sh`

```bash
# Delete unused shell scripts
rm -f run-chat-optimizations.sh setup-chat-v2-optimizations.sh

# Verify deletion
ls -la *.sh
```

---

### Step 1.3: Delete Migrations Backup Folder (3 minutes)

**Folder to delete:**
- `database/migrations_backup/` (40+ old migration files)

**⚠️ WARNING:** Ensure you have git history or a separate backup before proceeding.

```bash
# Check if folder exists
ls -la database/ | grep migrations_backup

# Create a final backup (optional, if not in git)
tar -czf ~/yoryor-migrations-backup-$(date +%Y%m%d).tar.gz database/migrations_backup/

# Delete the folder
rm -rf database/migrations_backup/

# Verify deletion
ls -la database/ | grep migrations_backup
```

---

### Step 1.4: Remove Unclear/Unknown Directories (10 minutes)

**Directories to investigate and potentially delete:**
- `claude/` - Unknown purpose
- `Users/` - Likely a mistake (macOS directory)
- `docs/` - Duplicate of `documentation/`

```bash
# Investigate claude/ folder
ls -R claude/

# If it's development notes/scratch files, delete it
# Otherwise, move to documentation/claude-notes/
rm -rf claude/  # OR: mv claude/ documentation/claude-notes/

# Investigate Users/ folder (likely an error)
ls -R Users/

# If it's a mistake, delete it
rm -rf Users/

# Check docs/ folder
ls -R docs/

# If it duplicates documentation/, merge or delete
# Compare folders first
diff -r docs/ documentation/ || true

# If different, merge useful content into documentation/
# If identical, delete docs/
rm -rf docs/  # Use with caution after comparing
```

---

### Step 1.5: Remove Unused Livewire Components (20 minutes)

**Components to delete** (unused starter kit components):

```bash
# Navigate to Livewire directory
cd app/Livewire

# Delete unused component directories (from starter kit)
rm -rf Checkout/ \
       Coupon/ \
       Customer/ \
       Dashboard/Category/ \
       Dashboard/Order/ \
       Dashboard/Product/ \
       Dashboard/Stats/ \
       Dashboard/Transaction/ \
       EditProduct/ \
       Invoice/ \
       OrderDetail/ \
       PaymentForm/ \
       ProductCard/ \
       ProductList/ \
       ShoppingCart/ \
       UserAccount/

# Navigate back to project root
cd /Users/khurshidjumaboev/Desktop/yoryor/yoryor-last

# Verify deletion
ls -R app/Livewire/
```

**Expected remaining components:**
- `Admin/`
- `Auth/`
- `Components/`
- `Dashboard/`
- `Pages/`
- `Profile/`
- `Settings/`
- `ComingSoon.php`
- `NewsletterSignup.php`
- `ThemeSwitcher.php`

---

### Step 1.6: Clean Up Root Directory Files (5 minutes)

**Files to review/delete:**
- `.DS_Store` (macOS metadata)
- Unused markdown files in root

```bash
# Delete .DS_Store files (macOS)
find . -name ".DS_Store" -type f -delete

# List markdown files in root
ls -la *.md

# Review each file:
# - Keep: README.md, CHANGELOG.md, CONTRIBUTING.md
# - Move to documentation/: LUCIDE_ICONS_GUIDE.md, SECURE_PROFILE_SYSTEM.md,
#                          THEME_SYSTEM_GUIDE.md, VIDEOSDK_SETUP.md

mv LUCIDE_ICONS_GUIDE.md documentation/technical/
mv SECURE_PROFILE_SYSTEM.md documentation/technical/
mv THEME_SYSTEM_GUIDE.md documentation/technical/
mv VIDEOSDK_SETUP.md documentation/technical/

# Create technical subfolder if needed
mkdir -p documentation/technical/
```

---

### Step 1.7: Clean Git Status (5 minutes)

```bash
# Check current git status
git status

# Review all changes
git diff

# Add deleted files to staging
git add -u

# Commit cleanup changes
git commit -m "chore: cleanup temporary files and unused components

- Remove temporary migration setup files (populate_*.php, setup-*.sh)
- Delete unused shell scripts (run-chat-optimizations.sh, setup-chat-v2-optimizations.sh)
- Remove migrations_backup/ folder (40+ old files)
- Delete unused Livewire starter kit components
- Clean up unclear directories (claude/, Users/, docs/)
- Move technical documentation to documentation/technical/
- Remove .DS_Store files

🤖 Generated with Claude Code
Co-Authored-By: Claude <noreply@anthropic.com>"
```

---

## Phase 2: Component Reorganization (2-3 hours)

### Step 2.1: Reorganize Service Layer (45 minutes)

**Goal:** Organize services into logical subdirectories

```bash
# Create service subdirectories
mkdir -p app/Services/Auth
mkdir -p app/Services/Communication
mkdir -p app/Services/Media
mkdir -p app/Services/Matching
mkdir -p app/Services/Payment
mkdir -p app/Services/Safety
mkdir -p app/Services/Video
mkdir -p app/Services/Core

# Move Auth services
git mv app/Services/AuthService.php app/Services/Auth/
git mv app/Services/OtpService.php app/Services/Auth/
git mv app/Services/TwoFactorAuthService.php app/Services/Auth/

# Move Communication services
git mv app/Services/PresenceService.php app/Services/Communication/

# Move Media services
git mv app/Services/MediaUploadService.php app/Services/Media/
git mv app/Services/ImageProcessingService.php app/Services/Media/

# Move Payment services
git mv app/Services/PaymentManager.php app/Services/Payment/
mv app/Services/Payment/*.php app/Services/Payment/ || true  # If payment subdirectory exists

# Move Safety services
git mv app/Services/PanicButtonService.php app/Services/Safety/
git mv app/Services/VerificationService.php app/Services/Safety/
git mv app/Services/EnhancedReportingService.php app/Services/Safety/

# Move Video services
# (Note: These might not exist yet, skip if not found)
test -f app/Services/VideoSDKService.php && git mv app/Services/VideoSDKService.php app/Services/Video/
test -f app/Services/AgoraService.php && git mv app/Services/AgoraService.php app/Services/Video/

# Move Core services
git mv app/Services/CacheService.php app/Services/Core/
git mv app/Services/ErrorHandlingService.php app/Services/Core/
git mv app/Services/ValidationService.php app/Services/Core/
git mv app/Services/PrivacyService.php app/Services/Core/
git mv app/Services/UsageLimitsService.php app/Services/Core/

# Move AI services
test -d app/Services/AI && echo "AI services already organized"

# Move remaining specialized services
git mv app/Services/MatchmakerService.php app/Services/Matching/ || true
git mv app/Services/FamilyApprovalService.php app/Services/Matching/ || true
git mv app/Services/PrayerTimeService.php app/Services/Core/ || true
```

**⚠️ Important:** After moving services, update namespaces in each file:

```bash
# Example: Update AuthService namespace
# Old: namespace App\Services;
# New: namespace App\Services\Auth;

# Use sed to update namespaces (macOS compatible)
find app/Services -type f -name "*.php" -exec sed -i '' 's/namespace App\\Services;/namespace App\\Services\\Auth;/g' {} + -path "app/Services/Auth/*"
```

**Manual tasks:**
1. Update all service imports across the codebase
2. Update service provider bindings in `app/Providers/AppServiceProvider.php`
3. Run tests to ensure nothing broke

---

### Step 2.2: Create Missing Request Validation Classes (1 hour)

**Goal:** Create Form Request validation classes for all major operations

```bash
# Create Requests subdirectories
mkdir -p app/Http/Requests/Auth
mkdir -p app/Http/Requests/Profile
mkdir -p app/Http/Requests/Chat
mkdir -p app/Http/Requests/Match
mkdir -p app/Http/Requests/Media

# Generate Request classes
php artisan make:request Auth/LoginRequest
php artisan make:request Auth/RegisterRequest
php artisan make:request Auth/VerifyEmailRequest
php artisan make:request Auth/ResetPasswordRequest
php artisan make:request Auth/TwoFactorVerifyRequest

php artisan make:request Profile/UpdateBasicInfoRequest
php artisan make:request Profile/UpdateCulturalProfileRequest
php artisan make:request Profile/UpdateCareerProfileRequest
php artisan make:request Profile/UpdatePhysicalProfileRequest
php artisan make:request Profile/UpdatePreferencesRequest
php artisan make:request Profile/UpdateLocationRequest

php artisan make:request Chat/SendMessageRequest
php artisan make:request Chat/CreateChatRequest
php artisan make:request Chat/DeleteMessageRequest

php artisan make:request Match/LikeUserRequest
php artisan make:request Match/DislikeUserRequest
php artisan make:request Match/UnmatchRequest

php artisan make:request Media/UploadPhotoRequest
php artisan make:request Media/DeletePhotoRequest
php artisan make:request Media/CreateStoryRequest

# Verify creation
ls -R app/Http/Requests/
```

**Manual task:** Populate each Request class with validation rules from controllers

---

### Step 2.3: Organize API Controllers into Subdirectories (30 minutes)

**Goal:** Group related controllers

```bash
# Create API controller subdirectories
mkdir -p app/Http/Controllers/Api/V1/Auth
mkdir -p app/Http/Controllers/Api/V1/Profile
mkdir -p app/Http/Controllers/Api/V1/Chat
mkdir -p app/Http/Controllers/Api/V1/Match
mkdir -p app/Http/Controllers/Api/V1/Settings
mkdir -p app/Http/Controllers/Api/V1/Media
mkdir -p app/Http/Controllers/Api/V1/Video

# Move Auth controllers
git mv app/Http/Controllers/Api/V1/AuthController.php app/Http/Controllers/Api/V1/Auth/
git mv app/Http/Controllers/Api/V1/DeviceTokenController.php app/Http/Controllers/Api/V1/Auth/
git mv app/Http/Controllers/Api/V1/VerificationController.php app/Http/Controllers/Api/V1/Auth/

# Move Profile controllers
git mv app/Http/Controllers/Api/V1/ProfileController.php app/Http/Controllers/Api/V1/Profile/
git mv app/Http/Controllers/Api/V1/ComprehensiveProfileController.php app/Http/Controllers/Api/V1/Profile/
git mv app/Http/Controllers/Api/V1/CulturalProfileController.php app/Http/Controllers/Api/V1/Profile/
git mv app/Http/Controllers/Api/V1/CareerProfileController.php app/Http/Controllers/Api/V1/Profile/
git mv app/Http/Controllers/Api/V1/PhysicalProfileController.php app/Http/Controllers/Api/V1/Profile/
git mv app/Http/Controllers/Api/V1/FamilyPreferenceController.php app/Http/Controllers/Api/V1/Profile/
git mv app/Http/Controllers/Api/V1/LocationPreferenceController.php app/Http/Controllers/Api/V1/Profile/
git mv app/Http/Controllers/Api/V1/UserPhotoController.php app/Http/Controllers/Api/V1/Profile/

# Move Chat controllers
git mv app/Http/Controllers/Api/V1/ChatController.php app/Http/Controllers/Api/V1/Chat/

# Move Match controllers
git mv app/Http/Controllers/Api/V1/LikeController.php app/Http/Controllers/Api/V1/Match/
git mv app/Http/Controllers/Api/V1/MatchController.php app/Http/Controllers/Api/V1/Match/

# Move Settings controllers
git mv app/Http/Controllers/Api/V1/SettingsController.php app/Http/Controllers/Api/V1/Settings/
git mv app/Http/Controllers/Api/V1/AccountController.php app/Http/Controllers/Api/V1/Settings/
git mv app/Http/Controllers/Api/V1/BlockedUsersController.php app/Http/Controllers/Api/V1/Settings/
git mv app/Http/Controllers/Api/V1/EmergencyContactsController.php app/Http/Controllers/Api/V1/Settings/

# Move Media controllers
git mv app/Http/Controllers/Api/V1/StoryController.php app/Http/Controllers/Api/V1/Media/

# Move Video controllers
git mv app/Http/Controllers/Api/V1/VideoCallController.php app/Http/Controllers/Api/V1/Video/
git mv app/Http/Controllers/Api/V1/AgoraController.php app/Http/Controllers/Api/V1/Video/
```

**⚠️ Important:** Update routes in `routes/api.php` after reorganization

---

## Phase 3: Add Missing Components (1 week)

### Step 3.1: Create Missing Job Classes (2 hours)

```bash
# Generate Job classes
php artisan make:job Notifications/SendPushNotificationJob
php artisan make:job Media/ProcessImageUploadJob
php artisan make:job Media/GenerateImageThumbnailJob
php artisan make:job Chat/SendMessageNotificationJob
php artisan make:job Match/SendMatchNotificationJob
php artisan make:job User/UpdateUserActivityJob
php artisan make:job User/CleanupInactiveUsersJob
php artisan make:job User/GenerateCompatibilityScoresJob
php artisan make:job Export/GenerateUserDataExportJob

# Verify creation
ls -R app/Jobs/
```

**Manual tasks:**
1. Implement job logic for each class
2. Update controllers to dispatch jobs instead of synchronous operations
3. Configure queue workers in production

---

### Step 3.2: Create Missing Notification Classes (1.5 hours)

```bash
# Generate Notification classes
php artisan make:notification NewMatchNotification
php artisan make:notification NewMessageNotification
php artisan make:notification ProfileViewedNotification
php artisan make:notification LikeReceivedNotification
php artisan make:notification VideoCallInvitationNotification
php artisan make:notification VerificationApprovedNotification
php artisan make:notification SubscriptionExpiringNotification

# Verify creation
ls -R app/Notifications/
```

**Manual tasks:**
1. Implement notification channels (database, push, SMS)
2. Design notification templates
3. Update event listeners to trigger notifications

---

### Step 3.3: Create Helper Classes (1 hour)

```bash
# Create Helpers directory
mkdir -p app/Helpers

# Create helper files (manual creation required)
touch app/Helpers/DateHelper.php
touch app/Helpers/StringHelper.php
touch app/Helpers/ValidationHelper.php
touch app/Helpers/ResponseHelper.php
touch app/Helpers/IslamicHelper.php  # For prayer times, hijri dates, etc.
```

**Manual task:** Implement helper functions and add to composer.json autoload

---

### Step 3.4: Create Missing Repository Classes (2 hours)

```bash
# Create Repositories directory structure
mkdir -p app/Repositories
mkdir -p app/Repositories/Interfaces

# Generate repository interfaces
touch app/Repositories/Interfaces/UserRepositoryInterface.php
touch app/Repositories/Interfaces/ProfileRepositoryInterface.php
touch app/Repositories/Interfaces/MatchRepositoryInterface.php
touch app/Repositories/Interfaces/ChatRepositoryInterface.php
touch app/Repositories/Interfaces/MessageRepositoryInterface.php

# Generate repository implementations
touch app/Repositories/UserRepository.php
touch app/Repositories/ProfileRepository.php
touch app/Repositories/MatchRepository.php
touch app/Repositories/ChatRepository.php
touch app/Repositories/MessageRepository.php

# Verify creation
ls -R app/Repositories/
```

**Manual tasks:**
1. Define repository interfaces
2. Implement repository classes
3. Bind interfaces to implementations in service provider
4. Refactor services to use repositories

---

### Step 3.5: Add Missing Middleware (1 hour)

```bash
# Generate additional middleware
php artisan make:middleware EnsureProfileComplete
php artisan make:middleware CheckSubscriptionStatus
php artisan make:middleware CheckUserVerification
php artisan make:middleware LogUserActivity

# Verify creation
ls app/Http/Middleware/
```

**Manual tasks:**
1. Implement middleware logic
2. Register middleware in `bootstrap/app.php`
3. Apply middleware to relevant routes

---

### Step 3.6: Create Policy Classes (2 hours)

```bash
# Generate Policy classes for authorization
php artisan make:policy UserPolicy --model=User
php artisan make:policy ProfilePolicy --model=Profile
php artisan make:policy ChatPolicy --model=Chat
php artisan make:policy MessagePolicy --model=Message
php artisan make:policy MatchPolicy --model=Match
php artisan make:policy UserPhotoPolicy --model=UserPhoto
php artisan make:policy StoryPolicy --model=UserStory

# Verify creation
ls app/Policies/
```

**Manual tasks:**
1. Implement authorization logic for each policy
2. Register policies in `AuthServiceProvider`
3. Use policies in controllers with `authorize()` method

---

## Phase 4: Testing Infrastructure (2 weeks)

### Step 4.1: Set Up Testing Environment (1 hour)

```bash
# Create test database configuration
cp .env .env.testing

# Edit .env.testing (manual step)
# Set: DB_DATABASE=yoryor_testing
# Set: DB_CONNECTION=sqlite (or dedicated test database)

# Create SQLite test database (if using SQLite)
touch database/database.testing.sqlite

# Run migrations for test database
php artisan migrate --env=testing

# Verify PHPUnit configuration
cat phpunit.xml
```

---

### Step 4.2: Create Feature Tests (1 week)

```bash
# Create Feature test structure
mkdir -p tests/Feature/Auth
mkdir -p tests/Feature/Profile
mkdir -p tests/Feature/Chat
mkdir -p tests/Feature/Match
mkdir -p tests/Feature/API

# Generate Feature tests
php artisan make:test Auth/LoginTest
php artisan make:test Auth/RegisterTest
php artisan make:test Auth/VerifyEmailTest
php artisan make:test Auth/TwoFactorAuthTest

php artisan make:test Profile/UpdateProfileTest
php artisan make:test Profile/UploadPhotoTest
php artisan make:test Profile/ViewProfileTest

php artisan make:test Chat/SendMessageTest
php artisan make:test Chat/CreateChatTest
php artisan make:test Chat/DeleteMessageTest

php artisan make:test Match/LikeUserTest
php artisan make:test Match/MatchingAlgorithmTest
php artisan make:test Match/UnmatchTest

php artisan make:test API/RateLimitingTest
php artisan make:test API/AuthenticationTest

# Verify creation
ls -R tests/Feature/
```

**Manual task:** Write test cases for each feature (estimated 1 week)

---

### Step 4.3: Create Unit Tests (1 week)

```bash
# Create Unit test structure
mkdir -p tests/Unit/Services
mkdir -p tests/Unit/Models
mkdir -p tests/Unit/Helpers

# Generate Unit tests for Services
php artisan make:test Services/AuthServiceTest --unit
php artisan make:test Services/OtpServiceTest --unit
php artisan make:test Services/MediaUploadServiceTest --unit
php artisan make:test Services/MatchingServiceTest --unit
php artisan make:test Services/PresenceServiceTest --unit

# Generate Unit tests for Models
php artisan make:test Models/UserTest --unit
php artisan make:test Models/ProfileTest --unit
php artisan make:test Models/MatchTest --unit
php artisan make:test Models/ChatTest --unit
php artisan make:test Models/MessageTest --unit

# Generate Unit tests for Helpers (if created)
php artisan make:test Helpers/DateHelperTest --unit
php artisan make:test Helpers/ValidationHelperTest --unit

# Verify creation
ls -R tests/Unit/
```

**Manual task:** Write unit test cases (estimated 1 week)

---

### Step 4.4: Run Tests and Check Coverage (30 minutes)

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with code coverage (requires Xdebug or PCOV)
php artisan test --coverage

# Run parallel tests (Laravel 12 feature)
php artisan test --parallel

# Generate HTML coverage report
php artisan test --coverage-html coverage-report/

# Open coverage report
open coverage-report/index.html
```

---

## Safety Checks

### Before Deleting Files

1. **Check Git Status**
   ```bash
   git status
   git diff
   ```

2. **Create Backup Branch**
   ```bash
   git checkout -b backup-before-cleanup
   git checkout main
   ```

3. **Verify File Usage**
   ```bash
   # Search for file references in codebase
   grep -r "filename.php" app/ resources/ routes/
   ```

4. **Check Dependencies**
   ```bash
   # For services
   grep -r "use App\\Services\\ServiceName" app/

   # For Livewire components
   grep -r "livewire:component-name" resources/views/
   ```

---

### Before Moving Files

1. **Check Namespace Usage**
   ```bash
   # Find all files importing the service
   grep -r "use App\\Services\\AuthService" app/
   ```

2. **Verify Route References**
   ```bash
   # Check if controller is referenced in routes
   grep -r "ProfileController" routes/
   ```

3. **Test After Each Move**
   ```bash
   php artisan route:list  # Verify routes still work
   php artisan config:clear
   php artisan cache:clear
   ```

---

## Verification Commands

### After Phase 1 (Cleanup)

```bash
# Verify temporary files deleted
ls -la | grep -E "populate|setup-migrations"  # Should be empty

# Verify migrations_backup deleted
ls -la database/ | grep migrations_backup  # Should be empty

# Verify unused components deleted
ls -R app/Livewire/ | grep -E "Checkout|Coupon|Customer"  # Should be empty

# Check git status
git status  # Should show deletions

# Run application to ensure nothing broke
php artisan serve  # Test manually
```

---

### After Phase 2 (Reorganization)

```bash
# Verify services moved correctly
ls -R app/Services/

# Check namespace updates (should not show old namespaces)
grep -r "namespace App\\Services;" app/Services/  # Should be empty

# Verify routes still work
php artisan route:list

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run application
php artisan serve
```

---

### After Phase 3 (New Components)

```bash
# Verify Request classes created
ls -R app/Http/Requests/

# Verify Job classes created
ls -R app/Jobs/

# Verify Notification classes created
ls -R app/Notifications/

# Verify Policy classes created
ls app/Policies/

# Check autoload (should not show errors)
composer dump-autoload
```

---

### After Phase 4 (Testing)

```bash
# Run all tests
php artisan test

# Check test coverage
php artisan test --coverage

# Verify test database configured
php artisan migrate:status --env=testing

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

---

## Rollback Plan

If something breaks during cleanup:

### Rollback Phase 1 (File Deletion)

```bash
# Restore from git (if committed before cleanup)
git checkout HEAD~1 -- populate_migrations.php
git checkout HEAD~1 -- database/migrations_backup/

# Or restore from backup tarball
tar -xzf ~/yoryor-migrations-backup-YYYYMMDD.tar.gz -C database/
```

---

### Rollback Phase 2 (File Reorganization)

```bash
# Use git to undo moves
git log --oneline  # Find commit hash before reorganization
git revert <commit-hash>

# Or manually move files back
git mv app/Services/Auth/AuthService.php app/Services/
```

---

### Rollback Phase 3 & 4 (New Components)

```bash
# Delete newly created files
rm -rf app/Http/Requests/Auth/
rm -rf app/Jobs/Notifications/
rm -rf tests/Feature/Auth/

# Or use git reset (if not pushed)
git reset --hard HEAD~N  # N = number of commits to undo
```

---

## Final Checklist

Before marking cleanup as complete:

- [ ] All temporary files deleted
- [ ] Unused components removed
- [ ] Services reorganized into subdirectories
- [ ] Request validation classes created
- [ ] Job classes created for async operations
- [ ] Notification classes created
- [ ] Policy classes created for authorization
- [ ] Test structure established
- [ ] Git history clean with meaningful commits
- [ ] Application runs without errors
- [ ] All routes accessible
- [ ] No namespace errors in logs
- [ ] Database migrations run successfully
- [ ] Tests pass (if any written)
- [ ] Documentation updated (CLAUDE.md, README.md)

---

## Estimated Total Time

| Phase | Time Estimate |
|-------|---------------|
| Phase 1: Critical Cleanup | 1 hour |
| Phase 2: Component Reorganization | 2-3 hours |
| Phase 3: Add Missing Components | 1 week (40 hours) |
| Phase 4: Testing Infrastructure | 2 weeks (80 hours) |
| **Total** | **~123 hours (~3 weeks)** |

---

## Notes

- **Incremental Approach:** Complete each phase before moving to the next
- **Git Commits:** Commit after each major step for easy rollback
- **Testing:** Test the application after each phase
- **Documentation:** Update CLAUDE.md as you make changes
- **Team Communication:** Inform team members of structural changes
- **Production:** Do NOT perform cleanup in production; use staging/development environments

---

**Created:** 2025-09-30
**Last Updated:** 2025-09-30
**Status:** Ready for implementation
**Priority:** 🔴 Critical (Phase 1), 🟡 High (Phase 2), 🟢 Medium (Phase 3-4)