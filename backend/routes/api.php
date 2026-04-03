<?php

use App\Http\Controllers\Api\Admin\PickupRequestController as AdminPickupRequestController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BinController;
use App\Http\Controllers\Api\CollectorPickupController;
use App\Http\Controllers\Api\CollectorRouteController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DetectionEventController;
use App\Http\Controllers\Api\DetectionPipelineController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\OutletController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PublicStatsController;
use App\Http\Controllers\Api\RegistrationRequestController;
use App\Http\Controllers\Api\ReportController as ApiReportController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\StoreOwner\DashboardController as StoreOwnerDashboardController;
use App\Http\Controllers\Api\StoreOwner\OutletController as StoreOwnerOutletController;
use App\Http\Controllers\Api\StoreOwner\RewardController as StoreOwnerRewardController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\VoucherController;
use Illuminate\Support\Facades\Route;

/*
|----------------------------------------------------------------------
| Bin Detection Pipeline (no auth — bin-initiated)
|----------------------------------------------------------------------
*/
Route::get('v1/bins/active', [DetectionPipelineController::class, 'listBins']);

Route::prefix('v1/bin/{serial}')->group(function (): void {
    Route::post('sessions', [DetectionPipelineController::class, 'startSession']);
    Route::post('sessions/{session}/link-user', [DetectionPipelineController::class, 'linkUser']);
    Route::post('sessions/{session}/detect', [DetectionPipelineController::class, 'detect']);
    Route::post('sessions/{session}/end', [DetectionPipelineController::class, 'endSession']);
    Route::post('sessions/{session}/rinse', [DetectionPipelineController::class, 'markRinsed']);
});

