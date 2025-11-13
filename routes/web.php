<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now all routes are organized into
| separate files for better maintainability.
|
*/

// TEST ROUTE - React + Inertia.js Welcome Page
Route::get('/react-test', function () {
    return \Inertia\Inertia::render('Welcome');
})->name('react.test');

// Language Switching Route
Route::get('/locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'uz', 'ru'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('locale.switch');

// Broadcasting Authentication Routes
Broadcast::routes(['middleware' => [\App\Http\Middleware\Authenticate::class]]);

// Custom broadcasting auth for Reverb
Route::post('/broadcasting/auth', [App\Http\Controllers\BroadcastAuthController::class, 'authenticate'])
    ->middleware([\App\Http\Middleware\Authenticate::class]);

// Landing/Public Routes - Clean home page
    Route::get('/', function () {
        return view('landing.home');
    })->name('home');

    // Landing Pages
    Route::get('/features', function () {
        return view('features'); // Dedicated features page
    })->name('features');

    Route::get('/about', function () {
        return view('landing.about');
    })->name('about');

    Route::get('/how-it-works', function () {
        return view('landing.how-it-works');
    })->name('how-it-works');

    Route::get('/safety', function () {
        return view('landing.safety');
    })->name('safety');

    Route::get('/success-stories', function () {
        return view('landing.success-stories');
    })->name('success-stories');

    Route::get('/faq', function () {
        return view('landing.faq');
    })->name('faq');

    Route::get('/contact', function () {
        return view('landing.contact');
    })->name('contact');

    Route::get('/help', function () {
        return view('landing.help');
    })->name('help');

    Route::get('/privacy', function () {
        return view('landing.privacy');
    })->name('privacy');

    Route::get('/terms', function () {
        return view('landing.terms');
    })->name('terms');

    Route::get('/coming-soon', App\Livewire\ComingSoon::class)->name('coming-soon');

    // Main entry point - Start authentication/registration flow (using existing register design)
    Route::get('/start', function () {
        return view('auth.register');
    })->name('start');

// Onboard routes moved to routes/user.php

// Web Authentication Routes
Route::controller(App\Http\Controllers\Web\AuthController::class)->group(function () {
    Route::post('/auth/authenticate', 'authenticate')->name('web.auth.authenticate');
    Route::post('/auth/logout', 'logout')->name('web.auth.logout');
    Route::get('/auth/check', 'checkAuth')->name('web.auth.check');
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->name('web.login');
});


// Legacy Settings Routes - Redirect to main settings page
Route::middleware([\App\Http\Middleware\Authenticate::class])->group(function () {
    Route::redirect('settings/profile', 'settings');
    Route::redirect('settings/photos', 'settings');
    Route::redirect('settings/password', 'settings');
    Route::redirect('settings/appearance', 'settings');
});



// Include auth routes
require __DIR__.'/auth.php';
