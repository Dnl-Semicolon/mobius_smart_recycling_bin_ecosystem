<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\BinController;
use App\Http\Controllers\Admin\OutletController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Brand\BillingController;
use App\Http\Controllers\Brand\DashboardController;
use App\Http\Controllers\Brand\StaffController;
use App\Http\Controllers\Brand\VoucherController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\Store\RedemptionController;
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
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/outlets', [OutletController::class, 'index'])->name('outlets.index');
    Route::get('/outlets/create', [OutletController::class, 'create'])->name('outlets.create');
    Route::post('/outlets', [OutletController::class, 'store'])->name('outlets.store');
    Route::get('/bins', [BinController::class, 'index'])->name('bins.index');
    Route::get('/bins/create', [BinController::class, 'create'])->name('bins.create');
    Route::post('/bins', [BinController::class, 'store'])->name('bins.store');
    Route::get('/vouchers', [App\Http\Controllers\Admin\VoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/billing', [App\Http\Controllers\Admin\BillingController::class, 'index'])->name('billing');
    Route::post('/billing/{subscription}/activate', [App\Http\Controllers\Admin\BillingController::class, 'activate'])->name('billing.activate');
    Route::get('/billing/{subscription}/customize', [App\Http\Controllers\Admin\BillingController::class, 'customize'])->name('billing.customize');
    Route::patch('/billing/{subscription}/customize', [App\Http\Controllers\Admin\BillingController::class, 'updateCustom'])->name('billing.update-custom');
});

// =============================================
// BRAND OWNER
// =============================================
Route::prefix('brand')->name('brand.')->middleware(['auth', 'verified', 'role:brand_owner'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/outlets', [App\Http\Controllers\Brand\OutletController::class, 'index'])->name('outlets.index');
    Route::get('/outlets/create', [App\Http\Controllers\Brand\OutletController::class, 'create'])->name('outlets.create');
    Route::post('/outlets', [App\Http\Controllers\Brand\OutletController::class, 'store'])->name('outlets.store');
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::get('/billing', [BillingController::class, 'index'])->name('billing');
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/success', [BillingController::class, 'success'])->name('billing.success');
    Route::get('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::get('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
});

// =============================================
// STORE OWNER
// =============================================
Route::prefix('store')->name('store.')->middleware(['auth', 'verified', 'role:store_owner'])->group(function () {
    Route::get('/', [App\Http\Controllers\Store\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/redeem', [RedemptionController::class, 'index'])->name('redeem');
    Route::post('/redeem/lookup', [RedemptionController::class, 'lookup'])->name('redeem.lookup');
    Route::post('/redeem/confirm', [RedemptionController::class, 'redeem'])->name('redeem.confirm');
});

// =============================================
// COLLECTOR
// =============================================
Route::prefix('collector')->name('collector.')->middleware(['auth', 'verified', 'role:collector'])->group(function () {
    Route::get('/', [App\Http\Controllers\Collector\DashboardController::class, 'index'])->name('dashboard');
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
    Route::get('/', [App\Http\Controllers\PublicUser\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/vouchers', [App\Http\Controllers\PublicUser\VoucherController::class, 'index'])->name('vouchers');
    Route::post('/vouchers/{template}/claim', [App\Http\Controllers\PublicUser\VoucherController::class, 'claim'])->name('vouchers.claim');
});

// Dev test routes
Route::inertia('/dev/places-test', 'Dev/PlacesTest')->name('dev.places-test');

require __DIR__.'/settings.php';
