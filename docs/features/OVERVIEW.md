# YorYor Features Overview

## Platform Purpose

YorYor is a comprehensive Muslim-focused dating and matchmaking platform that combines modern dating app features with traditional Islamic matchmaking values. The platform provides a safe, culturally-appropriate environment for Muslims seeking meaningful connections with serious commitment to marriage.

**Core Mission:** Modernizing traditional matchmaking while respecting Islamic cultural and religious values, emphasizing family involvement, and fostering serious relationships.

---

## Core Features List (30+ Features)

### User Management & Profiles
1. Multi-method authentication (Email/Password, OTP, Google OAuth, 2FA)
2. Multi-section profile system (6 extended profile sections)
3. Photo management (2-10 photos with verification)
4. Profile completion tracking with rewards
5. Profile visibility controls (public, matches-only, private)
6. UUID-based secure profile URLs
7. Multi-language support (English, Uzbek, Russian)

### Matching & Discovery
8. AI-powered compatibility matching algorithm
9. Swipe card interface (Tinder-style)
10. Grid discovery view
11. Advanced search filters (15+ criteria)
12. Daily curated recommendations
13. Like/dislike system with mutual match creation
14. Compatibility score calculation
15. Super likes feature (premium)
16. Profile boost functionality
17. Rewind last swipe (premium)

### Communication
18. Real-time messaging via Laravel Reverb WebSocket
19. Read receipts and typing indicators
20. Media sharing (photos, videos, voice notes)
21. Message editing and deletion
22. Chat archiving and management
23. Video calling (VideoSDK primary, Agora backup)
24. Audio calling with HD quality
25. Call history and statistics

### Safety & Privacy
26. Panic button with GPS location sharing
27. Emergency contacts management (up to 5 contacts)
28. User blocking and reporting system
29. Evidence-based reporting with admin review
30. Screenshot detection and notification
31. Location approximation (privacy protection)
32. Content moderation (AI-powered + manual)
33. Safety score system

### Verification & Trust
34. 5-type verification system (Identity, Photo, Employment, Education, Income)
35. Verification badges on profiles
36. Professional matchmaker system
37. Family approval workflow

### Subscription & Monetization
38. 3-tier subscription model (Free, Premium, Premium Plus)
39. Usage tracking and limit enforcement
40. Payment integration (Stripe ready)
41. Trial periods (7-day free Premium trial)

### Social Features
42. Stories (24-hour ephemeral content)
43. Story reactions and replies
44. Success story sharing
45. Testimonials system

### Admin & Moderation
46. Comprehensive admin dashboard
47. User management and analytics
48. Report review and moderation
49. Verification queue management
50. Safety incident tracking
51. Platform analytics and metrics

---

## Feature Categories

### 1. User Management & Profiles

**Multi-Section Profile Architecture:**
- **Basic Profile**: Name, age, gender, bio, location
- **Cultural Profile**: Religion, ethnicity, prayer frequency, dietary preferences, dress code
- **Career & Education**: Occupation, education level, income range
- **Physical Profile**: Height, body type, ethnicity, physical attributes
- **Family Preferences**: Marital status, children preferences, family values
- **Location Preferences**: Relocation willingness, geographic preferences
- **Lifestyle & Habits**: Smoking, alcohol, exercise, hobbies

**Profile Features:**
- Photo management: Upload up to 6 photos, drag-to-reorder, set primary photo
- Profile completion percentage with real-time tracking
- Privacy controls: Hide/show specific sections, last seen, distance
- Verification badges: 5 types of verification with trust indicators
- Profile visibility settings: Public, matches-only, private modes

**API Endpoints:**
- `GET /api/v1/profile` - Get authenticated user's profile
- `PUT /api/v1/profile` - Update basic profile
- `POST /api/v1/profile/photos` - Upload profile photos
- `GET /api/v1/profile/{uuid}` - View another user's profile (secure UUID)

**Livewire Components:**
- `Profile/BasicInfo` - Edit basic profile information
- `Profile/Photos` - Manage profile photos
- `Profile/CulturalBackground` - Cultural profile editing
- `Profile/CareerEducation` - Career and education information
- `Pages/UserProfilePage` - View user profiles

---

### 2. Matching & Discovery

