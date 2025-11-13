<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

// Admin Routes - Protected by auth and admin middleware
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', App\Livewire\Admin\Dashboard::class)->name('dashboard');
    
    // User Management
    Route::get('/users', App\Livewire\Admin\Users::class)->name('users');
    Route::get('/users/{userId}', App\Livewire\Admin\UserProfile::class)->name('user.profile');
    
    // Matches Management
    Route::get('/matches', App\Livewire\Admin\Matches::class)->name('matches');
    
    // Chat Management
    Route::get('/chats', App\Livewire\Admin\Chats::class)->name('chats');
    Route::get('/chats/{chatId}', App\Livewire\Admin\ChatDetails::class)->name('chat.details');
    
    // Reports & Safety
    Route::get('/reports', App\Livewire\Admin\Reports::class)->name('reports');
    
    // Verification
    Route::get('/verification', App\Livewire\Admin\Verification::class)->name('verification');
    
    // Analytics
    Route::get('/analytics', App\Livewire\Admin\Analytics::class)->name('analytics');
    
    // Settings
    Route::get('/settings', App\Livewire\Admin\Settings::class)->name('settings');
});