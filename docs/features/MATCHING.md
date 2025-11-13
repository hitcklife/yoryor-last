# Matching & Discovery System Documentation

## Overview

YorYor's matching and discovery system uses an AI-powered compatibility algorithm specifically designed for Muslim dating. The system prioritizes religious compatibility, cultural values, and serious marriage intentions while providing multiple discovery methods to suit different user preferences.

---

## Matching Algorithm

### Compatibility Scoring System

The compatibility algorithm calculates a score from 0-100 based on weighted criteria:

```php
public function calculateCompatibilityScore(User $user1, User $user2): int
{
    $scores = [
        'religious_compatibility' => $this->calculateReligiousScore($user1, $user2) * 0.30,
        'lifestyle_compatibility' => $this->calculateLifestyleScore($user1, $user2) * 0.20,
        'location_compatibility' => $this->calculateLocationScore($user1, $user2) * 0.15,
        'age_compatibility' => $this->calculateAgeScore($user1, $user2) * 0.15,
        'education_career' => $this->calculateCareerScore($user1, $user2) * 0.10,
        'physical_preferences' => $this->calculatePhysicalScore($user1, $user2) * 0.10,
    ];

    return round(array_sum($scores));
}
```

### 1. Religious Compatibility (30% Weight)

**Highest priority in the algorithm**

**Factors Evaluated:**
- **Sect Match** (40 points): Same sect = 40, Compatible sects = 25, Different = 10
- **Religiosity Level** (30 points): Within 1 level = 30, Within 2 levels = 15, Otherwise = 5
- **Prayer Frequency** (20 points): Matching frequency = 20, Similar = 10, Different = 5
- **Dietary Preferences** (10 points): Matching = 10, Compatible = 5

**Example Calculation:**
```php
private function calculateReligiousScore(User $user1, User $user2): int
{
    $score = 0;

    // Sect match (40 points max)
    if ($user1->culturalProfile->sect === $user2->culturalProfile->sect) {
        $score += 40;
    } elseif ($this->areSectsCompatible($user1->culturalProfile->sect, $user2->culturalProfile->sect)) {
        $score += 25;
    } else {
        $score += 10;
    }

    // Religiosity level (30 points max)
    $religiosityDiff = abs($this->getReligiosityNumeric($user1) - $this->getReligiosityNumeric($user2));
    if ($religiosityDiff === 0) {
        $score += 30;
    } elseif ($religiosityDiff === 1) {
        $score += 15;
    } else {
        $score += 5;
    }

    // Prayer frequency (20 points max)
    if ($user1->culturalProfile->prayer_frequency === $user2->culturalProfile->prayer_frequency) {
        $score += 20;
    } elseif ($this->arePrayerFrequenciesCompatible($user1, $user2)) {
        $score += 10;
    } else {
        $score += 5;
    }

    // Dietary preferences (10 points max)
    if ($user1->culturalProfile->dietary_preference === $user2->culturalProfile->dietary_preference) {
        $score += 10;
    } else {
        $score += 5;
    }

    return $score;
}
```

### 2. Lifestyle Compatibility (20% Weight)

**Factors Evaluated:**
- **Alcohol Stance** (40 points): Same stance = 40, Compatible = 20, Different = 5
- **Smoking Status** (30 points): Both non-smokers = 30, One smokes = 10
- **Work-Life Balance** (20 points): Similar priorities = 20, Different = 10
- **Hobbies/Interests** (10 points): Shared interests = 10 points

**Why This Matters:**
Lifestyle compatibility ensures couples can live harmoniously with aligned daily habits and values.

### 3. Location Compatibility (15% Weight)

**Factors Evaluated:**
- **Current Distance** (50 points):
  - Within 50km = 50 points
  - 50-100km = 35 points
  - 100-250km = 20 points
  - 250-500km = 10 points
  - 500km+ = 5 points

- **Relocation Willingness** (30 points):
  - Both willing to relocate = 30
  - One willing = 20
  - Both unwilling but same city = 25
  - Both unwilling, different cities = 5

- **Preferred Locations Overlap** (20 points):
  - Overlapping preferred countries/cities = 20
  - No overlap but open to long distance = 10

