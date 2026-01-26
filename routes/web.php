<?php

use App\Http\Controllers\Admin\StatisticsController as AdminStatisticsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VideoRequestController as AdminVideoRequestController;
use App\Http\Controllers\Auth\PatreonController;
use App\Http\Controllers\Auth\PatreonWebhookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubscribeController;
use App\Http\Controllers\VideoRequestController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Patreon OAuth routes
Route::get('/auth/patreon', [PatreonController::class, 'redirect'])->name('patreon.redirect');
Route::get('/auth/patreon/callback', [PatreonController::class, 'callback'])->name('patreon.callback');

// Patreon webhook (no CSRF)
Route::post('/webhooks/patreon', PatreonWebhookController::class)
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.patreon');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Subscribe page (for non-patrons)
    Route::get('/subscribe', [SubscribeController::class, 'index'])->name('subscribe');
    Route::post('/subscribe/refresh', [SubscribeController::class, 'refresh'])->name('subscribe.refresh');

    // Logout
    Route::post('/logout', [PatreonController::class, 'logout'])->name('logout');

    // Patron-only routes
    Route::middleware('patron')->group(function () {
        Route::get('/requests', [VideoRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/new', [VideoRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests/check', [VideoRequestController::class, 'checkVideo'])->name('requests.check');
        Route::post('/requests', [VideoRequestController::class, 'store'])->name('requests.store');
        Route::get('/my-requests', [VideoRequestController::class, 'myRequests'])->name('my-requests');
    });

    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/requests', [AdminVideoRequestController::class, 'index'])->name('requests.index');
        Route::patch('/requests/{request}/pending', [AdminVideoRequestController::class, 'markPending'])->name('requests.pending');
        Route::patch('/requests/{request}/rate', [AdminVideoRequestController::class, 'rate'])->name('requests.rate');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/credits', [AdminUserController::class, 'adjustCredits'])->name('users.credits');

        Route::get('/statistics', [AdminStatisticsController::class, 'index'])->name('statistics.index');
    });
});

require __DIR__.'/settings.php';
