# Profile System Documentation

## Overview

YorYor implements a comprehensive multi-section profile system designed specifically for Muslim dating and matchmaking. The system emphasizes cultural compatibility, religious values, and family involvement while maintaining user privacy and security.

---

## Multi-Section Profile Architecture

### Database Structure

The profile system uses a **User → Profile → Extended Profiles** architecture:

```
users (authentication, basic info, UUID)
  ├── profiles (core profile data)
  ├── user_cultural_profiles (religion, ethnicity, lifestyle)
  ├── user_career_profiles (education, occupation, income)
  ├── user_physical_profiles (height, body type, appearance)
  ├── user_family_preferences (marriage, children, family values)
  ├── user_location_preferences (relocation, geographic preferences)
  ├── user_prayer_times (Islamic prayer preferences)
  ├── user_photos (profile photos, up to 6)
  └── user_verified_badges (verification status)
```

### Key Relationships

```php
User hasOne Profile
User hasOne UserCulturalProfile
User hasOne UserCareerProfile
User hasOne UserPhysicalProfile
User hasOne UserFamilyPreference
User hasOne UserLocationPreference
User hasMany UserPhoto (max 6)
User hasMany UserVerifiedBadge
```

---

## Profile Sections

### 1. Basic Profile

**Purpose**: Core information visible to all matched users

**Fields:**
- **Name**: First name, last name (last name optional)
- **Age**: Calculated from date of birth
- **Gender**: Male, Female, Other
- **Bio**: Personal description (500 characters max)
- **Location**: City, country (with privacy controls)
- **Profile UUID**: Secure non-guessable identifier for profile URLs

**Privacy Controls:**
- Show/hide last name
- Show/hide exact age (can show age range instead)
- Show/hide city (can show only country)
- Show/hide distance from viewer

**Validation Rules:**
```php
'first_name' => 'required|string|max:50',
'last_name' => 'nullable|string|max:50',
'date_of_birth' => 'required|date|before:18 years ago',
'gender' => 'required|in:male,female,other',
'bio' => 'nullable|string|max:500',
'city' => 'required|string|max:100',
'country_id' => 'required|exists:countries,id',
```

**API Endpoints:**
- `GET /api/v1/profile` - Get authenticated user's profile
- `PUT /api/v1/profile` - Update basic profile
- `GET /api/v1/profile/{uuid}` - View another user's profile

**Livewire Component:**
- `Profile/BasicInfo` - Edit basic profile information

---

### 2. Cultural Profile

**Purpose**: Religious and cultural compatibility information

**Fields:**

#### Religious Information
- **Religion**: Islam (primary), Christianity, Judaism, Other
- **Sect**: Sunni, Shia, Sufi, Other, Prefer not to say
- **Religiosity Level**: Very religious, Religious, Moderately religious, Somewhat religious, Not religious
- **Prayer Frequency**: 5 times daily, 3-4 times daily, Friday prayers only, Occasionally, Rarely, Never

#### Cultural Preferences
- **Ethnicity**: Arab, South Asian, Southeast Asian, African, Caucasian, Mixed, Other
- **Languages Spoken**: Multiple selection (Arabic, English, Urdu, Turkish, Malay, etc.)
- **Dietary Preferences**: Halal only, Halal mostly, Flexible, Vegetarian, Vegan
- **Dress Code**: Hijab, Niqab, Modest, Western, Mixed, Prefer not to say

#### Lifestyle
- **Alcohol Stance**: Never, Socially, Occasionally, Prefer not to say
- **Smoking Status**: Non-smoker, Occasional smoker, Regular smoker, Trying to quit
- **Cultural Values**: Traditional, Modern, Mix of both

**Why This Matters:**
Religious and cultural compatibility is the #1 priority in the matching algorithm (30% weight). This section helps users find partners who share their values and lifestyle.

**Validation Rules:**
```php
'religion' => 'required|string|max:50',
'sect' => 'nullable|string|max:50',
'religiosity_level' => 'required|in:very_religious,religious,moderately_religious,somewhat_religious,not_religious',
'prayer_frequency' => 'required|string|max:50',
'ethnicity' => 'required|string|max:100',
'languages' => 'required|array|min:1',
'dietary_preference' => 'required|string|max:50',
'dress_code' => 'required|string|max:50',
'alcohol' => 'required|string|max:50',
'smoking' => 'required|string|max:50',
```

