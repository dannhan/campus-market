<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DisputeController as AdminDisputeController;
use App\Http\Controllers\Admin\EscrowController as AdminEscrowController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\PaymentSettingsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SellerBulkProductController;
use App\Http\Controllers\SellerDashboardController;
use App\Http\Controllers\ShippingRateController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WishlistController;
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
Route::get('/requests', [ItemRequestController::class, 'index'])->name('requests.index');
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

        // Chat System (SRS-016)
        Route::get('/chats', [ConversationController::class, 'index'])->name('chats.index');
        Route::post('/products/{product}/chat', [ConversationController::class, 'start'])->name('products.chat');
        Route::get('/chats/{conversation}', [ConversationController::class, 'show'])->name('chats.show');
        Route::post('/chats/{conversation}/messages', [ConversationController::class, 'storeMessage'])->name('chats.messages.store');

        // Wishlist / Bookmark
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/products/{product}/wishlist', [WishlistController::class, 'toggle'])->name('products.wishlist.toggle');
        Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

        // Dispute Resolution & Transaction Complaint Center
        Route::get('/disputes', [DisputeController::class, 'index'])->name('disputes.index');
        Route::get('/disputes/create', [DisputeController::class, 'create'])->name('disputes.create');
        Route::post('/disputes', [DisputeController::class, 'store'])->name('disputes.store');
        Route::get('/disputes/{dispute}', [DisputeController::class, 'show'])->name('disputes.show');

        // Student-Friendly Payment Methods & Transactions
        Route::get('/shipping/estimate', [ShippingRateController::class, 'estimate'])->name('shipping.estimate');
        Route::get('/profile/payments', [PaymentSettingsController::class, 'edit'])->name('profile.payments.edit');
        Route::match(['put', 'patch'], '/profile/payments', [PaymentSettingsController::class, 'update'])->name('profile.payments.update');

        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/checkout/{product}', [TransactionController::class, 'checkout'])->name('transactions.checkout');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::post('/transactions/{transaction}/payment-proof', [TransactionController::class, 'uploadProof'])->name('transactions.proof');
        Route::post('/transactions/{transaction}/release-escrow', [TransactionController::class, 'releaseEscrow'])->name('transactions.release-escrow');
        Route::post('/transactions/{transaction}/ship', [TransactionController::class, 'ship'])->name('transactions.ship');
        Route::post('/transactions/{transaction}/delivered', [TransactionController::class, 'markDelivered'])->name('transactions.delivered');
        Route::post('/transactions/{transaction}/confirm', [TransactionController::class, 'confirm'])->name('transactions.confirm');
        Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
        Route::post('/transactions/{transaction}/reviews', [ReviewController::class, 'store'])->name('transactions.reviews.store');
        Route::get('/users/{user}/reviews', [ReviewController::class, 'userReviews'])->name('users.reviews');

        // Notification Center & Push Subscriptions
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/push-subscriptions', [NotificationController::class, 'subscribePush'])->name('push.subscribe');
        Route::delete('/push-subscriptions', [NotificationController::class, 'unsubscribePush'])->name('push.unsubscribe');
        Route::post('/notifications/test-push', [NotificationController::class, 'sendTestPush'])->name('notifications.test-push');

        // Seller Center / Dashboard Penjual
        Route::get('/seller', [SellerDashboardController::class, 'index'])->name('seller.dashboard');
        Route::get('/seller/orders', [SellerDashboardController::class, 'orders'])->name('seller.orders');
        Route::get('/seller/products', [SellerDashboardController::class, 'products'])->name('seller.products');
        Route::post('/seller/products/{product}/status', [SellerDashboardController::class, 'toggleProductStatus'])->name('seller.products.status');
        Route::get('/seller/products/bulk', [SellerBulkProductController::class, 'create'])->name('seller.products.bulk');
        Route::get('/seller/products/bulk/template', [SellerBulkProductController::class, 'template'])->name('seller.products.bulk.template');
        Route::post('/seller/products/bulk', [SellerBulkProductController::class, 'store'])->name('seller.products.bulk.store');

        // Wanted Board (Papan Cari Barang) Actions
        Route::get('/requests/create', [ItemRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [ItemRequestController::class, 'store'])->name('requests.store');
        Route::get('/requests/{itemRequest}/edit', [ItemRequestController::class, 'edit'])->name('requests.edit');
        Route::put('/requests/{itemRequest}', [ItemRequestController::class, 'update'])->name('requests.update');
        Route::delete('/requests/{itemRequest}', [ItemRequestController::class, 'destroy'])->name('requests.destroy');
        Route::post('/requests/{itemRequest}/close', [ItemRequestController::class, 'close'])->name('requests.close');
        Route::post('/requests/{itemRequest}/reopen', [ItemRequestController::class, 'reopen'])->name('requests.reopen');
        Route::post('/requests/{itemRequest}/offers', [ItemRequestController::class, 'storeOffer'])->name('requests.offers.store');
        Route::post('/requests/{itemRequest}/offers/{offer}/accept', [ItemRequestController::class, 'acceptOffer'])->name('requests.offers.accept');

        // Admin Management Panel
        Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
            Route::resource('categories', AdminCategoryController::class)->except(['show']);
            Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('/disputes', [AdminDisputeController::class, 'index'])->name('disputes.index');
            Route::get('/disputes/{dispute}', [AdminDisputeController::class, 'show'])->name('disputes.show');
            Route::put('/disputes/{dispute}', [AdminDisputeController::class, 'update'])->name('disputes.update');
            Route::get('/escrows', [AdminEscrowController::class, 'index'])->name('escrows.index');
            Route::post('/escrows/{escrow}/verify', [AdminEscrowController::class, 'verify'])->name('escrows.verify');
            Route::post('/escrows/{escrow}/release', [AdminEscrowController::class, 'release'])->name('escrows.release');
            Route::post('/escrows/{escrow}/refund', [AdminEscrowController::class, 'refund'])->name('escrows.refund');
        });
    });
});

// Public Product Detail (SRS-007) — must come after the /products/create route above
Route::get('/products/{product}', [ProductController::class, 'show'])
    ->whereNumber('product')
    ->name('products.show');

// Public Item Request Detail (Wanted Board)
Route::get('/requests/{itemRequest}', [ItemRequestController::class, 'show'])
    ->whereNumber('itemRequest')
    ->name('requests.show');
