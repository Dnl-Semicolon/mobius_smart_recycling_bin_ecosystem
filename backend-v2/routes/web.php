<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\UserController;
use App\Models\Plan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Landing page (public, no auth)
Route::get('/', function () {
    $plans = Plan::where('is_active', true)->orderByRaw('price_monthly = 0, price_monthly')->get();

    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'plans' => $plans,
    ]);
})->name('home');

// Role router — redirects to the correct dashboard based on primary role
Route::get('dashboard', function () {
    return match (auth()->user()->primaryRole()) {
        UserRole::Admin => redirect()->route('admin.dashboard'),
        UserRole::BrandOwner => redirect()->route('brand.dashboard'),
        UserRole::StoreOwner => redirect()->route('store.dashboard'),
        UserRole::Collector => redirect()->route('collector.dashboard'),
        UserRole::AgencyAdmin => redirect()->route('agency.dashboard'),
        default => redirect()->route('public.dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// =============================================
// ADMIN
// =============================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/', fn () => Inertia::render('Admin/Dashboard'))->name('dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});

// =============================================
// BRAND OWNER
// =============================================
Route::prefix('brand')->name('brand.')->middleware(['auth', 'verified', 'role:brand_owner'])->group(function () {
    Route::get('/', fn () => Inertia::render('Brand/Dashboard'))->name('dashboard');
});

// =============================================
// STORE OWNER
// =============================================
Route::prefix('store')->name('store.')->middleware(['auth', 'verified', 'role:store_owner'])->group(function () {
    Route::get('/', fn () => Inertia::render('Store/Dashboard'))->name('dashboard');
});

// =============================================
// COLLECTOR
// =============================================
Route::prefix('collector')->name('collector.')->middleware(['auth', 'verified', 'role:collector'])->group(function () {
    Route::get('/', fn () => Inertia::render('Collector/Dashboard'))->name('dashboard');
});

// =============================================
// AGENCY ADMIN
// =============================================
Route::prefix('agency')->name('agency.')->middleware(['auth', 'verified', 'role:agency_admin'])->group(function () {
    Route::get('/', fn () => Inertia::render('Agency/Dashboard'))->name('dashboard');
});

// =============================================
// PUBLIC USER
// =============================================
Route::prefix('public')->name('public.')->middleware(['auth', 'verified', 'role:public_user'])->group(function () {
    Route::get('/', fn () => Inertia::render('Public/Dashboard'))->name('dashboard');
});

require __DIR__.'/settings.php';
