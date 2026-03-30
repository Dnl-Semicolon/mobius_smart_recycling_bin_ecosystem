<?php

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\BrandApplication;
use App\Models\CollectorAgency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires admin role to view brand applications', function () {
    $user = User::factory()->publicUser()->create();

    $this->actingAs($user)
        ->get(route('admin.applications.brands.index'))
        ->assertForbidden();
});

it('lists pending brand applications', function () {
    $admin = User::factory()->admin()->create();
    $applicant = User::factory()->create();
    $application = BrandApplication::create([
        'user_id' => $applicant->id,
        'status' => 'pending',
        'brand_name' => 'PendingBrand',
        'contact_person' => 'Test',
        'contact_email' => $applicant->email,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.applications.brands.index'))
        ->assertOk()
        ->assertSee('PendingBrand');
});

it('shows brand application details', function () {
    $admin = User::factory()->admin()->create();
    $applicant = User::factory()->create();
    $application = BrandApplication::create([
        'user_id' => $applicant->id,
        'status' => 'pending',
        'brand_name' => 'DetailBrand',
        'contact_person' => 'Test',
        'contact_email' => $applicant->email,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.applications.brands.show', $application))
        ->assertOk()
        ->assertSee('DetailBrand');
});

it('approves a brand application (claim existing)', function () {
    $admin = User::factory()->admin()->create();
    $applicant = User::factory()->publicUser()->create();
    $brand = Brand::factory()->create(['user_id' => null, 'status' => 'approved']);
    $application = BrandApplication::create([
        'brand_id' => $brand->id,
        'user_id' => $applicant->id,
        'status' => 'pending',
        'brand_name' => $brand->name,
        'contact_person' => 'Test',
        'contact_email' => $applicant->email,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.applications.brands.approve', $application), [
            'points_multiplier' => 1.50,
            'rewards_budget' => 10000,
        ])
        ->assertRedirect(route('admin.applications.brands.index'));

    $brand->refresh();
    $applicant->refresh();
    $application->refresh();

    expect($application->status)->toBe(ApplicationStatus::Approved)
        ->and($brand->user_id)->toBe($applicant->id)
        ->and($brand->points_multiplier)->toBe('1.50')
        ->and($application->reviewed_by)->toBe($admin->id)
        ->and($applicant->hasRole(UserRole::StoreOwner))->toBeTrue();
});

it('rejects a brand application', function () {
    $admin = User::factory()->admin()->create();
    $applicant = User::factory()->publicUser()->create();
    $application = BrandApplication::create([
        'user_id' => $applicant->id,
        'status' => 'pending',
        'brand_name' => 'RejectBrand',
        'contact_person' => 'Test',
        'contact_email' => $applicant->email,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.applications.brands.reject', $application), [
            'rejection_reason' => 'Insufficient documentation',
        ])
        ->assertRedirect(route('admin.applications.brands.index'));

    $application->refresh();
    expect($application->status)->toBe(ApplicationStatus::Rejected)
        ->and($application->rejection_reason)->toBe('Insufficient documentation');
});

it('validates approve brand requires multiplier and budget', function () {
    $admin = User::factory()->admin()->create();
    $applicant = User::factory()->create();
    $application = BrandApplication::create([
        'user_id' => $applicant->id,
        'status' => 'pending',
        'brand_name' => 'ValidateBrand',
        'contact_person' => 'Test',
        'contact_email' => $applicant->email,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.applications.brands.approve', $application), [])
        ->assertSessionHasErrors(['points_multiplier', 'rewards_budget']);
});

it('validates reject requires reason', function () {
    $admin = User::factory()->admin()->create();
    $applicant = User::factory()->create();
    $application = BrandApplication::create([
        'user_id' => $applicant->id,
        'status' => 'pending',
        'brand_name' => 'RejectValidate',
        'contact_person' => 'Test',
        'contact_email' => $applicant->email,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.applications.brands.reject', $application), [])
        ->assertSessionHasErrors(['rejection_reason']);
});

it('lists pending agency applications', function () {
    $admin = User::factory()->admin()->create();
    $agency = CollectorAgency::factory()->pending()->create(['name' => 'PendingAgency']);

    $this->actingAs($admin)
        ->get(route('admin.applications.agencies.index'))
        ->assertOk()
        ->assertSee('PendingAgency');
});

it('approves an agency application', function () {
    $admin = User::factory()->admin()->create();
    $applicant = User::factory()->publicUser()->create();
    $agency = CollectorAgency::factory()->pending()->create([
        'user_id' => $applicant->id,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.applications.agencies.approve', $agency))
        ->assertRedirect(route('admin.applications.agencies.index'));

    $agency->refresh();
    $applicant->refresh();

    expect($agency->status)->toBe(ApplicationStatus::Approved)
        ->and($applicant->hasRole(UserRole::AgencyAdmin))->toBeTrue();
});

it('rejects an agency application', function () {
    $admin = User::factory()->admin()->create();
    $agency = CollectorAgency::factory()->pending()->create();

    $this->actingAs($admin)
        ->post(route('admin.applications.agencies.reject', $agency), [
            'rejection_reason' => 'Coverage too limited',
        ])
        ->assertRedirect(route('admin.applications.agencies.index'));

    $agency->refresh();
    expect($agency->status)->toBe(ApplicationStatus::Rejected);
});
