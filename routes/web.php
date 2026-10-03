<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — CampusMarket (Sprint 1)
|--------------------------------------------------------------------------
|
| Authentication, Student Verification, and Student Profile.
|
*/

// Public Landing Page
Route::get('/', [AuthController::class, 'welcome'])->name('welcome');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Email Verification Routes
    Route::prefix('email')->name('verification.')->group(function () {
        Route::get('/verify', [EmailVerificationController::class, 'notice'])->name('notice');
        Route::get('/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware('signed')
            ->name('verify');
        Route::post('/verification-notification', [EmailVerificationController::class, 'resend'])
            ->middleware('throttle:6,1')
            ->name('send');
    });

    // Protected Student Profile Routes (Must be verified)
    Route::middleware('verified')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
    });
});
