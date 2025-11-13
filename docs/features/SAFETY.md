# Safety & Privacy Features Documentation

## Overview

YorYor prioritizes user safety and privacy with comprehensive features designed specifically for Muslim dating. The platform implements multiple layers of protection including panic button emergency system, evidence-based reporting, 5-type verification, family involvement, and granular privacy controls.

---

## Panic Button Emergency System

### Overview

Industry-leading emergency assistance system integrated throughout the app with one-tap activation, GPS location sharing, and automatic alerts to emergency contacts and admin.

### Features

**Activation Methods:**
1. **In-App Button**: Red panic button visible on all screens
2. **Volume Pattern**: Hold volume down button for 5 seconds
3. **Voice Activation**: "Hey YorYor, emergency" (when enabled)
4. **Widget**: Home screen widget for quick access (mobile)

**What Happens When Activated:**
1. GPS location captured
2. Emergency SMS sent to all emergency contacts
3. Admin alert triggered immediately
4. Optional police notification
5. Panic history recorded
6. User can cancel if false alarm

### Database Schema

```sql
CREATE TABLE panic_activations (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    trigger_method ENUM('button', 'volume', 'voice', 'widget'),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    address TEXT,  -- Reverse geocoded address
    metadata JSON,  -- Context: date location, match info, etc.
    status ENUM('active', 'cancelled', 'resolved'),
    cancelled_at TIMESTAMP NULL,
    resolved_at TIMESTAMP NULL,
    admin_notes TEXT NULL,
    created_at TIMESTAMP,
    INDEX idx_user_status (user_id, status),
    INDEX idx_created (created_at)
);
```

### Implementation

**PanicButtonService:**
```php
namespace App\Services;

use App\Models\PanicActivation;
use App\Models\UserEmergencyContact;
use App\Jobs\SendEmergencyNotificationJob;
use Illuminate\Support\Facades\DB;

class PanicButtonService
{
    /**
     * Activate panic button
     */
    public function activate(int $userId, array $data): PanicActivation
    {
        DB::beginTransaction();
        try {
            // Create panic activation record
            $panic = PanicActivation::create([
                'user_id' => $userId,
                'trigger_method' => $data['trigger_method'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'address' => $this->reverseGeocode($data['latitude'], $data['longitude']),
                'metadata' => json_encode([
                    'context' => $data['context'] ?? 'unknown',
                    'match_id' => $data['match_id'] ?? null,
                    'device_info' => $data['device_info'] ?? null,
                ]),
                'status' => 'active',
            ]);

            // Get emergency contacts
            $contacts = UserEmergencyContact::where('user_id', $userId)
                ->where('verified', true)
                ->orderBy('priority')
                ->get();

            // Send emergency notifications
            foreach ($contacts as $contact) {
                SendEmergencyNotificationJob::dispatch($panic, $contact);
            }

            // Alert admin
            $this->alertAdmin($panic);

            // Optional: Notify police
            if ($data['notify_police'] ?? false) {
                $this->notifyPolice($panic);
            }

            DB::commit();

            return $panic;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancel panic activation (false alarm)
     */
    public function cancel(int $panicId, int $userId, string $password): bool
    {
        $panic = PanicActivation::where('id', $panicId)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->firstOrFail();

        // Verify password to prevent unauthorized cancellation
        if (!Hash::check($password, auth()->user()->password)) {
            throw new \Exception('Invalid password');
        }

        $panic->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Notify contacts of cancellation
        $this->notifyContactsOfCancellation($panic);

        return true;
    }

    /**
     * Reverse geocode coordinates to address
     */
    private function reverseGeocode(float $lat, float $lng): string
    {
        // Use geocoding service (Google Maps, Mapbox, etc.)
        try {
            $response = Http::get("https://api.mapbox.com/geocoding/v5/mapbox.places/{$lng},{$lat}.json", [
                'access_token' => config('services.mapbox.token'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['features'][0]['place_name'] ?? 'Unknown location';
            }
        } catch (\Exception $e) {
            Log::error('Reverse geocoding failed', ['error' => $e->getMessage()]);
        }

        return "Lat: {$lat}, Lng: {$lng}";
    }

    /**
     * Alert admin team
     */
    private function alertAdmin(PanicActivation $panic): void
    {
        // Send to admin Slack/Discord channel
        // Send email to admin team
        // Create high-priority admin notification

        event(new PanicButtonActivatedEvent($panic));
    }

    /**
     * Notify police (optional feature)
     */
    private function notifyPolice(PanicActivation $panic): void
    {
        // Integration with local police API
        // Send SMS to emergency services
        // Requires user consent and local regulations
    }
}
```

