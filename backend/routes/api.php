<?php

use App\Http\Controllers\Api\Admin\PickupRequestController as AdminPickupRequestController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BinController;
use App\Http\Controllers\Api\CollectorPickupController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DetectionEventController;
use App\Http\Controllers\Api\Example\PersonController;
use App\Http\Controllers\Api\OutletController;
use App\Http\Controllers\Api\PublicStatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function (): void {

    /*
    |----------------------------------------------------------------------
    | Auth endpoints — for Flutter and any external client
    |----------------------------------------------------------------------
    | POST /api/v1/auth/login   → get a Bearer token
    | POST /api/v1/auth/logout  → revoke the token (requires auth)
    | GET  /api/v1/auth/user    → get current user info (requires auth)
    */
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('detect', [DetectionEventController::class, 'store'])->name('detect.store');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('auth/user', [AuthController::class, 'user'])->name('auth.user');

        // Shared read APIs (admin + collector)
        Route::middleware('role:admin,collector')->group(function (): void {
            Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::apiResource('outlets', OutletController::class)->only(['index', 'show']);
            Route::apiResource('bins', BinController::class)->only(['index', 'show']);
            Route::apiResource('detection-events', DetectionEventController::class)->only(['index', 'show']);
        });

        // Admin-only mutation APIs
        Route::middleware('role:admin')->group(function (): void {
            Route::apiResource('outlets', OutletController::class)->only(['store', 'update', 'destroy']);
            Route::apiResource('bins', BinController::class)->only(['store', 'update', 'destroy']);
            Route::post('bins/{bin}/assign', [BinController::class, 'assign'])->name('bins.assign');
            Route::post('bins/{bin}/unassign', [BinController::class, 'unassign'])->name('bins.unassign');
            Route::apiResource('persons', PersonController::class);

            // Admin pickup management
            Route::get('admin/pickup-requests', [AdminPickupRequestController::class, 'index'])->name('admin.pickup-requests.index');
            Route::get('admin/pickup-requests/{pickupRequest}', [AdminPickupRequestController::class, 'show'])->name('admin.pickup-requests.show');
            Route::post('admin/pickup-requests/{pickupRequest}/cancel', [AdminPickupRequestController::class, 'cancel'])->name('admin.pickup-requests.cancel');
            Route::post('admin/pickup-requests/{pickupRequest}/unclaim', [AdminPickupRequestController::class, 'unclaim'])->name('admin.pickup-requests.unclaim');

            // Admin user management
            Route::get('admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
            Route::get('admin/users/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
        });

        // Collector pickup APIs
        Route::prefix('collector')->name('collector.')->middleware('role:collector')->group(function (): void {
            Route::get('pickups', [CollectorPickupController::class, 'index'])->name('pickups.index');
            Route::get('stats', [CollectorPickupController::class, 'stats'])->name('stats');
            Route::post('pickups/{pickupRequest}/claim', [CollectorPickupController::class, 'claim'])->name('pickups.claim');
            Route::post('pickups/{pickupRequest}/complete', [CollectorPickupController::class, 'complete'])->name('pickups.complete');
        });
    });

    // Public stats — no auth required
    Route::get('public/stats', [PublicStatsController::class, 'index'])->name('public.stats');
});
