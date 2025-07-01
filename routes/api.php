<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\MatchController;
use App\Http\Controllers\Api\V1\LikeController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\UserPhotoController;
use App\Http\Controllers\Api\V1\PublicController;

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
    // Auth routes
    Route::prefix('auth')->group(function () {
//        Route::post('/authenticate', [AuthController::class, 'authenticate'])->middleware('rate.limit.otp');
        Route::post('/authenticate', [AuthController::class, 'authenticate']);
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
    });
});