**Discovery Methods:**
1. **Swipe Card Interface**: Tinder-style card stack with like/pass actions
2. **Grid Discovery**: Browse profiles in grid layout with filters
3. **Advanced Search**: Multi-criteria filtering with 15+ filter options
4. **Daily Recommendations**: AI-curated matches based on compatibility

**Compatibility Algorithm:**
The matching algorithm calculates compatibility scores based on:
- Religious values compatibility (30% weight)
- Lifestyle compatibility (20% weight)
- Location preferences (15% weight)
- Age and life stage (15% weight)
- Education and career (10% weight)
- Physical preferences (10% weight)

**Match Flow:**
1. User A likes User B → Stored in `likes` table
2. User B likes User A → Automatic `match` record created
3. Private chat automatically created for matched users
4. Both users receive match notifications

**Advanced Filters:**
- Age range (18-99)
- Distance radius (5-500 km)
- Height range
- Education level
- Occupation/Industry
- Religion and sect (Sunni, Shia, etc.)
- Religiosity level (Very religious to Moderate)
- Prayer frequency
- Marital status
- Children preferences
- Languages spoken
- Ethnicity
- Body type
- Lifestyle habits (smoking, alcohol)

**API Endpoints:**
- `GET /api/v1/discover` - Get discovery profiles
- `POST /api/v1/like/{userId}` - Like a user
- `POST /api/v1/dislike/{userId}` - Pass on a user
- `GET /api/v1/matches` - Get all matches
- `DELETE /api/v1/matches/{matchId}` - Unmatch a user

**Livewire Components:**
- `Dashboard/SwipeCards` - Main swipe interface
- `Dashboard/DiscoveryGrid` - Grid view discovery
- `Dashboard/ProfileModal` - Profile details modal
- `Pages/DiscoverPage` - Main discovery page

---

### 3. Communication

**Real-Time Messaging:**
- WebSocket-powered instant messaging via Laravel Reverb
- Message delivery confirmation
- Read receipts (seen status)
- Typing indicators
- Online/offline status
- Last active timestamp

**Message Types:**
- Text messages
- Photo sharing with inline preview
- Video sharing (up to 60 seconds)
- Voice messages
- Location sharing
- File attachments (PDFs)
- GIF support (integration ready)

**Chat Features:**
- Unread message counters
- Last message preview
- Conversation archiving
- Search within conversations
- Pinned conversations
- Message editing (within 15 minutes)
- Message deletion (for both sides)
- Copy message text
- Reply to specific messages

**Video & Voice Calling:**
- HD video quality (up to 1080p)
- Adaptive bitrate based on connection
- Front/back camera switching
- Picture-in-picture mode
- High-quality audio codec
- Noise and echo cancellation
- Call duration tracking
- Call history and statistics

**API Endpoints:**
- `GET /api/v1/chats` - Get all conversations
- `POST /api/v1/chats` - Create new chat
- `GET /api/v1/chats/{chatId}/messages` - Get chat messages
- `POST /api/v1/chats/{chatId}/messages` - Send message
- `PUT /api/v1/messages/{messageId}` - Edit message
- `DELETE /api/v1/messages/{messageId}` - Delete message
- `POST /api/v1/chats/{chatId}/mark-read` - Mark messages as read

**Call Endpoints:**
- `POST /api/v1/video-call/token` - Get VideoSDK token
- `POST /api/v1/video-call/create-meeting` - Create meeting
- `POST /api/v1/video-call/initiate` - Initiate call
- `POST /api/v1/video-call/{callId}/join` - Join call
- `POST /api/v1/video-call/{callId}/end` - End call

**Livewire Components:**
- `Dashboard/ChatList` - Conversation list
- `Dashboard/ChatWindow` - Active chat interface
- `Components/VideoCallModal` - Video calling interface

**JavaScript Modules:**
- `resources/js/messages.js` - Real-time chat updates
- `resources/js/video-call.js` - VideoSDK integration
- `resources/js/videosdk.js` - VideoSDK wrapper

---

### 4. Safety & Privacy

**Panic Button System:**
Emergency assistance system integrated throughout the app with one-tap activation:
- GPS location sharing to emergency contacts
- Automatic emergency SMS sending
- Police notification (optional)
- Silent activation mode
- False alarm cancellation with password
- Panic history tracking