```php
private function calculateLocationScore(User $user1, User $user2): int
{
    $score = 0;

    // Calculate distance between users
    $distance = $this->calculateDistance(
        $user1->profile->latitude,
        $user1->profile->longitude,
        $user2->profile->latitude,
        $user2->profile->longitude
    );

    // Distance scoring (50 points max)
    if ($distance <= 50) {
        $score += 50;
    } elseif ($distance <= 100) {
        $score += 35;
    } elseif ($distance <= 250) {
        $score += 20;
    } elseif ($distance <= 500) {
        $score += 10;
    } else {
        $score += 5;
    }

    // Relocation willingness (30 points max)
    if ($user1->locationPreference->willing_to_relocate === 'definitely_yes' &&
        $user2->locationPreference->willing_to_relocate === 'definitely_yes') {
        $score += 30;
    } elseif ($user1->locationPreference->willing_to_relocate === 'definitely_yes' ||
              $user2->locationPreference->willing_to_relocate === 'definitely_yes') {
        $score += 20;
    } elseif ($distance <= 50) {
        $score += 25;  // Same city, both unwilling to relocate is acceptable
    } else {
        $score += 5;
    }

    // Preferred locations overlap (20 points max)
    $preferredCountries1 = $user1->locationPreference->preferred_countries ?? [];
    $preferredCountries2 = $user2->locationPreference->preferred_countries ?? [];
    $overlap = count(array_intersect($preferredCountries1, $preferredCountries2));

    if ($overlap > 0) {
        $score += 20;
    } elseif ($user1->locationPreference->open_to_long_distance ||
              $user2->locationPreference->open_to_long_distance) {
        $score += 10;
    }

    return $score;
}
```

### 4. Age Compatibility (15% Weight)

**Factors Evaluated:**
- **Age Difference** (70 points):
  - 0-2 years = 70 points
  - 3-5 years = 50 points
  - 6-10 years = 30 points
  - 11-15 years = 15 points
  - 16+ years = 5 points

- **Life Stage Alignment** (30 points):
  - Both students = 30
  - Both professionals = 30
  - Similar career stage = 20
  - Different stages = 10

### 5. Education & Career Compatibility (10% Weight)

**Factors Evaluated:**
- **Education Level** (50 points):
  - Same level = 50
  - Adjacent levels = 30
  - Different levels = 15

- **Income Compatibility** (30 points):
  - Similar income bracket = 30
  - Adjacent brackets = 20
  - Different brackets = 10

- **Career Ambitions** (20 points):
  - Similar ambitions = 20
  - Different ambitions = 10

### 6. Physical Preferences (10% Weight)

**Factors Evaluated:**
- **Height Preference** (40 points): Within preference range = 40, Outside = 10
- **Body Type Preference** (30 points): Matches preference = 30, Otherwise = 10
- **Ethnicity Preference** (30 points): Matches preference = 30, Otherwise = 15

**Note**: Physical compatibility is intentionally the lowest weighted factor to emphasize substance over appearance.

---

## Discovery Methods

### 1. Swipe Card Interface

**Tinder-Style Card Stack Discovery**

**Features:**
- Stack of profile cards
- Swipe right to like
- Swipe left to pass
- Instant match notification on mutual like
- Smooth animations and transitions
- Compatibility score displayed
- Distance and age preview
- Quick profile preview with essential info

**User Flow:**
1. User sees stack of cards sorted by compatibility
2. User swipes right (like) or left (pass)
3. If mutual like, instant match notification appears
4. Chat automatically created
5. Next profile appears

**Keyboard Shortcuts:**
- Arrow Right / L: Like
- Arrow Left / D: Dislike
- Arrow Up: Super Like
- Arrow Down: Open full profile

**API Endpoint:**
```http
GET /api/v1/discover?mode=cards&limit=20
```

**Response:**
```json
{
  "data": [
    {
      "type": "user",
      "id": "uuid",
      "attributes": {
        "first_name": "Fatima",
        "age": 26,
        "bio": "Seeking serious relationship...",
        "compatibility_score": 87,
        "distance": "12 km",
        "primary_photo_url": "...",
        "verified_badges": ["identity", "photo"]
      }
    }
  ],
  "meta": {
    "has_more": true,
    "remaining_likes_today": 8
  }
}
```

**Livewire Component:**
- `Dashboard/SwipeCards` - Main swipe interface

**JavaScript:**
- Hammer.js for swipe gestures
- GSAP for animations
- Real-time WebSocket for instant match notifications

