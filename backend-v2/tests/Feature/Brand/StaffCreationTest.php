<?php

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\RegistrationRequest;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('brand staff creation normalizes email and phone', function () {
    $organization = Organization::create([
        'name' => 'Acme Brand',
        'type' => 'beverage_company',
        'is_active' => true,
    ]);

    $plan = Plan::create([
        'name' => 'Growth',
        'price_monthly' => 100,
        'price_yearly' => 1000,
        'staff_limit' => 5,
        'bin_limit' => 1,
        'outlet_limit' => 1,
        'api_access' => false,
        'is_active' => true,
    ]);

    Subscription::create([
        'organization_id' => $organization->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'billing_interval' => 'monthly',
    ]);

    $brandOwner = User::factory()->create([
        'organization_id' => $organization->id,
        'roles' => [UserRole::BrandOwner->value],
    ]);

    $response = $this
        ->actingAs($brandOwner)
        ->post(route('brand.staff.store'), [
            'name' => 'Outlet Manager',
            'email' => 'Manager@One.COM ',
            'phone' => '011-1234 5678',
        ]);

    $response->assertRedirect(route('brand.staff.index'));

    $staff = User::where('email', 'manager@one.com')->first();

    expect($staff)->not->toBeNull();
    expect($staff->phone)->toBe('+601112345678');
    expect($staff->email_verified_at)->not->toBeNull();
    expect($staff->organization_id)->toBe($organization->id);
    expect($staff->getRolesArray())->toBe([UserRole::StoreOwner->value]);
});

test('brand staff creation rejects duplicate canonical phone numbers', function () {
    $organization = Organization::create([
        'name' => 'Acme Brand',
        'type' => 'beverage_company',
        'is_active' => true,
    ]);

    $plan = Plan::create([
        'name' => 'Growth',
        'price_monthly' => 100,
        'price_yearly' => 1000,
        'staff_limit' => 5,
        'bin_limit' => 1,
        'outlet_limit' => 1,
        'api_access' => false,
        'is_active' => true,
    ]);

    Subscription::create([
        'organization_id' => $organization->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'billing_interval' => 'monthly',
    ]);

    $brandOwner = User::factory()->create([
        'organization_id' => $organization->id,
        'roles' => [UserRole::BrandOwner->value],
    ]);

    User::factory()->create([
        'phone' => '+601112345678',
    ]);

    $response = $this
        ->actingAs($brandOwner)
        ->from(route('brand.staff.create'))
        ->post(route('brand.staff.store'), [
            'name' => 'Outlet Manager',
            'email' => 'manager@one.com',
            'phone' => '011-1234 5678',
        ]);

    $response
        ->assertRedirect(route('brand.staff.create'))
        ->assertSessionHasErrors('phone');
});

test('brand staff creation rejects contact details that already exist as a lead', function () {
    $organization = Organization::create([
        'name' => 'Acme Brand',
        'type' => 'beverage_company',
        'is_active' => true,
    ]);

    $plan = Plan::create([
        'name' => 'Growth',
        'price_monthly' => 100,
        'price_yearly' => 1000,
        'staff_limit' => 5,
        'bin_limit' => 1,
        'outlet_limit' => 1,
        'api_access' => false,
        'is_active' => true,
    ]);

    Subscription::create([
        'organization_id' => $organization->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'billing_interval' => 'monthly',
    ]);

    $brandOwner = User::factory()->create([
        'organization_id' => $organization->id,
        'roles' => [UserRole::BrandOwner->value],
    ]);

    RegistrationRequest::create([
        'company_name' => 'Existing Lead Co',
        'contact_name' => 'Lead Owner',
        'contact_email' => 'lead@example.com',
        'contact_phone' => '+601112345678',
        'type' => 'beverage_company',
        'status' => 'pending',
    ]);

    $response = $this
        ->actingAs($brandOwner)
        ->from(route('brand.staff.create'))
        ->post(route('brand.staff.store'), [
            'name' => 'Outlet Manager',
            'email' => 'Lead@Example.COM ',
            'phone' => '011-1234 5678',
        ]);

    $response
        ->assertRedirect(route('brand.staff.create'))
        ->assertSessionHasErrors([
            'email' => 'Please contact admin to continue with this existing lead.',
            'phone' => 'Please contact admin to continue with this existing lead.',
        ]);
});
