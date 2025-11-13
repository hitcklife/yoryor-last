# YorYor Features Documentation

## Table of Contents
- [Overview](#overview)
- [User Authentication & Registration](#user-authentication--registration)
- [Profile System](#profile-system)
- [Discovery & Matching](#discovery--matching)
- [Real-Time Chat](#real-time-chat)
- [Video & Audio Calling](#video--audio-calling)
- [Stories](#stories)
- [Matchmaker System](#matchmaker-system)
- [Verification System](#verification-system)
- [Safety & Panic Button](#safety--panic-button)
- [Subscription Plans](#subscription-plans)
- [Admin Dashboard](#admin-dashboard)
- [Multilingual Support](#multilingual-support)
- [Additional Features](#additional-features)

---

## Overview

YorYor is a comprehensive Muslim-focused dating and matchmaking platform built with Laravel and Livewire. It combines modern dating app features with traditional matchmaking values, offering a safe and culturally-appropriate environment for finding meaningful connections.

---

## User Authentication & Registration

### Multi-Method Authentication
- **Email/Password Authentication**: Traditional login with secure password hashing (bcrypt)
- **OTP (One-Time Password)**: Passwordless login via email verification codes
- **Social Login**: Google OAuth integration (ready for expansion)
- **Two-Factor Authentication (2FA)**: Optional TOTP-based 2FA using Google Authenticator

### Registration Flow
1. **Email Verification**: Check if email exists, determine authentication method
2. **Basic Information**: Name, date of birth, gender, country
3. **Profile Creation**: Guided multi-step profile setup
4. **Photo Upload**: Minimum 2 photos required for activation
5. **Preferences Setup**: Set match preferences

### Security Features
- Rate limiting on authentication attempts (5 per minute)
- CSRF protection on all forms
- Email verification for new accounts
- Secure session management with Laravel Sanctum
- Password requirements enforcement
- Account lockout after failed attempts

### Registration Completion Tracking
- Profile completion percentage
- Visual progress indicators
- Section-by-section completion status
- Reminders for incomplete profiles

---

## Profile System

### Basic Profile
Core information about the user:
- **Personal Information**: Name, age, gender, bio
- **Location**: City, country, distance preferences
- **Demographics**: Nationality, ethnicity, languages
- **Physical Attributes**: Height, body type, eye color, hair color
- **Photos**: Up to 6 photos with drag-to-reorder functionality
- **Profile Visibility**: Public, friends, private options

### Extended Profile Sections

#### Cultural Profile
- Religion and sect (Sunni, Shia, etc.)
- Religiosity level (Very religious to Moderate)
- Prayer frequency (5 times daily, occasionally, etc.)
- Dietary preferences (Halal only, Halal mostly, Flexible)
- Languages spoken
- Cultural values and practices
- Dress code preferences (Hijab, Niqab, Modest, etc.)
- Alcohol and smoking stance

#### Career & Education
- Education level (High school through Doctorate)
- Field of study
- Current occupation
- Industry sector
- Income level (ranges for privacy)
- Career ambitions
- Work-life balance preferences

#### Family Preferences
- Marital status (Never married, Divorced, Widowed)
- Children status and preferences
- Family involvement preferences
- Living situation (Own place, With family, Roommates)
- Family values and traditions
- Parents' involvement in marriage decision

#### Location Preferences
- Current location with privacy controls
- Willingness to relocate
- Preferred countries/cities
- Distance preferences for matches
- Geographic flexibility

#### Physical Profile
- Height (cm/inches)
- Body type
- Ethnicity
- Hair color and style
- Eye color
- Physical fitness level
- Disability accommodations

#### Lifestyle & Habits
- Smoking status
- Alcohol consumption
- Dietary restrictions
- Exercise frequency
- Hobbies and interests
- Travel preferences
- Pet ownership

### Profile Features

#### Photo Management
- Upload up to 6 photos
- Set primary photo
- Drag-and-drop reordering
- Photo verification system
- Automatic image optimization and thumbnails
- Privacy controls per photo
- Delete and replace photos

#### Profile Verification Badges
- Identity verified
- Photo verified
- Income verified
- Education verified
- Profession verified
- Background check completed

#### Profile Privacy Controls
- Show/hide online status
- Show/hide last active
- Show/hide distance
- Show/hide specific profile sections
- Block specific users from viewing profile
- Incognito mode (premium feature)

#### Profile Completion
- Real-time completion percentage
- Section-by-section tracking
- Rewards for completion (boost visibility)
- Guided completion flow

---

## Discovery & Matching

### Discovery Methods

#### Swipe/Card Interface
- Tinder-style card stack
- Swipe right to like, left to pass
- Instant match notifications
- Compatibility score display
- Distance display
- Age and basic info preview
- Quick profile preview

#### Grid Discovery
- Browse profiles in a grid layout
- Filter and sort options
- Pagination with infinite scroll
- Quick actions (like, pass, bookmark)
- Profile thumbnails with key info

#### Search & Filters
Advanced filtering options:
- Age range
- Distance radius
- Height range
- Education level
- Occupation
- Religion and sect
- Religiosity level
- Marital status
- Children preferences
- Languages
- Ethnicity
- Body type
- Lifestyle habits

### Matching Algorithm

#### Compatibility Scoring
Calculated based on:
- Shared religious values (30% weight)
- Lifestyle compatibility (20% weight)
- Location preferences (15% weight)
- Age and life stage (15% weight)
- Education and career (10% weight)
- Physical preferences (10% weight)

#### Smart Recommendations
- Daily recommendations (curated matches)
- AI-powered suggestions
- Learn from user behavior (likes, passes)
- Mutual friend connections
- Shared interests highlighting

### Like System
- **Like Users**: Express interest in profiles
- **Super Like**: Stand out with premium feature
- **Received Likes**: See who liked you
- **Mutual Likes**: Automatic match creation
- **Like Limits**: Daily limits based on subscription tier

### Match Management
- View all matches
- Sort by recent activity
- Filter by conversation status
- Unmatch functionality
- Match expiration (optional)
- Rewind last swipe (premium)

---

## Real-Time Chat

### Messaging Features

#### Text Messaging
- Real-time message delivery via WebSockets
- Message read receipts (seen status)
- Typing indicators
- Message timestamps
- Message editing (within 15 minutes)
- Message deletion (for both sides)
- Message reactions (coming soon)

#### Media Sharing
- Photo sharing with inline preview
- Video sharing (up to 60 seconds)
- Voice messages
- Location sharing
- File attachments (PDF documents)
- GIF support (integration ready)

#### Chat Features
- Unread message counters
- Last message preview
- Online status indicators
- User presence awareness
- Chat archiving
- Chat deletion
- Search within conversations
- Pinned conversations

### Chat Management

#### Conversation List
- Sorted by recent activity
- Unread indicator badges
- Quick actions (archive, delete, mute)
- Search conversations
- Filter by unread/archived
- Pull-to-refresh

#### Message Features
- Copy message text
- Forward messages (coming soon)
- Quote/reply to specific messages
- Report inappropriate messages
- Block user from chat
- Mute notifications per chat

### Chat Safety

#### Content Moderation
- Automated inappropriate content detection
- Report message functionality
- Block users immediately
- Screenshot detection (notification)
- Link scanning for safety

#### Privacy Controls
- Match-only messaging (no unsolicited messages)
- Message request system for non-matches
- Profanity filters (optional)
- Media auto-download controls
- Read receipt controls

### Call Integration in Chat
- Call history in chat timeline
- Call duration display
- Missed call notifications
- Quick call buttons
- Call statistics

---

## Video & Audio Calling

### Supported Providers
- **VideoSDK**: Primary provider with advanced features
- **Agora**: Alternative provider for reliability
- Automatic fallback between providers

### Call Features

#### Video Calling
- HD video quality (up to 1080p)
- Adaptive bitrate based on connection
- Front/back camera switching
- Video mute/unmute
- Picture-in-picture mode
- Screen orientation support

#### Audio Calling
- High-quality audio codec
- Noise cancellation
- Echo cancellation
- Audio mute/unmute
- Speaker/headphone switching
- Background calling support

#### Call Controls
- Accept/Reject incoming calls
- End call
- Mute/unmute audio
- Toggle video
- Switch camera
- Speaker toggle
- Add time extension request

### Call Management

#### Call States
- Ringing: Incoming call notification
- Connecting: Establishing connection
- Active: Call in progress
- Ended: Call completed
- Missed: Unanswered calls
- Declined: Rejected calls
- Failed: Connection issues

#### Call History
- Complete call log
- Call duration tracking
- Call type (video/audio)
- Timestamp and date
- Missed call highlighting
- Call statistics per match

#### Call Notifications
- Push notifications for incoming calls
- In-app call alerts
- Missed call notifications
- Call recording consent (if enabled)

### Call Safety Features
- Report inappropriate behavior during calls
- Emergency exit button
- Call time limits (configurable)
- Call recording disclosure
- Block user during call
- Panic button integration

### Call Analytics
- Total call duration per match
- Average call length
- Call frequency
- Peak calling times
- Connection quality metrics

---

## Stories

### Story Features
Similar to Instagram/Snapchat Stories with matching-focused approach:

#### Story Creation
- Photo stories with filters
- Video stories (up to 30 seconds)
- Text overlays and stickers
- Location tags
- Music integration (coming soon)
- Story privacy controls

#### Story Viewing
- Stories from matched users only
- Auto-advance between stories
- Pause/resume functionality
- Story reactions (quick emoji response)
- Direct reply to stories (opens chat)
- Story view count
- Who viewed your story

#### Story Management
- 24-hour expiration
- Delete story manually
- Archive stories (optional)
- Story highlights (premium)
- Hide story from specific users
- Story drafts

### Story Privacy
- Visible to matches only by default
- Hide from specific users
- Share to specific lists
- Public story mode (premium)

### Story Analytics
- View count
- Viewer list with timestamps
- Interaction metrics
- Reach statistics

---

## Matchmaker System

### Professional Matchmakers
YorYor includes a unique professional matchmaker system for users who prefer traditional matchmaking.

### Matchmaker Profiles
- Professional bio and credentials
- Specialty areas (Muslim matchmaking, cultural-specific)
- Years of experience
- Success rate and testimonials
- Rating and reviews
- Service packages and pricing
- Availability calendar

### Matchmaker Services

#### For Users
- **Browse Matchmakers**: Search by specialty, rating, price
- **Hire Matchmaker**: Request matchmaking services
- **Consultations**: Video/audio calls with matchmakers
- **Introduction Requests**: Matchmakers suggest potential matches
- **Accept/Decline Introductions**: Review matchmaker suggestions
- **Provide Feedback**: Rate and review matchmakers
- **Progress Tracking**: Monitor matchmaking progress

#### For Matchmakers
- **Client Management**: View and manage clients
- **Match Suggestions**: Suggest matches from database
- **Introduction Creation**: Introduce compatible clients
- **Consultation Scheduling**: Book calls with clients
- **Client Notes**: Private notes on preferences
- **Success Tracking**: Track successful matches
- **Earnings Dashboard**: Revenue and statistics

### Matchmaker Features
- Background verification required
- Professional certification display
- Service packages (hourly, monthly, per-match)
- Client testimonials
- Success stories
- Consultation history
- Payment integration

### Family Approval System
- Invite family members to review matches
- Family member accounts (limited access)
- Approval workflow for matches
- Family feedback collection
- Privacy controls on family involvement

---

## Verification System

### Verification Types

#### Identity Verification
- Government-issued ID upload
- Selfie with ID
- Liveness detection
- Address verification
- Background check (optional)

#### Photo Verification
- Live selfie matching profile photos
- Pose replication
- Facial recognition verification
- Ensures photos are current and authentic

#### Income Verification
- Pay stubs or tax documents
- Employment verification letter
- Income range verification
- Privacy-protected (only shows verified badge)

#### Education Verification
- Diploma or degree certificate
- University email verification
- LinkedIn integration
- Field of study confirmation

#### Profession Verification
- Employment letter
- Business card or ID
- Professional license
- LinkedIn profile verification

### Verification Process
1. **Select Verification Type**: Choose what to verify
2. **Review Requirements**: See what documents needed
3. **Upload Documents**: Secure encrypted upload
4. **Admin Review**: Manual verification by team
5. **Approval/Rejection**: Notification of result
6. **Badge Granted**: Verification badge on profile

### Verification Benefits
- Increased profile visibility
- Trust badge on profile
- Higher match quality
- Access to verified-only search
- Priority in recommendations
- Premium feature access

### Verification Privacy
- Documents encrypted at rest
- Viewed by verification team only
- Automatic deletion after verification
- Compliance with data protection laws
- User control over badge visibility

---

## Safety & Panic Button

### Safety Features

#### Panic Button
Emergency assistance system integrated throughout the app:

**Features:**
- One-tap activation from any screen
- GPS location sharing to emergency contacts
- Automatic emergency SMS sending
- Police notification (optional)
- Silent activation mode
- False alarm cancellation
- Panic history tracking

**Activation Methods:**
- In-app button (always visible)
- Volume button pattern (hold volume down 5 seconds)
- Voice activation ("Hey YorYor, emergency")
- Widget on home screen

#### Emergency Contacts
- Add up to 5 emergency contacts
- Contact verification via SMS/email
- Contact priority ordering
- Custom messages per contact
- Automatic location updates during panic
- Test emergency system

#### Safety Features

**Profile Safety:**
- Block users instantly
- Report profiles with evidence upload
- Hide profile from specific users
- Screenshot detection and notification
- Location approximation (not exact address)

**Meeting Safety:**
- Date planning assistance
- Share date details with emergency contacts
- Check-in reminders during dates
- Public location suggestions
- Safety tips and guidelines
- Quick exit strategies

**Content Safety:**
- Report inappropriate content
- AI-powered content moderation
- Automated pattern detection
- Manual review by safety team
- User safety score system

### Reporting System

#### Report Categories
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

#### Report Process
1. Select report reason
2. Provide detailed description
3. Upload evidence (screenshots, messages)
4. Submit report
5. Automatic user temporary restriction
6. Admin review within 24 hours
7. Action taken (warning, suspension, ban)
8. Reporter notification

#### Automated Safety Features
- Suspicious activity detection
- Pattern recognition for scammers
- Automated temporary restrictions
- Safety score calculation
- Risk assessment for users

### User Blocking
- Block users permanently
- Blocked users invisible to you
- Your profile hidden from blocked users
- Unmatch automatically
- Delete all chat history
- Prevent future matching

### Safety Center
- Safety guidelines and tips
- Dating safety advice
- Red flags to watch for
- Community guidelines
- Reporting instructions
- Support resources
- Emergency resources

---

## Subscription Plans

### Subscription Tiers

#### Free Tier
- Basic profile creation
- Limited daily likes (10 per day)
- Match with users
- Send messages to matches
- Basic search filters
- 1 photo upload
- View who liked you (blurred)

#### Premium Tier
- Unlimited likes
- Advanced search filters
- See who liked you (unblurred)
- 6 photo uploads
- Profile boost (monthly)
- Rewind last swipe
- Read receipts
- Priority customer support
- Ad-free experience

#### Premium Plus Tier
All Premium features plus:
- Unlimited profile boosts
- Incognito mode
- Advanced matching algorithm
- Priority in search results
- Video profile feature
- Story highlights
- Enhanced privacy controls
- Dedicated matchmaker consultation (1/month)
- Verification priority

### Subscription Features

#### Payment Integration
- Stripe integration (ready)
- In-app purchases (iOS/Android)
- Recurring billing management
- Payment history
- Invoice generation
- Refund system
- Currency support (multi-currency)

#### Usage Tracking
- Track feature usage per tier
- Monthly usage reports
- Feature limit enforcement
- Upgrade prompts
- Usage notifications

#### Trial Periods
- 7-day free trial for Premium
- No credit card required for trial
- Trial conversion tracking
- Automatic cancellation if not converted

---

## Admin Dashboard

### Dashboard Overview
Comprehensive admin panel built with Livewire for managing the platform.

### Admin Features

#### User Management
- View all users with advanced filters
- User profile inspection
- Account status management (active, suspended, banned)
- User verification review
- Manual profile editing
- Password reset
- Email verification status
- Account deletion
- User analytics

#### Match Management
- View all matches
- Match statistics
- Unmatching functionality
- Match quality analysis
- Fake match detection

#### Chat Monitoring
- View chat conversations (with user consent)
- Message content moderation
- Inappropriate content flagging
- Chat statistics
- Report resolution

#### Report Management
- Review user reports
- Report queue with priority
- Evidence review
- Take action (warn, suspend, ban)
- Communication with reporters
- Report statistics
- Pattern detection

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
- Block/report patterns

#### Analytics Dashboard
- User growth metrics
- Active user statistics
- Match rate analytics
- Revenue tracking
- Subscription conversions
- Geographic distribution
- Feature usage analytics
- Retention metrics
- Churn analysis

#### Content Management
- Manage FAQ content
- Safety tips editing
- Announcement system
- Push notification broadcasts
- Email campaign management

#### Settings & Configuration
- Platform settings
- Feature flags
- Rate limit configuration
- Verification requirements
- Moderation rules
- Email templates
- Payment gateway settings

### Admin Roles & Permissions
- Super Admin: Full access
- Admin: Most features
- Moderator: Content and reports only
- Support: User assistance only
- Analyst: Read-only analytics

---

## Multilingual Support

### Language Features
- **Supported Languages**: English, Arabic (expandable)
- **RTL Support**: Full right-to-left layout for Arabic
- **Dynamic Language Switching**: Change language without reload
- **User Preference Storage**: Remember language choice
- **Localized Content**: All UI text, emails, notifications
- **Date/Time Formatting**: Culture-specific formatting
- **Number Formatting**: Locale-aware number display

### Translation System
- Laravel localization files
- JSON-based translations
- Missing translation detection
- Translation management interface (admin)
- Community translation contributions (planned)

### Localized Features
- UI text and labels
- Error messages
- Email notifications
- Push notifications
- Date and time formats
- Currency symbols
- Measurement units (metric/imperial)

---

## Additional Features

### Notifications

#### Push Notifications
- New match notifications
- New message alerts
- New like notifications
- Call notifications
- Story view notifications
- Verification status updates
- Safety alerts
- Custom notification preferences per type

#### In-App Notifications
- Notification center
- Unread badge counters
- Notification history
- Mark as read/unread
- Delete notifications
- Notification settings

#### Email Notifications
- Welcome emails
- Match notifications
- Weekly digest
- Safety tips
- Verification updates
- Subscription updates
- Marketing emails (opt-in)

### Search & Discovery

#### Advanced Search
- Keyword search in profiles
- Multi-criteria filtering
- Save search filters
- Search history
- Recommended searches

#### Profile Bookmarks
- Save interesting profiles
- Organize with tags
- Review later
- Remove from bookmarks

### Activity Tracking
- Last active timestamp
- Online status indicator
- Activity heat map
- Usage statistics
- Login history

### Privacy Controls
- Incognito browsing mode
- Selective profile hiding
- Last seen privacy
- Distance privacy
- Photo privacy controls
- Profile visibility settings

### Gamification
- Profile completion rewards
- Achievement badges
- Daily login rewards
- Referral system
- Engagement rewards

### Social Features
- Share success stories (anonymous option)
- Testimonials
- Community forum (planned)
- Blog integration (planned)

### Support System
- In-app chat support
- Email support
- FAQ section
- Video tutorials
- Feedback system
- Feature request voting

### Data & Privacy
- GDPR compliance
- Data export functionality
- Account deletion (right to be forgotten)
- Privacy policy
- Terms of service
- Cookie consent
- Data retention policies

### Performance Features
- Image optimization and CDN
- Lazy loading
- Infinite scroll
- Caching strategies
- Database query optimization
- Real-time updates via WebSockets

### Accessibility
- Screen reader support
- Keyboard navigation
- High contrast mode
- Font size adjustment
- Color blind friendly design
- WCAG 2.1 AA compliance

---

## Coming Soon

### Planned Features
- Video profiles (60-second intro videos)
- Group chats (family introductions)
- Event planning (virtual and in-person)
- Compatibility quizzes
- Icebreaker questions
- Virtual gifts
- Voice messages in profiles
- Advanced AI matching
- Compatibility reports
- Relationship coaching
- Pre-marital counseling directory
- Wedding planning integration
- Community events
- Success story showcase
- Referral program enhancements

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

---

## Technical Highlights

- **Real-time Communication**: Laravel Reverb WebSocket server
- **Mobile-First Design**: Responsive Tailwind CSS
- **Progressive Web App**: Installable on mobile devices
- **Offline Support**: Service worker for basic offline functionality
- **Image Processing**: Automatic optimization and thumbnails
- **Security**: Multiple layers of protection and validation
- **Scalability**: Queue system for background jobs
- **Monitoring**: Laravel Telescope, Pulse, and Horizon
- **API-First**: RESTful API for future mobile apps
- **Modern Stack**: Laravel 12, Livewire 3, Alpine.js

---

*YorYor - Where Faith Meets Love*

Last Updated: September 2025