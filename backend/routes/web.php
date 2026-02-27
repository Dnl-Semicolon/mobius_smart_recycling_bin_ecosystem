<?php

use App\Http\Controllers\Admin\BinController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DetectionEventController;
use App\Http\Controllers\Admin\OutletController;
use App\Http\Controllers\Admin\PickupRequestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\Z_ACodex_TempWorkbenchController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Collector\DashboardController as CollectorDashboardController;
use App\Http\Controllers\Dev\ApiExplorerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\DashboardController as PublicDashboardController;
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
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Root redirect — send users to their role-appropriate dashboard
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return match (auth()->user()->role) {
        \App\Enums\UserRole::Admin => redirect()->route('admin.dashboard'),
        \App\Enums\UserRole::Collector => redirect()->route('collector.dashboard'),
        \App\Enums\UserRole::PublicUser => redirect()->route('public.dashboard'),
        default => redirect()->route('public.dashboard'),
    };
});

/*
|--------------------------------------------------------------------------
| Admin Routes — requires login + admin role
|--------------------------------------------------------------------------
| These are the routes for the admin panel. The 'auth' middleware checks
| that the user is logged in. The 'role:admin' middleware (our custom one)
| checks that the logged-in user has the 'admin' role.
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('z-acodex-temp-workbench', [Z_ACodex_TempWorkbenchController::class, 'index'])->name('z-acodex-temp-workbench');

    // Outlets (complete CRUD)
    Route::resource('outlets', OutletController::class);

    // Bins (complete CRUD + assign/unassign)
    Route::resource('bins', BinController::class);
    Route::post('bins/{bin}/assign', [BinController::class, 'assign'])->name('bins.assign');
    Route::post('bins/{bin}/unassign', [BinController::class, 'unassign'])->name('bins.unassign');
    // Detection Events (read-only: index, show + simulate for demo)
    Route::post('detection-events/simulate', [DetectionEventController::class, 'simulate'])->name('detection-events.simulate');
    Route::resource('detection-events', DetectionEventController::class)->only(['index', 'show']);

    // Pickup Requests (admin oversight + cancel/unclaim actions)
    Route::resource('pickup-requests', PickupRequestController::class)->only(['index', 'show']);
    Route::post('pickup-requests/{pickup_request}/cancel', [PickupRequestController::class, 'cancel'])->name('pickup-requests.cancel');
    Route::post('pickup-requests/{pickup_request}/unclaim', [PickupRequestController::class, 'unclaim'])->name('pickup-requests.unclaim');

    // Users (admin manages all user accounts)
    Route::resource('users', UserController::class)->except(['show']);

    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');
});

/*
|--------------------------------------------------------------------------
| Collector Routes — requires login + collector role
|--------------------------------------------------------------------------
| Basic collector dashboard. For now just shows bins assigned to outlets
| that need pickup. Route optimization will be added here later.
*/
Route::prefix('collector')->name('collector.')->middleware(['auth', 'role:collector'])->group(function () {
    Route::get('/', [CollectorDashboardController::class, 'index'])->name('dashboard');
    Route::post('pickups/{pickupRequest}/claim', [CollectorDashboardController::class, 'claim'])->name('pickups.claim');
    Route::post('pickups/{pickupRequest}/complete', [CollectorDashboardController::class, 'complete'])->name('pickups.complete');

    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');
});

Route::prefix('public')->name('public.')->middleware(['auth', 'role:public_user'])->group(function () {
    Route::get('/', [PublicDashboardController::class, 'index'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Dev Routes — local/testing environment only
|--------------------------------------------------------------------------
*/
if (App::environment('local', 'testing')) {
    Route::get('/dev/api-explorer', [ApiExplorerController::class, 'index'])->name('dev.api-explorer');
}