**API Endpoints:**
```http
POST /api/v1/panic-button/activate
POST /api/v1/panic-button/{panicId}/cancel
GET /api/v1/panic-button/history
```

**Rate Limiting:**
- 5 activations per day (prevent abuse)
- No rate limit on cancellations

### Silent Activation Mode

**For Dangerous Situations:**
- No audio or visual feedback
- No screen change
- Vibration disabled
- Works in background
- Sends alerts silently

**Activation:**
- Triple-press volume down button
- Or triple-tap panic button widget

---

## Emergency Contacts Management

### Features

**Contact Management:**
- Add up to 5 emergency contacts
- Priority ordering (1-5)
- Contact verification via SMS/email
- Custom emergency messages per contact
- Test emergency system

### Database Schema

```sql
CREATE TABLE emergency_contacts (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    relationship VARCHAR(50),  -- Family, Friend, etc.
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    priority INTEGER DEFAULT 1,  -- 1 = highest
    verified BOOLEAN DEFAULT FALSE,
    verified_at TIMESTAMP NULL,
    custom_message TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_user_priority (user_id, priority)
);
```

### Implementation

**Adding Emergency Contact:**
```php
public function addEmergencyContact(Request $request): JsonResponse
{
    $validated = $request->validate([
        'name' => 'required|string|max:100',
        'relationship' => 'required|string|max:50',
        'phone' => 'required|string|max:20',
        'email' => 'nullable|email|max:100',
        'priority' => 'required|integer|min:1|max:5',
        'custom_message' => 'nullable|string|max:500',
    ]);

    // Check limit (max 5)
    $contactCount = UserEmergencyContact::where('user_id', auth()->id())->count();
    if ($contactCount >= 5) {
        abort(422, 'Maximum 5 emergency contacts allowed');
    }

    $contact = UserEmergencyContact::create([
        'user_id' => auth()->id(),
        'name' => $validated['name'],
        'relationship' => $validated['relationship'],
        'phone' => $validated['phone'],
        'email' => $validated['email'] ?? null,
        'priority' => $validated['priority'],
        'custom_message' => $validated['custom_message'] ?? null,
        'verified' => false,
    ]);

    // Send verification SMS/email
    $this->sendVerification($contact);

    return response()->json([
        'status' => 'success',
        'contact' => $contact,
    ], 201);
}

private function sendVerification(UserEmergencyContact $contact): void
{
    $verificationCode = rand(100000, 999999);

    Cache::put(
        "emergency_contact_verify_{$contact->id}",
        $verificationCode,
        now()->addMinutes(15)
    );

    // Send SMS
    SMS::send($contact->phone, "Your YorYor emergency contact verification code: {$verificationCode}");

    // Send email if provided
    if ($contact->email) {
        Mail::to($contact->email)->send(new EmergencyContactVerificationMail($contact, $verificationCode));
    }
}
```

**API Endpoints:**
```http
GET /api/v1/emergency-contacts
POST /api/v1/emergency-contacts
PUT /api/v1/emergency-contacts/{contactId}
DELETE /api/v1/emergency-contacts/{contactId}
POST /api/v1/emergency-contacts/{contactId}/verify
POST /api/v1/emergency-contacts/test
```

### Test Emergency System

**Feature:**
Users can test the emergency system without triggering real panic

**Test Mode:**
- Sends test SMS/email to contacts
- Does not alert admin
- Does not notify police
- Marks as "test" in logs

**API Endpoint:**
```http
POST /api/v1/emergency-contacts/test
```

---

## User Blocking & Reporting

### User Blocking

**Features:**
- Block users permanently
- Blocked users cannot see your profile
- Your profile hidden from blocked users
- Automatic unmatch
- All chat history deleted
- Prevents future matching