**Emergency Contacts:**
- Add up to 5 emergency contacts
- Contact verification via SMS/email
- Priority ordering
- Custom emergency messages per contact
- Automatic location updates during panic

**User Safety Features:**
- Block users instantly
- Report profiles with evidence upload (screenshots, messages)
- Screenshot detection and notification
- Location approximation (not exact address)
- Profile hiding from specific users
- Safety tips and guidelines

**Reporting System:**
10+ report categories:
- Inappropriate behavior
- Fake profile
- Harassment
- Spam or scam
- Underage user
- Inappropriate photos
- Offensive messages
- Requesting money
- Catfishing
- Other concerns

**Report Process:**
1. Select report reason
2. Provide detailed description
3. Upload evidence (screenshots, messages)
4. Submit report
5. Automatic temporary user restriction
6. Admin review within 24 hours
7. Action taken (warning, suspension, ban)
8. Reporter notification

**Privacy Controls:**
- Incognito browsing mode (Premium Plus)
- Show/hide online status
- Show/hide last active
- Show/hide distance
- Profile visibility settings (public, matches-only, private)
- Photo privacy controls
- Blocked users list management

**API Endpoints:**
- `POST /api/v1/users/{userId}/block` - Block a user
- `DELETE /api/v1/users/{userId}/block` - Unblock a user
- `POST /api/v1/users/{userId}/report` - Report a user
- `POST /api/v1/panic-button/activate` - Activate panic button
- `GET /api/v1/emergency-contacts` - Get emergency contacts
- `POST /api/v1/emergency-contacts` - Add emergency contact

**Services:**
- `PanicButtonService` - Emergency panic button logic
- `EnhancedReportingService` - Advanced user reporting
- `PrivacyService` - Privacy controls and data protection

---

### 5. Subscription & Monetization

**3-Tier Subscription Model:**

#### Free Tier
- Basic profile creation
- Limited daily likes (10 per day)
- Match with users
- Send messages to matches
- Basic search filters
- 1 photo upload
- View who liked you (blurred)

#### Premium Tier ($9.99/month)
All Free features plus:
- Unlimited likes
- Advanced search filters
- See who liked you (unblurred)
- 6 photo uploads
- Profile boost (1/month)
- Rewind last swipe
- Read receipts
- Priority customer support
- Ad-free experience

#### Premium Plus Tier ($19.99/month)
All Premium features plus:
- Unlimited profile boosts
- Incognito mode
- Advanced AI matching algorithm
- Priority in search results
- Video profile feature
- Story highlights
- Enhanced privacy controls
- Dedicated matchmaker consultation (1/month)
- Verification priority

**Subscription Features:**
- Stripe payment integration (ready)
- In-app purchases (iOS/Android ready)
- Recurring billing management
- Payment history
- Invoice generation
- Refund system
- Multi-currency support
- 7-day free trial for Premium (no credit card required)

**Usage Tracking:**
- Track feature usage per tier
- Monthly usage reports
- Feature limit enforcement
- Upgrade prompts when limits reached
- Usage notifications

**API Endpoints:**
- `GET /api/v1/subscription/plans` - Get available plans
- `POST /api/v1/subscription/subscribe` - Subscribe to plan
- `DELETE /api/v1/subscription/cancel` - Cancel subscription
- `GET /api/v1/subscription/usage` - Get current usage
- `GET /api/v1/subscription/invoices` - Get payment history

**Services:**
- `PaymentManager` - Payment processing
- `UsageLimitsService` - Subscription limit enforcement

---

### 6. Admin & Moderation

**Admin Dashboard Features:**

#### User Management
- View all users with advanced filters
- User profile inspection
- Account status management (active, suspended, banned)
- User verification review
- Manual profile editing
- Password reset
- Email verification status
- Account deletion
- User analytics (growth, activity, retention)

#### Match Management
- View all matches
- Match statistics and quality analysis
- Unmatching functionality
- Fake match detection
- Match rate analytics

#### Chat Monitoring
- View chat conversations (with user consent)
- Message content moderation
- Inappropriate content flagging
- Chat statistics
- Report resolution

#### Report Management
- Review user reports with priority queue
- Evidence review (screenshots, messages)
- Take action (warn, suspend, ban)
- Communication with reporters
- Report statistics and pattern detection
- Automated safety flags