### 2. Grid Discovery View

**Browse Multiple Profiles Simultaneously**

**Features:**
- Grid layout (2-4 columns responsive)
- Filter and sort controls
- Pagination with infinite scroll
- Quick actions on hover (like, pass, bookmark)
- Profile thumbnails with key info overlay
- Batch loading for performance

**Layout Options:**
- **Compact**: 4 columns, minimal info
- **Standard**: 3 columns, basic info
- **Detailed**: 2 columns, extended info

**Sort Options:**
- **Compatibility** (default): Highest compatibility first
- **Distance**: Nearest users first
- **Recently Active**: Most recently online
- **Newest Users**: Recently joined users
- **Random**: Shuffle profiles

**API Endpoint:**
```http
GET /api/v1/discover?mode=grid&page=1&per_page=20&sort=compatibility
```

**Livewire Component:**
- `Dashboard/DiscoveryGrid` - Grid view interface

### 3. Advanced Search & Filters

**Multi-Criteria Filtering System**

**Available Filters:**

#### Basic Filters (Free Tier)
- **Age Range**: 18-99 (slider)
- **Distance**: 5-500 km or Any
- **Gender**: Male, Female

#### Advanced Filters (Premium)
- **Height Range**: 150-220 cm
- **Education Level**: High school through Doctorate
- **Occupation/Industry**: 20+ industries
- **Religion & Sect**: Islam (Sunni, Shia, etc.)
- **Religiosity Level**: 5 levels
- **Prayer Frequency**: 6 options
- **Marital Status**: 4 options
- **Children**: Has children, Wants children, No preference
- **Languages**: Multiple selection
- **Ethnicity**: 10+ options
- **Body Type**: 6 types
- **Smoking**: Yes, No, Occasionally
- **Alcohol**: Never, Socially, Prefer not to say
- **Verification Status**: Verified only

#### Premium Plus Filters
- **Income Level**: 7 brackets
- **Relocation Willingness**: 4 levels
- **Marriage Timeline**: Within 6 months to 2+ years
- **Family Involvement**: Importance level
- **Last Active**: Within 24h, 7 days, 30 days
- **Profile Completion**: 50%+, 75%+, 100%