**Block Flow:**
```php
public function blockUser(int $userIdToBlock): JsonResponse
{
    $userId = auth()->id();

    DB::beginTransaction();
    try {
        // Create block record
        UserBlock::create([
            'user_id' => $userId,
            'blocked_user_id' => $userIdToBlock,
        ]);

        // Unmatch if matched
        Match::where(function ($query) use ($userId, $userIdToBlock) {
            $query->where('user1_id', $userId)->where('user2_id', $userIdToBlock);
        })->orWhere(function ($query) use ($userId, $userIdToBlock) {
            $query->where('user1_id', $userIdToBlock)->where('user2_id', $userId);
        })->delete();

        // Delete chat history (optional - based on user preference)
        $this->deleteChatHistory($userId, $userIdToBlock);

        // Remove from likes
        Like::where('user_id', $userId)->where('liked_user_id', $userIdToBlock)->delete();
        Like::where('user_id', $userIdToBlock)->where('liked_user_id', $userId)->delete();

        DB::commit();

        return response()->json(['status' => 'success']);
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

**API Endpoints:**
```http
POST /api/v1/users/{userId}/block
DELETE /api/v1/users/{userId}/block  (Unblock)
GET /api/v1/blocked-users
```

### Evidence-Based Reporting System

**10+ Report Categories:**
1. Inappropriate behavior
2. Fake profile
3. Harassment
4. Spam or scam
5. Underage user
6. Inappropriate photos
7. Offensive messages
8. Requesting money
9. Catfishing
10. Other concerns

**Database Schema:**
```sql
CREATE TABLE enhanced_user_reports (
    id BIGINT PRIMARY KEY,
    reporter_id BIGINT NOT NULL,
    reported_user_id BIGINT NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('pending', 'under_review', 'resolved', 'dismissed'),
    admin_notes TEXT NULL,
    action_taken VARCHAR(100) NULL,  -- warning, suspension, ban, etc.
    reviewed_by BIGINT NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_status_priority (status, priority),
    INDEX idx_reported_user (reported_user_id)
);