#### Verification Queue
- Review verification requests
- Document inspection
- Approve/reject with reasons
- Verification statistics
- Manual verification override
- Batch processing

#### Safety Management
- Panic button activation logs
- Emergency response tracking
- Safety incident management
- User safety scores
- Risk assessment dashboard
- Block/report pattern analysis

#### Analytics Dashboard
- User growth metrics
- Active user statistics (DAU, MAU, WAU)
- Match rate analytics
- Revenue tracking
- Subscription conversions
- Geographic distribution
- Feature usage analytics
- Retention metrics
- Churn analysis

**Admin Roles & Permissions:**
- **Super Admin**: Full access to all features
- **Admin**: Most features except system configuration
- **Moderator**: Content and reports only
- **Support**: User assistance only
- **Analyst**: Read-only analytics access

**Livewire Components:**
- `Admin/UserManagement` - User administration
- `Admin/ReportQueue` - Report moderation
- `Admin/VerificationQueue` - Verification review
- `Admin/Analytics` - Analytics dashboard

---

## Unique Selling Points

### 1. Islamic & Cultural Focus
- **Religion-First Matching**: Algorithm prioritizes religious compatibility
- **Prayer Time Integration**: Islamic prayer time notifications and preferences
- **Halal Dating**: No inappropriate content, family involvement encouraged
- **Cultural Sensitivity**: Respects Islamic values and cultural traditions
- **Modest Profile Options**: Hijab, niqab, modest dress code preferences

### 2. Family Involvement
- **Family Member Accounts**: Limited access for family oversight
- **Family Approval Workflow**: Get family approval before proceeding with matches
- **Shared Profile Access**: Controlled permissions for family viewing
- **Matchmaker Integration**: Professional matchmakers for traditional approach
- **Family Communication Channels**: Connect families of potential matches

### 3. Advanced Safety Features
- **Panic Button**: Industry-leading emergency system with GPS
- **Emergency Contacts**: Up to 5 contacts with automatic alerts
- **Evidence-Based Reporting**: Upload screenshots and messages as evidence
- **Safety Score System**: Track user trustworthiness
- **5-Type Verification**: Most comprehensive verification in Muslim dating apps

### 4. Professional Matchmakers
- **Certified Matchmakers**: Professional matchmaking services integrated into platform
- **Consultation Scheduling**: Video/audio calls with matchmakers
- **Introduction Requests**: Matchmakers suggest compatible matches
- **Success Tracking**: Track matchmaker effectiveness
- **Premium Plus Benefit**: Monthly matchmaker consultation included

### 5. Real-Time Communication
- **WebSocket Technology**: Instant message delivery via Laravel Reverb
- **Dual Video Providers**: VideoSDK primary, Agora backup for reliability
- **HD Video Quality**: Up to 1080p video calls
- **Low Latency**: Optimized for real-time interactions
- **Typing Indicators**: Real-time presence awareness

### 6. Privacy & Security
- **UUID Profile URLs**: Non-guessable secure profile links
- **Incognito Mode**: Browse without being seen (Premium Plus)
- **Granular Privacy Controls**: Control every aspect of profile visibility
- **Location Approximation**: Never reveal exact location
- **Screenshot Detection**: Notify when screenshots are taken

---

## Roadmap & Upcoming Features

### Q1 2026
- [ ] Video profiles (60-second intro videos)
- [ ] Group chats (family introductions)
- [ ] Compatibility quizzes
- [ ] Icebreaker questions system
- [ ] Voice messages in profiles

### Q2 2026
- [ ] Event planning (virtual and in-person Muslim events)
- [ ] Virtual gifts system
- [ ] Advanced AI matching with machine learning
- [ ] Compatibility reports (detailed personality analysis)
- [ ] Relationship coaching integration

### Q3 2026
- [ ] Pre-marital counseling directory
- [ ] Wedding planning integration
- [ ] Community events calendar
- [ ] Success story showcase platform
- [ ] Referral program enhancements

### Q4 2026
- [ ] Mobile apps (iOS & Android native)
- [ ] Wearable device integration
- [ ] AI chatbot for dating advice
- [ ] Blockchain-based verification
- [ ] Expanded language support (Arabic, Urdu, Turkish, Malay)