**Filter Presets:**
Users can save filter combinations as presets:
- "Serious About Marriage" (marriage timeline within 1 year, high religiosity)
- "In My Area" (within 50km, willing to relocate)
- "Highly Educated" (Bachelor's+, professional career)
- "Very Religious" (prays 5x daily, very religious)

**API Endpoint:**
```http
POST /api/v1/discover/search
Content-Type: application/json

{
  "age_min": 25,
  "age_max": 35,
  "distance_max": 100,
  "education_level": ["bachelors", "masters"],
  "religiosity_level": ["very_religious", "religious"],
  "prayer_frequency": "5_times_daily",
  "verified_only": true
}
```

**Livewire Component:**
- `Dashboard/AdvancedFilters` - Filter interface

### 4. Daily Recommendations

**AI-Curated Matches**

**Features:**
- 5-10 curated matches per day
- Personalized based on your activity
- Learn from your likes and passes
- Refreshed daily at midnight
- Higher quality than standard discovery

**Algorithm:**
```php
public function generateDailyRecommendations(User $user): Collection
{
    // Get users the current user hasn't interacted with
    $candidates = User::active()
        ->where('id', '!=', $user->id)
        ->whereNotIn('id', $user->likedUsers()->pluck('id'))
        ->whereNotIn('id', $user->dislikedUsers()->pluck('id'))
        ->whereNotIn('id', $user->blockedUsers()->pluck('id'))
        ->get();

    // Calculate compatibility scores
    $scored = $candidates->map(function ($candidate) use ($user) {
        return [
            'user' => $candidate,
            'compatibility_score' => $this->calculateCompatibilityScore($user, $candidate),
            'activity_score' => $this->calculateActivityScore($candidate),
            'freshness_score' => $this->calculateFreshnessScore($user, $candidate),
        ];
    });

    // Weight and sort
    $sorted = $scored->sortByDesc(function ($item) {
        return ($item['compatibility_score'] * 0.6) +
               ($item['activity_score'] * 0.2) +
               ($item['freshness_score'] * 0.2);
    });

    // Return top 10
    return $sorted->take(10)->pluck('user');
}
```

**API Endpoint:**
```http
GET /api/v1/discover/daily-recommendations
```

---

## Like System

### Like Flow

**Standard Like:**
1. User views profile
2. User clicks "Like" button
3. Like stored in `likes` table
4. Target user receives notification
5. If mutual like exists, match created automatically

**Super Like (Premium Feature):**
- Stands out to the recipient
- Recipient notified of super like
- Profile highlighted with star icon
- Limited quantity (5/week for Premium, Unlimited for Premium Plus)

**Database Schema:**
```sql
CREATE TABLE likes (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    liked_user_id BIGINT NOT NULL,
    is_super_like BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    INDEX idx_user_liked (user_id, liked_user_id),
    INDEX idx_liked_user (liked_user_id),
    UNIQUE KEY unique_like (user_id, liked_user_id)
);
```

### Like Limits

**Free Tier:**
- 10 likes per day
- 1 super like per month
- Resets at midnight UTC

**Premium Tier:**
- Unlimited likes
- 5 super likes per week
- No daily reset needed

**Premium Plus Tier:**
- Unlimited likes
- Unlimited super likes
- Priority in recipient's like queue

**Limit Enforcement:**
```php
public function canLike(User $user): bool
{
    if ($user->hasActivePremium()) {
        return true;  // Unlimited
    }

    $todayLikes = Like::where('user_id', $user->id)
        ->whereDate('created_at', today())
        ->count();

    return $todayLikes < 10;  // Free tier limit
}
```

**API Endpoints:**
```http
POST /api/v1/like/{userId}
POST /api/v1/super-like/{userId}
GET /api/v1/likes/remaining
```

### Received Likes Feature

**See Who Liked You**

**Free Tier:**
- See blurred thumbnails
- See count of likes received
- Can only see who liked them after matching

**Premium/Premium Plus:**
- See unblurred photos
- See full profiles of who liked you
- Like back directly from this list
- Sort by compatibility or recent

**API Endpoint:**
```http
GET /api/v1/likes/received?blur=false
```

**Livewire Component:**
- `Dashboard/ReceivedLikes` - Who liked you interface

---

## Match Management

### Automatic Match Creation

**When Does a Match Occur?**
1. User A likes User B
2. User B likes User A
3. System automatically creates match record
4. Private chat automatically created
5. Both users receive instant notification

**Match Creation Logic:**
```php
public function createMatchIfMutual(int $userId, int $likedUserId): ?Match
{
    DB::beginTransaction();
    try {
        // Check if mutual like exists
        $mutualLike = Like::where('user_id', $likedUserId)
            ->where('liked_user_id', $userId)
            ->exists();

        if (!$mutualLike) {
            DB::rollBack();
            return null;  // Not a match yet
        }

        // Create match
        $match = Match::create([
            'user1_id' => min($userId, $likedUserId),
            'user2_id' => max($userId, $likedUserId),
            'compatibility_score' => $this->calculateCompatibilityScore(
                User::find($userId),
                User::find($likedUserId)
            ),
            'matched_at' => now(),
        ]);

        // Create private chat
        $chat = Chat::create([
            'type' => 'private',
            'created_by' => $userId,
        ]);

        $chat->users()->attach([$userId, $likedUserId]);

        // Send notifications
        event(new NewMatchEvent($match, $userId, $likedUserId));

        DB::commit();
        return $match;
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

### Match List

**Features:**
- View all matches
- Sort by recent activity, compatibility, or match date
- Filter by conversation status (messaged, not messaged)
- Search matches by name
- Unmatch functionality
- Quick actions (message, view profile)

**Sort Options:**
- **Recent Activity**: Most recent message or match
- **Compatibility**: Highest compatibility score first
- **Match Date**: Newest matches first
- **Unread Messages**: Matches with unread messages first

**API Endpoint:**
```http
GET /api/v1/matches?sort=recent_activity&page=1
```

**Response:**
```json
{
  "data": [
    {
      "type": "match",
      "id": "123",
      "attributes": {
        "matched_at": "2025-10-01T12:00:00Z",
        "compatibility_score": 87,
        "last_message": "Hey! How are you?",
        "last_message_at": "2025-10-07T10:30:00Z",
        "unread_count": 2
      },
      "relationships": {
        "matched_user": {
          "data": {"type": "user", "id": "uuid"}
        },
        "chat": {
          "data": {"type": "chat", "id": "456"}
        }
      }
    }
  ],
  "meta": {
    "total": 45,
    "unread_matches": 5
  }
}
```

### Unmatch Functionality

**Unmatch Flow:**
1. User clicks "Unmatch" on a match
2. Confirmation modal appears with warning
3. Upon confirmation:
   - Match record deleted
   - Chat remains (messages preserved) but marked as unmatched
   - Users removed from each other's match lists
   - Can still report user if needed

**Options During Unmatch:**
- **Unmatch only**: Preserve chat history
- **Unmatch and delete chat**: Remove all messages
- **Unmatch and block**: Block user + remove chat

**API Endpoint:**
```http
DELETE /api/v1/matches/{matchId}?action=unmatch_only
```

---

## Premium Features

### Profile Boost

**What It Does:**
- Places your profile at the top of discovery for 30 minutes
- 10x visibility increase
- Higher likelihood of likes and matches

**Usage:**
- **Premium**: 1 boost per month
- **Premium Plus**: Unlimited boosts

**Boost Mechanics:**
```php
public function boostProfile(User $user): void
{
    $user->update([
        'boosted_until' => now()->addMinutes(30),
    ]);

    // Increase discovery ranking
    Cache::put("boosted_profile_{$user->id}", true, 30 * 60);

    // Notify user
    event(new ProfileBoostedEvent($user));
}

// In discovery query
public function getDiscoveryProfiles(User $user): Collection
{
    return User::active()
        ->where('id', '!=', $user->id)
        ->orderByRaw('boosted_until > NOW() DESC')  // Boosted profiles first
        ->orderBy('compatibility_score', 'desc')
        ->limit(50)
        ->get();
}
```

**API Endpoint:**
```http
POST /api/v1/profile/boost
```

### Rewind Last Swipe

**What It Does:**
- Undo last swipe (like or pass)
- Useful for accidental swipes
- Limited to last 5 swipes (within 24 hours)

**Usage:**
- **Free**: Not available
- **Premium/Premium Plus**: Unlimited rewinds

**API Endpoint:**
```http
POST /api/v1/swipe/rewind
```

**Livewire Component:**
- Rewind button appears after swipe for 5 seconds

---

## Best Practices

### For Users

**Maximize Your Matches:**
1. Complete your profile to 100%
2. Upload 4-6 high-quality photos
3. Get verified (identity + photo minimum)
4. Be active daily (activity boosts visibility)
5. Use advanced filters to find compatible matches
6. Like profiles genuinely (not just swiping right on everyone)
7. Respond to matches within 24 hours

**Discovery Tips:**
1. Check daily recommendations first (highest quality)
2. Use filters to narrow down to serious candidates
3. Read bios before swiping
4. Look for verified badges
5. Check compatibility score (aim for 70+)

### For Developers

**Performance Optimization:**
1. Cache compatibility scores (expensive to calculate)
2. Preload discovery profiles in batches
3. Use database indexes on filter fields
4. Eager load relationships (photos, profiles)
5. Implement pagination with cursor-based approach

**Algorithm Tuning:**
1. Monitor match success rates
2. A/B test weight adjustments
3. Collect user feedback on match quality
4. Analyze unmatched profiles for pattern insights
5. Adjust based on cultural/regional differences

---

## API Reference

### Discovery Endpoints

```http
GET /api/v1/discover?mode=cards&limit=20
GET /api/v1/discover?mode=grid&page=1&per_page=20
POST /api/v1/discover/search (with filters)
GET /api/v1/discover/daily-recommendations
```

### Like Endpoints

```http
POST /api/v1/like/{userId}
POST /api/v1/super-like/{userId}
DELETE /api/v1/like/{userId}  (Unlike)
GET /api/v1/likes/sent
GET /api/v1/likes/received
GET /api/v1/likes/remaining
```

### Match Endpoints

```http
GET /api/v1/matches
GET /api/v1/matches/{matchId}
DELETE /api/v1/matches/{matchId}  (Unmatch)
```

### Premium Features

```http
POST /api/v1/profile/boost
POST /api/v1/swipe/rewind
GET /api/v1/subscription/usage
```

---

*Last Updated: October 2025*
