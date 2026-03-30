<?php

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\BrandApplication;
use App\Models\User;
use App\Services\ApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a brand claim for an existing catalog brand', function () {
    $brand = Brand::factory()->create(['user_id' => null, 'status' => 'approved']);
    $service = app(ApplicationService::class);

    $application = $service->registerBrandClaim([
        'brand_id' => $brand->id,
        'contact_person' => 'Alice Wong',
        'email' => 'alice@starbucks.my',
        'phone' => '+60123456789',
        'password' => 'SecureP@ss1',
    ]);

    expect($application)->toBeInstanceOf(BrandApplication::class)
        ->and($application->brand_id)->toBe($brand->id)
        ->and($application->brand_name)->toBe($brand->name)
        ->and($application->status)->toBe(ApplicationStatus::Pending);

    $user = User::where('email', 'alice@starbucks.my')->first();
    expect($user)->not->toBeNull()
        ->and($application->user_id)->toBe($user->id);

    // Brand should remain unclaimed
    expect($brand->fresh()->user_id)->toBeNull();
});

it('registers a new brand request', function () {
    $service = app(ApplicationService::class);

    $application = $service->registerNewBrand([
        'brand_name' => 'Awesome Tea Co',
        'description' => 'New bubble tea brand',
        'website_url' => 'https://awesometea.com',
        'contact_person' => 'Bob Lee',
        'email' => 'bob@awesometea.com',
        'phone' => '+60198765432',
        'password' => 'SecureP@ss1',
    ]);

    expect($application)->toBeInstanceOf(BrandApplication::class)
        ->and($application->brand_id)->toBeNull()
        ->and($application->brand_name)->toBe('Awesome Tea Co')
        ->and($application->status)->toBe(ApplicationStatus::Pending);
});

it('approves a brand claim and sets user_id on brand', function () {
    $brand = Brand::factory()->create(['user_id' => null, 'status' => 'approved']);
    $applicantUser = User::factory()->create(['roles' => ['public_user']]);
    $admin = User::factory()->create(['roles' => ['admin']]);

    $application = BrandApplication::create([
        'brand_id' => $brand->id,
        'user_id' => $applicantUser->id,
        'status' => 'pending',
        'brand_name' => $brand->name,
        'contact_person' => 'Alice',
        'contact_email' => $applicantUser->email,
    ]);

    $service = app(ApplicationService::class);
    $service->approveBrandApplication($application, $admin, [
        'points_multiplier' => 1.50,
        'rewards_budget' => 10000,
    ]);

    expect($brand->fresh()->user_id)->toBe($applicantUser->id)
        ->and($application->fresh()->status)->toBe(ApplicationStatus::Approved)
        ->and($applicantUser->fresh()->hasRole(UserRole::StoreOwner))->toBeTrue();
});

it('approves a new brand request and creates the brand', function () {
    $applicantUser = User::factory()->create(['roles' => ['public_user']]);
    $admin = User::factory()->create(['roles' => ['admin']]);

    $application = BrandApplication::create([
        'brand_id' => null,
        'user_id' => $applicantUser->id,
        'status' => 'pending',
        'brand_name' => 'New Brand Co',
        'description' => 'A brand new brand',
        'website_url' => 'https://newbrand.com',
        'contact_person' => 'Charlie',
        'contact_email' => $applicantUser->email,
    ]);

    $service = app(ApplicationService::class);
    $service->approveBrandApplication($application, $admin, [
        'points_multiplier' => 1.30,
        'rewards_budget' => 5000,
    ]);

    $newBrand = Brand::where('name', 'New Brand Co')->first();
    expect($newBrand)->not->toBeNull()
        ->and($newBrand->user_id)->toBe($applicantUser->id)
        ->and($newBrand->status)->toBe(ApplicationStatus::Approved)
        ->and($newBrand->active)->toBeTrue()
        ->and($application->fresh()->status)->toBe(ApplicationStatus::Approved)
        ->and($application->fresh()->brand_id)->toBe($newBrand->id);
});

it('rejects a brand application', function () {
    $applicantUser = User::factory()->create(['roles' => ['public_user']]);
    $admin = User::factory()->create(['roles' => ['admin']]);

    $application = BrandApplication::create([
        'brand_id' => null,
        'user_id' => $applicantUser->id,
        'status' => 'pending',
        'brand_name' => 'Sketchy Brand',
        'contact_person' => 'Dan',
        'contact_email' => $applicantUser->email,
    ]);

    $service = app(ApplicationService::class);
    $service->rejectBrandApplication($application, $admin, 'Insufficient documentation');

    expect($application->fresh()->status)->toBe(ApplicationStatus::Rejected)
        ->and($application->fresh()->rejection_reason)->toBe('Insufficient documentation');
});
