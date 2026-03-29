<?php

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Brand;
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
    $brand = Brand::factory()->pending()->create(['name' => 'PendingBrand']);

    $this->actingAs($admin)
        ->get(route('admin.applications.brands.index'))
        ->assertOk()
        ->assertSee('PendingBrand');
});

it('shows brand application details', function () {
    $admin = User::factory()->admin()->create();
    $applicant = User::factory()->create();
    $brand = Brand::factory()->pending()->create([
        'name' => 'DetailBrand',
        'user_id' => $applicant->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.applications.brands.show', $brand))
        ->assertOk()
        ->assertSee('DetailBrand');
});

it('approves a brand application', function () {
    $admin = User::factory()->admin()->create();
    $applicant = User::factory()->publicUser()->create();
    $brand = Brand::factory()->pending()->create([
        'user_id' => $applicant->id,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.applications.brands.approve', $brand), [
            'points_multiplier' => 1.50,
            'rewards_budget' => 10000,
        ])
        ->assertRedirect(route('admin.applications.brands.index'));

    $brand->refresh();
    $applicant->refresh();

    expect($brand->status)->toBe(ApplicationStatus::Approved)
        ->and($brand->active)->toBeTrue()
        ->and($brand->points_multiplier)->toBe('1.50')
        ->and($brand->reviewed_by)->toBe($admin->id)
        ->and($applicant->hasRole(UserRole::StoreOwner))->toBeTrue();
});

it('rejects a brand application', function () {
    $admin = User::factory()->admin()->create();
    $applicant = User::factory()->publicUser()->create();
    $brand = Brand::factory()->pending()->create([
        'user_id' => $applicant->id,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.applications.brands.reject', $brand), [
            'rejection_reason' => 'Insufficient documentation',
        ])
        ->assertRedirect(route('admin.applications.brands.index'));

    $brand->refresh();
    expect($brand->status)->toBe(ApplicationStatus::Rejected)
        ->and($brand->rejection_reason)->toBe('Insufficient documentation');
});

it('validates approve brand requires multiplier and budget', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->pending()->create();

    $this->actingAs($admin)
        ->post(route('admin.applications.brands.approve', $brand), [])
        ->assertSessionHasErrors(['points_multiplier', 'rewards_budget']);
});

it('validates reject requires reason', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->pending()->create();

    $this->actingAs($admin)
        ->post(route('admin.applications.brands.reject', $brand), [])
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