CREATE TABLE report_evidence (
    id BIGINT PRIMARY KEY,
    report_id BIGINT NOT NULL,
    type ENUM('screenshot', 'message', 'photo', 'other'),
    file_path TEXT,
    description TEXT,
    created_at TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES enhanced_user_reports(id) ON DELETE CASCADE
);
```

### Report Process

**Step-by-Step Flow:**

1. **Select Report Reason**
2. **Provide Detailed Description** (min 20 characters)
3. **Upload Evidence** (screenshots, messages - optional but recommended)
4. **Submit Report**
5. **Automatic Temporary Restriction** (reported user)
6. **Admin Review** (within 24 hours)
7. **Action Taken** (warning, suspension, ban)
8. **Reporter Notification**

**Implementation:**
```php
public function reportUser(Request $request, int $reportedUserId): JsonResponse
{
    $validated = $request->validate([
        'category' => 'required|in:inappropriate_behavior,fake_profile,harassment,spam,underage,inappropriate_photos,offensive_messages,requesting_money,catfishing,other',
        'description' => 'required|string|min:20|max:2000',
        'evidence' => 'nullable|array|max:5',
        'evidence.*' => 'file|mimes:jpeg,png,pdf|max:5120',  // 5MB
    ]);

    DB::beginTransaction();
    try {
        // Create report
        $report = EnhancedUserReport::create([
            'reporter_id' => auth()->id(),
            'reported_user_id' => $reportedUserId,
            'category' => $validated['category'],
            'description' => $validated['description'],
            'priority' => $this->calculatePriority($validated['category']),
            'status' => 'pending',
        ]);

        // Upload evidence
        if (!empty($validated['evidence'])) {
            foreach ($validated['evidence'] as $file) {
                $path = $file->store('report-evidence', 'private');

                ReportEvidence::create([
                    'report_id' => $report->id,
                    'type' => $this->detectEvidenceType($file),
                    'file_path' => $path,
                ]);
            }
        }

        // Automatic temporary restriction for serious categories
        if (in_array($validated['category'], ['harassment', 'inappropriate_photos', 'underage'])) {
            $this->applyTemporaryRestriction($reportedUserId);
        }

        // Alert admin
        event(new UserReportedEvent($report));

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Report submitted successfully. Our team will review within 24 hours.',
            'report_id' => $report->id,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

private function calculatePriority(string $category): string
{
    $criticalCategories = ['underage', 'harassment'];
    $highCategories = ['inappropriate_photos', 'requesting_money', 'catfishing'];

    if (in_array($category, $criticalCategories)) {
        return 'critical';
    } elseif (in_array($category, $highCategories)) {
        return 'high';
    } else {
        return 'medium';
    }
}

private function applyTemporaryRestriction(int $userId): void
{
    User::where('id', $userId)->update([
        'restricted_until' => now()->addHours(24),
        'restriction_reason' => 'pending_report_review',
    ]);
}
```

**API Endpoint:**
```http
POST /api/v1/users/{userId}/report
Content-Type: multipart/form-data

category: harassment
description: User has been sending threatening messages
evidence[]: (file upload)
evidence[]: (file upload)
```

---

## Verification System

### 5 Verification Types

#### 1. Identity Verification

**Requirements:**
- Government-issued ID (passport, driver's license, national ID)
- Selfie holding ID with today's date
- Liveness check (blink, turn head)

**Process:**
1. User uploads ID (front and back)
2. User uploads selfie with ID
3. User completes liveness check
4. Admin reviews within 24-48 hours
5. Badge granted upon approval

**Benefits:**
- Blue verified badge
- Increased trust (75% more matches)
- Higher visibility in discovery (+50%)
- Access to verified-only filter

#### 2. Photo Verification

**Requirements:**
- Live selfie following pose instructions
- Facial recognition match with profile photos

**Process:**
1. User follows on-screen pose instructions
2. User takes live selfie
3. Automated facial recognition check
4. Admin review for confirmation
5. Badge granted within 24 hours

**Benefits:**
- Photo verified badge
- Reduces catfishing concerns
- Better match quality

#### 3. Employment Verification

**Requirements:**
- Employment letter on company letterhead
- Recent pay stub OR business card
- LinkedIn profile (optional)

**Process:**
1. User uploads employment documentation
2. User provides LinkedIn URL (optional)
3. Admin may contact employer for verification
4. Badge granted within 3-5 business days

**Benefits:**
- Employment verified badge
- Career information trusted
- Appeals to serious users

#### 4. Education Verification

**Requirements:**
- Diploma or degree certificate
- University email verification OR transcript
- LinkedIn education section (optional)

**Process:**
1. User uploads degree certificate
2. User verifies university email
3. Admin confirms with institution if needed
4. Badge granted within 3-5 business days

**Benefits:**
- Education verified badge
- Credentials trusted
- Attractive to educated users

#### 5. Income Verification

**Requirements:**
- Recent tax return OR
- Last 3 months of pay stubs OR
- Bank statements (personal info redacted)

**Process:**
1. User uploads income documentation
2. Admin verifies income falls within stated bracket
3. Only income range verified (privacy protected)
4. Badge granted within 5-7 business days

**Benefits:**
- Income verified badge
- Financial transparency
- Privacy maintained

**Privacy:**
- All documents encrypted at rest
- Viewed only by verification team
- Automatically deleted after 30 days
- GDPR compliant

**API Endpoints:**
```http
POST /api/v1/verification/identity
POST /api/v1/verification/photo
POST /api/v1/verification/employment
POST /api/v1/verification/education
POST /api/v1/verification/income
GET /api/v1/verification/status
```

---

## Safety Score System

### Overview

Automated safety scoring system that evaluates user trustworthiness based on behavior patterns.

### Scoring Factors

**Positive Factors (+points):**
- Profile completion (+10)
- Verification badges (+15 each)
- Consistent activity (+5)
- Positive user interactions (+3 each)
- No reports against user (+20)
- Long-term membership (+10)

**Negative Factors (-points):**
- Reports against user (-20 each)
- Blocked by other users (-10 each)
- Suspicious activity patterns (-15)
- Incomplete profile (-5)
- Low response rate (-5)

**Safety Score Ranges:**
- **90-100**: Excellent (green badge)
- **70-89**: Good (blue badge)
- **50-69**: Average (yellow badge)
- **Below 50**: Poor (red badge, restricted)

**Database Schema:**
```sql
CREATE TABLE user_safety_scores (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL UNIQUE,
    score INTEGER DEFAULT 50,  -- 0-100
    last_calculated_at TIMESTAMP,
    factors JSON,  -- Breakdown of score calculation
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_score (score)
);
```

**Recalculation:**
- Triggered daily
- Triggered after significant events (report, block, verification)
- Cached for performance

---

## Privacy Controls

### Profile Visibility Settings

**3 Visibility Modes:**

1. **Public Mode**
   - Visible to all active users in discovery
   - Appears in search results
   - Can be liked by anyone

2. **Matches Only Mode**
   - Profile visible in discovery (blurred)
   - Full profile visible only to matched users
   - Photos visible only to matches

3. **Private Mode**
   - Hidden from discovery and search
   - Accessible only via direct URL (UUID)
   - Can only match with users you like first

### Granular Privacy Controls

**Individual Field Privacy:**
- Last Name: Show, Hide, Matches only
- Age: Show exact, Show range, Hide
- Online Status: Show, Hide, Matches only
- Last Active: Show, Hide, Matches only
- Distance: Show exact, Show approximate, Hide
- Location: Show city, Show country only, Hide
- Photos: Public, Matches only, Private

**API Endpoint:**
```http
PUT /api/v1/profile/privacy
Content-Type: application/json

{
  "profile_visibility": "matches_only",
  "show_last_name": false,
  "show_exact_age": false,
  "show_online_status": true,
  "show_last_active": false,
  "show_distance": true,
  "show_exact_distance": false,
  "show_city": true,
  "photo_visibility": "matches_only"
}
```

### Screenshot Detection

**Features:**
- Detect screenshots on web and mobile
- Notify profile owner
- Log event for safety tracking
- Discourage inappropriate behavior

**Implementation:**
```javascript
// Web detection
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        axios.post('/api/v1/screenshot-detected', {
            context: 'profile',
            profile_id: profileId
        });
    }
});