**API Endpoints:**
- `GET /api/v1/profile/cultural` - Get cultural profile
- `PUT /api/v1/profile/cultural` - Update cultural profile

**Livewire Component:**
- `Profile/CulturalBackground` - Edit cultural profile

---

### 3. Career & Education Profile

**Purpose**: Professional background and educational achievements

**Fields:**

#### Education
- **Education Level**: High school, Some college, Bachelor's degree, Master's degree, Doctorate/PhD, Professional degree, Trade school
- **Field of Study**: Engineering, Medicine, Business, Arts, Science, Islamic Studies, etc.
- **University**: University name (optional)

#### Career
- **Occupation**: Current job title
- **Industry**: Technology, Healthcare, Education, Finance, Government, Religious services, etc.
- **Work Status**: Employed full-time, Employed part-time, Self-employed, Student, Unemployed, Retired
- **Income Level**: Ranges for privacy (Below $30k, $30k-$50k, $50k-$75k, $75k-$100k, $100k-$150k, Above $150k, Prefer not to say)

#### Career Ambitions
- **Career Goals**: Short description of career aspirations
- **Work-Life Balance**: Very important, Important, Somewhat important, Not important

**Validation Rules:**
```php
'education_level' => 'required|string|max:50',
'field_of_study' => 'nullable|string|max:100',
'university' => 'nullable|string|max:100',
'occupation' => 'required|string|max:100',
'industry' => 'required|string|max:100',
'work_status' => 'required|string|max:50',
'income_level' => 'required|string|max:50',
'career_goals' => 'nullable|string|max:500',
```

**API Endpoints:**
- `GET /api/v1/profile/career` - Get career profile
- `PUT /api/v1/profile/career` - Update career profile

**Livewire Component:**
- `Profile/CareerEducation` - Edit career and education

---

### 4. Physical Profile

**Purpose**: Physical attributes and appearance information

**Fields:**

#### Physical Attributes
- **Height**: In cm or feet/inches (150-220 cm)
- **Body Type**: Slim, Athletic, Average, Few extra pounds, Curvy, Heavyset
- **Eye Color**: Brown, Blue, Green, Hazel, Gray, Other
- **Hair Color**: Black, Brown, Blonde, Red, Gray, Bald, Other
- **Hair Style**: Long, Short, Medium, Curly, Straight, Wavy

#### Health & Fitness
- **Physical Fitness Level**: Very active, Active, Moderately active, Lightly active, Not active
- **Disability Status**: None, Prefer not to say, Has disability (with description)

**Privacy Controls:**
- Can hide height from discovery
- Can hide body type
- Can hide fitness level

**Validation Rules:**
```php
'height' => 'required|numeric|min:150|max:220',
'body_type' => 'required|string|max:50',
'eye_color' => 'nullable|string|max:50',
'hair_color' => 'nullable|string|max:50',
'hair_style' => 'nullable|string|max:50',
'fitness_level' => 'required|string|max:50',
'disability' => 'nullable|string|max:500',
```

**API Endpoints:**
- `GET /api/v1/profile/physical` - Get physical profile
- `PUT /api/v1/profile/physical` - Update physical profile

**Livewire Component:**
- `Profile/PhysicalAttributes` - Edit physical profile

---

### 5. Family Preferences

**Purpose**: Marriage intentions and family planning information

**Fields:**

#### Marital Information
- **Marital Status**: Never married, Divorced, Widowed, Separated
- **Has Children**: Yes (number), No
- **Want Children**: Definitely yes, Probably yes, Not sure, Probably not, Definitely not

#### Family Values
- **Family Involvement**: Very important, Important, Somewhat important, Not important
- **Parents' Opinion**: Very important, Important, Somewhat important, Not important
- **Living Situation**: Own place, With family, With roommates, Other
- **Willing to Live with In-Laws**: Yes, No, Maybe, Prefer not to say

#### Marriage Timeline
- **Marriage Timeline**: Within 6 months, 6-12 months, 1-2 years, 2+ years, Not sure
- **Polygamy Stance**: Open to it, Against it, Prefer not to say (for male profiles)