### Future Considerations
- [ ] Halal business directory integration
- [ ] Muslim marriage counselor network
- [ ] Islamic education resources
- [ ] Community forum and discussions
- [ ] Charity and volunteer matching
- [ ] Islamic lifestyle blog integration

---

## Technical Highlights

### Architecture
- **Framework**: Laravel 12 with PHP 8.2+
- **Frontend**: Livewire 3.6 + Livewire Flux 2.1 + Alpine.js
- **Styling**: Tailwind CSS 4.0
- **Real-time**: Laravel Reverb WebSocket server
- **API**: RESTful API with Laravel Sanctum authentication
- **Database**: SQLite (dev) / MySQL (production)

### Performance
- **Caching**: Redis-based caching strategy
- **Queue System**: Background job processing for heavy tasks
- **Image Optimization**: Automatic compression and thumbnails
- **CDN**: Cloudflare R2 for media storage
- **Lazy Loading**: Infinite scroll for discovery
- **Database Optimization**: Indexed queries, eager loading

### Monitoring & Debugging
- **Laravel Telescope**: Development debugging and request inspection
- **Laravel Pulse**: Performance monitoring and metrics
- **Laravel Horizon**: Queue monitoring (Redis-based)
- **Error Tracking**: Centralized error handling service
- **Logging**: Comprehensive application logging

### Security
- **Multi-Factor Authentication**: OTP + 2FA with Google Authenticator
- **CSRF Protection**: All forms protected
- **XSS Prevention**: Blade template escaping
- **SQL Injection Prevention**: Eloquent ORM
- **HTTPS Enforced**: Production SSL/TLS
- **Security Headers**: CSP, HSTS, X-Frame-Options
- **Rate Limiting**: 15+ action types with dynamic limits

### Scalability
- **Service Layer Pattern**: Business logic separation
- **Queue Workers**: Asynchronous processing
- **Database Sharding Ready**: Prepared for horizontal scaling
- **Stateless API**: RESTful API for mobile apps
- **Microservices Ready**: Modular service architecture

---

## Feature Comparison by Tier

| Feature | Free | Premium | Premium Plus |
|---------|------|---------|--------------|
| Daily Likes | 10 | Unlimited | Unlimited |
| Super Likes | 1/month | 5/week | Unlimited |
| Profile Photos | 1 | 6 | 6 |
| See Who Liked You | Blurred | Yes | Yes |
| Advanced Filters | No | Yes | Yes |
| Profile Boost | No | 1/month | Unlimited |
| Rewind Swipes | No | Yes | Yes |
| Incognito Mode | No | No | Yes |
| Read Receipts | No | Yes | Yes |
| Priority Support | No | Yes | Yes |
| Ad-Free | No | Yes | Yes |
| Verification Priority | No | No | Yes |
| Matchmaker Consult | No | No | 1/month |
| Story Highlights | No | No | Yes |
| Video Profile | No | No | Yes |
| Advanced AI Matching | No | No | Yes |

---

## Platform Statistics

- **70+ Database Tables**: Comprehensive data architecture
- **100+ API Endpoints**: Full REST API coverage
- **55+ Eloquent Models**: Rich domain models
- **25+ Services**: Modular business logic
- **50+ Livewire Components**: Interactive UI components
- **3 Languages**: English, Uzbek, Russian (expandable)
- **15+ Rate Limit Types**: Granular API protection
- **5 Verification Types**: Comprehensive trust system
- **10+ Report Categories**: Detailed safety reporting

---

## Compliance & Standards

### GDPR Compliance
- Right to Access (data export functionality)
- Right to Deletion (account deletion with 30-day grace)
- Right to Rectification (profile editing)
- Consent tracking for data usage
- Privacy policy and terms of service
- Cookie consent management

### Security Standards
- OWASP Top 10 protection
- WCAG 2.1 AA accessibility compliance
- PCI DSS ready (payment processing)
- ISO 27001 principles followed
- Regular security audits

### API Standards
- JSON:API specification
- RESTful design principles
- Semantic versioning (/api/v1/)
- Comprehensive API documentation
- Rate limiting and throttling

---

*YorYor - Where Faith Meets Love*

**Last Updated**: October 2025
**Version**: 1.0.0
