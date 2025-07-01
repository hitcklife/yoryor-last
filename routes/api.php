<?php

use App\Http\Controllers\Api\V1\AgoraController;
use App\Http\Controllers\Api\V1\BroadcastingController;
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
        // Home page route
        Route::get('/home', [HomeController::class, 'index']);


        // Profile routes
        Route::prefix('profile')->group(function () {
            Route::get('/me', [ProfileController::class, 'myProfile']);
            Route::put('/{profile}', [ProfileController::class, 'update']);
        });

        // Photo routes
        Route::prefix('photos')->group(function () {
            Route::get('/', [UserPhotoController::class, 'index']);
            Route::post('/upload', [UserPhotoController::class, 'upload']);
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

        // Chat routes
        Route::prefix('chats')->group(function () {
            Route::get('/', [ChatController::class, 'getChats']);
            Route::post('/create', [ChatController::class, 'createOrGetChat']);
            Route::get('/unread-count', [ChatController::class, 'getUnreadCount']);
            Route::get('/{id}', [ChatController::class, 'getChat']);
            Route::delete('/{id}', [ChatController::class, 'deleteChat']);
            Route::post('/{id}/messages', [ChatController::class, 'sendMessage']);
            Route::post('/{id}/read', [ChatController::class, 'markMessagesAsRead']);
        });

        // Preference routes
        Route::prefix('preferences')->group(function () {
            Route::get('/', [PreferenceController::class, 'getPreferences']);
            Route::put('/', [PreferenceController::class, 'updatePreferences']);
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

        // Story routes
        Route::prefix('stories')->group(function () {
            Route::get('/', [StoryController::class, 'getUserStories']);
            Route::post('/', [StoryController::class, 'createStory']);
            Route::delete('/{id}', [StoryController::class, 'deleteStory']);
            Route::get('/matches', [StoryController::class, 'getMatchedUserStories']);
        });

        // Device token routes for push notifications
        Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
    });
});
