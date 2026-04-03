<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\BinController;
use App\Http\Controllers\Admin\BinSessionController;
use App\Http\Controllers\Admin\BrandDirectoryController;
use App\Http\Controllers\Admin\BrandMonitoringController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DetectionEventController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OutletController;
use App\Http\Controllers\Admin\PaymentManagementController;
use App\Http\Controllers\Admin\PickupRequestController;
use App\Http\Controllers\Admin\PlacesProxyController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SubscriptionManagementController;
use App\Http\Controllers\Admin\UserController;
// use App\Http\Controllers\Admin\Z_ACodex_TempWorkbenchController;
use App\Http\Controllers\Agency;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Collector\DashboardController as CollectorDashboardController;
use App\Http\Controllers\Dev\ApiExplorerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\DashboardController as PublicDashboardController;
use App\Http\Controllers\Registration\AgencyRegistrationController;
use App\Http\Controllers\Registration\BrandRegistrationController;
use App\Http\Controllers\StoreOwner\DashboardController as StoreOwnerDashboardController;
use App\Http\Controllers\StoreOwner\RewardController as StoreOwnerRewardController;
use App\Http\Controllers\StoreOwner\StaffController as StoreOwnerStaffController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes (guests only — already logged in users get redirected)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Email Verification
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Corporate Home + Registration + Subscription
|--------------------------------------------------------------------------
*/
Route::get('/home', fn () => view('home'))->name('home');

Route::prefix('register')->name('registration.')->group(function () {
    Route::get('brand', [BrandRegistrationController::class, 'create'])->name('brand.create');
    Route::post('brand', [BrandRegistrationController::class, 'store'])->name('brand.store');
    Route::get('agency', [AgencyRegistrationController::class, 'create'])->name('agency.create');
    Route::post('agency', [AgencyRegistrationController::class, 'store'])->name('agency.store');
    Route::get('brand/search', [BrandRegistrationController::class, 'search'])->name('brand.search');
    Route::get('success', fn () => view('registration.success'))->name('success');
});