Route::prefix('v1')->name('api.')->group(function (): void {

    /*
    |----------------------------------------------------------------------
    | Public endpoints — no auth required
    |----------------------------------------------------------------------
    */
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('detect', [DetectionEventController::class, 'store'])->name('detect.store');
    Route::patch('detect/{detectionEvent}/classify', [DetectionEventController::class, 'classify'])->name('detect.classify');
    Route::post('detection-events/{detectionEvent}/feedback', [DetectionEventController::class, 'feedback'])->name('detection-events.feedback');
    Route::get('public/stats', [PublicStatsController::class, 'index'])->name('public.stats');
    Route::get('bins/resolve/{serial}', [BinController::class, 'resolve'])->name('bins.resolve');
    Route::get('bins/{bin}/qr', [BinController::class, 'qrCode'])->name('bins.qr');
    Route::post('bins/{bin}/heartbeat', [BinController::class, 'heartbeat'])->name('bins.heartbeat');

    // Public registration request submission (no auth)
    Route::post('registration-requests', [RegistrationRequestController::class, 'store'])->name('registration-requests.store');

    /*
    |----------------------------------------------------------------------
    | Authenticated endpoints — requires Sanctum token
    |----------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('auth/user', [AuthController::class, 'user'])->name('auth.user');

        // Profile management (any authenticated user)
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar.upload');
        Route::delete('profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
        Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');

        // Notifications (any authenticated user)
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('notifications/{appNotification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

        // Reports (any authenticated user)
        Route::get('reports', [ApiReportController::class, 'index'])->name('reports.index');
        Route::post('reports', [ApiReportController::class, 'store'])->name('reports.store');

        // Organizations
        Route::apiResource('organizations', OrganizationController::class);

        // Plans
        Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
        Route::get('plans/{plan}', [PlanController::class, 'show'])->name('plans.show');

        // Subscriptions
        Route::apiResource('subscriptions', SubscriptionController::class)->only(['index', 'store', 'show']);

        // Payments
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');

        // Invitations
        Route::get('invitations', [InvitationController::class, 'index'])->name('invitations.index');
        Route::post('invitations', [InvitationController::class, 'store'])->name('invitations.store');
        Route::post('invitations/{invitation}/approve', [InvitationController::class, 'approve'])->name('invitations.approve');
        Route::post('invitations/{invitation}/reject', [InvitationController::class, 'reject'])->name('invitations.reject');
        Route::post('invitations/{invitation}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');

        // Vouchers
        Route::get('vouchers/templates', [VoucherController::class, 'templates'])->name('vouchers.templates.index');
        Route::post('vouchers/templates', [VoucherController::class, 'storeTemplate'])->name('vouchers.templates.store');
        Route::get('vouchers/templates/{template}/allocations', [VoucherController::class, 'allocations'])->name('vouchers.allocations.index');
        Route::post('vouchers/allocations', [VoucherController::class, 'storeAllocation'])->name('vouchers.allocations.store');
        Route::post('vouchers/templates/{template}/claim', [VoucherController::class, 'claim'])->name('vouchers.claim');
        Route::get('vouchers/my-claims', [VoucherController::class, 'myClaims'])->name('vouchers.my-claims');
        Route::post('vouchers/claims/{claim}/redeem', [VoucherController::class, 'redeem'])->name('vouchers.redeem');

        // Bin pairing
        Route::post('bins/{bin}/pair', [BinController::class, 'pair'])->name('bins.pair');

        // Shared read APIs (admin + collector)
        Route::middleware('role:admin,collector')->group(function (): void {
            Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('outlets', [OutletController::class, 'index'])->name('outlets.index');
            Route::get('outlets/{outlet}', [OutletController::class, 'show'])->name('outlets.show');
            Route::get('bins', [BinController::class, 'index'])->name('bins.index');
            Route::get('bins/{bin}', [BinController::class, 'show'])->name('bins.show');
            Route::get('detection-events', [DetectionEventController::class, 'index'])->name('detection-events.index');
            Route::get('detection-events/{detection_event}', [DetectionEventController::class, 'show'])->name('detection-events.show');
        });

        // Admin-only mutation APIs
        Route::middleware('role:admin')->group(function (): void {
            Route::post('outlets', [OutletController::class, 'store'])->name('outlets.store');
            Route::match(['put', 'patch'], 'outlets/{outlet}', [OutletController::class, 'update'])->name('outlets.update');
            Route::delete('outlets/{outlet}', [OutletController::class, 'destroy'])->name('outlets.destroy');
            Route::post('bins', [BinController::class, 'store'])->name('bins.store');
            Route::match(['put', 'patch'], 'bins/{bin}', [BinController::class, 'update'])->name('bins.update');
            Route::delete('bins/{bin}', [BinController::class, 'destroy'])->name('bins.destroy');

            // Admin pickup management
            Route::get('admin/pickup-requests', [AdminPickupRequestController::class, 'index'])->name('admin.pickup-requests.index');
            Route::get('admin/pickup-requests/{pickupRequest}', [AdminPickupRequestController::class, 'show'])->name('admin.pickup-requests.show');
            Route::post('admin/pickup-requests/{pickupRequest}/assign', [AdminPickupRequestController::class, 'assign'])->name('admin.pickup-requests.assign');
            Route::post('admin/pickup-requests/{pickupRequest}/complete', [AdminPickupRequestController::class, 'complete'])->name('admin.pickup-requests.complete');
            Route::post('admin/pickup-requests/{pickupRequest}/cancel', [AdminPickupRequestController::class, 'cancel'])->name('admin.pickup-requests.cancel');

            // Admin registration request management
            Route::get('registration-requests', [RegistrationRequestController::class, 'index'])->name('registration-requests.index');
            Route::post('registration-requests/{registrationRequest}/approve', [RegistrationRequestController::class, 'approve'])->name('registration-requests.approve');
            Route::post('registration-requests/{registrationRequest}/reject', [RegistrationRequestController::class, 'reject'])->name('registration-requests.reject');

            // Admin user management
            Route::get('admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
            Route::get('admin/users/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
        });

        // Customer APIs (public_user, store_owner, collector)
        Route::middleware('role:public_user,store_owner,collector')->prefix('customer')->name('customer.')->group(function (): void {
            Route::get('stats', [CustomerController::class, 'stats'])->name('stats');
            Route::get('history', [CustomerController::class, 'history'])->name('history');
            Route::get('leaderboard', [CustomerController::class, 'leaderboard'])->name('leaderboard');
            Route::post('scan', [CustomerController::class, 'scan'])->name('scan');

            // Rewards & redemptions
            Route::get('rewards', [RewardController::class, 'index'])->name('rewards.index');
            Route::post('rewards/{reward}/redeem', [RewardController::class, 'redeem'])->name('rewards.redeem');
            Route::get('redemptions', [RewardController::class, 'myRedemptions'])->name('redemptions.index');
        });

        // Store owner APIs
        Route::middleware('role:store_owner')->prefix('store-owner')->name('store-owner.')->group(function (): void {
            // Dashboard
            Route::get('dashboard', [StoreOwnerDashboardController::class, 'index'])->name('dashboard');
            Route::get('brand', [StoreOwnerDashboardController::class, 'brand'])->name('brand');
            Route::get('analytics', [StoreOwnerDashboardController::class, 'analytics'])->name('analytics');
            // Outlets & Bins
            Route::get('outlets', [StoreOwnerOutletController::class, 'index'])->name('outlets.index');
            Route::get('outlets/{outlet}', [StoreOwnerOutletController::class, 'show'])->name('outlets.show');
            Route::get('bins', [StoreOwnerOutletController::class, 'bins'])->name('bins.index');
            // Rewards
            Route::get('rewards', [StoreOwnerRewardController::class, 'index'])->name('rewards.index');
            Route::post('rewards', [StoreOwnerRewardController::class, 'store'])->name('rewards.store');
            Route::put('rewards/{reward}', [StoreOwnerRewardController::class, 'update'])->name('rewards.update');
            Route::delete('rewards/{reward}', [StoreOwnerRewardController::class, 'destroy'])->name('rewards.destroy');
        });

        // Collector pickup APIs
        Route::prefix('collector')->name('collector.')->middleware('role:collector')->group(function (): void {
            Route::get('pickups', [CollectorPickupController::class, 'index'])->name('pickups.index');
            Route::get('stats', [CollectorPickupController::class, 'stats'])->name('stats');
            Route::post('pickups/{pickupRequest}/claim', [CollectorPickupController::class, 'claim'])->name('pickups.claim');
            Route::post('pickups/{pickupRequest}/complete', [CollectorPickupController::class, 'complete'])->name('pickups.complete');

            // Route optimization
            Route::get('routes', [CollectorRouteController::class, 'index'])->name('routes.index');
            Route::get('routes/{collectionRoute}', [CollectorRouteController::class, 'show'])->name('routes.show');
            Route::post('routes/generate', [CollectorRouteController::class, 'generate'])->name('routes.generate');
            Route::post('routes/{collectionRoute}/accept', [CollectorRouteController::class, 'accept'])->name('routes.accept');
            Route::post('routes/{collectionRoute}/start', [CollectorRouteController::class, 'start'])->name('routes.start');
            Route::post('routes/{collectionRoute}/stops/{order}/complete', [CollectorRouteController::class, 'completeStop'])->name('routes.stops.complete');
            Route::post('routes/{collectionRoute}/stops/{order}/skip', [CollectorRouteController::class, 'skipStop'])->name('routes.stops.skip');
            Route::post('routes/{collectionRoute}/complete', [CollectorRouteController::class, 'complete'])->name('routes.complete');
            Route::post('routes/{collectionRoute}/reject', [CollectorRouteController::class, 'reject'])->name('routes.reject');
        });
    });
});
