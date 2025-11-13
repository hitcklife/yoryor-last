<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
|
| Here is where you can register user routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

// Social Authentication Routes
Route::get('/auth/{provider}', [App\Http\Controllers\Auth\SocialiteController::class, 'redirect'])->name('auth.socialite');
Route::get('/auth/{provider}/callback', [App\Http\Controllers\Auth\SocialiteController::class, 'callback'])->name('auth.socialite.callback');

// Dashboard and Main User Routes
Route::middleware([\App\Http\Middleware\Authenticate::class, 'update.last.active'])->group(function () {
    // Modern Dashboard - Instagram-style interface
    Route::get('/dashboard', App\Livewire\Dashboard\MainDashboard::class)->name('dashboard');
    
    // Matches Page
    Route::get('/matches', App\Livewire\Pages\MatchesPage::class)->name('matches');
    
    // Messages Page
    Route::get('/messages', App\Livewire\Pages\MessagesPage::class)->name('messages');
    Route::get('/messages/{conversationId}', App\Livewire\Pages\ChatPage::class)->name('messages.chat');
    
    // My Profile Page
    Route::get('/my-profile', App\Livewire\Pages\MyProfilePage::class)->name('my-profile');
    
    // User Profile Show (for viewing other users' profiles) - using secure UUID
    Route::get('/user/{uuid}', App\Livewire\Pages\UserProfilePage::class)->name('user.profile.show');
    
    // Settings Page
    Route::get('/settings', App\Livewire\Pages\SettingsPage::class)->name('settings');
    
    // Notifications Page
    Route::get('/notifications', App\Livewire\Pages\NotificationsPage::class)->name('notifications');
    
    // Blocked Users Management
    Route::get('/blocked-users', App\Livewire\Pages\BlockedUsersPage::class)->name('blocked-users');
    
    // Subscription Management
    Route::get('/subscription', App\Livewire\Pages\SubscriptionPage::class)->name('subscription');
    
    // Verification Page
    Route::get('/verification', App\Livewire\Pages\VerificationPage::class)->name('verification');
    
    // Search Page
    Route::get('/search', App\Livewire\Pages\SearchPage::class)->name('search');
    
    // Video Call Page
    Route::get('/video-call/{conversationId?}', App\Livewire\Pages\VideoCallPage::class)->name('video-call');
    
    // Emergency/Panic Button
    Route::get('/emergency', App\Livewire\Components\PanicButton::class)->name('emergency');
    
    // Insights & Analytics
    Route::get('/insights', App\Livewire\Pages\InsightsPage::class)->name('insights');

    // Discover Page
    Route::get('/discover', App\Livewire\Pages\DiscoverPage::class)->name('discover');
    
    // Likes Page
    Route::get('/likes', App\Livewire\Pages\LikesPage::class)->name('likes');
    
    // Profile redirect (for backward compatibility)
    Route::get('/profile', function () {
        return redirect()->route('my-profile');
    })->name('profile');
});

// Onboarding Routes - For authenticated users completing their profile
Route::prefix('onboard')->middleware([\App\Http\Middleware\Authenticate::class])->group(function () {
    Route::get('/basic-info', App\Livewire\Profile\BasicInfo::class)->name('onboard.basic-info');          // Step 1
    Route::get('/contact-info', App\Livewire\Profile\ContactInfo::class)->name('onboard.contact-info');    // Step 2
    Route::get('/about-you', App\Livewire\Profile\AboutYou::class)->name('onboard.about-you');             // Step 3
    Route::get('/preferences', App\Livewire\Profile\Preferences::class)->name('onboard.preferences');      // Step 4
    Route::get('/interests', App\Livewire\Profile\Interests::class)->name('onboard.interests');            // Step 5
    Route::get('/photos', App\Livewire\Profile\Photos::class)->name('onboard.photos');                     // Step 6
    Route::get('/location', App\Livewire\Profile\Location::class)->name('onboard.location');               // Step 7
    Route::get('/preview', App\Livewire\Profile\Preview::class)->name('onboard.preview');                  // Step 8 - Preview
    Route::get('/complete', App\Livewire\Profile\Details::class)->name('onboard.complete');                // Step 9 - Final
});

// Profile Enhancement Routes - For improving profile after registration
Route::prefix('profile/enhance')->middleware([\App\Http\Middleware\Authenticate::class])->group(function () {
    Route::get('/', App\Livewire\Profile\EnhanceProfile::class)->name('profile.enhance');
    Route::get('/cultural', App\Livewire\Profile\CulturalBackground::class)->name('profile.enhance.cultural');
    Route::get('/family', App\Livewire\Profile\FamilyMarriage::class)->name('profile.enhance.family');
    Route::get('/career', App\Livewire\Profile\CareerEducation::class)->name('profile.enhance.career');
    Route::get('/lifestyle', App\Livewire\Profile\LifestyleHabits::class)->name('profile.enhance.lifestyle');
    Route::get('/location', App\Livewire\Profile\LocationPreferences::class)->name('profile.enhance.location');
});

// User Dashboard Routes moved to routes/web.php for better organization
// These routes are now handled by UserController in web.php

// User API Routes for AJAX calls (placeholder implementations)
Route::middleware([\App\Http\Middleware\Authenticate::class])->prefix('api/user')->name('api.user.')->group(function () {
    // Like/Unlike actions
    Route::post('/like/{userId}', function ($userId) {
        // TODO: Implement like functionality
        return response()->json(['success' => true, 'message' => 'User liked successfully']);
    })->name('like');
    
    Route::post('/unlike/{userId}', function ($userId) {
        // TODO: Implement unlike functionality
        return response()->json(['success' => true, 'message' => 'User unliked successfully']);
    })->name('unlike');
    
    Route::post('/pass/{userId}', function ($userId) {
        // TODO: Implement pass functionality
        return response()->json(['success' => true, 'message' => 'User passed successfully']);
    })->name('pass');
    
    // Block/Report actions
    Route::post('/block/{userId}', function ($userId) {
        // TODO: Implement block functionality
        return response()->json(['success' => true, 'message' => 'User blocked successfully']);
    })->name('block');
    
    Route::delete('/unblock/{userId}', function ($userId) {
        // TODO: Implement unblock functionality
        return response()->json(['success' => true, 'message' => 'User unblocked successfully']);
    })->name('unblock');
    
    Route::post('/report/{userId}', function ($userId) {
        // TODO: Implement report functionality
        return response()->json(['success' => true, 'message' => 'User reported successfully']);
    })->name('report');
    
    // Notification actions
    Route::post('/notifications/{notification}/mark-read', function ($notification) {
        // TODO: Implement mark notification as read
        return response()->json(['success' => true, 'message' => 'Notification marked as read']);
    })->name('notifications.mark-read');
    
    Route::post('/notifications/mark-all-read', function () {
        // TODO: Implement mark all notifications as read
        return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
    })->name('notifications.mark-all-read');
    
    Route::delete('/notifications/{notification}', function ($notification) {
        // TODO: Implement delete notification
        return response()->json(['success' => true, 'message' => 'Notification deleted']);
    })->name('notifications.destroy');
    
    // Emergency actions
    Route::post('/emergency/panic', function () {
        // TODO: Implement panic button functionality
        return response()->json(['success' => true, 'message' => 'Emergency alert sent']);
    })->name('emergency.panic');
    
    Route::post('/emergency/safety-check', function () {
        // TODO: Implement safety check functionality
        return response()->json(['success' => true, 'message' => 'Safety check completed']);
    })->name('emergency.safety-check');
});