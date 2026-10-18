<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — CampusMarket (Sprint 2)
|--------------------------------------------------------------------------
|
| Authentication, Student Verification, Student Profile, Product
| Marketplace (browse/search/listing), and Price Bargaining.
|
*/

// Public Landing Page
Route::get('/', [AuthController::class, 'welcome'])->name('welcome');

// Public Marketplace Browsing (SRS-005)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
// NOTE: the public product detail route is registered at the bottom of this file,
// after /products/create, so the static segment cannot be captured by {product}.

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

        // Product Listing Management (SRS-008)
        Route::get('/products/create', [ProductController::class, 'create'])
            ->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
            ->name('products.edit');
        Route::match(['put', 'patch'], '/products/{product}', [ProductController::class, 'update'])
            ->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])
            ->name('products.destroy');

        // Price Bargaining (SRS-010)
        Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
        Route::post('/products/{product}/offers', [OfferController::class, 'store'])
            ->name('offers.store');
        Route::post('/offers/{offer}/respond', [OfferController::class, 'respond'])
            ->name('offers.respond');
        Route::post('/offers/{offer}/cancel', [OfferController::class, 'cancel'])
            ->name('offers.cancel');
    });
});

// Public Product Detail (SRS-007) — must come after the /products/create route above
Route::get('/products/{product}', [ProductController::class, 'show'])
    ->whereNumber('product')
    ->name('products.show');