**Why This Matters:**
Family is central to Islamic marriage. This section helps users find partners with aligned family values and marriage intentions.

**Validation Rules:**
```php
'marital_status' => 'required|string|max:50',
'has_children' => 'required|boolean',
'number_of_children' => 'required_if:has_children,true|nullable|integer|min:1',
'want_children' => 'required|string|max:50',
'family_involvement' => 'required|string|max:50',
'parents_opinion' => 'required|string|max:50',
'living_situation' => 'required|string|max:100',
'willing_to_live_with_in_laws' => 'required|string|max:50',
'marriage_timeline' => 'required|string|max:50',
```

**API Endpoints:**
- `GET /api/v1/profile/family` - Get family preferences
- `PUT /api/v1/profile/family` - Update family preferences

**Livewire Component:**
- `Profile/FamilyPreferences` - Edit family preferences

---

### 6. Location Preferences

**Purpose**: Geographic preferences and relocation willingness

**Fields:**

#### Current Location
- **City**: Current city of residence
- **Country**: Current country
- **Distance Privacy**: Show exact distance, Show approximate distance, Hide distance

#### Relocation Preferences
- **Willing to Relocate**: Definitely yes, Maybe, Probably not, Definitely not
- **Preferred Countries**: Multiple selection of countries
- **Preferred Cities**: Multiple selection of cities (optional)
- **Immigration Status**: Citizen, Permanent resident, Work visa, Student visa, Other, Prefer not to say

#### Distance Preferences
- **Maximum Distance**: 10 km, 25 km, 50 km, 100 km, 250 km, 500 km, Any distance, Same country only
- **Open to Long Distance**: Yes, No, Maybe

**Validation Rules:**
```php
'current_city' => 'required|string|max:100',
'current_country_id' => 'required|exists:countries,id',
'willing_to_relocate' => 'required|string|max:50',
'preferred_countries' => 'nullable|array',
'preferred_cities' => 'nullable|array',
'immigration_status' => 'required|string|max:50',
'maximum_distance' => 'required|integer|in:10,25,50,100,250,500,999999',
'open_to_long_distance' => 'required|boolean',
```

**API Endpoints:**
- `GET /api/v1/profile/location` - Get location preferences
- `PUT /api/v1/profile/location` - Update location preferences

**Livewire Component:**
- `Profile/LocationPreferences` - Edit location preferences

---

## Photo Management

### Photo System

**Capabilities:**
- Upload up to 6 photos
- Minimum 2 photos required for profile activation
- Drag-and-drop reordering
- Set primary photo (shown first in discovery)
- Delete and replace photos
- Photo verification system

**Photo Requirements:**
- **Formats**: JPEG, PNG, WEBP
- **File Size**: Maximum 5MB per photo
- **Dimensions**: Minimum 400x400px, recommended 1000x1000px
- **Content**: Must show face clearly, no group photos as primary

**Privacy Controls:**
- **Public**: Visible to all users in discovery
- **Matches Only**: Only visible to matched users
- **Private**: Not visible in discovery (profile viewable by direct link only)

### Photo Verification

**Process:**
1. User uploads selfie following pose instructions
2. System compares selfie to profile photos using facial recognition
3. Admin reviews verification request
4. Photo verified badge granted upon approval

**Benefits:**
- Increases trust and match quality
- Reduces catfishing
- Higher visibility in discovery
- Access to verified-only search filter

### Photo Upload Flow

**User Flow:**
1. Navigate to Profile > Photos
2. Click "Add Photo" or drag-and-drop
3. Crop and adjust photo
4. Upload (automatic optimization and thumbnail generation)
5. Set as primary if desired
6. Reorder photos by dragging

**Technical Implementation:**
```php
// MediaUploadService handles uploads
public function upload(UploadedFile $file, string $folder = 'photos'): array
{
    // Optimize image
    $image = Image::make($file);
    $image->fit(1000, 1000);

    // Generate thumbnail
    $thumbnail = $image->fit(200, 200);

    // Upload to Cloudflare R2
    $path = Storage::disk('r2')->putFile($folder, $file);
    $thumbnailPath = Storage::disk('r2')->put($folder . '/thumbnails', $thumbnail);

    return [
        'path' => $path,
        'thumbnail_path' => $thumbnailPath,
        'url' => Storage::disk('r2')->url($path),
    ];
}
```

