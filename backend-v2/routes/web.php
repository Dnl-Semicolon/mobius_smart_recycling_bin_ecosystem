<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\BinController;
use App\Http\Controllers\Admin\OutletController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Brand\DashboardController;
use App\Http\Controllers\LeadController;
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

// Lead capture (public, no auth)
Route::get('get-started', [LeadController::class, 'create'])->name('get-started');
Route::post('get-started', [LeadController::class, 'store'])->name('get-started.store');
Route::get('get-started/thank-you', [LeadController::class, 'confirmation'])->name('get-started.confirmation');

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
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/leads', [App\Http\Controllers\Admin\LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [App\Http\Controllers\Admin\LeadController::class, 'show'])->name('leads.show');
    Route::post('/leads/{lead}/convert', [App\Http\Controllers\Admin\LeadController::class, 'convert'])->name('leads.convert');
    Route::post('/leads/{lead}/reject', [App\Http\Controllers\Admin\LeadController::class, 'reject'])->name('leads.reject');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/outlets', [OutletController::class, 'index'])->name('outlets.index');
    Route::get('/outlets/create', [OutletController::class, 'create'])->name('outlets.create');
    Route::post('/outlets', [OutletController::class, 'store'])->name('outlets.store');
    Route::get('/bins', [BinController::class, 'index'])->name('bins.index');
    Route::get('/bins/create', [BinController::class, 'create'])->name('bins.create');
    Route::post('/bins', [BinController::class, 'store'])->name('bins.store');
    Route::inertia('/billing', 'Admin/Billing')->name('billing');
});

// =============================================
// BRAND OWNER
// =============================================
Route::prefix('brand')->name('brand.')->middleware(['auth', 'verified', 'role:brand_owner'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/outlets', [App\Http\Controllers\Brand\OutletController::class, 'index'])->name('outlets.index');
    Route::get('/outlets/create', [App\Http\Controllers\Brand\OutletController::class, 'create'])->name('outlets.create');
    Route::post('/outlets', [App\Http\Controllers\Brand\OutletController::class, 'store'])->name('outlets.store');
    Route::inertia('/billing', 'Brand/Billing')->name('billing');
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
