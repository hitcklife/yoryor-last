<?php

use App\Http\Controllers\Api\V1\AgoraController;
use App\Http\Controllers\Api\V1\BroadcastingController;
use App\Http\Controllers\Api\V1\VideoCallController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\MatchController;
use App\Http\Controllers\Api\V1\LikeController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\UserPhotoController;
use App\Http\Controllers\Api\V1\PublicController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\PreferenceController;
use App\Http\Controllers\Api\V1\StoryController;
use App\Http\Controllers\Api\V1\DeviceTokenController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\BlockedUsersController;
use App\Http\Controllers\Api\V1\SupportController;
use App\Http\Controllers\Api\V1\EmergencyContactsController;
use App\Http\Controllers\Api\V1\CulturalProfileController;
use App\Http\Controllers\Api\V1\FamilyPreferenceController;
use App\Http\Controllers\Api\V1\LocationPreferenceController;
use App\Http\Controllers\Api\V1\CareerProfileController;
use App\Http\Controllers\Api\V1\PhysicalProfileController;
use App\Http\Controllers\Api\V1\ComprehensiveProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// Default route for getting authenticated user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Version 1 Routes
Route::prefix('v1')->group(function () {
    // Public Routes
    Route::get('/countries', [PublicController::class, 'getCountries']);

    Route::post('/broadcasting/auth', [BroadcastingController::class, 'authenticate'])->middleware('auth:sanctum');
    // Auth routes
    Route::prefix('auth')->group(function () {


//        Route::post('/authenticate', [AuthController::class, 'authenticate'])->middleware('rate.limit.otp');
        Route::post('/authenticate', [AuthController::class, 'authenticate']);
        Route::post('/check-email', [AuthController::class, 'checkEmail']);
        // Protected auth routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/complete-registration', [AuthController::class, 'completeRegistration']);
            Route::get('/home-stats', [HomeController::class, 'index']);

            // Two-factor authentication routes
            Route::prefix('2fa')->group(function () {
                Route::post('/enable', [AuthController::class, 'enableTwoFactor']);
                Route::post('/disable', [AuthController::class, 'disableTwoFactor']);
                Route::post('/verify', [AuthController::class, 'verifyTwoFactorCode']);
            });
        });
    });

    // Protected routes that require authentication
    Route::middleware('auth:sanctum')->group(function () {


        // Profile routes
        Route::prefix('profile')->group(function () {
            Route::get('/me', [ProfileController::class, 'myProfile']);
            Route::get('/completion-status', [ProfileController::class, 'getCompletionStatus']);
            Route::put('/{profile}', [ProfileController::class, 'update']);
        });

        // User profile routes (for viewing other users' profiles)
        Route::prefix('users')->group(function () {
            Route::get('/{userId}/profile', [ProfileController::class, 'getUserProfile']);
            Route::post('/{userId}/block', [ProfileController::class, 'blockUser']);
            Route::post('/{userId}/report', [ProfileController::class, 'reportUser']);
        });

        // Report reasons route
        Route::get('/report-reasons', [ProfileController::class, 'getReportReasons']);

        // Photo routes
        Route::prefix('photos')->group(function () {
            Route::get('/', [UserPhotoController::class, 'index']);
            Route::post('/upload', [UserPhotoController::class, 'upload']);
            Route::put('/{id}', [UserPhotoController::class, 'update']);
            Route::delete('/{id}', [UserPhotoController::class, 'destroy']);
        });

        // Match routes
        Route::prefix('matches')->group(function () {
            Route::get('/potential', [MatchController::class, 'getPotentialMatches']);
            Route::get('/', [MatchController::class, 'getMatches']);
            Route::post('/', [MatchController::class, 'createMatch']);
            Route::delete('/{id}', [MatchController::class, 'deleteMatch']);
        });

        // Like/Dislike routes
        Route::post('/likes', [LikeController::class, 'likeUser']);
        Route::post('/dislikes', [LikeController::class, 'dislikeUser']);
        Route::get('/likes/received', [LikeController::class, 'getReceivedLikes']);
        Route::get('/likes/sent', [LikeController::class, 'getSentLikes']);

        // Chat routes with rate limiting
        Route::prefix('chats')->group(function () {
            // General chat operations (moderate rate limiting)
            Route::get('/', [ChatController::class, 'getChats']);
            Route::get('/unread-count', [ChatController::class, 'getUnreadCount']);
            Route::get('/{id}', [ChatController::class, 'getChat']);
            Route::delete('/{id}', [ChatController::class, 'deleteChat']);
            
            // Chat creation (stricter rate limiting)
            Route::post('/create', [ChatController::class, 'createOrGetChat'])
                ->middleware('chat.rate.limit:create_chat');
            
            // Message sending (moderate rate limiting)
            Route::post('/{id}/messages', [ChatController::class, 'sendMessage'])
                ->middleware('chat.rate.limit:send_message');
            
            // Message reading (lenient rate limiting)
            Route::post('/{id}/read', [ChatController::class, 'markMessagesAsRead'])
                ->middleware('chat.rate.limit:mark_read');
            Route::post('/{id}/messages/{message}/read', [ChatController::class, 'markMessagesAsRead'])
                ->middleware('chat.rate.limit:mark_read');

            // Message edit and delete routes (moderate rate limiting)
            Route::put('/{chat_id}/messages/{message_id}', [ChatController::class, 'editMessage'])
                ->middleware('chat.rate.limit:edit_message');
            Route::delete('/{chat_id}/messages/{message_id}', [ChatController::class, 'deleteMessage'])
                ->middleware('chat.rate.limit:delete_message');

            // Call-related chat routes (general rate limiting)
            Route::get('/{id}/call-messages', [ChatController::class, 'getCallMessages']);
            Route::get('/{id}/call-statistics', [ChatController::class, 'getCallStatistics']);
        });

        // Preference routes
        Route::prefix('preferences')->group(function () {
            Route::get('/', [PreferenceController::class, 'getPreferences']);
            Route::put('/', [PreferenceController::class, 'updatePreferences']);
        });

        // Cultural Profile routes
        Route::prefix('cultural-profile')->group(function () {
            Route::get('/', [CulturalProfileController::class, 'getCulturalProfile']);
            Route::put('/', [CulturalProfileController::class, 'updateCulturalProfile']);
        });

        // Family Preference routes
        Route::prefix('family-preferences')->group(function () {
            Route::get('/', [FamilyPreferenceController::class, 'getFamilyPreferences']);
            Route::put('/', [FamilyPreferenceController::class, 'updateFamilyPreferences']);
        });

        // Location Preference routes
        Route::prefix('location-preferences')->group(function () {
            Route::get('/', [LocationPreferenceController::class, 'getLocationPreferences']);
            Route::put('/', [LocationPreferenceController::class, 'updateLocationPreferences']);
        });

        // Career Profile routes
        Route::prefix('career-profile')->group(function () {
            Route::get('/', [CareerProfileController::class, 'getCareerProfile']);
            Route::put('/', [CareerProfileController::class, 'updateCareerProfile']);
        });

        // Physical Profile routes
        Route::prefix('physical-profile')->group(function () {
            Route::get('/', [PhysicalProfileController::class, 'getPhysicalProfile']);
            Route::put('/', [PhysicalProfileController::class, 'updatePhysicalProfile']);
        });

        // Comprehensive Profile routes (all profile data in one endpoint)
        Route::prefix('comprehensive-profile')->group(function () {
            Route::get('/', [ComprehensiveProfileController::class, 'getAllProfileData']);
            Route::put('/', [ComprehensiveProfileController::class, 'updateAllProfileData']);
        });

        // Agora routes for video/voice calling
        Route::prefix('agora')->group(function () {
            Route::post('/token', [AgoraController::class, 'generateToken']);
            Route::post('/initiate', [AgoraController::class, 'initiateCall']);
            Route::post('/{callId}/join', [AgoraController::class, 'joinCall']);
            Route::post('/{callId}/end', [AgoraController::class, 'endCall']);
            Route::post('/{callId}/reject', [AgoraController::class, 'rejectCall']);
            Route::get('/history', [AgoraController::class, 'getCallHistory']);
        });

        // Video SDK routes for video/voice calling
        Route::prefix('video-call')->group(function () {
            Route::post('/token', [VideoCallController::class, 'getToken']);
            Route::post('/create-meeting', [VideoCallController::class, 'createMeeting']);
            Route::get('/validate-meeting/{meetingId}', [VideoCallController::class, 'validateMeeting']);
            Route::post('/initiate', [VideoCallController::class, 'initiateCall']);
            Route::post('/{callId}/join', [VideoCallController::class, 'joinCall']);
            Route::post('/{callId}/end', [VideoCallController::class, 'endCall']);
            Route::post('/{callId}/reject', [VideoCallController::class, 'rejectCall']);
            Route::post('/{callId}/missed', [VideoCallController::class, 'handleMissedCall']);
            Route::get('/history', [VideoCallController::class, 'getCallHistory']);
            Route::get('/analytics', [VideoCallController::class, 'getCallAnalytics']);
        });

        // Story routes
        Route::prefix('stories')->group(function () {
            Route::get('/', [StoryController::class, 'getUserStories']);
            Route::post('/', [StoryController::class, 'createStory']);
            Route::delete('/{id}', [StoryController::class, 'deleteStory']);
            Route::get('/matches', [StoryController::class, 'getMatchedUserStories']);
        });

        // Device token routes for push notifications
        Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
        Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);

        Route::prefix('location')->group(function () {
            Route::post('/update', [\App\Http\Controllers\Api\V1\LocationController::class, 'updateLocation']);
        });

        // Presence routes for online status tracking
        Route::prefix('presence')->group(function () {
            Route::get('/status', [\App\Http\Controllers\Api\V1\PresenceController::class, 'getOnlineStatus']);
            Route::post('/status', [\App\Http\Controllers\Api\V1\PresenceController::class, 'updateOnlineStatus']);
            Route::get('/online-users', [\App\Http\Controllers\Api\V1\PresenceController::class, 'getOnlineUsers']);
            Route::get('/online-matches', [\App\Http\Controllers\Api\V1\PresenceController::class, 'getOnlineMatches']);
            Route::get('/chats/{chatId}/online-users', [\App\Http\Controllers\Api\V1\PresenceController::class, 'getOnlineUsersInChat']);
            Route::post('/typing', [\App\Http\Controllers\Api\V1\PresenceController::class, 'updateTypingStatus']);
            Route::get('/chats/{chatId}/typing-users', [\App\Http\Controllers\Api\V1\PresenceController::class, 'getTypingUsers']);
            Route::get('/statistics', [\App\Http\Controllers\Api\V1\PresenceController::class, 'getPresenceStatistics']);
            Route::get('/history', [\App\Http\Controllers\Api\V1\PresenceController::class, 'getPresenceHistory']);
            Route::post('/heartbeat', [\App\Http\Controllers\Api\V1\PresenceController::class, 'heartbeat']);

            // Admin-only routes
            Route::post('/sync', [\App\Http\Controllers\Api\V1\PresenceController::class, 'syncOnlineStatus']);
            Route::post('/cleanup', [\App\Http\Controllers\Api\V1\PresenceController::class, 'cleanupExpiredPresence']);
        });

        // Settings Management
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingsController::class, 'getAllSettings']);
            Route::put('/', [SettingsController::class, 'updateSettings']);
            Route::get('/notifications', [SettingsController::class, 'getNotificationSettings']);
            Route::put('/notifications', [SettingsController::class, 'updateNotificationSettings']);
            Route::get('/privacy', [SettingsController::class, 'getPrivacySettings']);
            Route::put('/privacy', [SettingsController::class, 'updatePrivacySettings']);
            Route::get('/discovery', [SettingsController::class, 'getDiscoverySettings']);
            Route::put('/discovery', [SettingsController::class, 'updateDiscoverySettings']);
            Route::get('/security', [SettingsController::class, 'getSecuritySettings']);
            Route::put('/security', [SettingsController::class, 'updateSecuritySettings']);
        });

        // Account Management
        Route::prefix('account')->group(function () {
            Route::put('/password', [AccountController::class, 'changePassword']);
            Route::put('/email', [AccountController::class, 'changeEmail']);
            Route::delete('/', [AccountController::class, 'deleteAccount']);
            Route::post('/export-data', [AccountController::class, 'requestDataExport']);
        });

        // Blocked Users
        Route::prefix('blocked-users')->group(function () {
            Route::get('/', [BlockedUsersController::class, 'getBlockedUsers']);
            Route::post('/', [BlockedUsersController::class, 'blockUser']);
            Route::delete('/{userId}', [BlockedUsersController::class, 'unblockUser']);
        });

        // Support & Feedback
        Route::prefix('support')->group(function () {
            Route::post('/feedback', [SupportController::class, 'submitFeedback']);
            Route::post('/report', [SupportController::class, 'reportUser']);
            Route::get('/faq', [SupportController::class, 'getFaq']);
        });

        // Emergency Contacts
        Route::prefix('emergency-contacts')->group(function () {
            Route::get('/', [EmergencyContactsController::class, 'getEmergencyContacts']);
            Route::post('/', [EmergencyContactsController::class, 'addEmergencyContact']);
            Route::put('/{id}', [EmergencyContactsController::class, 'updateEmergencyContact']);
            Route::delete('/{id}', [EmergencyContactsController::class, 'deleteEmergencyContact']);
        });
    });
});