**API Endpoints:**
- `GET /api/v1/profile/photos` - Get all profile photos
- `POST /api/v1/profile/photos` - Upload new photo
- `PUT /api/v1/profile/photos/{photoId}` - Update photo (set primary, reorder)
- `DELETE /api/v1/profile/photos/{photoId}` - Delete photo
- `POST /api/v1/profile/photos/verify` - Request photo verification

**Livewire Component:**
- `Profile/Photos` - Photo management interface

---

## Profile Completion Tracking

### Completion Percentage

**Calculation:**
Profile completion is calculated based on filled sections:

```php
public function getCompletionPercentageAttribute(): int
{
    $sections = [
        'basic_profile' => 15,      // Name, age, gender, bio
        'photos' => 20,             // At least 2 photos
        'cultural_profile' => 15,   // Religious & cultural info
        'career_profile' => 15,     // Education & career
        'physical_profile' => 10,   // Physical attributes
        'family_preferences' => 15, // Marriage & family
        'location_preferences' => 10, // Location & relocation
    ];

    $completed = 0;
    foreach ($sections as $section => $weight) {
        if ($this->isSectionComplete($section)) {
            $completed += $weight;
        }
    }

    return $completed;
}
```

### Section Completion Requirements

**Basic Profile (15%):**
- First name provided
- Date of birth provided
- Gender selected
- Bio written (min 50 characters)
- Location selected

**Photos (20%):**
- At least 2 photos uploaded
- Primary photo set

**Cultural Profile (15%):**
- Religion selected
- Religiosity level selected
- Prayer frequency selected
- Ethnicity selected
- At least 1 language selected
- Dietary preference selected

**Career Profile (15%):**
- Education level selected
- Occupation provided
- Industry selected
- Work status selected
- Income level selected

**Physical Profile (10%):**
- Height provided
- Body type selected
- Fitness level selected

**Family Preferences (15%):**
- Marital status selected
- Children preferences selected
- Family involvement importance selected
- Marriage timeline selected

**Location Preferences (10%):**
- Relocation willingness selected
- Maximum distance selected
- Long distance openness selected

### Completion Rewards

**Benefits of Complete Profile:**
- **25% visibility boost** in discovery
- **Higher match quality** (better algorithm results)
- **Premium-like features** for 7 days (one-time reward)
- **Verification eligibility** (can apply for verification)
- **Trust indicator** (completion badge on profile)

**Visual Progress:**
```php
// Livewire component displays real-time progress
<div class="profile-completion-bar">
    <div class="progress" style="width: {{ $profile->completion_percentage }}%"></div>
    <span>{{ $profile->completion_percentage }}% Complete</span>
</div>

@if($profile->completion_percentage < 100)
    <div class="completion-suggestions">
        <h4>Complete your profile to get more matches!</h4>
        <ul>
            @foreach($profile->incomplete_sections as $section)
                <li>
                    <a href="{{ route('profile.edit', $section) }}">
                        Complete {{ $section }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
```

---

## Profile Verification Badges

### 5 Verification Types

#### 1. Identity Verification
**What's Verified**: Government-issued ID matches profile information