// Mobile detection (native app)
// Uses native screenshot listener
```

---

## Family Involvement Features

### Family Member Accounts

**Features:**
- Add family members with limited access
- View-only profiles
- Approve/disapprove matches
- Communicate with potential match families

**Access Levels:**
1. **View Only**: Can view profile and matches
2. **Advisor**: Can view + provide feedback
3. **Approver**: Can view + must approve matches

**Database Schema:**
```sql
CREATE TABLE family_members (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    relationship VARCHAR(50),  -- Mother, Father, Brother, Sister, etc.
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    access_level ENUM('view_only', 'advisor', 'approver'),
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_user (user_id)
);

CREATE TABLE family_approvals (
    id BIGINT PRIMARY KEY,
    match_id BIGINT NOT NULL,
    family_member_id BIGINT NOT NULL,
    status ENUM('pending', 'approved', 'rejected'),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_match (match_id)
);
```

### Family Approval Workflow

**Flow:**
1. User gets matched
2. Match sent to family members with "Approver" access
3. Family reviews profile
4. Family approves or rejects with notes
5. User notified of family decision
6. If approved, user can proceed with match

**Implementation:**
```php
public function requestFamilyApproval(int $matchId): JsonResponse
{
    $match = Match::findOrFail($matchId);

    // Get family members with approver access
    $approvers = FamilyMember::where('user_id', auth()->id())
        ->where('access_level', 'approver')
        ->where('verified', true)
        ->get();

    foreach ($approvers as $approver) {
        FamilyApproval::create([
            'match_id' => $matchId,
            'family_member_id' => $approver->id,
            'status' => 'pending',
        ]);

        // Send notification
        Mail::to($approver->email)->send(new FamilyApprovalRequestMail($match, $approver));
    }

    return response()->json(['status' => 'success']);
}
```

---

## Professional Matchmaker System

### Overview

Integrated professional matchmaker services for users who prefer traditional matchmaking approach.

### Matchmaker Features

**For Users:**
- Browse certified matchmakers
- Hire matchmaker for consultation
- Receive match suggestions from matchmaker
- Accept/decline matchmaker introductions
- Rate and review matchmakers

**For Matchmakers:**
- Professional profile with credentials
- Client management dashboard
- Suggest matches from database
- Schedule consultations
- Track success rate
- Earnings dashboard

**Database Schema:**
```sql
CREATE TABLE matchmakers (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL UNIQUE,
    bio TEXT,
    specialties JSON,  -- Muslim matchmaking, specific cultures, etc.
    years_experience INTEGER,
    success_rate DECIMAL(5,2),  -- Percentage
    certification VARCHAR(100),
    hourly_rate DECIMAL(10,2),
    verified BOOLEAN DEFAULT FALSE,
    rating DECIMAL(3,2),  -- 0.00-5.00
    total_reviews INTEGER DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE matchmaker_consultations (
    id BIGINT PRIMARY KEY,
    matchmaker_id BIGINT NOT NULL,
    client_id BIGINT NOT NULL,
    type ENUM('video', 'audio', 'chat'),
    scheduled_at TIMESTAMP,
    duration INTEGER,  -- minutes
    status ENUM('scheduled', 'completed', 'cancelled'),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE matchmaker_introductions (
    id BIGINT PRIMARY KEY,
    matchmaker_id BIGINT NOT NULL,
    client_id BIGINT NOT NULL,
    suggested_user_id BIGINT NOT NULL,
    status ENUM('pending', 'accepted', 'declined'),
    matchmaker_notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**API Endpoints:**
```http
GET /api/v1/matchmakers
GET /api/v1/matchmakers/{matchmakerId}
POST /api/v1/matchmakers/{matchmakerId}/hire
POST /api/v1/matchmakers/consultations
GET /api/v1/matchmakers/introductions
POST /api/v1/matchmakers/introductions/{introductionId}/respond
```

---

## Content Moderation

### Automated Moderation

**AI-Powered Content Detection:**
- Profanity filter
- NSFW image detection
- Spam detection
- Scam pattern recognition
- Link safety checking

**Implementation:**
```php
public function moderateContent(string $content, string $type = 'text'): array
{
    $flags = [];

    // Profanity check
    if ($this->containsProfanity($content)) {
        $flags[] = 'profanity';
    }

    // Link safety (for text)
    if ($type === 'text') {
        $links = $this->extractLinks($content);
        foreach ($links as $link) {
            if ($this->isUnsafeLink($link)) {
                $flags[] = 'unsafe_link';
            }
        }
    }

    // NSFW detection (for images)
    if ($type === 'image') {
        $nsfwScore = $this->detectNSFW($content);
        if ($nsfwScore > 0.7) {
            $flags[] = 'inappropriate_image';
        }
    }

    // Spam detection
    if ($this->isSpam($content)) {
        $flags[] = 'spam';
    }

    return [
        'is_safe' => empty($flags),
        'flags' => $flags,
        'confidence' => $this->calculateConfidence($flags),
    ];
}
```

### Manual Review Queue

**Admin Moderation:**
- Review flagged content
- Review user reports
- Review verification requests
- Take action (approve, reject, ban)

---

## Best Practices

### For Users

**Safety Tips:**
1. Never share personal contact info immediately
2. Meet in public places for first dates
3. Tell family/friends about date plans
4. Use panic button if feeling unsafe
5. Trust your instincts
6. Report suspicious behavior immediately

**Privacy Tips:**
1. Review privacy settings regularly
2. Don't share exact location
3. Use matches-only visibility if concerned
4. Be cautious of requests for money
5. Verify matches before sharing sensitive info

### For Developers

**Security:**
1. Encrypt sensitive verification documents
2. Rate limit reporting to prevent abuse
3. Implement automated pattern detection
4. Regular security audits
5. GDPR compliance for all data

**Performance:**
1. Cache safety scores
2. Queue emergency notifications
3. Batch process moderation checks
4. Index report queries
5. Optimize verification document storage

---

## API Reference

### Safety Endpoints

```http
POST /api/v1/panic-button/activate
POST /api/v1/panic-button/{panicId}/cancel
GET /api/v1/panic-button/history

GET /api/v1/emergency-contacts
POST /api/v1/emergency-contacts
PUT /api/v1/emergency-contacts/{contactId}
DELETE /api/v1/emergency-contacts/{contactId}
POST /api/v1/emergency-contacts/{contactId}/verify
POST /api/v1/emergency-contacts/test

POST /api/v1/users/{userId}/block
DELETE /api/v1/users/{userId}/block
GET /api/v1/blocked-users

POST /api/v1/users/{userId}/report
GET /api/v1/reports/{reportId}

POST /api/v1/verification/{type}
GET /api/v1/verification/status

PUT /api/v1/profile/privacy
POST /api/v1/screenshot-detected
```

---

*Last Updated: October 2025*