// Subscription (Stripe Cashier)
Route::get('/subscribe', [SubscriptionController::class, 'plans'])->name('subscribe.plans');
Route::middleware('auth')->prefix('subscribe')->name('subscribe.')->group(function () {
    Route::post('/checkout', [SubscriptionController::class, 'checkout'])->name('checkout');
    Route::get('/success', [SubscriptionController::class, 'success'])->name('success');
    Route::get('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
    Route::get('/manage', [SubscriptionController::class, 'manage'])->name('manage');
    Route::post('/cancel-subscription', [SubscriptionController::class, 'cancelSubscription'])->name('cancel-subscription');
    Route::get('/billing-portal', [SubscriptionController::class, 'billingPortal'])->name('billing-portal');
});

/*
|--------------------------------------------------------------------------
| Root redirect — send users to their role-appropriate dashboard
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return match (auth()->user()->primaryRole()) {
        UserRole::Admin => redirect()->route('admin.dashboard'),
        UserRole::Collector => redirect()->route('collector.dashboard'),
        UserRole::StoreOwner => redirect()->route('store.dashboard'),
        UserRole::PublicUser => redirect()->route('public.dashboard'),
        UserRole::AgencyAdmin => redirect()->route('agency.dashboard'),
        default => redirect()->route('public.dashboard'),
    };
});

/*
|--------------------------------------------------------------------------
| Admin Routes — requires login + admin role
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    // Route::get('z-acodex-temp-workbench', [Z_ACodex_TempWorkbenchController::class, 'index'])->name('z-acodex-temp-workbench');

    // Outlets
    Route::get('outlets', [OutletController::class, 'index'])->name('outlets.index');
    Route::get('outlets/create', [OutletController::class, 'create'])->name('outlets.create');
    Route::post('outlets', [OutletController::class, 'store'])->name('outlets.store');
    Route::get('outlets/{outlet}', [OutletController::class, 'show'])->name('outlets.show');
    Route::get('outlets/{outlet}/edit', [OutletController::class, 'edit'])->name('outlets.edit');
    Route::put('outlets/{outlet}', [OutletController::class, 'update'])->name('outlets.update');
    Route::delete('outlets/{outlet}', [OutletController::class, 'destroy'])->name('outlets.destroy');

    // Bins
    Route::get('bins', [BinController::class, 'index'])->name('bins.index');
    Route::get('bins/create', [BinController::class, 'create'])->name('bins.create');
    Route::get('bins/fleet-status', [BinController::class, 'fleetStatus'])->name('bins.fleet-status');
    Route::post('bins', [BinController::class, 'store'])->name('bins.store');
    Route::get('bins/{bin}', [BinController::class, 'show'])->name('bins.show');
    Route::get('bins/{bin}/edit', [BinController::class, 'edit'])->name('bins.edit');
    Route::get('bins/{bin}/telemetry', [BinController::class, 'telemetry'])->name('bins.telemetry');
    Route::put('bins/{bin}', [BinController::class, 'update'])->name('bins.update');
    Route::delete('bins/{bin}', [BinController::class, 'destroy'])->name('bins.destroy');
    Route::post('bins/{bin}/assign', [BinController::class, 'assign'])->name('bins.assign');
    Route::post('bins/{bin}/unassign', [BinController::class, 'unassign'])->name('bins.unassign');
    Route::get('bins/{bin}/detections', [BinController::class, 'detections'])->name('bins.detections');
    Route::get('bins/{bin}/pickups', [BinController::class, 'pickups'])->name('bins.pickups');
    Route::get('bins/{bin}/analytics', [BinController::class, 'analytics'])->name('bins.analytics');

    // Detection Events
    Route::get('detection-events', [DetectionEventController::class, 'index'])->name('detection-events.index');
    Route::get('detection-events/{detection_event}', [DetectionEventController::class, 'show'])->name('detection-events.show');
    Route::post('detection-events/simulate', [DetectionEventController::class, 'simulate'])->name('detection-events.simulate');

    // Bin Sessions
    Route::get('bin-sessions', [BinSessionController::class, 'index'])->name('bin-sessions.index');
    Route::get('bin-sessions/{binSession}', [BinSessionController::class, 'show'])->name('bin-sessions.show');

    // Pickup Requests
    Route::get('pickup-requests', [PickupRequestController::class, 'index'])->name('pickup-requests.index');
    Route::get('pickup-requests/{pickup_request}', [PickupRequestController::class, 'show'])->name('pickup-requests.show');
    Route::post('pickup-requests/{pickup_request}/cancel', [PickupRequestController::class, 'cancel'])->name('pickup-requests.cancel');
    Route::post('pickup-requests/{pickup_request}/unclaim', [PickupRequestController::class, 'unclaim'])->name('pickup-requests.unclaim');

    // Users
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::delete('users/{user}/avatar', [UserController::class, 'removeAvatar'])->name('users.avatar.remove');

    // Brand monitoring
    Route::get('brands', [BrandMonitoringController::class, 'index'])->name('brands.index');
    Route::get('brands/{brand}', [BrandMonitoringController::class, 'show'])->name('brands.show');

    // Brand directory (catalog management)
    Route::get('brand-directory', [BrandDirectoryController::class, 'index'])->name('brand-directory.index');
    Route::get('brand-directory/create', [BrandDirectoryController::class, 'create'])->name('brand-directory.create');
    Route::post('brand-directory', [BrandDirectoryController::class, 'store'])->name('brand-directory.store');
    Route::get('brand-directory/{brand}/edit', [BrandDirectoryController::class, 'edit'])->name('brand-directory.edit');
    Route::put('brand-directory/{brand}', [BrandDirectoryController::class, 'update'])->name('brand-directory.update');

    // Applications (brand + agency registrations)
    Route::prefix('applications')->name('applications.')->group(function () {
        Route::get('brands', [ApplicationController::class, 'brandApplications'])->name('brands.index');
        Route::get('brands/{brand_application}', [ApplicationController::class, 'showBrandApplication'])->name('brands.show');
        Route::post('brands/{brand_application}/approve', [ApplicationController::class, 'approveBrand'])->name('brands.approve');
        Route::post('brands/{brand_application}/reject', [ApplicationController::class, 'rejectBrand'])->name('brands.reject');
        Route::get('agencies', [ApplicationController::class, 'agencyApplications'])->name('agencies.index');
        Route::get('agencies/{collector_agency}', [ApplicationController::class, 'showAgencyApplication'])->name('agencies.show');
        Route::post('agencies/{collector_agency}/approve', [ApplicationController::class, 'approveAgency'])->name('agencies.approve');
        Route::post('agencies/{collector_agency}/reject', [ApplicationController::class, 'rejectAgency'])->name('agencies.reject');
    });

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::post('reports/{report}/resolve', [ReportController::class, 'resolve'])->name('reports.resolve');

    // Subscriptions
    Route::get('subscriptions', [SubscriptionManagementController::class, 'index'])->name('subscriptions.index');

    // Payments
    Route::get('payments', [PaymentManagementController::class, 'index'])->name('payments.index');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // Places Autocomplete proxy (server-side, keeps API key hidden)
    Route::get('places/autocomplete', [PlacesProxyController::class, 'autocomplete'])->name('places.autocomplete');
    Route::get('places/details', [PlacesProxyController::class, 'details'])->name('places.details');

    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');
});

/*
|--------------------------------------------------------------------------
| Collector Routes — requires login + collector role
|--------------------------------------------------------------------------
*/
Route::prefix('collector')->name('collector.')->middleware(['auth', 'verified', 'role:collector'])->group(function () {
    Route::get('/', [CollectorDashboardController::class, 'index'])->name('dashboard');
    Route::post('pickups/{pickupRequest}/claim', [CollectorDashboardController::class, 'claim'])->name('pickups.claim');
    Route::post('pickups/{pickupRequest}/complete', [CollectorDashboardController::class, 'complete'])->name('pickups.complete');

    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');
});

/*
|--------------------------------------------------------------------------
| Store Owner Routes — requires login + store_owner role
|--------------------------------------------------------------------------
*/
Route::prefix('store')->name('store.')->middleware(['auth', 'verified', 'role:store_owner'])->group(function () {
    Route::get('/', [StoreOwnerDashboardController::class, 'index'])->name('dashboard');
    Route::get('analytics', [StoreOwnerDashboardController::class, 'analytics'])->name('analytics');

    // Staff management (HQ only)
    Route::get('staff', [StoreOwnerStaffController::class, 'index'])->name('staff.index');
    Route::post('staff/invite', [StoreOwnerStaffController::class, 'invite'])->name('staff.invite');
    Route::delete('staff/{user}', [StoreOwnerStaffController::class, 'remove'])->name('staff.remove');

    // Reward management
    Route::get('rewards', [StoreOwnerRewardController::class, 'index'])->name('rewards.index');
    Route::get('rewards/create', [StoreOwnerRewardController::class, 'create'])->name('rewards.create');
    Route::post('rewards', [StoreOwnerRewardController::class, 'store'])->name('rewards.store');
    Route::get('rewards/{reward}/edit', [StoreOwnerRewardController::class, 'edit'])->name('rewards.edit');
    Route::put('rewards/{reward}', [StoreOwnerRewardController::class, 'update'])->name('rewards.update');
    Route::delete('rewards/{reward}', [StoreOwnerRewardController::class, 'destroy'])->name('rewards.destroy');

    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');
});

/*
|--------------------------------------------------------------------------
| Agency Admin Routes — requires login + agency_admin role
|--------------------------------------------------------------------------
*/
Route::prefix('agency')->name('agency.')->middleware(['auth', 'verified', 'role:agency_admin'])->group(function () {
    Route::get('/', [Agency\DashboardController::class, 'index'])->name('dashboard');
    Route::get('analytics', [Agency\DashboardController::class, 'analytics'])->name('analytics');

    // Collectors
    Route::get('collectors', [Agency\CollectorController::class, 'index'])->name('collectors.index');
    Route::post('collectors/invite', [Agency\CollectorController::class, 'invite'])->name('collectors.invite');
    Route::get('collectors/{user}', [Agency\CollectorController::class, 'show'])->name('collectors.show');
    Route::delete('collectors/{user}', [Agency\CollectorController::class, 'remove'])->name('collectors.remove');

    // Routes
    Route::get('routes', [Agency\RouteHistoryController::class, 'index'])->name('routes.index');
    Route::get('routes/{collectionRoute}', [Agency\RouteHistoryController::class, 'show'])->name('routes.show');

    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');
});

Route::prefix('public')->name('public.')->middleware(['auth', 'verified', 'role:public_user'])->group(function () {
    Route::get('/', [PublicDashboardController::class, 'index'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Dev Routes — local/testing environment only
|--------------------------------------------------------------------------
*/
if (App::environment('local', 'testing')) {
    Route::get('/dev/api-explorer', [ApiExplorerController::class, 'index'])->name('dev.api-explorer');
    Route::get('/dev/walkthrough/{file?}', function (string $file = 'demo') {
        $files = [
            'sprint4' => 'Z_AClaude_SPRINT4_WALKTHROUGH.md',
            'brand' => 'Z_AClaude_BRAND_DETECTION_WALKTHROUGH.md',
            'demo' => 'Z_AClaude_DEMO_FLOW_WALKTHROUGH.md',
        ];

        $filename = $files[$file] ?? $files['brand'];
        $path = base_path($filename);

        if (! file_exists($path)) {
            abort(404, "Walkthrough not found: {$file}");
        }

        $md = file_get_contents($path);
        $html = \Illuminate\Support\Str::markdown($md, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $html = preg_replace('/<table>/', '<div class="table-wrapper"><table>', $html);
        $html = preg_replace('/<\/table>/', '</table></div>', $html);

        return view('dev.walkthrough', compact('html'));
    })->name('dev.walkthrough');
}