**Required Documents:**
- Government-issued ID (passport, driver's license, national ID)
- Selfie holding ID
- Liveness check (follow on-screen prompts)

**Verification Process:**
1. Upload ID document (front and back)
2. Upload selfie holding ID with today's date
3. Complete liveness check (blink, turn head)
4. Admin reviews within 24-48 hours
5. Badge granted upon approval

**Benefits:**
- Blue verified badge
- Increased trust
- Higher visibility in discovery
- Access to verified-only filter

#### 2. Photo Verification
**What's Verified**: Profile photos are current and authentic

**Required:**
- Live selfie following pose instructions
- Facial recognition match with profile photos

**Verification Process:**
1. Follow on-screen pose instructions
2. Take live selfie
3. Automated facial recognition check
4. Admin review for confirmation
5. Badge granted within 24 hours

**Benefits:**
- Photo verified badge
- Reduces catfishing concerns
- Better match quality

#### 3. Employment Verification
**What's Verified**: Current employment status and occupation

**Required Documents:**
- Employment letter on company letterhead
- Recent pay stub OR business card with company email
- LinkedIn profile (optional, helps speed up verification)

**Verification Process:**
1. Upload employment documentation
2. Provide LinkedIn profile URL (optional)
3. Admin may contact employer for verification
4. Badge granted within 3-5 business days

**Benefits:**
- Employment verified badge
- Career information trusted
- Appeals to serious users

#### 4. Education Verification
**What's Verified**: Educational qualifications

**Required Documents:**
- Diploma or degree certificate
- University email verification OR transcript
- LinkedIn education section (optional)

**Verification Process:**
1. Upload degree certificate
2. Verify university email address
3. Admin confirms with institution if needed
4. Badge granted within 3-5 business days

**Benefits:**
- Education verified badge
- Credentials trusted
- Attractive to educated users

#### 5. Income Verification
**What's Verified**: Income bracket (range, not exact amount)

**Required Documents:**
- Recent tax return OR
- Last 3 months of pay stubs OR
- Bank statements showing income (personal info can be redacted)

**Verification Process:**
1. Upload income documentation
2. Admin verifies income falls within stated bracket
3. Only income range is verified (privacy protected)
4. Badge granted within 5-7 business days

**Benefits:**
- Income verified badge
- Financial transparency
- Privacy maintained (exact amount not disclosed)

**Privacy Note**: All verification documents are encrypted, viewed only by verification team, and automatically deleted after 30 days of verification.

### Verification API Endpoints

```
POST /api/v1/verification/identity
POST /api/v1/verification/photo
POST /api/v1/verification/employment
POST /api/v1/verification/education
POST /api/v1/verification/income
GET /api/v1/verification/status
```

**Livewire Component:**
- `Profile/VerificationCenter` - Manage all verifications

---

## Profile Privacy Controls

### Visibility Settings

**Profile Visibility Modes:**

#### 1. Public Mode
- Profile visible to all active users in discovery
- Appears in search results
- Can be liked by anyone
- Full profile visible to everyone

**Best For**: Users wanting maximum exposure and matches

#### 2. Matches Only Mode
- Profile visible only in discovery (blurred details)
- Full profile visible only to matched users
- Photos visible only to matches
- Bio and detailed info hidden until match

**Best For**: Users wanting more privacy while still being discoverable

#### 3. Private Mode
- Profile hidden from discovery and search
- Accessible only via direct profile URL (UUID-based)
- Can only match with users you like first
- Maximum privacy protection

**Best For**: Users wanting complete control over who sees their profile

### Granular Privacy Controls

**Individual Field Privacy:**
```php
// In UserSetting model
public $privacy_settings = [
    'show_last_name' => true,
    'show_age' => true,
    'show_exact_age' => false,      // Show age range instead
    'show_online_status' => true,
    'show_last_active' => true,
    'show_distance' => true,
    'show_exact_distance' => false,  // Show approximate distance
    'show_city' => true,
    'show_exact_location' => false,  // Show only country
    'allow_profile_search' => true,
    'show_in_discovery' => true,
    'allow_screenshots' => false,    // Notify if screenshot detected
];
```

**Privacy Control Options:**
- **Last Name**: Show, Hide, Show to matches only
- **Age**: Show exact, Show range (25-30), Hide
- **Online Status**: Show, Hide, Show to matches only
- **Last Active**: Show, Hide, Show to matches only
- **Distance**: Show exact, Show approximate, Hide
- **Location**: Show city, Show country only, Hide
- **Photos**: Public, Matches only, Private

### Screenshot Detection

**Feature**: Notify users when someone takes a screenshot of their profile or chat

**How It Works:**
- JavaScript detection on web
- Native detection on mobile apps
- Notification sent to profile owner
- Screenshot event logged for safety tracking

**API Endpoint:**
- `POST /api/v1/screenshot-detected` - Log screenshot event

---

## Profile Security Features

### UUID-Based Profile URLs

**Implementation:**
```php
// User model
protected static function boot()
{
    parent::boot();

    static::creating(function ($user) {
        $user->profile_uuid = Str::uuid();
    });
}

public function getProfileUrlAttribute(): string
{
    return route('profile.show', ['uuid' => $this->profile_uuid]);
}

public static function findByProfileUuid(string $uuid): ?User
{
    return static::where('profile_uuid', $uuid)->first();
}
```

**Security Benefits:**
- Non-guessable URLs (36-character UUIDs)
- No sequential IDs that reveal user count
- Cannot enumerate users by incrementing numbers
- Privacy protection from URL guessing

**Example URLs:**
```
Old (Insecure): /user/123
New (Secure):   /user/970d979c-681a-48ae-9020-59945293c62e
```

### Profile Blocking

**Features:**
- Block users permanently
- Blocked users cannot see your profile
- Your profile hidden from blocked users
- Automatic unmatch upon blocking
- All chat history deleted
- Prevents future matching

**Block Flow:**
1. User clicks "Block" on profile or chat
2. Confirmation modal appears
3. Upon confirmation:
   - Unmatch if currently matched
   - Delete all messages between users
   - Hide profiles from each other
   - Prevent future matching
4. User can unblock later from settings

**API Endpoints:**
```
POST /api/v1/users/{userId}/block
DELETE /api/v1/users/{userId}/block
GET /api/v1/blocked-users
```

---

## Best Practices

### For Users

**Profile Creation:**
1. Use recent, clear photos showing your face
2. Write an authentic bio (avoid clichés)
3. Be honest about religious practices
4. Complete all sections for better matches
5. Get verified to increase trust

**Privacy:**
1. Review privacy settings regularly
2. Don't share personal contact information in profile
3. Use matches-only mode if concerned about privacy
4. Enable screenshot notifications
5. Report suspicious profiles immediately

**Photo Guidelines:**
1. Minimum 2 photos, ideally 4-6
2. Include full-body photo
3. Show genuine smile
4. Avoid group photos as primary
5. Ensure photos are recent (within 1 year)
6. Modest photos respecting Islamic values

### For Developers

**Profile Updates:**
1. Always validate input server-side
2. Use transactions for multi-table updates
3. Clear relevant caches after updates
4. Dispatch profile-updated events for real-time UI updates
5. Log significant profile changes

**Privacy:**
1. Never expose UUIDs in client-side code patterns
2. Always check authorization before showing profile
3. Respect privacy settings in all queries
4. Audit privacy setting changes
5. Encrypt sensitive verification documents

**Performance:**
1. Eager load relationships when displaying profiles
2. Cache profile data for frequently viewed profiles
3. Use database indexes on frequently queried fields
4. Lazy load photos and media
5. Optimize images on upload

---

## API Reference

### Profile Endpoints

```http
GET /api/v1/profile
GET /api/v1/profile/{uuid}
PUT /api/v1/profile
PUT /api/v1/profile/cultural
PUT /api/v1/profile/career
PUT /api/v1/profile/physical
PUT /api/v1/profile/family
PUT /api/v1/profile/location

GET /api/v1/profile/photos
POST /api/v1/profile/photos
PUT /api/v1/profile/photos/{photoId}
DELETE /api/v1/profile/photos/{photoId}

POST /api/v1/verification/{type}
GET /api/v1/verification/status

PUT /api/v1/profile/privacy
GET /api/v1/profile/completion
```

### Response Example

```json
{
  "type": "user",
  "id": "970d979c-681a-48ae-9020-59945293c62e",
  "attributes": {
    "first_name": "Ahmed",
    "age": 28,
    "gender": "male",
    "bio": "Practicing Muslim seeking serious relationship...",
    "completion_percentage": 85,
    "verified_badges": ["identity", "photo"],
    "profile_url": "/user/970d979c-681a-48ae-9020-59945293c62e"
  },
  "relationships": {
    "photos": {
      "data": [
        {"type": "photo", "id": "1"},
        {"type": "photo", "id": "2"}
      ]
    },
    "cultural_profile": {
      "data": {"type": "cultural_profile", "id": "1"}
    }
  },
  "included": [
    {
      "type": "photo",
      "id": "1",
      "attributes": {
        "url": "https://r2.cloudflare.com/photos/1.jpg",
        "is_primary": true
      }
    },
    {
      "type": "cultural_profile",
      "id": "1",
      "attributes": {
        "religion": "Islam",
        "sect": "Sunni",
        "religiosity_level": "religious",
        "prayer_frequency": "5 times daily"
      }
    }
  ]
}
```

---

*Last Updated: October 2025*
